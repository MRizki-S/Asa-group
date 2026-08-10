@extends('layouts.app')

@section('pageActive', $pageActive)

@section('content')
<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

    {{-- Breadcrumb --}}
    <div x-data="{ pageName: '{{ $pageActive }}' }">
        @include('partials.breadcrumb')
    </div>

    {{-- ===== Header Info Card ===== --}}
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-white/[0.03] mb-5">
        <div class="px-5 py-4 sm:px-6 border-b border-gray-100 dark:border-gray-700 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-semibold text-gray-800 dark:text-white">
                    Detail Pengajuan Upah Harian Tukang {{ $isAbm ? 'ABM' : 'Mangoon' }}
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $pengajuan->nomor_upah_harian }}</p>
            </div>
            @php
                $statusConfig = match($pengajuan->status) {
                    'draft'     => ['label' => 'Draft',     'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'],
                    'diajukan'  => ['label' => 'Diajukan',  'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-800 dark:text-blue-200'],
                    'disetujui' => ['label' => 'Disetujui', 'class' => 'bg-green-100 text-green-700 dark:bg-green-800 dark:text-green-200'],
                    'ditolak'   => ['label' => 'Ditolak',   'class' => 'bg-red-100 text-red-700 dark:bg-red-800 dark:text-red-200'],
                    default       => ['label' => ucfirst($pengajuan->status), 'class' => 'bg-gray-100 text-gray-600'],
                };
            @endphp
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $statusConfig['class'] }}">
                {{ $statusConfig['label'] }}
            </span>
        </div>
        <div class="px-5 py-5 sm:px-6">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-5 text-sm">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-xs mb-0.5">Tanggal Mulai</p>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $pengajuan->tanggal_mulai->translatedFormat('d F Y') }}</p>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-xs mb-0.5">Tanggal Selesai</p>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $pengajuan->tanggal_selesai->translatedFormat('d F Y') }}</p>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-xs mb-0.5">Dibuat Oleh</p>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $pengajuan->createdBy->nama_lengkap ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-xs mb-0.5">Tanggal Dibuat</p>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $pengajuan->created_at->translatedFormat('d F Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Detail Per Tukang (Accordion) ===== --}}
    <div class="space-y-3 mb-5" x-data="{ openIdx: 0 }">
        @foreach($detailPerTukang as $idx => $row)
            @php
                $tukang  = $row['tukang'];
                $details = $row['details'];
                $hariMasuk    = $details->where('status_kehadiran', true)->count();
                $upahNormal   = $details->sum('nominal_harian_final');
                $totalLembur  = $details->sum(fn($d) => $d->alokasi->where('jenis', 'lembur')->sum('subtotal'));
                $grandTotal   = $upahNormal + $totalLembur;
            @endphp

            <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                {{-- Accordion Header --}}
                <button type="button" @click="openIdx = openIdx === {{ $idx }} ? null : {{ $idx }}"
                    class="w-full flex items-center justify-between px-5 py-3.5 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-left">
                    <div class="flex items-center gap-3 flex-wrap">
                        <span class="text-sm font-semibold text-gray-800 dark:text-white">{{ $tukang->nama_tukang ?? '-' }}</span>
                        <span class="text-xs text-gray-400 font-mono">{{ $tukang->kode ?? '' }}</span>
                        <span class="text-xs bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300 px-2 py-0.5 rounded-full">
                            {{ $hariMasuk }} hari masuk
                        </span>
                    </div>
                    <div class="flex items-center gap-4 shrink-0 ml-3">
                        <div class="hidden sm:block text-right text-xs text-gray-500">
                            <span>Upah: Rp {{ number_format($upahNormal, 0, ',', '.') }}</span>
                            @if($totalLembur > 0)
                                &middot; <span class="text-orange-600">Lembur: Rp {{ number_format($totalLembur, 0, ',', '.') }}</span>
                            @endif
                        </div>
                        <svg class="w-4 h-4 text-gray-500 transition-transform duration-200"
                            :class="openIdx === {{ $idx }} ? 'rotate-180' : '"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </button>

                {{-- Accordion Body --}}
                <div x-show="openIdx === {{ $idx }}" x-collapse>
                    {{-- Info snapshot tukang --}}
                    <div class="px-5 py-3 bg-blue-50 dark:bg-blue-900/20 border-b border-blue-100 dark:border-blue-800 flex flex-wrap gap-6 text-sm">
                        <div>
                            <span class="text-blue-600 dark:text-blue-400 text-xs">Gaji Harian (Snapshot)</span>
                            <p class="font-semibold text-blue-800 dark:text-blue-200">
                                Rp {{ number_format($details->first()->gaji_harian_default_snapshot ?? 0, 0, ',', '.') }}
                            </p>
                        </div>
                        <div>
                            <span class="text-blue-600 dark:text-blue-400 text-xs">Jam Kerja Default (Snapshot)</span>
                            <p class="font-semibold text-blue-800 dark:text-blue-200">
                                {{ $details->first()->jam_default_snapshot ?? 0 }} Jam
                            </p>
                        </div>
                    </div>

                    {{-- Daftar Hari --}}
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($details as $detail)
                            @php
                                $alokasiNormal = $detail->alokasi->where('jenis', 'normal');
                                $alokasiLembur = $detail->alokasi->where('jenis', 'lembur');
                            @endphp

                            <div x-data="{ open: {{ $detail->status_kehadiran && ($detail->alokasi->isNotEmpty()) ? 'true' : 'false' }} }" class="px-5 py-3">
                                <button type="button" @click="{{ $detail->status_kehadiran ? 'open = !open' : '' }}"
                                    class="w-full flex items-center justify-between text-left gap-3 {{ !$detail->status_kehadiran ? 'cursor-default' : '' }}">
                                    <div class="flex items-center gap-3">
                                        <span class="text-sm font-medium text-gray-800 dark:text-white w-36 shrink-0">
                                            {{ $detail->tanggal->translatedFormat('D, d M') }}
                                        </span>
                                        @if($detail->status_kehadiran)
                                            <span class="text-xs bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300 px-2 py-0.5 rounded-full font-medium">Hadir</span>
                                        @else
                                            <span class="text-xs bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400 px-2 py-0.5 rounded-full font-medium">Tidak Hadir</span>
                                        @endif
                                    </div>

                                    @if($detail->status_kehadiran)
                                        <div class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-400 flex-wrap ml-auto">
                                            <span class="font-semibold text-gray-800 dark:text-white">Rp {{ number_format($detail->nominal_harian_final, 0, ',', '.') }}</span>
                                            <span class="text-xs text-gray-500">{{ $detail->jam_kerja }} Jam</span>
                                            @if($alokasiNormal->count() > 0)
                                                <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">
                                                    {{ $alokasiNormal->count() }} Alokasi &middot; {{ $alokasiNormal->sum('jam_kerja') }} Jam
                                                </span>
                                            @else
                                                <span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full">Belum Dialokasikan</span>
                                            @endif
                                            @if($alokasiLembur->count() > 0)
                                                <span class="text-xs bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full">
                                                    Lembur {{ $alokasiLembur->sum('jam_kerja') }} Jam
                                                </span>
                                            @endif
                                            <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0"
                                                :class="open ? 'rotate-180' : '"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400 ml-auto">-</span>
                                    @endif
                                </button>

                                @if($detail->status_kehadiran)
                                    <div x-show="open" x-collapse class="mt-3 pl-2 space-y-3">
                                        {{-- Alokasi Normal --}}
                                        <div>
                                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">
                                                Alokasi Jam Normal ({{ $detail->jam_kerja }} Jam)
                                            </p>
                                            @if($alokasiNormal->isEmpty())
                                                <p class="text-xs text-red-500 italic">Belum ada alokasi normal.</p>
                                            @else
                                                <div class="space-y-1.5">
                                                    @foreach($alokasiNormal as $alokasi)
                                                        @php
                                                            $refLabel = match($alokasi->referensi_jenis) {
                                                                'pembangunan_unit'    => $unitLabels[$alokasi->referensi_id]['label'] ?? 'Unit #'.$alokasi->referensi_id,
                                                                'pembangunan_kawasan' => $kawasanLabels[$alokasi->referensi_id]['label'] ?? 'Kawasan #'.$alokasi->referensi_id,
                                                                'pembangunan_proyek'  => $proyekLabels[$alokasi->referensi_id]['label'] ?? 'Proyek #'.$alokasi->referensi_id,
                                                                default                => ucfirst(str_replace('_', ' ', $alokasi->referensi_jenis)).' #'.$alokasi->referensi_id,
                                                            };
                                                        @endphp
                                                        <div class="flex items-center justify-between bg-green-50 dark:bg-green-900/20 border border-green-100 dark:border-green-800 rounded-lg px-3 py-2 text-xs">
                                                            <span class="text-gray-700 dark:text-gray-300 font-medium">{{ $refLabel }}</span>
                                                            <span class="text-green-700 dark:text-green-400 font-semibold shrink-0 ml-3">{{ $alokasi->jam_kerja }} Jam</span>
                                                        </div>
                                                    @endforeach
                                                    @php $totalJamAlokasi = $alokasiNormal->sum('jam_kerja'); @endphp
                                                    @if($totalJamAlokasi !== $detail->jam_kerja)
                                                        <p class="text-xs text-red-500 mt-1">
                                                            ⚠ Total alokasi {{ $totalJamAlokasi }} Jam ≠ Jam kerja {{ $detail->jam_kerja }} Jam
                                                        </p>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Alokasi Lembur --}}
                                        @if($alokasiLembur->isNotEmpty())
                                            <div>
                                                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">
                                                    Lembur ({{ $alokasiLembur->sum('jam_kerja') }} Jam &middot; Rp {{ number_format($alokasiLembur->sum('subtotal'), 0, ',', '.') }})
                                                </p>
                                                <div class="space-y-1.5">
                                                    @foreach($alokasiLembur as $lembur)
                                                        @php
                                                            $refLabel = match($lembur->referensi_jenis) {
                                                                'pembangunan_unit'    => $unitLabels[$lembur->referensi_id]['label'] ?? 'Unit #'.$lembur->referensi_id,
                                                                'pembangunan_kawasan' => $kawasanLabels[$lembur->referensi_id]['label'] ?? 'Kawasan #'.$lembur->referensi_id,
                                                                'pembangunan_proyek'  => $proyekLabels[$lembur->referensi_id]['label'] ?? 'Proyek #'.$lembur->referensi_id,
                                                                default                => ucfirst(str_replace('_', ' ', $lembur->referensi_jenis)).' #'.$lembur->referensi_id,
                                                            };
                                                        @endphp
                                                        <div class="flex items-center justify-between bg-orange-50 dark:bg-orange-900/20 border border-orange-100 dark:border-orange-800 rounded-lg px-3 py-2 text-xs">
                                                            <span class="text-gray-700 dark:text-gray-300 font-medium">{{ $refLabel }}</span>
                                                            <div class="flex items-center gap-2 shrink-0 ml-3">
                                                                <span class="text-orange-600 dark:text-orange-400">{{ $lembur->jam_kerja }} Jam</span>
                                                                <span class="text-gray-400">&times;</span>
                                                                <span class="text-gray-600 dark:text-gray-400">Rp {{ number_format($lembur->tarif_per_jam, 0, ',', '.') }}/jam</span>
                                                                <span class="font-semibold text-orange-700 dark:text-orange-300">= Rp {{ number_format($lembur->subtotal, 0, ',', '.') }}</span>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Ringkasan per tukang --}}
                    <div class="px-5 py-3 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex flex-wrap gap-6 text-sm">
                            <div>
                                <span class="text-gray-500">Hari Masuk:</span>
                                <span class="font-semibold text-gray-900 dark:text-white ml-1">{{ $hariMasuk }} Hari</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Upah Normal:</span>
                                <span class="font-semibold text-gray-900 dark:text-white ml-1">Rp {{ number_format($upahNormal, 0, ',', '.') }}</span>
                            </div>
                            @if($totalLembur > 0)
                                <div>
                                    <span class="text-gray-500">Total Lembur:</span>
                                    <span class="font-semibold text-orange-600 ml-1">Rp {{ number_format($totalLembur, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            <div class="ml-auto">
                                <span class="text-gray-500">Total:</span>
                                <span class="font-bold text-blue-600 text-base ml-1">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ===== Grand Total Card ===== --}}
    @php
        $totalUpahAll   = $pengajuan->details->sum('nominal_harian_final');
        $totalLemburAll = $pengajuan->details->sum(fn($d) => $d->alokasi->where('jenis', 'lembur')->sum('subtotal'));
        $grandTotalAll  = $totalUpahAll + $totalLemburAll;
        $totalTukang    = $detailPerTukang->count();
        $totalHariPeriode = \Carbon\Carbon::parse($pengajuan->tanggal_mulai)->diffInDays(\Carbon\Carbon::parse($pengajuan->tanggal_selesai)) + 1;
    @endphp
    <div class="rounded-2xl border border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-900/20 mb-5">
        <div class="px-5 py-4 sm:px-6 border-b border-blue-100 dark:border-blue-800">
            <h3 class="text-base font-semibold text-blue-800 dark:text-blue-200">Ringkasan Total</h3>
        </div>
        <div class="px-5 py-5 sm:px-6">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                    <p class="text-xs text-gray-500 mb-1">Jumlah Tukang</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $totalTukang }} Orang</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                    <p class="text-xs text-gray-500 mb-1">Hari Periode</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $totalHariPeriode }} Hari</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                    <p class="text-xs text-gray-500 mb-1">Upah Normal</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white">Rp {{ number_format($totalUpahAll, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-orange-100 dark:border-orange-800">
                    <p class="text-xs text-gray-500 mb-1">Total Lembur</p>
                    <p class="text-lg font-bold text-orange-600">Rp {{ number_format($totalLemburAll, 0, ',', '.') }}</p>
                </div>
            </div>
            <div class="mt-4 bg-white dark:bg-gray-800 rounded-xl p-4 border border-blue-200 dark:border-blue-700 text-center">
                <p class="text-sm text-blue-600 dark:text-blue-400 mb-1">Grand Total</p>
                <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">Rp {{ number_format($grandTotalAll, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    {{-- ===== Tombol Kembali & Aksi ===== --}}
    <div class="flex items-center gap-3">
        <a href="{{ $isAbm ? route('gudang.pengajuanUpahHarianTukang.index') : route('gudang.pengajuanUpahHarianTukang.indexMangoon') }}"
            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 dark:text-white dark:bg-gray-700 dark:hover:bg-gray-600 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali ke Daftar
        </a>

        @if($pengajuan->status === 'diajukan')
            @php
                $canCancel = $isAbm
                    ? auth()->user()->can('gudang.upah-harian-tukang.upah-abm.batalkan-pengajuan')
                    : auth()->user()->can('gudang.upah-harian-tukang.upah-mangoon.batalkan-pengajuan');
            @endphp

            @if($canCancel)
            <form id="cancelForm" action="{{ $isAbm 
                    ? route('gudang.pengajuanUpahHarianTukang.cancel', $pengajuan->id) 
                    : route('gudang.pengajuanUpahHarianTukang.cancelMangoon', $pengajuan->id) }}" 
                  method="POST" 
                  class="inline-block">
                @csrf
                <button type="button" onclick="confirmCancel()"
                    class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:ring-red-300 transition-colors active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Batalkan Pengajuan
                </button>
            </form>
            @endif
        @endif
    </div>

</div>

<script>
    function confirmCancel() {
        Swal.fire({
            title: 'Batalkan Pengajuan?',
            html: 'Apakah Anda yakin ingin membatalkan pengajuan ini?<br><span class="text-xs text-gray-500">Status akan dikembalikan ke Draft.</span>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Batalkan!',
            cancelButtonText: 'Kembali',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('cancelForm').submit();
            }
        });
    }
</script>
@endsection
