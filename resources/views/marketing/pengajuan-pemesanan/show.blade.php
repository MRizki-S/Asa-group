@extends('layouts.app')

@section('pageActive', 'PengajuanPemesanan')

@section('content')

    {{--
    <link rel="stylesheet"
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
        <div x-data="{ openInfo: false }" class="mt-6 mb-6 rounded-xl border border-gray-200 dark:border-gray-700
                    bg-white dark:bg-gray-800/50 shadow-sm text-gray-800 dark:text-gray-100 overflow-hidden">

            <!-- Header -->
            <button @click="openInfo = !openInfo" class="w-full flex justify-between items-center px-5 py-3 bg-gray-50 dark:bg-gray-800/70
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
                <div class="space-y-2 text-sm leading-snug">
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

                {{-- 📱 TAMPILAN KHUSUS MOBILE (HP): Gabung jadi 1 kolom berlabel "Unit" --}}
                <div class="block md:hidden mt-4">
                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Unit</label>
                    <input type="text" readonly
                        value="{{ ($pengajuan->perumahaan->nama_perumahaan ?? '-') . ' - ' . ($pengajuan->unit->nama_unit ?? '-') }}"
                        class="w-full bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 font-medium">
                </div>

                {{-- 💻 TAMPILAN DESKTOP (MD KE ATAS): Tetap 3 kolom (Perumahan, Tahap, Unit) --}}
                <div class="hidden md:grid md:grid-cols-3 gap-6 mt-4">
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

            {{-- 🤝 Agent Referral (Jika ada) --}}
            @if ($pengajuan->source === 'agent')
                <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-6 border-b pb-1">
                        Informasi Agen Referral
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Nama Agen
                            </label>
                            <input type="text" readonly value="{{ $pengajuan->agent->nama_agent ?? '-' }}"
                                class="w-full bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg p-2.5 cursor-not-allowed">
                        </div>

                        @if ($pengajuan->feeAgent->isNotEmpty())
                            @foreach ($pengajuan->feeAgent as $fee)
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Fee Agen
                                    </label>
                                    <input type="text" readonly
                                        value="{{ $fee->masterAgentFee->judul_fee ?? 'Fee' }} — (Rp {{ number_format($fee->nominal_snapshot, 0, ',', '.') }})"
                                        class="w-full bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg p-2.5 cursor-not-allowed">
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            @endif

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
                    }" class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">

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
                            <select disabled name="cara_bayar" x-model="caraBayar" class="w-full bg-gray-100 border text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed
                                dark:bg-gray-700 dark:text-gray-400 border-gray-300">
                                <option value="">Pilih Cara Bayar</option>
                                <option value="cash">CASH</option>
                                <option value="kpr">KPR</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- 💵 FORM CASH -->
                @if ($pengajuan->cara_bayar === 'cash')
                    <div class="px-5 py-4 sm:px-6 sm:py-5 border-t border-gray-100 dark:border-gray-800">

                        <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">
                            <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Sistem Pembayaran</h3>
                            <span
                                class="inline-flex items-center px-3 py-1 text-sm font-semibold text-yellow-800 bg-yellow-100 rounded-full border border-yellow-300 dark:bg-yellow-900/30 dark:text-yellow-300">
                                CASH
                            </span>
                        </div>

                        <div class="space-y-4">
                            <!-- Harga Rumah & Kelebihan Tanah -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Harga Pokok Rumah
                                    </label>
                                    <input type="text" readonly
                                        value="Rp {{ number_format($pengajuan->cash?->harga_rumah ?? 0, 0, ',', '.') }}"
                                        class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-800 dark:text-white dark:border-gray-600 font-medium">
                                </div>

                                <div>
                                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Kelebihan Tanah ({{ $pengajuan->cash?->luas_kelebihan ?? 0 }} m²)
                                    </label>
                                    <input type="text" readonly
                                        class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-800 dark:text-white dark:border-gray-600 font-medium"
                                        value="Rp {{ number_format($pengajuan->cash?->nominal_kelebihan ?? 0, 0, ',', '.') }}">
                                </div>
                            </div>

                            <!-- Biaya Tambahan (Spesifikasi Cash) -->
                            @if ($pengajuan->spesifikasiCash && $pengajuan->spesifikasiCash->count() > 0)
                                <div
                                    class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 bg-gray-50/50 dark:bg-gray-800/30 space-y-2">
                                    <h4 class="text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Biaya Tambahan</h4>
                                    <div class="space-y-2">
                                        @foreach ($pengajuan->spesifikasiCash as $item)
                                            <div
                                                class="flex items-center justify-between text-sm py-1 border-b border-gray-100 dark:border-gray-700/50">
                                                <span class="text-gray-800 dark:text-gray-200">{{ $item->keterangan }}</span>
                                                <span class="font-semibold text-gray-900 dark:text-white">Rp
                                                    {{ number_format($item->nominal, 0, ',', '.') }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Harga Jadi Cash -->
                            <div
                                class="p-4 rounded-xl bg-blue-50/80 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 flex items-center justify-between">
                                <div>
                                    <span
                                        class="text-xs uppercase font-semibold text-blue-700 dark:text-blue-300 tracking-wider">Total
                                        / Harga Jadi Cash</span>
                                    <p class="text-xs text-blue-600 dark:text-blue-400">Harga Rumah + Kelebihan Tanah + Biaya
                                        Tambahan</p>
                                </div>
                                <span class="text-xl font-bold text-blue-800 dark:text-blue-200">
                                    Rp
                                    {{ number_format($pengajuan->cash?->harga_jadi ?? (($pengajuan->cash?->harga_rumah ?? 0) + ($pengajuan->cash?->nominal_kelebihan ?? 0) + ($pengajuan->cash?->total_tambahan ?? 0)), 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- 🏦 FORM KPR -->
                @if ($pengajuan->cara_bayar === 'kpr')
                    <div class="px-5 py-4 sm:px-6 sm:py-5 border-t border-gray-100 dark:border-gray-800">
                        <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">
                            <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Rincian Pembayaran KPR</h3>
                            <span
                                class="inline-flex items-center px-3 py-1 text-sm font-semibold text-blue-800 bg-blue-100 rounded-full border border-blue-300 dark:bg-blue-900/30 dark:text-blue-300">
                                KPR
                            </span>
                        </div>

                        <div class="space-y-5">
                            <!-- Info SBUM -->
                            <div
                                class="flex items-center gap-3 px-3 py-2.5 rounded-lg border border-yellow-200 bg-yellow-50 dark:bg-yellow-900/30 dark:border-yellow-700">
                                <span class="text-lg">💡</span>
                                <div>
                                    <p class="text-xs font-semibold text-yellow-800 dark:text-yellow-300">SBUM dari Pemerintah:
                                        Rp {{ number_format($pengajuan->kpr?->sbum_dari_pemerintah ?: 4000000, 0, ',', '.') }}
                                    </p>
                                    <p class="text-[11px] text-yellow-600 dark:text-yellow-400">Diperhitungkan untuk memotong
                                        nilai KPR Bank.</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Harga Rumah Asli -->
                                <div>
                                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Harga Rumah Asli
                                    </label>
                                    <input type="text" readonly
                                        value="Rp {{ number_format($pengajuan->kpr?->harga_total ?? 0, 0, ',', '.') }}"
                                        class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-800 dark:text-white dark:border-gray-600 font-medium">
                                </div>

                                <!-- DP Rumah -->
                                <div>
                                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        DP Rumah
                                    </label>
                                    <input type="text" readonly
                                        value="Rp {{ number_format($pengajuan->kpr?->dp_rumah_induk ?? $pengajuan->kpr?->dp_dibayarkan_pembeli ?? 0, 0, ',', '.') }}"
                                        class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-800 dark:text-white dark:border-gray-600 font-semibold text-blue-700 dark:text-blue-300">
                                </div>

                                <!-- Nilai KPR (Bank) -->
                                <div>
                                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Nilai KPR (Bank)
                                    </label>
                                    @php
                                        $sbumVal = $pengajuan->kpr?->sbum_dari_pemerintah ?: 4000000;
                                        $dpVal = $pengajuan->kpr?->dp_rumah_induk ?? $pengajuan->kpr?->dp_dibayarkan_pembeli ?? 0;
                                        $plafonKpr = $pengajuan->kpr?->harga_kpr ?: max(0, ($pengajuan->kpr?->harga_total ?? 0) - $sbumVal - $dpVal);
                                    @endphp
                                    <input type="text" readonly value="Rp {{ number_format($plafonKpr, 0, ',', '.') }}"
                                        class="w-full bg-blue-50 border border-blue-300 text-blue-800 text-sm font-bold rounded-lg p-2.5 cursor-not-allowed dark:bg-blue-900/30 dark:border-blue-700 dark:text-blue-200">
                                </div>
                            </div>

                            <!-- Biaya Administrasi / Tambahan KPR (Jika Ada) -->
                            @if ($pengajuan->biayaAdministrasi && $pengajuan->biayaAdministrasi->count() > 0)
                                <div
                                    class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 bg-gray-50/50 dark:bg-gray-800/30 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Daftar Biaya
                                            Administrasi / Tambahan</h4>
                                        <span class="text-xs font-bold text-amber-700 dark:text-amber-300">
                                            Total: Rp
                                            {{ number_format($pengajuan->kpr?->total_biaya_administrasi ?? $pengajuan->biayaAdministrasi->sum('nominal'), 0, ',', '.') }}
                                        </span>
                                    </div>
                                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                                        <table class="w-full text-left text-xs text-gray-700 dark:text-gray-200">
                                            <thead class="bg-gray-100 dark:bg-gray-700 uppercase text-gray-600 dark:text-gray-300">
                                                <tr>
                                                    <th class="px-3 py-2">Keterangan</th>
                                                    <th class="px-3 py-2 text-right">Nominal (Rp)</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                                @foreach ($pengajuan->biayaAdministrasi as $bItem)
                                                    <tr>
                                                        <td class="px-3 py-2 font-medium">{{ $bItem->keterangan }}</td>
                                                        <td class="px-3 py-2 text-right font-semibold">Rp
                                                            {{ number_format($bItem->nominal, 0, ',', '.') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Fasilitas Termasuk dalam Biaya Administrasi & Notaris -->
                                    @if ($pengajuan->promoAdmin && $pengajuan->promoAdmin->count() > 0)
                                        <div class="pt-3 border-t border-gray-200 dark:border-gray-700 space-y-1.5">
                                            <span class="text-[11px] font-semibold uppercase text-gray-500 dark:text-gray-400">
                                                Biaya Administrasi & Notaris ini sudah termasuk:
                                            </span>
                                            <div class="flex flex-wrap gap-1.5">
                                                @foreach ($pengajuan->promoAdmin as $pAdm)
                                                    <span
                                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800">
                                                        ✓ {{ $pAdm->nama_promo }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Note / Catatan Biaya Administrasi -->
                                    @if (!empty($pengajuan->kpr?->catatan_biaya_administrasi))
                                        <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                                            <p class="text-xs italic text-gray-600 dark:text-gray-300">
                                                <strong class="font-semibold not-italic">Note:</strong>
                                                {{ $pengajuan->kpr?->catatan_biaya_administrasi }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- 💸 Bonus Cash (muncul kalau cash dipilih) -->
            @if ($pengajuan->cara_bayar === 'cash' && $pengajuan->bonusCash->isNotEmpty())
                <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
                    <div class="px-5 py-4 sm:px-6 sm:py-5 space-y-3 border-t border-gray-100 dark:border-gray-800">
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-2">Bonus Cash</h3>

                        @foreach ($pengajuan->bonusCash as $bonus)
                            <div class="flex gap-2 items-center">
                                <input type="text" readonly value="{{ $bonus->nama_bonus ?? '-' }}" class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5
                                            dark:bg-gray-800 dark:text-white dark:border-gray-600 cursor-not-allowed">
                            </div>
                        @endforeach
                    </div>
                </div>
            @elseif($pengajuan->cara_bayar === 'kpr' && $pengajuan->bonusKpr->isNotEmpty())
                <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
                    <div class="px-5 py-4 sm:px-6 sm:py-5 space-y-3 border-t border-gray-100 dark:border-gray-800">
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-2">Bonus KPR</h3>

                        @foreach ($pengajuan->bonusKpr as $bonus)
                            <div class="flex gap-2 items-center">
                                <input type="text" readonly value="{{ $bonus->nama_bonus ?? '-' }}" class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5
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
                            <input type="text" readonly value="{{ $pengajuan->caraBayar->jumlah_cicilan ?? '-' }}" class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5
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

                        @if ($pengajuan->cara_bayar === 'kpr')
                            <!-- Cicilan DP Rumah -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                                    1. Angsuran DP Rumah
                                </label>
                                @php $cicilanDpList = $pengajuan->cicilanDp ?? $pengajuan->cicilan->where('tipe_cicilan', 'dp'); @endphp
                                @if ($cicilanDpList && $cicilanDpList->count() > 0)
                                    @foreach ($cicilanDpList as $cicilan)
                                        <div
                                            class="flex flex-col md:flex-row md:items-center gap-3 pb-3 mb-2 border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800/40 rounded-lg p-2.5 transition-all">
                                            <div class="w-full md:w-1/3">
                                                <input type="text" readonly
                                                    value="{{ $cicilan->keterangan ?? ('Pembayaran ke - ' . $cicilan->pembayaran_ke . ' / DP Rumah') }}"
                                                    class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg p-2 dark:bg-gray-700 dark:text-white dark:border-gray-600 font-medium cursor-not-allowed">
                                            </div>
                                            <div class="w-full md:w-1/3">
                                                <input type="text" readonly
                                                    value="{{ $cicilan->tanggal_jatuh_tempo ? \Carbon\Carbon::parse($cicilan->tanggal_jatuh_tempo)->format('d M Y') : '-' }}"
                                                    class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg p-2 dark:bg-gray-700 dark:text-white dark:border-gray-600 cursor-not-allowed">
                                            </div>
                                            <div class="w-full md:w-1/3">
                                                <input type="text" readonly
                                                    value="Rp {{ number_format($cicilan->nominal, 0, ',', '.') }}"
                                                    class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-xs text-right font-semibold rounded-lg p-2 dark:bg-gray-700 dark:text-white dark:border-gray-600 cursor-not-allowed">
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-xs text-gray-500 dark:text-gray-400 italic">Tidak ada data cicilan DP.</div>
                                @endif
                            </div>

                            <!-- Cicilan Biaya Tambahan (Jika Ada) -->
                            @php $cicilanAdminList = $pengajuan->cicilanAdministrasi ?? $pengajuan->cicilan->where('tipe_cicilan', 'administrasi'); @endphp
                            @if ($cicilanAdminList && $cicilanAdminList->count() > 0)
                                <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                                        2. Angsuran Biaya Tambahan
                                    </label>
                                    @foreach ($cicilanAdminList as $cicilan)
                                        <div
                                            class="flex flex-col md:flex-row md:items-center gap-3 pb-3 mb-2 border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800/40 rounded-lg p-2.5 transition-all">
                                            <div class="w-full md:w-1/3">
                                                <input type="text" readonly
                                                    value="{{ $cicilan->keterangan ?? ('Angsuran ke - ' . $cicilan->pembayaran_ke . ' Biaya Tambahan') }}"
                                                    class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg p-2 dark:bg-gray-700 dark:text-white dark:border-gray-600 font-medium cursor-not-allowed">
                                            </div>
                                            <div class="w-full md:w-1/3">
                                                <input type="text" readonly
                                                    value="{{ $cicilan->tanggal_jatuh_tempo ? \Carbon\Carbon::parse($cicilan->tanggal_jatuh_tempo)->format('d M Y') : '-' }}"
                                                    class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg p-2 dark:bg-gray-700 dark:text-white dark:border-gray-600 cursor-not-allowed">
                                            </div>
                                            <div class="w-full md:w-1/3">
                                                <input type="text" readonly
                                                    value="Rp {{ number_format($cicilan->nominal, 0, ',', '.') }}"
                                                    class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-xs text-right font-semibold rounded-lg p-2 dark:bg-gray-700 dark:text-white dark:border-gray-600 cursor-not-allowed">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @else
                            <!-- Daftar Angsuran Cash -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Daftar Angsuran CASH
                                </label>
                                @if ($pengajuan->cicilan->count() > 0)
                                    @foreach ($pengajuan->cicilan as $index => $cicilan)
                                        <div
                                            class="flex flex-col md:flex-row md:items-center gap-3 pb-3 mb-2 border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800/40 rounded-lg p-2.5 transition-all">
                                            <div class="w-full md:w-1/3">
                                                <input type="text" readonly
                                                    value="{{ $cicilan->keterangan ?? ('Pembayaran ke - ' . $cicilan->pembayaran_ke) }}"
                                                    class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg p-2 dark:bg-gray-700 dark:text-white dark:border-gray-600 font-medium cursor-not-allowed">
                                            </div>
                                            <div class="w-full md:w-1/3">
                                                <input type="text" readonly
                                                    value="{{ $cicilan->tanggal_jatuh_tempo ? \Carbon\Carbon::parse($cicilan->tanggal_jatuh_tempo)->format('d M Y') : '-' }}"
                                                    class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg p-2 dark:bg-gray-700 dark:text-white dark:border-gray-600 cursor-not-allowed">
                                            </div>
                                            <div class="w-full md:w-1/3">
                                                <input type="text" readonly
                                                    value="Rp {{ number_format($cicilan->nominal, 0, ',', '.') }}"
                                                    class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-xs text-right font-semibold rounded-lg p-2 dark:bg-gray-700 dark:text-white dark:border-gray-600 cursor-not-allowed">
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-sm text-gray-500 dark:text-gray-400 italic">
                                        Tidak ada data cicilan.
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @php
                $user = auth()->user();
                $namaPerumahaan = $pengajuan->perumahaan->nama_perumahaan;

                $bolehAction = false;

                if ($namaPerumahaan === 'Asa Dreamland') {
                    // Khusus ADL → Proyek Manager saja
                    $bolehAction = $user->hasRole(['PROYEK MANAJER (ADL)', 'superadmin', 'STAF SOLUSI KPR & PENJUALAN (ADL)', 'SPV PEMBIAYAAN KPR & BANK']);
                } else {
                    // Selain ADL → pakai permission (Staff KPR)
                    $bolehAction = $user->can('marketing.pengajuan-pemesanan.action');
                }
            @endphp

            @if ($bolehAction)
                <div class="flex justify-end gap-3">
                    <!-- Tombol Tolak -->
                    <form action="{{ route('marketing.pengajuanPemesanan.reject', $pengajuan->id) }}" method="POST"
                        class="tolak-form">
                        @csrf
                        @method('PATCH')
                        <button type="button" class="tolak-btn px-4 py-2 text-sm font-medium text-gray-800 bg-gray-300 rounded-lg shadow-md
                                hover:bg-gray-400 hover:shadow-lg transition duration-200 ease-in-out">
                            Tolak
                        </button>
                    </form>

                    <!-- Tombol Approve -->
                    <form action="{{ route('marketing.pengajuanPemesanan.approve', $pengajuan->id) }}" method="POST"
                        class="approve-form">
                        @csrf
                        @method('PATCH')
                        <button type="button" class="approve-btn px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-md
                                hover:bg-blue-700 hover:shadow-lg transition duration-200 ease-in-out">
                            Acc / Approve
                        </button>
                    </form>
                </div>
            @endif

        </div>
    </div>


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