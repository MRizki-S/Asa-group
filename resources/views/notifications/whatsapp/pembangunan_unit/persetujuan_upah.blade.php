@if(($statusAction ?? 'pengajuan') === 'pengajuan')
💵 *PENGAJUAN UPAH BORONGAN TUKANG*

Terdapat pengajuan upah borongan baru dengan rincian berikut:

@foreach($items as $index => $item)
@php
    $unit = $item->pembangunanUnit->unit ?? null;
@endphp
• *No. Pengajuan:* {{ $item->nomor_pengajuan ?? '-' }}
• *Perumahan:* {{ $unit->tahap->perumahaan->nama_perumahaan ?? '-' }}
• *Tahap:* {{ $unit->tahap->nama_tahap ?? '-' }}
• *Unit:* {{ $unit->nama_unit ?? '-' }}
• *Tahap QC:* {{ $item->pembangunanUnitQc->nama_qc ?? '-' }}
• *Nama Pekerjaan:* {{ $item->nama_upah }}
• *Nominal Diajukan:* Rp {{ number_format($item->nominal_diajukan, 0, ',', '.') }}
• *Diajukan Oleh:* {{ $pengaju }}
• *Tanggal:* {{ $tanggal }}
@if(!empty($item->catatan_pengawas))
• *Catatan Pengawas:* {{ $item->catatan_pengawas }}
@endif
@if(!$loop->last)


@endif
@endforeach
@else
@if($isApprove)
✅ *RESPON UPAH BORONGAN TUKANG (DISETUJUI)*
@else
❌ *RESPON UPAH BORONGAN TUKANG (DITOLAK)*
@endif

Status pengajuan upah borongan telah diperbarui:

@foreach($items as $index => $item)
@php
    $unit = $item->pembangunanUnit->unit ?? null;
@endphp
• *No. Pengajuan:* {{ $item->nomor_pengajuan ?? '-' }}
• *Perumahan:* {{ $unit->tahap->perumahaan->nama_perumahaan ?? '-' }}
• *Tahap:* {{ $unit->tahap->nama_tahap ?? '-' }}
• *Unit:* {{ $unit->nama_unit ?? '-' }}
• *Tahap QC:* {{ $item->pembangunanUnitQc->nama_qc ?? '-' }}
• *Nama Pekerjaan:* {{ $item->nama_upah }}
• *Nominal Diajukan:* Rp {{ number_format($item->nominal_diajukan, 0, ',', '.') }}
• *Respon Oleh:* {{ $penyetuju }} ({{ $rolePenyetuju }})
• *Status:* {{ $isApprove ? 'DISETUJUI' : 'DITOLAK' }}
• *Tanggal Respon:* {{ $tanggal }}
@if(!$isApprove && !empty($item->alasan_ditolak))
• *Alasan Ditolak:* {{ $item->alasan_ditolak }}
@endif
@if(!$loop->last)


@endif
@endforeach
@endif
