@extends('layouts.app')

@section('pageActive', $pageActive)

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-data="pengajuanUpahForm()" x-init="init()">

        <!-- Breadcrumb -->
        <div x-data="{ pageName: '{{ $pageActive }}' }">
            @include('partials.breadcrumb')
        </div>

        <!-- Step Indicator -->
        <div class="mb-6">
            <ol class="flex items-center w-full">
                <li class="flex w-full items-center" :class="currentStep >= 1 ? 'text-blue-600' : 'text-gray-500'">
                    <span
                        class="flex items-center justify-center w-10 h-10 rounded-full shrink-0 text-sm font-bold transition-all duration-300"
                        :class="currentStep >= 1 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600'">1</span>
                    <span class="ms-3 text-sm font-medium hidden sm:inline"
                        :class="currentStep >= 1 ? 'text-blue-600' : 'text-gray-500'">Informasi Pengajuan</span>
                    <div class="w-full h-0.5 mx-4" :class="currentStep >= 2 ? 'bg-blue-600' : 'bg-gray-200'"></div>
                </li>
                <li class="flex items-center" :class="currentStep >= 2 ? 'text-blue-600' : 'text-gray-500'">
                    <span
                        class="flex items-center justify-center w-10 h-10 rounded-full shrink-0 text-sm font-bold transition-all duration-300"
                        :class="currentStep >= 2 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600'">2</span>
                    <span class="ms-3 text-sm font-medium hidden sm:inline whitespace-nowrap"
                        :class="currentStep >= 2 ? 'text-blue-600' : 'text-gray-500'">Detail Aktivitas Tukang</span>
                </li>
            </ol>
        </div>

        {{-- ==================== STEP 1 ==================== --}}
        <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">

            <!-- Card: Informasi Pengajuan -->
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-white/[0.03] mb-5">
                <div class="px-5 py-4 sm:px-6 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white">Informasi Pengajuan
                        {{ $isAbm ? 'ABM' : 'Mangoon' }}
                    </h3>
                </div>
                <div class="px-5 py-5 sm:px-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Nomor Pengajuan -->
                    <div>
                        <label class="block mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">Nomor
                            Pengajuan</label>
                        <input type="text" :value="nomorUpah" readonly
                            class="w-full bg-gray-100 border border-gray-200 text-gray-500 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400">
                        <p class="mt-1 text-xs text-gray-400">Otomatis dibuat sistem</p>
                    </div>
                    <!-- Status -->
                    <div>
                        <label class="block mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                        <input type="text" value="Draft" readonly
                            class="w-full bg-gray-100 border border-gray-200 text-gray-500 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400">
                    </div>
                    <!-- Tanggal Mulai -->
                    <div>
                        <label class="block mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Mulai <span
                                class="text-red-500">*</span></label>
                        <div class="relative"
                            x-data="{ tampil: '{{ $pengajuan->tanggal_mulai->format('d-m-Y') }}', simpan: '{{ $pengajuan->tanggal_mulai->format('Y-m-d') }}' }">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path
                                        d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                                </svg>
                            </div>
                            <input type="text" x-init="flatpickr($el, {
                                                dateFormat: 'd-m-Y',
                                                defaultDate: '{{ $pengajuan->tanggal_mulai->format('d-m-Y') }}',
                                                onChange: (selectedDates, dateStr, instance) => {
                                                    tampil = dateStr;
                                                    simpan = instance.formatDate(selectedDates[0], 'Y-m-d');
                                                    tanggalMulai = simpan;
                                                    validatePeriode();
                                                }
                                            })"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 dark:bg-gray-700 dark:text-white">
                            <input type="hidden" x-model="tanggalMulai" :value="simpan">
                        </div>
                    </div>
                    <!-- Tanggal Selesai -->
                    <div>
                        <label class="block mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Selesai
                            <span class="text-red-500">*</span></label>
                        <div class="relative"
                            x-data="{ tampil: '{{ $pengajuan->tanggal_selesai->format('d-m-Y') }}', simpan: '{{ $pengajuan->tanggal_selesai->format('Y-m-d') }}' }">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path
                                        d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                                </svg>
                            </div>
                            <input type="text" x-init="flatpickr($el, {
                                                dateFormat: 'd-m-Y',
                                                defaultDate: '{{ $pengajuan->tanggal_selesai->format('d-m-Y') }}',
                                                onChange: (selectedDates, dateStr, instance) => {
                                                    tampil = dateStr;
                                                    simpan = instance.formatDate(selectedDates[0], 'Y-m-d');
                                                    tanggalSelesai = simpan;
                                                    validatePeriode();
                                                }
                                            })"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 dark:bg-gray-700 dark:text-white">
                            <input type="hidden" x-model="tanggalSelesai" :value="simpan">
                        </div>
                        <p x-show="periodeError" class="mt-1 text-xs text-red-500" x-text="periodeError"></p>
                    </div>
                </div>
            </div>

            <!-- Card: Daftar Tukang -->
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-white/[0.03] mb-5">
                <div
                    class="px-5 py-4 sm:px-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white">Daftar Tukang</h3>
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        Total Dipilih: <span class="font-semibold text-blue-600"
                            x-text="selectedTukang.length + ' Orang'"></span>
                    </span>
                </div>
                <div class="px-5 py-4 sm:px-6">
                    <!-- Search -->
                    <div class="mb-4">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" x-model="searchTukang" placeholder="Cari nama tukang..."
                                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    <!-- Tabel Tukang -->
                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <table class="w-full text-sm text-left text-gray-700 dark:text-gray-300">
                            <thead class="bg-gray-100 dark:bg-gray-700 text-xs uppercase">
                                <tr>
                                    <th class="px-4 py-3 w-12">
                                        <input type="checkbox" @change="toggleAllTukang($event)"
                                            :checked="filteredTukang.length > 0 && filteredTukang.every(t => selectedTukang.includes(t.id))"
                                            class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                    </th>
                                    <th class="px-4 py-3">Kode</th>
                                    <th class="px-4 py-3">Nama Tukang</th>
                                    <th class="px-4 py-3">Gaji Default</th>
                                    <th class="px-4 py-3">Jam Default</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <template x-for="tukang in filteredTukang" :key="tukang.id">
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors select-none"
                                        @click="toggleSelectTukang(tukang.id)"
                                        :class="selectedTukang.includes(tukang.id) ? 'bg-blue-50 dark:bg-blue-900/20' : ''">
                                        <td class="px-4 py-3">
                                            <input type="checkbox" :checked="selectedTukang.includes(tukang.id)"
                                                @click.stop="toggleSelectTukang(tukang.id)"
                                                class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                        </td>
                                        <td class="px-4 py-3 font-mono text-xs text-gray-500" x-text="tukang.kode"></td>
                                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white"
                                            x-text="tukang.nama_tukang"></td>
                                        <td class="px-4 py-3" x-text="'Rp ' + formatRupiah(tukang.gaji_harian_default)">
                                        </td>
                                        <td class="px-4 py-3" x-text="tukang.jam_kerja_default + ' Jam'"></td>
                                    </tr>
                                </template>
                                <tr x-show="filteredTukang.length === 0">
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">Tidak ada
                                        data tukang</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Error -->
                    <p x-show="step1Error" class="mt-3 text-sm text-red-500" x-text="step1Error"></p>
                </div>
            </div>

            <!-- Action Buttons Step 1 -->
            <div class="flex justify-between items-center">
                <a href="{{ $isAbm ? route('gudang.pengajuanUpahHarianTukang.index') : route('gudang.pengajuanUpahHarianTukang.indexMangoon') }}"
                    class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 dark:text-white dark:bg-gray-700 dark:hover:bg-gray-600 transition-colors">
                    Batal
                </a>
                <button type="button" @click="goToStep2"
                    class="px-8 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 transition-colors flex items-center gap-2">
                    Selanjutnya
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- ==================== STEP 2 ==================== --}}
        <div x-show="currentStep === 2" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">

            <!-- Info Periode -->
            <div
                class="rounded-2xl border border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-900/20 p-4 mb-5 flex flex-wrap gap-4 items-center">
                <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <div>
                    <span class="text-sm text-blue-700 dark:text-blue-300 font-medium">Periode :</span>
                    <span class="text-sm text-blue-800 dark:text-blue-200 font-semibold ml-2"
                        x-text="formatDateDisplay(tanggalMulai) + ' s/d ' + formatDateDisplay(tanggalSelesai)"></span>
                </div>
                <div class="ml-auto text-sm text-blue-700 dark:text-blue-300">
                    Nomor: <span class="font-semibold" x-text="nomorUpah"></span>
                </div>
            </div>

            <!-- Card: Detail Aktivitas -->
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-white/[0.03] mb-5">
                <div class="px-5 py-4 sm:px-6 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white">Detail Aktivitas Tukang</h3>
                </div>
                <div class="px-5 py-4 sm:px-6">
                    <!-- Accordion Tukang -->
                    <div class="space-y-3">
                        <template x-for="(tukangData, tIndex) in tukangDetails" :key="tukangData.id">
                            <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                                <!-- Accordion Header -->
                                <button type="button" @click="toggleAccordion(tIndex)"
                                    class="w-full flex items-center justify-between px-5 py-3.5 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <span class="text-sm font-semibold text-gray-800 dark:text-white"
                                            x-text="tukangData.nama_tukang"></span>
                                        <span class="text-xs text-gray-400 font-mono" x-text="tukangData.kode"></span>
                                        <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full"
                                            :class="getAlokasiWarning(tukangData) ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'">
                                            <span
                                                x-text="getAlokasiWarning(tukangData) ? '⚠ Ada alokasi belum lengkap' : '✓ Siap'"></span>
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <div class="text-right text-xs text-gray-500 hidden sm:block">
                                            <span x-text="getHariMasuk(tukangData) + ' hari masuk'"></span> ·
                                            <span x-text="'Rp ' + formatRupiah(getTotalUpahTukang(tukangData))"></span>
                                        </div>
                                        <svg class="w-4 h-4 text-gray-500 transition-transform duration-200"
                                            :class="openAccordion === tIndex ? 'rotate-180' : ''" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </button>

                                <!-- Accordion Body -->
                                <div x-show="openAccordion === tIndex" x-collapse>
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-sm text-left">
                                            <thead
                                                class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                                                <tr>
                                                    <th
                                                        class="px-4 py-2.5 text-xs font-semibold text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                                        Tanggal</th>
                                                    <th
                                                        class="px-4 py-2.5 text-xs font-semibold text-gray-600 dark:text-gray-400 text-center">
                                                        Hadir</th>
                                                    <th
                                                        class="px-4 py-2.5 text-xs font-semibold text-gray-600 dark:text-gray-400">
                                                        Nominal Harian</th>
                                                    <th
                                                        class="px-4 py-2.5 text-xs font-semibold text-gray-600 dark:text-gray-400 text-center">
                                                        Jam Normal</th>
                                                    <th
                                                        class="px-4 py-2.5 text-xs font-semibold text-gray-600 dark:text-gray-400 text-center">
                                                        Aktivitas Normal</th>
                                                    <th
                                                        class="px-4 py-2.5 text-xs font-semibold text-gray-600 dark:text-gray-400 text-center">
                                                        Lembur</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                                <template x-for="(detail, dIndex) in tukangData.details"
                                                    :key="detail.tanggal">
                                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50">
                                                        <!-- Tanggal -->
                                                        <td class="px-4 py-2.5 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white"
                                                            x-text="formatTanggalShort(detail.tanggal)"></td>

                                                        <!-- Hadir Switch -->
                                                        <td class="px-4 py-2.5 text-center">
                                                            <label class="relative inline-flex items-center cursor-pointer">
                                                                <input type="checkbox" x-model="detail.hadir"
                                                                    @change="onHadirChange(tIndex, dIndex)"
                                                                    class="sr-only peer">
                                                                <div
                                                                    class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600">
                                                                </div>
                                                            </label>
                                                        </td>

                                                        <!-- Nominal Harian -->
                                                        <td class="px-4 py-2.5">
                                                            <div class="flex items-center gap-1 min-w-[130px]">
                                                                <span class="text-xs text-gray-400">Rp</span>
                                                                <input type="text" :value="formatRupiah(detail.nominal)"
                                                                    @input="onNominalInput($event, tIndex, dIndex)"
                                                                    :disabled="!detail.hadir"
                                                                    class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded p-1.5 disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                                            </div>
                                                        </td>

                                                        <!-- Jam Normal -->
                                                        <td class="px-4 py-2.5 text-center">
                                                            <input type="number" x-model.number="detail.jam_normal"
                                                                :disabled="!detail.hadir" min="0" max="24"
                                                                class="w-16 bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded p-1.5 text-center disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                                        </td>

                                                        <!-- Aktivitas Normal -->
                                                        <td class="px-4 py-2.5 text-center">
                                                            <button type="button"
                                                                @click="openModalAktivitas(tIndex, dIndex)"
                                                                :disabled="!detail.hadir"
                                                                class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-md transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                                                                :class="detail.alokasi_normal.length > 0 ?
                                                                                (detail.alokasi_normal.reduce((s,a)=>s+a.jam,0) === detail.jam_normal ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-red-100 text-red-700 hover:bg-red-200') :
                                                                                'bg-blue-100 text-blue-700 hover:bg-blue-200'">
                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                                </svg>
                                                                <span
                                                                    x-text="detail.alokasi_normal.length > 0 ? detail.alokasi_normal.reduce((s,a)=>s+a.jam,0) + '/' + detail.jam_normal + ' Jam' : 'Detail Aktivitas'"></span>
                                                            </button>
                                                        </td>

                                                        <!-- Lembur -->
                                                        <td class="px-4 py-2.5 text-center">
                                                            <button type="button" @click="openModalLembur(tIndex, dIndex)"
                                                                :disabled="!detail.hadir"
                                                                class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-md transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                                                                :class="detail.alokasi_lembur.length > 0 ? 'bg-orange-100 text-orange-700 hover:bg-orange-200' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300'">
                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                                <span
                                                                    x-text="detail.alokasi_lembur.length > 0 ? detail.alokasi_lembur.reduce((s,a)=>s+a.jam,0) + ' Jam Lembur' : 'Tambah Lembur'"></span>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Ringkasan Tukang -->
                                    <div
                                        class="px-5 py-3 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                                        <div class="flex flex-wrap gap-6 text-sm">
                                            <div>
                                                <span class="text-gray-500">Hari Masuk:</span>
                                                <span class="font-semibold text-gray-900 dark:text-white ml-1"
                                                    x-text="getHariMasuk(tukangData) + ' Hari'"></span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Upah Normal:</span>
                                                <span class="font-semibold text-gray-900 dark:text-white ml-1"
                                                    x-text="'Rp ' + formatRupiah(getUpahNormal(tukangData))"></span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Total Lembur:</span>
                                                <span class="font-semibold text-orange-600 ml-1"
                                                    x-text="'Rp ' + formatRupiah(getTotalLembur(tukangData))"></span>
                                            </div>
                                            <div class="ml-auto">
                                                <span class="text-gray-500">Total:</span>
                                                <span class="font-bold text-blue-600 text-base ml-1"
                                                    x-text="'Rp ' + formatRupiah(getTotalUpahTukang(tukangData))"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Card: Ringkasan Pengajuan -->
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-white/[0.03] mb-5">
                <div
                    class="px-5 py-4 sm:px-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white">
                        Ringkasan Pengajuan
                    </h3>

                    {{-- Export Excel Button --}}
                    <button type="button" id="btn-export-excel" onclick="handleExportExcel()"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-green-700 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 dark:bg-green-900/30 dark:text-green-300 dark:border-green-700 dark:hover:bg-green-900/50 transition-colors focus:outline-none focus:ring-2 focus:ring-green-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                            stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        Export Excel
                    </button>
                </div>
                <div class="px-5 py-5 sm:px-6">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4 text-center">
                            <p class="text-xs text-gray-500 mb-1">Jumlah Tukang</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-white"
                                x-text="tukangDetails.length + ' Orang'"></p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4 text-center">
                            <p class="text-xs text-gray-500 mb-1">Total Hari Masuk</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-white"
                                x-text="getTotalHariMasukAll() + ' Hari'"></p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4 text-center">
                            <p class="text-xs text-gray-500 mb-1">Upah Normal</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-white"
                                x-text="'Rp ' + formatRupiah(getTotalUpahNormalAll())"></p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4 text-center">
                            <p class="text-xs text-gray-500 mb-1">Total Lembur</p>
                            <p class="text-lg font-bold text-orange-600" x-text="'Rp ' + formatRupiah(getTotalLemburAll())">
                            </p>
                        </div>
                        <div
                            class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 text-center border border-blue-200 dark:border-blue-700">
                            <p class="text-xs text-blue-600 mb-1">Grand Total</p>
                            <p class="text-lg font-bold text-blue-700 dark:text-blue-300"
                                x-text="'Rp ' + formatRupiah(getGrandTotal())"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons Step 2 -->
            <div class="flex justify-between items-center">
                <button type="button" @click="currentStep = 1"
                    class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 dark:text-white dark:bg-gray-700 dark:hover:bg-gray-600 transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Kembali
                </button>
                <div class="flex flex-col items-end">
                    <div class="flex gap-3">
                        <button type="button" @click="submitForm('draft')"
                            class="px-6 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:ring-4 focus:ring-gray-200 transition-colors">
                            Simpan Draft
                        </button>
                        <button type="button" @click="submitForm('diajukan')" :disabled="!canSubmitDiajukan()"
                            :class="canSubmitDiajukan() ? 'bg-blue-600 hover:bg-blue-700 text-white cursor-pointer' : 'bg-gray-300 text-gray-500 dark:bg-gray-700 dark:text-gray-500 cursor-not-allowed'"
                            class="px-8 py-2.5 text-sm font-semibold rounded-lg focus:ring-4 focus:ring-blue-300 transition-colors">
                            Ajukan
                        </button>
                    </div>
                    <p x-show="!canSubmitDiajukan()" class="text-xs text-red-500 mt-2 text-right">
                        * Alokasi jam aktivitas harus lengkap & sesuai jam kerja normal untuk dapat mengajukan.
                    </p>
                </div>
            </div>
        </div>


        @include('gudang.upah-harian-tukang.daftar-pengajuanUpah.modal.modal-detailAktivitas')

        @include('gudang.upah-harian-tukang.daftar-pengajuanUpah.modal.modal-detailLembur')

    </div>

    {{-- ============================================================
    ALPINE.JS COMPONENT
    ============================================================ --}}
    <script>
        const ALL_TUKANG = @json($masterTukang);
        const PEMBANGUNAN_UNITS = @json($pembangunanUnits);
        const PEMBANGUNAN_KAWASANS = @json($pembangunanKawasans);
        const PEMBANGUNAN_PROYEKS = @json($pembangunanProyeks ?? []);
        const NOMOR_UPAH = @json($nomorUpah);
        const EXISTING_DATA = @json($existingTukangDetails ?? []);
        const PENGAJUAN_ID = @json($pengajuan->id);
        const UPDATE_DRAFT_ROUTE = @json($updateDraftRoute);
        const SUBMIT_DRAFT_ROUTE = @json($submitDraftRoute);

        // ========================
        // Export Excel Handler
        // ========================
        function handleExportExcel() {
            window.location.href = "{{ route('keuangan.daftarUpahHarian.exportExcel') }}?id={{ $pengajuan->id }}";
        }

        function pengajuanUpahForm() {
            return {
                // --- Step ---
                currentStep: 1,

                // --- Step 1 ---
                nomorUpah: NOMOR_UPAH,
                tanggalMulai: '{{ $pengajuan->tanggal_mulai->format('Y-m-d') }}',
                tanggalSelesai: '{{ $pengajuan->tanggal_selesai->format('Y-m-d') }}',
                searchTukang: '',
                selectedTukang: [],
                periodeError: '',
                step1Error: '',
                isAbm: @json($isAbm),

                // --- Step 2 ---
                tukangDetails: [],
                openAccordion: 0,

                // --- Modal Aktivitas ---
                modalAktivitasOpen: false,
                modalAktivitasTIndex: null,
                modalAktivitasDIndex: null,
                modalAktivitasTukang: null,
                modalAktivitasDetail: null,
                newAktivitas: { referensi_jenis: '', referensi_id: '', jam: 1 },

                // --- Modal Lembur ---
                modalLemburOpen: false,
                modalLemburTIndex: null,
                modalLemburDIndex: null,
                modalLemburTukang: null,
                modalLemburDetail: null,
                newLembur: { referensi_jenis: '', referensi_id: '', jam: 1, tarif: 0 },

                // --- Reference Data ---
                pembangunanUnits: PEMBANGUNAN_UNITS,
                pembangunanKawasans: PEMBANGUNAN_KAWASANS,
                pembangunanProyeks: PEMBANGUNAN_PROYEKS,

                init() {
                    // Edit mode: pre-fill data dari draft dan langsung ke Step 2
                    if (EXISTING_DATA && EXISTING_DATA.length > 0) {
                        this.tukangDetails = EXISTING_DATA;
                        this.selectedTukang = EXISTING_DATA.map(t => t.id);
                        this.currentStep = 2;
                        this.openAccordion = 0;
                    }
                },

                // ---- Computed ----
                get filteredTukang() {
                    if (!this.searchTukang) return ALL_TUKANG;
                    const q = this.searchTukang.toLowerCase();
                    return ALL_TUKANG.filter(t =>
                        t.nama_tukang.toLowerCase().includes(q) || t.kode.toLowerCase().includes(q)
                    );
                },

                // ---- Step 1 Helpers ----
                validatePeriode() {
                    this.periodeError = '';
                    if (this.tanggalMulai && this.tanggalSelesai && this.tanggalSelesai < this.tanggalMulai) {
                        this.periodeError = 'Tanggal selesai tidak boleh sebelum tanggal mulai';
                    }
                },

                toggleSelectTukang(id) {
                    const idx = this.selectedTukang.indexOf(id);
                    if (idx === -1) this.selectedTukang.push(id);
                    else this.selectedTukang.splice(idx, 1);
                },

                toggleAllTukang(e) {
                    if (e.target.checked) {
                        this.selectedTukang = this.filteredTukang.map(t => t.id);
                    } else {
                        const filteredIds = this.filteredTukang.map(t => t.id);
                        this.selectedTukang = this.selectedTukang.filter(id => !filteredIds.includes(id));
                    }
                },

                goToStep2() {
                    this.step1Error = '';
                    if (!this.tanggalMulai) { this.step1Error = 'Tanggal mulai harus diisi.'; return; }
                    if (!this.tanggalSelesai) { this.step1Error = 'Tanggal selesai harus diisi.'; return; }
                    if (this.tanggalSelesai < this.tanggalMulai) { this.step1Error = 'Tanggal selesai tidak boleh sebelum tanggal mulai.'; return; }
                    if (this.selectedTukang.length === 0) { this.step1Error = 'Pilih minimal satu tukang.'; return; }

                    this.buildTukangDetails();
                    this.currentStep = 2;
                    this.openAccordion = 0;
                },

                buildTukangDetails() {
                    const dates = this.generateDateRange(this.tanggalMulai, this.tanggalSelesai);
                    const chosenTukang = ALL_TUKANG.filter(t => this.selectedTukang.includes(t.id));

                    // Urutkan berdasarkan ID tukang ASC
                    chosenTukang.sort((a, b) => a.id - b.id);

                    this.tukangDetails = chosenTukang.map(t => ({
                        id: t.id,
                        kode: t.kode,
                        nama_tukang: t.nama_tukang,
                        gaji_harian_default: parseFloat(t.gaji_harian_default),
                        jam_kerja_default: t.jam_kerja_default,
                        details: dates.map(d => ({
                            tanggal: d,
                            hadir: true,
                            nominal: parseFloat(t.gaji_harian_default),
                            jam_normal: t.jam_kerja_default,
                            alokasi_normal: [],
                            alokasi_lembur: [],
                        }))
                    }));
                },

                generateDateRange(start, end) {
                    const dates = [];
                    let current = new Date(start);
                    const endDate = new Date(end);
                    while (current <= endDate) {
                        dates.push(current.toISOString().split('T')[0]);
                        current.setDate(current.getDate() + 1);
                    }
                    return dates;
                },

                // ---- Accordion ----
                toggleAccordion(idx) {
                    this.openAccordion = this.openAccordion === idx ? null : idx;
                },

                // ---- Row handlers ----
                onHadirChange(tIdx, dIdx) {
                    const detail = this.tukangDetails[tIdx].details[dIdx];
                    const tukang = this.tukangDetails[tIdx];
                    if (!detail.hadir) {
                        detail.nominal = 0;
                        detail.jam_normal = 0;
                        detail.alokasi_normal = [];
                        detail.alokasi_lembur = [];
                    } else {
                        detail.nominal = tukang.gaji_harian_default;
                        detail.jam_normal = tukang.jam_kerja_default;
                    }
                },

                onNominalInput(e, tIdx, dIdx) {
                    const clean = e.target.value.replace(/\D/g, '');
                    const val = parseInt(clean) || 0;
                    this.tukangDetails[tIdx].details[dIdx].nominal = val;
                    e.target.value = this.formatRupiah(val);
                },

                // ---- Select2 Options Loader Helpers ----
                getReferensiOptions(jenis) {
                    if (!this.isAbm && jenis === 'pembangunan_proyek') return this.pembangunanProyeks;
                    if (jenis === 'pembangunan_unit') return this.pembangunanUnits;
                    if (jenis === 'pembangunan_kawasan') return this.pembangunanKawasans;
                    return [];
                },

                getLemburReferensiOptions(jenis) {
                    if (!this.isAbm && jenis === 'pembangunan_proyek') return this.pembangunanProyeks;
                    if (jenis === 'pembangunan_unit') return this.pembangunanUnits;
                    if (jenis === 'pembangunan_kawasan') return this.pembangunanKawasans;
                    return [];
                },

                onAktivitasJenisChange() {
                    this.newAktivitas.referensi_id = '';
                    const jenis = this.newAktivitas.referensi_jenis;
                    const options = this.getReferensiOptions(jenis);
                    this.$nextTick(() => {
                        const selectEl = $('#select-referensi-id');
                        // Destroy existing Select2 instance
                        if (selectEl.hasClass('select2-hidden-accessible')) {
                            selectEl.select2('destroy');
                        }
                        // Clear existing options and rebuild from Alpine data
                        selectEl.empty().append('<option value="">-- Pilih --</option>');
                        options.forEach(item => {
                            selectEl.append(new Option(item.label, item.id, false, false));
                        });
                        // Reinitialize Select2
                        selectEl.select2({
                            dropdownParent: selectEl.parent(),
                            placeholder: '-- Pilih --',
                            allowClear: true
                        }).off('change.aktivitas').on('change.aktivitas', (e) => {
                            this.newAktivitas.referensi_id = e.target.value;
                        });
                        selectEl.val('').trigger('change');
                    });
                },

                onLemburJenisChange() {
                    this.newLembur.referensi_id = '';
                    const jenis = this.newLembur.referensi_jenis;
                    const options = this.getLemburReferensiOptions(jenis);
                    this.$nextTick(() => {
                        const selectEl = $('#select-lembur-referensi-id');
                        if (selectEl.hasClass('select2-hidden-accessible')) {
                            selectEl.select2('destroy');
                        }
                        selectEl.empty().append('<option value="">-- Pilih --</option>');
                        options.forEach(item => {
                            selectEl.append(new Option(item.label, item.id, false, false));
                        });
                        selectEl.select2({
                            dropdownParent: selectEl.parent(),
                            placeholder: '-- Pilih --',
                            allowClear: true
                        }).off('change.lembur').on('change.lembur', (e) => {
                            this.newLembur.referensi_id = e.target.value;
                        });
                        selectEl.val('').trigger('change');
                    });
                },

                // ---- Modal Aktivitas ----
                openModalAktivitas(tIdx, dIdx) {
                    this.modalAktivitasTIndex = tIdx;
                    this.modalAktivitasDIndex = dIdx;
                    this.modalAktivitasTukang = this.tukangDetails[tIdx];
                    this.modalAktivitasDetail = this.tukangDetails[tIdx].details[dIdx];
                    this.newAktivitas = { referensi_jenis: '', referensi_id: '', jam: 1 };
                    this.modalAktivitasOpen = true;

                    this.$nextTick(() => {
                        const selectEl = $('#select-referensi-id');
                        if (selectEl.hasClass('select2-hidden-accessible')) {
                            selectEl.select2('destroy');
                        }
                        selectEl.empty().append('<option value="">-- Pilih --</option>');
                        selectEl.select2({
                            dropdownParent: selectEl.parent(),
                            placeholder: '-- Pilih --',
                            allowClear: true
                        }).off('change.aktivitas').on('change.aktivitas', (e) => {
                            this.newAktivitas.referensi_id = e.target.value;
                        });
                        selectEl.val('').trigger('change');
                    });
                },

                closeModalAktivitas() {
                    this.modalAktivitasOpen = false;
                },

                saveModalAktivitas() {
                    if (this.getSisaJam() !== 0) return;
                    this.closeModalAktivitas();
                },

                addAktivitas() {
                    if (!this.newAktivitas.referensi_jenis || !this.newAktivitas.referensi_id || !this.newAktivitas.jam) return;
                    this.modalAktivitasDetail.alokasi_normal.push({ ...this.newAktivitas });
                    this.newAktivitas = { referensi_jenis: '', referensi_id: '', jam: 1 };
                    this.$nextTick(() => {
                        $('#select-referensi-id').val('').trigger('change');
                    });
                },

                removeAktivitas(idx) {
                    this.modalAktivitasDetail.alokasi_normal.splice(idx, 1);
                },

                getTotalJamAlokasi() {
                    if (!this.modalAktivitasDetail) return 0;
                    return this.modalAktivitasDetail.alokasi_normal.reduce((s, a) => s + (a.jam || 0), 0);
                },

                getSisaJam() {
                    if (!this.modalAktivitasDetail) return 0;
                    return this.modalAktivitasDetail.jam_normal - this.getTotalJamAlokasi();
                },

                // ---- Modal Lembur ----
                openModalLembur(tIdx, dIdx) {
                    this.modalLemburTIndex = tIdx;
                    this.modalLemburDIndex = dIdx;
                    this.modalLemburTukang = this.tukangDetails[tIdx];
                    this.modalLemburDetail = this.tukangDetails[tIdx].details[dIdx];
                    this.newLembur = { referensi_jenis: '', referensi_id: '', jam: 1, tarif: 0 };
                    this.modalLemburOpen = true;

                    this.$nextTick(() => {
                        const selectEl = $('#select-lembur-referensi-id');
                        if (selectEl.hasClass('select2-hidden-accessible')) {
                            selectEl.select2('destroy');
                        }
                        selectEl.empty().append('<option value="">-- Pilih --</option>');
                        selectEl.select2({
                            dropdownParent: selectEl.parent(),
                            placeholder: '-- Pilih --',
                            allowClear: true
                        }).off('change.lembur').on('change.lembur', (e) => {
                            this.newLembur.referensi_id = e.target.value;
                        });
                        selectEl.val('').trigger('change');
                    });
                },

                closeModalLembur() {
                    this.modalLemburOpen = false;
                },

                saveModalLembur() {
                    this.closeModalLembur();
                },

                addLembur() {
                    if (!this.newLembur.referensi_jenis || !this.newLembur.referensi_id || !this.newLembur.jam) return;
                    this.modalLemburDetail.alokasi_lembur.push({ ...this.newLembur });
                    this.newLembur = { referensi_jenis: '', referensi_id: '', jam: 1, tarif: 0 };
                    this.$nextTick(() => {
                        $('#select-lembur-referensi-id').val('').trigger('change');
                    });
                },

                removeLembur(idx) {
                    this.modalLemburDetail.alokasi_lembur.splice(idx, 1);
                },

                getTotalJamLembur() {
                    if (!this.modalLemburDetail) return 0;
                    return this.modalLemburDetail.alokasi_lembur.reduce((s, l) => s + (l.jam || 0), 0);
                },

                getTotalNominalLembur() {
                    if (!this.modalLemburDetail) return 0;
                    return this.modalLemburDetail.alokasi_lembur.reduce((s, l) => s + (l.jam || 0) * (l.tarif || 0), 0);
                },

                // ---- Ringkasan per-Tukang ----
                getHariMasuk(t) {
                    return t.details.filter(d => d.hadir).length;
                },
                getUpahNormal(t) {
                    return t.details.reduce((s, d) => s + (d.hadir ? (d.nominal || 0) : 0), 0);
                },
                getTotalLembur(t) {
                    return t.details.reduce((s, d) => {
                        return s + d.alokasi_lembur.reduce((sl, l) => sl + (l.jam || 0) * (l.tarif || 0), 0);
                    }, 0);
                },
                getTotalUpahTukang(t) {
                    return this.getUpahNormal(t) + this.getTotalLembur(t);
                },
                getAlokasiWarning(t) {
                    return t.details.some(d => d.hadir && (
                        d.alokasi_normal.length === 0 ||
                        d.alokasi_normal.reduce((s, a) => s + (a.jam || 0), 0) !== d.jam_normal
                    ));
                },
                canSubmitDiajukan() {
                    return this.tukangDetails.length > 0 && !this.tukangDetails.some(t => this.getAlokasiWarning(t));
                },

                // ---- Ringkasan Global ----
                getTotalHariMasukAll() {
                    if (!this.tanggalMulai || !this.tanggalSelesai) return 0;
                    return this.generateDateRange(this.tanggalMulai, this.tanggalSelesai).length;
                },
                getTotalUpahNormalAll() {
                    return this.tukangDetails.reduce((s, t) => s + this.getUpahNormal(t), 0);
                },
                getTotalLemburAll() {
                    return this.tukangDetails.reduce((s, t) => s + this.getTotalLembur(t), 0);
                },
                getGrandTotal() {
                    return this.getTotalUpahNormalAll() + this.getTotalLemburAll();
                },

                // ---- Helpers ----
                getReferensiLabel(jenis, id) {
                    let list = [];
                    if (!this.isAbm && jenis === 'pembangunan_proyek') list = this.pembangunanProyeks;
                    else if (jenis === 'pembangunan_unit') list = this.pembangunanUnits;
                    else if (jenis === 'pembangunan_kawasan') list = this.pembangunanKawasans;
                    const found = list.find(x => x.id == id);
                    return found ? found.label : '-';
                },

                formatRupiah(num) {
                    if (!num) return '0';
                    return new Intl.NumberFormat('id-ID').format(num);
                },

                formatDateDisplay(dateStr) {
                    if (!dateStr) return '-';
                    const d = new Date(dateStr);
                    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                },

                formatTanggalShort(dateStr) {
                    if (!dateStr) return '-';
                    const d = new Date(dateStr);
                    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
                },

                formatTanggalLong(dateStr) {
                    if (!dateStr) return '-';
                    const d = new Date(dateStr);
                    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                },

                // ---- Submit ----
                submitForm(statusVal) {
                    if (statusVal === 'diajukan' && !this.canSubmitDiajukan()) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Validasi Gagal',
                            text: 'Ada aktivitas normal yang belum dialokasikan secara lengkap atau tidak sesuai jam kerja. Periksa kembali sebelum mengajukan.',
                        });
                        return;
                    }

                    const payload = {
                        nomor_upah_harian: this.nomorUpah,
                        tanggal_mulai: this.tanggalMulai,
                        tanggal_selesai: this.tanggalSelesai,
                        status: statusVal,
                        tukang_details: this.tukangDetails.map(t => ({
                            tukang_id: t.id,
                            gaji_harian_default_snapshot: t.gaji_harian_default,
                            jam_default_snapshot: t.jam_kerja_default,
                            details: t.details.map(d => ({
                                tanggal: d.tanggal,
                                status_kehadiran: d.hadir ? 1 : 0,
                                nominal_harian_final: d.hadir ? d.nominal : 0,
                                jam_kerja: d.hadir ? d.jam_normal : 0,
                                alokasi_normal: d.alokasi_normal,
                                alokasi_lembur: d.alokasi_lembur,
                            }))
                        }))
                    };

                    // PUT via form (update draft)
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = statusVal === 'diajukan' ? SUBMIT_DRAFT_ROUTE : UPDATE_DRAFT_ROUTE;

                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'PUT';
                    form.appendChild(methodInput);

                    const csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = '{{ csrf_token() }}';
                    form.appendChild(csrf);

                    const dataInput = document.createElement('input');
                    dataInput.type = 'hidden';
                    dataInput.name = 'payload';
                    dataInput.value = JSON.stringify(payload);
                    form.appendChild(dataInput);

                    document.body.appendChild(form);
                    form.submit();
                },
            };
        }
    </script>

@endsection