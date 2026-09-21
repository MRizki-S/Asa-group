@extends('layouts.app')

@section('pageActive', 'orderBarangUnit')

@section('content')
{{-- Select2 CSS --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">
<style>
    .select2-container--bootstrap4 .select2-selection--single {
        height: 48px !important;
        padding: 10px 12px !important;
        border-radius: 0.75rem !important;
        border-color: #d1d5db !important;
        background-color: #f9fafb !important;
        font-size: 0.875rem !important;
    }
    @media screen and (max-width: 768px) {
        .select2-container--bootstrap4 .select2-selection--single,
        .select2-search__field {
            font-size: 16px !important;
        }
    }
    .dark .select2-container--bootstrap4 .select2-selection--single {
        background-color: #1f2937 !important;
        border-color: #374151 !important;
        color: #fff !important;
    }
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
        color: #1f2937 !important;
        line-height: 26px !important;
    }
    .dark .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
        color: #f9fafb !important;
    }
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
        height: 46px !important;
    }
</style>

<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-data="createOrderUnitPengawasComponent()">

    <div x-data="{ pageName: 'Buat Order Barang Unit' }">
        @include('partials.breadcrumb')
    </div>

    <!-- Top Card: Header & Form Info Pembangunan -->
    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 dark:border-gray-800 pb-3">
            <div>
                <h3 class="text-lg font-bold text-gray-800 dark:text-white/90">Buat Order Barang Pembangunan Unit</h3>
                <p class="text-xs text-gray-500">Pilih unit pembangunan, pilih QC RAP, dan susun daftar barang ke dalam keranjang checkout</p>
            </div>
            <a href="{{ route('produksi.pembangunanUnit.orderIndex') }}"
                class="mt-2 sm:mt-0 inline-flex items-center gap-2 rounded-lg bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300 transition dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg> Kembali
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Tanggal & Waktu Order -->
            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">
                    Tanggal & Waktu Order <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-2 gap-2">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-2.5 pointer-events-none">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                            </svg>
                        </div>
                        <input type="text"
                            name="tanggal_order_display"
                            x-init="flatpickr($el, {
                                dateFormat: 'd-m-Y',
                                defaultDate: '{{ now()->format('d-m-Y') }}',
                                onChange: (selectedDates, dateStr, instance) => {
                                    tanggalSimpan = instance.formatDate(selectedDates[0], 'Y-m-d');
                                }
                            })"
                            class="w-full rounded-xl border border-gray-300 bg-gray-50 p-3 pl-8 text-xs sm:text-sm text-gray-800 dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500 shadow-sm"
                            placeholder="Tanggal Order">
                        <input type="hidden" name="tanggal_order" x-model="tanggalSimpan">
                    </div>

                    <div class="relative flex items-center">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-2.5 pointer-events-none">
                            <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <input type="text" readonly :value="waktuLive + ' WIB'"
                            class="w-full rounded-xl border border-blue-200 bg-blue-50/50 p-3 pl-8 pr-6 text-xs sm:text-sm text-blue-800 font-semibold dark:bg-gray-800 dark:border-blue-900/50 dark:text-blue-300 cursor-not-allowed select-none shadow-sm">
                        <span class="absolute right-2.5 flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-600"></span>
                        </span>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">Tipe Pembangunan <span class="text-red-500">*</span></label>
                <select x-model="jenisPembangunan" @change="onJenisPembangunanChange()"
                    class="w-full rounded-xl border-gray-300 bg-gray-50 p-3 text-sm text-gray-800 dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                    <option value="pembangunan">Pembangunan (Proses)</option>
                    <option value="servis">Servis (Selesai)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">Select Pembangunan Unit <span class="text-red-500">*</span></label>
                <select x-ref="pembangunanSelect"
                    class="w-full rounded-xl border-gray-300 bg-gray-50 p-3 text-sm text-gray-800 dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Pilih Pembangunan Unit --</option>
                    <template x-for="pu in filteredPembangunanUnits" :key="pu.id">
                        <option :value="pu.id" x-text="pu.label_formatted"></option>
                    </template>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">Select QC Pembangunan Unit <span class="text-red-500">*</span></label>
                <select x-ref="qcSelect" x-model="qcId"
                    class="w-full rounded-xl border-gray-300 bg-gray-50 p-3 text-sm text-gray-800 dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50">
                    <option value="">-- Pilih QC --</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">Pengawas Unit</label>
                <input type="text" disabled :value="selectedUnitInfo?.pengawas_nama || '-'"
                    class="w-full rounded-xl border border-gray-200 bg-gray-100 p-3 text-sm text-gray-700 font-semibold dark:bg-gray-800/80 dark:border-gray-700 dark:text-gray-300 outline-none cursor-not-allowed opacity-85 shadow-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">Subcon</label>
                <input type="text" disabled :value="selectedUnitInfo?.subcon_nama || '-'"
                    class="w-full rounded-xl border border-gray-200 bg-gray-100 p-3 text-sm text-gray-700 font-semibold dark:bg-gray-800/80 dark:border-gray-700 dark:text-gray-300 outline-none cursor-not-allowed opacity-85 shadow-sm">
            </div>

            <div class="md:col-span-3">
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">Catatan Order Pengawas (Opsional)</label>
                <textarea x-model="catatan" rows="2" placeholder="Masukkan catatan keperluan order material jika ada..."
                    class="w-full rounded-xl border-gray-300 bg-gray-50 p-3 text-sm text-gray-800 dark:bg-gray-800 dark:border-gray-700 dark:text-white placeholder:text-gray-400 focus:border-blue-500 focus:ring-blue-500"></textarea>
            </div>
        </div>
    </div>

    <!-- Bottom Section: Katalog Barang & Keranjang -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 pb-20 lg:pb-0">

        <!-- Card Left (7 Cols): Katalog Barang Gudang & RAP -->
        <div class="lg:col-span-7 rounded-2xl border border-gray-200 bg-white p-4 sm:p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03] flex flex-col h-[600px] lg:h-[680px]">

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 dark:border-gray-800 pb-3 mb-3 shrink-0">
                <h4 class="text-sm font-bold text-gray-800 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg> Katalog Barang
                </h4>

                <!-- Toggle Stock vs Direct -->
                <div class="flex items-center p-1 bg-gray-100 dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600">
                    <button type="button" @click="setJenisOrderType('stock')"
                        :class="jenisOrderType === 'stock' ? 'bg-white text-blue-600 shadow-sm dark:bg-gray-800 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
                        class="px-4 py-1.5 text-xs font-bold rounded-lg transition-all flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h6m-6 4h6m-6 4h6"/></svg> Barang Stock
                    </button>
                    <button type="button" @click="setJenisOrderType('direct')"
                        :class="jenisOrderType === 'direct' ? 'bg-white text-orange-600 shadow-sm dark:bg-gray-800 dark:text-orange-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
                        class="px-4 py-1.5 text-xs font-bold rounded-lg transition-all flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg> Barang Direct
                    </button>
                </div>
            </div>

            <!-- Search Bar Input -->
            <div class="mb-4 shrink-0 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" x-model="searchQuery" placeholder="Cari nama atau kode barang di katalog..."
                    class="w-full text-base sm:text-xs pl-9 pr-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-gray-800 dark:bg-gray-800 dark:border-gray-700 dark:text-white placeholder:text-gray-400 focus:bg-white focus:border-blue-500 focus:ring-blue-500 transition-all">
            </div>

            <!-- List Barang Scrollable -->
            <div class="flex-1 overflow-y-auto pr-1 sm:pr-2 custom-scrollbar space-y-5">
                <!-- 1. Barang Sesuai RAP QC -->
                <div>
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <span class="text-xs font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider bg-blue-50 dark:bg-blue-900/40 px-2.5 py-1 rounded-md border border-blue-100 dark:border-blue-800 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg> 1. Barang Sesuai RAP QC
                        </span>

                        <button type="button" x-show="qcId && filteredRapItems().length > 0" @click="addAllRapToCart()" :disabled="!pembangunanUnitId || !qcId"
                            class="px-3 py-1 bg-blue-100 hover:bg-blue-200 disabled:opacity-40 disabled:cursor-not-allowed text-blue-700 dark:bg-blue-900/60 dark:text-blue-300 dark:hover:bg-blue-800 text-[11px] font-bold rounded-lg transition-all flex items-center gap-1.5 shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Tambah Semua RAP</span>
                        </button>
                    </div>

                    <div x-show="qcId && filteredRapItems().length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <template x-for="rap in filteredRapItems()" :key="rap.id">
                            <div class="p-3.5 bg-blue-50/40 dark:bg-blue-950/20 rounded-xl border border-blue-100 dark:border-blue-800/40 flex items-center justify-between gap-3 hover:border-blue-300 transition-all">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[9px] font-black text-blue-600 dark:text-blue-400 uppercase bg-blue-100 dark:bg-blue-900/60 px-1.5 py-0.5 rounded">RAP</span>
                                        <span class="text-[9px] font-mono text-gray-400 uppercase" x-text="rap.kode_barang"></span>
                                    </div>
                                    <h5 class="text-xs font-bold text-gray-800 dark:text-gray-200 truncate mt-1" x-text="rap.nama_barang"></h5>
                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-[10px] text-gray-500 font-medium mt-1">
                                        <span x-text="'RAP: ' + formatNumber(rap.volume) + ' ' + (rap.rap_satuan_nama || rap.base_unit_nama)"></span>
                                        <span class="text-gray-300 dark:text-gray-600">•</span>
                                        <span class="text-blue-600 dark:text-blue-400 font-bold" x-text="'Terorder: ' + formatNumber((rap.total_ordered_base || 0) / (rap.faktor_konversi || 1)) + ' ' + (rap.rap_satuan_nama || rap.base_unit_nama)"></span>
                                        <span class="text-gray-300 dark:text-gray-600">•</span>
                                        <span class="font-bold" :class="((rap.volume - ((rap.total_ordered_base || 0) / (rap.faktor_konversi || 1))) > 0) ? 'text-indigo-600 dark:text-indigo-400' : 'text-amber-600 dark:text-amber-400'"
                                            x-text="'Sisa RAP: ' + formatNumber(Math.max(0, rap.volume - ((rap.total_ordered_base || 0) / (rap.faktor_konversi || 1)))) + ' ' + (rap.rap_satuan_nama || rap.base_unit_nama)"></span>
                                        <span class="text-gray-300 dark:text-gray-600">•</span>
                                        <span class="text-emerald-600 dark:text-emerald-400 font-bold" x-text="'Stok: ' + formatNumber(rap.stok_gudang || 0) + ' ' + rap.base_unit_nama"></span>
                                    </div>
                                </div>
                                <button type="button" @click="addToCart(null, true, rap)" :disabled="!pembangunanUnitId || !qcId"
                                    class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed text-white text-xs font-bold rounded-lg shadow-sm transition shrink-0 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Tambah
                                </button>
                            </div>
                        </template>
                    </div>

                    <div x-show="!qcId" class="p-4 bg-gray-50 dark:bg-gray-800/40 rounded-xl text-center text-xs text-gray-400 italic">
                        Pilih Unit dan QC di atas untuk melihat barang RAP.
                    </div>
                    <div x-show="qcId && filteredRapItems().length === 0" class="p-4 bg-gray-50 dark:bg-gray-800/40 rounded-xl text-center text-xs text-gray-400 italic">
                        Tidak ada barang RAP dengan tipe <span x-text="jenisOrderType"></span> di QC ini.
                    </div>
                </div>

                <!-- 2. Barang Di Luar RAP -->
                <div class="pt-2">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider bg-gray-100 dark:bg-gray-700 px-2.5 py-1 rounded-md border border-gray-200 dark:border-gray-600 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                            2. Barang Di Luar RAP (Katalog Gudang)
                        </span>
                        <div class="h-px bg-gray-200 dark:bg-gray-700 flex-1"></div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <template x-for="bg in filteredGudangItems()" :key="bg.id">
                            <div class="p-3.5 bg-gray-50/70 dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700/70 flex items-center justify-between gap-3 hover:border-gray-300 transition-all">
                                <div class="min-w-0 flex-1">
                                    <p class="text-[9px] font-mono text-gray-400 uppercase" x-text="bg.kode_barang"></p>
                                    <h5 class="text-xs font-bold text-gray-800 dark:text-gray-200 truncate" x-text="bg.nama_barang"></h5>
                                    <p class="text-[10px] text-gray-500 font-medium mt-0.5" x-text="'Stok: ' + formatNumber(bg.stok_gudang) + ' ' + bg.base_unit_nama"></p>
                                </div>
                                <button type="button" @click="addToCart(bg, false, null)" :disabled="!pembangunanUnitId || !qcId"
                                    class="px-3 py-1.5 bg-gray-800 hover:bg-gray-900 disabled:opacity-40 disabled:cursor-not-allowed text-white dark:bg-gray-700 dark:hover:bg-gray-600 text-xs font-bold rounded-lg shadow-sm transition shrink-0 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Tambah
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Right (5 Cols): Keranjang Checkout Order (Desktop) -->
        <div class="hidden lg:flex lg:col-span-5 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03] flex-col h-[680px]">

            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3 mb-3 shrink-0">
                <h4 class="text-sm font-bold text-gray-800 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg> Keranjang Checkout Order
                </h4>
                <span class="text-xs font-black px-2.5 py-1 bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 rounded-full"
                    x-text="formatNumber(cart.length) + ' Barang'"></span>
            </div>

            <!-- Search Bar Input Keranjang Checkout -->
            <div class="mb-4 shrink-0 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" x-model="cartSearchQuery" placeholder="Cari barang di keranjang..."
                    class="w-full text-base sm:text-xs pl-9 pr-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-gray-800 dark:bg-gray-800 dark:border-gray-700 dark:text-white placeholder:text-gray-400 focus:bg-white focus:border-blue-500 focus:ring-blue-500 transition-all">
            </div>

            <!-- Cart Items Container -->
            <div class="flex-1 overflow-y-auto pr-1 custom-scrollbar space-y-3">
                <template x-for="(item, idx) in cart" :key="item.barang_id + '-' + (item.rap_id ?? 'null')">
                    <div
                        x-show="cartMatchesSearch(item)"
                        class="p-3.5 bg-gray-50/80 dark:bg-gray-800/60 rounded-xl border border-gray-200 dark:border-gray-700/80 shadow-sm space-y-2 relative">
                        <button type="button" @click="removeFromCart(idx)" class="absolute top-3 right-3 text-red-500 hover:text-red-700 text-xs font-bold p-1" title="Hapus Barang">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>

                        <div class="pr-6">
                            <div class="flex items-center justify-between gap-1.5">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <span x-show="item.is_rap" class="px-1.5 py-0.5 bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-300 text-[8px] font-black uppercase rounded">RAP</span>
                                    <span x-show="!item.is_rap" class="px-1.5 py-0.5 bg-amber-100 text-amber-700 dark:bg-amber-900/60 dark:text-amber-300 text-[8px] font-black uppercase rounded">Luar RAP</span>
                                    <h5 class="text-xs font-bold text-gray-800 dark:text-gray-200 truncate" x-text="item.nama_barang"></h5>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center justify-between text-[10px] text-gray-400 mt-1 gap-1">
                                <span x-text="item.kode_barang"></span>
                                <div class="flex items-center gap-2">
                                    <span x-show="item.is_rap" class="text-blue-600 dark:text-blue-400 font-medium"
                                        x-text="'Terorder: ' + formatNumber(getConvertedOrdered(item)) + ' ' + (item.satuans.find(s=>s.id == item.satuan_id)?.nama_satuan || '')"></span>
                                    <span x-show="item.is_rap" class="text-indigo-600 dark:text-indigo-400 font-bold"
                                        x-text="'Sisa RAP: ' + formatNumber(getConvertedRemainingRap(item)) + ' ' + (item.satuans.find(s=>s.id == item.satuan_id)?.nama_satuan || '')"></span>
                                    <span class="font-bold text-emerald-600 dark:text-emerald-400" x-text="'Stok: ' + formatNumber(getConvertedStock(item)) + ' ' + (item.satuans.find(s=>s.id == item.satuan_id)?.nama_satuan || '')"></span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                            <!-- Input Jumlah dengan tombol - dan + -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Jumlah Order</label>
                                <div class="flex items-center rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 overflow-hidden">
                                    <button type="button" @click="decrementQty(item)"
                                        class="px-2 py-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-300 transition-colors select-none shrink-0">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                                    </button>
                                    <input type="text" inputmode="decimal" x-model="item.qty" @change="validateQtyOnChange(item)" placeholder="0"
                                        class="w-full text-xs font-bold text-center bg-transparent text-gray-800 dark:text-white border-0 focus:ring-0 focus:outline-none p-1">
                                    <button type="button" @click="incrementQty(item)"
                                        class="px-2 py-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-300 transition-colors select-none shrink-0">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Satuan</label>
                                <select x-model="item.satuan_id"
                                    class="w-full text-xs p-2 rounded-lg border-gray-300 bg-white text-gray-800 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-blue-500">
                                    <template x-for="s in item.satuans" :key="s.id">
                                        <option :value="s.id" class="text-gray-800 dark:text-white" x-text="s.nama_satuan"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <!-- Textarea Alasan jika barang diluar RAP ATAU akumulasi order melebihi RAP -->
                        <div x-show="jenisPembangunan !== 'servis' && (!item.is_rap || isExceedingRap(item))" class="pt-1">
                            <label class="block text-[10px] font-bold text-red-500 uppercase mb-1">
                                <span x-show="!item.is_rap">Alasan Permintaan (Diluar RAP) <span class="text-red-500">*</span></span>
                                <span x-show="item.is_rap && isExceedingRap(item)">Alasan Permintaan (Melebihi RAP) <span class="text-red-500">*</span></span>
                            </label>
                            <input type="text" x-model="item.alasan" placeholder="Masukkan alasan permintaan..."
                                class="w-full text-xs p-2 rounded-lg border-red-200 bg-red-50/30 text-gray-800 dark:bg-gray-700 dark:border-red-800 dark:text-white placeholder:text-gray-400 focus:ring-red-500">
                        </div>
                    </div>
                </template>

                <div x-show="cart.length === 0" class="h-full flex flex-col items-center justify-center text-center p-8 text-gray-400">
                    <svg class="w-12 h-12 mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400">Keranjang Masih Kosong</p>
                    <p class="text-[11px] text-gray-400 mt-1">Silakan pilih barang dari katalog di sebelah kiri untuk ditambahkan ke keranjang order.</p>
                </div>
            </div>

            <!-- Footer Submit & Reset -->
            <div class="pt-3 border-t border-gray-100 dark:border-gray-800 mt-3 shrink-0 grid grid-cols-2 gap-2">
                <button type="button" @click="resetCart()" :disabled="cart.length === 0"
                    class="w-full py-3 px-4 bg-gray-200 hover:bg-gray-300 disabled:opacity-50 text-gray-700 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 text-xs font-bold rounded-xl transition-all shadow-sm flex items-center justify-center gap-1.5"
                    title="Bersihkan Keranjang">
                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Reset Keranjang</span>
                </button>

                <button type="button" @click="submitForm()"
                    :disabled="submitting || cart.length === 0"
                    class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-bold rounded-xl transition-all shadow-md flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span x-text="submitting ? 'Memproses...' : 'Kirim Order ke Gudang'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Floating Bottom Bar Keranjang (Mobile Only) -->
    <div x-show="cart.length > 0"
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="translate-y-full opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-full opacity-0"
        class="fixed bottom-0 inset-x-0 z-40 p-3.5 bg-white/95 backdrop-blur-md border-t border-gray-200 shadow-2xl dark:bg-gray-900/95 dark:border-gray-800 lg:hidden"
        x-cloak>
        <div class="max-w-md mx-auto flex items-center justify-between gap-3">
            <button type="button" @click="showMobileCart = true" class="flex items-center gap-2.5 text-left flex-1 min-w-0">
                <div class="relative p-2.5 bg-blue-600 text-white rounded-xl shadow-md shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                    </svg>
                    <span class="absolute -top-1.5 -right-1.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-black text-white border-2 border-white dark:border-gray-900"
                        x-text="cart.length"></span>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-bold text-gray-800 dark:text-white truncate">Keranjang Order</p>
                    <p class="text-[11px] text-blue-600 dark:text-blue-400 font-semibold flex items-center gap-1">
                        <span x-text="formatNumber(cart.length) + ' Material dipilih'"></span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                    </p>
                </div>
            </button>

            <button type="button" @click="submitForm()"
                :disabled="submitting || cart.length === 0"
                class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-xs font-bold rounded-xl shadow-md transition shrink-0 flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Checkout</span>
            </button>
        </div>
    </div>

    <!-- Mobile Drawer / Bottom Sheet Keranjang Checkout -->
    <div x-show="showMobileCart" class="fixed inset-0 z-[99999] lg:hidden" x-cloak>
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
            x-show="showMobileCart"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="showMobileCart = false"></div>

        <!-- Drawer Content (Slide Up) -->
        <div class="fixed inset-x-0 bottom-0 max-h-[85vh] flex flex-col bg-white rounded-t-3xl shadow-2xl border-t border-gray-200 dark:bg-gray-900 dark:border-gray-800"
            x-show="showMobileCart"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="translate-y-full"
            x-transition:enter-end="translate-y-0"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="translate-y-0"
            x-transition:leave-end="translate-y-full">

            <!-- Drag Handle Bar -->
            <div class="pt-3 pb-1 flex justify-center cursor-pointer" @click="showMobileCart = false">
                <div class="w-12 h-1.5 bg-gray-300 rounded-full dark:bg-gray-700"></div>
            </div>

            <!-- Drawer Header -->
            <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2">
                    <div class="p-2 bg-blue-100 text-blue-600 rounded-xl dark:bg-blue-900/50 dark:text-blue-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-gray-800 dark:text-white">Keranjang Checkout</h4>
                        <p class="text-[11px] text-gray-500" x-text="formatNumber(cart.length) + ' Material dipilih'"></p>
                    </div>
                </div>
                <button type="button" @click="showMobileCart = false"
                    class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Search Bar Input Keranjang Mobile -->
            <div class="px-5 pt-3 shrink-0 relative">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" x-model="cartSearchQuery" placeholder="Cari barang di keranjang..."
                        class="w-full text-base sm:text-xs pl-9 pr-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-gray-800 dark:bg-gray-800 dark:border-gray-700 dark:text-white placeholder:text-gray-400 focus:bg-white focus:border-blue-500 focus:ring-blue-500 transition-all">
                </div>
            </div>

            <!-- Cart Items Container (Scrollable) -->
            <div class="flex-1 overflow-y-auto p-5 custom-scrollbar space-y-3">
                <template x-for="(item, idx) in cart" :key="'mob-' + item.barang_id + '-' + (item.rap_id ?? 'null')">
                    <div
                        x-show="cartMatchesSearch(item)"
                        class="p-3.5 bg-gray-50/90 dark:bg-gray-800/80 rounded-2xl border border-gray-200 dark:border-gray-700/80 shadow-sm space-y-2.5 relative">
                        <button type="button" @click="removeFromCart(idx)" class="absolute top-3 right-3 text-red-500 hover:text-red-700 p-1 rounded-lg" title="Hapus Barang">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>

                        <div class="pr-7">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <span x-show="item.is_rap" class="px-1.5 py-0.5 bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-300 text-[8px] font-black uppercase rounded shrink-0">RAP</span>
                                <span x-show="!item.is_rap" class="px-1.5 py-0.5 bg-amber-100 text-amber-700 dark:bg-amber-900/60 dark:text-amber-300 text-[8px] font-black uppercase rounded shrink-0">Luar RAP</span>
                                <h5 class="text-xs font-bold text-gray-800 dark:text-gray-200 truncate" x-text="item.nama_barang"></h5>
                            </div>
                            <div class="flex flex-wrap items-center justify-between text-[10px] text-gray-400 mt-1 gap-1">
                                <span class="font-mono" x-text="item.kode_barang"></span>
                                <div class="flex items-center gap-1.5">
                                    <span x-show="item.is_rap" class="text-blue-600 dark:text-blue-400 font-medium"
                                        x-text="'Terorder: ' + formatNumber(getConvertedOrdered(item))"></span>
                                    <span x-show="item.is_rap" class="text-gray-300 dark:text-gray-600">•</span>
                                    <span x-show="item.is_rap" class="text-indigo-600 dark:text-indigo-400 font-bold"
                                        x-text="'Sisa: ' + formatNumber(getConvertedRemainingRap(item))"></span>
                                    <span class="text-gray-300 dark:text-gray-600">•</span>
                                    <span class="font-bold text-emerald-600 dark:text-emerald-400" x-text="'Stok: ' + formatNumber(getConvertedStock(item)) + ' ' + (item.satuans.find(s=>s.id == item.satuan_id)?.nama_satuan || '')"></span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                            <!-- Input Jumlah dengan tombol - dan + -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Jumlah</label>
                                <div class="flex items-center rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 overflow-hidden">
                                    <button type="button" @click="decrementQty(item)"
                                        class="px-3 py-2.5 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-300 transition-colors select-none shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                                    </button>
                                    <input type="text" inputmode="decimal" x-model="item.qty" @change="validateQtyOnChange(item)" placeholder="0"
                                        class="w-full text-xs font-bold text-center bg-transparent text-gray-800 dark:text-white border-0 focus:ring-0 focus:outline-none p-1">
                                    <button type="button" @click="incrementQty(item)"
                                        class="px-3 py-2.5 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-300 transition-colors select-none shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Satuan</label>
                                <select x-model="item.satuan_id"
                                    class="w-full text-xs p-2.5 rounded-xl border-gray-300 bg-white text-gray-800 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-blue-500">
                                    <template x-for="s in item.satuans" :key="s.id">
                                        <option :value="s.id" class="text-gray-800 dark:text-white" x-text="s.nama_satuan"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <!-- Textarea Alasan jika barang diluar RAP ATAU akumulasi order melebihi RAP -->
                        <div x-show="jenisPembangunan !== 'servis' && (!item.is_rap || isExceedingRap(item))" class="pt-1">
                            <label class="block text-[10px] font-bold text-red-500 uppercase mb-1">
                                <span x-show="!item.is_rap">Alasan (Diluar RAP) <span class="text-red-500">*</span></span>
                                <span x-show="item.is_rap && isExceedingRap(item)">Alasan (Melebihi RAP) <span class="text-red-500">*</span></span>
                            </label>
                            <input type="text" x-model="item.alasan" placeholder="Masukkan alasan permintaan..."
                                class="w-full text-xs p-2 rounded-xl border-red-200 bg-red-50/40 text-gray-800 dark:bg-gray-700 dark:border-red-800 dark:text-white placeholder:text-gray-400 focus:ring-red-500">
                        </div>
                    </div>
                </template>

                <div x-show="cart.length === 0" class="py-12 flex flex-col items-center justify-center text-center text-gray-400">
                    <svg class="w-12 h-12 mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400">Keranjang Masih Kosong</p>
                    <p class="text-[11px] text-gray-400 mt-1">Silakan pilih barang dari katalog untuk ditambahkan.</p>
                </div>
            </div>

            <!-- Drawer Footer Submit & Reset -->
            <div class="p-4 border-t border-gray-100 dark:border-gray-800 shrink-0 grid grid-cols-2 gap-2.5 bg-gray-50 dark:bg-gray-900">
                <button type="button" @click="resetCart(); showMobileCart = false;" :disabled="cart.length === 0"
                    class="w-full py-3 px-3 bg-gray-200 hover:bg-gray-300 disabled:opacity-50 text-gray-700 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 text-xs font-bold rounded-xl transition-all flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Reset</span>
                </button>

                <button type="button" @click="showMobileCart = false; submitForm();"
                    :disabled="submitting || cart.length === 0"
                    class="w-full py-3 px-3 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-bold rounded-xl transition-all shadow-md flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span x-text="submitting ? 'Memproses...' : 'Kirim Order'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Ringkasan Order Barang -->
    <div x-show="showConfirmModal" class="fixed inset-0 z-[999999] overflow-y-auto" x-cloak>
        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-900/60 transition-opacity" @click="showConfirmModal = false"></div>

            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all dark:bg-gray-800 sm:my-8 sm:w-full sm:max-w-2xl border border-gray-100 dark:border-gray-700">
                <div class="bg-gray-50/50 p-5 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 bg-blue-100 text-blue-600 rounded-xl dark:bg-blue-900/50 dark:text-blue-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        </div>
                        <div>
                            <h4 class="text-base font-bold text-gray-800 dark:text-white">Konfirmasi Ringkasan Order Barang</h4>
                            <p class="text-xs text-gray-500">Tinjau kembali rincian barang yang akan diajukan ke staff gudang</p>
                        </div>
                    </div>
                    <button type="button" @click="showConfirmModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-5 max-h-[60vh] overflow-y-auto space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-2 p-3 bg-blue-50/50 rounded-xl border border-blue-100 dark:bg-blue-950/20 dark:border-blue-900/40 text-xs">
                        <div>
                            <span class="text-gray-500">Tipe Order:</span>
                            <span class="font-bold uppercase ml-1" :class="jenisOrderType === 'stock' ? 'text-blue-600' : 'text-orange-600'" x-text="jenisOrderType === 'stock' ? 'Barang Stock' : 'Barang Direct'"></span>
                        </div>
                        <div>
                            <span class="text-gray-500">Total Item:</span>
                            <span class="font-bold text-gray-800 dark:text-white ml-1" x-text="formatNumber(cart.length) + ' Barang'"></span>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-100 text-gray-700 uppercase font-bold dark:bg-gray-700 dark:text-gray-300">
                                <tr>
                                    <th class="p-3">No</th>
                                    <th class="p-3">Barang</th>
                                    <th class="p-3 text-center">Jumlah Order</th>
                                    <th class="p-3 text-center">Status Barang</th>
                                    <th class="p-3">Alasan / Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <template x-for="(c, i) in cart" :key="i">
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30">
                                        <td class="p-3 font-medium text-gray-500" x-text="i + 1"></td>
                                        <td class="p-3">
                                            <p class="font-bold text-gray-800 dark:text-white" x-text="c.nama_barang"></p>
                                            <p class="text-[10px] text-gray-400" x-text="c.kode_barang"></p>
                                        </td>
                                        <td class="p-3 text-center font-bold text-gray-800 dark:text-white">
                                            <span x-text="formatNumber(c.qty) + ' ' + (c.satuans.find(s=>s.id == c.satuan_id)?.nama_satuan || c.satuan_nama)"></span>
                                        </td>
                                        <td class="p-3 text-center">
                                            <span x-show="c.is_rap && !isExceedingRap(c)" class="inline-flex px-2 py-0.5 text-[10px] font-black rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-300">
                                                Sesuai RAP
                                            </span>
                                            <span x-show="c.is_rap && isExceedingRap(c)" class="inline-flex px-2 py-0.5 text-[10px] font-black rounded-full bg-red-100 text-red-700 dark:bg-red-900/60 dark:text-red-300">
                                                Melebihi RAP
                                            </span>
                                            <span x-show="!c.is_rap" class="inline-flex px-2 py-0.5 text-[10px] font-black rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/60 dark:text-amber-300">
                                                Diluar RAP
                                            </span>
                                        </td>
                                        <td class="p-3 text-gray-600 dark:text-gray-300 italic">
                                            <span x-text="c.alasan ? c.alasan : '-'"></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-gray-50 p-4 dark:bg-gray-800/80 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-2">
                    <button type="button" @click="showConfirmModal = false"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-bold rounded-xl dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition">
                        Batal
                    </button>
                    <button type="button" @click="processSubmitOrder()" :disabled="submitting"
                        class="px-5 py-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span x-text="submitting ? 'Memproses...' : 'Ya, Kirim Permintaan Order'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
