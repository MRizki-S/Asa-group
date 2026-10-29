🔄 *PENGAJUAN ULANG PERMINTAAN BARANG KELUAR KE KAWASAN*

Permintaan barang keluar ke kawasan telah *DIAJUKAN KEMBALI*:

• *No. Order:* {{ $order->nomor_order ?? '-' }}
• *Tipe:* {{ strtoupper($order->jenis_order ?? 'stock') }}
• *Perumahan:* {{ $namaPerumahan }}
• *Nama Kawasan:* {{ $namaKawasan }}
• *Diajukan Oleh:* {{ $pengaju }}
• *Tanggal:* {{ $tanggal }}
@if(!empty($order->catatan))
• *Catatan:* {{ $order->catatan }}
@endif

*Daftar Barang:*
@php
    $isCash = ($order->jenis_order ?? '') === 'cash';
    $totalSemuaCash = 0;
@endphp
@foreach($order->details as $idx => $item)
@php
    $qty = (float)$item->jumlah_input;
    $hargaSatuan = !is_null($item->harga_satuan_snapshot) ? (float)$item->harga_satuan_snapshot : 0;
    $hargaTotal = !is_null($item->harga_total_snapshot) ? (float)$item->harga_total_snapshot : ($hargaSatuan * $qty);
    if ($isCash) {
        $totalSemuaCash += $hargaTotal;
    }
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

• *Total Semua:* Rp {{ number_format($totalSemuaCash, 0, ',', '.') }}
@endif
