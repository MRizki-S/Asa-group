📦 *PENGAJUAN PERMINTAAN BAHAN {{ strtoupper($tipe ?? 'UNIT') }}*

Dear *Tim Logistik/Gudang*, terdapat pengajuan permintaan bahan material baru.

```
@if(isset($namaPerumahan) && $namaPerumahan !== '-')📍 Perumahan : {{ $namaPerumahan }}
@endif
@if(isset($namaArea))🏘️ {{ $tipe ?? 'Area' }}   : {{ $namaArea }}
@endif
@if(isset($namaTahap))🏠 Tahap     : {{ $namaTahap }}
@endif
@if(isset($namaUnit))🔑 Unit      : {{ $namaUnit }}
@endif👤 Diajukan  : {{ $pengaju }}
📅 Tanggal   : {{ $tanggal }}
```

*Daftar Barang:*
@foreach($order->details as $idx => $item)
{{ $idx + 1 }}. {{ $item->nama_barang }} ({{ (float)$item->jumlah_input }} {{ $item->satuan }})
@if(!empty($item->alasan_permintaan_tidak_sesuai_rap))
   _Alasan: {{ $item->alasan_permintaan_tidak_sesuai_rap }}_
@endif
@endforeach

Mohon untuk segera dicek pada sistem. Terima kasih! 🙏
@if(!empty($order->catatan))

📝 *Catatan:* {{ $order->catatan }}
@endif
