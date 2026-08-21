@extends('layouts.app')

@section('pageActive', 'ManagePemesananAgent')

@section('content')
    <!-- ===== Main Content Start ===== -->
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-data="{ showExportModal: false, exportSource: 'agent', exportTitle: 'Export Data Closing (Agent)' }">

        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: 'Manage Pemesanan Agent' }">
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

        {{-- hak akses berbeda untk print ppjb beradasarkan dari ubs mana --}}
        @php
            $user = auth()->user();
            $bolehPrintPPJB = false;

            if ($namaPerumahaanAktif === 'Asa Dreamland') {
                // Khusus ADL → ROLE
                $bolehPrintPPJB = $user->hasRole(['Proyek Manager (ADL)', 'SPV Penjualan (ADL)', 'Superadmin', 'Staff KPR (ADL)']);
            } else {
                // Selain ADL → PERMISSION
                $bolehPrintPPJB = $user->can('marketing.kelola-pemesanan.print-ppjb');
            }
        @endphp

        <div class="space-y-6">
            {{-- Filter Bulan & Tahun --}}
            <form method="GET" action="{{ route('marketing.managePemesananAgent.index') }}"
                class="flex flex-wrap items-end gap-3 bg-white dark:bg-white/[0.03] p-4 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm">
                
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Bulan (Tanggal Pemesanan)</label>
                    <select name="bulan" class="rounded-lg border-gray-300 bg-gray-50 dark:bg-gray-800 text-gray-800 text-sm focus:ring-blue-500 focus:border-blue-500 dark:border-gray-700 dark:text-gray-300 min-w-[150px] p-2.5">
                        <option value="">Semua Bulan</option>
                        @php
                            $namaBulan = [
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                            ];
                        @endphp
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ (string)$bulan === (string)$m ? 'selected' : '' }}>
                                {{ $namaBulan[$m] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Tahun</label>
                    <select name="tahun" class="rounded-lg border-gray-300 bg-gray-50 dark:bg-gray-800 text-gray-800 text-sm focus:ring-blue-500 focus:border-blue-500 dark:border-gray-700 dark:text-gray-300 min-w-[120px] p-2.5">
                        @php $currentYear = (int)date('Y'); @endphp
                        @foreach(range($currentYear - 4, $currentYear + 2) as $y)
                            <option value="{{ $y }}" {{ (int)$tahun === $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700 transition focus:ring-4 focus:ring-blue-300 active:scale-95 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Filter
                    </button>
                    @if($bulan !== '' || (int)$tahun !== (int)date('Y'))
                        <a href="{{ route('marketing.managePemesananAgent.index') }}"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-gray-200 dark:bg-gray-700 px-3 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                            Reset
                        </a>
                    @endif
                    @can('marketing.kelola-pemesanan-agent.export-closing')
                        <div class="relative" x-data="{ openExportDropdown: false }" @click.away="openExportDropdown = false">
                            <button type="button" @click="openExportDropdown = !openExportDropdown"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-emerald-700 transition focus:ring-4 focus:ring-emerald-300 active:scale-95 shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>Export Excel</span>
                                <svg class="w-3.5 h-3.5 ml-0.5 transition-transform" :class="openExportDropdown ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            {{-- Dropdown Menu --}}
                            <div x-show="openExportDropdown" x-cloak
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute right-0 mt-2 w-72 rounded-xl bg-white dark:bg-gray-800 shadow-xl border border-gray-100 dark:border-gray-700 p-1.5 z-50">
                                
                                <button type="button" @click="exportSource = 'agent'; exportTitle = 'Export Data Closing (Agent)'; showExportModal = true; openExportDropdown = false"
                                    class="group w-full flex items-center gap-3 px-3 py-2.5 text-xs text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-lg transition">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400 group-hover:bg-amber-100 dark:group-hover:bg-amber-900/50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                    <div class="text-left">
                                        <div class="font-medium text-gray-900 dark:text-white">Export Agent</div>
                                        <div class="text-[11px] text-gray-400 font-normal">Transaksi broker / agent</div>
                                    </div>
                                </button>

                                <div class="border-t border-gray-100 dark:border-gray-700 my-1"></div>

                                <button type="button" @click="exportSource = 'all'; exportTitle = 'Export Rekap Closing (All)'; showExportModal = true; openExportDropdown = false"
                                    class="group w-full flex items-center gap-3 px-3 py-2.5 text-xs font-semibold text-emerald-700 dark:text-emerald-300 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 rounded-lg transition">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400 group-hover:bg-emerald-100 dark:group-hover:bg-emerald-900/50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div class="text-left">
                                        <div class="font-semibold text-emerald-800 dark:text-emerald-300">Export Rekap Closing (All)</div>
                                        <div class="text-[11px] text-emerald-600/80 dark:text-emerald-400/80 font-normal">Gabungan Internal & Agent</div>
                                    </div>
                                </button>
                            </div>
                        </div>
                    @endcan
                </div>
            </form>

            {{-- ==================== KPR SECTION ==================== --}}
            <div
                class="rounded-2xl border border-gray-200 bg-white px-6 py-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90 flex items-center gap-2">
                        <span class="px-2.5 py-1 font-medium bg-indigo-100 text-indigo-700 rounded-full">
                            Manage Pemesanan Agent - KPR
                        </span>
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table id="table-managePemesananAgentKpr" class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                <th class="px-4 py-3 w-[200px]">Nama User</th>
                                <th class="px-4 py-3">Unit</th>
                                <th class="px-4 py-3">Nama Agent</th>
                                <th class="px-4 py-3">Tgl Pemesanan</th>
                                <th class="px-4 py-3 text-center">Detail</th>

                                @if ($bolehPrintPPJB)
                                    <th class="px-4 py-3 text-center">PPJB</th>
                                @endif

                                <th class="px-4 py-3 text-center">Kelengkapan Berkas</th>
                                <th class="px-4 py-3">Bank</th>
                                <th class="px-4 py-3 text-center">Progress Bangunan</th>
                                <th class="px-4 py-3 text-center">Status KPR</th>
                                <th class="px-4 py-3">Status Unit Pemesanan</th>

                                @can('marketing.kelola-pemesanan.read-berkas')
                                    <th class="px-4 py-3 text-center">Berkas KPR</th>
                                @endcan

                                @can('marketing.kelola-pemesanan.tagihan.read')
                                    <th class="px-4 py-3 text-center">Rincian Tagihan</th>
                                @endcan

                                @can('marketing.kelola-pemesanan.pengajuan-pembatalan')
                                    <th class="px-4 py-3 text-center">Pengajuan Pembatalan</th>
                                @endcan
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($pemesananKpr as $item)
                                <tr class="border-b hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                    <td class="px-4 py-2 font-medium text-gray-800 dark:text-white truncate max-w-[200px]" title="{{ $item->customer->nama_lengkap ?? $item->customer->username ?? '-' }}">
                                        {{ $item->customer->nama_lengkap ?? $item->customer->username ?? '-' }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">{{ $item->unit->nama_unit ?? '-' }}</td>
                                    <td class="px-4 py-2 font-medium text-blue-600 dark:text-blue-400">
                                        {{ $item->agent->nama_agent ?? '-' }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                                        {{ $item->tanggal_pemesanan ? \Carbon\Carbon::parse($item->tanggal_pemesanan)->format('d M Y') : '-' }}
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <a href="{{ route('marketing.managePemesananAgent.show', $item->id) }}"
                                            class="inline-flex items-center gap-1 px-3 py-1 text-white bg-blue-600 rounded hover:bg-blue-700 transition text-xs font-medium shadow-sm">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            Detail
                                        </a>
                                    </td>

                                    @if ($bolehPrintPPJB)
                                        <td class="px-4 py-2 text-center">
                                            <a href="{{ route('ppjbKPR.export.word', $item->id) }}"
                                                class="inline-flex items-center px-3 py-1 text-white bg-blue-600 rounded hover:bg-blue-700 transition">
                                                PPJB
                                            </a>
                                        </td>
                                    @endif

                                    <td class="px-4 py-2 text-center">
                                        <span class="text-gray-600">{{ $item->kelengkapan_berkas }}</span>
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        @if (!empty($item->kpr->bank->kode_bank))
                                            <span
                                                class="inline-block px-2 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded">
                                                {{ $item->kpr->bank->kode_bank }}
                                            </span>
                                        @else
                                            <span
                                                class="inline-block px-2 py-1 text-xs font-medium text-gray-500 bg-gray-100 rounded">
                                                -
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-2 text-center">{{ $item->progress_bangunan ?? '' }}</td>
                                    <td class="px-4 py-2 text-center">
                                        <span
                                            class="px-2 py-1 rounded font-medium
                                        {{ $item->kpr->status_kpr === 'acc'
                                            ? 'bg-green-100 text-green-700'
                                            : ($item->kpr->status_kpr === 'proses'
                                                ? 'bg-yellow-100 text-yellow-700'
                                                : ($item->kpr->status_kpr === 'realisasi'
                                                    ? 'bg-sky-100 text-sky-700'
                                                    : 'bg-gray-100 text-gray-700')) }}">
                                            {{ ucfirst($item->kpr->status_kpr ?? '-') }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        @php
                                            $status = $item->status_pemesanan ?? '-';
                                            $classes = match ($status) {
                                                'proses' => 'bg-yellow-100 text-yellow-800 px-2 py-1 rounded',
                                                'LPA' => 'bg-blue-100 text-blue-800 px-2 py-1 rounded',
                                                'serah_terima' => 'bg-green-100 text-green-800 px-2 py-1 rounded',
                                                default => 'bg-gray-100 text-gray-600 px-2 py-1 rounded',
                                            };
                                        @endphp
                                        <span class="{{ $classes }}">{{ ucfirst($status) }}</span>
                                    </td>

                                    {{-- lihat berkas kpr dan update (khusus staff kpr) --}}
                                    @can('marketing.kelola-pemesanan.read-berkas')
                                        <td class="px-4 py-2 text-center">
                                            <a href="{{ route('marketing.kelengkapanBerkasKpr.editKpr', $item->id) }}"
                                                class="inline-flex items-center gap-1 px-3 py-1 text-white bg-indigo-600 rounded hover:bg-indigo-700 transition whitespace-nowrap">
                                                <i class="ri-edit-line"></i>
                                                <span>Berkas KPR</span>
                                            </a>
                                        </td>
                                    @endcan

                                    {{-- 🟢 Rincian Tagihan --}}
                                    @can('marketing.kelola-pemesanan.tagihan.read')
                                        <td class="px-4 py-2 text-center">
                                            <a href="{{ route('marketing.rincianTagihan', $item->id) }}"
                                                class="inline-flex items-center gap-1 px-3 py-1 text-white bg-green-600 rounded hover:bg-green-700 transition">
                                                <i class="ri-file-list-3-line"></i> Lihat
                                            </a>
                                        </td>
                                    @endcan

                                    @can('marketing.kelola-pemesanan.pengajuan-pembatalan')
                                        {{-- 🔴 Pengajuan Pembatalan --}}
                                        <td class="px-4 py-2 text-center">
                                            <button data-modal-target="modal-pembatalan"
                                                data-modal-toggle="modal-pembatalan" data-id="{{ $item->id }}"
                                                data-nama-unit="{{ $item->unit->nama_unit ?? '-' }}"
                                                data-nama-user="{{ $item->customer->username ?? '-' }}"
                                                data-cara-bayar="{{ ucfirst($item->cara_bayar) }}"
                                                data-no-hp="{{ $item->customer->no_hp ?? '-' }}"
                                                class="inline-flex items-center gap-1 px-3 py-1 text-white bg-red-600 rounded hover:bg-red-700 transition">
                                                <i class="ri-close-circle-line"></i> Pembatalan
                                            </button>
                                        </td>
                                    @endcan

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ==================== CASH SECTION ==================== --}}
            <div
                class="rounded-2xl border border-gray-200 bg-white px-6 py-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90 flex items-center gap-2">
                        <span class="px-2.5 py-1 font-medium bg-emerald-100 text-emerald-700 rounded-full">
                            Manage Pemesanan Agent - Cash
                        </span>
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table id="table-managePemesananAgentCash" class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                <th class="px-4 py-3 w-[200px]">Nama User</th>
                                <th class="px-4 py-3">Unit</th>
                                <th class="px-4 py-3">Nama Agent</th>
                                <th class="px-4 py-3">Tgl Pemesanan</th>
                                <th class="px-4 py-3 text-center">Detail</th>

                                @if ($bolehPrintPPJB)
                                    <th class="px-4 py-3 text-center">PPJB</th>
                                @endif

                                <th class="px-4 py-3 text-center">Kelengkapan Berkas</th>
                                <th class="px-4 py-3 text-center">Progress Bangunan</th>
                                <th class="px-4 py-3">Status Unit Pemesanan</th>

                                @can('marketing.kelola-pemesanan.read-berkas')
                                    <th class="px-4 py-3 text-center">Berkas Cash</th>
                                @endcan

                                @can('marketing.kelola-pemesanan.tagihan.read')
                                    <th class="px-4 py-3 text-center">Rincian Tagihan</th>
                                @endcan

                                @can('marketing.kelola-pemesanan.pengajuan-pembatalan')
                                    <th class="px-4 py-3 text-center">Pengajuan Pembatalan</th>
                                @endcan
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($pemesananCash as $item)
                                <tr class="border-b hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                    <td class="px-4 py-2 font-medium text-gray-800 dark:text-white truncate max-w-[200px]" title="{{ $item->customer->nama_lengkap ?? $item->customer->username ?? '-' }}">
                                        {{ $item->customer->nama_lengkap ?? $item->customer->username ?? '-' }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">{{ $item->unit->nama_unit ?? '-' }}</td>
                                    <td class="px-4 py-2 font-medium text-blue-600 dark:text-blue-400">
                                        {{ $item->agent->nama_agent ?? '-' }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                                        {{ $item->tanggal_pemesanan ? \Carbon\Carbon::parse($item->tanggal_pemesanan)->format('d M Y') : '-' }}
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <a href="{{ route('marketing.managePemesananAgent.show', $item->id) }}"
                                            class="inline-flex items-center gap-1 px-3 py-1 text-white bg-blue-600 rounded hover:bg-blue-700 transition text-xs font-medium shadow-sm">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            Detail
                                        </a>
                                    </td>

                                    @if ($bolehPrintPPJB)
                                        <td class="px-4 py-2 text-center">
                                            <a href="{{ route('ppjbCASH.export.word', $item->id) }}"
                                                class="inline-flex items-center px-3 py-1 text-white bg-blue-600 rounded hover:bg-blue-700 transition">
                                                PPJB
                                            </a>
                                        </td>
                                    @endif

                                    <td class="px-4 py-2 text-center">
                                        <span class="text-gray-600">{{ $item->kelengkapan_berkas ?? 0 }}</span>
                                    </td>
                                    <td class="px-4 py-2 text-center">{{ $item->progress_bangunan ?? '-' }}</td>
                                    <td class="px-4 py-2 text-center">
                                        @php
                                            $status = $item->status_pemesanan ?? '-';
                                            $classes = match ($status) {
                                                'proses' => 'bg-yellow-100 text-yellow-800 px-2 py-1 rounded',
                                                'LPA' => 'bg-blue-100 text-blue-800 px-2 py-1 rounded',
                                                'serah_terima' => 'bg-green-100 text-green-800 px-2 py-1 rounded',
                                                default => 'bg-gray-100 text-gray-600 px-2 py-1 rounded',
                                            };
                                        @endphp
                                        <span class="{{ $classes }}">{{ ucfirst($status) }}</span>
                                    </td>

                                    {{-- 🔵 Update Data Cash --}}
                                    @can('marketing.kelola-pemesanan.read-berkas')
                                        <td class="px-4 py-2 text-center">
                                            <a href="{{ route('marketing.kelengkapanBerkasCash.editCash', $item->id) }}"
                                                class="inline-flex items-center gap-1 px-3 py-1 text-white bg-indigo-600 rounded hover:bg-indigo-700 transition">
                                                <i class="ri-edit-line"></i> Berkas Cash
                                            </a>
                                        </td>
                                    @endcan

                                    {{-- 🟢 Rincian Tagihan --}}
                                    @can('marketing.kelola-pemesanan.tagihan.read')
                                        <td class="px-4 py-2 text-center">
                                            <a href="{{ route('marketing.rincianTagihan', $item->id) }}"
                                                class="inline-flex items-center gap-1 px-3 py-1 text-white bg-green-600 rounded hover:bg-green-700 transition">
                                                <i class="ri-file-list-3-line"></i> Lihat
                                            </a>
                                        </td>
                                    @endcan

                                    @can('marketing.kelola-pemesanan.pengajuan-pembatalan')
                                        {{-- 🔴 Pengajuan Pembatalan --}}
                                        <td class="px-4 py-2 text-center">
                                            <button data-modal-target="modal-pembatalan" data-modal-toggle="modal-pembatalan"
                                                data-id="{{ $item->id }}"
                                                data-nama-unit="{{ $item->unit->nama_unit ?? '-' }}"
                                                data-nama-user="{{ $item->customer->username ?? '-' }}"
                                                data-cara-bayar="{{ ucfirst($item->cara_bayar) }}"
                                                data-no-hp="{{ $item->customer->no_hp ?? '-' }}"
                                                class="inline-flex items-center gap-1 px-3 py-1 text-white bg-red-600 rounded hover:bg-red-700 transition">
                                                <i class="ri-close-circle-line"></i> Pembatalan
                                            </button>
                                        </td>
                                    @endcan

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

    </div>

    {{-- Modal Export Custom Excel --}}
    <div x-show="showExportModal" class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/50" x-cloak>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-4xl p-6 border border-gray-200 dark:border-gray-700"
             @click.away="showExportModal = false">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4" x-text="exportTitle"></h3>
            
            <form action="{{ route('marketing.managePemesananAgent.exportExcel') }}" method="GET" @submit="showExportModal = false">
                <input type="hidden" name="tahun" value="{{ $tahun }}">
                <input type="hidden" name="bulan" value="{{ $bulan }}">
                <input type="hidden" name="source" :value="exportSource">
                
                <p class="text-xs text-gray-400 mb-3 uppercase font-bold tracking-wider">Pilih Kolom Yang Ingin Di-export</p>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 mb-6">
                    <!-- Nama Unit (Wajib) -->
                    <input type="hidden" name="columns[]" value="nama_unit">
                    <label class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-700/30 rounded-lg cursor-not-allowed border border-gray-200 dark:border-gray-700">
                        <input type="checkbox" checked disabled class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mt-1">
                        <div>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">Nama Unit</span>
                            <span class="block text-xs text-gray-400">Wajib disertakan</span>
                        </div>
                    </label>

                    <!-- Nama Customer -->
                    <label class="flex items-start gap-3 p-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 rounded-lg cursor-pointer border border-gray-200 dark:border-gray-700">
                        <input type="checkbox" name="columns[]" value="nama_user" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mt-1">
                        <div>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">Nama Customer</span>
                            <span class="block text-xs text-gray-400">Nama lengkap customer</span>
                        </div>
                    </label>

                    <!-- Sales / Agent -->
                    <label class="flex items-start gap-3 p-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 rounded-lg cursor-pointer border border-gray-200 dark:border-gray-700">
                        <input type="checkbox" name="columns[]" value="nama_agent_sales" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mt-1">
                        <div>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">Sales / Agent</span>
                            <span class="block text-xs text-gray-400">Nama marketing atau nama agen</span>
                        </div>
                    </label>

                    <!-- Tanggal Closing -->
                    <label class="flex items-start gap-3 p-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 rounded-lg cursor-pointer border border-gray-200 dark:border-gray-700">
                        <input type="checkbox" name="columns[]" value="tanggal_closing" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mt-1">
                        <div>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">Tanggal Closing</span>
                            <span class="block text-xs text-gray-400">Tanggal pemesanan unit</span>
                        </div>
                    </label>

                    <!-- Cara Bayar -->
                    <label class="flex items-start gap-3 p-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 rounded-lg cursor-pointer border border-gray-200 dark:border-gray-700">
                        <input type="checkbox" name="columns[]" value="cara_bayar" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mt-1">
                        <div>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">Cara Bayar</span>
                            <span class="block text-xs text-gray-400">Metode pembayaran (KPR atau CASH)</span>
                        </div>
                    </label>

                    <!-- Status KPR -->
                    <label class="flex items-start gap-3 p-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 rounded-lg cursor-pointer border border-gray-200 dark:border-gray-700">
                        <input type="checkbox" name="columns[]" value="status_kpr" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mt-1">
                        <div>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">Status KPR</span>
                            <span class="block text-xs text-gray-400">Status berkas KPR unit</span>
                        </div>
                    </label>

                    <!-- Data Diri WhatsApp / Phone -->
                    <label class="flex items-start gap-3 p-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 rounded-lg cursor-pointer border border-gray-200 dark:border-gray-700">
                        <input type="checkbox" name="columns[]" value="no_hp" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mt-1">
                        <div>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">No. HP / WA</span>
                            <span class="block text-xs text-gray-400">Nomor WhatsApp customer</span>
                        </div>
                    </label>

                    <!-- Data Diri NIK KTP -->
                    <label class="flex items-start gap-3 p-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 rounded-lg cursor-pointer border border-gray-200 dark:border-gray-700">
                        <input type="checkbox" name="columns[]" value="no_ktp" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mt-1">
                        <div>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">NIK / No. KTP</span>
                            <span class="block text-xs text-gray-400">Nomor KTP terdaftar</span>
                        </div>
                    </label>

                    <!-- Data Diri Pekerjaan -->
                    <label class="flex items-start gap-3 p-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 rounded-lg cursor-pointer border border-gray-200 dark:border-gray-700">
                        <input type="checkbox" name="columns[]" value="pekerjaan" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mt-1">
                        <div>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">Pekerjaan</span>
                            <span class="block text-xs text-gray-400">Pekerjaan customer</span>
                        </div>
                    </label>

                    <!-- Data Diri Alamat -->
                    <label class="flex items-start gap-3 p-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 rounded-lg cursor-pointer border border-gray-200 dark:border-gray-700">
                        <input type="checkbox" name="columns[]" value="alamat" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mt-1">
                        <div>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">Alamat Lengkap</span>
                            <span class="block text-xs text-gray-400">Alamat detail tempat tinggal</span>
                        </div>
                    </label>
                </div>
                
                <div class="flex justify-end gap-2">
                    <button type="button" @click="showExportModal = false" class="px-4 py-2 text-xs font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition">Batal</button>
                    <button type="submit" class="inline-flex items-center gap-1 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-xs font-semibold shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Download Excel
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
<!-- ===== Main Content End ===== -->

@include('marketing.manage-pemesanan.modal.modal-pengajuan-pembatalanPemesanan')

    <script>
        if (document.getElementById("table-managePemesananAgentKpr") && typeof simpleDatatables.DataTable !== 'undefined') {
            const dataTable = new simpleDatatables.DataTable("#table-managePemesananAgentKpr", {
                searchable: true,
                sortable: true,
            });
        }

        if (document.getElementById("table-managePemesananAgentCash") && typeof simpleDatatables.DataTable !== 'undefined') {
            const dataTable = new simpleDatatables.DataTable("#table-managePemesananAgentCash", {
                searchable: true,
                sortable: true,
            });
        }
    </script>
@endsection
