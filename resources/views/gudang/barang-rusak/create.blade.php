@extends('layouts.app')

@section('pageActive', 'DaftarBarangRusak')

@section('content')
<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-init="$dispatch('sidebar-minimize')">
    <div x-data="{ pageName: 'TambahBarangRusak' }">
        @include('partials.breadcrumb')
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-800 dark:bg-gray-800 dark:text-red-400" role="alert">
            <span class="font-medium">Terjadi kesalahan validasi:</span>
            <ul class="mt-1.5 list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('gudang.barangRusak.store') }}" method="POST"
                x-data="barangRusakForm()"
                @submit="validateSubmit">
                @csrf

        <div class="mb-6 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="px-5 py-4 sm:px-6 sm:py-5">
                <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-4 dark:border-gray-800">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        Tambah Barang Rusak
                    </h3>
                    <a href="{{ route('gudang.barangRusak.index') }}"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:hover:bg-gray-700">
                        Kembali
                    </a>
                </div>

                <div class="mb-6 rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800 dark:border-blue-800 dark:bg-gray-800 dark:text-blue-400">
                    Satu nomor barang rusak dipakai untuk satu barang. Jika ada barang rusak lain, buat transaksi baru agar rollback FIFO tetap jelas.
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-white">
                            Nomor Barang Rusak
                        </label>
                        <input type="text" name="nomor_barang_rusak" value="{{ $newNomorBarangRusak }}" readonly
                            class="w-full cursor-not-allowed rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-400">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-white">
                            Created By
                        </label>
                        <input type="text" value="{{ $createdByName }}" readonly
                            class="w-full cursor-not-allowed rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-400">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-white">
                            Gudang Sumber <span class="text-red-500">*</span>
                        </label>
                        <select name="stock_type" x-model="stockType" @change="onSourceChange" required
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option value="HUB">Gudang HUB</option>
                            <option value="UBS">Gudang UBS</option>
                        </select>
                    </div>

                    <div x-show="stockType === 'UBS'">
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-white">
                            Pilih UBS <span class="text-red-500">*</span>
                        </label>
                        <select name="ubs_id" x-model="ubsId" @change="onSourceChange" :required="stockType === 'UBS'"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option value="">Pilih UBS</option>
                            @foreach ($ubsList as $ubs)
                                <option value="{{ $ubs->id }}" {{ old('ubs_id') == $ubs->id ? 'selected' : '' }}>
                                    {{ $ubs->nama_ubs }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-white">
                            Tanggal Rusak <span class="text-red-500">*</span>
                        </label>
                        @php
                            $tglRusakValue = old('tgl_rusak', now()->toDateString());
                        @endphp
                        <div class="relative" x-data="{ tampil: '{{ \Carbon\Carbon::parse($tglRusakValue)->format('d-m-Y') }}', simpan: '{{ $tglRusakValue }}' }">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                                </svg>
                            </div>
                            <input type="text" id="tgl_rusak_display" x-model="tampil" required
                                x-init="flatpickr($el, {
                                    dateFormat: 'd-m-Y',
                                    defaultDate: tampil,
                                    allowInput: true,
                                    onChange: (selectedDates, dateStr, instance) => {
                                        tampil = dateStr;
                                        simpan = selectedDates.length ? instance.formatDate(selectedDates[0], 'Y-m-d') : '';
                                    }
                                })"
                                class="w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 pl-10 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <input type="hidden" name="tgl_rusak" x-model="simpan">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-6 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="px-5 py-4 sm:px-6 sm:py-5">
                <h3 class="mb-4 border-b border-gray-100 pb-4 text-base font-medium text-gray-800 dark:border-gray-800 dark:text-white/90">
                    Barang Yang Rusak
                </h3>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-white">
                            Barang <span class="text-red-500">*</span>
                        </label>
                        <select id="barang_id" name="barang_id" x-ref="barangSelect" x-init="initBarangSelect2()" required
                            class="select-barang w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900">
                            <option value="">Pilih Barang</option>
                            @foreach ($masterBarangs as $barang)
                                <option value="{{ $barang->id }}" {{ old('barang_id') == $barang->id ? 'selected' : '' }}>
                                    {{ $barang->kode_barang }} - {{ $barang->nama_barang }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-white">
                            Satuan <span class="text-red-500">*</span>
                        </label>
                        <select id="satuan_id" name="satuan_id" x-ref="satuanSelect" x-init="initSatuanSelect2()" required
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900">
                            <option value="">Pilih Satuan</option>
                            <template x-for="satuan in satuanList" :key="satuan.id">
                                <option :value="satuan.id" x-text="satuan.nama"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-white">
                            Stock Saat Ini
                        </label>
                        <input type="text" :value="stockSaatIni !== null ? stockSaatIni : '0'" readonly
                            class="w-full cursor-not-allowed rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-400">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-white">
                            Qty Rusak <span class="text-red-500">*</span>
                        </label>
                        <input type="number" step="any" min="0.001" :max="stockSaatIni || null" name="qty_out" x-model.number="qtyOut"
                            value="{{ old('qty_out') }}" required
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-white">
                            Keterangan
                        </label>
                        <input type="text" name="keterangan" value="{{ old('keterangan') }}"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            placeholder="Opsional">
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('gudang.barangRusak.index') }}"
                class="rounded-lg border border-gray-300 bg-white px-6 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Batal
            </a>
            <button type="submit"
                class="rounded-lg bg-blue-600 px-8 py-2.5 text-sm font-bold text-white shadow-lg shadow-blue-500/30 hover:bg-blue-700">
                Simpan Barang Rusak
            </button>
        </div>
    </form>
