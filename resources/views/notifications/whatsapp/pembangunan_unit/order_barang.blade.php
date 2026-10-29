📦 *PERMINTAAN BARANG KELUAR KE UNIT*

Terdapat permintaan barang keluar ke unit dengan rincian berikut:

• *No. Order:* {{ $order->nomor_order ?? '-' }}
• *Tipe:* {{ strtoupper($order->jenis_order ?? 'stock') }}
• *Perumahan:* {{ $namaPerumahan }}
• *Unit:* {{ $namaUnit }}
@if(!empty($namaQc))
• *QC:* {{ $namaQc }}
@endif
• *Diajukan Oleh:* {{ $pengaju }}
• *Tanggal diajukan:* {{ $tanggalDiajukan ?? ($order->created_at ? \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') . ' WIB' : now()->format('d/m/Y H:i') . ' WIB') }}
• *Tanggal nbk:* {{ $tanggalNbk ?? ($order->tanggal_diajukan ? \Carbon\Carbon::parse($order->tanggal_diajukan)->format('d/m/Y H:i') . ' WIB' : ($order->created_at ? \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') . ' WIB' : now()->format('d/m/Y H:i') . ' WIB')) }}
@php
    $isCash = ($order->jenis_order ?? '') === 'cash';
    $isServis = (bool)($order->qc->is_servis ?? false);
    $hasLuarRap = !$isCash && !$isServis && $order->details->contains(function($item) {
        return empty($item->rap_bahan_id);
    });
    $hasExceededRap = !$isCash && !$isServis && $order->details->contains(function($item) {
        return !empty($item->rap_bahan_id) && !empty($item->alasan_permintaan_tidak_sesuai_rap);
    });
@endphp
@if(!$isCash && !$isServis)
    @if($hasLuarRap && $hasExceededRap)
• *Status RAP:* ⚠️ *DI LUAR & MELEBIHI RAP*
    @elseif($hasExceededRap)
• *Status RAP:* ⚠️ *MELEBIHI RAP*
    @elseif($hasLuarRap)
• *Status RAP:* ⚠️ *DI LUAR RAP*
    @endif
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
    $isLuar = !$isServis && empty($item->rap_bahan_id);
    $isMelebihi = !$isServis && !empty($item->rap_bahan_id) && !empty($item->alasan_permintaan_tidak_sesuai_rap);
@endphp
@if($isCash)
{{ $idx + 1 }}. *{{ $item->nama_barang }}* ({{ $qty }} {{ $item->satuan }})
   • Harga Satuan: Rp {{ number_format($hargaSatuan, 0, ',', '.') }}
   • Total Harga: Rp {{ number_format($hargaTotal, 0, ',', '.') }}
@elseif($isLuar)
{{ $idx + 1 }}. ⚠️ *{{ $item->nama_barang }}* ({{ $qty }} {{ $item->satuan }}) - *[LUAR RAP]*
@elseif($isMelebihi)
{{ $idx + 1 }}. ⚠️ *{{ $item->nama_barang }}* ({{ $qty }} {{ $item->satuan }}) - *[MELEBIHI RAP]*
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
