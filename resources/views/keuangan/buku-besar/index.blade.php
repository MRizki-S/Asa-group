@extends('layouts.app')

@section('pageActive', 'BukuBesar')

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
        <div x-data="{ pageName: 'BukuBesar' }">
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

                {{-- filter and export to pdf-excel --}}
                <form method="GET" action="{{ route('keuangan.bukuBesar.index') }}"
                    class="mb-6 p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <div class="flex flex-wrap items-end gap-4">

                        <div class="flex items-center gap-2 pb-2.5">
                            <div class="p-2 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
                                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold text-gray-700 dark:text-white uppercase tracking-wider">Filter</h3>
                        </div>

                        <!-- Akun -->
                        <div class="flex-1 min-w-[250px]">
                            <label class="block mb-1.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                Pilih Akun
                            </label>

                            <select name="akun_id"
                                class="select2-akun w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
        dark:bg-gray-700 dark:text-white border-gray-300"
                                required>

                                <option value="">Pilih Akun...</option>

                                @foreach ($akunKeuangan as $kategori => $daftarAkun)
                                    <optgroup label="{{ $kategori }}">
                                        @foreach ($daftarAkun as $akun)
                                            <option value="{{ $akun->id }}"
                                                {{ request('akun_id') == $akun->id ? 'selected' : '' }}>
                                                {{ $akun->kode_akun }} - {{ $akun->nama_akun }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>


                        <!-- Periode -->
                        <div class="flex-1 min-w-[200px]">
                            <label class="block mb-1.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                Pilih Periode
                            </label>

                            <select name="periode_id"
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



                        <!-- Terapkan -->
                        <div class="flex items-center gap-2">
                            <button type="submit"
                                class="inline-flex items-center px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 transition-all shadow-sm shadow-blue-200 dark:shadow-none">
                                <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                Tampilkan
                            </button>
                        </div>

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
                                    <a href="{{ route('keuangan.bukuBesar.exportPdf', request()->all()) }}"
                                        class="flex items-center w-full px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400 rounded-lg transition-colors group">
                                        <svg class="w-5 h-5 me-3 text-gray-400 group-hover:text-red-500" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path
                                                d="M9 2a2 2 0 00-2 2v8a2 2 0 002 2h6a2 2 0 002-2V6.414A2 2 0 0016.414 5L14 2.586A2 2 0 0012.586 2H9z" />
                                            <path d="M3 8a2 2 0 012-2v10h8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z" />
                                        </svg>
                                        Export as PDF
                                    </a>
                                    <a href="{{ route('keuangan.bukuBesar.exportExcel', request()->all()) }}"
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

                <!-- Data -->
                <div class="space-y-6">
                
                    <!-- Menampilkan Buku Besar -->
                    {{-- HEADER INFO --}}
                    <div class="bg-white dark:bg-gray-900 p-6 rounded-xl border border-gray-200 dark:border-gray-700">
                        <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">
                            Buku Besar
                        </h2>

                        <div class="grid md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Akun</span>
                                <div class="font-medium text-gray-800 dark:text-white">
                                    @if($periodeAktif)
                                        @php
                                            $selectedAkun = $akunKeuangan->flatten()->firstWhere('id', request('akun_id'));
                                        @endphp
                                        {{ $selectedAkun ? $selectedAkun->nama_akun : '-' }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>

                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Kode Akun</span>
                                <div class="font-medium text-gray-800 dark:text-white">
                                    @if($periodeAktif)
                                        {{ $selectedAkun ? $selectedAkun->kode_akun : '-' }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>

                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Periode</span>
                                <div class="font-medium text-gray-800 dark:text-white">
                                    @if($periodeAktif)
                                        {{ $periodeAktif->nama_periode }}
                                        <span class="text-xs text-gray-500">
                                            ({{ \Carbon\Carbon::parse($periodeAktif->tanggal_mulai)->format('d M Y') }} -
                                            {{ \Carbon\Carbon::parse($periodeAktif->tanggal_selesai)->format('d M Y') }})
                                        </span>
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>


                    {{-- TABLE --}}
                    <div class="relative overflow-auto border border-gray-200 dark:border-gray-700 rounded-xl
                    bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200"
                        style="max-height: 600px;">
                        <table class="w-full text-sm text-left border-collapse">

                            {{-- HEADER --}}
                            <thead
                                class="sticky top-0 z-20
                   bg-gray-200 text-gray-700
                   dark:bg-gray-800 dark:text-gray-300 shadow-sm">
                                <tr>
                                    <th class="px-4 py-3">Tanggal</th>
                                    <th class="px-4 py-3">Keterangan</th>
                                    <th class="px-4 py-3 text-right">Debit</th>
                                    <th class="px-4 py-3 text-right">Kredit</th>
                                    <th class="px-4 py-3 text-right">Saldo</th>
                                </tr>
                            </thead>

                            <tbody class="bg-white dark:bg-gray-900">
                                @if ($periodeAktif)
                                    {{-- Saldo Awal --}}
                                    <tr
                                        class="border-b border-gray-200 dark:border-gray-700 font-medium bg-gray-50 dark:bg-white/5">
                                        <td class="px-4 py-3">
                                            {{ \Carbon\Carbon::parse($periodeAktif->tanggal_mulai)->format('d-m-Y') }}
                                        </td>
                                        <td class="px-4 py-3">Saldo Awal</td>
                                        
                                        {{-- Debit --}}
                                        <td class="px-4 py-3 text-right">
                                            {{ $saldoAwal > 0 ? 'Rp ' . number_format(abs($saldoAwal), 0, ',', '.') : '-' }}
                                        </td>
                                        
                                        {{-- Kredit --}}
                                        <td class="px-4 py-3 text-right">
                                            {{ $saldoAwal < 0 ? 'Rp ' . number_format(abs($saldoAwal), 0, ',', '.') : '-' }}
                                        </td>

                                        {{-- Saldo (Absolute) --}}
                                        <td class="px-4 py-3 text-right">
                                            {{ 'Rp ' . number_format(abs($saldoAwal), 0, ',', '.') }}
                                        </td>
                                    </tr>

                                    {{-- Loop Transaksi --}}
                                    @foreach ($rows as $row)
                                        <tr
                                            class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-white/5">
                                            <td class="px-4 py-3">
                                                {{ \Carbon\Carbon::parse($row->tanggal)->format('d-m-Y') }}
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="font-medium text-gray-900 dark:text-white">
                                                    {{ $row->keterangan }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $row->nomor_jurnal }}
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                {{ $row->debit > 0 ? 'Rp ' . number_format($row->debit, 0, ',', '.') : '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                {{ $row->kredit > 0 ? 'Rp ' . number_format($row->kredit, 0, ',', '.') : '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-right font-medium">
                                                {{ 'Rp ' . number_format(abs($row->saldo), 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                            Silakan pilih Akun dan Periode untuk menampilkan data.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>

                            {{-- FOOTER --}}
                            @if ($periodeAktif)
                                <tfoot
                                    class="sticky bottom-0 z-10 bg-gray-100 dark:bg-gray-800 font-semibold border-t-2 dark:border-gray-600">
                                    <tr class="font-semibold text-gray-900 dark:text-white">
                                        <td colspan="4" class="px-4 py-3 text-right uppercase tracking-wider text-xs">
                                            Saldo Akhir
                                        </td>
                                        <td class="px-4 py-3 text-right text-base text-blue-600 dark:text-blue-400">
                                            {{ 'Rp ' . number_format(abs($saldoAkhir), 0, ',', '.') }}
                                        </td>
                                    </tr>
                                </tfoot>
                            @endif

                        </table>

                    </div>

                </div>
            </div>


        </div>
        <!-- ===== Main Content End ===== -->

        <script>
            $(document).ready(function() {
                $('.select2-akun').select2({
                    placeholder: "Pilih Akun",
                    theme: 'bootstrap4',
                    allowClear: true,
                    width: '100%'
                });

                $('.select2-periode').select2({
                    placeholder: "Pilih Periode",
                    theme: 'bootstrap4',
                    allowClear: true,
                    width: '100%'
                });
            });
        </script>
    @endsection
