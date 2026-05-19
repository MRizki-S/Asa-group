@extends('layouts.app')

@section('pageActive', 'NeracaSaldo')

@section('content')

{{-- select 2 --}}
<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">

{{-- datepicker --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<!-- ===== Main Content Start ===== -->
<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

    <!-- Breadcrumb Start -->
    <div x-data="{ pageName: 'NeracaSaldo' }">
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

            {{-- FILTER NERACA SALDO --}}
            <form method="GET" action="{{ route('keuangan.neracaSaldo.index') }}" x-data="{ tipe: '{{ request('tipe', 'bulan') }}' }"
                class="relative mb-6 p-6 pt-8 bg-white dark:bg-gray-800 rounded-xl border border-gray-200
                    dark:border-gray-700 shadow-sm">
                <span class="absolute -top-3 left-5 px-3 py-1 text-xs font-semibold 
                    uppercase tracking-widest rounded-md
                    bg-white dark:bg-gray-800 
                    text-blue-600 dark:text-blue-400">
                    Filter Laporan
                </span>

                <div class="flex flex-wrap items-end gap-6">
                    {{-- JENIS LAPORAN --}}
                    <div>
                        <label class="block mb-1.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                            Jenis Laporan
                        </label>

                        <div class="flex items-center gap-6">
                            <label class="flex items-center gap-2 text-sm cursor-pointer">
                                <input type="radio" name="tipe" value="bulan" x-model="tipe"
                                    class="text-blue-600 focus:ring-blue-500"
                                    {{ request('tipe', 'bulan') == 'bulan' ? 'checked' : '' }}>
                                Per Bulan
                            </label>

                            <label class="flex items-center gap-2 text-sm cursor-pointer">
                                <input type="radio" name="tipe" value="tahun" x-model="tipe"
                                    class="text-blue-600 focus:ring-blue-500"
                                    {{ request('tipe') == 'tahun' ? 'checked' : '' }}>
                                Per Tahun
                            </label>
                        </div>
                    </div>

                    {{-- WRAPPER UNTUK TAMPILAN PERIODE / TAHUN --}}
                    <div class="relative min-w-[250px]">

                        {{-- PILIH PERIODE (BULAN) --}}
                        <div x-show="tipe === 'bulan'"
                            x-transition:enter="transition ease-out duration-300 transform"
                            x-transition:enter-start="opacity-0 translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-200 transform absolute top-0 left-0 w-full"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-2">

                            <label class="block mb-1.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                Pilih Periode
                            </label>

                            <select name="periode_id" :disabled="tipe !== 'bulan'"
                                :required="tipe === 'bulan'"
                                class="select2-periode w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
            dark:bg-gray-700 dark:text-white border-gray-300"
                                required>

                                <option value="">Pilih Periode...</option>

                                @foreach ($periodes as $periode)
                                <option value="{{ $periode->id }}"
                                    {{ request('periode_id') == $periode->id ? 'selected' : '' }}>
                                    {{ $periode->nama_periode }}
                                </option>
                                @endforeach

                            </select>
                        </div>

                        {{-- PILIH TAHUN --}}
                        <div x-show="tipe === 'tahun'"
                            x-transition:enter="transition ease-out duration-300 transform"
                            x-transition:enter-start="opacity-0 translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-200 transform absolute top-0 left-0 w-full"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-2">

                            <label class="block mb-1.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                Pilih Tahun
                            </label>

                            <select name="tahun" :disabled="tipe !== 'tahun'"
                                :required="tipe === 'tahun'"
                                class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                    dark:bg-gray-700 dark:text-white border-gray-300"
                                :required="tipe === 'tahun'">

                                <option value="">Pilih Tahun...</option>

                                @for ($i = now()->year; $i >= 2024; $i--)
                                <option value="{{ $i }}" {{ request('tahun') == $i ? 'selected' : '' }}>
                                    {{ $i }}
                                </option>
                                @endfor
                            </select>
                        </div>

                    </div>

                    <!-- UBS / Hub -->
                    <div class="w-full md:w-48">
                        <label class="block mb-1.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                            Pilih UBS / Hub
                        </label>

                        <select name="ubs_id" class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                                        dark:bg-gray-700 dark:text-white border-gray-300" required>

                            {{-- Opsi HUB (All) default --}}
                            <option value="all" {{ request('ubs_id', 'all') == 'all' ? 'selected' : '' }}>
                                HUB (Pusat)
                            </option>
                            @foreach ($ubsData as $ubs)
                            <option value="{{ $ubs->id }}" {{ request('ubs_id') == $ubs->id ? 'selected' : '' }}>
                                {{ $ubs->nama_ubs }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- BUTTON TAMPILKAN --}}
                    <div>
                        <button type="submit"
                            class="inline-flex items-center px-5 py-2.5 text-sm font-semibold text-white
                bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300
                transition-all shadow-sm shadow-blue-200 dark:shadow-none">

                            <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>

                            Tampilkan
                        </button>
                    </div>

                    {{-- EXPORT --}}
                    <div class="hidden lg:block w-px h-10 bg-gray-200 dark:bg-gray-700 mx-2"></div>

                    <div class="relative inline-block text-left" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false" type="button"
                            class="inline-flex items-center px-5 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-all shadow-sm">
                            <svg class="w-4 h-4 me-2 text-gray-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Export
                            <svg class="w-4 h-4 ms-2 transition-transform" :class="open ? 'rotate-180' : ''"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="open" x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            class="absolute right-0 z-20 mt-2 w-48 origin-top-right bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl ring-1 ring-black ring-opacity-5 focus:outline-none">
                            <div class="p-1">
                                <a href="{{ route('keuangan.neracaSaldo.exportPdf', request()->all()) }}"
                                    class="flex items-center w-full px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400 rounded-lg transition-colors group">
                                    <svg class="w-5 h-5 me-3 text-gray-400 group-hover:text-red-500" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path
                                            d="M9 2a2 2 0 00-2 2v8a2 2 0 002 2h6a2 2 0 002-2V6.414A2 2 0 0016.414 5L14 2.586A2 2 0 0012.586 2H9z" />
                                        <path d="M3 8a2 2 0 012-2v10h8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z" />
                                    </svg>
                                    Export as PDF
                                </a>
                                <a href="{{ route('keuangan.neracaSaldo.exportExcel', request()->all()) }}"
                                    class="flex items-center w-full px-4 py-3 text-sm text-gray-700 dark:text-gray-300
                                    hover:bg-green-50 dark:hover:bg-green-900/20
                                    hover:text-green-600 dark:hover:text-green-400 rounded-lg transition-colors group">
                                    <svg class="w-5 h-5 me-3 text-gray-400 group-hover:text-green-500"
                                        fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm2 10a1 1 0 10-2 0v3a1 1 0 102 0v-3zm2-3a1 1 0 011 1v5a1 1 0 11-2 0v-5a1 1 0 011-1zm4-1a1 1 0 10-2 0v7a1 1 0 102 0V8z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Export as Excel
                                </a>

                            </div>
                        </div>
                    </div>

                </div>
            </form>

            <!-- data -->
            <div class="space-y-6">
                <div class="bg-white dark:bg-gray-900 p-5 rounded-xl border border-gray-200 dark:border-gray-700">

                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
                                Neraca Saldo - {{ $ubsName }}
                            </h2>

                            @if($labelPeriode)
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Periode:
                                <span class="font-medium text-gray-700 dark:text-gray-300">
                                    {{ $labelPeriode }}
                                </span>
                                <br>
                                <span>
                                    {{ \Carbon\Carbon::parse($tanggalMulai)->format('d M Y') }}
                                    -
                                    {{ \Carbon\Carbon::parse($tanggalSelesai)->format('d M Y') }}
                                </span>
                            </p>
                            @else
                            <p class="text-sm text-gray-400 mt-1">
                                Silakan pilih periode atau tahun terlebih dahulu
                            </p>
                            @endif
                        </div>

                        {{-- INFO CARD --}}
                        <div class="md:max-w-md bg-blue-50/50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-800/30 p-4 rounded-xl flex gap-3 shadow-sm">
                            <div class="shrink-0 text-blue-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-bold text-blue-800 dark:text-blue-300 uppercase tracking-wider">Informasi Laporan</p>
                                <p class="text-[11px] leading-relaxed text-blue-700 dark:text-blue-400">
                                    • Tampilan <span class="font-semibold text-blue-900 dark:text-white underline">Web & PDF</span> hanya menyajikan hasil akhir saldo (akumulasi Mutasi Jurnal Umum + Jurnal Penyesuaian).
                                </p>
                                <p class="text-[11px] leading-relaxed text-blue-700 dark:text-blue-400">
                                    • Detail rincian (NS Awal, Penyesuaian, NS Akhir) tersedia lengkap pada <span class="font-semibold text-blue-900 dark:text-white underline">Export Excel</span> dalam format Neraca Lajur.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>



                {{-- TABLE --}}
                <div class="relative overflow-auto border border-gray-200 dark:border-gray-700 rounded-xl
                    bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 shadow-xl"
                    style="max-height: 700px;">
                    <table class="w-full text-sm text-left border-collapse">
                        {{-- HEADER --}}
                        <thead class="sticky top-0 z-20 bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
                            {{-- Header Row 1 --}}
                            <tr>
                                <th rowspan="2" class="px-6 py-4 border-b-2 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 font-bold text-gray-800 dark:text-white">
                                    Kode Akun
                                </th>
                                <th rowspan="2" class="px-6 py-4 border-b-2 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 font-bold text-gray-800 dark:text-white">
                                    Nama Akun
                                </th>
                                <th colspan="2" class="px-4 py-3 text-center bg-slate-50 dark:bg-slate-800/50 border-b border-gray-200 dark:border-gray-700 font-bold uppercase tracking-widest text-[10px]">
                                    Saldo Awal
                                </th>
                                <th colspan="2" class="px-4 py-3 text-center bg-slate-50 dark:bg-slate-800/50 border-b border-gray-200 dark:border-gray-700 font-bold uppercase tracking-widest text-[10px]">
                                    Mutasi (JU + JP)
                                </th>
                                <th colspan="2" class="px-4 py-3 text-center bg-blue-50 dark:bg-blue-900/10 border-b-2 border-blue-200 dark:border-blue-800 font-bold uppercase tracking-widest text-[10px] text-blue-700 dark:text-blue-400">
                                    Saldo Akhir
                                </th>
                            </tr>
                            {{-- Header Row 2 --}}
                            <tr class="text-center text-xs font-semibold">
                                <th class="px-4 py-2 border-b dark:border-gray-700 bg-slate-50/50 dark:bg-slate-800/30">Debit</th>
                                <th class="px-4 py-2 border-b dark:border-gray-700 bg-slate-50/50 dark:bg-slate-800/30">Kredit</th>
                                <th class="px-4 py-2 border-b dark:border-gray-700 bg-slate-50/50 dark:bg-slate-800/30">Debit</th>
                                <th class="px-4 py-2 border-b dark:border-gray-700 bg-slate-50/50 dark:bg-slate-800/30">Kredit</th>
                                <th class="px-4 py-2 border-b-2 dark:border-gray-700 bg-blue-50/20 dark:bg-blue-900/5 text-blue-600 dark:text-blue-400">Debit</th>
                                <th class="px-4 py-2 border-b-2 dark:border-gray-700 bg-blue-50/20 dark:bg-blue-900/5 text-blue-600 dark:text-blue-400">Kredit</th>
                            </tr>
                        </thead>

                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-800">
                            @php
                                $tSADebit = 0; $tSAKredit = 0;
                                $tMutDebit = 0; $tMutKredit = 0;
                                $tSakDebit = 0; $tSakKredit = 0;
                            @endphp

                            @forelse ($rows as $row)
                                @php
                                    $tSADebit += $row->sa_debit;
                                    $tSAKredit += $row->sa_kredit;
                                    $tMutDebit += $row->mutasi_debit;
                                    $tMutKredit += $row->mutasi_kredit;
                                    $tSakDebit += $row->sak_debit;
                                    $tSakKredit += $row->sak_kredit;
                                @endphp

                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-colors divide-x divide-gray-50 dark:divide-gray-800">
                                    <td class="px-6 py-4 font-mono text-gray-500 text-xs">{{ $row->kode_akun }}</td>
                                    <td class="px-6 py-4 font-medium text-gray-800 dark:text-gray-200">{{ $row->nama_akun }}</td>

                                    {{-- Saldo Awal --}}
                                    <td class="px-4 py-4 text-right text-sm">
                                        {{ $row->sa_debit > 0 ? number_format($row->sa_debit, 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-4 py-4 text-right text-sm">
                                        {{ $row->sa_kredit > 0 ? number_format($row->sa_kredit, 0, ',', '.') : '-' }}
                                    </td>

                                    {{-- Mutasi --}}
                                    <td class="px-4 py-4 text-right text-sm">
                                        {{ $row->mutasi_debit > 0 ? number_format($row->mutasi_debit, 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-4 py-4 text-right text-sm">
                                        {{ $row->mutasi_kredit > 0 ? number_format($row->mutasi_kredit, 0, ',', '.') : '-' }}
                                    </td>

                                    {{-- Saldo Akhir --}}
                                    <td class="px-4 py-4 text-right text-sm font-bold bg-blue-50/20 dark:bg-blue-900/5 text-gray-900 dark:text-white">
                                        {{ $row->sak_debit > 0 ? number_format($row->sak_debit, 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-4 py-4 text-right text-sm font-bold bg-blue-50/20 dark:bg-blue-900/5 text-gray-900 dark:text-white">
                                        {{ $row->sak_kredit > 0 ? number_format($row->sak_kredit, 0, ',', '.') : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-24">
                                        <div class="flex flex-col items-center text-gray-400">
                                            <svg class="w-16 h-16 mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <p class="text-base font-medium italic">Data tidak tersedia untuk periode ini.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                        {{-- FOOTER TOTAL --}}
                        @if(count($rows) > 0)
                            <tfoot class="sticky bottom-0 z-10 bg-gray-100 dark:bg-gray-800 font-bold border-t-2 border-gray-300 dark:border-gray-600 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
                                <tr class="divide-x divide-gray-200 dark:divide-gray-700">
                                    <td colspan="2" class="px-6 py-4 text-right text-gray-900 dark:text-white">TOTAL KESELURUHAN</td>
                                    <td class="px-4 py-4 text-right">{{ number_format($tSADebit, 0, ',', '.') }}</td>
                                    <td class="px-4 py-4 text-right">{{ number_format($tSAKredit, 0, ',', '.') }}</td>
                                    <td class="px-4 py-4 text-right">{{ number_format($tMutDebit, 0, ',', '.') }}</td>
                                    <td class="px-4 py-4 text-right">{{ number_format($tMutKredit, 0, ',', '.') }}</td>
                                    <td class="px-4 py-4 text-right bg-blue-100/50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400">{{ number_format($tSakDebit, 0, ',', '.') }}</td>
                                    <td class="px-4 py-4 text-right bg-blue-100/50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400">{{ number_format($tSakKredit, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>

                    </table>


                </div>


            </div>
        </div>


    </div>
    <!-- ===== Main Content End ===== -->

    <script>
        $(document).ready(function() {
            $('.select2-periode').select2({
                placeholder: "Pilih Periode",
                theme: 'bootstrap4',
                allowClear: true,
                width: '100%'
            });
        });
    </script>
    @endsection