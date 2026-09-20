<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bon Permintaan & Pengeluaran Barang Proyek - {{ $order->nomor_nbk ?? $order->nomor_order }}</title>
    <style>
        @page { size: a4 portrait; margin: 8mm 12mm; }
        table { border-collapse: collapse; }
        body { font-family: 'Arial', sans-serif; font-size: 8pt; color: #000; line-height: 1.25; }

        .sheet-table { width: 100%; border-collapse: collapse; border: none; table-layout: fixed; }
        .sheet-table > tbody > tr.row-top > td { height: 126mm; max-height: 126mm; vertical-align: top; padding: 0 0 2mm 0; border: none; overflow: hidden; }
        .sheet-table > tbody > tr.row-cut > td { height: 8mm; vertical-align: middle; border: none; padding: 0; }
        .sheet-table > tbody > tr.row-bottom > td { height: 126mm; max-height: 126mm; vertical-align: top; padding: 4mm 0 0 0; border: none; overflow: hidden; }

        .cut-line {
            width: 100%;
            border-top: 1px dashed #777;
            height: 0;
            line-height: 0;
        }
        .page-break {
            page-break-after: always;
        }

        .header-wrap { width: 100%; border-collapse: collapse; margin-bottom: 3px; }
        .header-wrap td { border: none; vertical-align: top; padding: 0; }
        .title-box { font-size: 11pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; line-height: 1.15; }
        .subtitle { font-size: 7pt; color: #444; margin-top: 1px; }
        .nbk-box { text-align: right; }
        .nbk-number { font-size: 9.5pt; font-weight: bold; }
        .nbk-ref { font-size: 6.5pt; color: #555; }
        .badge-part { display: inline-block; font-size: 6.5pt; color: #555; font-weight: bold; }

        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 3px; }
        .info-table td { padding: 1px 3px; font-size: 7.5pt; border: none; vertical-align: top; }
        .info-label { font-weight: bold; width: 20%; white-space: nowrap; }
        .info-sep { width: 2%; }
        .info-value { width: 28%; }

        hr.divider { border: none; border-top: 1.5px solid #000; margin: 3px 0; }
        hr.divider-thin { border: none; border-top: 1px solid #999; margin: 3px 0; }

        .items-table { width: 100%; border-collapse: collapse; margin-top: 3px; }
        .items-table th {
            border: 1px solid #000;
            padding: 3px 4px;
            font-size: 7pt;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            background-color: #f2f2f2;
        }
        .items-table td {
            border: 1px solid #000;
            padding: 2.5px 4px;
            font-size: 7.5pt;
            vertical-align: middle;
        }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }

        .sig-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .sig-table td {
            text-align: center;
            border: none;
            width: 50%;
            font-size: 7.5pt;
            vertical-align: top;
            padding: 0 8px;
        }
        .sig-space { height: 32px; }
        .sig-name { font-weight: bold; border-top: 1px solid #000; padding-top: 2px; margin-top: 2px; display: inline-block; min-width: 120px; }

        .print-footer {
            margin-top: 4px;
            border-top: 1px dashed #ccc;
            padding-top: 1.5px;
            font-size: 6pt;
            color: #999;
            text-align: right;
        }
    </style>
</head>
<body>

@php
    $proyek     = $order->proyek;
    $namaProyek = $proyek?->nama_project ?? $proyek?->nama ?? '-';
    $pengawas   = $proyek?->pengawas?->nama_lengkap ?? $proyek?->pengawas?->name ?? '-';
    $pembuat    = $order->user?->nama_lengkap ?? $order->user?->name ?? ($order->pembuat?->nama_lengkap ?? '-');
    $gudang     = $order->gudangBy?->nama_lengkap ?? $order->gudangBy?->name ?? '';

    $tglOrder = $order->tanggal_diajukan
        ? \Carbon\Carbon::parse($order->tanggal_diajukan)->format('d/m/Y')
        : date('d/m/Y');
    $jamOrder = $order->tanggal_diajukan
        ? \Carbon\Carbon::parse($order->tanggal_diajukan)->format('H:i')
        : date('H:i');

    $formatQty = function ($value) {
        $number = round((float) $value, 3);
        if (abs($number - round($number)) < 0.000001) {
            return number_format($number, 0, ',', '.');
        }
        return rtrim(rtrim(number_format($number, 3, ',', '.'), '0'), ',');
    };

    $itemChunks = $order->details->chunk(8)->values();
    $totalParts = $itemChunks->count();

    $slots = [];
    foreach ($itemChunks as $pIdx => $chunk) {
        $slots[] = [
            'part' => $pIdx + 1,
            'totalParts' => $totalParts,
            'isCopy' => false,
            'items' => $chunk->values(),
            'startIndex' => ($pIdx * 8) + 1,
        ];
    }

    $sheets = array_chunk($slots, 2);
@endphp

@foreach($sheets as $sheetIndex => $sheetSlots)
    <table class="sheet-table">
        <tbody>
            {{-- NOTA ATAS --}}
            @php $topSlot = $sheetSlots[0]; @endphp
            <tr class="row-top">
                <td>
                    <table class="header-wrap">
                        <tr>
                            <td style="width: 65%;">
                                <div class="title-box">Bon Permintaan &amp; Pengeluaran Barang</div>
                                <div class="subtitle">ABM Group / Mangoon &mdash; Pembangunan Proyek</div>
                            </td>
                            <td style="width: 35%;" class="nbk-box">
                                <div class="nbk-number">{{ $order->nomor_nbk ?? $order->nomor_order }}</div>
                                <div class="nbk-ref">Ref: {{ $order->nomor_order }}</div>
                                <div class="badge-part">
                                    @if($topSlot['isCopy'])
                                        [SALINAN ARSIP]
                                    @elseif($topSlot['totalParts'] > 1)
                                        [Bagian {{ $topSlot['part'] }} dari {{ $topSlot['totalParts'] }}]
                                    @else
                                        [ASLI]
                                    @endif
                                </div>
                            </td>
                        </tr>
                    </table>
                    <hr class="divider">

                    <table class="info-table">
                        <tr>
                            <td class="info-label">Nama Proyek</td>
                            <td class="info-sep">:</td>
                            <td class="info-value">{{ $namaProyek }}</td>
                            <td class="info-label">Tanggal</td>
                            <td class="info-sep">:</td>
                            <td class="info-value">{{ $tglOrder }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Gudang Asal</td>
                            <td class="info-sep">:</td>
                            <td class="info-value">UBS Mangoon</td>
                            <td class="info-label">Jam</td>
                            <td class="info-sep">:</td>
                            <td class="info-value">{{ $jamOrder }} WIB</td>
                        </tr>
                        <tr>
                            <td class="info-label">Diajukan Oleh</td>
                            <td class="info-sep">:</td>
                            <td class="info-value">{{ $pembuat }}</td>
                            <td class="info-label">Mandor</td>
                            <td class="info-sep">:</td>
                            <td class="info-value">{{ $pengawas }}</td>
                        </tr>
                    </table>
                    <hr class="divider-thin">

                    <table class="items-table">
                        <thead>
                            <tr>
                                <th style="width: 5%;">No.</th>
                                <th style="width: 16%;">Kode Item</th>
                                <th style="width: 38%;">Nama Material</th>
                                <th style="width: 12%;">Qty Diminta</th>
                                <th style="width: 12%;">Qty Diserahkan</th>
                                <th style="width: 17%;">Satuan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topSlot['items'] as $itemIdx => $item)
                                @php
                                    $qtyKeluar = !is_null($item->jumlah_acc) ? (float)$item->jumlah_acc : (float)$item->jumlah_input;
                                    $rowNum = $topSlot['startIndex'] + $itemIdx;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $rowNum }}</td>
                                    <td>{{ $item->barang?->kode_barang ?? '-' }}</td>
                                    <td><strong>{{ $item->nama_barang ?? $item->barang?->nama_barang ?? '-' }}</strong></td>
                                    <td class="text-center">{{ $formatQty($item->jumlah_input) }}</td>
                                    <td class="text-center font-bold">{{ $formatQty($qtyKeluar) }}</td>
                                    <td class="text-center">{{ $item->satuan }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <table class="sig-table">
                        <tr>
                            <td>
                                <div>Disetujui Oleh,</div>
                                <div style="font-size: 7pt; color: #555;">Pengawas Proyek</div>
                                <div class="sig-space"></div>
                                <div><span class="sig-name">({{ $pengawas }})</span></div>
                                <div style="font-size: 6.5pt; color: #888;">Nama Jelas &amp; Ttd</div>
                            </td>
                            <td>
                                <div>Diserahkan Oleh,</div>
                                <div style="font-size: 7pt; color: #555;">Staf Gudang</div>
                                <div class="sig-space"></div>
                                <div><span class="sig-name">({{ $gudang ?: '............................' }})</span></div>
                                <div style="font-size: 6.5pt; color: #888;">Nama Jelas &amp; Ttd</div>
                            </td>
                        </tr>
                    </table>

                    <div class="print-footer">
                        Dicetak otomatis oleh Sistem Logistik ABM pada {{ now()->format('d/m/Y H:i:s') }}
                    </div>
                </td>
            </tr>

            {{-- GARIS POTONG --}}
            <tr class="row-cut">
                <td>
                    <div class="cut-line"></div>
                </td>
            </tr>

            {{-- NOTA BAWAH --}}
            @php $botSlot = $sheetSlots[1] ?? null; @endphp
            <tr class="row-bottom">
                <td>
                    @if($botSlot)
                        <table class="header-wrap">
                            <tr>
                                <td style="width: 65%;">
                                    <div class="title-box">Bon Permintaan &amp; Pengeluaran Barang</div>
                                    <div class="subtitle">ABM Group / Mangoon &mdash; Pembangunan Proyek</div>
                                </td>
                                <td style="width: 35%;" class="nbk-box">
                                    <div class="nbk-number">{{ $order->nomor_nbk ?? $order->nomor_order }}</div>
                                    <div class="nbk-ref">Ref: {{ $order->nomor_order }}</div>
                                    <div class="badge-part">
                                        @if($botSlot['isCopy'])
                                            [SALINAN ARSIP]
                                        @elseif($botSlot['totalParts'] > 1)
                                            [Bagian {{ $botSlot['part'] }} dari {{ $botSlot['totalParts'] }}]
                                        @else
                                            [ASLI]
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        </table>
                        <hr class="divider">

                        <table class="info-table">
                            <tr>
                                <td class="info-label">Nama Proyek</td>
                                <td class="info-sep">:</td>
                                <td class="info-value">{{ $namaProyek }}</td>
                                <td class="info-label">Tanggal</td>
                                <td class="info-sep">:</td>
                                <td class="info-value">{{ $tglOrder }}</td>
                            </tr>
                            <tr>
                                <td class="info-label">Gudang Asal</td>
                                <td class="info-sep">:</td>
                                <td class="info-value">UBS Mangoon</td>
                                <td class="info-label">Jam</td>
                                <td class="info-sep">:</td>
                                <td class="info-value">{{ $jamOrder }} WIB</td>
                            </tr>
                            <tr>
                                <td class="info-label">Diajukan Oleh</td>
                                <td class="info-sep">:</td>
                                <td class="info-value">{{ $pembuat }}</td>
                                <td class="info-label">Mandor</td>
                                <td class="info-sep">:</td>
                                <td class="info-value">{{ $pengawas }}</td>
                            </tr>
                        </table>
                        <hr class="divider-thin">

                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">No.</th>
                                    <th style="width: 16%;">Kode Item</th>
                                    <th style="width: 38%;">Nama Material</th>
                                    <th style="width: 12%;">Qty Diminta</th>
                                    <th style="width: 12%;">Qty Diserahkan</th>
                                    <th style="width: 17%;">Satuan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($botSlot['items'] as $itemIdx => $item)
                                    @php
                                        $qtyKeluar = !is_null($item->jumlah_acc) ? (float)$item->jumlah_acc : (float)$item->jumlah_input;
                                        $rowNum = $botSlot['startIndex'] + $itemIdx;
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $rowNum }}</td>
                                        <td>{{ $item->barang?->kode_barang ?? '-' }}</td>
                                        <td><strong>{{ $item->nama_barang ?? $item->barang?->nama_barang ?? '-' }}</strong></td>
                                        <td class="text-center">{{ $formatQty($item->jumlah_input) }}</td>
                                        <td class="text-center font-bold">{{ $formatQty($qtyKeluar) }}</td>
                                        <td class="text-center">{{ $item->satuan }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <table class="sig-table">
                            <tr>
                                <td>
                                    <div>Disetujui Oleh,</div>
                                    <div style="font-size: 7pt; color: #555;">Pengawas Proyek</div>
                                    <div class="sig-space"></div>
                                    <div><span class="sig-name">({{ $pengawas }})</span></div>
                                    <div style="font-size: 6.5pt; color: #888;">Nama Jelas &amp; Ttd</div>
                                </td>
                                <td>
                                    <div>Diserahkan Oleh,</div>
                                    <div style="font-size: 7pt; color: #555;">Staf Gudang</div>
                                    <div class="sig-space"></div>
                                    <div><span class="sig-name">({{ $gudang ?: '............................' }})</span></div>
                                    <div style="font-size: 6.5pt; color: #888;">Nama Jelas &amp; Ttd</div>
                                </td>
                            </tr>
                        </table>

                        <div class="print-footer">
                            Dicetak otomatis oleh Sistem Logistik ABM pada {{ now()->format('d/m/Y H:i:s') }}
                        </div>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    @if(!$loop->last)
        <div class="page-break"></div>
    @endif
@endforeach

</body>
</html>
