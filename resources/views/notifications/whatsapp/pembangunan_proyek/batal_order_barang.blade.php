❌ *PEMBATALAN PERMINTAAN BARANG KELUAR KE KONTRAKTOR*

Permintaan barang keluar ke kontraktor berikut telah dibatalkan:

• *No. Order:* {{ $order->nomor_order ?? '-' }}
• *Nama Proyek:* {{ $namaProyek }}
• *Dibatalkan Oleh:* {{ $pembatal }}
• *Tanggal Batal:* {{ $tanggal }}

*Daftar Barang:*
@foreach($order->details as $idx => $item)
{{ $idx + 1 }}. *{{ $item->nama_barang }}* ({{ (float)$item->jumlah_input }} {{ $item->satuan }})
@endforeach

@if(!empty($order->catatan))
• *Catatan:* {{ $order->catatan }}
@endif
