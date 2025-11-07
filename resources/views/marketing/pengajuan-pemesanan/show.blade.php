@extends('layouts.app')

@section('pageActive', 'PengajuanPemesanan')

@section('content')

    {{-- <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css"> --}}
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb -->
        <div x-data="{ pageName: 'PengajuanPemesanan' }">
            @include('partials.breadcrumb')
        </div>

        <!-- Alert Error Validasi -->
        @if ($errors->any())
            <div class="flex p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400"
                role="alert">
                <svg class="shrink-0 inline w-4 h-4 me-3 mt-[2px]" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                    viewBox="0 0 20 20">
                    <path
                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                </svg>
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

        <!-- Informasi Promo & Ketentuan -->
        <div x-data="{ openInfo: false, openPromo: false }"
            class="mt-6 mb-6 rounded-xl border border-gray-200 dark:border-gray-700
            bg-white dark:bg-gray-800/50 shadow-sm text-gray-800 dark:text-gray-100 overflow-hidden">

            <!-- Header -->
            <button @click="openInfo = !openInfo"
                class="w-full flex justify-between items-center px-5 py-3 bg-gray-50 dark:bg-gray-800/70
                   hover:bg-gray-100 dark:hover:bg-gray-700 transition font-semibold text-sm">
                <span>📄 Informasi Terkait PPJB</span>
                <svg :class="{ 'rotate-180': openInfo }" xmlns="http://www.w3.org/2000/svg"
                    class="w-5 h-5 transform transition-transform duration-200" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div x-show="openInfo" x-transition x-cloak class="px-5 py-5">

                <!-- Ketentuan Keterlambatan & Pembatalan -->
                <div class="space-y-2 text-sm leading-snug mb-4">
                    @if ($keterlambatan)
                        <div class="flex items-start gap-2">
                            <span class="text-gray-500 text-lg">⚠️</span>
                            <p>
                                <span class="font-semibold">Keterlambatan:</span>
                                Pembayaran melewati tanggal jatuh tempo akan dikenakan denda sebesar
                                <span class="font-bold text-red-600 bg-red-50 dark:bg-red-900/30 px-1 rounded">
                                    {{ rtrim(rtrim(number_format($keterlambatan->persentase_denda, 2, ',', '.'), '0'), ',') }}%
                                </span>
                                per bulan.
                            </p>
                        </div>
                    @endif

                    @if ($pembatalan)
                        <div class="flex items-start gap-2">
                            <span class="text-gray-500 text-lg">❗</span>
                            <p>
                                <span class="font-semibold">Pembatalan:</span>
                                Potongan sebesar
                                <span class="font-bold text-yellow-600 bg-yellow-50 dark:bg-yellow-900/30 px-1 rounded">
                                    {{ rtrim(rtrim(number_format($pembatalan->persentase_potongan, 2, ',', '.'), '0'), ',') }}%
                                </span>
                                dari total dana.
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Promo berdasarkan cara bayar -->
                @php
                    $caraBayar = $pengajuan->cara_bayar;
                @endphp

                @if ($pengajuan->promo->count())
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                        <button @click="openPromo = !openPromo"
                            class="w-full flex justify-between items-center px-4 py-2.5
                               bg-gray-50 dark:bg-gray-700/40 text-sm font-medium
                               hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                            <span><b>Promo — {{ ucfirst($caraBayar) }}</b></span>
                            <svg :class="{ 'rotate-180': openPromo }" xmlns="http://www.w3.org/2000/svg"
                                class="w-4 h-4 transform transition-transform duration-200" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="openPromo" x-transition x-cloak
                            class="p-4 bg-white dark:bg-gray-800 text-sm leading-snug">
                            <ul class="list-disc ml-5 space-y-1">
                                @foreach ($pengajuan->promo as $item)
                                    <li>{{ $item->nama_promo }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            </div>
        </div>



        <div>

            {{-- 🧾 Akun User & Booking Unit --}}
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6 p-6">

                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-6 border-b pb-1">
                    Akun User & Booking Unit
                </h3>

                {{-- Akun user dan tanggal pemesanan --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                    {{-- Akun Customer --}}
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Akun Customer
                        </label>
                        <input type="text" readonly
                            value="{{ $pengajuan->customer->username ?? '-' }} — {{ $pengajuan->customer->no_hp ?? '-' }}"
                            class="w-full bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg p-2.5 cursor-not-allowed">
                    </div>

                    {{-- Tanggal Pemesanan --}}
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Tanggal Pemesanan
                        </label>
                        <input type="text" readonly value="{{ $pengajuan->tanggal_pemesanan->format('d M Y') }}"
                            class="w-full bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg p-2.5 cursor-not-allowed">
                    </div>
                </div>

                {{-- Blok perumahaan, tahap, unit --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                    {{-- Perumahaan --}}
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Perumahaan</label>
                        <input type="text" readonly value="{{ $pengajuan->perumahaan->nama_perumahaan ?? '-' }}"
                            class="w-full bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg p-2.5 cursor-not-allowed">
                    </div>

                    {{-- Tahap --}}
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Tahap</label>
                        <input type="text" readonly value="{{ $pengajuan->tahap->nama_tahap ?? '-' }}"
                            class="w-full bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg p-2.5 cursor-not-allowed">
                    </div>

                    {{-- Unit --}}
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Unit</label>
                        <input type="text" readonly value="{{ $pengajuan->unit->nama_unit ?? '-' }}"
                            class="w-full bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg p-2.5 cursor-not-allowed">
                    </div>
                </div>
            </div>

            {{-- 🧍‍♂️ Data Diri User --}}
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
                <div class="px-5 py-4 sm:px-6 sm:py-5">
                    <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">
                        <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                            Data Diri User
                        </h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        {{-- Nama User --}}
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Nama User
                            </label>
                            <input type="text" readonly value="{{ $pengajuan->dataDiri->nama_pribadi ?? '-' }}"
                                class="form-control w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-700 dark:text-white dark:border-gray-600">
                        </div>

                        {{-- Nomor HP --}}
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Nomor HP
                            </label>
                            <input type="text" readonly value="{{ $pengajuan->dataDiri->no_hp ?? '-' }}"
                                class="form-control w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-700 dark:text-white dark:border-gray-600">
                        </div>

                        <!-- Nomor KTP -->
                        <div>
                            <label for="no_hp" class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                No KTP <span class="text-red-500">*</span>
                            </label>
                            <input type="text" readonly value="{{ $pengajuan->dataDiri->no_ktp ?? '-' }}"
                                class="form-control w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-700 dark:text-white dark:border-gray-600">
                        </div>

                        <!-- Pekerjaan -->
                        <div>
                            <label for="no_hp" class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Pekerjaan <span class="text-red-500">*</span>
                            </label>
                            <input type="text" readonly value="{{ $pengajuan->dataDiri->pekerjaan ?? '-' }}"
                                class="form-control w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-700 dark:text-white dark:border-gray-600">
                        </div>

                        {{-- Provinsi --}}
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Provinsi
                            </label>
                            <input type="text" readonly value="{{ $pengajuan->dataDiri->provinsi_nama ?? '-' }}"
                                class="form-control w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-700 dark:text-white dark:border-gray-600">
                        </div>

                        {{-- Kota --}}
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Kota / Kabupaten
                            </label>
                            <input type="text" readonly value="{{ $pengajuan->dataDiri->kota_nama ?? '-' }}"
                                class="form-control w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-700 dark:text-white dark:border-gray-600">
                        </div>

                        {{-- Kecamatan --}}
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Kecamatan
                            </label>
                            <input type="text" readonly value="{{ $pengajuan->dataDiri->kecamatan_nama ?? '-' }}"
                                class="form-control w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-700 dark:text-white dark:border-gray-600">
                        </div>

                        {{-- Desa --}}
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Desa / Kelurahan
                            </label>
                            <input type="text" readonly value="{{ $pengajuan->dataDiri->desa_nama ?? '-' }}"
                                class="form-control w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-700 dark:text-white dark:border-gray-600">
                        </div>

                        {{-- RT --}}
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">RT</label>
                            <input type="text" readonly value="{{ $pengajuan->dataDiri->rt ?? '-' }}"
                                class="form-control w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-700 dark:text-white dark:border-gray-600">
                        </div>

                        {{-- RW --}}
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">RW</label>
                            <input type="text" readonly value="{{ $pengajuan->dataDiri->rw ?? '-' }}"
                                class="form-control w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-700 dark:text-white dark:border-gray-600">
                        </div>

                        {{-- Jalan / Dusun --}}
                        <div class="md:col-span-2">
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Jalan / Dusun
                            </label>
                            <textarea readonly rows="2"
                                class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-700 dark:text-white dark:border-gray-600">{{ $pengajuan->dataDiri->alamat_detail ?? '-' }}</textarea>
                        </div>

                    </div>
                </div>
            </div>

            {{-- sistem pembayaran --}}
            <div x-data="{
                caraBayar: '{{ $pengajuan->cara_bayar }}',
            }"
                class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">

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
                                Pilih Cara Bayar
                            </label>
                            <select disabled name="cara_bayar" x-model="caraBayar"
                                class="w-full bg-gray-100 border text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed
                        dark:bg-gray-700 dark:text-gray-400 border-gray-300">
                                <option value="">Pilih Cara Bayar</option>
                                <option value="cash">CASH</option>
                                <option value="kpr">KPR</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- 💵 FORM CASH -->
                <div x-show="caraBayar === 'cash'" x-transition
                    class="px-5 py-4 sm:px-6 sm:py-5 border-t border-gray-100 dark:border-gray-800">

                    <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Sistem Pembayaran</h3>
                        <span
                            class="inline-flex items-center px-3 py-1 text-sm font-semibold text-yellow-800 bg-yellow-100 rounded-full border border-yellow-300 dark:bg-yellow-900/30 dark:text-yellow-300">
                            CASH
                        </span>
                    </div>

                    <div x-data="{
                        hargaRumah: '{{ number_format($pengajuan->cash->harga_rumah ?? 0, 0, ',', '.') }}',
                        nominalKelebihan: {{ $pengajuan->cash->nominal_kelebihan ?? 0 }},
                        get hargaJadi() {
                            const rumah = parseInt(this.hargaRumah.replace(/\D/g, '')) || 0;
                            return formatRupiah((rumah + this.nominalKelebihan).toString());
                        },
                    }" class="space-y-4">

                        <!-- Harga Rumah -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Harga Rumah
                            </label>
                            <input type="text" x-model="hargaRumah" readonly
                                class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed
                        dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600">
                        </div>

                        <!-- Kelebihan Tanah -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Kelebihan Tanah
                            </label>
                            <div class="grid grid-cols-2 gap-4">
                                <input type="text" readonly
                                    class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed
                            dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600"
                                    value="{{ $pengajuan->cash->luas_kelebihan ?? '-' }}">

                                <input type="text" readonly
                                    class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed
                            dark:bg-gray-700 dark:text-gray-400 dark:border-gray-600"
                                    :value="formatRupiah(nominalKelebihan.toString())">
                            </div>
                        </div>

                        <!-- Harga Jadi -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Harga Jadi
                            </label>
                            <input type="text" readonly :value="hargaJadi"
                                class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed
                        dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600">
                        </div>
                    </div>
                </div>

                <!-- 🏦 FORM KPR -->
                <div x-show="caraBayar === 'kpr'" x-transition class="px-5 py-4 sm:px-6 sm:py-5">
                    <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Sistem Pembayaran</h3>
                        <span
                            class="inline-flex items-center px-3 py-1 text-sm font-semibold text-blue-800 bg-blue-100 rounded-full border border-blue-300 dark:bg-blue-900/30 dark:text-blue-300">
                            KPR
                        </span>
                    </div>

                    <div x-data="{
                        sbumPemerintah: 4000000,
                        dpRumahInduk: '{{ number_format($pengajuan->kpr->dp_rumah_induk ?? 0, 0, ',', '.') }}',
                        nominalKelebihan: {{ $pengajuan->kpr->nominal_kelebihan ?? 0 }},
                        hargaTotal: {{ $pengajuan->kpr->harga_total ?? 0 }},
                        get dpRumahIndukNumber() {
                            return parseInt(this.dpRumahInduk.replace(/\D/g, '')) || 0;
                        },
                        get totalDpNumber() {
                            return this.dpRumahIndukNumber + (this.nominalKelebihan || 0);
                        },
                        get dpPembeliNumber() {
                            const hasil = this.totalDpNumber - this.sbumPemerintah;
                            return hasil > 0 ? hasil : 0;
                        },
                        get hargaKprNumber() {
                            const total = this.hargaTotal - this.totalDpNumber;
                            return total > 0 ? total : 0;
                        },
                        get totalDp() { return formatRupiah(this.totalDpNumber.toString()); },
                        get dpPembeli() { return formatRupiah(this.dpPembeliNumber.toString()); },
                        get hargaKpr() { return formatRupiah(this.hargaKprNumber.toString()); },
                        get hargaTotalFormatted() { return formatRupiah(this.hargaTotal.toString()); },
                    }" class="space-y-5">

                        <!-- Info SBUM -->
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
                            </div>
                        </div>

                        <!-- DP Rumah Induk -->
                        <div>
                            <label class="block mt-4 mb-1 text-sm font-medium text-gray-900 dark:text-white">
                                DP Rumah Induk
                            </label>
                            <input type="text" x-model="dpRumahInduk" readonly
                                class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed
                        dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600">
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
                                    value="{{ $pengajuan->kpr->luas_kelebihan ?? '-' }}">
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                    Nominal Kelebihan (Rp)
                                </label>
                                <input type="text" readonly :value="formatRupiah(nominalKelebihan.toString())"
                                    class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed
                            dark:bg-gray-700 dark:text-gray-400 dark:border-gray-600">
                            </div>
                        </div>

                        <!-- Total DP -->
                        <div>
                            <label class="block mb-1 text-sm font-semibold text-gray-900 dark:text-white">
                                Total DP
                            </label>
                            <input type="text" readonly :value="totalDp"
                                class="w-full bg-green-50 border border-green-300 text-green-700 text-sm font-semibold rounded-lg p-2.5 cursor-not-allowed
                        dark:bg-green-900/30 dark:border-green-700">
                        </div>

                        <!-- DP Dibayarkan Pembeli -->
                        <div>
                            <label class="block mb-1 text-sm font-semibold text-gray-900 dark:text-white">
                                DP Dibayarkan Pembeli
                            </label>
                            <input type="text" readonly :value="dpPembeli"
                                class="w-full bg-gray-100 border border-gray-300 text-gray-600 text-sm rounded-lg p-2.5 cursor-not-allowed
                        dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700">
                        </div>

                        <!-- Harga Total & Nilai KPR -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block mb-1 text-sm font-semibold text-gray-900 dark:text-white">
                                    Harga Total Rumah
                                </label>
                                <input type="text" readonly :value="hargaTotalFormatted"
                                    class="w-full bg-indigo-50 border border-indigo-300 text-indigo-700 text-sm font-semibold rounded-lg p-2.5 cursor-not-allowed
                            dark:bg-indigo-900/30 dark:border-indigo-700">
                            </div>
                            <div>
                                <label class="block mb-1 text-sm font-semibold text-gray-900 dark:text-white">
                                    Nilai KPR
                                </label>
                                <input type="text" readonly :value="hargaKpr"
                                    class="w-full bg-blue-50 border border-blue-300 text-blue-700 text-sm font-semibold rounded-lg p-2.5 cursor-not-allowed
                            dark:bg-blue-900/30 dark:border-blue-700">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 💸 Bonus Cash (muncul kalau cash dipilih) -->
            @if ($pengajuan->cara_bayar === 'cash' && $pengajuan->bonusCash->isNotEmpty())
            <div
    class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
                <div class="px-5 py-4 sm:px-6 sm:py-5 space-y-3 border-t border-gray-100 dark:border-gray-800">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-2">Bonus Cash</h3>

                    @foreach ($pengajuan->bonusCash as $bonus)
                        <div class="flex gap-2 items-center">
                            <input type="text" readonly value="{{ $bonus->nama_bonus ?? '-' }}"
                                class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5
                    dark:bg-gray-800 dark:text-white dark:border-gray-600 cursor-not-allowed">
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- pemesanan unit cicilan --}}
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
                <div class="px-5 py-4 sm:px-6 sm:py-5">
                    <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                            Cara Pembayaran
                        </h3>
                    </div>

                    <!-- Bagian Isi -->
                    <div class="space-y-5">
                        <!-- Berapa Kali Angsur -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Berapa Kali Angsur
                            </label>
                            <input type="text" readonly value="{{ $pengajuan->caraBayar->jumlah_cicilan ?? '-' }}"
                                class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5
                    dark:bg-gray-800 dark:text-white dark:border-gray-600 cursor-not-allowed">
                        </div>

                        <!-- Minimal DP -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Minimal DP
                            </label>
                            <input type="text" readonly
                                value="{{ isset($pengajuan->caraBayar->minimal_dp) ? 'Rp ' . number_format($pengajuan->caraBayar->minimal_dp, 0, ',', '.') : '-' }}"
                                class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5
                    dark:bg-gray-800 dark:text-white dark:border-gray-600 cursor-not-allowed">
                        </div>

                        <!-- Daftar Angsuran -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Angsuran
                            </label>

                            @if ($pengajuan->cicilan->count() > 0)
                                @foreach ($pengajuan->cicilan as $index => $cicilan)
                                    <div
                                        class="flex flex-col md:flex-row md:items-center gap-3 pb-4 mb-2 border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800/40 rounded-lg p-3 transition-all">

                                        <!-- Pembayaran Ke -->
                                        <div class="w-full md:w-1/4">
                                            <input type="text" readonly
                                                value="{{ 'Pembayaran ke - ' . $cicilan->pembayaran_ke }}"
                                                class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5
                                    dark:bg-gray-800 dark:text-white dark:border-gray-600 cursor-not-allowed">
                                        </div>

                                        <!-- Tanggal Jatuh Tempo -->
                                        <div class="w-full md:w-1/3">
                                            <input type="text" readonly
                                                value="{{ $cicilan->tanggal_jatuh_tempo ? $cicilan->tanggal_jatuh_tempo->format('d M Y') : '-' }}"
                                                class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5
                                    dark:bg-gray-800 dark:text-white dark:border-gray-600 cursor-not-allowed">
                                        </div>

                                        <!-- Nominal -->
                                        <div class="w-full md:w-1/3">
                                            <input type="text" readonly
                                                value="Rp {{ number_format($cicilan->nominal, 0, ',', '.') }}"
                                                class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5
                                    dark:bg-gray-800 dark:text-white dark:border-gray-600 cursor-not-allowed">
                                        </div>

                                    </div>
                                @endforeach
                            @else
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    Tidak ada data cicilan.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- button aksi tolak & approve --}}
            @unlessrole('Sales')
                <div class="flex justify-end gap-3">
                    <!-- Tombol Tolak -->
                    <form action="{{ route('marketing.pengajuanPemesanan.reject', $pengajuan->id) }}" method="POST"
                        class="tolak-form">
                        @csrf
                        @method('PATCH')
                        <button type="button"
                            class="tolak-btn px-4 py-2 text-sm font-medium text-gray-800 bg-gray-300 rounded-lg shadow-md
                    hover:bg-gray-400 hover:shadow-lg transition duration-200 ease-in-out">
                            Tolak
                        </button>
                    </form>

                    <!-- Tombol Approve -->
                    <form action="{{ route('marketing.pengajuanPemesanan.approve', $pengajuan->id) }}" method="POST"
                        class="approve-form">
                        @csrf
                        @method('PATCH')
                        <button type="button"
                            class="approve-btn px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-md
                    hover:bg-blue-700 hover:shadow-lg transition duration-200 ease-in-out">
                            Acc / Approve
                        </button>
                    </form>
                </div>
            @endunlessrole



        </div>
    </div>
    </div>


    <script>
        document.addEventListener('click', function(e) {
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
