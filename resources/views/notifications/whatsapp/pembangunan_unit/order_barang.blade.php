📦 *PERMINTAAN BARANG KELUAR KE UNIT*

Terdapat permintaan barang keluar ke unit dengan rincian berikut:

• *No. Order:* {{ $order->nomor_order ?? '-' }}
• *Perumahan:* {{ $namaPerumahan }}
• *Tahap:* {{ $namaTahap }}
• *Unit:* {{ $namaUnit }}
@if(!empty($namaQc))
• *QC:* {{ $namaQc }}
@endif
• *Diajukan Oleh:* {{ $pengaju }}
• *Tanggal diajukan:* {{ $tanggalDiajukan ?? ($order->created_at ? \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') . ' WIB' : now()->format('d/m/Y H:i') . ' WIB') }}
• *Tanggal nbk:* {{ $tanggalNbk ?? ($order->tanggal_diajukan ? \Carbon\Carbon::parse($order->tanggal_diajukan)->format('d/m/Y H:i') . ' WIB' : ($order->created_at ? \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') . ' WIB' : now()->format('d/m/Y H:i') . ' WIB')) }}
@php
    $isServis = (bool)($order->qc->is_servis ?? false);
    $hasLuarRap = !$isServis && $order->details->contains(function($item) {
        return empty($item->rap_bahan_id);
    });
    $hasExceededRap = !$isServis && $order->details->contains(function($item) {
        return !empty($item->rap_bahan_id) && !empty($item->alasan_permintaan_tidak_sesuai_rap);
    });
@endphp
@if(!$isServis)
    @if($hasLuarRap && $hasExceededRap)
• *Status RAP:* ⚠️ *DI LUAR & MELEBIHI RAP*
    @elseif($hasExceededRap)
• *Status RAP:* ⚠️ *MELEBIHI RAP*
    @elseif($hasLuarRap)
• *Status RAP:* ⚠️ *DI LUAR RAP*
    @endif
@endif

*Daftar Barang:*
@foreach($order->details as $idx => $item)
@php
    $isLuar = !$isServis && empty($item->rap_bahan_id);
    $isMelebihi = !$isServis && !empty($item->rap_bahan_id) && !empty($item->alasan_permintaan_tidak_sesuai_rap);
@endphp
@if($isLuar)
{{ $idx + 1 }}. ⚠️ *{{ $item->nama_barang }}* ({{ (float)$item->jumlah_input }} {{ $item->satuan }}) - *[LUAR RAP]*
@elseif($isMelebihi)
{{ $idx + 1 }}. ⚠️ *{{ $item->nama_barang }}* ({{ (float)$item->jumlah_input }} {{ $item->satuan }}) - *[MELEBIHI RAP]*
@else
{{ $idx + 1 }}. *{{ $item->nama_barang }}* ({{ (float)$item->jumlah_input }} {{ $item->satuan }})
@endif
@endforeach

@if(!empty($order->catatan))
• *Catatan:* {{ $order->catatan }}
@endif
