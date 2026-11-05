✅ *KONFIRMASI (ACC) RETUR BARANG PEMBANGUNAN LAIN-LAIN*

Pengajuan retur material proyek pembangunan lain-lain telah disetujui (ACC) oleh Gudang:

• *No. Retur:* {{ $return->nomor_return ?? '-' }}
• *Proyek:* {{ $namaProyek }} [{{ $namaSub }}]
• *Dikonfirmasi Oleh:* {{ $adminGudang }}
• *Tanggal Disetujui:* {{ $tanggalAcc }}

*Rincian Barang Retur:*
@foreach($return->details as $idx => $item)
@php
   $namaBarang = $item->nama_barang ?? ($item->barang->nama_barang ?? 'Barang');
   $satuanItem = $item->satuan ?? '-';
@endphp
{{ $idx + 1 }}. *{{ $namaBarang }}* (Total: {{ (float)$item->jumlah_input }} {{ $satuanItem }})
   - *Layak:* {{ (float)$item->jumlah_layak_base }} {{ $item->barang?->baseUnit?->nama ?? '' }}
   - *Rusak:* {{ (float)$item->jumlah_rusak_base }} {{ $item->barang?->baseUnit?->nama ?? '' }}
@endforeach

