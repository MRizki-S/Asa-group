@extends('layouts.app')

@section('pageActive', 'orderBarangKawasan')

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

<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-data="createOrderKawasanComponent()">

    <div x-data="{ pageName: 'Tambah Barang Keluar Kawasan' }">
        @include('partials.breadcrumb')
    </div>

    <!-- Top Card: Header & Form Info Pembangunan -->
    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 dark:border-gray-800 pb-3">
            <div>
                <h3 class="text-lg font-bold text-gray-800 dark:text-white/90">Tambah Barang Keluar Kawasan</h3>
                <p class="text-xs text-gray-500">Pilih pembangunan kawasan dan susun daftar barang ke dalam keranjang checkout order</p>
            </div>
            <a href="{{ route('produksi.pembangunanKawasan.orderIndex') }}"
                class="mt-2 sm:mt-0 inline-flex items-center gap-2 rounded-lg bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300 transition dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg> Kembali
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Tanggal & Waktu NBK (1 Kolom) -->
            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">
                    Tanggal & Waktu NBK <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-2 gap-2">
                    <!-- Input Tanggal -->
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-2.5 pointer-events-none">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                            </svg>
                        </div>
                        <input type="text"
                            name="tanggal_nota_display"
                            x-init="flatpickr($el, {
                                dateFormat: 'd-m-Y',
                                defaultDate: '{{ now()->format('d-m-Y') }}',
                                onChange: (selectedDates, dateStr, instance) => {
                                    tanggalNbkTampil = dateStr;
                                    tanggalNbkSimpan = instance.formatDate(selectedDates[0], 'Y-m-d');
                                }
                            })"
                            class="w-full rounded-xl border border-gray-300 bg-gray-50 p-3 pl-8 text-xs sm:text-sm text-gray-800 dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500 shadow-sm"
                            placeholder="Tanggal NBK">
                        <input type="hidden" name="tanggal_nota" x-model="tanggalNbkSimpan">
                    </div>

                    <!-- Live Clock Otomatis -->
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

            <!-- Pilih Pembangunan Kawasan -->
            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">Select Pembangunan Kawasan <span class="text-red-500">*</span></label>
                <select x-ref="kawasanSelect"
                    class="w-full rounded-xl border-gray-300 bg-gray-50 p-3 text-sm text-gray-800 dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Pilih Pembangunan Kawasan --</option>
                    @foreach($pembangunanKawasan as $pk)
                        <option value="{{ $pk->id }}">{{ $pk->label_formatted ?? ($pk->perumahan->nama_perumahaan . ' - ' . $pk->nama) }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Periode Kawasan Aktif -->
            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">Periode Kawasan Aktif</label>
                <input type="text" readonly :value="activePeriodeLabel || 'Otomatis mengikuti periode berjalan'"
                    :class="kawasanHasActivePeriode === false ? 'border-red-300 bg-red-50 text-red-600 dark:bg-red-950/20 dark:text-red-400' : 'border-gray-300 bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'"
                    class="w-full rounded-xl p-3 text-sm dark:border-gray-600 font-medium">
                <p x-show="pembangunanKawasanId && kawasanHasActivePeriode === false"
                    class="mt-1.5 text-xs text-red-500 font-medium flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    Kawasan ini tidak memiliki periode aktif. Tambah periode terlebih dahulu.
                </p>
            </div>

            <!-- Pengawas Kawasan -->
            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">Pengawas Kawasan</label>
                <input type="text" disabled :value="selectedKawasanInfo?.pengawas_nama || '-'"
                    class="w-full rounded-xl border border-gray-200 bg-gray-100 p-3 text-sm text-gray-700 font-semibold dark:bg-gray-800/80 dark:border-gray-700 dark:text-gray-300 outline-none cursor-not-allowed opacity-85 shadow-sm">
            </div>

            <!-- Subcon -->
            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">Subcon</label>
                <input type="text" disabled :value="selectedKawasanInfo?.subcon_nama || '-'"
                    class="w-full rounded-xl border border-gray-200 bg-gray-100 p-3 text-sm text-gray-700 font-semibold dark:bg-gray-800/80 dark:border-gray-700 dark:text-gray-300 outline-none cursor-not-allowed opacity-85 shadow-sm">
            </div>

            <!-- Catatan Permintaan -->
            <div class="md:col-span-3">
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">Catatan Permintaan (Opsional)</label>
                <textarea x-model="catatan" rows="2" placeholder="Masukkan catatan atau pengingat order barang jika ada..."
                    class="w-full rounded-xl border-gray-300 bg-gray-50 p-3 text-sm text-gray-800 dark:bg-gray-800 dark:border-gray-700 dark:text-white placeholder:text-gray-400 focus:border-blue-500 focus:ring-blue-500"></textarea>
            </div>
        </div>
    </div>

    <!-- Bottom Section: 2 Card Row (Left: List Semua Barang yang ada di Gudang, Right: Checkout Cart) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 pb-20 lg:pb-0">

        <!-- Card Left (7 Cols): List Semua Barang yang ada di Gudang -->
        <div class="lg:col-span-7 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03] flex flex-col h-[600px] lg:h-[680px]">

            <!-- Header Left Card & Button Toggle Stock/Direct -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 dark:border-gray-800 pb-3 mb-3 shrink-0">
                <h4 class="text-sm font-bold text-gray-800 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg> Katalog Barang Gudang
                </h4>

                <!-- 2 Tombol Tipe Order (Stock vs Direct) -->
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

            <!-- Search Bar Input Katalog Barang -->
            <div class="mb-4 shrink-0 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" x-model="searchQuery" placeholder="Cari nama atau kode barang di katalog..."
                    class="w-full text-xs pl-9 pr-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-gray-800 dark:bg-gray-800 dark:border-gray-700 dark:text-white placeholder:text-gray-400 focus:bg-white focus:border-blue-500 focus:ring-blue-500 transition-all">
            </div>

            <!-- List Barang Scrollable Container -->
            <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar space-y-5">
                <div class="pt-2">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider bg-gray-100 dark:bg-gray-700 px-2.5 py-1 rounded-md border border-gray-200 dark:border-gray-600 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                            Daftar Barang Stok Gudang
                        </span>
                        <div class="h-px bg-gray-200 dark:bg-gray-700 flex-1"></div>
                    </div>

                    <!-- List Semua Barang Gudang -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <template x-for="bg in filteredGudangItems()" :key="bg.id">
                            <div class="p-3.5 bg-gray-50/70 dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700/70 flex items-center justify-between gap-3 hover:border-gray-300 transition-all">
                                <div class="min-w-0 flex-1">
                                    <p class="text-[9px] font-mono text-gray-400 uppercase" x-text="bg.kode_barang"></p>
                                    <h5 class="text-xs font-bold text-gray-800 dark:text-gray-200 truncate" x-text="bg.nama_barang"></h5>
                                    <p class="text-[10px] text-gray-500 font-medium mt-0.5" x-text="'Stok: ' + formatNumber(bg.stok_gudang) + ' ' + bg.base_unit_nama"></p>
                                </div>
                                <button type="button" @click="addToCart(bg)" :disabled="isButtonAddDisabled()"
                                    class="px-3 py-1.5 bg-gray-800 hover:bg-gray-900 disabled:opacity-40 disabled:cursor-not-allowed text-white dark:bg-gray-700 dark:hover:bg-gray-600 text-xs font-bold rounded-lg shadow-sm transition shrink-0 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Tambah
                                </button>
                            </div>
                        </template>
                    </div>

                    <div x-show="filteredGudangItems().length === 0" class="text-center py-12">
                        <p class="text-xs text-gray-400 italic">Tidak ada barang katalog yang sesuai pencarian atau tipe order ini.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Right (5 Cols): Keranjang Checkout Order Barang (Desktop Only) -->
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
                    class="w-full text-xs pl-9 pr-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-gray-800 dark:bg-gray-800 dark:border-gray-700 dark:text-white placeholder:text-gray-400 focus:bg-white focus:border-blue-500 focus:ring-blue-500 transition-all">
            </div>

            <!-- Cart Items Container -->
            <div class="flex-1 overflow-y-auto pr-1 custom-scrollbar space-y-3">
                <template x-for="(item, idx) in cart" :key="item.barang_id">
                    <div
                        x-show="cartMatchesSearch(item)"
                        class="p-3.5 bg-gray-50/80 dark:bg-gray-800/60 rounded-xl border border-gray-200 dark:border-gray-700/80 shadow-sm space-y-2 relative">
                        <button type="button" @click="removeFromCart(idx)" class="absolute top-3 right-3 text-red-500 hover:text-red-700 text-xs font-bold p-1" title="Hapus Barang">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>

                        <div class="pr-6">
                            <div class="flex items-center justify-between gap-1.5">
                                <h5 class="text-xs font-bold text-gray-800 dark:text-gray-200 truncate" x-text="item.nama_barang"></h5>
                            </div>
                            <div class="flex flex-wrap items-center justify-between text-[10px] text-gray-400 mt-1 gap-1">
                                <span x-text="item.kode_barang"></span>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-emerald-600 dark:text-emerald-400" x-text="'Stok: ' + formatNumber(getConvertedStock(item)) + ' ' + (item.satuans.find(s=>s.id == item.satuan_id)?.nama_satuan || '')"></span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                            <!-- Input Jumlah dengan tombol - dan + -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Jumlah</label>
                                <div class="flex items-center rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 overflow-hidden">
                                    <!-- Tombol kurang -->
                                    <button type="button"
                                        @click="decrementQty(item)"
                                        class="px-2 py-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-300 transition-colors select-none shrink-0"
                                        title="Kurangi">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                                    </button>
                                    <!-- Input langsung -->
                                    <input
                                        type="text"
                                        inputmode="decimal"
                                        x-model="item.qty"
                                        @change="validateQtyOnChange(item)"
                                        placeholder="0"
                                        class="w-full text-xs font-bold text-center bg-transparent text-gray-800 dark:text-white border-0 focus:ring-0 focus:outline-none p-1"
                                    >
                                    <!-- Tombol tambah -->
                                    <button type="button"
                                        @click="incrementQty(item)"
                                        class="px-2 py-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-300 transition-colors select-none shrink-0"
                                        title="Tambah">
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
                    </div>
                </template>

                <div x-show="cart.length === 0" class="h-full flex flex-col items-center justify-center text-center p-8 text-gray-400">
                    <svg class="w-12 h-12 mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400">Keranjang Checkout Masih Kosong</p>
                    <p class="text-[11px] text-gray-400 mt-1">Silakan pilih barang dari katalog di sebelah kiri untuk ditambahkan ke keranjang.</p>
                </div>
            </div>

            <!-- Footer Submit & Reset (50:50) -->
            <div class="pt-3 border-t border-gray-100 dark:border-gray-800 mt-3 shrink-0 grid grid-cols-2 gap-2">
                <button type="button" @click="resetCart()" :disabled="cart.length === 0"
                    class="w-full py-3 px-4 bg-gray-200 hover:bg-gray-300 disabled:opacity-50 text-gray-700 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 text-xs font-bold rounded-xl transition-all shadow-sm flex items-center justify-center gap-1.5"
                    title="Bersihkan Keranjang">
                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Reset Keranjang</span>
                </button>

                <button type="button" @click="submitForm()"
                    :disabled="submitting || cart.length === 0 || kawasanHasActivePeriode === false"
                    class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-bold rounded-xl transition-all shadow-md flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span x-text="submitting ? 'Memproses...' : 'Simpan Order'"></span>
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
                :disabled="submitting || cart.length === 0 || kawasanHasActivePeriode === false"
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
                        class="w-full text-xs pl-9 pr-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-gray-800 dark:bg-gray-800 dark:border-gray-700 dark:text-white placeholder:text-gray-400 focus:bg-white focus:border-blue-500 focus:ring-blue-500 transition-all">
                </div>
            </div>

            <!-- Cart Items Container (Scrollable) -->
            <div class="flex-1 overflow-y-auto p-5 custom-scrollbar space-y-3">
                <template x-for="(item, idx) in cart" :key="'mob-' + item.barang_id">
                    <div
                        x-show="cartMatchesSearch(item)"
                        class="p-3.5 bg-gray-50/90 dark:bg-gray-800/80 rounded-2xl border border-gray-200 dark:border-gray-700/80 shadow-sm space-y-2.5 relative">
                        <button type="button" @click="removeFromCart(idx)" class="absolute top-3 right-3 text-red-500 hover:text-red-700 p-1 rounded-lg" title="Hapus Barang">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>

                        <div class="pr-7">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <h5 class="text-xs font-bold text-gray-800 dark:text-gray-200 truncate" x-text="item.nama_barang"></h5>
                            </div>
                            <div class="flex flex-wrap items-center justify-between text-[10px] text-gray-400 mt-1 gap-1">
                                <span class="font-mono" x-text="item.kode_barang"></span>
                                <div class="flex items-center gap-1.5">
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
                    :disabled="submitting || cart.length === 0 || kawasanHasActivePeriode === false"
                    class="w-full py-3 px-3 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-bold rounded-xl transition-all shadow-md flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span x-text="submitting ? 'Memproses...' : 'Simpan Order'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Ringkasan Order Barang -->
    <div x-show="showConfirmModal" class="fixed inset-0 z-[999999] overflow-y-auto" x-cloak>
        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-gray-900/60 transition-opacity" @click="showConfirmModal = false"></div>

            <!-- Modal Panel -->
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all dark:bg-gray-800 sm:my-8 sm:w-full sm:max-w-2xl border border-gray-100 dark:border-gray-700">
                <div class="bg-gray-50/50 p-5 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 bg-blue-100 text-blue-600 rounded-xl dark:bg-blue-900/50 dark:text-blue-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        </div>
                        <div>
                            <h4 class="text-base font-bold text-gray-800 dark:text-white">Konfirmasi Ringkasan Order Barang</h4>
                            <p class="text-xs text-gray-500">Tinjau kembali rincian barang yang akan diajukan ke sistem</p>
                        </div>
                    </div>
                    <button type="button" @click="showConfirmModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-5 max-h-[60vh] overflow-y-auto space-y-4">
                    <!-- Informative Badges & Type -->
                    <div class="flex flex-wrap items-center justify-between gap-2 p-3 bg-blue-50/50 rounded-xl border border-blue-100 dark:bg-blue-950/20 dark:border-blue-900/40 text-xs">
                        <div>
                            <span class="text-gray-500">Kawasan:</span>
                            <span class="font-bold text-gray-800 dark:text-white ml-1" x-text="selectedKawasanInfo?.label_formatted || '-'"></span>
                        </div>
                        <div>
                            <span class="text-gray-500">Tipe Order:</span>
                            <span class="font-bold uppercase ml-1" :class="jenisOrderType === 'stock' ? 'text-blue-600' : 'text-orange-600'" x-text="jenisOrderType === 'stock' ? 'Barang Stock' : 'Barang Direct'"></span>
                        </div>
                        <div>
                            <span class="text-gray-500">Total Item:</span>
                            <span class="font-bold text-gray-800 dark:text-white ml-1" x-text="formatNumber(cart.length) + ' Barang'"></span>
                        </div>
                    </div>

                    <!-- Table Ringkasan -->
                    <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-100 text-gray-700 uppercase font-bold dark:bg-gray-700 dark:text-gray-300">
                                <tr>
                                    <th class="p-3">No</th>
                                    <th class="p-3">Barang</th>
                                    <th class="p-3 text-center">Jumlah Order</th>
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
                        <span x-text="submitting ? 'Memproses...' : 'Ya, Simpan & Kirim Order'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Select2 JS --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
function createOrderKawasanComponent() {
    return {
        allPembangunanKawasan: {!! $pembangunanKawasan->toJson() !!},
        allBarangGudang: {!! $barangGudang->toJson() !!},
        preselectKawasanId: '{{ $preselectKawasanId ?? '' }}',

        tanggalNbkTampil: '{{ now()->format('d-m-Y') }}',
        tanggalNbkSimpan: '{{ now()->format('Y-m-d') }}',
        waktuLive: '{{ now()->format('H:i:s') }}',
        clockInterval: null,

        pembangunanKawasanId: '',
        activePeriodeLabel: '',
        kawasanHasActivePeriode: null,
        catatan: '',
        jenisOrderType: 'stock',
        cart: [],
        searchQuery: '',
        cartSearchQuery: '',
        submitting: false,
        showConfirmModal: false,
        showMobileCart: false,

        get selectedKawasanInfo() {
            if (!this.pembangunanKawasanId) return null;
            return this.allPembangunanKawasan.find(pk => String(pk.id) === String(this.pembangunanKawasanId)) || null;
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
            const preselectId = urlParams.get('pembangunan_kawasan_id') || this.preselectKawasanId;

            this.$nextTick(() => {
                let kawasanSelectEl = $(this.$refs.kawasanSelect);
                kawasanSelectEl.empty().append('<option value="">-- Pilih Pembangunan Kawasan --</option>');
                this.allPembangunanKawasan.forEach(pk => {
                    kawasanSelectEl.append(new Option(pk.label_formatted, pk.id, false, false));
                });

                kawasanSelectEl.select2({
                    theme: 'bootstrap4',
                    placeholder: '-- Pilih Pembangunan Kawasan --',
                    allowClear: true,
                    width: '100%'
                }).on('change', (e) => {
                    this.pembangunanKawasanId = e.target.value;
                    this.onKawasanChange();
                });

                if (preselectId) {
                    this.pembangunanKawasanId = preselectId;
                    kawasanSelectEl.val(preselectId).trigger('change.select2');
                    this.onKawasanChange();
                }
            });
        },

        onKawasanChange() {
            let found = this.selectedKawasanInfo;
            if (found && found.has_active_periode) {
                this.activePeriodeLabel = found.periode_label;
                this.kawasanHasActivePeriode = true;
            } else if (found) {
                this.activePeriodeLabel = 'Tidak ada periode aktif';
                this.kawasanHasActivePeriode = false;
            } else {
                this.activePeriodeLabel = '';
                this.kawasanHasActivePeriode = null;
            }
        },

        filteredGudangItems() {
            let q = this.searchQuery.toLowerCase().trim();
            return this.allBarangGudang.filter((item) => {
                let matchType = (this.jenisOrderType === 'stock') ? (item.is_stock === true) : (item.is_stock === false);
                if (!matchType) return false;
                if (!q) return true;
                return (item.nama_barang && item.nama_barang.toLowerCase().includes(q)) ||
                       (item.kode_barang && item.kode_barang.toLowerCase().includes(q));
            });
        },

        cartMatchesSearch(item) {
            let q = this.cartSearchQuery.toLowerCase().trim();
            if (!q) return true;
            return (item.nama_barang && item.nama_barang.toLowerCase().includes(q)) ||
                   (item.kode_barang && item.kode_barang.toLowerCase().includes(q));
        },

        async setJenisOrderType(type) {
            if (this.jenisOrderType === type) return;
            if (this.cart.length > 0) {
                let res = await Swal.fire({
                    title: 'Konfirmasi Tipe Order',
                    text: 'Mengubah tipe order akan mengosongkan keranjang checkout. Lanjutkan?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#2563EB',
                    cancelButtonColor: '#6B7280',
                    confirmButtonText: 'Ya, Ganti Tipe',
                    cancelButtonText: 'Batal'
                });
                if (!res.isConfirmed) return;
            }
            this.jenisOrderType = type;
            this.cart = [];
        },

        async resetCart() {
            if (this.cart.length === 0) return;
            let res = await Swal.fire({
                title: 'Konfirmasi Reset',
                text: 'Apakah Anda yakin ingin mengosongkan keranjang checkout?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Ya, Kosongkan',
                cancelButtonText: 'Batal'
            });
            if (res.isConfirmed) {
                this.cart = [];
            }
        },

        addToCart(barang) {
            let exists = this.cart.find((c) => c.barang_id == barang.id);
            if (exists) {
                this.incrementQty(exists);
                return;
            }

            let defaultSatuanId = barang.base_unit_id;
            let defaultSatuanNama = barang.base_unit_nama;
            let availableSatuans = barang.satuans || [];
            let stokBase = barang.stok_gudang || 0;

            this.cart.push({
                barang_id: barang.id,
                nama_barang: barang.nama_barang,
                kode_barang: barang.kode_barang,
                stok_gudang_base: stokBase,
                qty: 1,
                satuan_id: defaultSatuanId,
                satuan_nama: defaultSatuanNama,
                satuans: availableSatuans
            });
        },

        formatNumber(val) {
            let num = parseFloat(val);
            if (isNaN(num)) return '0';
            let parts = num.toString().split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            return parts.join(',');
        },

        getConvertedStock(item) {
            let st = (item.satuans || []).find((s) => s.id == item.satuan_id);
            let konversi = st ? (parseFloat(st.konversi_ke_base) || 1) : 1;
            let totalStok = parseFloat(item.stok_gudang_base || 0) / konversi;
            return Math.floor(totalStok * 1000) / 1000;
        },

        validateQtyOnChange(item) {
            let raw = String(item.qty || '').replace(',', '.');
            let val = parseFloat(raw);

            if (isNaN(val) || val <= 0) return;

            let maxStok = this.getConvertedStock(item);
            if (maxStok > 0 && val > maxStok) {
                item.qty = maxStok;
            } else {
                item.qty = val;
            }
        },

        incrementQty(item) {
            let raw = String(item.qty || '0').replace(',', '.');
            let current = parseFloat(raw) || 0;
            let isDecimal = current % 1 !== 0;
            let step = isDecimal ? 0.1 : 1;
            let newVal = Math.round((current + step) * 10000) / 10000;
            let maxStok = this.getConvertedStock(item);
            item.qty = (maxStok > 0) ? Math.min(newVal, maxStok) : newVal;
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

        isButtonAddDisabled() {
            return !this.pembangunanKawasanId || this.kawasanHasActivePeriode === false;
        },

        submitForm() {
            if (!this.pembangunanKawasanId) {
                Swal.fire('Peringatan', 'Silakan pilih Pembangunan Kawasan terlebih dahulu!', 'warning');
                return;
            }

            if (this.cart.length === 0) {
                Swal.fire('Peringatan', 'Keranjang checkout order barang masih kosong!', 'warning');
                return;
            }

            this.showConfirmModal = true;
        },

        parseQty(val) {
            let num = parseFloat(String(val || '0').replace(',', '.'));
            return isNaN(num) ? 0 : num;
        },

        processSubmitOrder() {
            this.submitting = true;
            let targetUrl = "{{ route('produksi.pembangunanKawasan.orderStore') }}";
            let payload = {
                pembangunan_kawasan_id: this.pembangunanKawasanId,
                tanggal_order: this.tanggalNbkSimpan,
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
                        jumlah_input: this.parseQty(c.qty)
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
                        text: data.message || 'Order barang kawasan berhasil diajukan',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = "{{ route('produksi.pembangunanKawasan.orderIndex') }}";
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
