<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\BarangRusak;
use App\Models\MasterBarang;
use App\Models\NotaBarangMasuk;
use App\Models\PembangunanKawasanBarangOrder;
use App\Models\PembangunanKawasanBarangReturn;
use App\Models\PembangunanLainBarangOrder;
use App\Models\PembangunanLainBarangReturn;
use App\Models\PembangunanProyekBarangOrder;
use App\Models\PembangunanProyekBarangReturn;
use App\Models\PembangunanUnitBarangOrder;
use App\Models\PembangunanUnitBarangReturn;
use App\Models\ProduksiBarangRakitan;
use App\Models\StockGudang;
use App\Models\StockLedger;
use App\Models\TransferStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RekapNotaMasukController extends Controller
{
    public function index(Request $request)
    {
        $bulan = (int) $request->get('bulan', now()->month);
        $tahun = (int) $request->get('tahun', now()->year);

        $daysInMonth = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->daysInMonth;
        $startOfMonth = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->startOfDay();

        $allUbs = \App\Models\Ubs::orderBy('id', 'asc')->get();
        $selectedUbsId = $request->get('ubs_id', '1');

        // Fetch real-time warehouse stock from stock_gudang table for Stok Akhir (Gudang)
        $stokGudangQuery = StockGudang::query();
        if ($selectedUbsId !== 'all') {
            $stokGudangQuery->where('ubs_id', $selectedUbsId);
        }
        $stokGudangMap = $stokGudangQuery
            ->groupBy('barang_id')
            ->select('barang_id', DB::raw('SUM(jumlah_stock) as total_gudang'))
            ->pluck('total_gudang', 'barang_id')
            ->toArray();

        // 1. Calculate Stok Awal (Opening Balance) prior to startOfMonth
        $priorLedgersQuery = StockLedger::query()
            ->join('master_barang as b', 'b.id', '=', 'stock_ledger.barang_id')
            ->select([
                'b.id as barang_id',
                'stock_ledger.tanggal',
                'stock_ledger.ref_type',
                'stock_ledger.ref_id',
                'stock_ledger.qty_masuk',
                'stock_ledger.qty_keluar',
                'stock_ledger.stock_type',
                'stock_ledger.ubs_id',
            ])
            ->where('stock_ledger.tanggal', '<', $startOfMonth);

        if ($selectedUbsId !== 'all') {
            $priorLedgersQuery->where('stock_ledger.ubs_id', $selectedUbsId);
        }
        $priorLedgers = $priorLedgersQuery->get();

        $priorUnitOrderIds = [];
        $priorKawasanOrderIds = [];
        $priorProyekOrderIds = [];
        $priorLainOrderIds = [];

        foreach ($priorLedgers as $l) {
            $norm = class_basename($l->ref_type);
            if ($norm === 'PembangunanUnitBarangOrder')
                $priorUnitOrderIds[] = $l->ref_id;
            elseif ($norm === 'PembangunanKawasanBarangOrder')
                $priorKawasanOrderIds[] = $l->ref_id;
            elseif ($norm === 'PembangunanProyekBarangOrder')
                $priorProyekOrderIds[] = $l->ref_id;
            elseif ($norm === 'PembangunanLainBarangOrder')
                $priorLainOrderIds[] = $l->ref_id;
        }

        $priorUnitOrders = PembangunanUnitBarangOrder::whereIn('id', array_unique($priorUnitOrderIds))->get(['id', 'tanggal_diajukan', 'status_order'])->keyBy('id');
        $priorKawasanOrders = PembangunanKawasanBarangOrder::whereIn('id', array_unique($priorKawasanOrderIds))->get(['id', 'tanggal_diajukan', 'status_order'])->keyBy('id');
        $priorProyekOrders = PembangunanProyekBarangOrder::whereIn('id', array_unique($priorProyekOrderIds))->get(['id', 'tanggal_diajukan', 'status_order'])->keyBy('id');
        $priorLainOrders = PembangunanLainBarangOrder::whereIn('id', array_unique($priorLainOrderIds))->get(['id', 'tanggal_diajukan', 'status_order'])->keyBy('id');

        $stokAwalMap = [];
        foreach ($priorLedgers as $l) {
            $norm = class_basename($l->ref_type);
            $txDate = \Carbon\Carbon::parse($l->tanggal);

            if (in_array($norm, ['PembangunanUnitBarangOrder', 'PembangunanKawasanBarangOrder', 'PembangunanProyekBarangOrder', 'PembangunanLainBarangOrder'])) {
                $refObj = match ($norm) {
                    'PembangunanUnitBarangOrder' => $priorUnitOrders[$l->ref_id] ?? null,
                    'PembangunanKawasanBarangOrder' => $priorKawasanOrders[$l->ref_id] ?? null,
                    'PembangunanProyekBarangOrder' => $priorProyekOrders[$l->ref_id] ?? null,
                    'PembangunanLainBarangOrder' => $priorLainOrders[$l->ref_id] ?? null,
                };
                if ($refObj && strtolower($refObj->status_order ?? '') === 'ditolak')
                    continue;
                if ($refObj && $refObj->tanggal_diajukan) {
                    $txDate = \Carbon\Carbon::parse($refObj->tanggal_diajukan);
                }
            }

            if ($txDate >= $startOfMonth)
                continue;

            $bId = $l->barang_id;
            if (!isset($stokAwalMap[$bId]))
                $stokAwalMap[$bId] = 0;
            $stokAwalMap[$bId] += ((float) $l->qty_masuk - (float) $l->qty_keluar);
        }

        // 2. Fetch current ledgers for target period processing
        $ledgersQuery = StockLedger::query()
            ->join('master_barang as b', 'b.id', '=', 'stock_ledger.barang_id')
            ->leftJoin('master_satuan as s', 's.id', '=', 'b.base_unit_id')
            ->leftJoin('ubs as u', 'u.id', '=', 'stock_ledger.ubs_id')
            ->select([
                'b.id as barang_id',
                'b.kode_barang',
                'b.nama_barang',
                's.nama as nama_satuan',
                'stock_ledger.tanggal',
                'stock_ledger.stock_type',
                'stock_ledger.ref_type',
                'stock_ledger.ref_id',
                'stock_ledger.qty_masuk',
                'stock_ledger.qty_keluar',
                'stock_ledger.tipe',
                'stock_ledger.ubs_id',
                'u.nama_ubs',
            ])
            ->orderBy('stock_ledger.tanggal', 'asc');

        if ($selectedUbsId !== 'all') {
            $ledgersQuery->where('stock_ledger.ubs_id', $selectedUbsId);
        }
        $ledgers = $ledgersQuery->get();

        // Batch resolve document numbers, tanggal_diajukan, and status_order
        $refMap = [];
        $groupedRefs = $ledgers->groupBy('ref_type');
        foreach ($groupedRefs as $refType => $items) {
            $ids = $items->pluck('ref_id')->filter()->unique()->values()->all();
            if (empty($ids))
                continue;
            $normalized = class_basename($refType);
            switch ($normalized) {
                case 'NotaBarangMasuk':
                    $refMap[$refType] = NotaBarangMasuk::whereIn('id', $ids)->get(['id', 'nomor_nota'])->keyBy('id');
                    break;
                case 'PembangunanUnitBarangOrder':
                    $refMap[$refType] = PembangunanUnitBarangOrder::with(['pembangunanUnit.unit', 'pembangunanUnit.perumahaan'])
                        ->whereIn('id', $ids)
                        ->get(['id', 'nomor_order', 'nomor_nbk', 'tanggal_diajukan', 'status_order', 'pembangunan_unit_id'])
                        ->keyBy('id');
                    break;
                case 'PembangunanKawasanBarangOrder':
                    $refMap[$refType] = PembangunanKawasanBarangOrder::with(['kawasan.perumahan'])
                        ->whereIn('id', $ids)
                        ->get(['id', 'nomor_order', 'nomor_nbk', 'tanggal_diajukan', 'status_order', 'pembangunan_kawasan_id'])
                        ->keyBy('id');
                    break;
                case 'PembangunanProyekBarangOrder':
                    $refMap[$refType] = PembangunanProyekBarangOrder::with(['proyek'])
                        ->whereIn('id', $ids)
                        ->get(['id', 'nomor_order', 'nomor_nbk', 'tanggal_diajukan', 'status_order', 'pembangunan_proyek_id'])
                        ->keyBy('id');
                    break;
                case 'PembangunanLainBarangOrder':
                    $refMap[$refType] = PembangunanLainBarangOrder::with(['pembangunanLain', 'sub'])
                        ->whereIn('id', $ids)
                        ->get(['id', 'nomor_order', 'nomor_nbk', 'tanggal_diajukan', 'status_order', 'pembangunan_lain_id', 'pembangunan_lain_sub_id'])
                        ->keyBy('id');
                    break;
                case 'TransferStock':
                    $refMap[$refType] = TransferStock::with(['fromUbs', 'toUbs'])
                        ->whereIn('id', $ids)
                        ->get(['id', 'nomor_transfer', 'dari_stock_type', 'dari_ubs_id', 'ke_stock_type', 'ke_ubs_id'])
                        ->keyBy('id');
                    break;
                case 'ProduksiBarangRakitan':
                    $refMap[$refType] = ProduksiBarangRakitan::whereIn('id', $ids)->get(['id', 'nomor_rakitan'])->keyBy('id');
                    break;
                case 'BarangRusak':
                    $refMap[$refType] = BarangRusak::whereIn('id', $ids)->get(['id', 'nomor_barang_rusak'])->keyBy('id');
                    break;
                case 'PembangunanUnitBarangReturn':
                    $refMap[$refType] = PembangunanUnitBarangReturn::with(['pembangunanUnit.unit', 'pembangunanUnit.perumahaan'])
                        ->whereIn('id', $ids)
                        ->get(['id', 'nomor_return', 'tanggal_return', 'pembangunan_unit_id'])
                        ->keyBy('id');
                    break;
                case 'PembangunanKawasanBarangReturn':
                    $refMap[$refType] = PembangunanKawasanBarangReturn::with(['kawasan.perumahan'])
                        ->whereIn('id', $ids)
                        ->get(['id', 'nomor_return', 'tanggal_return', 'pembangunan_kawasan_id'])
                        ->keyBy('id');
                    break;
                case 'PembangunanProyekBarangReturn':
                    $refMap[$refType] = PembangunanProyekBarangReturn::with(['proyek'])
                        ->whereIn('id', $ids)
                        ->get(['id', 'nomor_return', 'tanggal_return', 'pembangunan_proyek_id'])
                        ->keyBy('id');
                    break;
                case 'PembangunanLainBarangReturn':
                    $refMap[$refType] = PembangunanLainBarangReturn::with(['pembangunanLain', 'sub'])
                        ->whereIn('id', $ids)
                        ->get(['id', 'nomor_return', 'tanggal_return', 'pembangunan_lain_id', 'pembangunan_lain_sub_id'])
                        ->keyBy('id');
                    break;
            }
        }

        $rekapMatrix = [];
        foreach ($ledgers as $row) {
            $refBasename = class_basename($row->ref_type);
            $refObj = $refMap[$row->ref_type][$row->ref_id] ?? null;
            $isReturn = in_array($refBasename, ['PembangunanUnitBarangReturn', 'PembangunanKawasanBarangReturn', 'PembangunanProyekBarangReturn', 'PembangunanLainBarangReturn']);

            $txDate = \Carbon\Carbon::parse($row->tanggal);
            if (in_array($refBasename, ['PembangunanUnitBarangOrder', 'PembangunanKawasanBarangOrder', 'PembangunanProyekBarangOrder', 'PembangunanLainBarangOrder'])) {
                if ($refObj && strtolower($refObj->status_order ?? '') === 'ditolak') {
                    continue;
                }
                if ($refObj && $refObj->tanggal_diajukan) {
                    $txDate = \Carbon\Carbon::parse($refObj->tanggal_diajukan);
                }
            } elseif ($isReturn && $refObj && $refObj->tanggal_return) {
                $txDate = \Carbon\Carbon::parse($refObj->tanggal_return);
            }

            if ((int) $txDate->month !== $bulan || (int) $txDate->year !== $tahun) {
                continue;
            }

            $day = (int) $txDate->day;
            $qtyMasuk = (float) $row->qty_masuk;
            $qtyKeluar = (float) $row->qty_keluar;

            $bId = $row->barang_id;
            if (!isset($rekapMatrix[$bId])) {
                $stokAwal = (float) ($stokAwalMap[$bId] ?? 0);
                $stokRealtimeGudang = (float) ($stokGudangMap[$bId] ?? 0);
                $rekapMatrix[$bId] = [
                    'kode_barang' => $row->kode_barang,
                    'nama_barang' => $row->nama_barang,
                    'nama_satuan' => $row->nama_satuan ?? '-',
                    'stok_awal' => $stokAwal,
                    'masuk' => array_fill(1, $daysInMonth, 0),
                    'keluar' => array_fill(1, $daysInMonth, 0),
                    'return' => array_fill(1, $daysInMonth, 0),
                    'transactions' => array_fill(1, $daysInMonth, []),
                    'total_masuk' => 0,
                    'total_keluar' => 0,
                    'total_return' => 0,
                    'stok_gudang' => $stokRealtimeGudang,
                    'stok_akhir' => $stokRealtimeGudang,
                ];
            }

            if ($day >= 1 && $day <= $daysInMonth) {
                if ($isReturn) {
                    $rekapMatrix[$bId]['return'][$day] += $qtyMasuk;
                    $rekapMatrix[$bId]['total_return'] += $qtyMasuk;
                } elseif ($qtyMasuk > 0) {
                    $rekapMatrix[$bId]['masuk'][$day] += $qtyMasuk;
                    $rekapMatrix[$bId]['total_masuk'] += $qtyMasuk;
                } else {
                    $rekapMatrix[$bId]['keluar'][$day] += $qtyKeluar;
                    $rekapMatrix[$bId]['total_keluar'] += $qtyKeluar;
                }

                $nbkNumber = null;
                if (in_array($refBasename, ['PembangunanUnitBarangOrder', 'PembangunanKawasanBarangOrder', 'PembangunanProyekBarangOrder', 'PembangunanLainBarangOrder'])) {
                    $nbkNumber = $refObj->nomor_nbk ?? null;
                }

                $docNumber = match ($refBasename) {
                    'NotaBarangMasuk' => $refObj->nomor_nota ?? ('Nota #' . $row->ref_id),
                    'PembangunanUnitBarangOrder', 'PembangunanKawasanBarangOrder', 'PembangunanProyekBarangOrder', 'PembangunanLainBarangOrder' => $refObj->nomor_order ?? ('Order #' . $row->ref_id),
                    'PembangunanUnitBarangReturn', 'PembangunanKawasanBarangReturn', 'PembangunanProyekBarangReturn', 'PembangunanLainBarangReturn' => $refObj->nomor_return ?? ('Retur #' . $row->ref_id),
                    'TransferStock' => $refObj->nomor_transfer ?? ('Transfer #' . $row->ref_id),
                    'ProduksiBarangRakitan' => $refObj->nomor_rakitan ?? ('Rakitan #' . $row->ref_id),
                    'BarangRusak' => $refObj->nomor_barang_rusak ?? ('Rusak #' . $row->ref_id),
                    'PenyesuaianStock' => 'Penyesuaian Stok (Opname)',
                    default => $row->ref_id ? ('#' . $row->ref_id) : 'Penyesuaian Stok',
                };

                $jenisLabel = match ($refBasename) {
                    'NotaBarangMasuk' => 'Nota Masuk',
                    'PembangunanUnitBarangOrder' => 'Order Unit',
                    'PembangunanKawasanBarangOrder' => 'Order Kawasan',
                    'PembangunanProyekBarangOrder' => 'Order Proyek',
                    'PembangunanLainBarangOrder' => 'Order Lain-lain',
                    'PembangunanUnitBarangReturn' => 'Retur Unit',
                    'PembangunanKawasanBarangReturn' => 'Retur Kawasan',
                    'PembangunanProyekBarangReturn' => 'Retur Proyek',
                    'PembangunanLainBarangReturn' => 'Retur Lain-lain',
                    'ProduksiBarangRakitan' => $qtyMasuk > 0 ? 'Produksi Rakitan (Hasil Jadi)' : 'Produksi Rakitan (Bahan Baku)',
                    'TransferStock' => 'Transfer Stok',
                    'BarangRusak' => 'Barang Rusak',
                    'PenyesuaianStock' => $qtyMasuk > 0 ? 'Penyesuaian Stok (+)' : 'Penyesuaian Stok (-)',
                    default => ucfirst($row->tipe ?? 'Mutasi'),
                };

                $tipeTx = $isReturn ? 'return' : ($qtyMasuk > 0 ? 'masuk' : 'keluar');
                $qtyTx = $isReturn ? $qtyMasuk : ($qtyMasuk > 0 ? $qtyMasuk : $qtyKeluar);

                $targetUrl = match ($refBasename) {
                    'NotaBarangMasuk' => isset($refObj->nomor_nota)
                        ? route('gudang.daftarNotaMasuk.show', $refObj->nomor_nota)
                        : null,

                    'PembangunanUnitBarangOrder' => route('gudang.permintaanBarang.show', [
                        'id' => $row->ref_id,
                        'jenis_order' => 'pembangunan_unit',
                    ]),

                    'PembangunanKawasanBarangOrder' => route('gudang.permintaanBarang.show', [
                        'id' => $row->ref_id,
                        'jenis_order' => 'pembangunan_kawasan',
                    ]),

                    'PembangunanProyekBarangOrder' => route('gudang.permintaanBarang.show', [
                        'id' => $row->ref_id,
                        'jenis_order' => 'pembangunan_proyek',
                    ]),

                    'PembangunanLainBarangOrder' => route('gudang.permintaanBarang.show', [
                        'id' => $row->ref_id,
                        'jenis_order' => 'pembangunan_lain',
                    ]),

                    'TransferStock' => isset($refObj->nomor_transfer)
                        ? route('gudang.transferStockBarang.daftar.show', $refObj->nomor_transfer)
                        : null,

                    'PembangunanUnitBarangReturn' => route('gudang.returnBarang.unit.show', $row->ref_id),

                    'PembangunanKawasanBarangReturn' => route('gudang.returnBarang.kawasan.show', $row->ref_id),

                    'PembangunanProyekBarangReturn' => route('gudang.returnBarang.proyek.show', $row->ref_id),

                    'PembangunanLainBarangReturn' => route('gudang.returnBarang.pembangunanLain.show', $row->ref_id),

                    'BarangRusak' => isset($refObj->nomor_barang_rusak)
                        ? route('gudang.barangRusak.show', $refObj->nomor_barang_rusak)
                        : null,

                    'ProduksiBarangRakitan' => route('gudang.produksiRakitan.show', $row->ref_id),

                    default => null,
                };

                $lokasi = match ($refBasename) {
                    'TransferStock' => (function () use ($refObj, $row) {
                        if (!$refObj) {
                            return $row->stock_type === 'HUB' ? 'HUB (Pusat)' : ($row->nama_ubs ?? 'UBS');
                        }
                        $dari = $refObj->dari_stock_type === 'HUB' ? 'HUB' : ($refObj->fromUbs?->nama_ubs ?? 'UBS');
                        $ke = $refObj->ke_stock_type === 'HUB' ? 'HUB' : ($refObj->toUbs?->nama_ubs ?? 'UBS');
                        return "{$dari} -> {$ke}";
                    })(),
                    'PembangunanUnitBarangOrder', 'PembangunanUnitBarangReturn' => (function () use ($refObj, $row) {
                        $unitName = $refObj?->pembangunanUnit?->unit?->nama_unit;
                        $perumName = $refObj?->pembangunanUnit?->perumahaan?->nama_perumahaan ?? $row->nama_ubs ?? 'UBS';
                        if ($unitName) {
                            $prefix = str_starts_with(strtolower($unitName), 'unit') ? '' : 'Unit ';
                            return "{$prefix}{$unitName} ({$perumName})";
                        }
                        return $row->stock_type === 'HUB' ? 'HUB (Pusat)' : ($row->nama_ubs ?? 'UBS');
                    })(),
                    'PembangunanKawasanBarangOrder', 'PembangunanKawasanBarangReturn' => (function () use ($refObj, $row) {
                        $kwsName = $refObj?->kawasan?->nama;
                        $perumName = $refObj?->kawasan?->perumahan?->nama_perumahaan ?? $row->nama_ubs ?? 'UBS';
                        if ($kwsName) {
                            return "Kws. {$kwsName} ({$perumName})";
                        }
                        return $row->stock_type === 'HUB' ? 'HUB (Pusat)' : ($row->nama_ubs ?? 'UBS');
                    })(),
                    'PembangunanProyekBarangOrder', 'PembangunanProyekBarangReturn' => (function () use ($refObj, $row) {
                        $proyekName = $refObj?->proyek?->nama;
                        $ubsName = $row->nama_ubs ?? 'UBS';
                        if ($proyekName) {
                            return "Proyek {$proyekName} ({$ubsName})";
                        }
                        return $row->stock_type === 'HUB' ? 'HUB (Pusat)' : ($row->nama_ubs ?? 'UBS');
                    })(),
                    'PembangunanLainBarangOrder', 'PembangunanLainBarangReturn' => (function () use ($refObj, $row) {
                        $proyekName = $refObj?->pembangunanLain?->nama_proyek;
                        $subName = $refObj?->sub?->nama_sub;
                        $ubsName = $row->nama_ubs ?? 'UBS';
                        if ($proyekName) {
                            $subText = $subName ? " - {$subName}" : '';
                            return "Lain: {$proyekName}{$subText} ({$ubsName})";
                        }
                        return $row->stock_type === 'HUB' ? 'HUB (Pusat)' : ($row->nama_ubs ?? 'UBS');
                    })(),
                    default => $row->stock_type === 'HUB' ? 'HUB (Pusat)' : ($row->nama_ubs ?? 'UBS'),
                };

                $rekapMatrix[$bId]['transactions'][$day][] = [
                    'doc_number' => $docNumber,
                    'nbk_number' => $nbkNumber,
                    'jenis_label' => $jenisLabel,
                    'tipe' => $tipeTx,
                    'qty' => $qtyTx,
                    'lokasi' => $lokasi,
                    'tanggal' => $txDate->format('d-m-Y H:i'),
                    'satuan' => $row->nama_satuan ?? '-',
                    'url' => $targetUrl,
                ];
            }
        }

        // Include master barang that have non-zero stok_awal or non-zero stock_gudang
        $priorItemIds = array_keys(array_filter($stokAwalMap, fn($v) => $v != 0));
        $gudangItemIds = array_keys(array_filter($stokGudangMap, fn($v) => $v != 0));
        $extraItemIds = array_diff(array_unique(array_merge($priorItemIds, $gudangItemIds)), array_keys($rekapMatrix));

        if (!empty($extraItemIds)) {
            $extraItems = MasterBarang::with('baseUnit')->whereIn('id', $extraItemIds)->get();
            foreach ($extraItems as $b) {
                $bId = $b->id;
                $stokAwal = (float) ($stokAwalMap[$bId] ?? 0);
                $stokRealtimeGudang = (float) ($stokGudangMap[$bId] ?? 0);
                $rekapMatrix[$bId] = [
                    'kode_barang' => $b->kode_barang,
                    'nama_barang' => $b->nama_barang,
                    'nama_satuan' => $b->baseUnit->nama ?? '-',
                    'stok_awal' => $stokAwal,
                    'masuk' => array_fill(1, $daysInMonth, 0),
                    'keluar' => array_fill(1, $daysInMonth, 0),
                    'return' => array_fill(1, $daysInMonth, 0),
                    'transactions' => array_fill(1, $daysInMonth, []),
                    'total_masuk' => 0,
                    'total_keluar' => 0,
                    'total_return' => 0,
                    'stok_gudang' => $stokRealtimeGudang,
                    'stok_akhir' => $stokRealtimeGudang,
                ];
            }
        }

        // Calculate Stok Akhir = Stock Gudang + Total Return for each item
        foreach ($rekapMatrix as $bId => &$item) {
            $item['stok_akhir'] = (float) $item['stok_gudang'] + (float) $item['total_return'];
        }
        unset($item);

        // Sort by kode_barang ascending
        usort($rekapMatrix, fn($a, $b) => strnatcasecmp($a['kode_barang'], $b['kode_barang']));

        $hanyaTransaksi = $request->boolean('hanya_transaksi');
        if ($hanyaTransaksi) {
            $rekapMatrix = array_values(array_filter($rekapMatrix, function ($item) {
                return $item['total_masuk'] > 0 || $item['total_keluar'] > 0 || $item['total_return'] > 0;
            }));
        }

        $stats = [
            'total_masuk' => array_sum(array_column($rekapMatrix, 'total_masuk')),
            'total_keluar' => array_sum(array_column($rekapMatrix, 'total_keluar')),
            'total_return' => array_sum(array_column($rekapMatrix, 'total_return')),
        ];

        return view('gudang.rekap-nota-masuk.index', [
            'rekapMatrix' => $rekapMatrix,
            'daysInMonth' => $daysInMonth,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'hanyaTransaksi' => $hanyaTransaksi,
            'allUbs' => $allUbs,
            'selectedUbsId' => $selectedUbsId,
            'stats' => $stats,
            'breadcrumbs' => [
                [
                    'label' => 'Stok Barang',
                    'url' => route('gudang.stockBarang.index'),
                ],
                [
                    'label' => 'Rekap Masuk & Keluar',
                    'url' => route('gudang.rekapNotaMasuk.index'),
                ],
            ],
        ]);
    }
}
