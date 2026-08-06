@extends('layouts.app')

@php
    $category = request()->get('category', $category ?? 'pembangunan_unit');
    $pageActive = [
        'pembangunan_unit' => 'PermintaanBarangUnit',
        'pembangunan_kawasan' => 'PermintaanBarangKawasan',
        'pembangunan_proyek_mangoon' => 'PermintaanBarangProyek',
    ][$category] ?? 'PermintaanBarangUnit';
@endphp

@section('pageActive', $pageActive)

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

<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-data="createOrderUnitComponent()">

    <div x-data="{ pageName: 'Tambah Barang Keluar Unit' }">
        @include('partials.breadcrumb')
    </div>

    <!-- Top Card: Header & Form Info Pembangunan -->
    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 dark:border-gray-800 pb-3">
            <div>
                <h3 class="text-lg font-bold text-gray-800 dark:text-white/90">{{ $titlePage }}</h3>
                <p class="text-xs text-gray-500">Pilih pembangunan dan susun daftar barang ke dalam keranjang checkout order</p>
            </div>
            <a href="{{ route('gudang.permintaanBarang.index', ['jenis_order' => $category]) }}"
                class="mt-2 sm:mt-0 inline-flex items-center gap-2 rounded-lg bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300 transition dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg> Kembali
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @if($category === 'pembangunan_unit')
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">Select Pembangunan Unit <span class="text-red-500">*</span></label>
                    <select x-ref="pembangunanSelect"
                        class="w-full rounded-xl border-gray-300 bg-gray-50 p-3 text-sm text-gray-800 dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                        <option value="">-- Pilih Pembangunan Unit --</option>
                        @foreach($pembangunanUnits as $pu)
                            @php
                                $namaPerumahan = $pu->unit->tahap->perumahaan->nama_perumahaan ?? '';
                                $namaTahap = $pu->unit->tahap->nama_tahap ?? '';
                                $namaUnit = $pu->unit->nama_unit ?? '-';
                                $tglMulai = $pu->tanggal_mulai ? \Carbon\Carbon::parse($pu->tanggal_mulai)->format('d/m/Y') : '-';
                                $isSelesai = in_array($pu->status_pembangunan, ['selesai', 'selesai dengan catatan']);
                                $statusSuffix = $isSelesai ? ' (SELESAI)' : '';
                            @endphp
                            <option value="{{ $pu->id }}">{{ $namaPerumahan }} - {{ $namaTahap }} ({{ $namaUnit }}) - {{ $tglMulai }}{{ $statusSuffix }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">Select QC Pembangunan Unit <span class="text-red-500">*</span></label>
                    <select x-ref="qcSelect" x-model="qcId" :disabled="!pembangunanUnitId || loadingQc"
                        class="w-full rounded-xl border-gray-300 bg-gray-50 p-3 text-sm text-gray-800 dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50">
                        <option value="">-- Pilih QC --</option>
                        <template x-for="q in qcs" :key="q.id">
                            <option :value="q.id" x-text="q.nama + (q.is_servis ? ' (SERVIS)' : '')"></option>
                        </template>
                    </select>
                </div>
            @elseif($category === 'pembangunan_kawasan')
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">Select Pembangunan Kawasan <span class="text-red-500">*</span></label>
                    <select x-ref="kawasanSelect"
                        class="w-full rounded-xl border-gray-300 bg-gray-50 p-3 text-sm text-gray-800 dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                        <option value="">-- Pilih Pembangunan Kawasan --</option>
                        @foreach($pembangunanKawasan as $pk)
                            <option value="{{ $pk->id }}">{{ $pk->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">Periode Kawasan Aktif</label>
                    <input type="text" readonly :value="activePeriodeLabel || 'Otomatis mengikuti periode berjalan'"
                        :class="kawasanHasActivePeriode === false ? 'border-red-300 bg-red-50 text-red-600 dark:bg-red-950/20 dark:text-red-400' : 'border-gray-300 bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'"
                        class="w-full rounded-xl p-3 text-sm dark:border-gray-600">
                    <p x-show="pembangunanKawasanId && kawasanHasActivePeriode === false"
                        class="mt-1.5 text-xs text-red-500 font-medium flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        Kawasan ini tidak memiliki periode aktif. Tambah periode terlebih dahulu.
                    </p>
                </div>
            @elseif($category === 'pembangunan_proyek_mangoon')
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">Select Pembangunan Proyek <span class="text-red-500">*</span></label>
                    <select x-ref="proyekSelect"
                        class="w-full rounded-xl border-gray-300 bg-gray-50 p-3 text-sm text-gray-800 dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                        <option value="">-- Pilih Pembangunan Proyek --</option>
                        @foreach($pembangunanProyek as $pp)
                            <option value="{{ $pp->id }}">{{ $pp->nama }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">Catatan Permintaan (Opsional)</label>
                <textarea x-model="catatan" rows="2" placeholder="Masukkan catatan atau pengayut order barang jika ada..."
                    class="w-full rounded-xl border-gray-300 bg-gray-50 p-3 text-sm text-gray-800 dark:bg-gray-800 dark:border-gray-700 dark:text-white placeholder:text-gray-400 focus:border-blue-500 focus:ring-blue-500"></textarea>
            </div>
        </div>
    </div>

    <!-- Bottom Section: 2 Card Row (Left: List Barang Gudang & RAP, Right: Checkout Cart) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Card Left (7 Cols): List Semua Barang yang ada di Gudang / RAP -->
        <div class="lg:col-span-7 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03] flex flex-col h-[680px]">

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

                @if($category === 'pembangunan_unit')
                <!-- List Atas: Barang RAP (Jika QC Terpilih) - KHUSUS PEMBANGUNAN UNIT -->
                <div>
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <span class="text-xs font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider bg-blue-50 dark:bg-blue-900/40 px-2.5 py-1 rounded-md border border-blue-100 dark:border-blue-800 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg> 1. Barang Sesuai RAP QC
                        </span>

                        <!-- Tombol Tambah Semua Barang RAP -->
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
                                        <span x-text="'RAP: ' + formatNumber(rap.volume) + ' ' + rap.base_unit_nama"></span>
                                        <span class="text-gray-300 dark:text-gray-600">•</span>
                                        <span class="text-blue-600 dark:text-blue-400 font-bold" x-text="'Terorder: ' + formatNumber((rap.total_ordered_base || 0) / (rap.faktor_konversi || 1)) + ' ' + rap.base_unit_nama"></span>
                                        <span class="text-gray-300 dark:text-gray-600">•</span>
                                        <span class="font-bold" :class="((rap.volume - ((rap.total_ordered_base || 0) / (rap.faktor_konversi || 1))) > 0) ? 'text-indigo-600 dark:text-indigo-400' : 'text-amber-600 dark:text-amber-400'"
                                            x-text="'Sisa RAP: ' + formatNumber(Math.max(0, rap.volume - ((rap.total_ordered_base || 0) / (rap.faktor_konversi || 1)))) + ' ' + rap.base_unit_nama"></span>
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
                @endif

                <!-- Pemisah Garis Biasa antara Barang RAP dan Barang Luar RAP / Stok Gudang -->
                <div class="pt-2">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider bg-gray-100 dark:bg-gray-700 px-2.5 py-1 rounded-md border border-gray-200 dark:border-gray-600 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                            {{ $category === 'pembangunan_unit' ? '2. Barang Di Luar RAP (Stok Gudang)' : 'Daftar Barang Stok Gudang' }}
                        </span>
                        <div class="h-px bg-gray-200 dark:bg-gray-700 flex-1"></div>
                    </div>

                    <!-- List Bawah: Barang Luar RAP / Semua Barang Gudang -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <template x-for="bg in filteredGudangItems()" :key="bg.id">
                            <div class="p-3.5 bg-gray-50/70 dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700/70 flex items-center justify-between gap-3 hover:border-gray-300 transition-all">
                                <div class="min-w-0 flex-1">
                                    <p class="text-[9px] font-mono text-gray-400 uppercase" x-text="bg.kode_barang"></p>
                                    <h5 class="text-xs font-bold text-gray-800 dark:text-gray-200 truncate" x-text="bg.nama_barang"></h5>
                                    <p class="text-[10px] text-gray-500 font-medium mt-0.5" x-text="'Stok: ' + formatNumber(bg.stok_gudang) + ' ' + bg.base_unit_nama"></p>
                                </div>
                                <button type="button" @click="addToCart(bg, false, null)" :disabled="isButtonAddDisabled()"
                                    class="px-3 py-1.5 bg-gray-800 hover:bg-gray-900 disabled:opacity-40 disabled:cursor-not-allowed text-white dark:bg-gray-700 dark:hover:bg-gray-600 text-xs font-bold rounded-lg shadow-sm transition shrink-0 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Tambah
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

            </div>
        </div>

        <!-- Card Right (5 Cols): Keranjang Checkout Order Barang -->
        <div class="lg:col-span-5 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03] flex flex-col h-[680px]">

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
                                    @if($category === 'pembangunan_unit')
                                    <span x-show="item.is_rap" class="px-1.5 py-0.5 bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-300 text-[8px] font-black uppercase rounded">RAP</span>
                                    <span x-show="!item.is_rap" class="px-1.5 py-0.5 bg-amber-100 text-amber-700 dark:bg-amber-900/60 dark:text-amber-300 text-[8px] font-black uppercase rounded">Luar RAP</span>
                                    @endif
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

                        @if($category === 'pembangunan_unit')
                        <!-- Textarea Alasan jika barang diluar RAP ATAU akumulasi order melebihi RAP -->
                        <div x-show="!item.is_rap || isExceedingRap(item)" class="pt-1">
                            <label class="block text-[10px] font-bold text-red-500 uppercase mb-1">
                                <span x-show="!item.is_rap">Alasan Permintaan (Diluar RAP) <span class="text-red-500">*</span></span>
                                <span x-show="item.is_rap && isExceedingRap(item)">Alasan Permintaan (Melebihi RAP) <span class="text-red-500">*</span></span>
                            </label>
                            <input type="text" x-model="item.alasan" placeholder="Masukkan alasan permintaan..."
                                class="w-full text-xs p-2 rounded-lg border-red-200 bg-red-50/30 text-gray-800 dark:bg-gray-700 dark:border-red-800 dark:text-white placeholder:text-gray-400 focus:ring-red-500">
                        </div>
                        @endif
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
                    :disabled="submitting || cart.length === 0 || (category === 'pembangunan_kawasan' && kawasanHasActivePeriode === false)"
                    class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-bold rounded-xl transition-all shadow-md flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span x-text="submitting ? 'Memproses...' : 'Simpan Order'"></span>
                </button>
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
                                    @if($category === 'pembangunan_unit')
                                    <th class="p-3 text-center">Status Barang</th>
                                    @endif
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
                                        @if($category === 'pembangunan_unit')
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
                                        @endif
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
                        <span x-text="submitting ? 'Memproses...' : 'Ya, Simpan & Kirim Order'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    </div>
</div>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
function createOrderUnitComponent() {
    return {
        category: '{{ $category }}',
        pembangunanUnitId: '',
        pembangunanKawasanId: '',
        pembangunanProyekId: '',
        activePeriodeLabel: '',
        kawasanHasActivePeriode: null,
        qcId: '',
        catatan: '',
        jenisOrderType: 'stock',
        qcs: [],
        rapItems: [],
        cart: [],
        allBarangGudang: {!! $barangGudang->map(function($b) {
            $satuans = collect();
            if ($b->baseUnit) {
                $satuans->push([
                    'id' => $b->base_unit_id,
                    'nama_satuan' => $b->baseUnit->nama,
                    'konversi_ke_base' => 1
                ]);
            }
            if ($b->satuanKonversi) {
                foreach ($b->satuanKonversi as $sk) {
                    if ($sk->satuan) {
                        $satuans->push([
                            'id' => $sk->satuan_id,
                            'nama_satuan' => $sk->satuan->nama,
                            'konversi_ke_base' => (float)$sk->konversi_ke_base
                        ]);
                    }
                }
            }
            return [
                'id' => $b->id,
                'nama_barang' => $b->nama_barang,
                'kode_barang' => $b->kode_barang,
                'base_unit_id' => $b->base_unit_id,
                'base_unit_nama' => $b->baseUnit->nama ?? '',
                'stok_gudang' => (float) ($b->stok_gudang_aktif ?? 0),
                'satuans' => $satuans->unique('id')->values()->toArray(),
                'is_stock' => (bool) ($b->is_stock ?? true)
            ];
        })->toJson() !!},
        searchQuery: '',
        cartSearchQuery: '',
        loadingQc: false,
        submitting: false,
        showConfirmModal: false,

        init() {
            this.$nextTick(() => {
                if (this.category === 'pembangunan_unit') {
                    // Inisialisasi Select2 Pembangunan Unit
                    $(this.$refs.pembangunanSelect).select2({
                        theme: 'bootstrap4',
                        placeholder: '-- Pilih Pembangunan Unit --',
                        allowClear: true,
                        width: '100%'
                    }).on('change', (e) => {
                        this.pembangunanUnitId = e.target.value;
                        this.onPembangunanChange();
                    });

                    // Inisialisasi Select2 QC
                    $(this.$refs.qcSelect).select2({
                        theme: 'bootstrap4',
                        placeholder: '-- Pilih QC --',
                        allowClear: true,
                        width: '100%'
                    }).on('change', (e) => {
                        this.qcId = e.target.value;
                        this.onQcChange();
                    });

                } else if (this.category === 'pembangunan_kawasan') {
                    // Inisialisasi Select2 Pembangunan Kawasan
                    $(this.$refs.kawasanSelect).select2({
                        theme: 'bootstrap4',
                        placeholder: '-- Pilih Pembangunan Kawasan --',
                        allowClear: true,
                        width: '100%'
                    }).on('change', (e) => {
                        this.pembangunanKawasanId = e.target.value;
                        this.onKawasanChange();
                    });

                } else if (this.category === 'pembangunan_proyek_mangoon') {
                    // Inisialisasi Select2 Pembangunan Proyek
                    $(this.$refs.proyekSelect).select2({
                        theme: 'bootstrap4',
                        placeholder: '-- Pilih Pembangunan Proyek --',
                        allowClear: true,
                        width: '100%'
                    }).on('change', (e) => {
                        this.pembangunanProyekId = e.target.value;
                    });
                }
            });
        },

        async onPembangunanChange() {
            this.qcId = '';
            this.qcs = [];
            this.rapItems = [];
            this.cart = [];

            let qcSelectEl = $(this.$refs.qcSelect);
            qcSelectEl.val('').trigger('change.select2');

            if (!this.pembangunanUnitId) return;

            this.loadingQc = true;
            try {
                let res = await fetch(`{{ route('gudang.permintaanBarang.pembangunanUnit.qcList', ['pembangunanUnitId' => '___ID___']) }}`.replace('___ID___', this.pembangunanUnitId));
                if (res.ok) {
                    let data = await res.json();
                    this.qcs = data.qcs || [];

                    this.$nextTick(() => {
                        qcSelectEl.empty().append('<option value="">-- Pilih QC --</option>');
                        this.qcs.forEach(q => {
                            let label = q.nama + (q.is_servis ? ' (SERVIS)' : '');
                            qcSelectEl.append(new Option(label, q.id, false, false));
                        });
                        qcSelectEl.select2('destroy');
                        qcSelectEl.select2({
                            theme: 'bootstrap4',
                            placeholder: '-- Pilih QC --',
                            allowClear: true,
                            width: '100%'
                        }).on('change', (e) => {
                            this.qcId = e.target.value;
                            this.onQcChange();
                        });
                    });
                }
            } catch(e) {
                console.error(e);
            }
            this.loadingQc = false;
        },

        onQcChange() {
            this.rapItems = [];
            this.cart = [];
            if (!this.qcId) return;

            let selectedQc = this.qcs.find((q) => q.id == this.qcId);
            if (selectedQc) {
                this.rapItems = selectedQc.rap_bahan || [];
            }
        },

        filteredRapItems() {
            let q = this.searchQuery.toLowerCase().trim();
            return this.rapItems.filter((item) => {
                let matchType = (this.jenisOrderType === 'stock') ? (item.is_stock === true) : (item.is_stock === false);
                if (!matchType) return false;
                if (!q) return true;
                return (item.nama_barang && item.nama_barang.toLowerCase().includes(q)) ||
                       (item.kode_barang && item.kode_barang.toLowerCase().includes(q));
            });
        },

        filteredGudangItems() {
            let q = this.searchQuery.toLowerCase().trim();
            // Ambil daftar barang_id yang ada di RAP QC terpilih
            let rapBarangIds = this.rapItems.map((r) => String(r.barang_id));

            return this.allBarangGudang.filter((item) => {
                // Hindari duplikasi: jika barang sudah ada di RAP QC, hilangkan dari list luar RAP
                if (rapBarangIds.includes(String(item.id))) return false;

                let matchType = (this.jenisOrderType === 'stock') ? (item.is_stock === true) : (item.is_stock === false);
                if (!matchType) return false;
                if (!q) return true;
                return (item.nama_barang && item.nama_barang.toLowerCase().includes(q)) ||
                       (item.kode_barang && item.kode_barang.toLowerCase().includes(q));
            });
        },

        filteredCartItems() {
            let q = this.cartSearchQuery.toLowerCase().trim();
            if (!q) return this.cart;
            return this.cart.filter((item) => {
                return (item.nama_barang && item.nama_barang.toLowerCase().includes(q)) ||
                       (item.kode_barang && item.kode_barang.toLowerCase().includes(q));
            });
        },

        // Helper untuk x-show filter di cart items (agar index asli terjaga)
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

        addAllRapToCart() {
            let rapList = this.filteredRapItems();
            if (rapList.length === 0) return;

            rapList.forEach((rap) => {
                let exists = this.cart.find((c) => c.is_rap && c.rap_id == rap.id);
                if (!exists) {
                    let defaultSatuanId = rap.base_unit_id;
                    let defaultSatuanNama = rap.base_unit_nama;
                    let availableSatuans = rap.satuans || [];

                    this.cart.push({
                        barang_id: rap.barang_id,
                        nama_barang: rap.nama_barang,
                        kode_barang: rap.kode_barang,
                        is_rap: true,
                        rap_id: rap.id,
                        volume_rap: rap.volume,
                        faktor_konversi_rap: rap.faktor_konversi || 1,
                        total_ordered_base: rap.total_ordered_base || 0,
                        stok_gudang_base: rap.stok_gudang || 0,
                        qty: rap.volume > 0 ? rap.volume : 1,
                        satuan_id: defaultSatuanId,
                        satuan_nama: defaultSatuanNama,
                        satuans: availableSatuans,
                        alasan: ''
                    });
                }
            });
        },

        addToCart(barang, isRap = false, rapObj = null) {
            let exists = this.cart.find((c) => c.barang_id == (rapObj ? rapObj.barang_id : barang.id) && c.is_rap == isRap && c.rap_id == (rapObj ? rapObj.id : null));
            if (exists) {
                exists.qty += 1;
                return;
            }

            let defaultSatuanId = rapObj ? rapObj.base_unit_id : barang.base_unit_id;
            let defaultSatuanNama = rapObj ? rapObj.base_unit_nama : barang.base_unit_nama;
            let availableSatuans = rapObj ? (rapObj.satuans || []) : (barang.satuans || []);
            let stokBase = rapObj ? (rapObj.stok_gudang || 0) : (barang.stok_gudang || 0);

            this.cart.push({
                barang_id: rapObj ? rapObj.barang_id : barang.id,
                nama_barang: rapObj ? rapObj.nama_barang : barang.nama_barang,
                kode_barang: rapObj ? rapObj.kode_barang : barang.kode_barang,
                is_rap: isRap,
                rap_id: rapObj ? rapObj.id : null,
                volume_rap: rapObj ? rapObj.volume : null,
                faktor_konversi_rap: rapObj ? (rapObj.faktor_konversi || 1) : 1,
                total_ordered_base: rapObj ? (rapObj.total_ordered_base || 0) : 0,
                stok_gudang_base: stokBase,
                qty: rapObj ? (rapObj.volume > 0 ? rapObj.volume : 1) : 1,
                satuan_id: defaultSatuanId,
                satuan_nama: defaultSatuanNama,
                satuans: availableSatuans,
                alasan: ''
            });
        },

        // Helper format number dengan titik setiap 3 digit (format Indonesia)
        formatNumber(val) {
            let num = parseFloat(val);
            if (isNaN(num)) return '0';
            let parts = num.toString().split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            return parts.join(',');
        },

        // Helper untuk menghitung stok gudang terkonversi sesuai satuan terpilih
        getConvertedStock(item) {
            let st = (item.satuans || []).find((s) => s.id == item.satuan_id);
            let konversi = st ? (parseFloat(st.konversi_ke_base) || 1) : 1;
            let totalStok = parseFloat(item.stok_gudang_base || 0) / konversi;
            return Math.floor(totalStok * 1000) / 1000;
        },

        // Helper untuk menghitung jumlah terorder terkonversi sesuai satuan terpilih
        getConvertedOrdered(item) {
            if (!item.is_rap) return 0;
            let st = (item.satuans || []).find((s) => s.id == item.satuan_id);
            let konversi = st ? (parseFloat(st.konversi_ke_base) || 1) : 1;
            let totalOrdered = parseFloat(item.total_ordered_base || 0) / konversi;
            return Math.round(totalOrdered * 1000) / 1000;
        },

        // Helper untuk menghitung sisa RAP terkonversi sesuai satuan terpilih
        getConvertedRemainingRap(item) {
            if (!item.is_rap || !item.volume_rap) return 0;
            let st = (item.satuans || []).find((s) => s.id == item.satuan_id);
            let konversi = st ? (parseFloat(st.konversi_ke_base) || 1) : 1;

            let totalOrderedBase = parseFloat(item.total_ordered_base || 0);
            let rapTotalBase = parseFloat(item.volume_rap || 0) * (parseFloat(item.faktor_konversi_rap) || 1);

            let sisaBase = Math.max(0, rapTotalBase - totalOrderedBase);
            let sisaTerkonversi = sisaBase / konversi;
            return Math.round(sisaTerkonversi * 1000) / 1000;
        },

        // Helper untuk mengecek apakah akumulasi barang tersedia & input melebihi RAP
        isExceedingRap(item) {
            if (!item.is_rap || !item.volume_rap) return false;
            let st = (item.satuans || []).find((s) => s.id == item.satuan_id);
            let konversi = st ? (parseFloat(st.konversi_ke_base) || 1) : 1;
            let inputBase = (parseFloat(item.qty) || 0) * konversi;

            let totalOrderedBase = parseFloat(item.total_ordered_base || 0);
            let rapTotalBase = parseFloat(item.volume_rap || 0) * (parseFloat(item.faktor_konversi_rap) || 1);

            return (totalOrderedBase + inputBase) > (rapTotalBase + 0.0001);
        },

        // Handler validasi max input stok — hanya cap ke stok max, tidak reset paksa ke 0.01
        // Dipanggil @change (saat user selesai mengetik dan keluar dari field)
        validateQtyOnChange(item) {
            // Normalisasi koma ke titik (untuk input gaya Indonesia seperti "0,5")
            let raw = String(item.qty || '').replace(',', '.');
            let val = parseFloat(raw);

            // Kalau tidak valid atau negatif — biarkan saja, jangan paksa reset
            // (user mungkin sedang menghapus untuk ganti angka)
            if (isNaN(val) || val <= 0) return;

            // Cap ke stok maksimum jika melebihi
            let maxStok = this.getConvertedStock(item);
            if (maxStok > 0 && val > maxStok) {
                item.qty = maxStok;
            } else {
                // Simpan kembali sebagai angka bersih (dot sebagai desimal)
                item.qty = val;
            }
        },

        // Increment qty: +1 untuk bulat, +0.1 untuk desimal
        incrementQty(item) {
            let raw = String(item.qty || '0').replace(',', '.');
            let current = parseFloat(raw) || 0;
            // Deteksi apakah desimal
            let isDecimal = current % 1 !== 0;
            let step = isDecimal ? 0.1 : 1;
            let newVal = Math.round((current + step) * 10000) / 10000;
            let maxStok = this.getConvertedStock(item);
            item.qty = (maxStok > 0) ? Math.min(newVal, maxStok) : newVal;
        },

        // Decrement qty: -1 untuk bulat, -0.1 untuk desimal, min 0.01
        decrementQty(item) {
            let raw = String(item.qty || '0').replace(',', '.');
            let current = parseFloat(raw) || 0;
            let isDecimal = current % 1 !== 0;
            let step = isDecimal ? 0.1 : 1;
            let newVal = Math.round((current - step) * 10000) / 10000;
            item.qty = Math.max(newVal, 0.01);
        },

        removeFromCart(index) {
            // index adalah index asli di this.cart (bukan dari filtered)
            this.cart.splice(index, 1);
        },

        isButtonAddDisabled() {
            if (this.category === 'pembangunan_unit') {
                return !this.pembangunanUnitId || !this.qcId;
            } else if (this.category === 'pembangunan_kawasan') {
                return !this.pembangunanKawasanId || this.kawasanHasActivePeriode === false;
            } else if (this.category === 'pembangunan_proyek_mangoon') {
                return !this.pembangunanProyekId;
            }
            return false;
        },

        onKawasanChange() {
            let kawasanList = {!! $pembangunanKawasan->toJson() !!};
            let found = kawasanList.find(k => k.id == this.pembangunanKawasanId);
            if (found && found.periodes && found.periodes.length > 0) {
                let p = found.periodes[0];
                let tglMulai = p.tanggal_mulai ? new Date(p.tanggal_mulai).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';
                let tglSelesai = p.tanggal_selesai ? new Date(p.tanggal_selesai).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';
                this.activePeriodeLabel = tglMulai + ' s/d ' + tglSelesai;
                this.kawasanHasActivePeriode = true;
            } else {
                this.activePeriodeLabel = 'Tidak ada periode aktif';
                this.kawasanHasActivePeriode = false;
            }
        },

        submitForm() {
            if (this.category === 'pembangunan_unit') {
                if (!this.pembangunanUnitId) {
                    Swal.fire('Peringatan', 'Silakan pilih Pembangunan Unit terlebih dahulu!', 'warning');
                    return;
                }
                if (!this.qcId) {
                    Swal.fire('Peringatan', 'Silakan pilih QC Pembangunan Unit terlebih dahulu!', 'warning');
                    return;
                }
            } else if (this.category === 'pembangunan_kawasan') {
                if (!this.pembangunanKawasanId) {
                    Swal.fire('Peringatan', 'Silakan pilih Pembangunan Kawasan terlebih dahulu!', 'warning');
                    return;
                }
            } else if (this.category === 'pembangunan_proyek_mangoon') {
                if (!this.pembangunanProyekId) {
                    Swal.fire('Peringatan', 'Silakan pilih Pembangunan Proyek terlebih dahulu!', 'warning');
                    return;
                }
            }

            if (this.cart.length === 0) {
                Swal.fire('Peringatan', 'Keranjang checkout order barang masih kosong!', 'warning');
                return;
            }

            if (this.category === 'pembangunan_unit') {
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

            // Buka Modal Konfirmasi Ringkasan Order
            this.showConfirmModal = true;
        },

        // Helper parse qty dari string/number (handle koma desimal gaya Indonesia)
        parseQty(val) {
            let num = parseFloat(String(val || '0').replace(',', '.'));
            return isNaN(num) ? 0 : num;
        },

        processSubmitOrder() {
            this.submitting = true;
            let targetUrl = '';
            let payload = {};

            if (this.category === 'pembangunan_unit') {
                targetUrl = "{{ route('produksi.pembangunanUnit.orderStore') }}";
                payload = {
                    pembangunan_unit_id: this.pembangunanUnitId,
                    pembangunan_unit_qc_id: this.qcId,
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
            } else if (this.category === 'pembangunan_kawasan') {
                targetUrl = "{{ route('produksi.pembangunanKawasan.orderStore') }}";
                payload = {
                    pembangunan_kawasan_id: this.pembangunanKawasanId,
                    catatan: this.catatan,
                    jenis_order: this.jenisOrderType,
                    barang: this.cart.map((c) => {
                        return {
                            id: c.barang_id,
                            satuan_id: c.satuan_id,
                            jumlah_input: this.parseQty(c.qty)
                        };
                    })
                };
            } else if (this.category === 'pembangunan_proyek_mangoon') {
                targetUrl = "{{ route('produksi.pembangunanProyek.orderStore') }}";
                payload = {
                    pembangunan_proyek_id: this.pembangunanProyekId,
                    catatan: this.catatan,
                    jenis_order: this.jenisOrderType,
                    barang: this.cart.map((c) => {
                        return {
                            id: c.barang_id,
                            satuan_id: c.satuan_id,
                            jumlah_input: this.parseQty(c.qty)
                        };
                    })
                };
            }

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
                        text: data.message || 'Order barang berhasil diajukan',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = "{{ route('gudang.permintaanBarang.index') }}?jenis_order=" + this.category;
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
