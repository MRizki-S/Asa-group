❌ *PEMBATALAN PERMINTAAN BARANG KELUAR KE KAWASAN*

Permintaan barang keluar ke kawasan berikut telah dibatalkan:

• *No. Order:* {{ $order->nomor_order ?? '-' }}
• *Perumahan:* {{ $namaPerumahan }}
• *Nama Kawasan:* {{ $namaKawasan }}
• *Dibatalkan Oleh:* {{ $pembatal }}
• *Tanggal Batal:* {{ $tanggal }}

*Daftar Barang:*
@foreach($order->details as $idx => $item)
{{ $idx + 1 }}. *{{ $item->nama_barang }}* ({{ (float)$item->jumlah_input }} {{ $item->satuan }})
@endforeach

@if(!empty($order->catatan))
• *Catatan:* {{ $order->catatan }}
@endif
