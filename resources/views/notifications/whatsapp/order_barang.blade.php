📦 *PENGAJUAN PERMINTAAN BAHAN {{ strtoupper($tipe ?? 'UNIT') }}*

Terdapat pengajuan permintaan bahan material baru dengan rincian berikut:

@if(!empty($order->nomor_order))Nomor Order: {{ $order->nomor_order }}
@endif
@if(isset($namaPerumahan) && $namaPerumahan !== '-')Perumahan: {{ $namaPerumahan }}
@endif
@if(isset($namaArea)){{ $tipe ?? 'Area' }}: {{ $namaArea }}
@endif
@if(isset($namaTahap))Tahap: {{ $namaTahap }}
@endif
@if(isset($namaUnit))Unit: {{ $namaUnit }}
@endif
Diajukan: {{ $pengaju }}
Tanggal: {{ $tanggal }}

*Daftar Barang:*
@foreach($order->details as $idx => $item)
{{ $idx + 1 }}. {{ $item->nama_barang }} ({{ (float)$item->jumlah_input }} {{ $item->satuan }})
@if(!empty($item->alasan_permintaan_tidak_sesuai_rap))
   _Alasan: {{ $item->alasan_permintaan_tidak_sesuai_rap }}_
@endif
@endforeach

@if(!empty($order->catatan))
Catatan: {{ $order->catatan }}
@endif
