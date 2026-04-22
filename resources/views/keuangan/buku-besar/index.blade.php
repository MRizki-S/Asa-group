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
                    class="relative mb-6 p-6 pt-8 bg-white dark:bg-gray-800 rounded-xl border border-gray-200
                    dark:border-gray-700 shadow-sm">
                    <span class="absolute -top-3 left-5 px-3 py-1 text-xs font-semibold 
                    uppercase tracking-widest rounded-md
                    bg-white dark:bg-gray-800 
                    text-blue-600 dark:text-blue-400">
                        Filter Laporan
                    </span>

                    <div class="flex flex-wrap items-end gap-4">
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
                        <div class="flex-1 min-w-[250px]">
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
                            Buku Besar - {{ $ubsName }}
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
                                        
                                        @php
                                            $isKredit = in_array(strtolower($normalBalance), ['kredit', 'credit', 'cr']);
                                            $isDebitBalance = $isKredit ? $saldoAwal < 0 : $saldoAwal > 0;
                                            $isKreditBalance = $isKredit ? $saldoAwal > 0 : $saldoAwal < 0;
                                        @endphp
                                        
                                        {{-- Debit --}}
                                        <td class="px-4 py-3 text-right">
                                            {{ $isDebitBalance ? 'Rp ' . number_format(abs($saldoAwal), 0, ',', '.') : '-' }}
                                        </td>
                                        
                                        {{-- Kredit --}}
                                        <td class="px-4 py-3 text-right">
                                            {{ $isKreditBalance ? 'Rp ' . number_format(abs($saldoAwal), 0, ',', '.') : '-' }}
                                        </td>

                                        {{-- Saldo --}}
                                        <td class="px-4 py-3 text-right">
                                            {{ $saldoAwal < 0 ? '-Rp ' . number_format(abs($saldoAwal), 0, ',', '.') : 'Rp ' . number_format($saldoAwal, 0, ',', '.') }}
                                        </td>
                                    </tr>

                                    {{-- Group 1: Transaksi Rutin (Umum) --}}
                                    @forelse ($rowsUmum as $row)
                                        <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50/50 dark:hover:bg-white/5 transition-colors">
                                             <td class="px-4 py-3.5">
                                                 <div class="text-gray-900 dark:text-white font-medium">
                                                     {{ \Carbon\Carbon::parse($row->tanggal)->format('d-m-Y') }}
                                                 </div>
                                                 @if ($isHub)
                                                     <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 mt-1">
                                                         {{ $row->ubs_abbr }}
                                                     </span>
                                                 @endif
                                             </td>
                                             <td class="px-4 py-3.5">
                                                 <div class="text-sm font-medium text-gray-800 dark:text-gray-200 leading-tight">
                                                     {{ $row->keterangan }}
                                                 </div>
                                                 <div class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5 font-mono">
                                                     {{ $row->nomor_jurnal }}
                                                 </div>
                                             </td>
                                             <td class="px-4 py-3.5 text-right font-medium text-gray-600 dark:text-gray-400">
                                                 {{ $row->debit > 0 ? 'Rp ' . number_format($row->debit, 0, ',', '.') : '-' }}
                                             </td>
                                             <td class="px-4 py-3.5 text-right font-medium text-gray-600 dark:text-gray-400">
                                                 {{ $row->kredit > 0 ? 'Rp ' . number_format($row->kredit, 0, ',', '.') : '-' }}
                                             </td>
                                             <td class="px-4 py-3.5 text-right font-semibold text-gray-900 dark:text-white">
                                                 {{ $row->saldo < 0 ? '-Rp ' . number_format(abs($row->saldo), 0, ',', '.') : 'Rp ' . number_format($row->saldo, 0, ',', '.') }}
                                             </td>
                                        </tr>
                                    @empty
                                        <tr class="border-b border-gray-100 dark:border-gray-800 italic text-gray-400">
                                            <td colspan="5" class="px-4 py-4 text-center text-xs">Tidak ada transaksi rutin pada periode ini</td>
                                        </tr>
                                    @endforelse

                                    {{-- Sub-Total: Saldo Sebelum Penyesuaian --}}
                                    <tr class="bg-gray-50 dark:bg-gray-800/50 border-y border-gray-200 dark:border-gray-700">
                                        <td colspan="4" class="px-4 py-3 text-right">
                                            <span class="text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">
                                                Saldo Sebelum Penyesuaian
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white text-base">
                                            {{ $saldoAkhirUmum < 0 ? '-Rp ' . number_format(abs($saldoAkhirUmum), 0, ',', '.') : 'Rp ' . number_format($saldoAkhirUmum, 0, ',', '.') }}
                                        </td>
                                    </tr>

                                    {{-- Section Header: PENYESUAIAN (Calm Grey Style) --}}
                                    <tr class="bg-gray-200 dark:bg-gray-700 border-y border-gray-300 dark:border-gray-600">
                                        <td colspan="5" class="px-4 py-2 text-center">
                                            <span class="text-[10px] font-bold text-gray-600 dark:text-gray-300 uppercase tracking-[0.2em]">
                                                DATA JURNAL PENYESUAIAN
                                            </span>
                                        </td>
                                    </tr>

                                    {{-- Group 2: Transaksi Penyesuaian --}}
                                    @forelse ($rowsPenyesuaian as $row)
                                        <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50/50 dark:hover:bg-white/5 transition-colors">
                                            <td class="px-4 py-3.5">
                                                <div class="text-gray-900 dark:text-white font-medium">
                                                    {{ \Carbon\Carbon::parse($row->tanggal)->format('d-m-Y') }}
                                                </div>
                                            </td>
                                            <td class="px-4 py-3.5">
                                                <div class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                                    {{ $row->keterangan }}
                                                </div>
                                                <div class="text-[11px] text-gray-400 dark:text-gray-500 font-mono mt-0.5">
                                                    {{ $row->nomor_jurnal }}
                                                </div>
                                            </td>
                                            <td class="px-4 py-3.5 text-right font-medium text-gray-500">
                                                {{ $row->debit > 0 ? 'Rp ' . number_format($row->debit, 0, ',', '.') : '-' }}
                                            </td>
                                            <td class="px-4 py-3.5 text-right font-medium text-gray-500">
                                                {{ $row->kredit > 0 ? 'Rp ' . number_format($row->kredit, 0, ',', '.') : '-' }}
                                            </td>
                                            <td class="px-4 py-3.5 text-right font-semibold text-gray-900 dark:text-white">
                                                {{ $row->saldo < 0 ? '-Rp ' . number_format(abs($row->saldo), 0, ',', '.') : 'Rp ' . number_format($row->saldo, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="border-b border-gray-100 dark:border-gray-800 italic text-gray-300 dark:text-gray-600">
                                            <td colspan="5" class="px-4 py-6 text-center text-xs">
                                                -- Tidak ada data jurnal penyesuaian pada periode ini --
                                            </td>
                                        </tr>
                                    @endforelse

                                @else
                                    <tr>
                                        <td colspan="5" class="px-4 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center text-gray-400">
                                                <svg class="w-12 h-12 mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                                </svg>
                                                <p class="text-sm font-medium">Silakan pilih Akun dan Periode untuk memuat data laporan</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>

                            {{-- FOOTER FINAL (Calm Grey) --}}
                            @if ($periodeAktif)
                                <tfoot class="sticky bottom-0 z-10 bg-gray-100 dark:bg-gray-800 border-t-2 border-gray-300 dark:border-gray-600 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
                                    <tr>
                                        <td colspan="3" class="px-4 py-4"></td>
                                        <td class="px-4 py-4 text-right uppercase tracking-[.15em] text-[10px] font-bold text-gray-500 dark:text-gray-400">
                                            Saldo Akhir Jurnal (Final)
                                        </td>
                                        <td class="px-4 py-4 text-right text-base font-black text-gray-900 dark:text-white">
                                            {{ $saldoAkhirTotal < 0 ? '-Rp ' . number_format(abs($saldoAkhirTotal), 0, ',', '.') : 'Rp ' . number_format($saldoAkhirTotal, 0, ',', '.') }}
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
