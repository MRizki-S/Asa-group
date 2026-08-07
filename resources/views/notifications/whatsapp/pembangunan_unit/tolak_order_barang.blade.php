❌ *PERMINTAAN BAHAN UNIT DITOLAK*

Permintaan bahan material unit telah *DITOLAK* oleh Gudang:

• *No. Order:* {{ $order->nomor_order ?? '-' }}
• *Perumahan:* {{ $namaPerumahan }}
• *Tahap:* {{ $namaTahap }}
• *Unit:* {{ $namaUnit }}
• *Ditolak Oleh:* {{ $adminGudang }}
• *Tanggal:* {{ $tanggal }}
@if(!empty($namaQc))
• *QC:* {{ $namaQc }}
@endif
@if(!empty($alasanTolak))
• *Alasan Penolakan:* {{ $alasanTolak }}
@endif

*Daftar Barang yang Ditolak:*
@foreach($order->details as $idx => $item)
@php
    $isLuar = empty($item->rap_bahan_id);
    $isMelebihi = !empty($item->rap_bahan_id) && !empty($item->alasan_permintaan_tidak_sesuai_rap);
@endphp
@if($isLuar)
{{ $idx + 1 }}. ⚠️ *{{ $item->nama_barang }}* ({{ (float)$item->jumlah_input }} {{ $item->satuan }}) - *[LUAR RAP]*
@elseif($isMelebihi)
{{ $idx + 1 }}. ⚠️ *{{ $item->nama_barang }}* ({{ (float)$item->jumlah_input }} {{ $item->satuan }}) - *[MELEBIHI RAP]*
@else
{{ $idx + 1 }}. *{{ $item->nama_barang }}* ({{ (float)$item->jumlah_input }} {{ $item->satuan }})
@endif
@endforeach

Silakan lakukan perbaikan dan ajukan ulang permintaan.
