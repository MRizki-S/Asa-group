@extends('layouts.app')

@section('pageActive', 'BuatAdendum')

@section('content')
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">

    <!-- ===== Main Content Start ===== -->
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: 'BuatAdendum' }">
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

        <form x-data="adendumForm" method="POST" action="{{ route('marketing.adendum.store') }}">
            @csrf
            {{-- Pilih Pemesanan Unit - Tampil Data Lama --}}
            <div x-init="initSelect2()"
                class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6 p-6">


                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-6 border-b pb-1">
                    Akun User & Unit Dipesan
                </h3>

                {{-- akun user dan tanggal pemesanan --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-2">
                    {{-- Akun Customer --}}
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Akun Customer <span class="text-red-500">*</span>
                        </label>
                        <select id="selectUser" name="user_id" required
                            class="select-user w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5">
                            <option value="">Pilih Akun Customer</option>

                            <template x-for="c in customers" :key="c.id">
                                <option :value="c.id"
                                    x-text="c.nama_lengkap + ' — ' + (c.pemesanan?.nama_unit ?? '-')">
                                </option>
                            </template>
                        </select>

                    </div>

                    <!-- Tanggal Adendum -->
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Tanggal Adendum <span class="text-red-500">*</span>
                        </label>

                        <div class="relative" x-data="{
                            tampil: '{{ now()->format('d-m-Y') }}',
                            simpan: '{{ now()->format('Y-m-d') }}'
                        }">
                            <!-- Icon Kalender -->
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                                </svg>
                            </div>

                            <!-- Input tampilan -->
                            <input type="text" x-model="tampil" x-init="flatpickr($el, {
                                dateFormat: 'd-m-Y',
                                defaultDate: tampil,
                                onChange: (dates, dateStr) => {
                                    tampil = dateStr;
                                    // ubah ke format Y-m-d untuk dikirim
                                    const d = dates[0];
                                    simpan = d.getFullYear() + '-' +
                                        ('0' + (d.getMonth() + 1)).slice(-2) + '-' +
                                        ('0' + d.getDate()).slice(-2);
                                }
                            })" placeholder="Pilih tanggal"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg
               focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5
               dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400
               dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">

                            <!-- Input hidden format Y-m-d -->
                            <input type="hidden" name="tanggal_adendum" x-model="simpan">
                        </div>
                    </div>
                </div>

                {{-- blok perumahaan, tahap, unit --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                    {{-- Perumahaan --}}
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Perumahaan</label>
                        <input type="text" readonly :value="selectedCustomer?.pemesanan?.nama_perumahaan ?? ''"
                            placeholder="otomatis dari akun user yang dipilih"
                            class="w-full bg-gray-50 border border-gray-200 text-gray-500 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300">
                        <input type="hidden" name="perumahaan_id"
                            :value="selectedCustomer?.pemesanan?.perumahaan_id ?? ''">
                    </div>

                    {{-- Tahap --}}
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Tahap</label>
                        <input type="text" readonly :value="selectedCustomer?.pemesanan?.nama_tahap ?? ''"
                            placeholder="otomatis dari akun user yang dipilih"
                            class="w-full bg-gray-50 border border-gray-200 text-gray-500 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300">
                        <input type="hidden" name="tahap_id" :value="selectedCustomer?.pemesanan?.tahap_id ?? ''">
                    </div>

                    {{-- Unit --}}
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Unit</label>
                        <input type="text" readonly :value="selectedCustomer?.pemesanan?.nama_unit ?? ''"
                            placeholder="otomatis dari akun user yang dipilih"
                            class="w-full bg-gray-50 border border-gray-200 text-gray-500 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300">
                        <input type="hidden" name="unit_id" :value="selectedCustomer?.pemesanan?.unit_id ?? ''">
                    </div>
                </div>

                {{-- Data Lama KPR --}}
                <div x-show="caraBayar === 'kpr'" class="mt-6" x-transition>
                    <!-- INFORMASI UMUM (Cara Bayar, Total Tagihan, Sisa Tagihan) -->
                    <div class="mt-4 mb-4 border-b pb-3 border-gray-300 dark:border-gray-700" x-show="selectedCustomer">
                        <table class="text-sm w-auto">
                            <tbody>

                                <tr>
                                    <td class="text-gray-600 dark:text-gray-300 pr-4">Cara Bayar</td>
                                    <td class="pr-2 text-gray-600 dark:text-gray-300">:</td>
                                    <td class="font-medium text-gray-900 dark:text-white"
                                        x-text="selectedCustomer?.cara_bayar?.toUpperCase() ?? ''"></td>
                                </tr>

                                <tr>
                                    <td class="text-gray-600 dark:text-gray-300 pr-4">Total Tagihan</td>
                                    <td class="pr-2 text-gray-600 dark:text-gray-300">:</td>
                                    <td class="font-medium text-gray-900 dark:text-white"
                                        x-text="formatRupiah(selectedCustomer?.pemesanan?.total_tagihan)"></td>
                                </tr>

                                <tr>
                                    <td class="text-gray-600 dark:text-gray-300 pr-4">Sisa Tagihan</td>
                                    <td class="pr-2 text-gray-600 dark:text-gray-300">:</td>
                                    <td class="font-medium text-gray-900 dark:text-white"
                                        x-text="formatRupiah(selectedCustomer?.pemesanan?.sisa_tagihan)"></td>
                                </tr>

                            </tbody>
                        </table>

                    </div>


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
                            <!-- 1 -->
                            <tr>
                                <td class="border dark:border-gray-700 p-2 text-center text-gray-800 dark:text-gray-300">1
                                </td>
                                <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300">
                                    DP Rumah Induk (Termasuk SBUM dari Pemerintah)
                                </td>
                                <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300"
                                    x-text="formatRupiah(defaultData.dp_rumah_induk)"></td>
                            </tr>

                            <!-- 2 -->
                            <tr>
                                <td class="border dark:border-gray-700 p-2 text-center text-gray-800 dark:text-gray-300">2
                                </td>
                                <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300"
                                    x-text="'Kelebihan Tanah ' + (defaultData.luas_kelebihan ?? '')"></td>
                                <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300"
                                    x-text="formatRupiah(defaultData.nominal_kelebihan)"></td>
                            </tr>

                            <!-- 3 -->
                            <tr>
                                <td class="border dark:border-gray-700 p-2 text-center text-gray-800 dark:text-gray-300">3
                                </td>
                                <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300">Total DP</td>
                                <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300"
                                    x-text="formatRupiah(defaultData.total_dp)"></td>
                            </tr>

                            <!-- Garis Pembatas -->
                            <tr>
                                <td colspan="3"
                                    class="border dark:border-gray-700 p-2 text-center font-semibold bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                    - -
                                </td>
                            </tr>

                            <!-- DP -->
                            <tr>
                                <td class="border dark:border-gray-700 p-2 text-center text-gray-800 dark:text-gray-300">1
                                </td>
                                <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300">DP Dibayarkan
                                    Pembeli</td>
                                <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300"
                                    x-text="formatRupiah(defaultData.dp_dibayarkan_pembeli)"></td>
                            </tr>

                            <tr>
                                <td class="border dark:border-gray-700 p-2 text-center text-gray-800 dark:text-gray-300">2
                                </td>
                                <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300">SBUM Dari
                                    Pemerintah</td>
                                <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300"
                                    x-text="formatRupiah(defaultData.sbum_dari_pemerintah)"></td>
                            </tr>

                            <tr>
                                <td class="border dark:border-gray-700 p-2 text-center text-gray-800 dark:text-gray-300">3
                                </td>
                                <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300">KPR</td>
                                <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300"
                                    x-text="formatRupiah(defaultData.harga_kpr)"></td>
                            </tr>

                            <tr>
                                <td class="border dark:border-gray-700 p-2 text-center text-gray-800 dark:text-gray-300">4
                                </td>
                                <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300">Harga Total
                                </td>
                                <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300"
                                    x-text="formatRupiah(defaultData.harga_total)"></td>
                            </tr>
                        </tbody>
                    </table>

                    <br>

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
                            <template x-for="(item, index) in cicilan" :key="item.id">
                                <tr>
                                    <td class="border dark:border-gray-700 p-2 text-center text-gray-800 dark:text-gray-300"
                                        x-text="index + 1"></td>

                                    <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300">
                                        Pembayaran ke <span x-text="item.pembayaran_ke"></span>
                                    </td>

                                    <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300">
                                        <span x-text="formatTanggal(item.jatuh_tempo)"></span>
                                    </td>

                                    <td class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-300">
                                        <span x-text="formatRupiah(item.nominal)"></span>
                                    </td>
                                </tr>
                            </template>

                            <!-- Jika tidak ada cicilan -->
                            <tr x-show="cicilan.length === 0">
                                <td colspan="4"
                                    class="border dark:border-gray-700 p-2 text-center text-gray-600 dark:text-gray-400">
                                    Tidak ada cicilan
                                </td>
                            </tr>
                        </tbody>

                    </table>
                </div>

                {{-- Data Lama  Cash --}}
                <div x-show="caraBayar === 'cash'" class="mt-6" x-transition>
                    <!-- INFORMASI UMUM (Cara Bayar, Total Tagihan, Sisa Tagihan) -->
                    <div class="mt-4 mb-4 border-b pb-3
           border-gray-300 dark:border-gray-700"
                        x-show="selectedCustomer">
                        <table class="text-sm w-auto">
                            <tbody>

                                <tr>
                                    <td class="text-gray-600 dark:text-gray-300 pr-4">Cara Bayar</td>
                                    <td class="text-gray-700 dark:text-gray-400 pr-2">:</td>
                                    <td class="font-medium text-gray-900 dark:text-gray-100"
                                        x-text="selectedCustomer?.cara_bayar?.toUpperCase() ?? ''">
                                    </td>
                                </tr>

                                <tr>
                                    <td class="text-gray-600 dark:text-gray-300 pr-4">Total Tagihan</td>
                                    <td class="text-gray-700 dark:text-gray-400 pr-2">:</td>
                                    <td class="font-medium text-gray-900 dark:text-gray-100"
                                        x-text="formatRupiah(selectedCustomer?.pemesanan?.total_tagihan)">
                                    </td>
                                </tr>

                                <tr>
                                    <td class="text-gray-600 dark:text-gray-300 pr-4">Sisa Tagihan</td>
                                    <td class="text-gray-700 dark:text-gray-400 pr-2">:</td>
                                    <td class="font-medium text-gray-900 dark:text-gray-100"
                                        x-text="formatRupiah(selectedCustomer?.pemesanan?.sisa_tagihan)">
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
                                <td class="border dark:border-gray-700 p-2 text-gray-900 dark:text-gray-100"
                                    x-text="'Harga Rumah Type ' + (pemesanan?.type_unit ?? '')">
                                </td>
                                <td class="border dark:border-gray-700 p-2 text-gray-900 dark:text-gray-100"
                                    x-text="formatRupiah(defaultData.harga_rumah)">
                                </td>
                            </tr>

                            <!-- 2 -->
                            <tr>
                                <td class="border dark:border-gray-700 p-2 text-center text-gray-900 dark:text-gray-100">2
                                </td>
                                <td class="border dark:border-gray-700 p-2 text-gray-900 dark:text-gray-100"
                                    x-text="'Kelebihan Tanah ' + (defaultData.luas_kelebihan ?? '')">
                                </td>
                                <td class="border dark:border-gray-700 p-2 text-gray-900 dark:text-gray-100"
                                    x-text="formatRupiah(defaultData.nominal_kelebihan)">
                                </td>
                            </tr>

                            <!-- 3 -->
                            <tr>
                                <td class="border dark:border-gray-700 p-2 text-center text-gray-900 dark:text-gray-100">3
                                </td>
                                <td class="border dark:border-gray-700 p-2 text-gray-900 dark:text-gray-100">Harga Jadi
                                </td>
                                <td class="border dark:border-gray-700 p-2 text-gray-900 dark:text-gray-100"
                                    x-text="formatRupiah(defaultData.harga_jadi)">
                                </td>
                            </tr>
                        </tbody>
                    </table>


                    <br>

                    <!-- TABLE 2 — CICILAN / PEMBAYARAN CASH -->
                    <table class="w-full border border-gray-300 dark:border-gray-700 rounded-lg text-sm mt-4">
                        <thead class="bg-gray-100 dark:bg-gray-800">
                            <tr>
                                <th class="border dark:border-gray-700 p-2 w-12 text-gray-800 dark:text-gray-200">No</th>
                                <th class="border dark:border-gray-700 p-2 text-gray-800 dark:text-gray-200">Keterangan
                                </th>
                                <th class="border dark:border-gray-700 p-2 w-40 text-gray-800 dark:text-gray-200">Tanggal
                                    Estimasi</th>
                                <th class="border dark:border-gray-700 p-2 w-48 text-gray-800 dark:text-gray-200">Nominal
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <template x-for="(item, index) in cicilan" :key="item.id">
                                <tr>
                                    <td class="border dark:border-gray-700 p-2 text-center text-gray-900 dark:text-gray-100"
                                        x-text="index + 1"></td>

                                    <td class="border dark:border-gray-700 p-2 text-gray-900 dark:text-gray-100">
                                        Pembayaran ke <span x-text="item.pembayaran_ke"></span>
                                    </td>

                                    <td class="border dark:border-gray-700 p-2 text-gray-900 dark:text-gray-100">
                                        <span x-text="formatTanggal(item.jatuh_tempo)"></span>
                                    </td>

                                    <td class="border dark:border-gray-700 p-2 text-gray-900 dark:text-gray-100">
                                        <span x-text="formatRupiah(item.nominal)"></span>
                                    </td>
                                </tr>
                            </template>

                            <!-- Jika tidak ada cicilan -->
                            <tr x-show="cicilan.length === 0">
                                <td colspan="4"
                                    class="border dark:border-gray-700 p-2 text-center text-gray-500 dark:text-gray-400">
                                    Tidak ada cicilan
                                </td>
                            </tr>
                        </tbody>
                    </table>

                </div>

                <!-- FIXED STATIC VALUE  -->
                <input type="hidden" name="pemesanan_id" :value="selectedCustomer?.id ?? ''">
                <input type="hidden" name="jenis_adendum" value="cara_bayar">
                <input type="hidden" name="jenis_list_adendum_cara_bayar" value="cara_bayar">
            </div>

            {{-- Adendum Data Baru --}}
            <div x-data="{
                caraBayarBaru: '',

                // otomatis mengikuti cara bayar lama
                init() {
                    if (selectedCustomer?.cara_bayar) {
                        this.caraBayarBaru = selectedCustomer.cara_bayar;
                    }
                }
            }" x-init="init()"
                class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">

                <!-- Judul -->
                <div class="px-5 py-5 border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/30">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        Adendum
                        <span class="text-xs px-2 py-0.5 rounded-full bg-blue-600 text-white">
                            Form Perubahan
                        </span>
                    </h3>
                </div>

                <!-- 🔘 Pilihan Cara Bayar -->
                <div class="px-5 py-4 sm:px-6 sm:py-5">
                    <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                            Sistem Pembayaran
                        </h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Select Cara Bayar -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Pilih Cara Bayar <span class="text-red-500">*</span>
                            </label>
                            <select name="cara_bayar_baru" x-model="caraBayarBaru" required
                                class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                                    dark:bg-gray-700 dark:text-white
                                    @error('cara_bayar_baru') border-red-500 @else border-gray-300 @enderror">
                                <option value="">Pilih Cara Bayar</option>
                                <option value="cash">CASH</option>
                                <option value="kpr">KPR</option>
                            </select>
                            @error('cara_bayar_baru')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- 💵 FORM CASH -->
                <div x-show="caraBayarBaru === 'cash'" x-transition
                    class="px-5 py-4 sm:px-6 sm:py-5 border-t border-gray-100 dark:border-gray-800">
                    <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Sistem Pembayaran</h3>
                        <span
                            class="inline-flex items-center px-3 py-1 text-sm font-semibold text-yellow-800 bg-yellow-100 rounded-full border border-yellow-300 dark:bg-yellow-900/30 dark:text-yellow-300">
                            CASH
                        </span>
                    </div>

                    <div x-data="{
                        hargaRumah: '',
                        nominalKelebihan: 0,

                        get hargaJadi() {
                            const rumah = parseInt(this.hargaRumah.replace(/\D/g, '')) || 0;
                            return formatRupiah((rumah + this.nominalKelebihan).toString());
                        },

                        updateHargaRumah(e) {
                            let raw = e.target.value.replace(/\D/g, '');
                            this.hargaRumah = formatRupiah(raw);
                        },
                    }" x-init="// jalan ketika customer berubah
                    $watch('selectedCustomer', value => {
                        if (value?.default_data) {
                            hargaRumah = formatRupiah(value.default_data.harga_rumah ?? 0);
                            nominalKelebihan = parseInt(value.default_data.nominal_kelebihan ?? 0);
                        }
                    });

                    // jalan saat reload (customer sudah ter-select)
                    if (selectedCustomer?.default_data) {
                        hargaRumah = formatRupiah(selectedCustomer.default_data.harga_rumah ?? 0);
                        nominalKelebihan = parseInt(selectedCustomer.default_data.nominal_kelebihan ?? 0);
                    }" class="space-y-4">

                        <!-- Harga Rumah -->
                        <div>
                            <label for="no_hp" class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Harga Rumah<span class="text-red-500">*</span>
                            </label>
                            <input type="text" x-model="hargaRumah" @input="updateHargaRumah"
                                placeholder="Masukkan harga rumah"
                                :value="formatRupiah(selectedCustomer?.default_data?.harga_rumah ?? 0)"
                                class="w-full bg-white border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5
                   dark:bg-gray-700 dark:text-white dark:border-gray-600">
                            <!-- Input hidden dikirim ke server -->
                            <input type="hidden" name="cash_harga_rumah" :value="hargaRumah.replace(/\D/g, '')">
                        </div>

                        <!-- Kelebihan Tanah -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Kelebihan Tanah
                            </label>
                            <div class="grid grid-cols-2 gap-4">
                                <input type="text" readonly name="cash_luas_kelebihan"
                                    class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed
                       dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600"
                                    :value="selectedCustomer?.default_data?.luas_kelebihan ?? ''">

                                <input type="text" readonly
                                    class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5
                       dark:bg-gray-700 dark:text-white dark:border-gray-600"
                                    :value="formatRupiah(nominalKelebihan.toString())">

                                <!-- Input hidden dikirim ke server -->
                                <input type="hidden" name="cash_nominal_kelebihan" :value="nominalKelebihan">
                            </div>
                        </div>

                        <!-- Harga Jadi -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Harga Jadi
                            </label>
                            <input type="text" readonly
                                class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed
                   dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600"
                                :value="hargaJadi">

                            <!-- Hidden untuk dikirim ke server -->
                            <input type="hidden" name="cash_harga_jadi"
                                :value="(parseInt(hargaRumah.replace(/\D/g, '')) || 0) + nominalKelebihan">
                        </div>
                    </div>
                </div>

                <!-- 🏦 FORM KPR -->
                <div x-show="caraBayarBaru === 'kpr'" x-transition class="px-5 py-4 sm:px-6 sm:py-5">

                    <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                            Sistem Pembayaran
                        </h3>
                        <span
                            class="inline-flex items-center px-3 py-1 text-sm font-semibold text-blue-800 bg-blue-100 rounded-full border border-blue-300 dark:bg-blue-900/30 dark:text-blue-300">
                            KPR
                        </span>
                    </div>

                    <div x-data="{
                        sbumPemerintah: 4000000,
                        dpRumahInduk: '',
                        nominalKelebihan: 0,
                        hargaTotal: 0,

                        // Getter angka
                        get dpRumahIndukNumber() {
                            return parseInt(this.dpRumahInduk.replace(/\D/g, '')) || 0;
                        },
                        get totalDpNumber() {
                            return this.dpRumahIndukNumber + this.nominalKelebihan;
                        },
                        get dpPembeliNumber() {
                            const hasil = this.totalDpNumber - this.sbumPemerintah;
                            return hasil > 0 ? hasil : 0;
                        },
                        get hargaKprNumber() {
                            const total = this.hargaTotal - this.totalDpNumber;
                            return total > 0 ? total : 0;
                        },

                        // Format tampilan
                        get totalDp() {
                            return formatRupiah(this.totalDpNumber.toString());
                        },
                        get dpPembeli() {
                            return formatRupiah(this.dpPembeliNumber.toString());
                        },
                        get hargaKpr() {
                            return formatRupiah(this.hargaKprNumber.toString());
                        },
                        get hargaTotalFormatted() {
                            return formatRupiah(this.hargaTotal.toString());
                        },

                        // Event
                        updateDpRumahInduk(e) {
                            let raw = e.target.value.replace(/\D/g, '');
                            this.dpRumahInduk = formatRupiah(raw);
                        },
                    }" x-init="// WATCH perubahan customer
                    $watch('selectedCustomer', value => {
                        if (value?.default_data && value.cara_bayar === 'kpr') {

                            dpRumahInduk = formatRupiah((value.default_data.dp_rumah_induk || 0).toString());
                            nominalKelebihan = parseInt((value.default_data.nominal_kelebihan || 0).toString());
                            sbumPemerintah = parseInt((value.default_data.sbum_dari_pemerintah || 0).toString());
                            hargaTotal = parseInt((value.default_data.harga_total || 0).toString());
                        }
                    });

                    // INIT saat pertama kali tampil
                    if (selectedCustomer?.default_data && selectedCustomer.cara_bayar === 'kpr') {
                        dpRumahInduk = formatRupiah((selectedCustomer.default_data.dp_rumah_induk || 0).toString());
                        nominalKelebihan = parseInt((selectedCustomer.default_data.nominal_kelebihan || 0).toString());
                        sbumPemerintah = parseInt((selectedCustomer.default_data.sbum_dari_pemerintah || 0).toString());
                        hargaTotal = parseInt((selectedCustomer.default_data.harga_total || 0).toString());
                    }" class="space-y-5">

                        <!-- Info SBUM Pemerintah -->
                        <div
                            class="mt-3 flex items-center gap-3 px-3 py-2 rounded-lg border border-yellow-200 bg-yellow-50
        dark:bg-yellow-900/30 dark:border-yellow-700">
                            <div
                                class="flex items-center justify-center w-7 h-7 rounded-full bg-yellow-500 text-white font-bold text-sm">
                                💡
                            </div>
                            <div>
                                <p class="text-sm text-yellow-800 dark:text-yellow-300 font-medium">SBUM dari Pemerintah
                                </p>
                                <p class="text-xs text-yellow-600 dark:text-yellow-400">
                                    Tambahan harga: Rp <span x-text="formatRupiah(sbumPemerintah.toString())"></span>
                                </p>
                                <!-- SBUM dari Pemerintah (hidden input) -->
                                <input type="hidden" name="kpr_sbum_dari_pemerintah_baru" :value="sbumPemerintah">

                            </div>
                        </div>

                        <!-- DP Rumah Induk -->
                        <div>
                            <label class="block mt-4 mb-1 text-sm font-medium text-gray-900 dark:text-white">
                                DP Rumah Induk <span class="text-red-500">*</span>
                            </label>
                            <input type="text" x-model="dpRumahInduk" @input="updateDpRumahInduk"
                                class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5
                            dark:bg-gray-700 dark:text-white dark:border-gray-600"
                                placeholder="Masukkan DP Rumah Induk">
                            <input type="hidden" name="kpr_dp_rumah_induk_baru" :value="dpRumahIndukNumber">
                        </div>

                        <!-- Kelebihan Tanah -->
                        <div class="grid grid-cols-2 gap-4 items-end mt-4">
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                    Luas Kelebihan Tanah (m²)
                                </label>
                                <input type="text" readonly
                                    class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed
                    dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600"
                                    :value="selectedCustomer?.default_data?.luas_kelebihan ?? '-'">
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                    Nominal Kelebihan (Rp)
                                </label>
                                <input type="text" readonly :value="formatRupiah(nominalKelebihan.toString())"
                                    class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5
                    dark:bg-gray-700 dark:text-white dark:border-gray-600">
                                <input type="hidden" name="kpr_nominal_kelebihan_baru" :value="nominalKelebihan">
                            </div>
                        </div>

                        <!-- Total DP -->
                        <div class="mt-4">
                            <label class="block mb-1 text-sm font-semibold text-gray-900 dark:text-white">Total DP</label>
                            <input type="text" readonly :value="totalDp"
                                class="w-full bg-green-50 border border-green-300 text-green-700 text-sm font-semibold rounded-lg p-2.5
                dark:bg-green-900/30 dark:border-green-700 cursor-not-allowed">
                            <input type="hidden" name="kpr_total_dp_baru" :value="totalDpNumber">
                        </div>

                        <!-- DP Pembeli -->
                        <div>
                            <label class="block mb-1 text-sm font-semibold text-gray-900 dark:text-white">
                                DP Dibayarkan Pembeli
                            </label>
                            <input type="text" readonly :value="dpPembeli"
                                class="w-full bg-gray-100 border border-gray-300 text-gray-600 text-sm rounded-lg p-2.5
                dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700 cursor-not-allowed">
                            <input type="hidden" name="kpr_dp_dibayarkan_pembeli_baru" :value="dpPembeliNumber">
                        </div>

                        <!-- Harga Total & Harga KPR -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block mb-1 text-sm font-semibold text-gray-900 dark:text-white">
                                    Harga Total Rumah
                                </label>
                                <input type="text" readonly :value="hargaTotalFormatted"
                                    class="w-full bg-indigo-50 border border-indigo-300 text-indigo-700 text-sm font-semibold rounded-lg p-2.5
                    dark:bg-indigo-900/30 dark:border-indigo-700 cursor-not-allowed">
                                <input type="hidden" name="kpr_harga_total_baru" :value="hargaTotal">
                            </div>

                            <div>
                                <label class="block mb-1 text-sm font-semibold text-gray-900 dark:text-white">
                                    Nilai KPR
                                </label>
                                <input type="text" readonly :value="hargaKpr"
                                    class="w-full bg-blue-50 border border-blue-300 text-blue-700 text-sm font-semibold rounded-lg p-2.5
                    dark:bg-blue-900/30 dark:border-blue-700 cursor-not-allowed">
                                <input type="hidden" name="kpr_harga_kpr_baru" :value="hargaKprNumber">
                            </div>
                        </div>

                    </div>

                </div>

                <!-- Cicilan  -->
                <div class="px-5 py-4">
                    <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                            Cicilan
                        </h3>
                    </div>

                    <!-- DAFTAR ANGSURAN -->
                    <div x-data="adendumForm">
                        <!-- LIST CICILAN -->
                        <template x-for="(item, index) in cicilan" :key="index + '-' + selectedCustomerId">
                            <div
                                class="flex flex-col md:flex-row md:items-center gap-3 pb-4 mb-0 border-b border-gray-200
        dark:border-gray-700 transition-all duration-150 hover:bg-gray-50
        dark:hover:bg-gray-800/40 rounded-lg p-3">

                                <!-- Pembayaran Ke -->
                                <div class="w-full md:w-1/4 relative">
                                    <input type="hidden" name="pembayaran_ke[]" :value="index + 1">
                                    <input type="hidden" name="status_bayar[]" :value="item.status_bayar">

                                    <input type="text"
                                        :value="index === 0 ?
                                            `Pembayaran ke - ${index + 1} / DP` :
                                            (index === cicilan.length - 1 ?
                                                `Pembayaran ke - ${index + 1} / Pelunasan` :
                                                `Pembayaran ke - ${index + 1}`)"
                                        readonly
                                        class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5
                dark:bg-gray-800 dark:text-white dark:border-gray-600 cursor-not-allowed select-none">

                                    <!-- Badge Status -->
                                    <span
                                        class="absolute right-2 top-1/2 -translate-y-1/2 text-xs font-semibold px-2 py-1 rounded-full"
                                        :class="{
                                            'bg-green-100 text-green-700': item.status_bayar === 'lunas',
                                            'bg-yellow-100 text-yellow-700': item.status_bayar === 'pending',
                                            'bg-red-100 text-red-700': item.status_bayar === 'telat',
                                        }">
                                        <span
                                            x-text="item.status_bayar.charAt(0).toUpperCase() + item.status_bayar.slice(1)"></span>
                                    </span>
                                </div>

                                <!-- Tanggal Pembayaran -->
                                <div class="relative w-full md:w-1/3" x-data="{
                                    tampil: item.jatuh_tempo ?
                                        (() => {
                                            const d = new Date(item.jatuh_tempo);
                                            return ('0' + d.getDate()).slice(-2) + '-' +
                                                ('0' + (d.getMonth() + 1)).slice(-2) + '-' +
                                                d.getFullYear();
                                        })() : '',

                                    simpan: item.jatuh_tempo || ''
                                }">
                                    <!-- Icon Kalender -->
                                    <div class="absolute inset-y-0 left-0 flex items-center ps-3.5 pointer-events-none">
                                        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                            xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                                        </svg>
                                    </div>

                                    <!-- Input Tampil -->
                                    <input type="text" x-model="tampil" x-init="flatpickr($el, {
                                        dateFormat: 'd-m-Y',
                                        defaultDate: simpan,
                                        onChange: (dates, dateStr) => {
                                            tampil = dateStr;

                                            // Format simpan (Y-m-d)
                                            const d = dates[0];
                                            simpan = d.getFullYear() + '-' +
                                                ('0' + (d.getMonth() + 1)).slice(-2) + '-' +
                                                ('0' + d.getDate()).slice(-2);
                                        },
                                        clickOpens: item.status_bayar !== 'lunas',
                                        allowInput: item.status_bayar !== 'lunas',
                                    })"
                                        placeholder="Pilih tanggal" :readonly="item.status_bayar === 'lunas'"
                                        :class="item.status_bayar === 'lunas' ?
                                            'bg-gray-100 border-gray-300 cursor-not-allowed text-gray-500' :
                                            'bg-gray-50 border-gray-300'"
                                        class="border text-gray-900 text-sm rounded-lg
               focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5
               dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400
               dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">

                                    <!-- Input hidden format Y-m-d -->
                                    <input type="hidden" name="tanggal_angsuran[]" x-model="simpan">
                                </div>

                                <!-- Nominal -->
                                <div class="w-full md:w-1/3">
                                    <input type="text" x-model="item.nominalFormatted" x-init="item.nominalFormatted = formatRupiah(item.nominal || 0)"
                                        @input="
                    if (item.status_bayar !== 'lunas') {
                        item.nominal = parseInt($el.value.replace(/\D/g,'')) || 0;
                        item.nominalFormatted = formatRupiah(item.nominal);
                    }
                "
                                        :disabled="item.status_bayar === 'lunas'"
                                        :class="{
                                            'bg-gray-100 cursor-not-allowed text-gray-500': item
                                                .status_bayar === 'lunas'
                                        }"
                                        placeholder="Masukkan nominal"
                                        class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5
                dark:bg-gray-700 dark:text-white dark:border-gray-600">

                                    <input type="hidden" name="nominal_angsuran[]" :value="parseInt(item.nominal) || 0">
                                </div>

                                <!-- Tombol Tambah & Hapus -->
                                <div class="flex items-center space-x-2">

                                    <!-- Tombol + hanya tampil di baris pertama -->
                                    <button type="button" @click="tambahCicilan()" x-show="index === 0"
                                        class="px-3 py-2 bg-green-500 text-white rounded-lg text-sm">
                                        +
                                    </button>

                                    <!-- Tombol - muncul jika:
                                                                            1) bukan baris pertama
                                                                            2) status bayar bukan lunas
                                                                        -->
                                    <button type="button" @click="hapusCicilan(index)"
                                        x-show="index > 0 && item.status_bayar !== 'lunas'"
                                        class="px-3 py-2 bg-red-500 text-white rounded-lg text-sm">
                                        -
                                    </button>

                                </div>




                            </div>
                        </template>

                    </div>
                </div>
            </div>


            <!-- Tombol Aksi -->
            <div class="flex justify-end gap-2">
                {{-- <button type="button" onclick="history.back()"
                    class="px-8 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300
                dark:text-white dark:bg-gray-700 dark:hover:bg-gray-600">
                    Kembali
                </button> --}}
                <button type="submit"
                    class="px-8 py-2.5 text-sm font-medium text-white rounded-lg bg-blue-600 hover:bg-blue-700
                focus:outline-none focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800">
                    Simpan
                </button>
            </div>
        </form>
    </div>

    </div>
    <!-- ===== Main Content End ===== -->

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('adendumForm', () => ({

                customers: @json($customersData),

                selectedCustomerId: '',
                selectedCustomer: null,

                caraBayar: '',
                defaultData: {},
                pemesanan: {},
                cicilan: [],

                formatRupiah(num) {
                    if (!num) return "Rp 0";
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }).format(num);
                },

                formatTanggal(tgl) {
                    if (!tgl) return "-";
                    return new Date(tgl).toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'long',
                        year: 'numeric'
                    });
                },

                init() {
                    this.initSelect2();
                },

                setCustomer(id) {
                    this.selectedCustomerId = id;
                    this.selectedCustomer = this.customers.find(c => c.id == id) || null;

                    if (this.selectedCustomer) {
                        this.caraBayar = this.selectedCustomer.cara_bayar;
                        this.pemesanan = this.selectedCustomer.pemesanan ?? {};
                        this.defaultData = this.selectedCustomer.default_data ?? {};
                        this.cicilan = this.selectedCustomer.cicilan ?? [];
                    } else {
                        this.caraBayar = '';
                        this.pemesanan = {};
                        this.defaultData = {};
                        this.cicilan = [];
                    }
                },

                initSelect2() {
                    const self = this;
                    const select = $('#selectUser');

                    select.select2({
                        theme: 'bootstrap4',
                        width: '100%',
                        placeholder: "Cari & pilih akun customer...",
                        allowClear: true
                    });

                    select.on('change', function() {
                        self.setCustomer(this.value);
                    });
                },

                tambahCicilan() {
                    this.cicilan.push({
                        jatuh_tempo: '',
                        nominal: 0,
                        nominalFormatted: '',
                        status_bayar: 'pending',
                    });
                },

                hapusCicilan(index) {
                    if (this.cicilan.length > 1) {
                        this.cicilan.splice(index, 1);
                    }
                },


            }));
        });
    </script>
@endsection
