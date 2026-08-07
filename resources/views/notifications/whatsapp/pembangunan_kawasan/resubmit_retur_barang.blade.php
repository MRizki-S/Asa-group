🔄 *PENGAJUAN ULANG RETUR BAHAN KAWASAN*

Pengajuan retur bahan material kawasan telah *DIAJUKAN KEMBALI*:

• *No. Retur:* {{ $return->nomor_return ?? '-' }}
• *Perumahan:* {{ $namaPerumahan }}
• *Nama Kawasan:* {{ $namaKawasan }}
• *Diajukan Oleh:* {{ $pengaju }}
• *Tanggal:* {{ $tanggal }}

*Daftar Barang yang Diretur:*
@foreach($return->details as $idx => $item)
@php
   $namaBarang = $item->nama_barang ?? ($item->barang->nama_barang ?? 'Barang');
   $satuanItem = $item->satuan ?? '-';
@endphp
{{ $idx + 1 }}. *{{ $namaBarang }}* ({{ (float)$item->jumlah_input }} {{ $satuanItem }})
@if(!empty($item->keterangan))
   _Alasan: {{ $item->keterangan }}_
@endif
@endforeach

@if(!empty($return->catatan))
• *Catatan:* {{ $return->catatan }}
@endif
