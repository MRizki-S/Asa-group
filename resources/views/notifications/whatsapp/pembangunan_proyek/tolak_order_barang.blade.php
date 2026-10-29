❌ *PENOLAKAN PERMINTAAN BARANG KELUAR KE KONTRAKTOR*

Permintaan barang keluar ke kontraktor berikut telah ditolak oleh {{ $penolakRole ?? 'Gudang' }}:

• *No. Order:* {{ $order->nomor_order ?? '-' }}
• *Tipe:* {{ strtoupper($order->jenis_order ?? 'stock') }}
• *Nama Proyek:* {{ $namaProyek }}
• *Ditolak Oleh:* {{ $adminGudang }}
• *Tanggal:* {{ $tanggal }}
• *Alasan Penolakan:* {{ $alasanTolak ?? '-' }}

_Silakan lakukan penyesuaian atau ajukan ulang jika diperlukan._
