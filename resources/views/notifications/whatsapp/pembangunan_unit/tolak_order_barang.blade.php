❌ *PENOLAKAN PERMINTAAN BARANG KELUAR KE UNIT*

Permintaan barang keluar ke unit telah *DITOLAK* oleh {{ $penolakRole ?? 'Gudang' }}:

• *No. Order:* {{ $order->nomor_order ?? '-' }}
• *Tipe:* {{ strtoupper($order->jenis_order ?? 'stock') }}
• *Perumahan:* {{ $namaPerumahan }}
• *Unit:* {{ $namaUnit }}
• *Ditolak Oleh:* {{ $adminGudang }}
• *Tanggal:* {{ $tanggal }}
@if(!empty($namaQc))
• *QC:* {{ $namaQc }}
@endif
@if(!empty($alasanTolak))
• *Alasan Penolakan:* {{ $alasanTolak }}
@endif

*Daftar Barang yang Ditolak:*
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
    $isLuar = !$isCash && empty($item->rap_bahan_id);
    $isMelebihi = !$isCash && !empty($item->rap_bahan_id) && !empty($item->alasan_permintaan_tidak_sesuai_rap);
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

Silakan lakukan perbaikan dan ajukan ulang permintaan.
