@extends('layouts.app')

@section('pageActive', 'pembangunanUnit')

@section('content')
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">
    <!-- ===== Main Content Start ===== -->
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: 'Pembangunan Unit' }">
            @include('partials.breadcrumb')
        </div>
        <!-- Breadcrumb End -->

        {{-- Alert Error Validasi --}}
        @if ($errors->any())
            <div class="flex p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400"
                role="alert">
                <svg class="shrink-0 inline w-4 h-4 me-3 mt-[2px]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                    fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                </svg>
                <span class="sr-only">Danger</span>
                <div>
                    <span class="font-medium">Terjadi kesalahan validasi:</span>
                    <ul class="mt-1.5 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="space-y-5 sm:space-y-6">
            <div
                class="rounded-2xl border border-gray-200 px-5 py-4 sm:px-6 sm:py-5 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

                {{-- Header --}}
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        List Pembangunan Unit
                        {{ $perumahaanSlug ? ' - ' . ucwords(str_replace('-', ' ', $perumahaanSlug)) : '' }}
                    </h3>
                </div>

                {{-- Filter --}}
                <form method="GET" action="{{ route('produksi.pembangunanUnit.index') }}"
                    class="mb-4"
                    x-data="{
                        tahap: [],
                        async fetchTahap() {
                            const res = await fetch(`/etalase/perumahaan/{{ $perumahaanSlug }}/tahap-json`);
                            if (!res.ok) return;
                            this.tahap = await res.json();
                        }
                    }" x-init="fetchTahap()">

                    {{-- Mobile: compact grid layout --}}
                    <div class="block sm:hidden">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            <span class="text-xs font-medium text-gray-500">Filter</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mb-2">
                            <!-- Select Tahap -->
                            <div>
                                <select name="tahapFil" id="selectTahapMobile"
                                    class="w-full bg-gray-50 border text-gray-900 text-xs rounded-lg p-2
                                    dark:bg-gray-600 dark:text-white">
                                    <option value="">Semua Tahap</option>
                                    <template x-for="t in tahap" :key="t.id">
                                        <option :value="t.slug" :selected="t.slug === '{{ $tahapSlug }}'"
                                            x-text="t.nama_tahap">
                                        </option>
                                    </template>
                                </select>
                            </div>
                            @unless(auth()->user()->hasRole(['STAF MUTU & LAYANAN KONSUMEN (ADL)', 'STAF MUTU & LAYANAN KONSUMEN (LHR)']))
                            <!-- Select Status -->
                            <div>
                                <select name="statusFil" id="selectStatusFilMobile"
                                    class="w-full bg-gray-50 border text-gray-900 text-xs rounded-lg p-2 dark:bg-gray-600 dark:text-white">
                                    <option value="all" {{ ($statusFil ?? 'all') === 'all' ? 'selected' : '' }}>Semua Status</option>
                                    <option value="proses" {{ ($statusFil ?? '') === 'proses' ? 'selected' : '' }}>Proses</option>
                                    <option value="selesai" {{ ($statusFil ?? '') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                                </select>
                            </div>
                            @endunless
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="submit"
                                class="px-3 py-2 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 text-center transition-colors">
                                Terapkan
                            </button>
                            <a href="{{ route('produksi.pembangunanUnit.index') }}"
                                class="px-3 py-2 text-xs font-medium bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 text-center transition-colors">
                                Reset
                            </a>
                        </div>
                    </div>

                    {{-- Desktop: original flex layout --}}
                    <div class="hidden sm:flex flex-wrap items-center gap-3">
                        <h3 class="text-sm text-gray-500 dark:text-white/90">Filter -</h3>

                        <!-- Select Tahap -->
                        <div class="w-64">
                            <select name="tahapFil" id="selectTahap"
                                class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                                dark:bg-gray-600 dark:text-white">
                                <option value="">Semua Tahap</option>
                                <template x-for="t in tahap" :key="t.id">
                                    <option :value="t.slug" :selected="t.slug === '{{ $tahapSlug }}'"
                                        x-text="t.nama_tahap">
                                    </option>
                                </template>
                            </select>
                            <script>
                                $(document).ready(function() {
                                    $('#selectTahap').select2({
                                        placeholder: "Semua Tahap",
                                        theme: 'bootstrap4',
                                        allowClear: true,
                                        width: '100%'
                                    });
                                });
                            </script>
                        </div>

                        @unless(auth()->user()->hasRole(['STAF MUTU & LAYANAN KONSUMEN (ADL)', 'STAF MUTU & LAYANAN KONSUMEN (LHR)']))
                            <!-- Select Status Pembangunan -->
                            <div class="w-64">
                                <select name="statusFil" id="selectStatusFil"
                                    class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-600 dark:text-white">
                                    <option value="all" {{ ($statusFil ?? 'all') === 'all' ? 'selected' : '' }}>Semua Status</option>
                                    <option value="proses" {{ ($statusFil ?? '') === 'proses' ? 'selected' : '' }}>Proses</option>
                                    <option value="selesai" {{ ($statusFil ?? '') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                                </select>
                                <script>
                                    $(document).ready(function() {
                                        $('#selectStatusFil').select2({
                                            placeholder: "Semua Status",
                                            theme: 'bootstrap4',
                                            allowClear: false,
                                            width: '100%'
                                        });
                                    });
                                </script>
                            </div>
                        @endunless

                        <div class="flex gap-2">
                            <button type="submit"
                                class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700 text-center">
                                Terapkan
                            </button>
                            <a href="{{ route('produksi.pembangunanUnit.index') }}"
                                class="px-4 py-2 text-sm bg-gray-200 rounded-lg hover:bg-gray-300 text-center">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>

                <!-- Desktop Table View -->
                <div class="hidden md:block overflow-x-auto">
                    <table id="table-pembangunan-unit" class="w-full">
                        <thead>
                            <tr>
                                <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Unit</th>
                                <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                                    Tahap</th>
                                <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                                    RAP Acuan</th>
                                    <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                                        Progres & Status</th>
                                    <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                                        Pengawas</th>
                                    <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                                        Subcon</th>
                                <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                                    Serah Terima</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($allPembangunanUnit as $item)
                                <tr>
                                    <td class="font-bold text-gray-900 whitespace-nowrap dark:text-white text-center">
                                        {{ $item->unit->nama_unit }}
                                    </td>
                                    <td class="font-medium text-gray-600 whitespace-nowrap dark:text-gray-400 text-center">
                                        {{ $item->tahap->nama_tahap }}
                                    </td>
                                    <td class="font-medium text-gray-600 whitespace-nowrap dark:text-gray-400 text-center">
                                        {{ $item->qcContainer->nama_container }}
                                    </td>
                                    <td class="p-0 text-center" style="padding: 0 !important;">
                                        @php
                                            $bgClass = 'bg-blue-500 hover:bg-blue-600';
                                            $textClass = 'text-white';
                                            $statusIcon = '';

                                            if ($item->status_pembangunan === 'selesai') {
                                                $bgClass = 'bg-green-500 hover:bg-green-600';
                                                $statusIcon = '<i class="fa-solid fa-circle-check mr-1"></i>';
                                            } elseif ($item->status_pembangunan === 'selesai dengan catatan') {
                                                $bgClass = 'bg-yellow-500 hover:bg-yellow-600';
                                                $textClass = 'text-yellow-950';
                                                $statusIcon =
                                                    '<i class="fa-solid fa-circle-exclamation mr-1"></i>';
                                            }
                                        @endphp

                                        @can('produksi.properti.pembangunan-unit.detail')
                                        <a href="{{ route('produksi.pembangunanUnit.show', $item->id) }}"
                                            class="flex flex-col items-center justify-center w-full h-full min-h-[50px] {{ $bgClass }} {{ $textClass }} transition-all group">
                                            <span class="text-sm font-black">{!! $statusIcon !!}
                                                {{ $item->total_progres }}%</span>
                                            <span
                                                class="text-[9px] uppercase font-bold opacity-80 group-hover:opacity-100">
                                                {{ $item->status_pembangunan }}
                                            </span>
                                        </a>
                                        @else
                                        <div class="flex flex-col items-center justify-center w-full h-full min-h-[50px] {{ $bgClass }} {{ $textClass }}">
                                            <span class="text-sm font-black">{!! $statusIcon !!}
                                                {{ $item->total_progres }}%</span>
                                            <span class="text-[9px] uppercase font-bold opacity-80">
                                                {{ $item->status_pembangunan }}
                                            </span>
                                        </div>
                                        @endcan
                                    </td>

                                    <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white text-center">
                                        {{ $item->pengawas->nama_lengkap ?? '-' }}
                                    </td>

                                    <td class="font-medium text-gray-600 whitespace-nowrap dark:text-gray-400 text-center">
                                        {{ $item->subcon ?? '-' }}
                                    </td>

                                    <td class="whitespace-nowrap text-center">
                                        @php
                                            $st = $item->status_serah_terima;

                                            $config = [
                                                'pending' => [
                                                    'bg' =>
                                                        'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
                                                    'label' => 'Pending',
                                                ],
                                                'siap_serah_terima' => [
                                                    'bg' =>
                                                        'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                                    'label' => 'Siap Serah Terima',
                                                ],
                                                'siap_lpa' => [
                                                    'bg' =>
                                                        'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                                    'label' => 'Siap LPA',
                                                ],
                                            ];

                                            $current = $config[$st] ?? $config['pending'];
                                        @endphp

                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-tight {{ $current['bg'] }} border border-transparent">
                                            {{ $current['label'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View -->
                <div class="block md:hidden" x-data="{
                    searchMobile: '',
                    expandedId: null,
                    toggle(id) { this.expandedId = this.expandedId === id ? null : id }
                }">
                    {{-- Search Bar Mobile --}}
                    <div class="relative mb-3">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" x-model="searchMobile" placeholder="Cari unit, tahap, pengawas..."
                            class="w-full pl-10 pr-4 py-2.5 text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-800 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <button x-show="searchMobile.length > 0" @click="searchMobile = ''"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Result count --}}
                    <div class="mb-2 text-[11px] text-gray-400 dark:text-gray-500 px-1" x-show="searchMobile.length > 0">
                        Menampilkan hasil untuk "<span class="font-medium text-gray-600 dark:text-gray-300" x-text="searchMobile"></span>"
                    </div>

                    {{-- Card Grid: 1 col default, 2 col on wider phones --}}
                    <div class="grid grid-cols-1 min-[480px]:grid-cols-2 gap-2.5">
                        @forelse ($allPembangunanUnit as $item)
                            @php
                                $progres = $item->total_progres;
                                $bgClass = 'bg-blue-500';
                                $barColor = 'bg-blue-500';
                                $textClass = 'text-white';
                                $statusIcon = '';
                                $statusLabel = $item->status_pembangunan;

                                if ($item->status_pembangunan === 'selesai') {
                                    $bgClass = 'bg-green-500';
                                    $barColor = 'bg-green-500';
                                    $statusIcon = '<i class="fa-solid fa-circle-check mr-0.5"></i>';
                                } elseif ($item->status_pembangunan === 'selesai dengan catatan') {
                                    $bgClass = 'bg-yellow-500';
                                    $barColor = 'bg-yellow-500';
                                    $textClass = 'text-yellow-950';
                                    $statusIcon = '<i class="fa-solid fa-circle-exclamation mr-0.5"></i>';
                                }

                                $st = $item->status_serah_terima;
                                $stConfig = [
                                    'pending' => [
                                        'bg' => 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400',
                                        'label' => 'Pending',
                                        'dot' => 'bg-gray-400',
                                    ],
                                    'siap_serah_terima' => [
                                        'bg' => 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
                                        'label' => 'Siap ST',
                                        'dot' => 'bg-blue-500',
                                    ],
                                    'siap_lpa' => [
                                        'bg' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400',
                                        'label' => 'Siap LPA',
                                        'dot' => 'bg-emerald-500',
                                    ],
                                ];
                                $stCurrent = $stConfig[$st] ?? $stConfig['pending'];

                                // Search data attributes
                                $searchData = strtolower(
                                    ($item->unit->nama_unit ?? '') . ' ' .
                                    ($item->tahap->nama_tahap ?? '') . ' ' .
                                    ($item->pengawas->nama_lengkap ?? '') . ' ' .
                                    ($item->spv->nama_lengkap ?? '') . ' ' .
                                    ($item->qcContainer->nama_container ?? '') . ' ' .
                                    ($item->subcon ?? '')
                                );
                            @endphp

                            <div x-show="searchMobile === '' || '{{ $searchData }}'.includes(searchMobile.toLowerCase())"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm hover:shadow-md transition-all">

                                {{-- Compact Header: Unit name + Progress --}}
                                <div class="px-3 pt-3 pb-2">
                                    <div class="flex items-center justify-between gap-2 mb-1.5">
                                        <h4 class="text-sm font-bold text-gray-800 dark:text-white truncate leading-tight">
                                            {{ $item->unit->nama_unit }}
                                        </h4>
                                        <span class="shrink-0 px-2 py-0.5 text-[10px] font-bold rounded-full {{ $bgClass }} {{ $textClass }}">
                                            {!! $statusIcon !!}{{ $progres }}%
                                        </span>
                                    </div>

                                    {{-- Visual Progress Bar --}}
                                    <div class="w-full h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden mb-2">
                                        <div class="h-full rounded-full transition-all duration-500 {{ $barColor }}"
                                            style="width: {{ $progres }}%"></div>
                                    </div>

                                    {{-- Key info: Tahap + QC inline --}}
                                    <div class="flex items-center gap-1.5 flex-wrap text-[11px]">
                                        <span class="px-1.5 py-0.5 rounded bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 font-semibold">
                                            {{ $item->tahap->nama_tahap }}
                                        </span>
                                        <span class="text-gray-300 dark:text-gray-600">•</span>
                                        <span class="text-gray-500 dark:text-gray-400 truncate">{{ $item->qcContainer->nama_container }}</span>
                                    </div>
                                </div>

                                {{-- Expandable Detail --}}
                                <div x-show="expandedId === {{ $item->id }}"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 -translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-collapse
                                    class="px-3 pb-2">
                                    <div class="pt-2 border-t border-gray-100 dark:border-gray-700 space-y-1.5 text-[11px]">
                                        <div class="flex justify-between">
                                            <span class="text-gray-400">SPV</span>
                                            <span class="text-gray-700 dark:text-gray-300 font-medium">{{ $item->spv->nama_lengkap ?? '-' }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-400">Pengawas</span>
                                            <span class="text-gray-700 dark:text-gray-300 font-medium">{{ $item->pengawas->nama_lengkap ?? '-' }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-400">Subcon</span>
                                            <span class="text-gray-700 dark:text-gray-300 font-medium">{{ $item->subcon ?? '-' }}</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-400">Serah Terima</span>
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold {{ $stCurrent['bg'] }}">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $stCurrent['dot'] }}"></span>
                                                {{ $stCurrent['label'] }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Footer: Toggle Detail + Action --}}
                                <div class="flex items-center border-t border-gray-100 dark:border-gray-700 divide-x divide-gray-100 dark:divide-gray-700">
                                    <button @click="toggle({{ $item->id }})"
                                        class="flex-1 flex items-center justify-center gap-1 py-2 text-[11px] font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                        <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="{'rotate-180': expandedId === {{ $item->id }}}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                        <span x-text="expandedId === {{ $item->id }} ? 'Tutup' : 'Info'"></span>
                                    </button>
                                    @can('produksi.properti.pembangunan-unit.detail')
                                    <a href="{{ route('produksi.pembangunanUnit.show', $item->id) }}"
                                        class="flex-1 flex items-center justify-center gap-1 py-2 text-[11px] font-semibold text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                        Detail
                                    </a>
                                    @endcan
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full py-8 text-center text-gray-400 text-sm">
                                Tidak ada data Pembangunan Unit.
                            </div>
                        @endforelse
                    </div>

                    {{-- No result message --}}
                    <div x-show="searchMobile.length > 0 && document.querySelectorAll('[x-show*=searchMobile]:not([style*=none])').length === 0"
                        class="py-6 text-center text-gray-400 text-sm">
                        <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Data tidak ditemukan.
                    </div>
                </div>

            </div>
        </div>

    </div>
    <!-- ===== Main Content End ===== -->

    <script>
        document.addEventListener('click', function(e) {
            if (e.target.closest('.delete-btn')) {
                const btn = e.target.closest('.delete-btn');
                const form = btn.closest('.delete-form');

                Swal.fire({
                    title: 'Yakin hapus data ini?',
                    text: "Apakah anda yakin menghapus pembangunan unit ini? Semua data yang terkait dengan pembangunan unit akan ikut terhapus.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            }
        });
    </script>

    <script>
        if (document.getElementById("table-pembangunan-unit") && typeof simpleDatatables.DataTable !== 'undefined') {
            const dataTable = new simpleDatatables.DataTable("#table-pembangunan-unit", {
                searchable: true,
                sortable: false
            });
        }
    </script>
@endsection
