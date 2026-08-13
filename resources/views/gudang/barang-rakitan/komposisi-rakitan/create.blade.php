@extends('layouts.app')

@section('pageActive', 'KomposisiRakitan')

@section('content')
{{-- select 2  --}}
<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">

<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

    <!-- Breadcrumb -->
    <div x-data="{ pageName: 'KomposisiRakitan' }">
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

    <form action="{{ route('gudang.komposisiRakitan.store') }}" method="POST"
        x-data="komposisiRakitanForm({{ Js::from($masterBarangs) }})">
        @csrf
        {{-- Barang Hasil --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
            <div class="px-5 py-4 sm:px-6 sm:py-5">
                <h3
                    class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 border-b border-gray-100 dark:border-gray-800">
                    Barang Hasil Rakitan
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="barang_hasil_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Barang Hasil <span class="text-red-500">*</span>
                        </label>
                        <select id="barang_hasil_id" name="barang_hasil_id" x-ref="barangHasilSelect" x-model="barangHasilId" required
                            class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-white">
                            <option value="">Pilih Barang Hasil</option>
                            <template x-for="barang in masterBarangs" :key="barang.id">
                                <option :value="barang.id" x-text="`${barang.kode_barang} - ${barang.nama_barang}`"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label for="satuan_hasil_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Satuan Hasil <span class="text-red-500">*</span>
                        </label>
                        <select id="satuan_hasil_id" name="satuan_hasil_id" x-ref="satuanHasilSelect" x-model="satuanHasilId" required
                            class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-white">
                            <option value="">Pilih Satuan</option>
                            <template x-for="satuan in satuanHasilList" :key="satuan.id">
                                <option :value="satuan.id" x-text="satuan.nama"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label for="qty_hasil" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Jumlah Hasil <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="qty_hasil" name="qty_hasil" step="any" min="0.0001"
                            x-model.number="qtyHasil" @input="hitungQtyHasilBase"
                            class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-white"
                            required>
                    </div>

                    <div>
                        <label for="qty_hasil_base" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Jumlah Base
                        </label>
                        <div class="flex">
                            <input type="text" id="qty_hasil_base_display"
                                x-model="qtyHasilBaseDisplay"
                                class="w-full rounded-l-lg bg-gray-100 border border-gray-300 text-gray-500 text-sm p-2.5 dark:bg-gray-700 dark:text-gray-400"
                                readonly>
                            <span class="inline-flex items-center rounded-r-lg border border-l-0 border-gray-300 bg-gray-100 px-3 text-sm text-gray-500 dark:bg-gray-700 dark:text-gray-400"
                                x-text="barangHasilBaseUnitName || '-'"></span>
                        </div>
                        <input type="hidden" id="qty_hasil_base" name="qty_hasil_base" x-model="qtyHasilBase">
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Status
                        </label>
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="status" value="active" x-model="isActive"
                                class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-600">
                            <span class="text-sm font-medium text-gray-800 dark:text-white">Active</span>
                        </label>
                    </div>

                    <div>
                        <label for="keterangan" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Keterangan
                        </label>
                        <textarea id="keterangan" name="keterangan" rows="2" x-model="keterangan"
                            class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-white"
                            placeholder="Keterangan singkat"></textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Komposisi Bahan --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
            <div class="px-5 py-4 sm:px-6 sm:py-5">
                <h3
                    class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 border-b border-gray-100 dark:border-gray-800">
                    Komposisi Bahan
                </h3>

                <div class="overflow-x-auto">
                    <table class="w-full border border-gray-300 dark:border-gray-700">
                        <thead class="bg-gray-100 dark:bg-gray-800">
                            <tr>
                                <th class="border border-gray-300 dark:border-gray-700 px-2 py-2 text-gray-900 dark:text-gray-100 w-[35%]">Barang Bahan</th>
                                <th class="border border-gray-300 dark:border-gray-700 px-2 py-2 text-gray-900 dark:text-gray-100">Satuan</th>
                                <th class="border border-gray-300 dark:border-gray-700 px-2 py-2 text-gray-900 dark:text-gray-100">Jumlah</th>
                                <th class="border border-gray-300 dark:border-gray-700 px-2 py-2 text-gray-900 dark:text-gray-100 w-24">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(item, index) in items" :key="item.key">
                                <tr>
                                    <td class="border border-gray-300 dark:border-gray-700 p-1">
                                        <select :name="`items[${index}][barang_bahan_id]`"
                                            x-init="$nextTick(() => initBarangBahanSelect($el, index))"
                                            class="w-full bg-gray-50 border border-gray-300 text-gray-900 rounded-lg p-2 dark:bg-gray-700 dark:text-white"
                                            required>
                                            <option value="">Pilih Barang</option>
                                            <template x-for="barang in masterBarangs" :key="barang.id">
                                                <option :value="barang.id" x-text="`${barang.kode_barang} - ${barang.nama_barang}`"></option>
                                            </template>
                                        </select>
                                    </td>

                                    <td class="border border-gray-300 dark:border-gray-700 p-1">
                                        <select :name="`items[${index}][satuan_id]`"
                                            x-init="$nextTick(() => initSatuanBahanSelect($el, index))"
                                            class="w-full bg-gray-50 border border-gray-300 text-gray-900 rounded-lg p-2 dark:bg-gray-700 dark:text-white"
                                            required>
                                            <option value="">Pilih Satuan</option>
                                            <template x-for="satuan in item.satuanList" :key="satuan.id">
                                                <option :value="satuan.id" x-text="satuan.nama"></option>
                                            </template>
                                        </select>
                                    </td>

                                    <td class="border border-gray-300 dark:border-gray-700 p-1">
                                        <input type="number" step="any" min="0.0001" :name="`items[${index}][qty]`"
                                            x-model.number="item.qty" @input="hitungItemBase(index)"
                                            class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2 dark:bg-gray-700 dark:text-white text-center"
                                            required>
                                    </td>

                                    <td class="border border-gray-300 dark:border-gray-700 p-1 text-center">
                                        <button type="button" @click="removeRow(index)" x-show="items.length > 1"
                                            class="px-3 py-1 text-xs text-white bg-red-600 rounded hover:bg-red-700">
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>

                    <button type="button" @click="addRow"
                        class="mt-3 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600">
                        + Tambah Baris
                    </button>
                </div>
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="flex justify-end gap-2">
            <a href="{{ route('gudang.komposisiRakitan.index') }}"
                class="px-8 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 dark:text-white dark:bg-gray-700 dark:hover:bg-gray-600">
                Kembali
            </a>
            <button type="submit"
                class="px-8 py-2.5 text-sm font-medium text-white rounded-lg bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800">
                Simpan
            </button>
        </div>
    </form>
</div>

<script>
    function komposisiRakitanForm(masterBarangs) {
        return {
            masterBarangs,
            satuanHasilList: [],
            barangHasilId: '',
            satuanHasilId: '',
            qtyHasil: '',
            qtyHasilBase: '',
            qtyHasilBaseDisplay: '',
            hasilKonversi: 1,
            barangHasilBaseUnitName: '',
            isActive: true,
            keterangan: '',
            items: [{
                key: Date.now(),
                barang_bahan_id: '',
                satuan_id: '',
                satuanList: [],
                qty: '',
                qty_base: '',
                konversi: 1,
                _barangSelectEl: null,
                _satuanSelectEl: null,
            }],

            init() {
                this.$nextTick(() => {
                    this.initBarangHasilSelect();
                    this.initSatuanHasilSelect();
                });
            },

            findBarang(id) {
                return this.masterBarangs.find(barang => String(barang.id) === String(id));
            },

            initBarangHasilSelect() {
                const selectEl = this.$refs.barangHasilSelect;
                if (!selectEl || $(selectEl).hasClass('select2-hidden-accessible')) return;

                $(selectEl).select2({
                    placeholder: 'Cari barang hasil...',
                    allowClear: true,
                    theme: 'bootstrap4',
                    width: '100%',
                }).on('change', (e) => {
                    this.applyBarangHasil(e.target.value);
                });
            },

            initSatuanHasilSelect() {
                const selectEl = this.$refs.satuanHasilSelect;
                if (!selectEl || $(selectEl).hasClass('select2-hidden-accessible')) return;

                $(selectEl).select2({
                    placeholder: 'Pilih satuan',
                    allowClear: true,
                    theme: 'bootstrap4',
                    width: '100%',
                }).on('change', (e) => {
                    this.satuanHasilId = e.target.value;
                    const satuan = this.satuanHasilList.find(item => String(item.id) === String(this.satuanHasilId));
                    this.hasilKonversi = Number(satuan?.konversi_ke_base || 1);
                    this.hitungQtyHasilBase();
                });
            },

            applyBarangHasil(barangId, selectedSatuanId = null) {
                this.barangHasilId = barangId;
                const barang = this.findBarang(this.barangHasilId);

                this.satuanHasilList = barang ? barang.satuans : [];
                this.satuanHasilId = '';
                this.hasilKonversi = 1;
                this.qtyHasilBase = '';
                this.qtyHasilBaseDisplay = '';
                this.barangHasilBaseUnitName = barang?.base_unit_name || '';

                this.$nextTick(() => {
                    const satuanTerpilih = selectedSatuanId
                        ? this.satuanHasilList.find(satuan => String(satuan.id) === String(selectedSatuanId))
                        : null;
                    const defaultSatuan = satuanTerpilih || this.satuanHasilList.find(satuan => satuan.is_default) || this.satuanHasilList[0];

                    if (defaultSatuan) {
                        this.satuanHasilId = defaultSatuan.id;
                        this.hasilKonversi = Number(defaultSatuan.konversi_ke_base || 1);
                        $(this.$refs.satuanHasilSelect).val(defaultSatuan.id).trigger('change.select2');
                    } else {
                        $(this.$refs.satuanHasilSelect).val('').trigger('change.select2');
                    }
                    this.hitungQtyHasilBase();
                });
            },

            initBarangBahanSelect(selectEl, index) {
                if (!selectEl || $(selectEl).hasClass('select2-hidden-accessible')) return;

                this.items[index]._barangSelectEl = selectEl;
                $(selectEl).select2({
                    placeholder: 'Cari barang bahan...',
                    allowClear: true,
                    theme: 'bootstrap4',
                    width: '100%',
                }).on('change', (e) => {
                    const item = this.items[index];
                    item.barang_bahan_id = e.target.value;
                    const barang = this.findBarang(item.barang_bahan_id);

                    item.satuanList = barang ? barang.satuans : [];
                    item.satuan_id = '';
                    item.qty_base = '';
                    item.konversi = 1;

                    this.$nextTick(() => {
                        const defaultSatuan = item.satuanList.find(satuan => satuan.is_default) || item.satuanList[0];
                        if (defaultSatuan && item._satuanSelectEl) {
                            item.satuan_id = defaultSatuan.id;
                            item.konversi = Number(defaultSatuan.konversi_ke_base || 1);
                            $(item._satuanSelectEl).val(defaultSatuan.id).trigger('change.select2');
                        } else if (item._satuanSelectEl) {
                            $(item._satuanSelectEl).val('').trigger('change.select2');
                        }
                        this.hitungItemBase(index);
                    });
                });
            },

            initSatuanBahanSelect(selectEl, index) {
                if (!selectEl || $(selectEl).hasClass('select2-hidden-accessible')) return;

                this.items[index]._satuanSelectEl = selectEl;
                $(selectEl).select2({
                    placeholder: 'Pilih satuan',
                    allowClear: true,
                    theme: 'bootstrap4',
                    width: '100%',
                }).on('change', (e) => {
                    const item = this.items[index];
                    item.satuan_id = e.target.value;
                    const satuan = item.satuanList.find(data => String(data.id) === String(item.satuan_id));
                    item.konversi = Number(satuan?.konversi_ke_base || 1);
                    this.hitungItemBase(index);
                });
            },

            hitungQtyHasilBase() {
                const qty = Number(this.qtyHasil || 0);
                const qtyBase = qty ? qty * this.hasilKonversi : 0;
                this.qtyHasilBase = qtyBase ? qtyBase.toFixed(3) : '';
                this.qtyHasilBaseDisplay = qtyBase ? this.formatQty(qtyBase) : '';
            },

            hitungItemBase(index) {
                const item = this.items[index];
                const qty = Number(item.qty || 0);
                item.qty_base = qty ? (qty * Number(item.konversi || 1)).toFixed(3) : '';
            },

            formatQty(value) {
                return Number(value || 0).toLocaleString('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 3,
                });
            },

            addRow() {
                this.items.push({
                    key: Date.now() + Math.random(),
                    barang_bahan_id: '',
                    satuan_id: '',
                    satuanList: [],
                    qty: '',
                    qty_base: '',
                    konversi: 1,
                    _barangSelectEl: null,
                    _satuanSelectEl: null,
                });
            },

            removeRow(index) {
                const item = this.items[index];
                if (item?._barangSelectEl && $(item._barangSelectEl).hasClass('select2-hidden-accessible')) {
                    $(item._barangSelectEl).select2('destroy');
                }
                if (item?._satuanSelectEl && $(item._satuanSelectEl).hasClass('select2-hidden-accessible')) {
                    $(item._satuanSelectEl).select2('destroy');
                }

                this.items.splice(index, 1);
            },
        }
    }
</script>

@endsection
