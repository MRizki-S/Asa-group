<?php

namespace App\Http\Controllers\Produksi\PembangunanUnit;

use App\Http\Controllers\Controller;
use App\Models\PembangunanUnit;
use App\Models\PembangunanUnitBarangOrder;
use App\Models\PembangunanUnitBarangOrderDetail;
use App\Services\NotificationGroupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PembangunanUnitOrderBarangController extends Controller
{
    protected NotificationGroupService $notificationGroup;

    public function __construct(NotificationGroupService $notificationGroup)
    {
        $this->notificationGroup = $notificationGroup;
    }

    public function sendGroupNotificationOrder($pembangunanUnit, $order, $jenis = 'permintaan')
    {
        $pembangunanUnit->loadMissing(['unit.tahap.perumahaan', 'pembangunanUnitQc']);

        $unit = $pembangunanUnit->unit;
        $namaPerumahan = $unit->tahap->perumahaan->nama_perumahaan ?? '-';
        $namaTahap = $unit->tahap->nama_tahap ?? '-';
        $namaUnit = $unit->nama_unit ?? '-';
        $pengaju = Auth::user()->nama_lengkap ?? Auth::user()->name;

        $groupId = "ID GRUP GUDANG";

        if (!$groupId) return;

        if ($jenis === 'permintaan') {
            $header = "📦 *PENGAJUAN PERMINTAAN BAHAN*";
            $body = "Dear *Tim Logistik/Gudang*, terdapat pengajuan permintaan bahan material baru dari lapangan.";
        } else {
            $header = "🔄 *PENGAJUAN RETUR BAHAN*";
            $body = "Dear *Tim Logistik/Gudang*, terdapat pengajuan pengembalian (retur) bahan material dari lapangan.";
        }

        $messageGroup = "{$header}\n\n"
            . "{$body}\n\n"
            . "```\n"
            . "📍 Perumahan : {$namaPerumahan}\n"
            . "🏠 Tahap     : {$namaTahap}\n"
            . "🔑 Unit      : {$namaUnit}\n"
            . "👤 Diajukan  : {$pengaju}\n"
            . "📅 Tanggal   : " . now()->format('d/m/Y H:i') . " WIB\n"
            . "```\n\n"
            . "Mohon segera dicek pada sistem. Terima kasih! 🙏";

        try {
            $this->notificationGroup->send($groupId, $messageGroup);
        } catch (\Exception $e) {
            Log::error('WA Error: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'pembangunan_unit_id' => 'required',
            'pembangunan_unit_qc_id' => 'required',
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required',
            'items.*.nama_barang' => 'required',
            'items.*.satuan_id' => 'required',
            'items.*.satuan' => 'required',
            'items.*.jumlah_input' => 'required|numeric|min:0.001',
            'items.*.faktor_konversi' => 'required|numeric|min:0.001',
            'jenis_order' => 'required|string|in:stock,direct'
        ]);

        try {
            DB::beginTransaction();

            $pembangunanUnit = PembangunanUnit::findOrFail($request->pembangunan_unit_id);

            $order = PembangunanUnitBarangOrder::create([
                'pembangunan_unit_id' => $request->pembangunan_unit_id,
                'pembangunan_unit_qc_id' => $request->pembangunan_unit_qc_id,
                'jenis_order' => $request->jenis_order,
                'tanggal_diajukan' => now(),
                'status_order' => 'diproses',
                'catatan' => $request->catatan,
                'created_by' => Auth::id(),
            ]);

            foreach ($request->items as $item) {
                PembangunanUnitBarangOrderDetail::create([
                    'order_id' => $order->id,
                    'barang_id' => $item['barang_id'],
                    'nama_barang' => $item['nama_barang'],
                    'satuan_id' => $item['satuan_id'],
                    'satuan' => $item['satuan'],
                    'ubs_id' => $pembangunanUnit->perumahaan_id,
                    'rap_bahan_id' => $item['pembangunan_unit_rap_bahan_id'] ?? null,
                    'jumlah_input' => $item['jumlah_input'],
                    'jumlah_base'   => (float)$item['faktor_konversi'] * (float)$item['jumlah_input'],
                    'alasan_permintaan_tidak_sesuai_rap' => $item['alasan'] ?? null,
                ]);
            }

            $this->sendGroupNotificationOrder($pembangunanUnit, $order, 'permintaan');

            DB::commit();
            return response()->json(['message' => 'Permintaan barang berhasil dikirim.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request) {}

    public function storeReturn(Request $request, $orderId)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.detail_id' => 'required|exists:pembangunan_unit_barang_order_detail,id',
            'items.*.jumlah_return' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $order = PembangunanUnitBarangOrder::findOrFail($orderId);
            $hasReturn = false;

            foreach ($request->items as $item) {
                if ($item['jumlah_return'] > 0) {
                    $detail = PembangunanUnitBarangOrderDetail::where('id', $item['detail_id'])
                        ->where('order_id', $orderId)
                        ->firstOrFail();

                    if ($item['jumlah_return'] > $detail->jumlah_input) {
                        return back()->with('error', "Jumlah retur {$detail->nama_barang} melebihi jumlah order.");
                    }

                    $detail->update([
                        'jumlah_return' => $item['jumlah_return'],
                        'keterangan_return' => $item['keterangan_return']
                    ]);

                    $hasReturn = true;
                }
            }

            if ($hasReturn) {
                $order->update([
                    'status_order' => 'pengembalian',
                    'updated_at' => now()
                ]);
            }

            $pembangunanUnit = PembangunanUnit::find($order->pembangunan_unit_id);

            if ($pembangunanUnit) {
                $this->sendGroupNotificationOrder($pembangunanUnit, $order, 'retur');
            }

            DB::commit();
            return back()->with('success', 'Data retur barang berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
