@extends('layouts.app')

@section('pageActive', 'TambahNotaMasuk')

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

<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-init="$dispatch('sidebar-minimize')">

    <!-- Breadcrumb -->
    <div x-data="{ pageName: 'TambahNotaMasuk' }">  
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

    <form action="{{ route('gudang.notaBarangMasuk.store') }}" method="POST">
        @csrf

        {{-- Bikin Nota Barang --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
            <div class="px-5 py-4 sm:px-6 sm:py-5">
                <h3
                    class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 border-b border-gray-100 dark:border-gray-800">
                    Buat Nota
                </h3>

                <div class="flex p-4 mb-6 text-sm text-blue-800 border border-blue-300 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400 dark:border-blue-800" role="alert">
                    <svg class="flex-shrink-0 inline w-4 h-4 me-3 mt-[2px]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                    </svg>
                    <span class="sr-only">Info</span>
                    <div>
                        <span class="font-bold">Informasi Penting:</span>
                        <ul class="mt-1.5 list-disc list-inside">
                            <li>Seluruh nota baru akan tersimpan otomatis sebagai <span class="font-semibold italic">Draft</span>.</li>
                            <li>Stok di <span class="font-semibold">Gudang HUB</span> tidak akan bertambah selama status nota masih <span class="font-semibold text-red-600 dark:text-red-400">Draft</span> (belum di-Submit/Post pada halaman daftar nota draft).</li>
                        </ul>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label for="nomor_nota" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Nomor Nota <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="nomor_nota" name="nomor_nota" readonly value="{{ $newNomorNota }}"
                            class="w-full bg-gray-100 border text-gray-500 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-gray-400 border-gray-300 cursor-not-allowed">
                        @error('nomor_nota')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Tanggal Nota <span class="text-red-500">*</span>
                        </label>
                        <div class="relative" x-data="{ tampil: '{{ now()->format('d-m-Y') }}', simpan: '{{ now()->format('Y-m-d') }}' }">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                                </svg>
                            </div>
                            <input type="date" name="tanggal_nota_display"
                                x-init="flatpickr($el, {
                                    dateFormat: 'd-m-Y',
                                    defaultDate: '{{ now()->format('d-m-Y') }}',
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
                        <label for="status" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Status
                        </label>
                        <input type="text" id="status" name="status" readonly value="Draft"
                            class="w-full bg-yellow-50 border border-yellow-300 text-yellow-800 text-sm font-bold rounded-lg p-2.5
               dark:bg-yellow-900/30 dark:border-yellow-600 dark:text-yellow-400 cursor-not-allowed">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="supplier" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Supplier <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="supplier" name="supplier" required value="{{ old('supplier') }}" placeholder="Contoh: PT. Semen Indonesia"
                            class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5 @error('supplier') border-red-500 @else border-gray-300 @enderror">
                        @error('supplier')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="cara_bayar" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Cara Bayar <span class="text-red-500">*</span>
                        </label>
                        <select id="cara_bayar" name="cara_bayar" required
                            class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5 @error('cara_bayar') border-red-500 @else border-gray-300 @enderror">
                            <option value="">Pilih cara bayar</option>
                            <option value="cash" {{ old('cara_bayar') == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="hutang" {{ old('cara_bayar') == 'hutang' ? 'selected' : '' }}>Hutang</option>
                        </select>
                        @error('cara_bayar')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Iput Barang Masuk --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
            <div class="px-5 py-4 sm:px-6 sm:py-5">
                <h3
                    class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 border-b border-gray-100 dark:border-gray-800">
                    Input Barang Masuk
                </h3>

                <div x-data="notaBarangMasuk({{ Js::from($masterBarangs) }})" class="overflow-x-auto">

                    <table class="w-full border border-gray-300">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border px-2 py-2 w-[25%]">Barang</th>
                                <th class="border px-2 py-2">Merk</th>
                                <th class="border px-2 py-2">Satuan</th>
                                <th class="border px-2 py-2">Jumlah Masuk </th>
                                <th class="border px-2 py-2">Harga Satuan</th>
                                <th class="border px-2 py-2">Harga Total</th>
                                <th class="border px-2 py-2">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            <template x-for="(item, index) in items" :key="index">
                                <tr x-init="$nextTick(() => initSelect2($refs.barangSelect, index))">

                                    <!-- Barang -->
                                    <td class="border p-1">
                                        <select x-ref="barangSelect"
                                            class="select-barang w-full bg-gray-50 border border-gray-300 text-gray-900 rounded-lg p-2"
                                            :name="`items[${index}][barang_id]`" required>

                                            <option value="">Pilih</option>

                                            <template x-for="barang in masterBarangs" :key="barang.id">
                                                <option :value="barang.id"
                                                    x-text="`${barang.kode_barang} - ${barang.nama_barang}`">
                                                </option>
                                            </template>
                                        </select>
                                    </td>

                                    <!-- Merk -->
                                    <td class="border p-1">
                                        <input type="text" :name="`items[${index}][merk]`" x-model="item.merk"
                                            required class="w-full border rounded p-1">
                                    </td>

                                    <!-- Satuan -->
                                    <td class="border p-1">
                                        <select x-init="$nextTick(() => initSatuanSelect2($el, index))"
                                            class="w-full bg-gray-50 border border-gray-300 text-gray-900 rounded-lg p-2"
                                            :name="`items[${index}][satuan_id]`" required>

                                            <option value="">Pilih</option>
                                            <template x-for="sat in item.satuanList" :key="sat.id">
                                                <option :value="sat.id" x-text="sat.nama"></option>
                                            </template>
                                        </select>
                                    </td>

                                    <!-- Jumlah Masuk -->
                                    <td class="border p-1 text-center">
                                        <input type="number" min="1" :name="`items[${index}][jumlah_masuk]`"
                                            x-model.number="item.jumlah" @input="hitungTotal(index)"
                                            class="w-20 text-center border rounded p-1" required>
                                    </td>

                                    <!-- Harga Satuan -->
                                    <td class="border p-1">
                                        <input type="text" x-model="item.harga_satuan_display"
                                            @input="formatHargaSatuan(index)" class="w-full border rounded p-1"
                                            required>
                                        <input type="hidden" :name="`items[${index}][harga_satuan]`"
                                            :value="item.harga_satuan" required>
                                    </td>

                                    <!-- Harga Total -->
                                    <td class="border p-1">
                                        <input type="text" x-model="item.harga_total_display"
                                            class="w-full border rounded p-1" readonly required>
                                        <input type="hidden" :name="`items[${index}][harga_total]`"
                                            :value="item.harga_total">
                                    </td>

                                    <!-- Aksi -->
                                    <td class="border p-1 text-center">
                                        <template x-if="index === items.length - 1">
                                            <button type="button" @click="removeRow(index)"
                                                class="text-red-600 hover:underline">
                                                Hapus
                                            </button>
                                        </template>
                                    </td>

                                </tr>
                            </template>
                        </tbody>

                    </table>

                    <!-- Tombol tambah -->
                    <button type="button" @click="addRow" class="mt-3 px-4 py-2 bg-blue-600 text-white rounded">
                        + Tambah Baris
                    </button>

                </div>

            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="flex justify-end gap-2">
            <button type="submit"
                class="px-8 py-2.5 text-sm font-bold text-yellow-900 rounded-lg bg-yellow-400 hover:bg-yellow-500
               focus:outline-none focus:ring-4 focus:ring-yellow-300 transition-colors duration-200">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z" />
                    </svg>
                    Simpan sebagai Draft
                </div>
            </button>
        </div>
    </form>
</div>

<script>
    function notaBarangMasuk(masterBarangs) {
        return {
            masterBarangs,

            items: [{
                barang_id: '',
                merk: '',
                satuan_id: '',
                satuanList: [],
                jumlah: 1,
                harga_satuan: '',
                harga_satuan_display: '',
                harga_total: '',
                harga_total_display: '',
                _selectEl: null,
                _satuanSelectEl: null
            }],

            /* =======================
             * INIT SELECT2
             * ======================= */
            initSelect2(selectEl, index) {
                if (!selectEl) return

                // Simpan reference select
                this.items[index]._selectEl = selectEl

                // Cegah double init
                if ($(selectEl).hasClass('select2-hidden-accessible')) return

                $(selectEl).select2({
                    placeholder: 'Cari barang...',
                    allowClear: true,
                    theme: 'bootstrap4',
                    width: '100%'
                })

                $(selectEl).on('change', async (e) => {
                    this.items[index].barang_id = e.target.value
                    this.refreshDisabledOptions()

                    // RESET semua input row
                    this.resetRow(index)
                    if (this.items[index]._satuanSelectEl) {
                        $(this.items[index]._satuanSelectEl).val('').trigger('change.select2');
                    }

                    if (e.target.value) {
                        try {
                            const response = await fetch(`/gudang/barang/${e.target.value}/satuan`);
                            const data = await response.json();
                            this.items[index].satuanList = data;

                            // Auto select jika ada yang is_default
                            const defaultSatuan = data.find(s => s.is_default == 1);
                            if (defaultSatuan && this.items[index]._satuanSelectEl) {
                                this.items[index].satuan_id = defaultSatuan.id;
                                setTimeout(() => {
                                    $(this.items[index]._satuanSelectEl).val(defaultSatuan.id).trigger('change.select2');
                                }, 100);
                            }
                        } catch (err) {
                            console.error("Gagal get satuan:", err);
                        }
                    }
                })

                this.refreshDisabledOptions()
            },

            /* =======================
             * DISABLE BARANG DUPLIKAT
             * ======================= */
            refreshDisabledOptions() {
                let selectedIds = this.items
                    .map(item => item.barang_id)
                    .filter(id => id)

                this.items.forEach((item) => {
                    if (!item._selectEl) return

                    let select = item._selectEl
                    let currentValue = item.barang_id

                    Array.from(select.options).forEach(option => {
                        if (!option.value) return

                        option.disabled =
                            selectedIds.includes(option.value) &&
                            option.value !== currentValue
                    })

                    // Refresh Select2 UI
                    $(select).trigger('change.select2')
                })
            },

            /* =======================
             * INIT SATUAN SELECT2
             * ======================= */
            initSatuanSelect2(selectEl, index) {
                if (!selectEl) return

                this.items[index]._satuanSelectEl = selectEl

                if ($(selectEl).hasClass('select2-hidden-accessible')) return

                $(selectEl).select2({
                    placeholder: 'Pilih Satuan',
                    allowClear: true,
                    theme: 'bootstrap4',
                    width: '100%'
                })

                $(selectEl).on('change', (e) => {
                    this.items[index].satuan_id = e.target.value
                })
            },

            /* =======================
             * ADD ROW
             * ======================= */
            addRow() {
                this.items.push({
                    barang_id: '',
                    merk: '',
                    satuan_id: '',
                    satuanList: [],
                    jumlah: 1,
                    harga_satuan: '',
                    harga_satuan_display: '',
                    harga_total: '',
                    harga_total_display: '',
                    _selectEl: null,
                    _satuanSelectEl: null
                })
            },

            /* =======================
             * REMOVE ROW (AMAN)
             * ======================= */
            removeRow(index) {
                let item = this.items[index]

                if (item && item._selectEl) {
                    let $el = $(item._selectEl)
                    if ($el.hasClass('select2-hidden-accessible')) {
                        $el.select2('destroy')
                    }
                }

                if (item && item._satuanSelectEl) {
                    let $satEl = $(item._satuanSelectEl)
                    if ($satEl.hasClass('select2-hidden-accessible')) {
                        $satEl.select2('destroy')
                    }
                }

                this.items.splice(index, 1)

                this.$nextTick(() => {
                    this.refreshDisabledOptions()
                })
            },

            // reset semua inputan ketika ganti barang
            resetRow(index) {
                this.items[index].merk = ''
                this.items[index].jumlah = 1
                this.items[index].harga_satuan = ''
                this.items[index].harga_satuan_display = ''
                this.items[index].harga_total = ''
                this.items[index].harga_total_display = ''

                this.items[index].satuan_id = ''
                this.items[index].satuanList = []

                // reset select2 satuan
                if (this.items[index]._satuanSelectEl) {
                    $(this.items[index]._satuanSelectEl).val('').trigger('change.select2')
                }
            },

            /* =======================
             * HARGA & TOTAL
             * ======================= */
            formatHargaSatuan(index) {
                let raw = this.items[index].harga_satuan_display.replace(/\D/g, '')
                this.items[index].harga_satuan = raw
                this.items[index].harga_satuan_display = this.formatRupiah(raw)
                this.hitungTotal(index)
            },

            hitungTotal(index) {
                let qty = this.items[index].jumlah || 0
                let harga = this.items[index].harga_satuan || 0
                let total = qty * harga

                this.items[index].harga_total = total
                this.items[index].harga_total_display = this.formatRupiah(total)
            },

            formatRupiah(angka) {
                if (!angka) return ''
                return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
            }
        }
    }
</script>




@endsection
