<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Neraca Saldo</title>
    <style>
        @page { 
            size: A4 landscape; 
            margin: 1cm; 
        }

        body { 
            font-family: 'Helvetica', Arial, sans-serif; 
            font-size: 9pt; /* Ukuran font */
            color: #333; 
            line-height: 1.4; 
        }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        .header-title { color: #2f5597; font-size: 16pt; font-weight: bold; margin-bottom: 2px; }
        .header-sub { font-size: 10pt; color: #555; margin-bottom: 15px; }

        table { 
            width: 100%; 
            border-collapse: collapse; 
            table-layout: fixed; 
        }
        
        th { 
            background-color: #2f5597; 
            color: white; 
            border: 0.5pt solid #1e3a63; 
            padding: 6px 2px; 
            font-size: 8.5pt;
            text-transform: uppercase;
        }
        
        td { 
            border: 0.5pt solid #ccc; 
            padding: 5px 4px; 
            vertical-align: middle;
            word-wrap: break-word;
        }
        
        .bg-gray { background-color: #f2f2f2; }
        .row-total { background-color: #ddebf7; font-weight: bold; }
        
        /* Pengaturan Lebar Kolom */
        .col-kode { width: 7%; }
        .col-nama { width: 19%; }
        .col-amount { width: 12.33%; }

        /* Currency Styling */
        .rp { float: left; font-size: 8pt; color: #777; }
        .amt { float: right; white-space: nowrap; }
        .clearfix { clear: both; }

        .footer { 
            position: fixed; 
            bottom: -0.5cm; 
            right: 0; 
            font-size: 8pt; 
            color: #aaa; 
        }
    </style>
</head>
<body>

    <div class="text-center">
        <div class="header-title">NERACA SALDO</div>
        <div class="header-sub">
            Periode: <strong>{{ $labelPeriode ?? '-' }}</strong><br>
            {{ \Carbon\Carbon::parse($tanggalMulai)->format('d M Y') }} - {{ \Carbon\Carbon::parse($tanggalSelesai)->format('d M Y') }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" class="col-kode">Kode</th>
                <th rowspan="2" class="col-nama">Nama Akun</th>
                <th colspan="2">Saldo Awal</th>
                <th colspan="2">Mutasi</th>
                <th colspan="2">Saldo Akhir</th>
            </tr>
            <tr>
                <th class="col-amount">Debit</th>
                <th class="col-amount">Kredit</th>
                <th class="col-amount">Debit</th>
                <th class="col-amount">Kredit</th>
                <th class="col-amount">Debit</th>
                <th class="col-amount">Kredit</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalSA_D = 0; $totalSA_K = 0;
                $totalM_D = 0;  $totalM_K = 0;
                $totalSK_D = 0; $totalSK_K = 0;
            @endphp

            @forelse($rows as $index => $row)
                @php
                    $saD = $row->saldo_awal > 0 ? $row->saldo_awal : 0;
                    $saK = $row->saldo_awal < 0 ? abs($row->saldo_awal) : 0;
                    $skD = $row->saldo_akhir > 0 ? $row->saldo_akhir : 0;
                    $skK = $row->saldo_akhir < 0 ? abs($row->saldo_akhir) : 0;
                    
                    $mD = $row->mutasi_debit ?? 0;
                    $mK = $row->mutasi_kredit ?? 0;

                    $totalSA_D += $saD; $totalSA_K += $saK;
                    $totalM_D += $mD;   $totalM_K += $mK;
                    $totalSK_D += $skD; $totalSK_K += $skK;
                @endphp
                <tr class="{{ $index % 2 == 1 ? 'bg-gray' : '' }}">
                    <td class="text-center">{{ $row->kode_akun }}</td>
                    <td>{{ $row->nama_akun }}</td>
                    
                    @foreach([$saD, $saK, $mD, $mK, $skD, $skK] as $val)
                        <td class="text-right">
                            {{-- Jika nilai tidak kosong dan bukan 0, tampilkan angka dan Rp --}}
                            @if(!empty($val) && $val != 0)
                                <span class="rp">Rp</span>
                                <span class="amt">{{ number_format($val, 0, ',', '.') }}</span>
                                <div class="clearfix"></div>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 20px;">Data tidak ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="row-total">
                <td colspan="2" class="text-right">TOTAL</td>
                @foreach([$totalSA_D, $totalSA_K, $totalM_D, $totalM_K, $totalSK_D, $totalSK_K] as $total)
                    <td class="text-right">
                        @if($total != 0)
                            <span class="rp">Rp</span>
                            <span class="amt">{{ number_format($total, 0, ',', '.') }}</span>
                            <div class="clearfix"></div>
                        @endif
                    </td>
                @endforeach
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Dicetak pada: {{ date('d/m/Y H:i') }}
    </div>

</body>
</html>