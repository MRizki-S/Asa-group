❌ *PEMBATALAN PERMINTAAN BAHAN UNIT*

Permintaan bahan material unit berikut telah dibatalkan:

• *No. Order:* {{ $order->nomor_order ?? '-' }}
• *Perumahan:* {{ $namaPerumahan }}
• *Tahap:* {{ $namaTahap }}
• *Unit:* {{ $namaUnit }}
• *Dibatalkan Oleh:* {{ $pembatal }}
• *Tanggal Batal:* {{ $tanggal }}

*Daftar Barang:*
@foreach($order->details as $idx => $item)
{{ $idx + 1 }}. *{{ $item->nama_barang }}* ({{ (float)$item->jumlah_input }} {{ $item->satuan }})
@endforeach

@if(!empty($order->catatan))
• *Catatan:* {{ $order->catatan }}
@endif
