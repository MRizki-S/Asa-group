🔄 *PENGAJUAN ULANG PERMINTAAN BARANG KELUAR KE KONTRAKTOR*

Permintaan barang keluar ke kontraktor telah *DIAJUKAN KEMBALI*:

• *No. Order:* {{ $order->nomor_order ?? '-' }}
• *Nama Proyek:* {{ $namaProyek }}
• *Diajukan Oleh:* {{ $pengaju }}
• *Tanggal:* {{ $tanggal }}
@if(!empty($order->catatan))
• *Catatan:* {{ $order->catatan }}
@endif

*Daftar Barang:*
@foreach($order->details as $idx => $item)
{{ $idx + 1 }}. *{{ $item->nama_barang }}* ({{ (float)$item->jumlah_input }} {{ $item->satuan }})
@endforeach
