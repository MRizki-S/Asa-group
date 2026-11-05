<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bon Permintaan & Pengeluaran Barang Lain-lain - {{ $order->nomor_nbk ?? $order->nomor_order }}</title>
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
        .info-label { font-weight: bold; width: 18%; white-space: nowrap; }
        .info-sep { width: 2%; }
        .info-value { width: 30%; }

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
            width: 33.33%;
            font-size: 7.5pt;
            vertical-align: top;
            padding: 0 8px;
        }
        .sig-space { height: 32px; }
        .sig-name { font-weight: bold; border-top: 1px solid #000; padding-top: 2px; margin-top: 2px; display: inline-block; min-width: 100px; }

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
    $sub        = $order->sub;
    $namaProyek = $proyek?->nama_proyek ?? '-';
    $namaSub    = $sub?->nama_sub ?? '-';
    $namaGudang = $order->ubs ? $order->ubs->nama_ubs : ($order->jenis_order === 'stock' ? '-' : 'Non-Stok (' . strtoupper($order->jenis_order) . ')');
    $pengawas   = $order->pengawas?->nama_lengkap ?? $order->pengawas?->name ?? '-';
    $pembuat    = $order->pembuat?->nama_lengkap ?? $order->pembuat?->name ?? '-';

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
                                <div class="title-box">
                                    Bon Permintaan &amp; Pengeluaran Barang
                                    @if(($order->jenis_order ?? '') === 'cash')
                                        <span style="font-size: 7.5pt; vertical-align: middle; background-color: #065f46; color: #ffffff; padding: 1.5px 5px; border-radius: 3px; font-weight: bold; letter-spacing: 0.5px; margin-left: 4px;">PEMBELIAN CASH</span>
                                    @endif
                                </div>
                                <div class="subtitle">ASA Group &mdash; Pembangunan Lain-lain</div>
                            </td>
                            <td style="width: 35%;" class="nbk-box">
                                <div class="nbk-number">{{ $order->nomor_nbk ?? $order->nomor_order }}</div>
                                <div class="nbk-ref">Ref: {{ $order->nomor_order }}</div>
                                <div class="badge-part">
                                    @if($topSlot['totalParts'] > 1)
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
                            <td class="info-label">Proyek Induk</td>
                            <td class="info-sep">:</td>
                            <td class="info-value">{{ $namaProyek }}</td>
                            <td class="info-label">Tanggal</td>
                            <td class="info-sep">:</td>
                            <td class="info-value">{{ $tglOrder }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Sub-Pekerjaan</td>
                            <td class="info-sep">:</td>
                            <td class="info-value">{{ $namaSub }}</td>
                            <td class="info-label">Jam</td>
                            <td class="info-sep">:</td>
                            <td class="info-value">{{ $jamOrder }} WIB</td>
                        </tr>
                        <tr>
                            <td class="info-label">Gudang Asal</td>
                            <td class="info-sep">:</td>
                            <td class="info-value">{{ $namaGudang }}</td>
                            <td class="info-label">Pengawas / PIC</td>
                            <td class="info-sep">:</td>
                            <td class="info-value">{{ $pengawas }}</td>
                        </tr>
                    </table>
                    <hr class="divider-thin">

                    <table class="items-table">
                        <thead>
                            <tr>
                                <th style="width: 6%;">No.</th>
                                <th style="width: 18%;">Kode Item</th>
                                <th style="width: 44%;">Nama Material</th>
                                <th style="width: 14%;">Qty</th>
                                <th style="width: 18%;">Satuan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topSlot['items'] as $iIdx => $detail)
                                <tr>
                                    <td class="text-center">{{ $topSlot['startIndex'] + $iIdx }}</td>
                                    <td style="font-family: monospace;">{{ $detail->barang?->kode_barang ?? '-' }}</td>
                                    <td>{{ $detail->nama_barang ?? $detail->barang?->nama_barang ?? '-' }}</td>
                                    <td class="text-center font-bold">{{ $formatQty($detail->jumlah_input) }}</td>
                                    <td class="text-center">{{ $detail->satuan }}</td>
                                </tr>
                            @endforeach
                            @for($emptyRow = count($topSlot['items']); $emptyRow < 8; $emptyRow++)
                                <tr>
                                    <td class="text-center" style="color: #ccc;">-</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>

                    <table class="sig-table">
                        <tr>
                            <td>
                                Diajukan Oleh,<br>
                                <div class="sig-space"></div>
                                <span class="sig-name">{{ $pembuat }}</span>
                            </td>
                            <td>
                                Diterima Oleh (PIC),<br>
                                <div class="sig-space"></div>
                                <span class="sig-name">{{ $pengawas }}</span>
                            </td>
                            <td>
                                Mengetahui (Gudang/SPV),<br>
                                <div class="sig-space"></div>
                                <span class="sig-name">{{ $order->accBy?->nama_lengkap ?? $order->accBy?->name ?? 'Gudang' }}</span>
                            </td>
                        </tr>
                    </table>

                    <div class="print-footer">
                        Dicetak pada {{ now()->format('d/m/Y H:i') }} WIB &bull; ASA Group System
                    </div>
                </td>
            </tr>

            {{-- GARIS POTONG --}}
            <tr class="row-cut">
                <td><div class="cut-line"></div></td>
            </tr>

            {{-- NOTA BAWAH (SALINAN ARSIP) --}}
            <tr class="row-bottom">
                <td>
                    <table class="header-wrap">
                        <tr>
                            <td style="width: 65%;">
                                <div class="title-box">
                                    Bon Permintaan &amp; Pengeluaran Barang
                                    @if(($order->jenis_order ?? '') === 'cash')
                                        <span style="font-size: 7.5pt; vertical-align: middle; background-color: #065f46; color: #ffffff; padding: 1.5px 5px; border-radius: 3px; font-weight: bold; letter-spacing: 0.5px; margin-left: 4px;">PEMBELIAN CASH</span>
                                    @endif
                                </div>
                                <div class="subtitle">ASA Group &mdash; Pembangunan Lain-lain</div>
                            </td>
                            <td style="width: 35%;" class="nbk-box">
                                <div class="nbk-number">{{ $order->nomor_nbk ?? $order->nomor_order }}</div>
                                <div class="nbk-ref">Ref: {{ $order->nomor_order }}</div>
                                <div class="badge-part">
                                    [SALINAN ARSIP GUDANG]
                                </div>
                            </td>
                        </tr>
                    </table>
                    <hr class="divider">

                    <table class="info-table">
                        <tr>
                            <td class="info-label">Proyek Induk</td>
                            <td class="info-sep">:</td>
                            <td class="info-value">{{ $namaProyek }}</td>
                            <td class="info-label">Tanggal</td>
                            <td class="info-sep">:</td>
                            <td class="info-value">{{ $tglOrder }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Sub-Pekerjaan</td>
                            <td class="info-sep">:</td>
                            <td class="info-value">{{ $namaSub }}</td>
                            <td class="info-label">Jam</td>
                            <td class="info-sep">:</td>
                            <td class="info-value">{{ $jamOrder }} WIB</td>
                        </tr>
                        <tr>
                            <td class="info-label">Gudang Asal</td>
                            <td class="info-sep">:</td>
                            <td class="info-value">{{ $namaGudang }}</td>
                            <td class="info-label">Pengawas / PIC</td>
                            <td class="info-sep">:</td>
                            <td class="info-value">{{ $pengawas }}</td>
                        </tr>
                    </table>
                    <hr class="divider-thin">

                    <table class="items-table">
                        <thead>
                            <tr>
                                <th style="width: 6%;">No.</th>
                                <th style="width: 18%;">Kode Item</th>
                                <th style="width: 44%;">Nama Material</th>
                                <th style="width: 14%;">Qty</th>
                                <th style="width: 18%;">Satuan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topSlot['items'] as $iIdx => $detail)
                                <tr>
                                    <td class="text-center">{{ $topSlot['startIndex'] + $iIdx }}</td>
                                    <td style="font-family: monospace;">{{ $detail->barang?->kode_barang ?? '-' }}</td>
                                    <td>{{ $detail->nama_barang ?? $detail->barang?->nama_barang ?? '-' }}</td>
                                    <td class="text-center font-bold">{{ $formatQty($detail->jumlah_input) }}</td>
                                    <td class="text-center">{{ $detail->satuan }}</td>
                                </tr>
                            @endforeach
                            @for($emptyRow = count($topSlot['items']); $emptyRow < 8; $emptyRow++)
                                <tr>
                                    <td class="text-center" style="color: #ccc;">-</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>

                    <table class="sig-table">
                        <tr>
                            <td>
                                Diajukan Oleh,<br>
                                <div class="sig-space"></div>
                                <span class="sig-name">{{ $pembuat }}</span>
                            </td>
                            <td>
                                Diterima Oleh (PIC),<br>
                                <div class="sig-space"></div>
                                <span class="sig-name">{{ $pengawas }}</span>
                            </td>
                            <td>
                                Mengetahui (Gudang/SPV),<br>
                                <div class="sig-space"></div>
                                <span class="sig-name">{{ $order->accBy?->nama_lengkap ?? $order->accBy?->name ?? 'Gudang' }}</span>
                            </td>
                        </tr>
                    </table>

                    <div class="print-footer">
                        Dicetak pada {{ now()->format('d/m/Y H:i') }} WIB &bull; ASA Group System
                    </div>
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

