❌ *PERMINTAAN BAHAN KAWASAN DITOLAK*

Permintaan bahan material kawasan telah *DITOLAK* oleh Gudang:

• *No. Order:* {{ $order->nomor_order ?? '-' }}
• *Perumahan:* {{ $namaPerumahan }}
• *Nama Kawasan:* {{ $namaKawasan }}
• *Ditolak Oleh:* {{ $adminGudang }}
• *Tanggal:* {{ $tanggal }}
@if(!empty($alasanTolak))
• *Alasan Penolakan:* {{ $alasanTolak }}
@endif

*Daftar Barang yang Ditolak:*
@foreach($order->details as $idx => $item)
{{ $idx + 1 }}. *{{ $item->nama_barang }}* ({{ (float)$item->jumlah_input }} {{ $item->satuan }})
@endforeach

Silakan lakukan perbaikan dan ajukan ulang permintaan.
