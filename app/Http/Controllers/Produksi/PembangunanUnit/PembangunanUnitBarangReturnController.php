<?php

namespace App\Http\Controllers\Produksi\PembangunanUnit;

use App\Http\Controllers\Controller;
use App\Models\BarangSatuanKonversi;
use App\Models\MasterBarang;
use App\Models\MasterSatuan;
use App\Models\PembangunanUnit;
use App\Models\PembangunanUnitBarangOrder;
use App\Models\PembangunanUnitBarangOrderDetail;
use App\Models\PembangunanUnitBarangReturn;
use App\Models\PembangunanUnitBarangReturnDetail;
use App\Models\PembangunanUnitQc;
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

    /**
     * Return aggregated barang data (total ACC'd & already returned) per QC.
     * Used by the front-end modal via AJAX / blade to build the return form.
     */
    public function summary(Request $request, int $qcId)
    {
        $qc = PembangunanUnitQc::findOrFail($qcId);

        // Aggregate all confirmed order-details for this QC grouped STRICTLY by barang_id
        $details = PembangunanUnitBarangOrderDetail::query()
            ->join('pembangunan_unit_barang_order as o', 'o.id', '=', 'pembangunan_unit_barang_order_detail.order_id')
            ->where('o.pembangunan_unit_qc_id', $qcId)
            ->where('o.status_order', 'selesai')
            ->where('pembangunan_unit_barang_order_detail.konfirmasi', true)
            ->select([
                'pembangunan_unit_barang_order_detail.barang_id',
                DB::raw('MAX(pembangunan_unit_barang_order_detail.nama_barang) as nama_barang'),
                DB::raw('SUM(pembangunan_unit_barang_order_detail.jumlah_base) as total_diterima_base'),
            ])
            ->groupBy('pembangunan_unit_barang_order_detail.barang_id')
            ->get();

        // Sum up already-returned base quantities for each barang from this QC's returs
        $returnedBase = PembangunanUnitBarangReturnDetail::query()
            ->join('pembangunan_unit_barang_return as r', 'r.id', '=', 'pembangunan_unit_barang_return_detail.return_id')
            ->where('r.pembangunan_unit_qc_id', $qcId)
            ->whereIn('r.status', ['diajukan', 'diproses', 'selesai'])
            ->select([
                'pembangunan_unit_barang_return_detail.barang_id',
                DB::raw('SUM(pembangunan_unit_barang_return_detail.jumlah_base) as total_returned_base'),
            ])
            ->groupBy('pembangunan_unit_barang_return_detail.barang_id')
            ->pluck('total_returned_base', 'barang_id')
            ->toArray();

        $items = $details->map(function ($d) use ($returnedBase) {
            $totalDiterimaBase = (float) $d->total_diterima_base;
            $sudahReturBase    = (float) ($returnedBase[$d->barang_id] ?? 0);
            $sisaBase           = max(0, $totalDiterimaBase - $sudahReturBase);

            $masterBarang = MasterBarang::with(['baseUnit', 'satuanKonversi.satuan'])->find($d->barang_id);
            $baseSatuanNama = $masterBarang?->baseUnit?->nama ?? 'Unit';
            $baseSatuanId   = $masterBarang?->base_unit_id;

            $satuanOptions = [];
            if ($baseSatuanId) {
                $satuanOptions[] = [
                    'satuan_id'        => $baseSatuanId,
                    'nama_satuan'      => $baseSatuanNama,
                    'konversi_ke_base' => 1.0,
                    'is_base'          => true,
                ];
            }

            if ($masterBarang?->satuanKonversi) {
                foreach ($masterBarang->satuanKonversi as $konv) {
                    if ($konv->satuan_id != $baseSatuanId && $konv->satuan) {
                        $satuanOptions[] = [
                            'satuan_id'        => $konv->satuan_id,
                            'nama_satuan'      => $konv->satuan->nama,
                            'konversi_ke_base' => (float) $konv->konversi_ke_base,
                            'is_base'          => false,
                        ];
                    }
                }
            }

            return [
                'barang_id'           => $d->barang_id,
                'nama_barang'         => $masterBarang->nama_barang ?? $d->nama_barang,
                'base_satuan_id'      => $baseSatuanId,
                'base_satuan_nama'    => $baseSatuanNama,
                'total_diterima_base' => $totalDiterimaBase,
                'sudah_retur_base'    => $sudahReturBase,
                'sisa_retur_base'     => $sisaBase,
                'satuan_options'      => $satuanOptions,
            ];
        })->filter(fn($i) => $i['sisa_retur_base'] > 0.0001)->values();

        return response()->json([
            'qc'    => ['id' => $qc->id, 'nama' => 'Ke - ' . $qc->qc_urutan_ke . ' (' . ($qc->nama_qc ?? $qc->masterQc->nama_qc ?? 'QC') . ')'],
            'items' => $items,
        ]);
    }

    /**
     * Store a new return request per-QC.
     * Only saves header + details. No stock/FIFO changes here.
     */
    public function store(Request $request)
    {
        $unit = \App\Models\PembangunanUnit::findOrFail($request->pembangunan_unit_id);
        $qc = \App\Models\PembangunanUnitQc::find($request->pembangunan_unit_qc_id);

        if (in_array($unit->status_pembangunan, ['selesai', 'selesai dengan catatan']) && (!$qc || !$qc->is_servis)) {
            return response()->json([
                'message' => 'Unit ini sudah selesai dibangun, tidak dapat melakukan retur barang.'
            ], 403);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'pembangunan_unit_id'     => 'required|exists:pembangunan_unit,id',
            'pembangunan_unit_qc_id'  => 'required|exists:pembangunan_unit_qc,id',
            'tanggal_return'          => 'required|date',
            'catatan'                 => 'nullable|string',
            'items'                   => 'required|array|min:1',
            'items.*.barang_id'       => 'required|exists:master_barang,id',
            'items.*.satuan_id'       => 'required|exists:master_satuan,id',
            'items.*.jumlah_input'    => 'required|numeric|min:0.001',
        ]);

        if ($validator->fails()) {
            \Illuminate\Support\Facades\Log::error('Return Store Validation Failed: ', $validator->errors()->toArray());
            $firstError = collect($validator->errors()->all())->first();
            return response()->json([
                'message' => 'Validasi Data Gagal: ' . $firstError
            ], 422);
        }

        $qcId = $request->pembangunan_unit_qc_id;

        // Build per-barang sisa map (base unit)
        $details = PembangunanUnitBarangOrderDetail::query()
            ->join('pembangunan_unit_barang_order as o', 'o.id', '=', 'pembangunan_unit_barang_order_detail.order_id')
            ->where('o.pembangunan_unit_qc_id', $qcId)
            ->where('o.status_order', 'selesai')
            ->where('pembangunan_unit_barang_order_detail.konfirmasi', true)
            ->select([
                'pembangunan_unit_barang_order_detail.barang_id',
                DB::raw('SUM(pembangunan_unit_barang_order_detail.jumlah_base) as total_base'),
            ])
            ->groupBy('pembangunan_unit_barang_order_detail.barang_id')
            ->pluck('total_base', 'barang_id')
            ->toArray();

        $returnedBase = PembangunanUnitBarangReturnDetail::query()
            ->join('pembangunan_unit_barang_return as r', 'r.id', '=', 'pembangunan_unit_barang_return_detail.return_id')
            ->where('r.pembangunan_unit_qc_id', $qcId)
            ->whereIn('r.status', ['diproses', 'selesai'])
            ->select([
                'pembangunan_unit_barang_return_detail.barang_id',
                DB::raw('SUM(pembangunan_unit_barang_return_detail.jumlah_base) as total_returned_base'),
            ])
            ->groupBy('pembangunan_unit_barang_return_detail.barang_id')
            ->pluck('total_returned_base', 'barang_id')
            ->toArray();

        try {
            return DB::transaction(function () use ($request, $qcId, $details, $returnedBase) {
                // Generate Nomor Return
                $datePrefix = 'RTN-UNT-' . now()->format('Ymd') . '-';
                $lastReturn = PembangunanUnitBarangReturn::where('nomor_return', 'like', $datePrefix . '%')
                    ->orderBy('nomor_return', 'desc')
                    ->lockForUpdate()
                    ->first();

                $nextSeq = 1;
                if ($lastReturn) {
                    $lastSeq = (int) substr($lastReturn->nomor_return, strlen($datePrefix));
                    $nextSeq = $lastSeq + 1;
                }
                $nomorReturn = $datePrefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

                $tanggalReturn = $request->tanggal_return;
                if ($tanggalReturn && strlen($tanggalReturn) <= 10) {
                    $tanggalReturn = $tanggalReturn . ' ' . now()->format('H:i:s');
                } else if (!$tanggalReturn) {
                    $tanggalReturn = now();
                }

                // Create Return Header
                $return = PembangunanUnitBarangReturn::create([
                    'nomor_return'           => $nomorReturn,
                    'pembangunan_unit_id'    => $request->pembangunan_unit_id,
                    'pembangunan_unit_qc_id' => $qcId,
                    'tanggal_return'         => $tanggalReturn,
                    'catatan'                => $request->catatan,
                    'status'                 => 'diproses',
                    'created_by'             => Auth::id(),
                ]);

                foreach ($request->items as $item) {
                    $barangId     = $item['barang_id'];
                    $satuanId     = $item['satuan_id'];
                    $jumlahInput  = (float) $item['jumlah_input'];

                    // Lookup Master Data (Never trust request for names)
                    $masterBarang = MasterBarang::find($barangId);
                    $masterSatuan = MasterSatuan::find($satuanId);
                    if (!$masterBarang || !$masterSatuan) {
                        throw new \Exception("Data barang atau satuan tidak ditemukan for ID: {$barangId}.");
                    }

                    // Resolve faktor konversi
                    $faktor = BarangSatuanKonversi::where('barang_id', $barangId)
                        ->where('satuan_id', $satuanId)
                        ->value('konversi_ke_base') ?? 1;

                    $jumlahBase = round($jumlahInput * $faktor, 4);

                    // Validate does not exceed sisa (barang pernah keluar)
                    $totalOrdered = (float)($details[$barangId] ?? 0);
                    if ($totalOrdered <= 0.0001) {
                         throw new \Exception("Barang {$masterBarang->nama_barang} tidak pernah keluar atau belum dikonfirmasi selesai pada QC ini.");
                    }

                    $sisaBase = max(0, $totalOrdered - (float)($returnedBase[$barangId] ?? 0));
                    if ($jumlahBase > $sisaBase + 0.0001) {
                        throw new \Exception("Jumlah return melebihi sisa yang dapat dikembalikan untuk barang {$masterBarang->nama_barang}.");
                    }

                    PembangunanUnitBarangReturnDetail::create([
                        'return_id'             => $return->id,
                        'barang_id'             => $barangId,
                        'nama_barang'           => $masterBarang->nama_barang,
                        'satuan_id'             => $satuanId,
                        'satuan'                => $masterSatuan->nama,
                        'jumlah_input'          => $jumlahInput,
                        'jumlah_base'           => $jumlahBase,
                        'jumlah_layak_base'     => 0,
                        'jumlah_rusak_base'     => 0,
                        'harga_satuan_snapshot' => 0,
                        'harga_total_snapshot'  => 0,
                        'keterangan'            => $item['keterangan'] ?? null,
                    ]);
                }

                // WA notification via After Commit
                DB::afterCommit(function () use ($request, $return) {
                    $pembangunanUnit = PembangunanUnit::find($request->pembangunan_unit_id);
                    if ($pembangunanUnit) {
                        $this->sendGroupNotificationReturn($pembangunanUnit, $return);
                    }
                });

                return response()->json(['message' => 'Pengajuan return barang berhasil disimpan.'], 200);
            });
        } catch (\Exception $e) {
            Log::error('Return Store Error: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 422); // Using 422 if it's validation logic exception
        }
    }

    protected function sendGroupNotificationReturn(PembangunanUnit $pembangunanUnit, $return)
    {
        $pembangunanUnit->loadMissing(['unit.tahap.perumahaan']);

        $unit         = $pembangunanUnit->unit;
        $namaPerumahan = $unit->tahap->perumahaan->nama_perumahaan ?? '-';
        $namaTahap    = $unit->tahap->nama_tahap ?? '-';
        $namaUnit     = $unit->nama_unit ?? '-';
        $pengaju      = Auth::user()->nama_lengkap ?? Auth::user()->name;
        $groupId      = env('FONNTE_ID_RETURN_BARANG_UNIT', env('FONNTE_ID_GROUP_RETUR_BARANG_UNIT', env('FONNTE_ID_ORDER_BARANG_ABM')));

        if (!$groupId) return;

        $messageGroup = view('notifications.whatsapp.pembangunan_unit.retur_barang', [
            'tipe'          => 'Unit',
            'namaPerumahan' => $namaPerumahan,
            'namaTahap'     => $namaTahap,
            'namaUnit'      => $namaUnit,
            'namaQc'        => $return->qc->nama_qc ?? null,
            'pengaju'       => $pengaju,
            'tanggal'       => now()->format('d/m/Y H:i') . ' WIB',
            'return'        => $return,
        ])->render();

        try {
            $this->notificationGroup->send($groupId, $messageGroup);
        } catch (\Exception $e) {
            Log::error('WA Return Error: ' . $e->getMessage());
        }
    }
}
