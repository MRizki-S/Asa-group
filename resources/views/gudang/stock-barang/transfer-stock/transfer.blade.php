@extends('layouts.app')

@section('pageActive', 'StokBarangGudang')

@section('content')
{{-- select 2  --}}
<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">

<style>
    .select2-results__option[aria-disabled=true] {
        background-color: #e5e7eb;
        /* gray-200 */
        color: #6b7280;
        /* gray-500 */
        cursor: not-allowed;
    }
</style>

<!-- ===== Main Content Start ===== -->
<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-data="transferStock({{ Js::from($masterBarangs) }}, {{ Js::from($ubsList) }})">

    <!-- Breadcrumb Start -->
    <div x-data="{ pageName: 'StokBarangGudang' }">
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

    <form action="{{ route('gudang.transferStockBarang.store') }}" method="POST" @submit="validateSubmit">
        @csrf

        {{-- hedaer transfer barang--}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
            <div class="px-5 py-4 sm:px-6 sm:py-5">
                <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-gray-800 pb-4">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        Transfer Stock Barang
                    </h3>
                </div>

                <div class="flex p-4 mb-6 text-sm text-blue-800 border border-blue-300 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400 dark:border-blue-800" role="alert">
                    <svg class="flex-shrink-0 inline w-4 h-4 me-3 mt-[2px]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                    </svg>
                    <span class="sr-only">Info</span>
                    <div>
                        <span class="font-bold">Informasi Penting:</span>
                        <ul class="mt-1.5 list-disc list-inside space-y-1">
                            <li>Transfer yang diajukan akan dikirim kepada SPV Logistik & Pengadaan untuk dilakukan persetujuan. Stok gudang baru akan berubah setelah pengajuan disetujui.</li>
                            <li>Pastikan seluruh data barang dan jumlah telah sesuai sebelum mengajukan transfer.</li>
                        </ul>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-white">
                            Dari Gudang UBS <span class="text-red-500">*</span>
                        </label>
                        <select name="dari_ubs_id" x-model="dariUbsId" required
                            @change="resetAllRows()"
                            class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                                   dark:bg-gray-800 dark:text-white
                                   @error('dari_ubs_id') border-red-500 @else border-gray-300 @enderror">
                            <option value="">Pilih Gudang Asal</option>
                            @foreach($ubsList as $ubs)
                                <option value="{{ $ubs->id }}" {{ old('dari_ubs_id') == $ubs->id ? 'selected' : '' }}>
                                    {{ $ubs->nama_ubs }} {{ $ubs->kode_ubs ? '('.$ubs->kode_ubs.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('dari_ubs_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-white">
                            Transfer ke Gudang UBS <span class="text-red-500">*</span>
                        </label>
                        <select name="ke_ubs_id" x-model="keUbsId" required
                            class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                                   dark:bg-gray-800 dark:text-white
                                   @error('ke_ubs_id') border-red-500 @else border-gray-300 @enderror">
                            <option value="">Pilih Gudang Tujuan</option>
                            @foreach($ubsList as $ubs)
                                <option value="{{ $ubs->id }}" 
                                    x-bind:disabled="dariUbsId == {{ $ubs->id }}"
                                    {{ old('ke_ubs_id') == $ubs->id ? 'selected' : '' }}>
                                    {{ $ubs->nama_ubs }} {{ $ubs->kode_ubs ? '('.$ubs->kode_ubs.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('ke_ubs_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Tanggal Transfer <span class="text-red-500">*</span>
                        </label>
                        <div class="relative" x-data="{ tampil: '{{ now()->format('d-m-Y') }}', simpan: '{{ now()->format('Y-m-d') }}' }">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                                </svg>
                            </div>
                            <input type="date" name="tanggal_transfer_display"
                                x-init="flatpickr($el, { 
                                    dateFormat: 'd-m-Y', 
                                    defaultDate: '{{ now()->format('d-m-Y') }}',
                                    onChange: (selectedDates, dateStr, instance) => {
                                        tampil = dateStr;
                                        simpan = instance.formatDate(selectedDates[0], 'Y-m-d');
                                    }
                                })"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 dark:bg-gray-700 dark:text-white">
                            <input type="hidden" name="tanggal_transfer" x-model="simpan">
                        </div>
                    </div>

                    <div>
                        <label for="tipe_transaksi" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Tipe Transaksi
                        </label>
                        <div class="relative">
                            <input type="text"
                                id="tipe_transaksi"
                                name="tipe_transaksi"
                                value="Transfer"
                                readonly
                                class="w-full bg-gray-100 border border-gray-300 text-gray-500 text-sm rounded-lg p-2.5 
                      cursor-not-allowed dark:bg-gray-700 dark:text-gray-400 dark:border-gray-600 focus:ring-0 focus:border-gray-300"
                                placeholder="Tipe Transaksi">

                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label for="keterangan" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Keterangan Transfer <span class="text-red-500">*</span>
                        </label>
                        <textarea id="keterangan" name="keterangan" rows="2" required
                            class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 @error('keterangan') border-red-500 @enderror" 
                            placeholder="Tulis alasan transfer stok di sini...">{{ old('keterangan') }}</textarea>
                        @error('keterangan')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
        
        {{-- pilih barang ditransfer --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
            <div class="px-5 py-4 sm:px-6 sm:py-5">
                <h3
                    class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 border-b border-gray-100 dark:border-gray-800">
                    Barang Ditransfer
                </h3>

                <div class="overflow-x-auto">

                    <table class="w-full border border-gray-300">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border px-2 py-2 w-[25%]">Barang</th>
                                <th class="border px-2 py-2">Satuan</th>
                                <th class="border px-2 py-2" x-text="dariUbsId ? 'Stock UBS (' + getUbsName(dariUbsId) + ')' : 'Stock UBS Asal'">Stock UBS Asal</th>
                                <th class="border px-2 py-2">Jumlah Transfer</th>
                                <th class="border px-2 py-2">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            <template x-for="(item, index) in items" :key="item.ui_id">
                                <tr x-init="$nextTick(() => initSelect2($refs.barangSelect, index))">

                                    <!-- Barang -->
                                    <td class="border p-1">
                                        <select x-ref="barangSelect"
                                            :disabled="!dariUbsId"
                                            class="select-barang w-full bg-gray-50 border border-gray-300 text-gray-900 rounded-lg p-2"
                                            :name="`items[${index}][barang_id]`" required>

                                            <option value="">Pilih Barang</option>

                                            <template x-for="barang in masterBarangs" :key="barang.id">
                                                <option :value="barang.id"
                                                    x-text="`${barang.kode_barang} - ${barang.nama_barang}`">
                                                </option>
                                            </template>
                                        </select>
                                    </td>

                                    <!-- Satuan -->
                                    <td class="border p-1">
                                        <select x-init="$nextTick(() => initSatuanSelect2($el, index))"
                                            :disabled="!dariUbsId"
                                            class="w-full bg-gray-50 border border-gray-300 text-gray-900 rounded-lg p-2 cursor-pointer"
                                            :name="`items[${index}][satuan_id]`" required>

                                            <option value="">Pilih Satuan</option>
                                            <template x-for="sat in item.satuanList" :key="sat.id">
                                                <option :value="sat.id" x-text="sat.nama"></option>
                                            </template>
                                        </select>
                                    </td>

                                    <!-- Stock UBS Asal -->
                                    <td class="border p-1 text-center">
                                        <input type="text"
                                            :value="item.stock_hub_saat_ini !== null ? item.stock_hub_saat_ini : '0'"
                                            class="w-full text-center bg-gray-100 border border-gray-300 text-gray-500 rounded p-1 cursor-not-allowed"
                                            readonly>
                                    </td>

                                    <!-- Jumlah Transfer -->
                                    <td class="border p-1 text-center">
                                        <input type="number" step="any" min="0.0001" :max="item.stock_hub_saat_ini" :name="`items[${index}][jumlah_masuk]`"
                                            x-model.number="item.jumlah"
                                            :disabled="!dariUbsId"
                                            class="w-full text-center border border-gray-300 rounded p-1" required>
                                    </td>

                                    <!-- Aksi -->
                                    <td class="border p-1 text-center">
                                        <template x-if="items.length > 1">
                                            <button type="button" @click="removeRow(index)"
                                                class="text-red-600 hover:underline">
                                                Hapus
                                            </button>
                                        </template>
                                        <template x-if="items.length <= 1">
                                            <span class="text-gray-400">-</span>
                                        </template>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>

                    <div class="mt-4 flex justify-between">
                        <button type="button" @click="addRow()"
                            :disabled="!dariUbsId"
                            :class="!dariUbsId ? 'bg-gray-300 cursor-not-allowed' : 'bg-gray-800 hover:bg-gray-700'"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-lg transition duration-200">
                            <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Tambah Baris
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-6">
            <button type="submit"
                class="inline-flex items-center justify-center px-5 py-3 text-sm font-medium text-center text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-blue-800 transition duration-200 active:scale-95">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                    </svg>
                    <span>Ajukan Transfer</span>
                </div>
            </button>
        </div>
    </form>

</div>
<!-- ===== Main Content End ===== -->

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    function transferStock(masterBarangs, ubsList) {
        return {
            masterBarangs,
            ubsList,
            dariUbsId: '',
            keUbsId: '',
            items: [{
                ui_id: Date.now().toString() + Math.random().toString(),
                barang_id: '',
                satuan_id: '',
                satuanList: [],
                stock_hub_saat_ini: null,
                jumlah: 0,
                _selectEl: null,
                _satuanSelectEl: null
            }],

            getUbsName(id) {
                let ubs = this.ubsList.find(u => u.id == id);
                return ubs ? ubs.nama_ubs : '';
            },

            resetAllRows() {
                this.items.forEach(item => {
                    if (item._selectEl && $(item._selectEl).hasClass('select2-hidden-accessible')) {
                        $(item._selectEl).select2('destroy');
                    }
                    if (item._satuanSelectEl && $(item._satuanSelectEl).hasClass('select2-hidden-accessible')) {
                        $(item._satuanSelectEl).select2('destroy');
                    }
                });
                this.items = [{
                    ui_id: Date.now().toString() + Math.random().toString(),
                    barang_id: '',
                    satuan_id: '',
                    satuanList: [],
                    stock_hub_saat_ini: null,
                    jumlah: 0,
                    _selectEl: null,
                    _satuanSelectEl: null
                }];
                this.$nextTick(() => {
                    this.refreshDisabledOptions();
                });
            },

            initSelect2(selectEl, index) {
                if (!selectEl) return;
                this.items[index]._selectEl = selectEl;
                if ($(selectEl).hasClass('select2-hidden-accessible')) return;

                $(selectEl).select2({
                    placeholder: 'Cari barang...',
                    allowClear: true,
                    theme: 'bootstrap4',
                    width: '100%'
                });

                $(selectEl).on('change', async (e) => {
                    this.items[index].barang_id = e.target.value;
                    this.refreshDisabledOptions();
                    this.resetRow(index);
                    
                    if (this.items[index]._satuanSelectEl) {
                        $(this.items[index]._satuanSelectEl).val('').trigger('change.select2');
                    }

                    if (e.target.value) {
                        try {
                            if (!this.dariUbsId) {
                                alert("Silakan pilih Gudang Asal terlebih dahulu.");
                                $(selectEl).val('').trigger('change.select2');
                                return;
                            }
                            const response = await fetch(`/gudang/transfer-stock-barang/satuan-dan-stok/${e.target.value}/${this.dariUbsId}`);
                            const data = await response.json();
                            this.items[index].satuanList = data;

                            const defaultSatuan = data.find(s => s.is_default == 1) || data[0];
                            if (defaultSatuan && this.items[index]._satuanSelectEl) {
                                this.items[index].satuan_id = defaultSatuan.id;
                                this.items[index].stock_hub_saat_ini = defaultSatuan.stock_hub_saat_ini;
                                setTimeout(() => {
                                    $(this.items[index]._satuanSelectEl).val(defaultSatuan.id).trigger('change.select2');
                                }, 100);
                            }
                        } catch (err) {
                            console.error("Gagal get satuan:", err);
                        }
                    }
                });
                
                this.refreshDisabledOptions();
            },

            initSatuanSelect2(selectEl, index) {
                if (!selectEl) return;
                this.items[index]._satuanSelectEl = selectEl;
                if ($(selectEl).hasClass('select2-hidden-accessible')) return;

                $(selectEl).select2({
                    placeholder: 'Pilih Satuan',
                    allowClear: true,
                    theme: 'bootstrap4',
                    width: '100%'
                });

                $(selectEl).on('change', (e) => {
                    this.items[index].satuan_id = e.target.value;
                    this.updateStockByUnit(index, e.target.value);
                });
            },

            updateStockByUnit(index, satuanId) {
                if(!satuanId) {
                    this.items[index].stock_hub_saat_ini = null;
                    return;
                }
                let selectedSat = this.items[index].satuanList.find(s => s.id == satuanId);
                if(selectedSat) {
                    this.items[index].stock_hub_saat_ini = selectedSat.stock_hub_saat_ini;
                }
            },

            refreshDisabledOptions() {
                let selectedIds = this.items.map(item => item.barang_id).filter(id => id);
                this.items.forEach((item) => {
                    if (!item._selectEl) return;
                    let select = item._selectEl;
                    let currentValue = item.barang_id;
                    Array.from(select.options).forEach(option => {
                        if (!option.value) return;
                        option.disabled = selectedIds.includes(option.value) && option.value !== currentValue;
                    });
                    $(select).trigger('change.select2');
                });
            },

            addRow() {
                this.items.push({
                    ui_id: Date.now().toString() + Math.random().toString(),
                    barang_id: '',
                    satuan_id: '',
                    satuanList: [],
                    stock_hub_saat_ini: null,
                    jumlah: 0,
                    _selectEl: null,
                    _satuanSelectEl: null
                });
            },

            removeRow(index) {
                let item = this.items[index];
                if (item && item._selectEl) {
                    let $el = $(item._selectEl);
                    if ($el.hasClass('select2-hidden-accessible')) $el.select2('destroy');
                }
                if (item && item._satuanSelectEl) {
                    let $satEl = $(item._satuanSelectEl);
                    if ($satEl.hasClass('select2-hidden-accessible')) $satEl.select2('destroy');
                }

                this.items.splice(index, 1);
                this.$nextTick(() => this.refreshDisabledOptions());
            },

            resetRow(index) {
                this.items[index].jumlah = 0;
                this.items[index].satuan_id = '';
                this.items[index].satuanList = [];
                this.items[index].stock_hub_saat_ini = null;
                if (this.items[index]._satuanSelectEl) {
                    $(this.items[index]._satuanSelectEl).val('').trigger('change.select2');
                }
            },

            validateSubmit(e) {
                if (!this.dariUbsId || !this.keUbsId) {
                    e.preventDefault();
                    alert("Gagal disubmit! Pastikan Gudang Asal dan Gudang Tujuan telah dipilih.");
                    return;
                }
                if (this.dariUbsId === this.keUbsId) {
                    e.preventDefault();
                    alert("Gagal disubmit! Gudang Asal dan Gudang Tujuan tidak boleh sama.");
                    return;
                }
                let invalidItems = this.items.filter(item => !item.jumlah || item.jumlah <= 0);
                if (invalidItems.length > 0) {
                    e.preventDefault();
                    alert("Gagal disubmit! Pastikan Jumlah Transfer pada setiap baris lebih dari 0.");
                }
            }
        }
    }
</script>

@endsection