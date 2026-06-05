<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stock Barang - {{ $titleGudang }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10pt; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #777; padding: 6px; }
        thead { background-color: #2F5597; color: white; }
        .zebra:nth-child(even) { background-color: #F9F9F9; }
        .title { font-size: 14pt; margin-bottom: 5px; }
        .mb-1 { margin-bottom: 5px; }
    </style>
</head>
<body>

    <div class="text-center mb-1">
        <div class="title font-bold">LAPORAN STOCK BARANG</div>
        <div>Gudang: {{ $titleGudang }}</div>
        <div>Tanggal: {{ now()->format('d-m-Y H:i') }}</div>
    </div>

    @php
        if (!function_exists('formatStockPdf')) {
            function formatStockPdf($val) {
                $formatted = number_format((float)$val, 2, ',', '.');
                $formatted = rtrim($formatted, '0');
                $formatted = rtrim($formatted, ',');
                return $formatted;
            }
        }
    @endphp

    <table>
        <thead>
            <tr>
                <th style="width: 20%;">KODE BARANG</th>
                <th style="width: 50%;">NAMA BARANG</th>
                <th style="width: 15%;">SATUAN</th>
                <th style="width: 15%;">{{ $ubsId === 'all' ? 'TOTAL STOK' : 'STOK' }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stocks as $stock)
                @php
                    $defaultKonversi = $stock->satuanKonversi->where('is_default', true)->first();
                    
                    if ($defaultKonversi) {
                        $satuanNama = $defaultKonversi->satuan->nama ?? 'Unknown';
                        $konversiRate = (float)$defaultKonversi->konversi_ke_base;
                    } else {
                        $satuanNama = $stock->baseUnit->nama ?? '-';
                        $konversiRate = 1;
                    }
                    
                    $stockVal = 0;
                    if ($ubsId === 'all') {
                        $stockVal = $stock->stock->sum('jumlah_stock');
                    } elseif ($ubsId === 'hub') {
                        $stockVal = $stock->stockHub->jumlah_stock ?? 0;
                    } else {
                        $s = $stock->stock->first();
                        $stockVal = $s->jumlah_stock ?? 0;
                    }
                    
                    $stockDisplay = $stockVal / $konversiRate;
                @endphp
                <tr class="zebra">
                    <td>{{ $stock->kode_barang }}</td>
                    <td>{{ $stock->nama_barang }}</td>
                    <td class="text-center">{{ $satuanNama }}</td>
                    <td class="text-right font-bold">{{ formatStockPdf($stockDisplay) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
