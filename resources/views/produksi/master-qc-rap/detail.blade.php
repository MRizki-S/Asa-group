@extends('layouts.app')

@section('pageActive', 'MasterQC-RAP')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-data="{ openQc: null }">

        <div x-data="{ pageName: 'Detail Master QC & RAP' }">
            @include('partials.breadcrumb')
        </div>

        <div class="mb-6 flex justify-between items-center">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">Review Master QC & RAP</h2>
            <div class="flex gap-3">
                <button onclick="window.print()"
                    class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
                    Cetak Detail
                </button>
                <a href="{{ route('produksi.masterQcRap.edit', $container->id) }}"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                    Edit Data
                </a>
            </div>
        </div>

        {{-- 1. INFORMASI UTAMA --}}
        <div
            class="rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800 mb-8 shadow-sm overflow-hidden">
            <div class="bg-gray-50/50 dark:bg-gray-700/50 px-5 py-3 border-b dark:border-gray-700">
                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Informasi Utama</h3>
            </div>
            <div class="p-5 sm:p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <p class="text-xs font-medium text-gray-400 uppercase mb-1">Type Unit</p>
                        <p class="text-lg font-semibold text-gray-800 dark:text-white">{{ $container->type->nama_type }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-400 uppercase mb-1">Nama Container</p>
                        <p class="text-lg font-semibold text-gray-800 dark:text-white">{{ $container->nama_container }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- HEADER SECTION --}}
        <div class="flex items-center gap-4 mb-6">
            <div class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></div>
            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500">Struktur QC & Rencana Anggaran</h3>
            <div class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></div>
        </div>

        {{-- ACCORDION MASTER LOOP --}}
        <div class="space-y-4 mb-10">
            @foreach ($container->urutan as $index => $qc)
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden"
                    x-data="{ isOpen: false }">

                    {{-- Header Accordion --}}
                    <div @click="isOpen = !isOpen"
                        class="flex justify-between items-center p-5 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors bg-white dark:bg-gray-800">
                        <div class="flex items-center gap-4">
                            <div
                                class="w-10 h-10 rounded-full bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold text-sm">
                                {{ $qc->qc_ke }}
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-gray-800 dark:text-white">{{ $qc->nama_qc }}</h4>
                                <p class="text-[10px] text-gray-400 uppercase tracking-widest">{{ $qc->tugas->count() }}
                                    Tugas</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-6">
                            <div class="hidden md:flex gap-4 text-right">
                                <div>
                                    <p class="text-[9px] text-gray-400 uppercase">Sub-Total Upah</p>
                                    <p class="text-xs font-bold text-gray-700 dark:text-gray-300">Rp
                                        {{ number_format($qc->rapUpah->sum('nominal_standar'), 0, ',', '.') }},00</p>
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 transition-transform duration-200"
                                :class="isOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>

                    {{-- Body Accordion --}}
                    <div x-show="isOpen" x-collapse x-cloak>
                        <div class="p-6 border-t border-gray-100 dark:border-gray-700 space-y-8">

                            {{-- A. DAFTAR TUGAS --}}
                            <div>
                                <p
                                    class="text-[10px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span> Daftar Checklist Tugas
                                </p>
                                <ul class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2 ml-3">
                                    @foreach ($qc->tugas as $tugas)
                                        <li class="flex items-start gap-3 text-sm text-gray-600 dark:text-gray-300">
                                            <svg class="w-4 h-4 text-green-500 mt-0.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                            {{ $tugas->tugas }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                {{-- B. RAP BAHAN PER QC --}}
                                <div class="space-y-3">
                                    <p
                                        class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-widest flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Rencana Anggaran Bahan
                                    </p>
                                    <div class="rounded-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                                        <table class="w-full text-xs text-left">
                                            <thead class="bg-gray-50 dark:bg-gray-700/30 text-gray-500 uppercase">
                                                <tr>
                                                    <th class="px-4 py-2">Barang</th>
                                                    <th class="px-4 py-2 text-center">Qty</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                                                @forelse($qc->rapBahan as $bahan)
                                                    <tr>
                                                        <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300">
                                                            {{ $bahan->barang->nama_barang ?? '-' }}</td>
                                                        <td
                                                            class="px-4 py-2.5 text-center font-bold text-gray-900 dark:text-white">
                                                            {{ str_replace('.', ',', (float) $bahan->jumlah_kebutuhan_standar) }}
                                                            {{ $bahan->satuan->nama ?? '-' }}
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="3"
                                                            class="px-4 py-4 text-center text-gray-400 italic">Tidak ada
                                                            bahan.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                {{-- C. RAP UPAH PER QC --}}
                                <div class="space-y-3">
                                    <p
                                        class="text-[10px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-widest flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span> Rencana Anggaran Upah
                                    </p>
                                    <div class="rounded-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                                        <table class="w-full text-xs text-left">
                                            <thead class="bg-gray-50 dark:bg-gray-700/30 text-gray-500 uppercase">
                                                <tr>
                                                    <th class="px-4 py-2">Pekerjaan</th>
                                                    <th class="px-4 py-2 text-right">Nominal</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                                                @forelse($qc->rapUpah as $upah)
                                                    <tr>
                                                        <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300">
                                                            {{ $upah->masterUpah->nama_upah }}</td>
                                                        <td
                                                            class="px-4 py-2.5 text-right font-bold text-blue-600 dark:text-blue-400">
                                                            Rp {{ number_format($upah->nominal_standar, 0, ',', '.') }},00
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="2"
                                                            class="px-4 py-4 text-center text-gray-400 italic">Tidak ada
                                                            upah.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                            <tfoot
                                                class="bg-amber-50/30 dark:bg-amber-900/10 font-bold border-t dark:border-gray-700">
                                                <tr>
                                                    <td
                                                        class="px-4 py-2 text-gray-600 dark:text-gray-400 uppercase text-[9px]">
                                                        Sub-Total Upah QC</td>
                                                    <td class="px-4 py-2 text-right text-amber-700 dark:text-amber-400">Rp
                                                        {{ number_format($qc->rapUpah->sum('nominal_standar'), 0, ',', '.') }},00
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- RINGKASAN TOTAL AKHIR (Versi Slim & Elegant) --}}
        <div
            class="rounded-2xl border border-gray-200 bg-white p-2 shadow-sm transition-all dark:border-gray-700 dark:bg-gray-800">
            <div class="flex flex-col items-center justify-between gap-4 rounded-xl px-5 py-4 md:flex-row">

                <div class="flex items-center gap-4">
                    {{-- Icon Container Slim --}}
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-600 dark:bg-blue-500">
                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>

                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">
                            Total Anggaran
                        </h3>
                        <p class="text-[10px] text-gray-400 dark:text-gray-500">Estimasi akumulasi upah QC</p>
                    </div>
                </div>

                {{-- Price Display Slim --}}
                <div class="flex items-baseline gap-1">
                    <span class="text-sm font-bold text-gray-400 dark:text-gray-500">Rp</span>
                    <p class="text-lg font-black tracking-tight text-gray-900 dark:text-white ">
                        {{ number_format($container->rapUpah->sum('nominal_standar'), 0, ',', '.') }},00
                    </p>
                </div>
            </div>
        </div>

        {{-- Footer Nav --}}
        <div class="flex justify-start border-t border-gray-200 dark:border-gray-700 pt-6">
            <a href="{{ route('produksi.masterQcRap.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-blue-600 transition">
                <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Daftar Master
            </a>
        </div>
    </div>
@endsection
