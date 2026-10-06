✅ *PERSETUJUAN (ACC) BARANG KELUAR KE UNIT*

Permintaan barang keluar ke unit telah disetujui (ACC) oleh SPV Layanan & Dukungan:

• *No. Order:* {{ $order->nomor_order ?? '-' }}
@if(!empty($order->nomor_nbk))
• *No. NBK:* {{ $order->nomor_nbk }}
@endif
• *Perumahan:* {{ $namaPerumahan }}
• *Tahap:* {{ $namaTahap }}
• *Unit:* {{ $namaUnit }}
• *Disetujui Oleh:* {{ $spvName ?? $adminGudang ?? 'SPV Layanan & Dukungan' }}
• *Tanggal Disetujui:* {{ $tanggalSpv ?? $tanggalAcc ?? now()->format('d/m/Y H:i') . ' WIB' }}

*Daftar Barang yang Disetujui:*
@foreach($order->details as $idx => $item)
@php
    $isServis = (bool)($order->qc->is_servis ?? false);
    $isLuar = !$isServis && empty($item->rap_bahan_id);
    $isMelebihi = !$isServis && !empty($item->rap_bahan_id) && !empty($item->alasan_permintaan_tidak_sesuai_rap);
    $qtyDiserahkan = !is_null($item->jumlah_acc) ? (float) $item->jumlah_acc : (float) $item->jumlah_input;
@endphp
@if($isLuar)
{{ $idx + 1 }}. ⚠️ *{{ $item->nama_barang }}* - *[LUAR RAP]*
@elseif($isMelebihi)
{{ $idx + 1 }}. ⚠️ *{{ $item->nama_barang }}* - *[MELEBIHI RAP]*
@else
{{ $idx + 1 }}. *{{ $item->nama_barang }}*
@endif
   • Jumlah Diserahkan: *{{ $qtyDiserahkan }} {{ $item->satuan }}*
@endforeach
