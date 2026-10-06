✅ *PERSETUJUAN (ACC) BARANG KELUAR KE KAWASAN*

Permintaan barang keluar ke kawasan telah disetujui (ACC) oleh SPV Layanan & Dukungan:

• *No. Order:* {{ $order->nomor_order ?? '-' }}
@if(!empty($order->nomor_nbk))
• *No. NBK:* {{ $order->nomor_nbk }}
@endif
• *Perumahan:* {{ $namaPerumahan }}
• *Kawasan:* {{ $namaKawasan }}
• *Disetujui Oleh:* {{ $spvName ?? $adminGudang ?? 'SPV Layanan & Dukungan' }}
• *Tanggal Disetujui:* {{ $tanggalSpv ?? $tanggalAcc ?? now()->format('d/m/Y H:i') . ' WIB' }}

*Daftar Barang yang Disetujui:*
@foreach($order->details as $idx => $item)
@php
    $isMelebihi = !empty($item->alasan_permintaan_tidak_sesuai_rap);
    $qtyDiserahkan = !is_null($item->jumlah_acc) ? (float) $item->jumlah_acc : (float) $item->jumlah_input;
@endphp
@if($isMelebihi)
{{ $idx + 1 }}. ⚠️ *{{ $item->nama_barang }}* - *[MELEBIHI RAP]*
@else
{{ $idx + 1 }}. *{{ $item->nama_barang }}*
@endif
   • Jumlah Diserahkan: *{{ $qtyDiserahkan }} {{ $item->satuan }}*
@endforeach

@if(!empty($order->catatan))
• *Catatan:* {{ $order->catatan }}
@endif
