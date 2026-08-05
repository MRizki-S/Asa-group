❌ *PERMINTAAN BAHAN PROYEK DITOLAK*

Permintaan bahan material proyek telah *DITOLAK* oleh Gudang:

• *No. Order:* {{ $order->nomor_order ?? '-' }}
• *Nama Proyek:* {{ $namaProyek }}
• *Ditolak Oleh:* {{ $adminGudang }}
• *Tanggal:* {{ $tanggal }}
@if(!empty($order->catatan))
• *Alasan Penolakan:* {{ $order->catatan }}
@endif

*Daftar Barang yang Ditolak:*
@foreach($order->details as $idx => $item)
{{ $idx + 1 }}. *{{ $item->nama_barang }}* ({{ (float)$item->jumlah_input }} {{ $item->satuan }})
@endforeach

Silakan lakukan perbaikan dan ajukan ulang permintaan.
