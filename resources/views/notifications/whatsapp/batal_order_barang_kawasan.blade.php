❌ *PEMBATALAN PENGAJUAN PERMINTAAN BAHAN KAWASAN*

Permintaan bahan material berikut telah dibatalkan:

Perumahan: {{ $namaPerumahan }}
Kawasan: {{ $namaArea }}
Pembatal: {{ $pembatal }}
Tanggal: {{ $tanggal }}

*Daftar Barang:*
@foreach($order->details as $idx => $item)
{{ $idx + 1 }}. {{ $item->nama_barang }} ({{ (float)$item->jumlah_input }} {{ $item->satuan }})
@endforeach

@if(!empty($order->catatan))
Catatan: {{ $order->catatan }}
@endif
