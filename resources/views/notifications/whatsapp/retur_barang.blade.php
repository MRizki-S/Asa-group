🔄 *PENGAJUAN RETUR BAHAN {{ strtoupper($tipe ?? 'UNIT') }}*

Terdapat pengajuan pengembalian (retur) bahan material dengan rincian berikut:

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

*Daftar Barang yang Diretur:*
@foreach($return->details as $idx => $item)
@php
$namaBarang = $item->nama_barang ?? ($item->barang->nama_barang ?? ($item->orderDetail->nama_barang ?? 'Barang'));
$satuanItem = $item->satuan ?? ($item->orderDetail->satuan ?? '-');
@endphp
{{ $idx + 1 }}. {{ $namaBarang }} ({{ (float)$item->jumlah_return }} {{ $satuanItem }})
@if(!empty($item->keterangan_return))
   _Alasan: {{ $item->keterangan_return }}_
@endif
@endforeach
