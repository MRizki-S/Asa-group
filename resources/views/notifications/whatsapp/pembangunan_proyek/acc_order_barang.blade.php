✅ *PERSETUJUAN (ACC) ORDER BARANG PROYEK*

Permintaan bahan material proyek telah disetujui (ACC) oleh SPV Logistik & Pengadaan:

• *No. Order:* {{ $order->nomor_order ?? '-' }}
@if(!empty($order->nomor_nbk))
• *No. NBK:* {{ $order->nomor_nbk }}
@endif
• *Nama Proyek:* {{ $namaProyek }}
• *Disetujui Oleh:* {{ $spvName ?? $adminGudang ?? 'SPV Logistik' }}
• *Tanggal Disetujui:* {{ $tanggalSpv ?? $tanggalAcc ?? now()->format('d/m/Y H:i') . ' WIB' }}

*Daftar Barang yang Disetujui:*
@foreach($order->details as $idx => $item)
@php
    $isMelebihi = !empty($item->alasan_permintaan_tidak_sesuai_rap);
    $qtyMinta = (float) $item->jumlah_input;
    $qtyDiserahkan = !is_null($item->jumlah_acc) ? (float) $item->jumlah_acc : $qtyMinta;
@endphp
@if($isMelebihi)
{{ $idx + 1 }}. ⚠️ *{{ $item->nama_barang }}* - *[MELEBIHI RAP]*
@else
{{ $idx + 1 }}. *{{ $item->nama_barang }}*
@endif
   • Diminta: {{ $qtyMinta }} {{ $item->satuan }}
   • Diserahkan: *{{ $qtyDiserahkan }} {{ $item->satuan }}*
@endforeach

@if(!empty($order->catatan))
• *Catatan:* {{ $order->catatan }}
@endif