function createOrderUnitPengawasComponent() {
    return {
        tanggalSimpan: '{{ now()->format('Y-m-d') }}',
        waktuLive: '{{ now()->format('H:i:s') }}',
        clockInterval: null,
        pembangunanUnitId: '',
        qcId: '',
        catatan: '',
        jenisOrderType: 'stock',
        qcs: [],
        rapItems: [],
        cart: [],
        allBarangGudang: {!! json_encode($barangGudang) !!},
        searchQuery: '',
        cartSearchQuery: '',
        loadingQc: false,
        submitting: false,
        showConfirmModal: false,
        showMobileCart: false,
        jenisPembangunan: 'pembangunan',
        allPembangunanUnits: {!! json_encode($pembangunanUnits) !!},

        get filteredPembangunanUnits() {
            if (this.jenisPembangunan === 'servis') {
                return this.allPembangunanUnits.filter(pu => pu.is_selesai);
            }
            return this.allPembangunanUnits.filter(pu => !pu.is_selesai);
        },

        get selectedUnitInfo() {
            if (!this.pembangunanUnitId) return null;
            return this.allPembangunanUnits.find(pu => pu.id == this.pembangunanUnitId) || null;
        },

        onJenisPembangunanChange() {
            this.pembangunanUnitId = '';
            this.qcId = '';
            this.qcs = [];
            this.rapItems = [];
            this.cart = [];

            let pemSelectEl = $(this.$refs.pembangunanSelect);
            let qcSelectEl = $(this.$refs.qcSelect);

            pemSelectEl.val('').empty().append('<option value="">-- Pilih Pembangunan Unit --</option>');
            this.filteredPembangunanUnits.forEach(pu => {
                pemSelectEl.append(new Option(pu.label_formatted, pu.id, false, false));
            });
            pemSelectEl.trigger('change.select2');

            qcSelectEl.val('').empty().append('<option value="">-- Pilih QC --</option>').trigger('change.select2');
        },

        init() {
            const updateClock = () => {
                const now = new Date();
                const h = String(now.getHours()).padStart(2, '0');
                const m = String(now.getMinutes()).padStart(2, '0');
                const s = String(now.getSeconds()).padStart(2, '0');
                this.waktuLive = `${h}:${m}:${s}`;
            };
            updateClock();
            this.clockInterval = setInterval(updateClock, 1000);

            const urlParams = new URLSearchParams(window.location.search);
            const preselectUnitId = urlParams.get('pembangunan_unit_id') || '{{ request('pembangunan_unit_id') }}';
            const preselectQcId = urlParams.get('qc_id') || '{{ request('qc_id') }}';

            if (preselectUnitId) {
                const targetPu = this.allPembangunanUnits.find(pu => String(pu.id) === String(preselectUnitId));
                if (targetPu) {
                    this.jenisPembangunan = targetPu.is_selesai ? 'servis' : 'pembangunan';
                }
            }

            this.$nextTick(() => {
                let pemSelectEl = $(this.$refs.pembangunanSelect);
                pemSelectEl.empty().append('<option value="">-- Pilih Pembangunan Unit --</option>');
                this.filteredPembangunanUnits.forEach(pu => {
                    pemSelectEl.append(new Option(pu.label_formatted, pu.id, false, false));
                });

                pemSelectEl.select2({
                    theme: 'bootstrap4',
                    placeholder: '-- Pilih Pembangunan Unit --',
                    allowClear: true,
                    width: '100%'
                }).on('change', (e) => {
                    this.pembangunanUnitId = e.target.value;
                    this.onPembangunanChange();
                });

                $(this.$refs.qcSelect).select2({
                    theme: 'bootstrap4',
                    placeholder: '-- Pilih QC --',
                    allowClear: true,
                    width: '100%'
                }).on('change', (e) => {
                    this.qcId = e.target.value;
                    this.onQcChange();
                });

                // Auto-select jika diarahkan dari shortcut halaman detail
                if (preselectUnitId) {
                    this.pembangunanUnitId = preselectUnitId;
                    pemSelectEl.val(preselectUnitId).trigger('change.select2');
                    this.onPembangunanChange(preselectQcId);
                }
            });
        },

        onPembangunanChange(targetQcId = null) {
            this.qcId = '';
            this.qcs = [];
            this.rapItems = [];
            this.cart = [];

            let qcSelectEl = $(this.$refs.qcSelect);
            qcSelectEl.val('').empty().append('<option value="">-- Pilih QC --</option>').trigger('change.select2');

            if (!this.pembangunanUnitId) return;

            this.loadingQc = true;
            fetch(`/gudang/permintaan-barang/pembangunan-unit/qc-list/${this.pembangunanUnitId}`)
                .then(res => res.json())
                .then(data => {
                    this.loadingQc = false;
                    if (data.success && data.qcs) {
                        this.qcs = data.qcs;
                        qcSelectEl.empty().append('<option value="">-- Pilih QC --</option>');
                        this.qcs.forEach(qc => {
                            qcSelectEl.append(new Option(qc.nama, qc.id, false, false));
                        });

                        if (targetQcId && this.qcs.some(q => String(q.id) === String(targetQcId))) {
                            this.qcId = targetQcId;
                            qcSelectEl.val(targetQcId).trigger('change.select2');
                            this.onQcChange();
                        } else {
                            qcSelectEl.trigger('change.select2');
                        }
                    }
                })
                .catch(err => {
                    this.loadingQc = false;
                    console.error(err);
                });
        },

        onQcChange() {
            this.cart = [];
            if (!this.qcId) {
                this.rapItems = [];
                return;
            }
            let selectedQc = this.qcs.find(q => q.id == this.qcId);
            this.rapItems = selectedQc ? selectedQc.rap_bahan : [];
        },

        setJenisOrderType(type) {
            if (this.jenisOrderType === type) return;
            if (this.cart.length > 0) {
                Swal.fire({
                    title: 'Ganti Tipe Order?',
                    text: 'Mengubah tipe order akan mengosongkan keranjang saat ini.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Ganti',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.jenisOrderType = type;
                        this.cart = [];
                    }
                });
            } else {
                this.jenisOrderType = type;
            }
        },

        filteredRapItems() {
            let items = this.rapItems.filter(item => {
                return this.jenisOrderType === 'stock' ? item.is_stock : !item.is_stock;
            });
            if (!this.searchQuery) return items;
            let q = this.searchQuery.toLowerCase();
            return items.filter(item => {
                return (item.nama_barang && item.nama_barang.toLowerCase().includes(q)) ||
                       (item.kode_barang && item.kode_barang.toLowerCase().includes(q));
            });
        },

        filteredGudangItems() {
            let rapBarangIds = this.rapItems.map(r => r.barang_id);
            let items = this.allBarangGudang.filter(bg => {
                if (rapBarangIds.includes(bg.id)) return false;
                return this.jenisOrderType === 'stock' ? bg.is_stock : !bg.is_stock;
            });
            if (!this.searchQuery) return items;
            let q = this.searchQuery.toLowerCase();
            return items.filter(bg => {
                return (bg.nama_barang && bg.nama_barang.toLowerCase().includes(q)) ||
                       (bg.kode_barang && bg.kode_barang.toLowerCase().includes(q));
            });
        },

        formatNumber(val) {
            if (!val && val !== 0) return '0';
            return parseFloat(val).toLocaleString('id-ID', { maximumFractionDigits: 3 });
        },

        cartMatchesSearch(item) {
            if (!this.cartSearchQuery) return true;
            let q = this.cartSearchQuery.toLowerCase();
            return (item.nama_barang && item.nama_barang.toLowerCase().includes(q)) ||
                   (item.kode_barang && item.kode_barang.toLowerCase().includes(q));
        },

        addToCart(bgItem, isRap, rapItem) {
            let targetBarangId = isRap ? rapItem.barang_id : bgItem.id;
            let targetRapId = isRap ? rapItem.id : null;

            let existing = this.cart.find(c => c.barang_id === targetBarangId && c.rap_id === targetRapId);
            if (existing) {
                this.incrementQty(existing);
                return;
            }

            if (isRap) {
                let defaultSatuanId = rapItem.rap_satuan_id || (rapItem.satuans.length > 0 ? rapItem.satuans[0].id : null);
                this.cart.push({
                    barang_id: rapItem.barang_id,
                    rap_id: rapItem.id,
                    kode_barang: rapItem.kode_barang,
                    nama_barang: rapItem.nama_barang,
                    is_rap: true,
                    rap_volume: rapItem.volume,
                    rap_faktor: rapItem.faktor_konversi || 1,
                    total_ordered_base: rapItem.total_ordered_base || 0,
                    stok_gudang: rapItem.stok_gudang || 0,
                    satuans: rapItem.satuans || [],
                    satuan_id: defaultSatuanId,
                    satuan_nama: rapItem.rap_satuan_nama || rapItem.base_unit_nama,
                    qty: 1,
                    alasan: ''
                });
            } else {
                let defaultSatuanId = bgItem.base_unit_id || (bgItem.satuans.length > 0 ? bgItem.satuans[0].id : null);
                this.cart.push({
                    barang_id: bgItem.id,
                    rap_id: null,
                    kode_barang: bgItem.kode_barang,
                    nama_barang: bgItem.nama_barang,
                    is_rap: false,
                    rap_volume: 0,
                    rap_faktor: 1,
                    total_ordered_base: 0,
                    stok_gudang: bgItem.stok_gudang || 0,
                    satuans: bgItem.satuans || [],
                    satuan_id: defaultSatuanId,
                    satuan_nama: bgItem.base_unit_nama,
                    qty: 1,
                    alasan: ''
                });
            }
        },

        addAllRapToCart() {
            let rapList = this.filteredRapItems();
            rapList.forEach(rap => {
                let existing = this.cart.find(c => c.barang_id === rap.barang_id && c.rap_id === rap.id);
                if (!existing) {
                    this.addToCart(null, true, rap);
                }
            });
        },

        resetCart() {
            this.cart = [];
        },

        getFaktorKonversi(item) {
            if (!item.satuans || item.satuans.length === 0) return 1;
            let s = item.satuans.find(st => st.id == item.satuan_id);
            return s ? parseFloat(s.konversi_ke_base) || 1 : 1;
        },

        getConvertedStock(item) {
            let faktor = this.getFaktorKonversi(item);
            return faktor > 0 ? (item.stok_gudang / faktor) : item.stok_gudang;
        },

        getConvertedOrdered(item) {
            let faktor = this.getFaktorKonversi(item);
            return faktor > 0 ? (item.total_ordered_base / faktor) : item.total_ordered_base;
        },

        getConvertedRemainingRap(item) {
            let faktor = this.getFaktorKonversi(item);
            let totalRapBase = item.rap_volume * item.rap_faktor;
            let sisaBase = Math.max(0, totalRapBase - item.total_ordered_base);
            return faktor > 0 ? (sisaBase / faktor) : sisaBase;
        },

        isExceedingRap(item) {
            if (!item.is_rap) return false;
            let faktor = this.getFaktorKonversi(item);
            let orderBase = this.parseQty(item.qty) * faktor;
            let totalRapBase = item.rap_volume * item.rap_faktor;
            return (item.total_ordered_base + orderBase) > (totalRapBase + 0.001);
        },

        validateQtyOnChange(item) {
            let raw = String(item.qty || '0').replace(',', '.');
            let val = parseFloat(raw);
            if (isNaN(val) || val <= 0) {
                item.qty = 1;
            } else {
                item.qty = val;
            }
        },

        incrementQty(item) {
            let raw = String(item.qty || '0').replace(',', '.');
            let current = parseFloat(raw) || 0;
            let isDecimal = current % 1 !== 0;
            let step = isDecimal ? 0.1 : 1;
            item.qty = Math.round((current + step) * 10000) / 10000;
        },

        decrementQty(item) {
            let raw = String(item.qty || '0').replace(',', '.');
            let current = parseFloat(raw) || 0;
            let isDecimal = current % 1 !== 0;
            let step = isDecimal ? 0.1 : 1;
            let newVal = Math.round((current - step) * 10000) / 10000;
            item.qty = Math.max(newVal, 0.01);
        },

        removeFromCart(index) {
            this.cart.splice(index, 1);
        },

        parseQty(val) {
            let num = parseFloat(String(val || '0').replace(',', '.'));
            return isNaN(num) ? 0 : num;
        },

        submitForm() {
            if (!this.pembangunanUnitId) {
                Swal.fire('Peringatan', 'Silakan pilih Pembangunan Unit terlebih dahulu!', 'warning');
                return;
            }
            if (!this.qcId) {
                Swal.fire('Peringatan', 'Silakan pilih QC Pembangunan Unit terlebih dahulu!', 'warning');
                return;
            }

            if (this.cart.length === 0) {
                Swal.fire('Peringatan', 'Keranjang checkout order barang masih kosong!', 'warning');
                return;
            }

            if (this.jenisPembangunan !== 'servis') {
                let missingAlasan = false;
                let missingAlasanRap = false;
                this.cart.forEach((c) => {
                    if (!c.is_rap && !c.alasan.trim()) {
                        missingAlasan = true;
                    }
                    if (c.is_rap && this.isExceedingRap(c) && !c.alasan.trim()) {
                        missingAlasanRap = true;
                    }
                });

                if (missingAlasan) {
                    Swal.fire('Peringatan', 'Mohon isi alasan permintaan untuk barang diluar RAP!', 'warning');
                    return;
                }

                if (missingAlasanRap) {
                    Swal.fire('Peringatan', 'Mohon isi alasan permintaan untuk barang yang melebihi RAP!', 'warning');
                    return;
                }
            }

            this.showConfirmModal = true;
        },

        processSubmitOrder() {
            this.submitting = true;
            let targetUrl = "{{ route('produksi.pembangunanUnit.orderStore') }}";
            let payload = {
                pembangunan_unit_id: this.pembangunanUnitId,
                pembangunan_unit_qc_id: this.qcId,
                tanggal_order: this.tanggalSimpan,
                waktu_order: this.waktuLive,
                catatan: this.catatan,
                jenis_order: this.jenisOrderType,
                items: this.cart.map((c) => {
                    let st = c.satuans.find((s) => s.id == c.satuan_id);
                    return {
                        barang_id: c.barang_id,
                        nama_barang: c.nama_barang,
                        satuan_id: c.satuan_id,
                        satuan: st ? st.nama_satuan : c.satuan_nama,
                        jumlah_input: this.parseQty(c.qty),
                        faktor_konversi: st ? st.konversi_ke_base : 1,
                        pembangunan_unit_rap_bahan_id: c.rap_id,
                        alasan: c.alasan
                    };
                })
            };

            fetch(targetUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            })
            .then(async (res) => {
                let data = await res.json();
                this.submitting = false;
                this.showConfirmModal = false;
                if (res.ok) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message || 'Order barang berhasil diajukan ke Gudang',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = "{{ route('produksi.pembangunanUnit.orderIndex') }}";
                    });
                } else {
                    Swal.fire('Gagal', data.message || 'Terjadi kesalahan', 'error');
                }
            })
            .catch((err) => {
                this.submitting = false;
                this.showConfirmModal = false;
                Swal.fire('Error', 'Terjadi kesalahan server saat menyimpan order', 'error');
            });
        }
    };
}
</script>
@endsection
