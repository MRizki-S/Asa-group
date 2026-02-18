<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Buku Besar</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.4;
        }

        /* Header & Kop */
        .header-container {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #2f5597;
            padding-bottom: 10px;
        }

        .company-name {
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
            color: #2f5597;
        }

        .report-title {
            font-size: 13pt;
            margin: 5px 0;
            font-weight: bold;
        }

        .info-sub {
            font-size: 9pt;
            color: #555;
        }

        /* Informasi Akun & Periode */
        .meta-table {
            width: 100%;
            margin-bottom: 15px;
            font-size: 9pt;
        }

        .meta-table td {
            padding: 2px 0;
        }

        /* Tabel Utama */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        th {
            background-color: #2f5597;
            color: white;
            text-transform: uppercase;
            font-size: 8.5pt;
            padding: 10px 5px;
            border: 1px solid #2f5597;
        }

        td {
            padding: 7px 5px;
            border: 1px solid #ddd;
            vertical-align: top;
            font-size: 9pt;
        }

        /* Styling Baris Khusus */
        .row-highlight {
            background-color: #f2f5fa;
            font-weight: bold;
        }

        .row-footer {
            background-color: #e9ecef;
            font-weight: bold;
        }

        /* Utility */
        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .currency-symbol {
            float: left;
            color: #999;
            font-size: 7.5pt;
        }

        .italic {
            font-style: italic;
            color: #777;
            font-size: 8pt;
        }

        /* Page numbering */
        .footer {
            position: fixed;
            bottom: -20px;
            left: 0;
            right: 0;
            height: 30px;
            font-size: 8pt;
            color: #aaa;
            text-align: right;
        }
    </style>
</head>

<body>

    <div class="header-container">
        <h1 class="company-name">PT Alvin Bhakti Mandiri</h1>
        <h2 class="report-title">BUKU BESAR</h2>
    </div>

    <table class="meta-table">
        <tr>
            <td width="15%">Nama Akun</td>
            <td width="2%">:</td>
            <td width="40%"><strong>{{ $akun ? $akun->nama_akun : 'Semua Akun' }}</strong></td>
            <td width="15%">Periode</td>
            <td width="2%">:</td>
            <td width="26%">{{ $periodeAktif }}</td>
        </tr>
        <tr>
            <td>Kode Akun</td>
            <td>:</td>
            <td>{{ $akun ? $akun->kode_akun : '-' }}</td>
            <td>Periode Transaksi</td>
            <td>:</td>
            <td>
                {{ $tanggalStart ? \Carbon\Carbon::parse($tanggalStart)->format('d/m/Y') : 'Awal' }}
                s/d
                {{ $tanggalEnd ? \Carbon\Carbon::parse($tanggalEnd)->format('d/m/Y') : 'Akhir' }}
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th width="12%">Tanggal</th>
                <th width="33%">Keterangan</th>
                <th width="18%">Debit</th>
                <th width="18%">Kredit</th>
                <th width="19%">Saldo</th>
            </tr>
        </thead>
        <tbody>
            {{-- SALDO AWAL --}}
            <tr class="row-highlight">
                <td colspan="2" class="text-right">SALDO AWAL</td>
                <td class="text-right">-</td>
                <td class="text-right">-</td>
                <td class="text-right">
                    <span class="currency-symbol">Rp</span> {{ number_format($saldoAwal, 0, ',', '.') }}
                </td>
            </tr>

            @php $currentSaldo = $saldoAwal; @endphp

            @forelse($rows as $row)
                @php $currentSaldo += ($row->debit - $row->kredit); @endphp
                <tr>
                    <td class="text-center">{{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') }}</td>
                    <td>
                        {{ $row->keterangan }}
                        @if(isset($row->jurnal_id))
                            <br><span class="italic">Ref: #TRX-{{ $row->jurnal_id }}</span>
                        @endif
                    </td>
                    <td class="text-right">
                        @if($row->debit > 0)
                            <span class="currency-symbol">Rp</span> {{ number_format($row->debit, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right">
                        @if($row->kredit > 0)
                            <span class="currency-symbol">Rp</span> {{ number_format($row->kredit, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right">
                        <span class="currency-symbol">Rp</span> {{ number_format($currentSaldo, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center italic" style="padding: 20px;">Tidak ada transaksi pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="row-footer">
                <td colspan="2" class="text-right">TOTAL MUTASI & SALDO AKHIR</td>
                <td class="text-right">
                    <span class="currency-symbol">Rp</span> {{ number_format($totalDebit, 0, ',', '.') }}
                </td>
                <td class="text-right">
                    <span class="currency-symbol">Rp</span> {{ number_format($totalKredit, 0, ',', '.') }}
                </td>
                <td class="text-right" style="background-color: #d1d9e6;">
                    <span class="currency-symbol">Rp</span> {{ number_format($saldoAkhir, 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Dicetak pada: {{ now()->format('d/m/Y H:i') }} | Hal: {PAGENO}
    </div>

</body>

</html>