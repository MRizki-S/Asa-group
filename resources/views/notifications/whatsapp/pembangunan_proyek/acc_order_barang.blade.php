✅ *PERSETUJUAN (ACC) BARANG KELUAR KE KONTRAKTOR*

Permintaan barang keluar ke kontraktor telah disetujui (ACC) oleh {{ $spvRole ?? 'Petugas' }}:

• *No. Order:* {{ $order->nomor_order ?? '-' }}
• *Tipe:* {{ strtoupper($order->jenis_order ?? 'stock') }}
@if(!empty($order->nomor_nbk))
• *No. NBK:* {{ $order->nomor_nbk }}
@endif
• *Nama Proyek:* {{ $namaProyek }}
• *Disetujui Oleh:* {{ $spvName ?? $adminGudang ?? '-' }}
• *Tanggal Disetujui:* {{ $tanggalSpv ?? $tanggalAcc ?? now()->format('d/m/Y H:i') . ' WIB' }}

*Daftar Barang yang Disetujui:*
@php
    $isCash = ($order->jenis_order ?? '') === 'cash';
    $totalSemuaCash = 0;
@endphp
@foreach($order->details as $idx => $item)
@php
    $qtyMinta = (float) $item->jumlah_input;
    $qtyDiserahkan = !is_null($item->jumlah_acc) ? (float) $item->jumlah_acc : $qtyMinta;
    $hargaSatuan = !is_null($item->harga_satuan_snapshot) ? (float)$item->harga_satuan_snapshot : 0;
    $hargaTotal = !is_null($item->harga_total_snapshot) ? (float)$item->harga_total_snapshot : ($hargaSatuan * $qtyDiserahkan);
    if ($isCash) {
        $totalSemuaCash += $hargaTotal;
    }
    $isMelebihi = !$isCash && !empty($item->alasan_permintaan_tidak_sesuai_rap);
@endphp
@if($isCash)
{{ $idx + 1 }}. *{{ $item->nama_barang }}*
   • Diminta: {{ $qtyMinta }} {{ $item->satuan }}
   • Diserahkan: *{{ $qtyDiserahkan }} {{ $item->satuan }}*
   • Harga Satuan: Rp {{ number_format($hargaSatuan, 0, ',', '.') }}
   • Total Harga: Rp {{ number_format($hargaTotal, 0, ',', '.') }}
@elseif($isMelebihi)
{{ $idx + 1 }}. ⚠️ *{{ $item->nama_barang }}* - *[MELEBIHI RAP]*
   • Diminta: {{ $qtyMinta }} {{ $item->satuan }}
   • Diserahkan: *{{ $qtyDiserahkan }} {{ $item->satuan }}*
@else
{{ $idx + 1 }}. *{{ $item->nama_barang }}*
   • Diminta: {{ $qtyMinta }} {{ $item->satuan }}
   • Diserahkan: *{{ $qtyDiserahkan }} {{ $item->satuan }}*
@endif
@endforeach
@if($isCash)

• *Total Semua:* Rp {{ number_format($totalSemuaCash, 0, ',', '.') }}
@endif

@if(!empty($order->catatan))
• *Catatan:* {{ $order->catatan }}
@endif