</div>

<script>
    function barangRusakForm() {
        return {
            stockType: @json(old('stock_type', 'HUB')),
            ubsId: @json(old('ubs_id', '')),
            barangId: @json(old('barang_id', '')),
            satuanId: @json(old('satuan_id', '')),
            satuanList: [],
            stockSaatIni: null,
            qtyOut: @json(old('qty_out', 0)),

            initBarangSelect2() {
                const selectEl = this.$refs.barangSelect;

                $(selectEl).select2({
                    placeholder: 'Cari barang...',
                    allowClear: true,
                    theme: 'bootstrap4',
                    width: '100%'
                });

                $(selectEl).on('change', async (event) => {
                    this.barangId = event.target.value;
                    this.resetSatuan();

                    if (this.barangId) {
                        await this.loadSatuanAndStock();
                    }
                });

                if (this.barangId) {
                    $(selectEl).val(this.barangId).trigger('change');
                }
            },

            initSatuanSelect2() {
                const selectEl = this.$refs.satuanSelect;

                $(selectEl).select2({
                    placeholder: 'Pilih satuan',
                    allowClear: true,
                    theme: 'bootstrap4',
                    width: '100%'
                });

                $(selectEl).on('change', (event) => {
                    this.satuanId = event.target.value;
                    this.updateStockBySatuan();
                });
            },

            async loadSatuanAndStock() {
                if (this.stockType === 'UBS' && !this.ubsId) {
                    alert('Pilih UBS dulu sebelum memilih barang.');
                    return;
                }

                const params = new URLSearchParams({
                    stock_type: this.stockType,
                    ubs_id: this.ubsId || ''
                });

                try {
                    const response = await fetch(`/gudang/barang-rusak/satuan-dan-stok/${this.barangId}?${params.toString()}`);
                    const data = await response.json();
                    this.satuanList = data;

                    const selectedSatuan = data.find((row) => row.id == this.satuanId);
                    const defaultSatuan = selectedSatuan || data.find((row) => row.is_default == 1) || data[0];

                    if (defaultSatuan) {
                        this.satuanId = defaultSatuan.id;
                        this.stockSaatIni = defaultSatuan.stock_saat_ini;

                        setTimeout(() => {
                            $(this.$refs.satuanSelect).val(defaultSatuan.id).trigger('change.select2');
                        }, 100);
                    }
                } catch (error) {
                    console.error('Gagal mengambil satuan dan stock:', error);
                }
            },

            updateStockBySatuan() {
                const satuan = this.satuanList.find((row) => row.id == this.satuanId);
                this.stockSaatIni = satuan ? satuan.stock_saat_ini : null;
            },

            onSourceChange() {
                this.resetSatuan();

                if (this.barangId && (this.stockType === 'HUB' || this.ubsId)) {
                    this.loadSatuanAndStock();
                }
            },

            resetSatuan() {
                this.satuanId = '';
                this.satuanList = [];
                this.stockSaatIni = null;
                this.qtyOut = 0;

                if (this.$refs.satuanSelect) {
                    $(this.$refs.satuanSelect).val('').trigger('change.select2');
                }
            },

            validateSubmit(event) {
                const invalidSource = this.stockType === 'UBS' && !this.ubsId;
                const invalidItem = !this.barangId || !this.satuanId || !this.qtyOut || this.qtyOut <= 0;
                const overStock = this.stockSaatIni !== null && Number(this.qtyOut) > Number(this.stockSaatIni);

                if (invalidSource || invalidItem || overStock) {
                    event.preventDefault();
                    alert('Pastikan gudang sumber, barang, satuan, dan qty rusak sudah benar serta tidak melebihi stock.');
                }
            }
        }
    }
</script>
@endsection
