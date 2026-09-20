✅ *PERSETUJUAN (ACC) ORDER BARANG UNIT*

Permintaan bahan material unit telah disetujui (ACC) oleh SPV Logistik & Pengadaan:

• *No. Order:* {{ $order->nomor_order ?? '-' }}
@if(!empty($order->nomor_nbk))
• *No. NBK:* {{ $order->nomor_nbk }}
@endif
• *Perumahan:* {{ $namaPerumahan }}
• *Tahap:* {{ $namaTahap }}
• *Unit:* {{ $namaUnit }}
• *Disetujui Oleh:* {{ $spvName ?? $adminGudang ?? 'SPV Logistik' }}
• *Tanggal Disetujui:* {{ $tanggalSpv ?? $tanggalAcc ?? now()->format('d/m/Y H:i') . ' WIB' }}

*Daftar Barang yang Disetujui:*
@foreach($order->details as $idx => $item)
@php
    $isLuar = empty($item->rap_bahan_id);
    $isMelebihi = !empty($item->rap_bahan_id) && !empty($item->alasan_permintaan_tidak_sesuai_rap);
    $qtyMinta = (float) $item->jumlah_input;
    $qtyDiserahkan = !is_null($item->jumlah_acc) ? (float) $item->jumlah_acc : $qtyMinta;
@endphp
@if($isLuar)
{{ $idx + 1 }}. ⚠️ *{{ $item->nama_barang }}* - *[LUAR RAP]*
@elseif($isMelebihi)
{{ $idx + 1 }}. ⚠️ *{{ $item->nama_barang }}* - *[MELEBIHI RAP]*
@else
{{ $idx + 1 }}. *{{ $item->nama_barang }}*
@endif
   • Diminta: {{ $qtyMinta }} {{ $item->satuan }}
   • Diserahkan: *{{ $qtyDiserahkan }} {{ $item->satuan }}*
@endforeach
