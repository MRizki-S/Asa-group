<?php

namespace App\Http\Controllers\Produksi\PembangunanUnit;

use App\Http\Controllers\Controller;
use App\Models\PembangunanUnit;
use App\Models\PembangunanUnitBarangOrder;
use App\Models\PembangunanUnitBarangOrderDetail;
use App\Models\PembangunanUnitBarangReturn;
use App\Models\PembangunanUnitBarangReturnDetail;
use App\Services\NotificationGroupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PembangunanUnitBarangReturnController extends Controller
{
    protected NotificationGroupService $notificationGroup;

    public function __construct(NotificationGroupService $notificationGroup)
    {
        $this->notificationGroup = $notificationGroup;
    }

    protected function sendGroupNotificationReturn(PembangunanUnit $pembangunanUnit)
    {
        $pembangunanUnit->loadMissing(['unit.tahap.perumahaan', 'pembangunanUnitQc']);

        $unit = $pembangunanUnit->unit;
        $namaPerumahan = $unit->tahap->perumahaan->nama_perumahaan ?? '-';
        $namaTahap = $unit->tahap->nama_tahap ?? '-';
        $namaUnit = $unit->nama_unit ?? '-';
        $pengaju = Auth::user()->nama_lengkap ?? Auth::user()->name;

        $groupId = "ID GRUP GUDANG";

        if (!$groupId) return;

        $messageGroup = "🔄 *PENGAJUAN RETUR BAHAN*\n\n"
            . "Dear *Tim Logistik/Gudang*, terdapat pengajuan pengembalian (retur) bahan material dari lapangan.\n\n"
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

    public function store(Request $request, string $orderId)
    {
        $request->validate([
            'items'                       => 'required|array',
            'items.*.detail_id'           => 'required|exists:pembangunan_unit_barang_order_detail,id',
            'items.*.jumlah_return'       => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $order = PembangunanUnitBarangOrder::findOrFail($orderId);
            $hasReturn = false;

            foreach ($request->items as $item) {
                if ($item['jumlah_return'] > 0) {
                    $detail = PembangunanUnitBarangOrderDetail::where('id', $item['detail_id'])
                        ->where('order_id', $order->id)
                        ->firstOrFail();

                    if ($item['jumlah_return'] > $detail->jumlah_input) {
                        return back()->with('error', "Jumlah retur {$detail->nama_barang} melebihi jumlah order.");
                    }

                    $hasReturn = true;
                }
            }

            if (!$hasReturn) {
                return back()->with('error', 'Tidak ada item retur yang diisi.');
            }

            $return = PembangunanUnitBarangReturn::create([
                'order_id'         => $order->id,
                'status'           => 'diajukan',
                'diajukan_oleh'    => Auth::id(),
                'tanggal_diajukan' => now(),
            ]);

            foreach ($request->items as $item) {
                if ($item['jumlah_return'] > 0) {
                    $detail = PembangunanUnitBarangOrderDetail::where('id', $item['detail_id'])
                        ->where('order_id', $order->id)
                        ->firstOrFail();

                    PembangunanUnitBarangReturnDetail::create([
                        'return_id'         => $return->id,
                        'order_detail_id'   => $detail->id,
                        'barang_id'         => $detail->barang_id,
                        'jumlah_return'     => $item['jumlah_return'],
                        'keterangan_return' => $item['keterangan_return'] ?? null,
                    ]);
                }
            }

            $order->update([
                'status_order' => 'pengembalian',
                'updated_at'   => now(),
            ]);

            $pembangunanUnit = PembangunanUnit::find($order->pembangunan_unit_id);

            if ($pembangunanUnit) {
                $this->sendGroupNotificationReturn($pembangunanUnit);
            }

            DB::commit();
            return back()->with('success', 'Data retur barang berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}

