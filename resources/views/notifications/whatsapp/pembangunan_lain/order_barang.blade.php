📦 *PERMINTAAN BARANG KELUAR PEMBANGUNAN LAIN-LAIN*

Terdapat pengajuan permintaan barang keluar untuk proyek pembangunan lain-lain dengan rincian berikut:

• *No. Order:* {{ $order->nomor_order ?? '-' }}
• *Tipe:* {{ strtoupper($order->jenis_order ?? 'stock') }}
• *Proyek:* {{ $namaProyek }} [{{ $namaSub }}]
@if(!empty($gudangAsal))
• *Gudang Asal:* {{ $gudangAsal }}
@endif
• *Pengawas / PIC:* {{ $pengawas }}
• *Diajukan Oleh:* {{ $pengaju }}
• *Tanggal diajukan:* {{ $tanggalDiajukan ?? ($order->created_at ? \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') . ' WIB' : now()->format('d/m/Y H:i') . ' WIB') }}
• *Tanggal nbk:* {{ $order->tanggal_diajukan ? \Carbon\Carbon::parse($order->tanggal_diajukan)->format('d/m/Y H:i') . ' WIB' : ($order->created_at ? \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') . ' WIB' : now()->format('d/m/Y H:i') . ' WIB') }}

*Daftar Barang:*
@php $totalCash = 0; $isCash = ($order->jenis_order ?? '') === 'cash'; @endphp
@foreach($order->details as $idx => $item)
@php
    $qty = (float) $item->jumlah_input;
    $hargaSatuan = (float) ($item->harga_satuan_snapshot ?? 0);
    $hargaTotal = (float) ($item->harga_total_snapshot ?? ($hargaSatuan * $qty));
    if ($isCash) $totalCash += $hargaTotal;
@endphp
@if($isCash)
{{ $idx + 1 }}. *{{ $item->nama_barang }}* ({{ $qty }} {{ $item->satuan }})
   • Harga Satuan: Rp {{ number_format($hargaSatuan, 0, ',', '.') }}
   • Total Harga: Rp {{ number_format($hargaTotal, 0, ',', '.') }}
@else
{{ $idx + 1 }}. *{{ $item->nama_barang }}* ({{ $qty }} {{ $item->satuan }})
@endif
@endforeach
@if($isCash)

• *Total Semua:* Rp {{ number_format($totalCash, 0, ',', '.') }}
@endif

@if(!empty($order->catatan))
• *Catatan:* {{ $order->catatan }}
@endif

