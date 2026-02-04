@extends('layouts.app')

@section('pageActive', 'SettingPPJB')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        {{-- Breadcrumb --}}
        <div x-data="{ pageName: 'SettingPPJB' }">
            @include('partials.breadcrumb')
        </div>


        {{-- Tombol Back --}}
        <div class="mb-4 flex justify-end">
            <a href="{{ route('settingPPJB.bonusKpr.edit') }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-md
              bg-blue-600 text-white hover:bg-blue-700
              dark:bg-blue-700 dark:hover:bg-blue-600">
                <svg class="w-5 h-5" aria-hidden="true" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M5 12h14M5 12l4-4m-4 4 4 4" />
                </svg>
                Kembali
            </a>
        </div>



        {{-- === Bonus KPR Non-Aktif === --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
            <div class="px-5 py-4 sm:px-6 sm:py-5">

                {{-- Header --}}
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        Bonus KPR Non-Aktif
                    </h3>
                </div>

                @if ($nonAktif->isEmpty())
                    <div class="mb-4">
                        <div
                            class="rounded-xl border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/20 p-4 text-center">
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                Belum ada Bonus KPR non-aktif.
                            </p>
                        </div>
                    </div>
                @else
                    @foreach ($nonAktif as $batch)
                        <div class="mb-4">
                            <div
                                class="rounded-xl border border-gray-300 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/20 p-4 flex flex-col">

                                {{-- Header tiap batch --}}
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="font-semibold text-gray-800 dark:text-gray-200 text-lg">
                                        Bonus KPR PPJB – Non-Aktif
                                    </h4>
                                    <span
                                        class="px-2 py-0.5 text-xs rounded bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                        Non-Aktif
                                    </span>
                                </div>

                                {{-- List Items --}}
                                <ul class="space-y-1 text-gray-700 dark:text-gray-300 text-sm">
                                    @foreach ($batch->items as $item)
                                        <li class="flex items-center justify-between">
                                            <span>{{ $item->nama_bonus }}</span>
                                        </li>
                                    @endforeach
                                </ul>

                                {{-- Info Tambahan --}}
                                <div class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                                    Disetujui oleh <strong>{{ $batch->penyetuju->username ?? '-' }}</strong>
                                    &middot; Diajukan {{ $batch->tanggal_pengajuan?->format('d M Y') ?? '-' }}
                                    &middot; Nonaktif sejak {{ $batch->updated_at->format('d M Y') }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif

            </div>
        </div>

        {{-- ===Bonus KPR Ditolak === --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
            <div class="px-5 py-4 sm:px-6 sm:py-5">

                {{-- Header --}}
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-medium text-red-800 dark:text-red-800">
                        Bonus KPR Ditolak
                    </h3>
                </div>

                @if ($ditolak->isEmpty())
                    <div class="mb-4">
                        <div
                            class="rounded-xl border border-red-200 bg-red-50 dark:border-red-700 dark:bg-red-900/20 p-4 text-center">
                            <p class="text-sm text-red-600 dark:text-red-300">
                                Belum ada Bonus KPR yang ditolak.
                            </p>
                        </div>
                    </div>
                @else
                    @foreach ($ditolak as $batch)
                        <div class="mb-4">
                            <div
                                class="rounded-xl border border-red-300 bg-red-50 dark:border-red-700 dark:bg-red-900/20 p-4 flex flex-col">

                                {{-- Header tiap batch --}}
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="font-semibold text-red-800 dark:text-red-300 text-lg">
                                        Bonus KPR PPJB – Ditolak
                                    </h4>
                                    <span
                                        class="px-2 py-0.5 text-xs rounded bg-red-100 text-red-700 dark:bg-red-800 dark:text-red-100">
                                        Ditolak
                                    </span>
                                </div>

                                {{-- List Items --}}
                                <ul class="space-y-1 text-red-900 dark:text-red-200 text-sm">
                                    @foreach ($batch->items as $item)
                                        <li class="flex items-center justify-between">
                                            <span>{{ $item->nama_bonus }}</span>
                                        </li>
                                    @endforeach
                                </ul>

                                {{-- Info Tambahan --}}
                                <div class="mt-3 text-sm text-red-700 dark:text-red-300">
                                    Diajukan oleh <strong>{{ $batch->pengaju->username ?? '-' }}</strong>
                                    pada {{ $batch->tanggal_pengajuan?->format('d M Y') ?? '-' }}
                                    &middot; Ditolak pada {{ $batch->updated_at->format('d M Y') }}
                                    @if (!empty($batch->catatan_penolakan))
                                        <br><span class="italic">Alasan: {{ $batch->catatan_penolakan }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif


            </div>
        </div>



    </div>

@endsection
