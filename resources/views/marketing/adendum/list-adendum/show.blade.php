@extends('layouts.app')

@section('pageActive', 'ListAdendum')

@section('content')
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">

    <!-- ===== Main Content Start ===== -->
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: 'ListAdendum' }">
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


        {{-- Pilih Pemesanan Unit - Tampil Data Lama --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6 p-6">


            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-6 border-b pb-1">
                Akun User & Unit Dipesan
            </h3>

            {{-- akun user dan tanggal pemesanan --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-2">
                {{-- Akun Customer --}}
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">
                        Akun Customer
                    </label>

                    <input type="text"
                        value="{{ $dataAdendum->pemesananUnit->customer->nama_lengkap ?? '-' }} — {{ $dataAdendum->pemesananUnit->unit->nama_unit ?? '-' }}"
                        readonly
                        class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed" />
                </div>


                <!-- Tanggal Adendum -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tanggal Adendum <span class="text-red-500">*</span>
                    </label>

                    <div class="relative">
                        <!-- Icon Kalender -->
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                            </svg>
                        </div>

                        <!-- Input tampilan readonly -->
                        <input type="text" value="{{ $dataAdendum->tanggal_adendum?->format('d-m-Y') }}" readonly
                            class="bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg
                                                                                                                                                           block w-full ps-10 p-2.5 cursor-default
                                                                                                                                                           dark:bg-gray-700 dark:border-gray-600 dark:text-white">

                        <!-- Input hidden format Y-m-d (jika butuh submit) -->
                        <input type="hidden" name="tanggal_adendum"
                            value="{{ $dataAdendum->tanggal_adendum?->format('Y-m-d') }}">
                    </div>
                </div>

            </div>

            {{-- blok perumahaan, tahap, unit --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">

                {{-- Perumahaan --}}
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Perumahaan
                    </label>
                    <input type="text" readonly
                        value="{{ $dataAdendum->pemesananUnit->perumahaan->nama_perumahaan ?? '-' }}"
                        class="w-full bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg p-2.5 cursor-not-allowed">
                </div>

                {{-- Tahap --}}
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tahap
                    </label>
                    <input type="text" readonly value="{{ $dataAdendum->pemesananUnit->tahap->nama_tahap ?? '-' }}"
                        class="w-full bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg p-2.5 cursor-not-allowed">
                </div>

                {{-- Unit --}}
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Unit
                    </label>
                    <input type="text" readonly value="{{ $dataAdendum->pemesananUnit->unit->nama_unit ?? '-' }}"
                        class="w-full bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg p-2.5 cursor-not-allowed">
                </div>

            </div>



            @if($dataAdendum->subCaraBayar && $dataAdendum->subCaraBayar->cara_bayar_lama == 'kpr')
                <div class="mt-6">
                    <!-- INFORMASI UMUM (Cara Bayar, Total Tagihan, Sisa Tagihan) -->
                    <div class="mt-4 mb-4 border-b pb-3 border-gray-300 dark:border-gray-700">
                        <table class="text-sm w-auto">
                            <tbody>

                                <!-- Cara Bayar -->
                                <tr>
                                    <td class="text-gray-600 dark:text-gray-300 pr-4">Cara Bayar</td>
                                    <td class="pr-2">:</td>
                                    <td class="font-medium text-gray-900 dark:text-white">
                                        {{ strtoupper($dataAdendum->subCaraBayar->cara_bayar_lama ?? '-') }}
                                    </td>
                                </tr>

                                <!-- Total Tagihan (dari data lama adendum) -->
                                <tr>
                                    <td class="text-gray-600 dark:text-gray-300 pr-4">Total Tagihan</td>
                                    <td class="pr-2">:</td>
                                    <td class="font-medium text-gray-900 dark:text-white">
                                        Rp {{ number_format($dataAdendum->pemesananUnit->total_tagihan ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>

                                <!-- Sisa Tagihan (dari pemesananUnit) -->
                                <tr>
                                    <td class="text-gray-600 dark:text-gray-300 pr-4">Sisa Tagihan</td>
                                    <td class="pr-2">:</td>
                                    <td class="font-medium text-gray-900 dark:text-white">
                                        Rp {{ number_format($dataAdendum->pemesananUnit->sisa_tagihan ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>

                    <!-- Informasi Data KPR LAMA -->
                    <table class="w-full border border-gray-300 dark:border-gray-700 rounded-lg text-sm">
                        <thead class="bg-gray-100 dark:bg-gray-800">
                            <tr>
                                <th class="border dark:border-gray-700 p-2 w-12 text-gray-700 dark:text-gray-200">No</th>
                                <th class="border dark:border-gray-700 p-2 text-gray-700 dark:text-gray-200">Keterangan</th>
                                <th class="border dark:border-gray-700 p-2 w-48 text-gray-700 dark:text-gray-200">Jumlah
                                    Pembayaran</th>
                            </tr>
                        </thead>

                        <tbody class="bg-white dark:bg-gray-900">

                            <tr>
                                <td class="border dark:border-gray-700 p-2 text-center text-gray-800 dark:text-gray-300">1
                                </td>
                                <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300">
                                    DP Rumah Induk (Termasuk SBUM dari Pemerintah)
                                </td>
                                <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300 font-medium ">
                                    Rp {{ number_format($detailSubCaraBayar['lama']['dp_rumah_induk'] ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>

                            <tr>
                                <td class="border dark:border-gray-700 p-2 text-center text-gray-800 dark:text-gray-300">2
                                </td>
                                <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300">
                                    Kelebihan Tanah {{ $detailSubCaraBayar['lama']['luas_kelebihan'] ?? '' }}
                                </td>
                                <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300 font-medium ">
                                    Rp
                                    {{ number_format($detailSubCaraBayar['lama']['nominal_kelebihan'] ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>

                            <tr>
                                <td class="border dark:border-gray-700 p-2 text-center text-gray-800 dark:text-gray-300">3
                                </td>
                                <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300">Total DP</td>
                                <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300 font-medium ">
                                    Rp {{ number_format($detailSubCaraBayar['lama']['total_dp'] ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>

                            <tr>
                                <td colspan="3"
                                    class="border dark:border-gray-700 p-2 text-center font-semibold bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                    - -
                                </td>
                            </tr>

                            <tr>
                                <td class="border dark:border-gray-700 p-2 text-center text-gray-800 dark:text-gray-300">1
                                </td>
                                <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300">DP Dibayarkan
                                    Pembeli</td>
                                <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300 font-medium ">
                                    Rp
                                    {{ number_format($detailSubCaraBayar['lama']['dp_dibayarkan_pembeli'] ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>

                            <tr>
                                <td class="border dark:border-gray-700 p-2 text-center text-gray-800 dark:text-gray-300">2
                                </td>
                                <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300">SBUM Dari
                                    Pemerintah</td>
                                <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300 font-medium ">
                                    Rp
                                    {{ number_format($detailSubCaraBayar['lama']['sbum_dari_pemerintah'] ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>

                            <tr>
                                <td class="border dark:border-gray-700 p-2 text-center text-gray-800 dark:text-gray-300">3
                                </td>
                                <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300">KPR</td>
                                <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300 font-medium ">
                                    Rp {{ number_format($detailSubCaraBayar['lama']['harga_kpr'] ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>

                            <tr>
                                <td class="border dark:border-gray-700 p-2 text-center text-gray-800 dark:text-gray-300">4
                                </td>
                                <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300">Harga Total
                                </td>
                                <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300 font-medium ">
                                    Rp {{ number_format($detailSubCaraBayar['lama']['harga_total'] ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>

                        </tbody>

                    </table>

                    <br>
                    <!-- Cicilan Data KPR LAMA -->
                    <table class="w-full border border-gray-300 dark:border-gray-700 rounded-lg text-sm">
                        <thead class="bg-gray-100 dark:bg-gray-800">
                            <tr>
                                <th class="border dark:border-gray-700 p-2 w-12 text-gray-700 dark:text-gray-200">No</th>
                                <th class="border dark:border-gray-700 p-2 text-gray-700 dark:text-gray-200">Keterangan
                                </th>
                                <th class="border dark:border-gray-700 p-2 w-40 text-gray-700 dark:text-gray-200">Tanggal
                                    Bayar</th>
                                <th class="border dark:border-gray-700 p-2 w-48 text-gray-700 dark:text-gray-200">Nominal
                                </th>
                            </tr>
                        </thead>

                        <tbody class="bg-white dark:bg-gray-900">
                            @forelse($detailSubCaraBayar['lama']['cicilan'] ?? [] as $index => $item)
                                <tr>
                                    <td class="border dark:border-gray-700 p-2 text-center text-gray-800 dark:text-gray-300">
                                        {{ $index + 1 }}
                                    </td>

                                    <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300">
                                        Pembayaran ke {{ $item['pembayaran_ke'] ?? '-' }}
                                    </td>

                                    <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300">
                                        {{ isset($item['tanggal_jatuh_tempo']) ? \Carbon\Carbon::parse($item['tanggal_jatuh_tempo'])->translatedFormat('d F Y') : '-' }}
                                    </td>

                                    <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300 font-medium">
                                        Rp {{ number_format($item['nominal'] ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4"
                                        class="border dark:border-gray-700 p-2 text-center text-gray-600 dark:text-gray-400">
                                        Tidak ada cicilan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>


                    </table>
                </div>
            @elseif($dataAdendum->subCaraBayar->cara_bayar_lama == 'cash')
                <div class="mt-6">
                    <!-- INFORMASI UMUM (Cara Bayar, Total Tagihan, Sisa Tagihan) -->
                    <div class="mt-4 mb-4 border-b pb-3 border-gray-300 dark:border-gray-700">
                        <table class="text-sm w-auto">
                            <tbody>

                                <!-- Cara Bayar -->
                                <tr>
                                    <td class="text-gray-600 dark:text-gray-300 pr-4">Cara Bayar</td>
                                    <td class="text-gray-700 dark:text-gray-400 pr-2">:</td>
                                    <td class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ strtoupper($dataAdendum->pemesananUnit->cara_bayar ?? '-') }}
                                    </td>
                                </tr>

                                <!-- Total Tagihan (dari data lama adendum) -->
                                <tr>
                                    <td class="text-gray-600 dark:text-gray-300 pr-4">Total Tagihan</td>
                                    <td class="pr-2">:</td>
                                    <td class="font-medium text-gray-900 dark:text-white">
                                        Rp {{ number_format($dataAdendum->pemesananUnit->total_tagihan ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>

                                <!-- Sisa Tagihan (dari pemesananUnit) -->
                                <tr>
                                    <td class="text-gray-600 dark:text-gray-300 pr-4">Sisa Tagihan</td>
                                    <td class="pr-2">:</td>
                                    <td class="font-medium text-gray-900 dark:text-white">
                                        Rp {{ number_format($dataAdendum->pemesananUnit->sisa_tagihan ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>



                    <!-- TABLE 1 — DETAIL CASH -->
                    <table class="w-full border border-gray-300 dark:border-gray-700 rounded-lg text-sm">
                        <thead class="bg-gray-100 dark:bg-gray-800">
                            <tr>
                                <th class="border dark:border-gray-700 p-2 w-12 text-gray-800 dark:text-gray-200">No</th>
                                <th class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-200">Keterangan
                                </th>
                                <th class="border dark:border-gray-700 p-2 w-48 text-gray-800 dark:text-gray-200">Jumlah
                                    Pembayaran</th>
                            </tr>
                        </thead>

                        <tbody>
                            <!-- 1 -->
                            <tr>
                                <td class="border dark:border-gray-700 p-2 text-center text-gray-900 dark:text-gray-100">1
                                </td>
                                <td class="border dark:border-gray-700 p-2 text-gray-900 dark:text-gray-100">
                                    Harga Rumah Type {{ $dataAdendum->pemesananUnit->unit->type->nama_type }}
                                </td>
                                <td class="border dark:border-gray-700 p-2 text-gray-900 dark:text-gray-100">
                                    Rp {{ number_format($detailSubCaraBayar['lama']['harga_rumah'] ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>

                            <!-- 2 -->
                            <tr>
                                <td class="border dark:border-gray-700 p-2 text-center text-gray-900 dark:text-gray-100">2
                                </td>
                                <td class="border dark:border-gray-700 p-2 text-gray-900 dark:text-gray-100">
                                    Kelebihan Tanah {{ $detailSubCaraBayar['lama']['luas_kelebihan'] ?? '' }}
                                </td>
                                <td class="border dark:border-gray-700 p-2 text-gray-900 dark:text-gray-100">
                                    Rp {{ number_format($detailSubCaraBayar['lama']['harga_kelebihan'] ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>

                            <!-- 3 -->
                            <tr>
                                <td class="border dark:border-gray-700 p-2 text-center text-gray-900 dark:text-gray-100">3
                                </td>
                                <td class="border dark:border-gray-700 p-2 text-gray-900 dark:text-gray-100">Harga Jadi
                                </td>
                                <td class="border dark:border-gray-700 p-2 text-gray-900 dark:text-gray-100">
                                    Rp {{ number_format($detailSubCaraBayar['lama']['harga_jadi'] ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <br>
                    <!-- TABLE 2 — CICILAN / PEMBAYARAN CASH -->
                    <table class="w-full border border-gray-300 dark:border-gray-700 rounded-lg text-sm">
                        <thead class="bg-gray-100 dark:bg-gray-800">
                            <tr>
                                <th class="border dark:border-gray-700 p-2 w-12 text-gray-700 dark:text-gray-200">No</th>
                                <th class="border dark:border-gray-700 p-2 text-gray-700 dark:text-gray-200">Keterangan
                                </th>
                                <th class="border dark:border-gray-700 p-2 w-40 text-gray-700 dark:text-gray-200">Tanggal
                                    Bayar</th>
                                <th class="border dark:border-gray-700 p-2 w-48 text-gray-700 dark:text-gray-200">Nominal
                                </th>
                            </tr>
                        </thead>

                        <tbody class="bg-white dark:bg-gray-900">
                            @forelse($detailSubCaraBayar['lama']['cicilan'] ?? [] as $index => $item)
                                <tr>
                                    <td class="border dark:border-gray-700 p-2 text-center text-gray-800 dark:text-gray-300">
                                        {{ $index + 1 }}
                                    </td>

                                    <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300">
                                        Pembayaran ke {{ $item['pembayaran_ke'] ?? '-' }}
                                    </td>

                                    <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300">
                                        {{ isset($item['tanggal_jatuh_tempo']) ? \Carbon\Carbon::parse($item['tanggal_jatuh_tempo'])->translatedFormat('d F Y') : '-' }}
                                    </td>

                                    <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300 font-medium">
                                        Rp {{ number_format($item['nominal'] ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4"
                                        class="border dark:border-gray-700 p-2 text-center text-gray-600 dark:text-gray-400">
                                        Tidak ada cicilan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                </div>
            @endif
        </div>

        {{-- Adendum Data Baru --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">

            <!-- Judul -->
            <div class="px-5 py-5 border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/30">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    Adendum
                    <span class="text-xs px-2 py-0.5 rounded-full bg-blue-600 text-white">
                        Form Perubahan
                    </span>
                </h3>
            </div>

            <!-- 🔘 Cara Bayar Baru-->
            <div class="px-5 py-4 sm:px-6 sm:py-5">
                <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        Sistem Pembayaran
                    </h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Cara Bayar Baru
                        </label>

                        <input type="text" name="cara_bayar_baru"
                            value="{{ strtoupper($dataAdendum->subCaraBayar->cara_bayar_baru) }}" readonly class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5
                                                                    dark:bg-gray-700 dark:text-white dark:border-gray-600">

                        @error('cara_bayar_baru')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

            </div>

            <!-- 💵 Table Adendum CASH Baru -->
            @if($dataAdendum->subCaraBayar->cara_bayar_baru == 'cash')
                <div class="px-5 py-4 sm:px-6 sm:py-5 border-t border-gray-100 dark:border-gray-800">
                    <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Sistem Pembayaran</h3>
                        <span
                            class="inline-flex items-center px-3 py-1 text-sm font-semibold text-yellow-800 bg-yellow-100 rounded-full border border-yellow-300 dark:bg-yellow-900/30 dark:text-yellow-300">
                            CASH
                        </span>
                    </div>

                    <div class="mt-6">
                        <!-- TABLE 1 — DETAIL CASH -->
                        <table class="w-full border border-gray-300 dark:border-gray-700 rounded-lg text-sm">
                            <thead class="bg-gray-100 dark:bg-gray-800">
                                <tr>
                                    <th class="border dark:border-gray-700 p-2 w-12 text-gray-800 dark:text-gray-200">No
                                    </th>
                                    <th class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-200">Keterangan
                                    </th>
                                    <th class="border dark:border-gray-700 p-2 w-48 text-gray-800 dark:text-gray-200">Jumlah
                                        Pembayaran</th>
                                </tr>
                            </thead>

                            <tbody>
                                <!-- 1 -->
                                <tr>
                                    <td class="border dark:border-gray-700 p-2 text-center text-gray-900 dark:text-gray-100">
                                        1
                                    </td>
                                    <td class="border dark:border-gray-700 p-2 text-gray-900 dark:text-gray-100">
                                        Harga Rumah Type {{ $dataAdendum->pemesananUnit->unit->type->nama_type }}
                                    </td>
                                    <td class="border dark:border-gray-700 p-2 text-gray-900 dark:text-gray-100">
                                        Rp {{ number_format($detailSubCaraBayar['baru']['harga_rumah'] ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>

                                <!-- 2 -->
                                <tr>
                                    <td class="border dark:border-gray-700 p-2 text-center text-gray-900 dark:text-gray-100">
                                        2
                                    </td>
                                    <td class="border dark:border-gray-700 p-2 text-gray-900 dark:text-gray-100">
                                        Kelebihan Tanah {{ $detailSubCaraBayar['baru']['luas_kelebihan'] ?? '' }}
                                    </td>
                                    <td class="border dark:border-gray-700 p-2 text-gray-900 dark:text-gray-100">
                                        Rp
                                        {{ number_format($detailSubCaraBayar['baru']['harga_kelebihan'] ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>

                                <!-- 3 -->
                                <tr>
                                    <td class="border dark:border-gray-700 p-2 text-center text-gray-900 dark:text-gray-100">
                                        3
                                    </td>
                                    <td class="border dark:border-gray-700 p-2 text-gray-900 dark:text-gray-100">Harga Jadi
                                    </td>
                                    <td class="border dark:border-gray-700 p-2 text-gray-900 dark:text-gray-100">
                                        Rp {{ number_format($detailSubCaraBayar['baru']['harga_jadi'] ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <br>
                        <!-- TABLE 2 — CICILAN / PEMBAYARAN CASH -->
                        <table class="w-full border border-gray-300 dark:border-gray-700 rounded-lg text-sm">
                            <thead class="bg-gray-100 dark:bg-gray-800">
                                <tr>
                                    <th class="border dark:border-gray-700 p-2 w-12 text-gray-700 dark:text-gray-200">No
                                    </th>
                                    <th class="border dark:border-gray-700 p-2 text-gray-700 dark:text-gray-200">Keterangan
                                    </th>
                                    <th class="border dark:border-gray-700 p-2 w-40 text-gray-700 dark:text-gray-200">
                                        Tanggal
                                        Bayar</th>
                                    <th class="border dark:border-gray-700 p-2 w-48 text-gray-700 dark:text-gray-200">
                                        Nominal
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="bg-white dark:bg-gray-900">
                                @forelse($detailSubCaraBayar['baru']['cicilan'] ?? [] as $index => $item)
                                    <tr>
                                        <td class="border dark:border-gray-700 p-2 text-center text-gray-800 dark:text-gray-300">
                                            {{ $index + 1 }}
                                        </td>

                                        <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300">
                                            Pembayaran ke {{ $item['pembayaran_ke'] ?? '-' }}
                                        </td>

                                        <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300">
                                            {{ isset($item['tanggal_jatuh_tempo']) ? \Carbon\Carbon::parse($item['tanggal_jatuh_tempo'])->translatedFormat('d F Y') : '-' }}
                                        </td>

                                        <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300 font-medium">
                                            Rp {{ number_format($item['nominal'] ?? 0, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4"
                                            class="border dark:border-gray-700 p-2 text-center text-gray-600 dark:text-gray-400">
                                            Tidak ada cicilan
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @elseif($dataAdendum->subCaraBayar->cara_bayar_baru == 'kpr')
                <div class="px-5 py-4 sm:px-6 sm:py-5 border-t border-gray-100 dark:border-gray-800">
                    <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Sistem Pembayaran</h3>
                        <span
                            class="inline-flex items-center px-3 py-1 text-sm font-semibold text-yellow-800 bg-yellow-100 rounded-full border border-yellow-300 dark:bg-yellow-900/30 dark:text-yellow-300">
                            KPR
                        </span>
                    </div>

                    <div class="mt-6">
                        <!-- Informasi Data KPR LAMA -->
                        <table class="w-full border border-gray-300 dark:border-gray-700 rounded-lg text-sm">
                            <thead class="bg-gray-100 dark:bg-gray-800">
                                <tr>
                                    <th class="border dark:border-gray-700 p-2 w-12 text-gray-700 dark:text-gray-200">No
                                    </th>
                                    <th class="border dark:border-gray-700 p-2 text-gray-700 dark:text-gray-200">Keterangan
                                    </th>
                                    <th class="border dark:border-gray-700 p-2 w-48 text-gray-700 dark:text-gray-200">Jumlah
                                        Pembayaran</th>
                                </tr>
                            </thead>

                            <tbody class="bg-white dark:bg-gray-900">

                                <tr>
                                    <td class="border dark:border-gray-700 p-2 text-center text-gray-800 dark:text-gray-300">
                                        1
                                    </td>
                                    <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300">
                                        DP Rumah Induk (Termasuk SBUM dari Pemerintah)
                                    </td>
                                    <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300 font-medium ">
                                        Rp
                                        {{ number_format($detailSubCaraBayar['baru']['dp_rumah_induk'] ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>

                                <tr>
                                    <td class="border dark:border-gray-700 p-2 text-center text-gray-800 dark:text-gray-300">
                                        2
                                    </td>
                                    <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300">
                                        Kelebihan Tanah {{ $detailSubCaraBayar['baru']['luas_kelebihan'] ?? '' }}
                                    </td>
                                    <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300 font-medium ">
                                        Rp
                                        {{ number_format($detailSubCaraBayar['baru']['nominal_kelebihan'] ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>

                                <tr>
                                    <td class="border dark:border-gray-700 p-2 text-center text-gray-800 dark:text-gray-300">
                                        3
                                    </td>
                                    <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300">Total DP
                                    </td>
                                    <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300 font-medium ">
                                        Rp {{ number_format($detailSubCaraBayar['baru']['total_dp'] ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>

                                <tr>
                                    <td colspan="3"
                                        class="border dark:border-gray-700 p-2 text-center font-semibold bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                        - -
                                    </td>
                                </tr>

                                <tr>
                                    <td class="border dark:border-gray-700 p-2 text-center text-gray-800 dark:text-gray-300">
                                        1
                                    </td>
                                    <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300">DP
                                        Dibayarkan
                                        Pembeli</td>
                                    <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300 font-medium ">
                                        Rp
                                        {{ number_format($detailSubCaraBayar['baru']['dp_dibayarkan_pembeli'] ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>

                                <tr>
                                    <td class="border dark:border-gray-700 p-2 text-center text-gray-800 dark:text-gray-300">
                                        2
                                    </td>
                                    <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300">SBUM Dari
                                        Pemerintah</td>
                                    <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300 font-medium ">
                                        Rp
                                        {{ number_format($detailSubCaraBayar['baru']['sbum_dari_pemerintah'] ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>

                                <tr>
                                    <td class="border dark:border-gray-700 p-2 text-center text-gray-800 dark:text-gray-300">
                                        3
                                    </td>
                                    <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300">KPR</td>
                                    <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300 font-medium ">
                                        Rp {{ number_format($detailSubCaraBayar['baru']['harga_kpr'] ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>

                                <tr>
                                    <td class="border dark:border-gray-700 p-2 text-center text-gray-800 dark:text-gray-300">
                                        4
                                    </td>
                                    <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300">Harga Total
                                    </td>
                                    <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300 font-medium ">
                                        Rp {{ number_format($detailSubCaraBayar['baru']['harga_total'] ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>

                            </tbody>

                        </table>

                        <br>
                        <!-- Cicilan Data KPR LAMA -->
                        <table class="w-full border border-gray-300 dark:border-gray-700 rounded-lg text-sm">
                            <thead class="bg-gray-100 dark:bg-gray-800">
                                <tr>
                                    <th class="border dark:border-gray-700 p-2 w-12 text-gray-700 dark:text-gray-200">No
                                    </th>
                                    <th class="border dark:border-gray-700 p-2 text-gray-700 dark:text-gray-200">Keterangan
                                    </th>
                                    <th class="border dark:border-gray-700 p-2 w-40 text-gray-700 dark:text-gray-200">
                                        Tanggal
                                        Bayar</th>
                                    <th class="border dark:border-gray-700 p-2 w-48 text-gray-700 dark:text-gray-200">
                                        Nominal
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="bg-white dark:bg-gray-900">
                                @forelse($detailSubCaraBayar['baru']['cicilan'] ?? [] as $index => $item)
                                    <tr>
                                        <td class="border dark:border-gray-700 p-2 text-center text-gray-800 dark:text-gray-300">
                                            {{ $index + 1 }}
                                        </td>

                                        <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300">
                                            Pembayaran ke {{ $item['pembayaran_ke'] ?? '-' }}
                                        </td>

                                        <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300">
                                            {{ isset($item['tanggal_jatuh_tempo']) ? \Carbon\Carbon::parse($item['tanggal_jatuh_tempo'])->translatedFormat('d F Y') : '-' }}
                                        </td>

                                        <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300 font-medium">
                                            Rp {{ number_format($item['nominal'] ?? 0, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4"
                                            class="border dark:border-gray-700 p-2 text-center text-gray-600 dark:text-gray-400">
                                            Tidak ada cicilan
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>



        <!-- Tombol Aksi -->
        <div class="flex justify-end gap-2">
            {{-- Tombol Kembali --}}
            <button type="button" onclick="history.back()" class="px-8 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300
                dark:text-white dark:bg-gray-700 dark:hover:bg-gray-600">
                Kembali
            </button>

            @if ($dataAdendum->status === 'acc')
                {{-- SUDAH ACC --}}
                <span class="px-4 py-2 text-sm font-medium text-green-700 bg-green-100 border border-green-300 rounded-lg">
                    Sudah Disetujui
                </span>

            @elseif ($dataAdendum->status === 'tolak')
                {{-- SUDAH DITOLAK --}}
                <span class="px-4 py-2 text-sm font-medium text-red-700 bg-red-100 border border-red-300 rounded-lg">
                    Pengajuan Ditolak
                </span>
            @endif

            @hasrole(['Super Admin', 'Manager Keuangan'])

            @if ($dataAdendum->status === 'pending')

                {{-- TOMBOL TOLAK --}}
                <form action="{{ route('marketing.adendum.reject', $dataAdendum->id) }}" method="POST" class="tolak-form">
                    @csrf
                    @method('PATCH')
                    <button type="button" class="tolak-btn px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg shadow-md
                                        hover:bg-red-700 hover:shadow-lg transition duration-200 ease-in-out">
                        Tolak
                    </button>
                </form>

                {{-- TOMBOL APPROVE --}}
                <form action="{{ route('marketing.adendum.approve', $dataAdendum->id) }}" method="POST" class="approve-form">
                    @csrf
                    @method('PATCH')
                    <button type="button" class="approve-btn px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-md
                                        hover:bg-blue-700 hover:shadow-lg transition duration-200 ease-in-out">
                        Acc / Approve
                    </button>
                </form>
            @endif

            @endhasrole
        </div>


    </div>

    </div>
    <!-- ===== Main Content End ===== -->

    <script>
        document.addEventListener('click', function (e) {
            // Tombol Tolak
            if (e.target.closest('.tolak-btn')) {
                const btn = e.target.closest('.tolak-btn');
                const form = btn.closest('.tolak-form');

                Swal.fire({
                    title: 'Tolak Pengajuan?',
                    text: "Apakah Anda yakin ingin menolak pengajuan ini?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Tolak',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            }

            // Tombol Approve
            if (e.target.closest('.approve-btn')) {
                const btn = e.target.closest('.approve-btn');
                const form = btn.closest('.approve-form');

                Swal.fire({
                    title: 'Setujui Pengajuan?',
                    text: "Apakah Anda yakin ingin menyetujui pengajuan ini?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#aaa',
                    cancelButtonText: 'Batal',
                    confirmButtonText: 'Ya, Setujui',
                    reverseButtons: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            }
        });
    </script>

@endsection