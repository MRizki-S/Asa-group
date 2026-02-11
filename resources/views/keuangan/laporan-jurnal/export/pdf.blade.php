<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Jurnal Umum</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.4;
        }
        /* Header Laporan / Kop Surat */
        .header-container { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #444; padding-bottom: 10px; }
        .company-name { font-size: 16pt; font-bold: true; text-transform: uppercase; margin: 0; }
        .report-title { font-size: 13pt; margin: 5px 0; color: #666; }
        .period-info { font-size: 9pt; color: #888; }

        /* Tabel Styling */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th {
            background-color: #4f81bd;
            color: white;
            text-transform: uppercase;
            font-size: 9pt;
            padding: 10px 5px;
            border: 1px solid #3f6a9d;
        }
        td { padding: 8px 5px; border-bottom: 1px solid #eee; vertical-align: top; }

        /* Akuntansi Styles */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .indent { padding-left: 25px; color: #555; } /* Indentasi untuk Kredit */
        .currency-prefix { float: left; color: #999; font-size: 8pt; }

        /* Footer / Total */
        .total-row { background-color: #f8f9fa; font-weight: bold; border-top: 2px solid #444; }
        .status-badge {
            font-size: 8pt;
            padding: 2px 5px;
            border-radius: 3px;
        }
        .balanced { color: #28a745; }
        .unbalanced { color: #dc3545; }

        /* Footer Halaman */
        .page-footer { position: fixed; bottom: -20px; left: 0; right: 0; font-size: 8pt; color: #aaa; text-align: right; }
    </style>
</head>
<body>

    <div class="header-container">
        <h1 class="company-name">PT ALVIN BHAKTI MANDIRI</h1>
        <h2 class="report-title">LAPORAN JURNAL UMUM</h2>
        <div class="period-info">
            @if($tanggalStart || $tanggalEnd)
                Rentang Tanggal: {{ $tanggalStart ?? '...' }} s/d {{ $tanggalEnd ?? '...' }}
            @else
                Periode: {{ $periodeAktif?->nama_periode }}
            @endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="12%">Tanggal</th>
                <th width="15%">Kode Akun</th>
                <th width="30%">Akun / Keterangan</th>
                <th width="15%">Debit</th>
                <th width="15%">Kredit</th>
            </tr>
        </thead>
        <tbody>
            @php $lastJurnalId = null; @endphp
            @foreach($rows as $row)
                <tr>
                    <td class="text-center">
                        @if($row->jurnal_id !== $lastJurnalId)
                            {{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') }}
                        @endif
                    </td>
                    <td class="text-center">{{ $row->kode_akun }}</td>
                    <td>
                        <div class="font-bold">
                            {{ $row->nama_akun }}
                        </div>
                        @if($row->jurnal_id !== $lastJurnalId && $row->keterangan)
                            <div style="font-size: 8pt; color: #888; font-style: italic; margin-top: 2px;">
                                ({{ $row->keterangan }})
                            </div>
                        @endif
                    </td>
                    <td class="text-right">
                        @if($row->debit > 0)
                            <span class="currency-prefix">Rp</span> {{ number_format($row->debit, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right">
                        @if($row->kredit > 0)
                            <span class="currency-prefix">Rp</span> {{ number_format($row->kredit, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @php $lastJurnalId = $row->jurnal_id; @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" class="text-right">TOTAL AKHIR</td>
                <td class="text-right">
                    <span class="currency-prefix">Rp</span> {{ number_format($totalDebit, 0, ',', '.') }}
                </td>
                <td class="text-right">
                    <span class="currency-prefix">Rp</span> {{ number_format($totalKredit, 0, ',', '.') }}
                </td>
            </tr>
            <tr>
                <td colspan="5" class="text-right" style="border:none; padding-top: 5px;">
                    <span class="status-badge {{ $totalDebit == $totalKredit ? 'balanced' : 'unbalanced' }}">
                        Status: {{ $totalDebit == $totalKredit ? 'SEIMBANG (BALANCED)' : 'TIDAK SEIMBANG' }}
                    </span>
                </td>
            </tr>
        </tfoot>
    </table>

    <div class="page-footer">
        Dicetak pada: {{ now()->format('d/m/Y H:i') }}
    </div>

</body>
</html>
