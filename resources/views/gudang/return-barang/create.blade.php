@extends('layouts.app')

@php
    $category = request()->get('category', $category ?? 'pembangunan_unit');
    $pageActive = [
        'pembangunan_unit' => 'BarangReturnUnit',
        'pembangunan_kawasan' => 'BarangReturnKawasan',
        'pembangunan_proyek_mangoon' => 'BarangReturnProyek',
    ][$category] ?? 'BarangReturnUnit';

    $indexRouteName = [
        'pembangunan_unit' => 'gudang.returnBarang.unit.index',
        'pembangunan_kawasan' => 'gudang.returnBarang.kawasan.index',
        'pembangunan_proyek_mangoon' => 'gudang.returnBarang.proyek.index',
    ][$category] ?? 'gudang.returnBarang.unit.index';
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

<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-data="createReturnComponent()" x-init="initSelect2()">

    <div x-data="{ pageName: '{{ $titlePage }}' }">
        @include('partials.breadcrumb')
    </div>

    @if (session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({ icon: 'error', title: 'Gagal', text: @json(session('error')), confirmButtonColor: '#3b82f6' });
            });
        </script>
    @endif

    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: @json(session('success')), timer: 2500, showConfirmButton: false });
            });
        </script>
    @endif

    <!-- Top Card: Header & Form Seleksi Lokasi -->
    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 dark:border-gray-800 pb-3">
            <div>
                <h3 class="text-lg font-bold text-gray-800 dark:text-white/90">{{ $titlePage }}</h3>
                <p class="text-xs text-gray-500">Pilih lokasi pembangunan dan tambahkan barang ke keranjang retur</p>
            </div>
            <a href="{{ route($indexRouteName) }}"
                class="mt-2 sm:mt-0 inline-flex items-center gap-2 rounded-lg bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300 transition dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg> Kembali
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @if($category === 'pembangunan_unit')
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
                    <select x-ref="unitSelect" name="pembangunan_unit_id" required
                        class="w-full">
                        <option value="">-- Pilih Pembangunan Unit --</option>
                        @foreach($pembangunanUnits as $pu)
                            <option value="{{ $pu->id }}">{{ $pu->label_formatted }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">QC Pembangunan <span class="text-red-500">*</span></label>
                    <select x-ref="qcSelect" name="pembangunan_unit_qc_id" :disabled="!pembangunanUnitId || loadingSummary" required
                        class="w-full">
                        <option value="">-- Pilih QC --</option>
                        <template x-for="q in selectedQcList" :key="q.id">
                            <option :value="q.id" x-text="q.nama_qc"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">Tanggal Retur <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="tanggal_return" x-model="tanggalReturn" required
                        class="w-full rounded-xl border-gray-300 bg-gray-50 p-3 text-sm text-gray-800 dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500">
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
            @elseif($category === 'pembangunan_kawasan')
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">Select Pembangunan Kawasan <span class="text-red-500">*</span></label>
                    <select x-ref="kawasanSelect" name="pembangunan_kawasan_id" required
                        class="w-full">
                        <option value="">-- Pilih Pembangunan Kawasan --</option>
                        @foreach($pembangunanKawasan as $pk)
                            <option value="{{ $pk->id }}">{{ $pk->perumahan->nama_perumahaan ?? '' }} - {{ $pk->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">Periode Aktif Kawasan</label>
                    <input type="text" readonly :value="selectedKawasanPeriodeName" placeholder="Otomatis mendeteksi periode aktif..."
                        class="w-full rounded-xl border-gray-200 bg-gray-100 p-3 text-sm text-gray-600 font-semibold dark:bg-gray-800 dark:text-gray-300">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">Tanggal Retur <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="tanggal_return" x-model="tanggalReturn" required
                        class="w-full rounded-xl border-gray-300 bg-gray-50 p-3 text-sm text-gray-800 dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">Pengawas Kawasan</label>
                    <input type="text" disabled :value="selectedKawasanInfo?.pengawas_nama || '-'"
                        class="w-full rounded-xl border border-gray-200 bg-gray-100 p-3 text-sm text-gray-700 font-semibold dark:bg-gray-800/80 dark:border-gray-700 dark:text-gray-300 outline-none cursor-not-allowed opacity-85 shadow-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">Subcon</label>
                    <input type="text" disabled :value="selectedKawasanInfo?.subcon_nama || '-'"
                        class="w-full rounded-xl border border-gray-200 bg-gray-100 p-3 text-sm text-gray-700 font-semibold dark:bg-gray-800/80 dark:border-gray-700 dark:text-gray-300 outline-none cursor-not-allowed opacity-85 shadow-sm">
                </div>
            @elseif($category === 'pembangunan_proyek_mangoon' || $category === 'pembangunan_proyek')
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">Select Pembangunan Proyek <span class="text-red-500">*</span></label>
                    <select x-ref="proyekSelect" name="pembangunan_proyek_id" required
                        class="w-full">
                        <option value="">-- Pilih Pembangunan Proyek --</option>
                        @foreach($pembangunanProyek as $pp)
                            <option value="{{ $pp->id }}">{{ $pp->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">Tanggal Retur <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="tanggal_return" x-model="tanggalReturn" required
                        class="w-full rounded-xl border-gray-300 bg-gray-50 p-3 text-sm text-gray-800 dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">Pengawas Proyek</label>
                    <input type="text" disabled :value="selectedProyekInfo?.pengawas_nama || '-'"
                        class="w-full rounded-xl border border-gray-200 bg-gray-100 p-3 text-sm text-gray-700 font-semibold dark:bg-gray-800/80 dark:border-gray-700 dark:text-gray-300 outline-none cursor-not-allowed opacity-85 shadow-sm">
                </div>
            @endif

            <div class="md:col-span-3">
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">Catatan Pengajuan Retur (Opsional)</label>
                <textarea name="catatan" x-model="catatan" rows="2" placeholder="Masukkan catatan umum retur barang jika ada..."
                    class="w-full rounded-xl border-gray-300 bg-gray-50 p-3 text-sm text-gray-800 dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500"></textarea>
            </div>
        </div>
    </div>

    <!-- Layout 2 Kolom (Grid Card Katalog vs Keranjang Retur) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Kolom Kiri (7 Cols): Grid Cards Barang Diterima -->
        <div class="lg:col-span-7 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03] flex flex-col h-[680px]">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3 mb-3 shrink-0">
                <h4 class="text-sm font-bold text-gray-800 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    Katalog Barang Diterima
                </h4>
                <span class="text-xs font-bold text-gray-400" x-show="targetId" x-text="filteredAvailableBarang().length + ' Item Ditemukan'"></span>
            </div>

            <!-- Search Bar Katalog -->
            <div class="mb-4 shrink-0 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" x-model="searchQuery" placeholder="Cari nama atau kode barang..."
                    class="w-full text-xs pl-9 pr-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-gray-800 dark:bg-gray-800 dark:border-gray-700 dark:text-white placeholder:text-gray-400 focus:bg-white focus:border-blue-500 focus:ring-blue-500 transition-all">
            </div>

            <!-- Grid Card 2-Kolom (Persis Katalog Order Barang) -->
            <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar space-y-4">
                <div x-show="loadingSummary" class="py-16 text-center text-gray-400 text-xs">
                    Memuat data barang diterima pada lokasi ini...
                </div>

                <div x-show="!loadingSummary && !targetId" class="py-16 text-center text-gray-400 text-xs italic">
                    Silakan pilih lokasi pembangunan di atas terlebih dahulu.
                </div>

                <div x-show="!loadingSummary && targetId && filteredAvailableBarang().length === 0" class="py-16 text-center text-gray-400 text-xs italic">
                    Tidak ada barang diterima yang tersedia untuk diretur pada lokasi ini.
                </div>

                <div x-show="!loadingSummary && targetId && filteredAvailableBarang().length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <template x-for="b in filteredAvailableBarang()" :key="b.barang_id">
                        <div class="p-3.5 bg-gray-50/70 dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700/70 flex items-center justify-between gap-3 hover:border-blue-300 transition-all">
                            <div class="min-w-0 flex-1">
                                <p class="text-[9px] font-mono text-gray-400 uppercase" x-text="b.kode_barang"></p>
                                <h5 class="text-xs font-bold text-gray-800 dark:text-gray-200 truncate mt-0.5" x-text="b.nama_barang"></h5>
                                <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[10px] text-gray-500 font-medium mt-1">
                                    <span class="text-gray-600 dark:text-gray-300" x-text="'Diterima: ' + formatNumber(b.total_diterima_base) + ' ' + b.base_satuan_nama"></span>
                                    <span class="text-gray-300 dark:text-gray-600">•</span>
                                    <span class="text-amber-600 dark:text-amber-400 font-bold" x-text="'Diretur: ' + formatNumber(b.sudah_retur_base) + ' ' + b.base_satuan_nama"></span>
                                    <span class="text-gray-300 dark:text-gray-600">•</span>
                                    <span class="text-blue-600 dark:text-blue-400 font-bold" x-text="'Sisa: ' + formatNumber(b.sisa_retur_base) + ' ' + b.base_satuan_nama"></span>
                                </div>
                            </div>
                            <button type="button" @click="addToCart(b)" :disabled="isInCart(b.barang_id)"
                                class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed text-white text-xs font-bold rounded-lg shadow-sm transition shrink-0 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Tambah
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan (5 Cols): Keranjang Checkout Retur -->
        <div class="lg:col-span-5 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03] flex flex-col h-[680px]">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3 mb-3 shrink-0">
                <h4 class="text-sm font-bold text-gray-800 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                    Keranjang Retur
                </h4>
                <span class="text-xs font-black px-2.5 py-1 bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 rounded-full"
                    x-text="formatNumber(cart.length) + ' Barang'"></span>
            </div>

            <!-- Search Bar Keranjang -->
            <div class="mb-3 shrink-0 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" x-model="cartSearchQuery" placeholder="Cari nama atau kode di keranjang..."
                    class="w-full text-xs pl-9 pr-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-gray-800 dark:bg-gray-800 dark:border-gray-700 dark:text-white placeholder:text-gray-400 focus:bg-white focus:border-blue-500 focus:ring-blue-500 transition-all">
            </div>

            <!-- List Item di Keranjang -->
            <div class="flex-1 overflow-y-auto pr-1 custom-scrollbar space-y-3">
                <template x-for="(item, idx) in filteredCart()" :key="item.barang_id">
                    <div class="p-3.5 bg-gray-50/80 dark:bg-gray-800/60 rounded-xl border border-gray-200 dark:border-gray-700/80 shadow-sm space-y-2 relative">
                        <button type="button" @click="removeFromCart(idx)" class="absolute top-3 right-3 text-red-500 hover:text-red-700 text-xs font-bold p-1" title="Hapus Barang">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>

                        <div class="pr-6">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <h5 class="text-xs font-bold text-gray-800 dark:text-gray-200 truncate" x-text="item.nama_barang"></h5>
                            </div>
                            <div class="flex flex-wrap items-center justify-between text-[10px] text-gray-400 mt-1 gap-1">
                                <span class="font-mono" x-text="item.kode_barang"></span>
                                <span class="text-blue-600 dark:text-blue-400 font-bold"
                                    x-text="'Maks: ' + formatNumber(item.sisa_display) + ' ' + item.satuan_nama"></span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                            <!-- Input Jumlah Retur dengan tombol - dan + -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Jumlah Retur *</label>
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
                                        x-model="item.jumlah_input"
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
                                <select x-model="item.satuan_id" @change="onSatuanChange(item)"
                                    class="w-full text-xs p-2 rounded-lg border-gray-300 bg-white text-gray-800 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-blue-500">
                                    <template x-for="s in item.satuan_options" :key="s.satuan_id">
                                        <option :value="s.satuan_id" class="text-gray-800 dark:text-white" x-text="s.nama_satuan"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <div>
                            <input type="text" x-model="item.catatan" placeholder="Catatan item (Opsional)..."
                                class="w-full text-xs p-2 rounded-lg border-gray-200 bg-white text-gray-800 dark:bg-gray-700 dark:border-gray-600 dark:text-white placeholder:text-gray-400 focus:ring-blue-500">
                        </div>
                    </div>
                </template>

                <div x-show="cart.length > 0 && filteredCart().length === 0" class="py-10 text-center text-gray-400 text-xs italic">
                    Tidak ada barang yang cocok dengan pencarian.
                </div>

                <div x-show="cart.length === 0" class="h-full flex flex-col items-center justify-center text-center p-8 text-gray-400">
                    <svg class="w-12 h-12 mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400">Keranjang Retur Masih Kosong</p>
                    <p class="text-[11px] text-gray-400 mt-1">Silakan pilih barang dari katalog di sebelah kiri untuk ditambahkan ke keranjang.</p>
                </div>
            </div>

            <!-- Footer Submit Form -->
            <form x-ref="returnForm" action="{{ route('gudang.returnBarang.store') }}" method="POST" @submit="openConfirmModal(); $event.preventDefault();" class="pt-3 border-t border-gray-100 dark:border-gray-800 mt-3 shrink-0">
                @csrf
                <div class="hidden">
                    <input type="hidden" name="category" value="{{ $category }}">

                    @if($category === 'pembangunan_unit')
                        <input type="hidden" name="pembangunan_unit_id" :value="pembangunanUnitId">
                        <input type="hidden" name="pembangunan_unit_qc_id" :value="targetId">
                    @elseif($category === 'pembangunan_kawasan')
                        <input type="hidden" name="pembangunan_kawasan_id" :value="targetId">
                    @elseif($category === 'pembangunan_proyek_mangoon' || $category === 'pembangunan_proyek')
                        <input type="hidden" name="pembangunan_proyek_id" :value="targetId">
                    @endif

                    <input type="hidden" name="tanggal_return" :value="tanggalReturn">
                    <input type="hidden" name="catatan" :value="catatan">

                    <template x-for="(item, idx) in cart" :key="idx">
                        <div>
                            <input type="hidden" :name="'items['+idx+'][barang_id]'" :value="item.barang_id">
                            <input type="hidden" :name="'items['+idx+'][satuan_id]'" :value="item.satuan_id">
                            <input type="hidden" :name="'items['+idx+'][jumlah_input]'" :value="item.jumlah_input">
                            <input type="hidden" :name="'items['+idx+'][catatan]'" :value="item.catatan">
                        </div>
                    </template>
                </div>

                <template x-if="category === 'pembangunan_kawasan' && targetId && !hasActivePeriode">
                    <div class="mb-3 p-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs font-semibold">
                        ⚠️ Pembangunan kawasan ini tidak memiliki periode aktif (status proses). Pengajuan retur tidak dapat dilakukan.
                    </div>
                </template>

                <div class="grid grid-cols-2 gap-2">
                    <button type="button" @click="confirmResetCart()" :disabled="cart.length === 0"
                        class="w-full py-3 px-4 bg-gray-200 hover:bg-gray-300 disabled:opacity-50 text-gray-700 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 text-xs font-bold rounded-xl transition-all shadow-sm flex items-center justify-center gap-1.5"
                        title="Bersihkan Keranjang">
                        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span>Reset Keranjang</span>
                    </button>

                    <button type="button" @click="openConfirmModal()" :disabled="submitting || cart.length === 0 || !targetId || (category === 'pembangunan_kawasan' && !hasActivePeriode)"
                        class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-bold rounded-xl transition-all shadow-md flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span x-text="submitting ? 'Menyimpan...' : 'Simpan Retur'"></span>
                    </button>
                </div>
            </form>
        </div>
        <!-- Modal Konfirmasi Ringkasan Retur Barang -->
    <template x-teleport="body">
        <div x-show="showConfirmModal" x-cloak class="fixed inset-0 z-[999999] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <!-- Overlay -->
            <div x-show="showConfirmModal"
                x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="absolute inset-0 bg-gray-900/60"
                @click="showConfirmModal = false"></div>

            <!-- Modal Content -->
            <div x-show="showConfirmModal"
                x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                class="relative z-10 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col border border-gray-100 dark:border-gray-700">

                <!-- Header -->
                <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-100 text-blue-600 rounded-xl dark:bg-blue-900/50 dark:text-blue-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        </div>
                        <div>
                            <h4 class="text-base font-bold text-gray-800 dark:text-white">Konfirmasi Ringkasan Retur Barang</h4>
                            <p class="text-xs text-gray-500">Tinjau kembali rincian barang yang akan diajukan retur ke gudang</p>
                        </div>
                    </div>
                    <button type="button" @click="showConfirmModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Body -->
                <div class="p-5 overflow-y-auto flex-1 space-y-4">
                    <div class="flex items-center justify-between p-3 bg-blue-50/50 rounded-xl border border-blue-100 dark:bg-blue-950/20 dark:border-blue-900/40 text-xs">
                        <div>
                            <span class="text-gray-500">Total Item Retur:</span>
                            <span class="font-bold text-gray-800 dark:text-white ml-1" x-text="formatNumber(cart.length) + ' Barang'"></span>
                        </div>
                    </div>
                    <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-100 text-gray-700 uppercase font-bold dark:bg-gray-700 dark:text-gray-300">
                                <tr>
                                    <th class="p-3">No</th>
                                    <th class="p-3">Barang</th>
                                    <th class="p-3 text-center">Jumlah Retur</th>
                                    <th class="p-3">Catatan</th>
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
                                        <td class="p-3 text-center font-bold text-gray-800 dark:text-white whitespace-nowrap">
                                            <span x-text="formatNumber(c.jumlah_input) + ' ' + c.satuan_nama"></span>
                                        </td>
                                        <td class="p-3 text-gray-600 dark:text-gray-300 italic">
                                            <span x-text="c.catatan ? c.catatan : '-'"></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 p-4 dark:bg-gray-800/80 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-2 shrink-0">
                    <button type="button" @click="showConfirmModal = false"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-bold rounded-xl dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition">
                        Batal
                    </button>
                    <button type="button" @click="processSubmitReturn()" :disabled="submitting"
                        class="px-5 py-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span x-text="submitting ? 'Memproses...' : 'Ya, Simpan & Pengajuan Retur'"></span>
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>

{{-- jQuery & Select2 JS --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    function createReturnComponent() {
        return {
            category: '{{ $category }}',
            pembangunanUnits: @json($pembangunanUnits),
            pembangunanKawasan: @json($pembangunanKawasan),
            pembangunanProyek: @json($pembangunanProyek),
            
            pembangunanUnitId: '',
            targetId: '',
            selectedQcList: [],
            selectedKawasanPeriodeName: '',
            jenisPembangunan: 'pembangunan',
            get filteredPembangunanUnits() {
                if (this.jenisPembangunan === 'servis') {
                    return this.pembangunanUnits.filter(pu => pu.is_selesai);
                }
                return this.pembangunanUnits.filter(pu => !pu.is_selesai);
            },
            onJenisPembangunanChange() {
                this.pembangunanUnitId = '';
                this.targetId = '';
                this.selectedQcList = [];
                this.availableBarangList = [];
                this.cart = [];

                let unitSelectEl = $(this.$refs.unitSelect);
                let qcSelectEl = $(this.$refs.qcSelect);

                unitSelectEl.val('').empty().append('<option value="">-- Pilih Pembangunan Unit --</option>');
                this.filteredPembangunanUnits.forEach(pu => {
                    unitSelectEl.append(new Option(pu.label_formatted, pu.id, false, false));
                });
                unitSelectEl.trigger('change.select2');

                qcSelectEl.val('').empty().append('<option value="">-- Pilih QC --</option>').trigger('change.select2');
            },
            
            get selectedUnitInfo() {
                if (!this.pembangunanUnitId) return null;
                return this.pembangunanUnits.find(pu => pu.id == this.pembangunanUnitId) || null;
            },
            get selectedKawasanInfo() {
                if (!this.targetId) return null;
                return this.pembangunanKawasan.find(pk => pk.id == this.targetId) || null;
            },
            get selectedProyekInfo() {
                if (!this.targetId) return null;
                return this.pembangunanProyek.find(pp => pp.id == this.targetId) || null;
            },
            
            availableBarangList: [],
            searchQuery: '',
            tanggalReturn: new Date().toISOString().slice(0,16),
            catatan: '',
            loadingSummary: false,
            cart: [],
            cartSearchQuery: '',
            submitting: false,
            showConfirmModal: false,

            initSelect2() {
                this.$nextTick(() => {
                    const category = this.category;

                    if (category === 'pembangunan_unit') {
                        let unitSelectEl = $(this.$refs.unitSelect);
                        unitSelectEl.empty().append('<option value="">-- Pilih Pembangunan Unit --</option>');
                        this.filteredPembangunanUnits.forEach(pu => {
                            unitSelectEl.append(new Option(pu.label_formatted, pu.id, false, false));
                        });

                        unitSelectEl.select2({
                            theme: 'bootstrap4',
                            placeholder: '-- Pilih Pembangunan Unit --',
                            allowClear: true,
                            width: '100%'
                        }).on('change', (e) => {
                            this.pembangunanUnitId = e.target.value;
                            this.onUnitChange();
                        });

                        $(this.$refs.qcSelect).select2({
                            theme: 'bootstrap4',
                            placeholder: '-- Pilih QC --',
                            allowClear: true,
                            width: '100%'
                        }).on('change', (e) => {
                            this.targetId = e.target.value;
                            this.fetchSummary();
                        });
                    } else if (category === 'pembangunan_kawasan') {
                        $(this.$refs.kawasanSelect).select2({
                            theme: 'bootstrap4',
                            placeholder: '-- Pilih Pembangunan Kawasan --',
                            allowClear: true,
                            width: '100%'
                        }).on('change', (e) => {
                            this.targetId = e.target.value;
                            this.onKawasanChange();
                        });
                    } else if (category === 'pembangunan_proyek_mangoon' || category === 'pembangunan_proyek') {
                        $(this.$refs.proyekSelect).select2({
                            theme: 'bootstrap4',
                            placeholder: '-- Pilih Pembangunan Proyek --',
                            allowClear: true,
                            width: '100%'
                        }).on('change', (e) => {
                            this.targetId = e.target.value;
                            this.fetchSummary();
                        });
                    }
                });
            },

            formatNumber(num) {
                if (num === null || num === undefined) return 0;
                return parseFloat(num).toLocaleString('id-ID', { maximumFractionDigits: 3 });
            },

            onUnitChange() {
                this.targetId = '';
                this.availableBarangList = [];
                this.cart = [];
                const found = this.pembangunanUnits.find(u => u.id == this.pembangunanUnitId);
                const allQcs = found ? (found.pembangunan_unit_qc || []) : [];

                if (this.jenisPembangunan === 'servis') {
                    this.selectedQcList = allQcs.filter(q => Boolean(q.is_servis));
                } else {
                    this.selectedQcList = allQcs.filter(q => !q.is_servis);
                }

                this.$nextTick(() => {
                    const qcEl = $(this.$refs.qcSelect);
                    qcEl.empty().append('<option value="">-- Pilih QC --</option>');
                    this.selectedQcList.forEach(q => {
                        qcEl.append(new Option(q.nama_qc, q.id));
                    });
                    qcEl.val('').trigger('change.select2');
                });
            },

            onKawasanChange() {
                this.availableBarangList = [];
                this.cart = [];
                this.hasActivePeriode = false;
                const found = this.pembangunanKawasan.find(k => k.id == this.targetId);
                if (found && found.periodes) {
                    const activeP = found.periodes.find(p => p.status === 'proses');
                    if (activeP) {
                        this.hasActivePeriode = true;
                        this.selectedKawasanPeriodeName = 'Periode ' + (activeP.tanggal_mulai ? activeP.tanggal_mulai.substring(0,10) : '-') + ' s/d ' + (activeP.tanggal_selesai ? activeP.tanggal_selesai.substring(0,10) : '-');
                    } else {
                        this.hasActivePeriode = false;
                        this.selectedKawasanPeriodeName = 'Tidak ada periode aktif (Tidak dapat mengajukan retur)';
                    }
                } else {
                    this.hasActivePeriode = false;
                    this.selectedKawasanPeriodeName = '';
                }

                if (this.hasActivePeriode) {
                    this.fetchSummary();
                }
            },

            async fetchSummary() {
                if (!this.targetId) return;
                this.loadingSummary = true;
                this.cart = [];

                let url = '';
                if (this.category === 'pembangunan_unit') {
                    url = `/gudang/return-barang/qc-summary/${this.targetId}`;
                } else if (this.category === 'pembangunan_kawasan') {
                    url = `/gudang/return-barang/kawasan-summary/${this.targetId}`;
                } else if (this.category === 'pembangunan_proyek_mangoon' || this.category === 'pembangunan_proyek') {
                    url = `/gudang/return-barang/proyek-summary/${this.targetId}`;
                }

                try {
                    const res = await axios.get(url);
                    if (res.data && res.data.items) {
                        this.availableBarangList = res.data.items.filter(i => i.sisa_retur_base > 0);
                    }
                } catch (e) {
                    console.error(e);
                } finally {
                    this.loadingSummary = false;
                }
            },

            filteredAvailableBarang() {
                if (!this.searchQuery) return this.availableBarangList;
                const q = this.searchQuery.toLowerCase();
                return this.availableBarangList.filter(b =>
                    b.nama_barang.toLowerCase().includes(q) ||
                    (b.kode_barang && b.kode_barang.toLowerCase().includes(q))
                );
            },

            filteredCart() {
                if (!this.cartSearchQuery) return this.cart;
                const q = this.cartSearchQuery.toLowerCase();
                return this.cart.filter(item =>
                    item.nama_barang.toLowerCase().includes(q) ||
                    (item.kode_barang && item.kode_barang.toLowerCase().includes(q))
                );
            },

            isInCart(barangId) {
                return this.cart.some(c => c.barang_id == barangId);
            },

            addToCart(b) {
                if (this.isInCart(b.barang_id)) return;

                const baseOpt = b.satuan_options.find(s => s.is_base) || b.satuan_options[0];
                const konv = parseFloat(baseOpt ? baseOpt.konversi_ke_base : 1.0) || 1.0;
                const sisaDisplay = Math.round((b.sisa_retur_base / konv) * 1000) / 1000;
                const initialQty = Math.min(1, sisaDisplay);

                const item = {
                    barang_id: b.barang_id,
                    nama_barang: b.nama_barang,
                    kode_barang: b.kode_barang ?? '',
                    sisa_base: b.sisa_retur_base,
                    satuan_id: baseOpt ? baseOpt.satuan_id : b.base_satuan_id,
                    satuan_nama: baseOpt ? baseOpt.nama_satuan : b.base_satuan_nama,
                    sisa_display: sisaDisplay,
                    jumlah_input: initialQty,
                    catatan: '',
                    satuan_options: b.satuan_options
                };
                this.onSatuanChange(item);
                this.cart.push(item);
            },

            removeFromCart(idx) {
                this.cart.splice(idx, 1);
            },

            confirmResetCart() {
                if (this.cart.length === 0) return;
                Swal.fire({
                    title: 'Bersihkan Keranjang?',
                    text: 'Seluruh barang di keranjang retur akan dihapus.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Reset!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.cart = [];
                    }
                });
            },

            onSatuanChange(item) {
                const opt = item.satuan_options.find(s => s.satuan_id == item.satuan_id);
                if (opt) {
                    item.satuan_nama = opt.nama_satuan;
                    const konv = parseFloat(opt.konversi_ke_base) || 1.0;
                    item.sisa_display = Math.round((item.sisa_base / konv) * 1000) / 1000;
                    this.validateQtyOnChange(item);
                }
            },

            validateQtyOnChange(item) {
                let raw = String(item.jumlah_input || '').replace(',', '.');
                let val = parseFloat(raw);
                if (isNaN(val) || val <= 0) return;
                let maxRetur = parseFloat(item.sisa_display || 0);
                if (maxRetur > 0 && val > maxRetur) {
                    item.jumlah_input = maxRetur;
                } else {
                    item.jumlah_input = val;
                }
            },

            incrementQty(item) {
                let raw = String(item.jumlah_input || '0').replace(',', '.');
                let current = parseFloat(raw) || 0;
                let isDecimal = current % 1 !== 0;
                let step = isDecimal ? 0.1 : 1;
                let newVal = Math.round((current + step) * 10000) / 10000;
                let maxRetur = parseFloat(item.sisa_display || 0);
                item.jumlah_input = (maxRetur > 0) ? Math.min(newVal, maxRetur) : newVal;
            },

            decrementQty(item) {
                let raw = String(item.jumlah_input || '0').replace(',', '.');
                let current = parseFloat(raw) || 0;
                let isDecimal = current % 1 !== 0;
                let step = isDecimal ? 0.1 : 1;
                let newVal = Math.round((current - step) * 10000) / 10000;
                item.jumlah_input = Math.max(newVal, 0.01);
            },

            openConfirmModal() {
                if (this.cart.length === 0) {
                    Swal.fire('Peringatan', 'Keranjang retur masih kosong.', 'warning');
                    return;
                }
                if (!this.targetId) {
                    Swal.fire('Peringatan', 'Silakan pilih lokasi retur terlebih dahulu.', 'warning');
                    return;
                }
                if (this.category === 'pembangunan_kawasan' && !this.hasActivePeriode) {
                    Swal.fire('Peringatan', 'Pembangunan kawasan tidak memiliki periode aktif.', 'warning');
                    return;
                }
                
                // Validasi setiap item di keranjang
                for (let c of this.cart) {
                    let val = parseFloat(c.jumlah_input);
                    if (isNaN(val) || val <= 0) {
                        Swal.fire('Peringatan', `Jumlah retur untuk barang "${c.nama_barang}" tidak boleh kosong atau 0.`, 'warning');
                        return;
                    }
                    if (val > c.sisa_display) {
                        c.jumlah_input = c.sisa_display;
                        Swal.fire('Peringatan', `Jumlah retur "${c.nama_barang}" melebihi batas sisa maks (${c.sisa_display} ${c.satuan_nama}). Disesuaikan ke batas maksimum.`, 'warning');
                        return;
                    }
                }

                this.showConfirmModal = true;
            },

            processSubmitReturn() {
                this.submitting = true;
                this.$refs.returnForm.submit();
            }
        };
    }
</script>
@endsection
