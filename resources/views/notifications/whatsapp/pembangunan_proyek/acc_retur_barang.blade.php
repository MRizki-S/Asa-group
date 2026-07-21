✅ *KONFIRMASI ACC RETUR BAHAN PROYEK*

Pengajuan retur bahan material proyek telah disetujui (ACC) oleh Gudang:

• *No. Retur:* {{ $return->nomor_return ?? '-' }}
• *Nama Proyek:* {{ $namaProyek }}
• *Dikonfirmasi Oleh:* {{ $adminGudang }}
• *Tanggal ACC:* {{ $tanggalAcc }}

*Rincian Barang Retur:*
@foreach($return->details as $idx => $item)
@php
   $namaBarang = $item->nama_barang ?? ($item->barang->nama_barang ?? 'Barang');
   $satuanItem = $item->satuan ?? '-';
@endphp
{{ $idx + 1 }}. *{{ $namaBarang }}* (Total: {{ (float)$item->jumlah_input }} {{ $satuanItem }})
   - *Layak:* {{ (float)$item->jumlah_layak_base }}
   - *Rusak:* {{ (float)$item->jumlah_rusak_base }}
@endforeach
