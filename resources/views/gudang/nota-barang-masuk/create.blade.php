@extends('layouts.app')

@section('pageActive', 'TambahNotaMasuk')

@section('content')
    {{-- select 2  --}}
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">


    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

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

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <!-- Nomor Nota -->
                        <div>
                            <label for="nomor_nota" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Nomor Nota <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="nomor_nota" name="nomor_nota" readonly value="{{ $newNomorNota }}"
                                class="w-full bg-gray-100 border text-gray-500 text-sm rounded-lg p-2.5
                                  dark:bg-gray-700 dark:text-gray-400 border-gray-300 cursor-not-allowed">

                            @error('nomor_nota')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tanggal Nota -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Tanggal Nota <span class="text-red-500">*</span>
                            </label>

                            <div class="relative" x-data="{
                                tampil: '{{ now()->format('d-m-Y') }}',
                                simpan: '{{ now()->format('Y-m-d') }}'
                            }">
                                <!-- Icon Kalender -->
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                                    </svg>
                                </div>

                                <!-- Input Date -->
                                <input type="date" name="tanggal_nota" x-data="{ tanggal: '{{ now()->format('d-m-Y') }}' }" x-init="flatpickr($el, {
                                    dateFormat: 'd-m-Y',
                                    defaultDate: tanggal,
                                    onChange: (selectedDates, dateStr) => { tanggal = dateStr }
                                })"
                                    placeholder="Pilih tanggal"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500
                                focus:border-blue-500 block w-full pl-10 p-2.5 dark:bg-gray-700 dark:border-gray-600
                                dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500
                                dark:focus:border-blue-500">
                                <!-- Input hidden format Y-m-d -->
                                <input type="hidden" name="tanggal_nota" x-model="simpan">
                            </div>
                        </div>

                        <!-- Supplier -->
                        <div>
                            <label for="nama_barang" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Supplier <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="nama_barang" name="nama_barang" required
                                value="{{ old('nama_barang') }}" placeholder="Contoh: Semen"
                                class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                            dark:bg-gray-700 dark:text-white
                            @error('nama_barang') border-red-500 @else border-gray-300 @enderror">
                            @error('nama_barang')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Cara Bayar -->
                        <div>
                            <label for="cara_bayar" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Cara Bayar <span class="text-red-500">*</span>
                            </label>

                            <div class="relative">
                                <select id="cara_bayar" name="cara_bayar" required
                                    class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5 pr-10
                   focus:ring-blue-500 focus:border-blue-500
                   dark:bg-gray-700 dark:text-white
                   @error('cara_bayar') border-red-500 @else border-gray-300 @enderror">
                                    <option value="">Pilih cara bayar</option>
                                    <option value="cash" {{ old('cara_bayar') == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="hutang" {{ old('cara_bayar') == 'hutang' ? 'selected' : '' }}>Hutang
                                    </option>
                                </select>
                            </div>

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
                                    <th class="border px-2 py-2">Jumlah</th>
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
                    class="px-8 py-2.5 text-sm font-medium text-white rounded-lg bg-blue-600 hover:bg-blue-700
                       focus:outline-none focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800">
                    Simpan
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
                    jumlah: 1,
                    harga_satuan: '',
                    harga_satuan_display: '',
                    harga_total: '',
                    harga_total_display: '',
                    _selectEl: null
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
                        width: '100%'
                    })

                    $(selectEl).on('change', (e) => {
                        this.items[index].barang_id = e.target.value
                        this.refreshDisabledOptions()
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
                 * ADD ROW
                 * ======================= */
                addRow() {
                    this.items.push({
                        barang_id: '',
                        merk: '',
                        jumlah: 1,
                        harga_satuan: '',
                        harga_satuan_display: '',
                        harga_total: '',
                        harga_total_display: '',
                        _selectEl: null
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

                    this.items.splice(index, 1)

                    this.$nextTick(() => {
                        this.refreshDisabledOptions()
                    })
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
