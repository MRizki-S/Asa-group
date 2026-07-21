📦 *PENGAJUAN PERMINTAAN BAHAN UNIT*

Terdapat pengajuan permintaan bahan material baru dengan rincian berikut:

• *No. Order:* {{ $order->nomor_order ?? '-' }}
• *Perumahan:* {{ $namaPerumahan }}
• *Tahap:* {{ $namaTahap }}
• *Unit:* {{ $namaUnit }}
@if(!empty($namaQc))
• *QC:* {{ $namaQc }}
@endif
• *Diajukan Oleh:* {{ $pengaju }}
• *Tanggal:* {{ $tanggal }}
@php
   $hasExceededRap = $order->details->contains(function($item) {
       return !empty($item->alasan_permintaan_tidak_sesuai_rap);
   });
@endphp
@if($hasExceededRap)
• *Status RAP:* ⚠️ *MELEBIHI RAP*
@endif

*Daftar Barang:*
@foreach($order->details as $idx => $item)
@if(!empty($item->alasan_permintaan_tidak_sesuai_rap))
{{ $idx + 1 }}. ⚠️ *{{ $item->nama_barang }}* ({{ (float)$item->jumlah_input }} {{ $item->satuan }}) - *[MELEBIHI RAP]*
   - *Alasan Melebihi RAP:* {{ $item->alasan_permintaan_tidak_sesuai_rap }}
@else
{{ $idx + 1 }}. *{{ $item->nama_barang }}* ({{ (float)$item->jumlah_input }} {{ $item->satuan }})
@endif
@endforeach

@if(!empty($order->catatan))
• *Catatan:* {{ $order->catatan }}
@endif
