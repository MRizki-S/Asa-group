@extends('layouts.app')

@section('pageActive', 'DaftarNotaMasuk')

@section('content')
{{-- select 2  --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">

<style>
    .select2-results__option[aria-disabled=true] {
        background-color: #e5e7eb;
        color: #6b7280;
        cursor: not-allowed;
    }
</style>

<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-init="$dispatch('sidebar-minimize')">

    <!-- Breadcrumb -->
    <div x-data="{ pageName: 'DaftarNotaMasuk' }">
        @include('partials.breadcrumb')
    </div>

    <!-- Alert Error Validasi -->
    @if ($errors->any())
    <div class="flex p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
        <svg class="shrink-0 inline w-4 h-4 me-3 mt-[2px]" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
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

    <!-- Help Information -->
    <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800 p-4 rounded-xl shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <div class="p-1.5 bg-red-100 dark:bg-red-800 rounded-lg text-red-600 dark:text-red-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </div>
                <h4 class="text-sm font-bold text-red-800 dark:text-red-300">Hapus Draft</h4>
            </div>
            <p class="text-xs text-red-700 dark:text-red-400 leading-relaxed">
                Menghapus seluruh data nota draft ini secara permanen. Tindakan ini <strong>tidak dapat dibatalkan</strong> dan tidak mempengaruhi stok gudang.
            </p>
        </div>

        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-100 dark:border-yellow-800 p-4 rounded-xl shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <div class="p-1.5 bg-yellow-100 dark:bg-yellow-800 rounded-lg text-yellow-700 dark:text-yellow-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                </div>
                <h4 class="text-sm font-bold text-yellow-800 dark:text-yellow-300">Update Change</h4>
            </div>
            <p class="text-xs text-yellow-700 dark:text-yellow-400 leading-relaxed">
                Menyimpan perubahan terbaru (supplier, barang, harga) agar tetap tersimpan sebagai <strong>Draft</strong>. Belum ada perubahan stok.
            </p>
        </div>

        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 p-4 rounded-xl shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <div class="p-1.5 bg-blue-100 dark:bg-blue-800 rounded-lg text-blue-600 dark:text-blue-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                </div>
                <h4 class="text-sm font-bold text-blue-800 dark:text-blue-300">Posting ke Stok</h4>
            </div>
            <p class="text-xs text-blue-700 dark:text-blue-400 leading-relaxed">
                Memvalidasi dan memasukkan barang ke <strong>Stok Gudang HUB</strong>. Nota akan berpindah ke Daftar Final dan tidak bisa diedit lagi.
            </p>
        </div>
    </div>

    <form action="{{ route('gudang.draftNotaMasuk.update', $nota->nomor_nota) }}" method="POST" id="form-nota">
        @csrf
        @method('PATCH')

        {{-- Header Nota --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6 shadow-sm">
            <div class="px-5 py-4 sm:px-6 sm:py-5">
                <div class="flex justify-between items-center mb-6 border-b border-gray-100 dark:border-gray-800 pb-4">
                    <h3 class="text-base font-bold text-gray-800 dark:text-white/90">
                        Edit Draft Nota: {{ $nota->nomor_nota }}
                    </h3>
                    <span class="px-3 py-1 text-xs font-bold uppercase tracking-wider text-yellow-800 bg-yellow-100 rounded-full border border-yellow-200">
                        Draft Status
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label for="nomor_nota" class="block mb-2 text-sm font-semibold text-gray-900 dark:text-white">
                            Nomor Nota (Read Only)
                        </label>
                        <input type="text" id="nomor_nota" name="nomor_nota" readonly value="{{ $nota->nomor_nota }}"
                            class="w-full bg-gray-100 border border-gray-300 text-gray-500 text-sm rounded-lg p-2.5 dark:bg-gray-700/50 dark:text-gray-400 cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                            Tanggal Nota <span class="text-red-500">*</span>
                        </label>
                        <div class="relative" x-data="{ 
                            tampil: '{{ $nota->tanggal_nota->format('d-m-Y') }}', 
                            simpan: '{{ $nota->tanggal_nota->format('Y-m-d') }}' 
                        }">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                                </svg>
                            </div>
                            <input type="date" name="tanggal_nota_display" x-init="flatpickr($el, { 
                                dateFormat: 'd-m-Y', 
                                defaultDate: '{{ $nota->tanggal_nota->format('d-m-Y') }}',
                                onChange: (selectedDates, dateStr, instance) => { 
                                    tampil = dateStr;
                                    simpan = instance.formatDate(selectedDates[0], 'Y-m-d');
                                }
                            })"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 dark:bg-gray-700 dark:text-white">
                            <input type="hidden" name="tanggal_nota" x-model="simpan">
                        </div>
                    </div>

                    <div>
                        <label for="cara_bayar" class="block mb-2 text-sm font-semibold text-gray-900 dark:text-white">
                            Cara Bayar <span class="text-red-500">*</span>
                        </label>
                        <select id="cara_bayar" name="cara_bayar" required
                            class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="cash" {{ $nota->cara_bayar == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="hutang" {{ $nota->cara_bayar == 'hutang' ? 'selected' : '' }}>Hutang</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="supplier" class="block mb-2 text-sm font-semibold text-gray-900 dark:text-white">
                        Supplier <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="supplier" name="supplier" required value="{{ old('supplier', $nota->supplier) }}"
                        class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
            </div>
        </div>

        {{-- Detail Barang --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6 shadow-sm">
            <div class="px-5 py-4 sm:px-6 sm:py-5">
                <h3 class="text-base font-bold text-gray-800 dark:text-white/90 mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">
                    Detail Barang
                </h3>

                <div x-data="notaBarangMasuk({{ Js::from($masterBarangs) }}, {{ Js::from($existingItems) }})" class="overflow-x-auto">
                    <table class="w-full border-collapse border border-gray-300 min-w-[800px]">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="border border-gray-300 px-2 py-2 w-[30%] text-sm font-bold text-gray-700 dark:text-gray-200">Barang</th>
                                <th class="border border-gray-300 px-2 py-2 text-sm font-bold text-gray-700 dark:text-gray-200">Merk</th>
                                <th class="border border-gray-300 px-2 py-2 w-[15%] text-sm font-bold text-gray-700 dark:text-gray-200">Satuan</th>
                                <th class="border border-gray-300 px-2 py-2 text-sm font-bold text-gray-700 dark:text-gray-200 text-center">Jumlah</th>
                                <th class="border border-gray-300 px-2 py-2 text-sm font-bold text-gray-700 dark:text-gray-200 text-right">Harga Satuan</th>
                                <th class="border border-gray-300 px-2 py-2 text-sm font-bold text-gray-700 dark:text-gray-200 text-right">Total</th>
                                <th class="border border-gray-300 px-2 py-2 text-sm font-bold text-gray-700 dark:text-gray-200 text-center">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            <template x-for="(item, index) in items" :key="index">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/10 transition-colors">
                                    <!-- Barang -->
                                    <td class="border border-gray-300 p-1">
                                        <select x-init="$nextTick(() => initSelect2($el, index))"
                                            class="w-full bg-gray-50 dark:bg-gray-700 border-none text-sm"
                                            :name="`items[${index}][barang_id]`" required>
                                            <option value="">Pilih</option>
                                            <template x-for="barang in masterBarangs" :key="barang.id">
                                                <option :value="barang.id" :selected="barang.id == item.barang_id"
                                                    x-text="`${barang.kode_barang} - ${barang.nama_barang}`">
                                                </option>
                                            </template>
                                        </select>
                                    </td>

                                    <!-- Merk -->
                                    <td class="border border-gray-300 p-1">
                                        <input type="text" :name="`items[${index}][merk]`" x-model="item.merk"
                                            class="w-full border-none focus:ring-0 bg-transparent text-sm">
                                    </td>

                                    <!-- Satuan -->
                                    <td class="border border-gray-300 p-1">
                                        <select x-init="$nextTick(() => initSatuanSelect2($el, index))"
                                            class="w-full bg-gray-50 dark:bg-gray-700 border-none text-sm"
                                            :name="`items[${index}][satuan_id]`" required>
                                            <option value="">Pilih</option>
                                            <template x-for="sat in item.satuanList" :key="sat.id">
                                                <option :value="sat.id" :selected="sat.id == item.satuan_id" x-text="sat.nama"></option>
                                            </template>
                                        </select>
                                    </td>

                                    <!-- Jumlah -->
                                    <td class="border border-gray-300 p-1">
                                        <input type="number" step="0.001" :name="`items[${index}][jumlah_masuk]`"
                                            x-model.number="item.jumlah" @input="hitungTotal(index)"
                                            class="w-full text-center border-none focus:ring-0 bg-transparent text-sm font-bold" required>
                                    </td>

                                    <!-- Harga Satuan -->
                                    <td class="border border-gray-300 p-1">
                                        <input type="text" x-model="item.harga_satuan_display"
                                            @input="formatHargaSatuan(index)" 
                                            class="w-full text-right border-none focus:ring-0 bg-transparent text-sm" required>
                                        <input type="hidden" :name="`items[${index}][harga_satuan]`" :value="item.harga_satuan">
                                    </td>

                                    <!-- Harga Total -->
                                    <td class="border border-gray-300 p-1">
                                        <input type="text" x-model="item.harga_total_display"
                                            class="w-full text-right border-none focus:ring-0 bg-gray-100 dark:bg-gray-800/50 text-sm font-bold" readonly>
                                        <input type="hidden" :name="`items[${index}][harga_total]`" :value="item.harga_total">
                                    </td>

                                    <!-- Aksi -->
                                    <td class="border border-gray-300 p-1 text-center">
                                        <button type="button" @click="removeRow(index)" class="text-red-500 hover:text-red-700 p-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot class="bg-gray-50 dark:bg-gray-800/50">
                            <tr>
                                <td colspan="5" class="px-4 py-2 text-right text-sm font-bold">GRAND TOTAL</td>
                                <td class="px-4 py-2 text-right text-sm font-extrabold text-blue-600 dark:text-blue-400">
                                    Rp <span x-text="formatRupiah(items.reduce((sum, item) => sum + (parseFloat(item.harga_total) || 0), 0))"></span>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>

                    <button type="button" @click="addRow" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition shadow-sm font-medium text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Tambah Baris
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="flex flex-col md:flex-row justify-between items-center bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-lg gap-4">
            <div class="flex gap-2">
                <a href="{{ route('gudang.draftNotaMasuk.index') }}" 
                   class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-all dark:text-white dark:bg-gray-700 dark:hover:bg-gray-600 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </a>
                
                <button type="button" onclick="confirmDelete()"
                        class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Hapus Draft
                </button>
            </div>

            <div class="flex gap-3">
                <button type="submit"
                        class="px-8 py-2.5 text-sm font-bold text-yellow-900 rounded-lg bg-yellow-400 hover:bg-yellow-500 focus:ring-4 focus:ring-yellow-300 transition-all shadow-md active:scale-95">
                    Update Change
                </button>

                <button type="button" onclick="confirmPosting()"
                        class="px-8 py-2.5 text-sm font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 transition-all shadow-md active:scale-95 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    Posting ke Stok
                </button>
            </div>
        </div>
    </form>

    {{-- Hidden Form for secondary actions --}}
    <form id="form-delete" action="{{ route('gudang.draftNotaMasuk.destroy', $nota->nomor_nota) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>

<script>
    function notaBarangMasuk(masterBarangs, existingItems) {
        return {
            masterBarangs,
            items: [],

            init() {
                // Konfigurasi data awal dari controller
                if (existingItems && existingItems.length > 0) {
                    this.items = existingItems.map(item => ({
                        ...item,
                        _loading: false,
                        _selectEl: null,
                        _satuanSelectEl: null
                    }));
                } else {
                    this.addRow();
                }

                // Global refresh setelah semua Alpine terpasang
                this.$nextTick(() => {
                    this.refreshDisabledOptions();
                });
            },

            initSelect2(el, index) {
                if ($(el).hasClass('select2-hidden-accessible')) return;
                
                this.items[index]._selectEl = el;

                $(el).select2({ 
                    theme: 'bootstrap4', 
                    width: '100%',
                    placeholder: 'Pilih Barang'
                });

                // Set value awal jika ada
                if (this.items[index].barang_id) {
                    $(el).val(this.items[index].barang_id).trigger('change.select2');
                }

                $(el).on('change', async (e) => {
                    let val = e.target.value;
                    if (this.items[index].barang_id == val && this.items[index].satuanList.length > 0) return;
                    
                    this.items[index].barang_id = val;
                    this.resetRow(index);
                    this.refreshDisabledOptions();

                    if (val) {
                        try {
                            const response = await fetch(`/gudang/barang/${val}/satuan`);
                            const data = await response.json();
                            this.items[index].satuanList = data;

                            // OTOMATIS PILIH DEFAULT SATUAN
                            const defaultSatuan = data.find(s => s.is_default == 1);
                            if (defaultSatuan) {
                                this.items[index].satuan_id = defaultSatuan.id;
                                this.$nextTick(() => {
                                    if (this.items[index]._satuanSelectEl) {
                                        $(this.items[index]._satuanSelectEl).val(defaultSatuan.id).trigger('change.select2');
                                    }
                                });
                            }

                            this.$nextTick(() => {
                                if (this.items[index]._satuanSelectEl) {
                                    $(this.items[index]._satuanSelectEl).trigger('change.select2');
                                }
                                this.refreshDisabledOptions();
                            });
                        } catch (err) { console.error(err); }
                    }
                });

                // Refresh setiap kali ada baris baru terinisialisasi
                setTimeout(() => {
                    this.refreshDisabledOptions();
                }, 100);
            },

            refreshDisabledOptions() {
                // Ambil semua barang_id yang terpilih saat ini sebagai String
                let selectedIds = this.items
                    .map(item => item.barang_id ? item.barang_id.toString() : '')
                    .filter(id => id !== '');

                this.items.forEach((item) => {
                    if (!item._selectEl) return;

                    let select = item._selectEl;
                    let currentValue = item.barang_id ? item.barang_id.toString() : '';

                    Array.from(select.options).forEach(option => {
                        if (!option.value) return;
                        
                        let optVal = option.value.toString();
                        // Disable jika id ini ada di baris lain DAN bukan id miliknya sendiri
                        let isSelectedByOthers = selectedIds.includes(optVal) && optVal !== currentValue;
                        option.disabled = isSelectedByOthers;
                    });

                    // Update UI Select2
                    $(select).trigger('change.select2');
                });
            },

            initSatuanSelect2(el, index) {
                this.items[index]._satuanSelectEl = el;
                if ($(el).hasClass('select2-hidden-accessible')) return;
                
                $(el).select2({ 
                    theme: 'bootstrap4', 
                    width: '100%',
                    placeholder: 'Satuan'
                });

                // Set value awal jika ada
                if (this.items[index].satuan_id) {
                    $(el).val(this.items[index].satuan_id).trigger('change.select2');
                }

                $(el).on('change', (e) => { this.items[index].satuan_id = e.target.value; });
            },

            addRow() {
                this.items.push({
                    barang_id: '', merk: '', satuan_id: '', satuanList: [], jumlah: 1, 
                    harga_satuan: '', harga_satuan_display: '', harga_total: '', harga_total_display: '',
                    _selectEl: null, _satuanSelectEl: null
                });
                this.$nextTick(() => this.refreshDisabledOptions());
            },

            removeRow(index) {
                if (this.items.length > 1) {
                    let item = this.items[index];
                    if (item._selectEl) $(item._selectEl).select2('destroy');
                    if (item._satuanSelectEl) $(item._satuanSelectEl).select2('destroy');
                    
                    this.items.splice(index, 1);
                    this.$nextTick(() => this.refreshDisabledOptions());
                }
            },

            resetRow(index) {
                this.items[index].merk = '';
                this.items[index].jumlah = 1;
                this.items[index].harga_satuan = '';
                this.items[index].harga_satuan_display = '';
                this.items[index].harga_total = '';
                this.items[index].harga_total_display = '';
                this.items[index].satuan_id = '';
                this.items[index].satuanList = [];

                if (this.items[index]._satuanSelectEl) {
                    $(this.items[index]._satuanSelectEl).val('').trigger('change.select2');
                }
            },

            formatHargaSatuan(index) {
                let raw = this.items[index].harga_satuan_display.toString().replace(/\D/g, '');
                this.items[index].harga_satuan = raw;
                this.items[index].harga_satuan_display = this.formatRupiah(raw);
                this.hitungTotal(index);
            },

            hitungTotal(index) {
                let qty = parseFloat(this.items[index].jumlah) || 0;
                let harga = parseFloat(this.items[index].harga_satuan) || 0;
                let total = qty * harga;
                this.items[index].harga_total = total;
                this.items[index].harga_total_display = this.formatRupiah(Math.round(total));
            },

            formatRupiah(angka) {
                if (angka === '' || angka === null || angka === undefined) return '0';
                return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }
        }
    }

    function confirmDelete() {
        Swal.fire({
            title: 'Hapus Draft?',
            text: "Seluruh data draft ini akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) document.getElementById('form-delete').submit();
        });
    }

    function confirmPosting() {
        // Validasi form dasar sebelum konfirmasi
        const form = document.getElementById('form-nota');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        Swal.fire({
            title: 'Posting Nota?',
            text: "Setelah diposting, stok gudang HUB akan bertambah dan nota tidak bisa diedit lagi sebagai draft!",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Posting!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Ubah action form ke route submit/post
                form.action = "{{ route('gudang.draftNotaMasuk.submit', $nota->nomor_nota) }}";
                form.submit();
            }
        });
    }
</script>
@endsection