✅ *KONFIRMASI ACC PERMINTAAN BAHAN KAWASAN*

Permintaan bahan material kawasan telah disetujui (ACC) oleh Gudang:

• *No. Order:* {{ $order->nomor_order ?? '-' }}
• *Perumahan:* {{ $namaPerumahan }}
• *Nama Kawasan:* {{ $namaKawasan }}
• *Dikonfirmasi Oleh:* {{ $adminGudang }}
• *Tanggal ACC:* {{ $tanggalAcc }}

*Daftar Barang yang Disetujui:*
@foreach($order->details as $idx => $item)
{{ $idx + 1 }}. *{{ $item->nama_barang }}* ({{ (float)$item->jumlah_input }} {{ $item->satuan }})
@endforeach
