@if(($statusAction ?? 'pengajuan') === 'pengajuan')
💵 *PENGAJUAN UPAH BORONGAN TUKANG*

Terdapat pengajuan upah borongan baru dengan rincian berikut:

• *No. Pengajuan:* {{ $pengajuan->nomor_pengajuan ?? '-' }}
• *Perumahan:* {{ $namaPerumahan }}
• *Tahap:* {{ $namaTahap }}
• *Unit:* {{ $namaUnit }}
• *Tahap QC:* {{ $namaQc }}
• *Nama Pekerjaan:* {{ $pengajuan->nama_upah }}
• *Nominal Diajukan:* Rp {{ number_format($pengajuan->nominal_diajukan, 0, ',', '.') }}
• *Diajukan Oleh:* {{ $pengaju }}
• *Tanggal:* {{ $tanggal }}
@if(!empty($pengajuan->catatan_pengawas))
• *Catatan Pengawas:* {{ $pengajuan->catatan_pengawas }}
@endif
@else
@if($isApprove)
✅ *RESPON UPAH BORONGAN TUKANG (DISETUJUI)*
@else
❌ *RESPON UPAH BORONGAN TUKANG (DITOLAK)*
@endif

Status pengajuan upah borongan telah diperbarui:

• *No. Pengajuan:* {{ $pengajuan->nomor_pengajuan ?? '-' }}
• *Perumahan:* {{ $namaPerumahan }}
• *Tahap:* {{ $namaTahap }}
• *Unit:* {{ $namaUnit }}
• *Nama Pekerjaan:* {{ $pengajuan->nama_upah }}
• *Nominal Diajukan:* Rp {{ number_format($pengajuan->nominal_diajukan, 0, ',', '.') }}
• *Respon Oleh:* {{ $penyetuju }} ({{ $rolePenyetuju }})
• *Status:* {{ $isApprove ? 'DISETUJUI' : 'DITOLAK' }}
• *Tanggal Respon:* {{ $tanggal }}
@if(!$isApprove && !empty($pengajuan->alasan_ditolak))
• *Alasan Ditolak:* {{ $pengajuan->alasan_ditolak }}
@endif
@endif
