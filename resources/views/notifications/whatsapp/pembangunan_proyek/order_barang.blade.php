📦 *PERMINTAAN BARANG KELUAR KE KONTRAKTOR*

Terdapat permintaan barang keluar ke kontraktor dengan rincian berikut:

• *No. Order:* {{ $order->nomor_order ?? '-' }}
• *Tipe:* {{ strtoupper($order->jenis_order ?? 'stock') }}
• *Nama Proyek:* {{ $namaProyek }}
• *Pengawas:* {{ $pengawas }}
• *Diajukan Oleh:* {{ $pengaju }}
• *Tanggal diajukan:* {{ $tanggalDiajukan ?? ($order->created_at ? \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') . ' WIB' : now()->format('d/m/Y H:i') . ' WIB') }}
• *Tanggal nbk:* {{ $tanggalNbk ?? ($order->tanggal_diajukan ? \Carbon\Carbon::parse($order->tanggal_diajukan)->format('d/m/Y H:i') . ' WIB' : ($order->created_at ? \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') . ' WIB' : now()->format('d/m/Y H:i') . ' WIB')) }}
@php
   $isCash = ($order->jenis_order ?? '') === 'cash';
   $hasExceededRap = !$isCash && $order->details->contains(function($item) {
       return !empty($item->alasan_permintaan_tidak_sesuai_rap);
   });
@endphp
@if($hasExceededRap)
• *Status RAP:* ⚠️ *MELEBIHI RAP*
@endif

*Daftar Barang:*
@php $totalSemuaCash = 0; @endphp
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
@elseif(!empty($item->alasan_permintaan_tidak_sesuai_rap))
{{ $idx + 1 }}. ⚠️ *{{ $item->nama_barang }}* ({{ $qty }} {{ $item->satuan }}) - *[MELEBIHI RAP]*
   - *Alasan Melebihi RAP:* {{ $item->alasan_permintaan_tidak_sesuai_rap }}
@else
{{ $idx + 1 }}. *{{ $item->nama_barang }}* ({{ $qty }} {{ $item->satuan }})
@endif
@endforeach
@if($isCash)

• *Total Semua:* Rp {{ number_format($totalSemuaCash, 0, ',', '.') }}
@endif

@if(!empty($order->catatan))
• *Catatan:* {{ $order->catatan }}
@endif
