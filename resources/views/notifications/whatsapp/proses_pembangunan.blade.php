🏗️ *{{ strtoupper($tipe) }} MULAI DIPROSES*

Pembangunan {{ strtolower($tipe) }} baru telah mulai diproses dengan rincian berikut:

Perumahan: {{ $namaPerumahan }}
@if(isset($namaArea))
{{ $tipe }}: {{ $namaArea }}
@endif
Pengawas: {{ $namaPengawas }}
Tanggal: {{ $tanggal }}
