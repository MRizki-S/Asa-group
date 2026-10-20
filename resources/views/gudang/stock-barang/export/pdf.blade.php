<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stock Barang - {{ $titleGudang }}</title>
    <style>
        @page {
            margin: 25px 30px;
        }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 8.5pt; 
            color: #1f2937;
            line-height: 1.3;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .italic { font-style: italic; }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 15px; 
        }
        th, td { 
            border: 1px solid #94a3b8; 
            padding: 5px 6px; 
            vertical-align: middle;
        }
        thead th { 
            background-color: #2F5597; 
            color: white; 
            font-size: 8pt;
            font-weight: bold;
            text-align: center;
        }
        .zebra:nth-child(even) { 
            background-color: #f8fafc; 
        }
        .title { 
            font-size: 13pt; 
            margin-bottom: 3px; 
            color: #1e3a8a;
        }
        .meta-info {
            font-size: 8.5pt;
            color: #4b5563;
        }
        .mb-1 { margin-bottom: 4px; }
    </style>
</head>
<body>

    <div class="text-center mb-1">
        <div class="title font-bold">LAPORAN STOCK BARANG</div>
        <div class="meta-info font-bold">Gudang: {{ $titleGudang }}</div>
        <div class="meta-info">Tanggal: {{ now()->format('d-m-Y H:i') }}
            @if(!empty($cariMaterial))
                | Filter: &ldquo;{{ $cariMaterial }}&rdquo;
            @endif
        </div>
    </div>

    @php
        if (!function_exists('formatStockPdf')) {
            function formatStockPdf($val) {
                $formatted = number_format((float)$val, 2, ',', '.');
                $formatted = rtrim($formatted, '0');
                $formatted = rtrim($formatted, ',');
                return $formatted ?: '0';
            }
        }
    @endphp

    <table>
        <thead>
            <tr>
                <th style="width: 16%;">KODE BARANG</th>
                <th style="text-align: left;">NAMA BARANG</th>
                <th style="width: 10%;">SATUAN</th>
                @if(isset($selectedDbUbs) && $selectedDbUbs->count() > 1)
                    @foreach($selectedDbUbs as $ubs)
                        <th style="width: 11%;">STOK {{ strtoupper($ubs->kode_ubs) }}</th>
                    @endforeach
                    <th style="width: 12%;">TOTAL STOK</th>
                @elseif(isset($selectedDbUbs) && $selectedDbUbs->count() === 1)
                    @php $singleUbs = $selectedDbUbs->first(); @endphp
                    <th style="width: 15%;">STOK ({{ strtoupper($singleUbs->kode_ubs) }})</th>
                    <th style="width: 15%;">MINIMAL STOK</th>
                @else
                    <th style="width: 15%;">TOTAL STOK</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($stocks as $stock)
                @php
                    $defaultKonversi = $stock->satuanKonversi->where('is_default', true)->first();
                    
                    if ($defaultKonversi) {
                        $satuanNama = $defaultKonversi->satuan->nama ?? 'Unknown';
                        $konversiRate = (float)$defaultKonversi->konversi_ke_base;
                    } else {
                        $satuanNama = $stock->baseUnit->nama ?? '-';
                        $konversiRate = 1;
                    }
                    if ($konversiRate <= 0) $konversiRate = 1;
                @endphp
                <tr class="zebra">
                    <td class="text-center" style="font-family: monospace;">{{ $stock->kode_barang }}</td>
                    <td class="text-left">{{ $stock->nama_barang }}</td>
                    <td class="text-center">{{ $satuanNama }}</td>
                    @if(isset($selectedDbUbs) && $selectedDbUbs->count() > 1)
                        @foreach($selectedDbUbs as $ubs)
                            @php
                                $s = $stock->stock->firstWhere('ubs_id', $ubs->id);
                                $qty = $s ? (float)$s->jumlah_stock : 0;
                            @endphp
                            <td class="text-right">{{ formatStockPdf($qty / $konversiRate) }}</td>
                        @endforeach
                        @php
                            $totalQty = (float)$stock->stock->sum('jumlah_stock');
                        @endphp
                        <td class="text-right font-bold">{{ formatStockPdf($totalQty / $konversiRate) }}</td>
                    @elseif(isset($selectedDbUbs) && $selectedDbUbs->count() === 1)
                        @php
                            $singleUbs = $selectedDbUbs->first();
                            $s = $stock->stock->firstWhere('ubs_id', $singleUbs->id);
                            $qty = $s ? (float)$s->jumlah_stock : 0;
                            $minQty = $s ? (float)$s->minimal_stock : 0;
                        @endphp
                        <td class="text-right font-bold">{{ formatStockPdf($qty / $konversiRate) }}</td>
                        <td class="text-right italic">{{ formatStockPdf($minQty / $konversiRate) }}</td>
                    @else
                        @php
                            $totalQty = (float)$stock->stock->sum('jumlah_stock');
                        @endphp
                        <td class="text-right font-bold">{{ formatStockPdf($totalQty / $konversiRate) }}</td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ (isset($selectedDbUbs) && $selectedDbUbs->count() > 1) ? (4 + $selectedDbUbs->count()) : ((isset($selectedDbUbs) && $selectedDbUbs->count() === 1) ? 5 : 4) }}" class="text-center" style="padding: 15px; color: #6b7280;">
                        Tidak ada data stock barang yang sesuai dengan filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
