✅ *PERSETUJUAN (ACC) BARANG KELUAR PEMBANGUNAN LAIN-LAIN*

Permintaan barang keluar proyek pembangunan lain-lain telah disetujui (ACC) oleh {{ $spvRole ?? 'Petugas' }}:

• *No. Order:* {{ $order->nomor_order ?? '-' }}
• *Tipe:* {{ strtoupper($order->jenis_order ?? 'stock') }}
@if(!empty($order->nomor_nbk))
• *No. NBK:* {{ $order->nomor_nbk }}
@endif
• *Proyek:* {{ $namaProyek }} [{{ $namaSub }}]
@if(!empty($gudangAsal))
• *Gudang Asal:* {{ $gudangAsal }}
@endif
• *Disetujui Oleh:* {{ $spvName ?? 'Petugas Gudang' }}
• *Tanggal Disetujui:* {{ $tanggalAcc ?? now()->format('d/m/Y H:i') . ' WIB' }}

*Daftar Barang yang Disetujui:*
@php $totalCash = 0; $isCash = ($order->jenis_order ?? '') === 'cash'; @endphp
@foreach($order->details as $idx => $item)
@php
    $qty = (float) $item->jumlah_input;
    $hargaSatuan = (float) ($item->harga_satuan_snapshot ?? 0);
    $hargaTotal = (float) ($item->harga_total_snapshot ?? ($hargaSatuan * $qty));
    if ($isCash) $totalCash += $hargaTotal;
@endphp
@if($isCash)
{{ $idx + 1 }}. *{{ $item->nama_barang }}*
   • Jumlah Diserahkan: *{{ $qty }} {{ $item->satuan }}*
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

