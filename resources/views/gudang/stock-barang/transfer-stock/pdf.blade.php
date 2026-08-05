<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Transfer Stock Barang Gudang - {{ $transfer->nomor_transfer }}</title>
    <style>
        @page {
            size: A4;
            margin: 15mm 15mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            color: #1a1a1a;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        /* Header Layout */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .header-table td {
            vertical-align: middle;
        }

        .company-name {
            font-size: 26px;
            font-weight: bold;
            color: #2b4c7e;
        }

        .logo-img {
            max-width: 250px;
            max-height: 90px;
            height: auto;
        }

        .doc-title {
            text-align: right;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            color: #2b4c7e; /* Biru Redup / Muted Navy */
        }

        .doc-no {
            text-align: right;
            font-size: 13px;
            margin-top: 4px;
            color: #444;
        }

        /* Divider Line */
        .line-divider {
            border-bottom: 2px solid #2b4c7e;
            margin-bottom: 18px;
        }

        /* Information Table Header */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .info-table td {
            padding: 5px 0;
            font-size: 13px;
            vertical-align: top;
        }

        .info-label {
            width: 130px;
            font-weight: bold;
            color: #222;
        }

        .info-colon {
            width: 15px;
            text-align: center;
            color: #555;
        }

        .info-value {
            color: #111;
        }

        /* Items Table with Muted Blue Header */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .items-table th {
            background-color: #2b4c7e; /* Biru Redup / Soft Navy */
            color: #ffffff;
            border: 1px solid #2b4c7e;
            padding: 9px 10px;
            text-align: left;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .items-table td {
            border-bottom: 1px solid #e0e0e0;
            border-left: 1px solid #f0f0f0;
            border-right: 1px solid #f0f0f0;
            padding: 9px 10px;
            font-size: 12.5px;
            color: #222;
        }

        .items-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        /* Footer / Paraf Section */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            page-break-inside: avoid;
        }

        .footer-table td {
            text-align: right;
            vertical-align: top;
        }

        .signature-box {
            display: inline-block;
            width: 230px;
            text-align: center;
        }

        .signature-title {
            font-size: 12px;
            font-weight: bold;
            color: #333;
            margin-bottom: 60px;
        }

        .signature-line {
            border-bottom: 1px solid #333;
            margin-bottom: 5px;
            min-height: 18px;
            font-weight: bold;
            font-size: 13px;
        }

        .signature-role {
            font-size: 11px;
            color: #555;
        }
    </style>
</head>
<body>

    <!-- Header Table -->
    <table class="header-table">
        <tr>
            <td>
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" class="logo-img" alt="Logo">
                @else
                    <div class="company-name">ASA GROUP</div>
                @endif
            </td>
            <td>
                <div class="doc-title">Transfer Stock Barang</div>
                <div class="doc-no">No. Nota: <strong>{{ $transfer->nomor_transfer }}</strong></div>
            </td>
        </tr>
    </table>

    <div class="line-divider"></div>

    <!-- Info Details Header -->
    <table class="info-table">
        <tr>
            <td class="info-label">Dari Gudang</td>
            <td class="info-colon">:</td>
            <td class="info-value">{{ $transfer->fromUbs->nama_ubs ?? '-' }} ({{ $transfer->fromUbs->kode_ubs ?? '-' }})</td>
            <td class="info-label">Tanggal Transfer</td>
            <td class="info-colon">:</td>
            <td class="info-value">{{ \Carbon\Carbon::parse($transfer->tanggal_transfer)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td class="info-label">Ke Gudang</td>
            <td class="info-colon">:</td>
            <td class="info-value">{{ $transfer->toUbs->nama_ubs ?? '-' }} ({{ $transfer->toUbs->kode_ubs ?? '-' }})</td>
            <td class="info-label">Diajukan Oleh</td>
            <td class="info-colon">:</td>
            <td class="info-value">{{ $transfer->creator->nama_lengkap ?? $transfer->creator->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Tipe Transaksi</td>
            <td class="info-colon">:</td>
            <td class="info-value">Transfer Gudang (UBS ke UBS)</td>
            <td class="info-label">Status</td>
            <td class="info-colon">:</td>
            <td class="info-value"><strong>{{ strtoupper($transfer->status) }}</strong></td>
        </tr>
        <tr>
            <td class="info-label">Keterangan</td>
            <td class="info-colon">:</td>
            <td class="info-value" colspan="4">{{ $transfer->keterangan ?? '-' }}</td>
        </tr>
    </table>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 40px; text-align: center;">No</th>
                <th style="width: 130px;">Kode Barang</th>
                <th>Nama Barang</th>
                <th style="text-align: right; width: 110px;">Jumlah</th>
                <th style="width: 90px; text-align: center;">Satuan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transfer->details as $no => $detail)
            <tr>
                <td style="text-align: center;">{{ $no + 1 }}</td>
                <td style="font-weight: bold;">{{ $detail->barang->kode_barang ?? '-' }}</td>
                <td>{{ $detail->nama_barang_snapshot ?? $detail->barang->nama_barang ?? '-' }}</td>
                <td style="text-align: right; font-weight: bold;">{{ (float)$detail->qty }}</td>
                <td style="text-align: center;">{{ $detail->satuan->nama ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Footer Paraf SPV Only -->
    <table class="footer-table">
        <tr>
            <td>
                <div class="signature-box">
                    <div class="signature-title">Mengetahui / Menyetujui,</div>
                    <div class="signature-line">
                        @if($transfer->status === 'disetujui' && $transfer->approvedBy)
                            {{ $transfer->approvedBy->nama_lengkap ?? $transfer->approvedBy->name }}
                        @else
                            &nbsp;
                        @endif
                    </div>
                    <div class="signature-role">Spv Logistik & Pengadaan</div>
                </div>
            </td>
        </tr>
    </table>

</body>
</html>