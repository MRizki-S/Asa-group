❌ *PENOLAKAN PERMINTAAN BARANG KELUAR PEMBANGUNAN LAIN-LAIN*

Permintaan barang keluar proyek pembangunan lain-lain berikut telah ditolak oleh {{ $penolakRole ?? 'Gudang' }}:

• *No. Order:* {{ $order->nomor_order ?? '-' }}
• *Tipe:* {{ strtoupper($order->jenis_order ?? 'stock') }}
• *Proyek:* {{ $namaProyek }} [{{ $namaSub }}]
• *Ditolak Oleh:* {{ $adminGudang }}
• *Tanggal:* {{ $tanggal }}
• *Alasan Penolakan:* {{ $alasanTolak ?? '-' }}

_Silakan lakukan penyesuaian atau ajukan ulang jika diperlukan._

