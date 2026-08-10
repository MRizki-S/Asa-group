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
                    Detail Pengajuan Upah Harian Tukang
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $pengajuan->nomor_upah_harian }}</p>
            </div>

            {{-- Header Actions --}}
            <div class="flex flex-wrap items-center gap-2">
                {{-- Export Excel Button --}}
                @can('keuangan.upah-harian-tukang.daftar-pengajuan.export-excel')
                <button type="button"
                    id="btn-export-excel"
                    onclick="handleExportExcel()"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-green-700 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 dark:bg-green-900/30 dark:text-green-300 dark:border-green-700 dark:hover:bg-green-900/50 transition-colors focus:outline-none focus:ring-2 focus:ring-green-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Export Excel
                </button>
                @endcan

                @php
                $statusConfig = match($pengajuan->status) {
                'draft' => ['label' => 'Draft', 'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'],
                'diajukan' => ['label' => 'Diajukan', 'class' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-800 dark:text-yellow-200'],
                'disetujui' => ['label' => 'Disetujui', 'class' => 'bg-green-100 text-green-700 dark:bg-green-800 dark:text-green-200'],
                'ditolak' => ['label' => 'Ditolak', 'class' => 'bg-red-100 text-red-700 dark:bg-red-800 dark:text-red-200'],
                default => ['label' => ucfirst($pengajuan->status), 'class' => 'bg-gray-100 text-gray-600'],
                };
                @endphp
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold {{ $statusConfig['class'] }}">
                    {{ $statusConfig['label'] }}
                </span>
            </div>
        </div>

        {{-- Meta Info --}}
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
                    <p class="text-gray-500 dark:text-gray-400 text-xs mb-0.5">Referensi</p>
                    @if($pengajuan->jenis_referensi === 'perumahan')
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300">ABM / Perumahan</span>
                    @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-teal-100 text-teal-700 dark:bg-teal-900 dark:text-teal-300">Mangoon</span>
                    @endif
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-xs mb-0.5">Dibuat Oleh</p>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $pengajuan->createdBy->nama_lengkap ?? $pengajuan->createdBy->name ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Detail Per Tukang (Accordion + Bon Input) ===== --}}
    <div class="space-y-3 mb-5" x-data="{ openIdx: 0 }">
        @foreach($detailPerTukang as $idx => $row)
        @php
        $tukang = $row['tukang'];
        $details = $row['details'];
        $tukangId = $tukang->id ?? $idx;
        $hariMasuk = $details->where('status_kehadiran', true)->count();
        $upahNormal = $details->sum('nominal_harian_final');
        $totalLembur = $details->sum(fn($d) => $d->alokasi->where('jenis', 'lembur')->sum('subtotal'));
        $grandTotal = $upahNormal + $totalLembur;
        @endphp

        {{-- Alpine component per tukang untuk bon & sisa upah --}}
        <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden"
            x-data="bonCalc({{ $grandTotal }}, 'tukang_{{ $tukangId }}', {{ $row['bon'] ?? 0 }}, {{ $tukangId }}, '{{ route('keuangan.daftarUpahHarian.updateBon', $pengajuan->id) }}')"
            x-init="init()">

            {{-- Accordion Header --}}
            <button type="button" @click="openIdx = openIdx === {{ $idx }} ? null : {{ $idx }}"
                class="w-full flex items-center justify-between px-4 py-3.5 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-left gap-2">
                {{-- Kiri: Nama + badge hari masuk --}}
                <div class="flex items-center gap-2 flex-wrap min-w-0">
                    <span class="text-sm font-semibold text-gray-800 dark:text-white truncate">{{ $tukang->nama_tukang ?? '-' }}</span>
                    @if($tukang->kode ?? false)
                    <span class="text-xs text-gray-400 font-mono hidden sm:inline">{{ $tukang->kode }}</span>
                    @endif
                    <span class="text-xs bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300 px-2 py-0.5 rounded-full shrink-0">
                        {{ $hariMasuk }} hari
                    </span>
                </div>
                {{-- Kanan: Info ringkas + chevron --}}
                <div class="flex items-center gap-2 shrink-0">
                    {{-- Total & Sisa — hanya tampil di sm ke atas --}}
                    <div class="hidden sm:flex items-center gap-2 text-xs">
                        <span class="text-gray-400">Total:</span>
                        <span class="font-semibold text-gray-700 dark:text-gray-300">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                        <span class="text-gray-300 dark:text-gray-600">|</span>
                        <span class="text-gray-400">Sisa:</span>
                        <span class="font-semibold" :class="sisa >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                            x-text="'Rp ' + formatRupiah(sisa)"></span>
                    </div>
                    {{-- Sisa saja untuk mobile --}}
                    <div class="flex sm:hidden items-center text-xs">
                        <span class="font-semibold" :class="sisa >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                            x-text="'Rp ' + formatRupiah(sisa)"></span>
                    </div>
                    <svg class="w-4 h-4 text-gray-500 transition-transform duration-200 shrink-0"
                        :class="openIdx === {{ $idx }} ? 'rotate-180' : ''"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </button>

            {{-- Accordion Body --}}
            <div x-show="openIdx === {{ $idx }}" x-collapse>

                {{-- Info snapshot tukang --}}
                <div class="px-4 py-3 bg-blue-50 dark:bg-blue-900/20 border-b border-blue-100 dark:border-blue-800 flex flex-wrap gap-4 sm:gap-6 text-sm">
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

                    <div x-data="{ open: {{ $detail->status_kehadiran && $detail->alokasi->isNotEmpty() ? 'true' : 'false' }} }" class="px-4 py-3">
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
                                    {{ $alokasiNormal->count() }} Alokasi
                                </span>
                                @else
                                <span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full">Belum Dialokasikan</span>
                                @endif
                                @if($alokasiLembur->count() > 0)
                                <span class="text-xs bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full">
                                    Lembur {{ $alokasiLembur->sum('subtotal') > 0 ? 'Rp '.number_format($alokasiLembur->sum('subtotal'), 0, ',', '.') : '' }}
                                </span>
                                @endif
                                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0"
                                    :class="open ? 'rotate-180' : ''"
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
                                            default               => ucfirst(str_replace('_', ' ', $alokasi->referensi_jenis ?? '')).' #'.$alokasi->referensi_id,
                                        };
                                    @endphp
                                    <div class="flex items-center justify-between bg-green-50 dark:bg-green-900/20 border border-green-100 dark:border-green-800 rounded-lg px-3 py-2 text-xs">
                                        <span class="text-gray-700 dark:text-gray-300 font-medium">{{ $refLabel }}</span>
                                        <span class="text-green-700 dark:text-green-400 font-semibold shrink-0 ml-3">{{ $alokasi->jam_kerja ?? 0 }} Jam</span>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>

                            {{-- Alokasi Lembur --}}
                            @if($alokasiLembur->isNotEmpty())
                            <div>
                                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">
                                    Lembur (Rp {{ number_format($alokasiLembur->sum('subtotal'), 0, ',', '.') }})
                                </p>
                                <div class="space-y-1.5">
                                    @foreach($alokasiLembur as $lembur)
                                    @php
                                        $refLabel = match($lembur->referensi_jenis) {
                                            'pembangunan_unit'    => $unitLabels[$lembur->referensi_id]['label'] ?? 'Unit #'.$lembur->referensi_id,
                                            'pembangunan_kawasan' => $kawasanLabels[$lembur->referensi_id]['label'] ?? 'Kawasan #'.$lembur->referensi_id,
                                            'pembangunan_proyek'  => $proyekLabels[$lembur->referensi_id]['label'] ?? 'Proyek #'.$lembur->referensi_id,
                                            default               => ucfirst(str_replace('_', ' ', $lembur->referensi_jenis ?? '')).' #'.$lembur->referensi_id,
                                        };
                                    @endphp
                                    <div class="flex items-center justify-between bg-orange-50 dark:bg-orange-900/20 border border-orange-100 dark:border-orange-800 rounded-lg px-3 py-2 text-xs">
                                        <span class="text-gray-700 dark:text-gray-300 font-medium">{{ $refLabel }}</span>
                                        <span class="font-semibold text-orange-700 dark:text-orange-300">Rp {{ number_format($lembur->subtotal ?? 0, 0, ',', '.') }}</span>
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

                {{-- ===== Ringkasan Per Tukang + BON INPUT + SISA UPAH ===== --}}
                <div class="px-4 py-4 bg-gray-50 dark:bg-gray-800/60 border-t border-gray-200 dark:border-gray-700">

                    {{-- Baris ringkasan upah — 2-col grid di mobile, flex-wrap di sm+ --}}
                    <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-x-6 gap-y-2 text-sm mb-4">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Hari Masuk</p>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $hariMasuk }} Hari</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Upah Normal</p>
                            <p class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($upahNormal, 0, ',', '.') }}</p>
                        </div>
                        @if($totalLembur > 0)
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Lembur</p>
                            <p class="font-semibold text-orange-600 dark:text-orange-400">Rp {{ number_format($totalLembur, 0, ',', '.') }}</p>
                        </div>
                        @endif
                        <div class="col-span-2 sm:col-span-1 sm:ml-auto">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Total Upah</p>
                            <p class="font-bold text-blue-600 dark:text-blue-400 text-base">Rp {{ number_format($grandTotal, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    {{-- Divider --}}
                    <div class="border-t border-dashed border-gray-300 dark:border-gray-600 my-3"></div>

                    {{-- BON Input + Sisa Upah — Stack di mobile, side-by-side di sm+ --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        {{-- Input Bon --}}
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">
                                    <span class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                                        </svg>
                                        Bon / Kasbon (Rp)
                                    </span>
                                </label>
                                @can('keuangan.upah-harian-tukang.daftar-pengajuan.input-bon')
                                <div class="text-xs font-medium">
                                    <template x-if="saveStatus === 'saving'">
                                        <span class="text-blue-500 flex items-center gap-1">
                                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                            Menyimpan...
                                        </span>
                                    </template>
                                    <template x-if="saveStatus === 'saved'">
                                        <span class="text-green-600 dark:text-green-400 flex items-center gap-1">
                                            ✓ Tersimpan
                                        </span>
                                    </template>
                                    <template x-if="saveStatus === 'failed'">
                                        <span class="text-red-500 flex items-center gap-1">
                                            ⚠ Gagal menyimpan
                                        </span>
                                    </template>
                                </div>
                                @endcan
                            </div>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-500 dark:text-gray-400 font-medium pointer-events-none">Rp</span>
                                @can('keuangan.upah-harian-tukang.daftar-pengajuan.input-bon')
                                <input
                                    type="text"
                                    id="bon_{{ $tukangId }}"
                                    name="bon[{{ $tukangId }}]"
                                    x-model="bonDisplay"
                                    @input="onBonInput($event)"
                                    @focus="$event.target.select()"
                                    placeholder="0"
                                    class="w-full pl-9 pr-3 py-2 text-sm border rounded-lg bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition-colors" />
                                @else
                                <input
                                    type="text"
                                    id="bon_{{ $tukangId }}"
                                    name="bon[{{ $tukangId }}]"
                                    x-model="bonDisplay"
                                    disabled
                                    class="w-full pl-9 pr-3 py-2 text-sm border rounded-lg bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed placeholder-gray-400 focus:outline-none" />
                                @endcan
                            </div>
                        </div>

                        {{-- Sisa Upah Display --}}
                        <div class="flex flex-col justify-end">
                            <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                Sisa Upah Diterima
                            </p>
                            <div class="px-3 py-2 rounded-lg border text-sm font-bold transition-colors"
                                :class="sisa >= 0
                                        ? 'bg-green-50 border-green-200 text-green-700 dark:bg-green-900/30 dark:border-green-700 dark:text-green-300'
                                        : 'bg-red-50 border-red-200 text-red-700 dark:bg-red-900/30 dark:border-red-700 dark:text-red-300'">
                                <span x-text="'Rp ' + formatRupiah(sisa)"></span>
                                <span class="text-xs font-normal ml-1 opacity-70"
                                    x-show="bon > 0"
                                    x-text="sisa < 0 ? '(Kelebihan bon)' : ''"></span>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- ===== End Ringkasan Per Tukang ===== --}}

            </div>
        </div>
        @endforeach
    </div>

    {{-- ===== Grand Total + Total Bon + Total Sisa ===== --}}
    @php
    $totalUpahAll = $pengajuan->details->sum('nominal_harian_final');
    $totalLemburAll = $pengajuan->details->sum(fn($d) => $d->alokasi->where('jenis', 'lembur')->sum('subtotal'));
    $grandTotalAll = $totalUpahAll + $totalLemburAll;
    $totalTukang = $detailPerTukang->count();
    $totalHariPeriode = \Carbon\Carbon::parse($pengajuan->tanggal_mulai)->diffInDays(\Carbon\Carbon::parse($pengajuan->tanggal_selesai)) + 1;
    @endphp

    <div class="rounded-2xl border border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-900/20 mb-5"
        x-data="grandSummary()">
        {{-- Card header --}}
        <div class="px-4 py-3 sm:px-6 sm:py-4 border-b border-blue-100 dark:border-blue-800">
            <div class="flex flex-wrap items-center justify-between gap-1">
                <h3 class="text-sm sm:text-base font-semibold text-blue-800 dark:text-blue-200">Ringkasan Total Keseluruhan</h3>
                <span class="text-xs text-blue-500 dark:text-blue-400 italic hidden sm:inline">*Sisa upah dihitung otomatis dari input bon di atas</span>
            </div>
            <p class="text-xs text-blue-400 dark:text-blue-500 italic mt-0.5 sm:hidden">*Sisa dihitung dari bon di atas</p>
        </div>

        <div class="px-4 py-4 sm:px-6 sm:py-5 space-y-3">
            {{-- Stats Grid — 2 col mobile, 4 col sm+ --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="bg-white dark:bg-gray-800 rounded-xl p-3 sm:p-4 border border-gray-100 dark:border-gray-700 text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Jumlah Tukang</p>
                    <p class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white">{{ $totalTukang }} <span class="text-xs sm:text-sm font-normal text-gray-500">Org</span></p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-3 sm:p-4 border border-gray-100 dark:border-gray-700 text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Hari Periode</p>
                    <p class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white">{{ $totalHariPeriode }} <span class="text-xs sm:text-sm font-normal text-gray-500">Hari</span></p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-3 sm:p-4 border border-gray-100 dark:border-gray-700 text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Upah Normal</p>
                    <p class="text-sm sm:text-lg font-bold text-gray-900 dark:text-white leading-tight">Rp {{ number_format($totalUpahAll, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-3 sm:p-4 border border-orange-100 dark:border-orange-900/40 text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Total Lembur</p>
                    <p class="text-sm sm:text-lg font-bold text-orange-600 dark:text-orange-400 leading-tight">Rp {{ number_format($totalLemburAll, 0, ',', '.') }}</p>
                </div>
            </div>

            {{-- Grand Total + Bon + Sisa — 1 row inline on desktop, wraps on mobile --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-300 dark:border-gray-600 flex flex-col sm:flex-row overflow-hidden">

                {{-- Grand Total --}}
                <div class="flex-1 px-6 py-5 flex sm:flex-col items-center justify-between sm:justify-center sm:text-center gap-2 border-b sm:border-b-0 sm:border-r border-gray-300 dark:border-gray-600">
                    <p class="text-[11px] text-blue-500 dark:text-blue-400 font-semibold uppercase tracking-widest">
                        Grand Total Upah
                    </p>

                    <p class="text-2xl font-bold text-blue-700 dark:text-blue-300 leading-none">
                        Rp {{ number_format($grandTotalAll, 0, ',', '.') }}
                    </p>
                </div>

                {{-- Total Bon --}}
                <div class="flex-1 px-6 py-5 flex sm:flex-col items-center justify-between sm:justify-center sm:text-center gap-2 border-b sm:border-b-0 sm:border-r border-gray-300 dark:border-gray-600">
                    <p class="text-[11px] text-amber-500 dark:text-amber-400 font-semibold uppercase tracking-widest">
                        Total Bon / Kasbon
                    </p>

                    <p class="text-2xl font-bold text-amber-700 dark:text-amber-300 leading-none"
                        x-text="'Rp ' + formatRupiah(totalBon)">
                        Rp 0
                    </p>
                </div>

                {{-- Total Sisa --}}
                <div
                    class="flex-1 px-6 py-5 flex sm:flex-col items-center justify-between sm:justify-center sm:text-center gap-2 transition-colors"
                    :class="totalSisa >= 0 ? 'bg-green-50/40 dark:bg-green-900/10' : 'bg-red-50/40 dark:bg-red-900/10'">

                    <p class="text-[11px] font-semibold uppercase tracking-widest"
                        :class="totalSisa >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                        Total Sisa Upah
                    </p>

                    <p class="text-2xl font-bold leading-none"
                        :class="totalSisa >= 0 ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300'"
                        x-text="'Rp ' + formatRupiah(totalSisa)">
                        Rp {{ number_format($grandTotalAll, 0, ',', '.') }}
                    </p>
                </div>

            </div>
        </div>
    </div>

    {{-- ===== Action Bar ===== --}}
    <div class="flex flex-wrap items-center justify-between gap-3 pt-2 pb-4">
        {{-- Kiri: Tombol Kembali --}}
        <a href="{{ route('keuangan.daftarUpahHarian.index') }}"
            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:text-white dark:bg-gray-800 dark:border-gray-600 dark:hover:bg-gray-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali ke Daftar
        </a>

        {{-- Kanan: Export & ACC --}}
        <div class="flex items-center gap-2">

            {{-- ACC Pengajuan --}}
            @if($pengajuan->status === 'diajukan')
            @can('keuangan.upah-harian-tukang.daftar-pengajuan.aksi')
            <form id="accForm" action="{{ route('keuangan.daftarUpahHarian.accPengajuan', $pengajuan->nomor_upah_harian) }}" method="POST" class="inline-block">
                @csrf
                @method('PATCH')
                <button type="button"
                    id="btn-acc-pengajuan"
                    onclick="confirmAcc()"
                    class="inline-flex items-center gap-2
                    px-5 py-2.5
                    rounded-lg
                    bg-green-600
                    hover:bg-green-700
                    border border-green-700
                    text-white
                    font-semibold
                    shadow-md
                    hover:-translate-y-0.5
                    transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    ACC Pengajuan
                </button>
            </form>
            @endcan
            @else
            <div class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-200 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-gray-500 cursor-not-allowed">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                {{ $pengajuan->status === 'disetujui' ? 'Sudah Disetujui' : 'ACC Pengajuan' }}
            </div>
            @endif
        </div>
    </div>

</div>

<script>
    // ========================
    // Alpine: Per-Tukang Bon
    // ========================
    function bonCalc(totalUpah, storageKey, initialBon, tukangId, saveUrl) {
        return {
            totalUpah: totalUpah,
            bon: initialBon,
            bonDisplay: '',
            sisa: totalUpah - initialBon,
            tukangId: tukangId,
            saveUrl: saveUrl,
            saveStatus: '', // 'saving', 'saved', 'failed'
            saveTimeout: null,

            init() {
                this.bonDisplay = this.bon > 0 ? this.formatRupiah(this.bon) : '';

                // Watch bon changes → update sisa, grand summary, and trigger auto save
                this.$watch('bon', (newVal) => {
                    this.sisa = this.totalUpah - newVal;
                    window.dispatchEvent(new CustomEvent('bon-updated'));
                    this.debouncedSave();
                });
            },

            onBonInput(event) {
                const raw = event.target.value.replace(/\D/g, '');
                this.bon = parseInt(raw) || 0;
                this.bonDisplay = raw.length > 0 ? this.formatRupiah(this.bon) : '';
                // Set cursor to the end
                this.$nextTick(() => {
                    const el = event.target;
                    el.setSelectionRange(el.value.length, el.value.length);
                });
            },

            debouncedSave() {
                this.saveStatus = 'saving';
                if (this.saveTimeout) {
                    clearTimeout(this.saveTimeout);
                }
                this.saveTimeout = setTimeout(() => {
                    this.saveBon();
                }, 1000);
            },

            saveBon() {
                fetch(this.saveUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            tukang_id: this.tukangId,
                            bon: this.bon
                        })
                    })
                    .then(res => {
                        if (!res.ok) throw new Error('Network response was not ok');
                        return res.json();
                    })
                    .then(data => {
                        if (data.success) {
                            this.saveStatus = 'saved';
                            // Auto clear saved status after 3 seconds
                            setTimeout(() => {
                                if (this.saveStatus === 'saved') {
                                    this.saveStatus = '';
                                }
                            }, 3000);
                        } else {
                            this.saveStatus = 'failed';
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        this.saveStatus = 'failed';
                    });
            },

            formatRupiah(val) {
                return Math.abs(val).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }
        };
    }

    // ========================
    // Alpine: Grand Summary
    // ========================
    function grandSummary() {
        return {
            grandTotal: {{ $grandTotalAll }},
            totalBon: 0,
            totalSisa: {{ $grandTotalAll }},

            init() {
                this.recalculate();
                window.addEventListener('bon-updated', () => this.recalculate());
            },

            recalculate() {
                let sumBon = 0;
                // Ambil semua input bon yang ada di halaman
                document.querySelectorAll('input[name^="bon["]').forEach(input => {
                    const raw = input.value.replace(/\D/g, '');
                    sumBon += parseInt(raw) || 0;
                });
                this.totalBon = sumBon;
                this.totalSisa = this.grandTotal - sumBon;
            },

            formatRupiah(val) {
                const sign = val < 0 ? '-' : '';
                return sign + Math.abs(val).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }
        };
    }

    // ========================
    // Export Excel Handler
    // ========================
    function handleExportExcel() {
        window.location.href = "{{ route('keuangan.daftarUpahHarian.exportExcel') }}?id={{ $pengajuan->id }}";
    }

    // ========================
    // ACC Pengajuan Handler (UI only)
    // ========================
    function confirmAcc() {
        Swal.fire({
            title: 'ACC Pengajuan?',
            html: `
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Apakah Anda yakin ingin menyetujui pengajuan upah harian ini?
                </p>
                <p class="text-xs text-gray-400 mt-1">Status akan berubah menjadi <strong>Disetujui</strong>.</p>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#16a34a',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, ACC!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('accForm').submit();
            }
        });
    }
</script>
@endsection