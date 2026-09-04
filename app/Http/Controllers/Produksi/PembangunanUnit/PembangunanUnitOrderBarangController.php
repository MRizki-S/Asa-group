<?php

namespace App\Http\Controllers\Produksi\PembangunanUnit;

use App\Http\Controllers\Controller;
use App\Models\BarangSatuanKonversi;
use App\Models\MasterBarang;
use App\Models\PembangunanUnit;
use App\Models\PembangunanUnitBarangOrder;
use App\Models\PembangunanUnitBarangOrderDetail;
use App\Models\PembangunanUnitQc;
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

        $groupId = env('FONNTE_ID_ORDER_BARANG_UNIT', env('FONNTE_ID_GROUP_ORDER_BARANG_UNIT', env('FONNTE_ID_ORDER_BARANG_ABM')));

        if (!$groupId) return;

        $messageGroup = view('notifications.whatsapp.pembangunan_unit.order_barang', [
            'tipe' => 'Unit',
            'namaPerumahan' => $namaPerumahan,
            'namaTahap' => $namaTahap,
            'namaUnit' => $namaUnit,
            'namaQc' => $order->qc->nama_qc ?? null,
            'pengaju' => $pengaju,
            'tanggalDiajukan' => ($order->created_at ? \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') : now()->format('d/m/Y H:i')) . ' WIB',
            'tanggalNbk' => ($order->tanggal_diajukan ? \Carbon\Carbon::parse($order->tanggal_diajukan)->format('d/m/Y H:i') : ($order->created_at ? \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') : now()->format('d/m/Y H:i'))) . ' WIB',
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
            'items.*.jumlah_input' => 'required|numeric|min:0.0001',
            'items.*.faktor_konversi' => 'nullable|numeric|min:0.0001',
            'jenis_order' => 'required|string|in:stock,direct'
        ]);

        try {
            DB::beginTransaction();

            $pembangunanUnit = PembangunanUnit::findOrFail($request->pembangunan_unit_id);

            $pembangunanUnitQc = PembangunanUnitQc::where('id', $request->pembangunan_unit_qc_id)
                ->where('pembangunan_unit_id', $pembangunanUnit->id)
                ->first();

            if (!$pembangunanUnitQc) {
                throw new \Exception('QC tidak sesuai dengan pembangunan unit yang dipilih.');
            }

            if (in_array($pembangunanUnit->status_pembangunan, ['selesai', 'selesai dengan catatan']) && !$pembangunanUnitQc->is_servis) {
                throw new \Exception('Unit ini sudah selesai dibangun, tidak dapat melakukan order barang.');
            }

            $datePrefix = 'ORD-UNT-' . now()->format('Ymd') . '-';
            $lastOrder = PembangunanUnitBarangOrder::where('nomor_order', 'like', $datePrefix . '%')
                ->orderBy('nomor_order', 'desc')
                ->lockForUpdate()
                ->first();

            $nextSeq = 1;
            if ($lastOrder) {
                $lastSeq = (int) substr($lastOrder->nomor_order, strlen($datePrefix));
                $nextSeq = $lastSeq + 1;
            }
            $nomorOrder = $datePrefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

            $tglDiajukan = now();
            if ($request->filled('tanggal_order')) {
                $tglStr = $request->tanggal_order;
                if ($request->filled('waktu_order')) {
                    $tglStr .= ' ' . $request->waktu_order;
                }
                try {
                    $tglDiajukan = \Carbon\Carbon::parse($tglStr);
                } catch (\Exception $e) {
                    $tglDiajukan = now();
                }
            }

            $order = PembangunanUnitBarangOrder::create([
                'nomor_order' => $nomorOrder,
                'pembangunan_unit_id' => $request->pembangunan_unit_id,
                'pembangunan_unit_qc_id' => $request->pembangunan_unit_qc_id,
                'jenis_order' => $request->jenis_order,
                'tanggal_diajukan' => $tglDiajukan,
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

                $newQtyBase = $faktorKonversi * (float) $item['jumlah_input'];
                $alasan = $item['alasan'] ?? null;

                if (!empty($item['pembangunan_unit_rap_bahan_id'])) {
                    $rapBahan = \App\Models\PembangunanUnitRapBahan::findOrFail($item['pembangunan_unit_rap_bahan_id']);
                    $alreadyOrderedBase = PembangunanUnitBarangOrderDetail::where('rap_bahan_id', $rapBahan->id)
                        ->whereHas('order', fn($q) => $q->where('status_order', '!=', 'ditolak'))
                        ->sum('jumlah_base');

                    $rapTotalBase = (float) $rapBahan->jumlah_standar * (float) $rapBahan->faktor_konversi;

                    if (($alreadyOrderedBase + $newQtyBase) > ($rapTotalBase + 0.001)) {
                        if (empty($alasan)) {
                            throw new \Exception("Order barang {$barang->nama_barang} melebihi RAP. Harap masukkan alasan melebihi RAP.");
                        }
                    }
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
                    'jumlah_base' => $newQtyBase,
                    'alasan_permintaan_tidak_sesuai_rap' => $alasan,
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

    public function destroy($id)
    {
        $order = PembangunanUnitBarangOrder::with(['details'])->findOrFail($id);

        if ($order->status_order !== 'diproses') {
            return redirect()->back()->with('error', 'Gagal membatalkan order! Order ini sudah tidak dalam status menunggu.');
        }

        try {
            DB::beginTransaction();

            $pembangunanUnit = PembangunanUnit::find($order->pembangunan_unit_id);
            if ($pembangunanUnit) {
                $this->sendGroupNotificationCancelOrder($pembangunanUnit, $order);
            }

            $order->details()->delete();
            $order->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Order barang berhasil dibatalkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function sendGroupNotificationCancelOrder(PembangunanUnit $pembangunanUnit, $order)
    {
        $pembangunanUnit->loadMissing(['unit.tahap.perumahaan']);
        $order->loadMissing(['details']);

        $unit = $pembangunanUnit->unit;
        $namaPerumahan = $unit->tahap->perumahaan->nama_perumahaan ?? '-';
        $namaTahap = $unit->tahap->nama_tahap ?? '-';
        $namaUnit = $unit->nama_unit ?? '-';
        $pembatal = Auth::user()->nama_lengkap ?? Auth::user()->name;

        $groupId = env('FONNTE_ID_GROUP_BATAL_ORDER_BARANG_UNIT');

        if (!$groupId) return;

        $messageGroup = view('notifications.whatsapp.pembangunan_unit.batal_order_barang', [
            'tipe' => 'Unit',
            'namaPerumahan' => $namaPerumahan,
            'namaTahap' => $namaTahap,
            'namaUnit' => $namaUnit,
            'pembatal' => $pembatal,
            'tanggal' => now()->format('d/m/Y H:i') . ' WIB',
            'order' => $order
        ])->render();

        try {
            $this->notificationGroup->send($groupId, $messageGroup);
        } catch (\Exception $e) {
            Log::error('WA Cancel Order Error: ' . $e->getMessage());
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
