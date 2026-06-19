<?php

namespace App\Http\Controllers\Produksi\PembangunanUnit;

use App\Http\Controllers\Controller;
use App\Models\BarangSatuanKonversi;
use App\Models\MasterBarang;
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

    public function sendGroupNotificationOrder(PembangunanUnit $pembangunanUnit, $order)
    {
        $pembangunanUnit->loadMissing(['unit.tahap.perumahaan', 'pembangunanUnitQc']);
        $order->loadMissing(['details']);

        $unit = $pembangunanUnit->unit;
        $namaPerumahan = $unit->tahap->perumahaan->nama_perumahaan ?? '-';
        $namaTahap = $unit->tahap->nama_tahap ?? '-';
        $namaUnit = $unit->nama_unit ?? '-';
        $pengaju = Auth::user()->nama_lengkap ?? Auth::user()->name;

        $groupId = env('FONNTE_ID_GROUP_ORDER_BARANG_UNIT');

        if (!$groupId) return;

        $messageGroup = view('notifications.whatsapp.order_barang', [
            'tipe' => 'Unit',
            'namaPerumahan' => $namaPerumahan,
            'namaTahap' => $namaTahap,
            'namaUnit' => $namaUnit,
            'pengaju' => $pengaju,
            'tanggal' => now()->format('d/m/Y H:i') . ' WIB',
            'order' => $order
        ])->render();

        try {
            $this->notificationGroup->send($groupId, $messageGroup);
        } catch (\Exception $e) {
            Log::error('WA Error: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'pembangunan_unit_id' => 'required|exists:pembangunan_unit,id',
            'pembangunan_unit_qc_id' => 'required|exists:pembangunan_unit_qc,id',
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:master_barang,id',
            'items.*.nama_barang' => 'required',
            'items.*.satuan_id' => 'required|exists:master_satuan,id',
            'items.*.satuan' => 'required',
            'items.*.jumlah_input' => 'required|numeric|min:0.001',
            'items.*.faktor_konversi' => 'nullable|numeric|min:0.001',
            'jenis_order' => 'required|string|in:stock,direct'
        ]);

        try {
            DB::beginTransaction();

            $pembangunanUnit = PembangunanUnit::findOrFail($request->pembangunan_unit_id);

            $qcBelongsToUnit = DB::table('pembangunan_unit_qc')
                ->where('id', $request->pembangunan_unit_qc_id)
                ->where('pembangunan_unit_id', $pembangunanUnit->id)
                ->exists();

            if (!$qcBelongsToUnit) {
                throw new \Exception('QC tidak sesuai dengan pembangunan unit yang dipilih.');
            }

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
                $barang = MasterBarang::findOrFail($item['barang_id']);
                $expectedStock = $request->jenis_order === 'stock';

                if ((bool) $barang->is_stock !== $expectedStock) {
                    $jenis = $expectedStock ? 'stock' : 'direct';
                    throw new \Exception("Barang {$barang->nama_barang} bukan tipe {$jenis}.");
                }

                // Backend menjadi sumber kebenaran untuk konversi satuan.
                // Jangan percaya faktor dari frontend karena bisa stale saat data master satuan berubah.
                $faktorKonversi = BarangSatuanKonversi::where('barang_id', $item['barang_id'])
                    ->where('satuan_id', $item['satuan_id'])
                    ->value('konversi_ke_base');

                // Fallback ini menjaga barang lama/direct yang belum punya baris konversi tetap bisa order.
                // Namun jika konversi tersedia di master, nilai master selalu menang.
                $faktorKonversi = (float) ($faktorKonversi ?? ($item['faktor_konversi'] ?? 1));

                if ($faktorKonversi <= 0) {
                    throw new \Exception("Konversi satuan untuk {$item['nama_barang']} tidak valid.");
                }

                PembangunanUnitBarangOrderDetail::create([
                    'order_id' => $order->id,
                    'barang_id' => $item['barang_id'],
                    'nama_barang' => $barang->nama_barang,
                    'satuan_id' => $item['satuan_id'],
                    'satuan' => $item['satuan'],
                    'ubs_id' => $pembangunanUnit->perumahaan_id,
                    'rap_bahan_id' => $item['pembangunan_unit_rap_bahan_id'] ?? null,
                    'jumlah_input' => $item['jumlah_input'],
                    'jumlah_base' => $faktorKonversi * (float) $item['jumlah_input'],
                    'alasan_permintaan_tidak_sesuai_rap' => $item['alasan'] ?? null,
                ]);
            }

            $this->sendGroupNotificationOrder($pembangunanUnit, $order);

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
