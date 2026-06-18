@extends('layouts.app')

@section('pageActive', 'ProduksiRakitan')

@section('content')
{{-- select 2 --}}
<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">

<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

    <div x-data="{ pageName: 'ProduksiRakitan' }">
        @include('partials.breadcrumb')
    </div>

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

    @if (session('error'))
    <div class="flex p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400"
        role="alert">
        <svg class="shrink-0 inline w-4 h-4 me-3 mt-[2px]" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
            viewBox="0 0 20 20">
            <path
                d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
        </svg>
        <div>{{ session('error') }}</div>
    </div>
    @endif

    <form action="{{ route('gudang.produksiRakitan.store') }}" method="POST"
        x-data="produksiRakitanForm({{ Js::from($komposisiRakitans) }}, {{ Js::from($stockGudang) }}, {{ Js::from([
            'stock_type' => old('stock_type', 'HUB'),
            'ubs_id' => old('ubs_id', ''),
            'barang_rakitan_id' => old('barang_rakitan_id', ''),
            'qty_hasil' => old('qty_hasil', ''),
        ]) }})">
        @csrf

        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
            <div class="px-5 py-4 sm:px-6 sm:py-5">
                <h3
                    class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 border-b border-gray-100 dark:border-gray-800">
                    Produksi Barang Rakitan
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="tanggal_rakitan" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Tanggal <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="tanggal_rakitan" name="tanggal_rakitan"
                            value="{{ old('tanggal_rakitan', now()->toDateString()) }}"
                            class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-white"
                            required>
                    </div>

                    <div>
                        <label for="gudang_tujuan" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Gudang <span class="text-red-500">*</span>
                        </label>
                        <select id="gudang_tujuan" x-model="gudangTujuan" @change="applyGudang"
                            class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-white"
                            required>
                            <option value="HUB">HUB</option>
                            @foreach ($ubsList as $ubs)
                            <option value="UBS:{{ $ubs->id }}">
                                {{ $ubs->nama_ubs }} {{ $ubs->kode_ubs ? '(' . $ubs->kode_ubs . ')' : '' }}
                            </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="stock_type" x-model="stockType">
                        <input type="hidden" name="ubs_id" x-model="ubsId">
                    </div>

                    <div>
                        <label for="barang_rakitan_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Komposisi Rakitan <span class="text-red-500">*</span>
                        </label>
                        <select id="barang_rakitan_id" name="barang_rakitan_id" x-ref="komposisiSelect" x-model="barangRakitanId"
                            class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-white"
                            required>
                            <option value="">Pilih Komposisi</option>
                            <template x-for="komposisi in komposisiRakitans" :key="komposisi.id">
                                <option :value="komposisi.id"
                                    x-text="`${komposisi.label} (${formatQty(komposisi.qty_hasil)} ${komposisi.satuan_hasil_nama})`">
                                </option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label for="qty_hasil" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Jumlah Produksi <span class="text-red-500">*</span>
                        </label>
                        <div class="flex">
                            <input type="number" id="qty_hasil" name="qty_hasil" step="0.001" min="1"
                                x-model.number="qtyHasil" @input="hitungKomposisi"
                                class="w-full rounded-l-lg bg-gray-50 border border-gray-300 text-gray-900 text-sm p-2.5 dark:bg-gray-700 dark:text-white"
                                required>
                            <span class="inline-flex items-center rounded-r-lg border border-l-0 border-gray-300 bg-gray-100 px-3 text-sm text-gray-500 dark:bg-gray-700 dark:text-gray-400"
                                x-text="selectedKomposisi?.satuan_hasil_nama || '-'"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Barang Hasil
                        </label>
                        <div class="rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-300">
                            <div class="font-medium" x-text="selectedKomposisi?.barang_hasil_nama || '-'"></div>
                            <div class="text-xs text-gray-500 dark:text-gray-400" x-text="selectedKomposisi?.barang_hasil_kode || '-'"></div>
                        </div>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Dasar Komposisi
                        </label>
                        <div class="rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-300">
                            <span x-text="selectedKomposisi ? formatQty(selectedKomposisi.qty_hasil) : '-'"></span>
                            <span x-text="selectedKomposisi?.satuan_hasil_nama || ''"></span>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label for="keterangan" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Keterangan
                        </label>
                        <textarea id="keterangan" name="keterangan" rows="2"
                            class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-white"
                            placeholder="Keterangan produksi">{{ old('keterangan') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

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
                                <th class="border border-gray-300 dark:border-gray-700 px-2 py-2 text-left text-gray-900 dark:text-gray-100 w-[45%]">Barang Bahan</th>
                                <th class="border border-gray-300 dark:border-gray-700 px-2 py-2 text-right text-gray-900 dark:text-gray-100">Qty Komposisi</th>
                                <th class="border border-gray-300 dark:border-gray-700 px-2 py-2 text-right text-gray-900 dark:text-gray-100">Qty Pakai</th>
                                <th class="border border-gray-300 dark:border-gray-700 px-2 py-2 text-gray-900 dark:text-gray-100">Satuan</th>
                                <th class="border border-gray-300 dark:border-gray-700 px-2 py-2 text-right text-gray-900 dark:text-gray-100">Stok Gudang</th>
                                <th class="border border-gray-300 dark:border-gray-700 px-2 py-2 text-center text-gray-900 dark:text-gray-100">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="!selectedKomposisi">
                                <tr>
                                    <td colspan="6" class="border border-gray-300 dark:border-gray-700 px-3 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                        Pilih komposisi rakitan terlebih dahulu.
                                    </td>
                                </tr>
                            </template>

                            <template x-if="selectedKomposisi && calculatedItems.length === 0">
                                <tr>
                                    <td colspan="6" class="border border-gray-300 dark:border-gray-700 px-3 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                        Komposisi ini belum memiliki bahan.
                                    </td>
                                </tr>
                            </template>

                            <template x-for="item in calculatedItems" :key="`${item.barang_bahan_id}-${item.satuan_id}`">
                                <tr>
                                    <td class="border border-gray-300 dark:border-gray-700 px-3 py-2 text-gray-900 dark:text-white">
                                        <div x-text="item.barang_bahan_nama"></div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400" x-text="item.barang_bahan_kode"></div>
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-700 px-3 py-2 text-right text-gray-900 dark:text-white"
                                        x-text="formatQty(item.qty)"></td>
                                    <td class="border border-gray-300 dark:border-gray-700 px-3 py-2 text-right font-medium text-gray-900 dark:text-white"
                                        x-text="formatQty(item.qty_pakai)"></td>
                                    <td class="border border-gray-300 dark:border-gray-700 px-3 py-2 text-center text-gray-900 dark:text-white"
                                        x-text="item.satuan_nama"></td>
                                    <td class="border border-gray-300 dark:border-gray-700 px-3 py-2 text-right text-gray-900 dark:text-white"
                                        x-text="`${formatQty(item.stock_display)} ${item.satuan_nama}`"></td>
                                    <td class="border border-gray-300 dark:border-gray-700 px-3 py-2 text-center">
                                        <span x-show="item.stock_cukup"
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                            Cukup
                                        </span>
                                        <span x-show="!item.stock_cukup"
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                                            Kurang
                                        </span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div x-show="hasStockKurang()" x-cloak
            class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300">
            Stok gudang tidak mencukupi untuk komposisi bahan yang dipilih. Sesuaikan jumlah produksi atau pilih gudang lain.
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('gudang.produksiRakitan.index') }}"
                class="px-8 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 dark:text-white dark:bg-gray-700 dark:hover:bg-gray-600">
                Kembali
            </a>
            <button type="submit"
                :disabled="hasStockKurang()"
                :class="hasStockKurang() ? 'cursor-not-allowed opacity-60' : ''"
                class="px-8 py-2.5 text-sm font-medium text-white rounded-lg bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800">
                Simpan
            </button>
        </div>
    </form>
</div>

<script>
    function produksiRakitanForm(komposisiRakitans, stockGudang, oldData = {}) {
        return {
            komposisiRakitans,
            stockGudang,
            stockType: oldData.stock_type || 'HUB',
            ubsId: oldData.ubs_id || '',
            gudangTujuan: oldData.stock_type === 'UBS' && oldData.ubs_id ? `UBS:${oldData.ubs_id}` : 'HUB',
            barangRakitanId: oldData.barang_rakitan_id || '',
            qtyHasil: oldData.qty_hasil || '',
            selectedKomposisi: null,
            calculatedItems: [],

            init() {
                this.$nextTick(() => {
                    this.initKomposisiSelect();
                    this.applyGudang();

                    if (this.barangRakitanId) {
                        $(this.$refs.komposisiSelect).val(this.barangRakitanId).trigger('change.select2');
                        this.applyKomposisi(this.barangRakitanId);
                    }
                });
            },

            initKomposisiSelect() {
                const selectEl = this.$refs.komposisiSelect;
                if (!selectEl || $(selectEl).hasClass('select2-hidden-accessible')) return;

                $(selectEl).select2({
                    placeholder: 'Cari komposisi rakitan...',
                    allowClear: true,
                    theme: 'bootstrap4',
                    width: '100%',
                }).on('change', (e) => {
                    this.applyKomposisi(e.target.value);
                });
            },

            applyGudang() {
                if (this.gudangTujuan === 'HUB') {
                    this.stockType = 'HUB';
                    this.ubsId = '';
                    this.hitungKomposisi();
                    return;
                }

                const [stockType, ubsId] = this.gudangTujuan.split(':');
                this.stockType = stockType || 'HUB';
                this.ubsId = ubsId || '';
                this.hitungKomposisi();
            },

            applyKomposisi(id) {
                this.barangRakitanId = id;
                this.selectedKomposisi = this.komposisiRakitans.find(komposisi => String(komposisi.id) === String(id)) || null;

                if (this.selectedKomposisi && !this.qtyHasil) {
                    this.qtyHasil = this.selectedKomposisi.qty_hasil;
                }

                this.hitungKomposisi();
            },

            hitungKomposisi() {
                if (!this.selectedKomposisi) {
                    this.calculatedItems = [];
                    return;
                }

                const qtyProduksi = Number(this.qtyHasil || 0);
                const qtyDasar = Number(this.selectedKomposisi.qty_hasil || 0);
                const ratio = qtyDasar > 0 ? qtyProduksi / qtyDasar : 0;

                this.calculatedItems = this.selectedKomposisi.details.map((item) => ({
                    ...this.buildCalculatedItem(item, ratio),
                }));
            },

            buildCalculatedItem(item, ratio) {
                const qtyPakai = Number(item.qty || 0) * ratio;
                const qtyPakaiBase = Number(item.qty_base || 0) * ratio;
                const stockBase = this.getStockBase(item.barang_bahan_id);
                const konversiKeBase = Number(item.qty || 0) > 0
                    ? Number(item.qty_base || 0) / Number(item.qty || 0)
                    : 1;
                const stockDisplay = konversiKeBase > 0 ? stockBase / konversiKeBase : stockBase;

                return {
                    ...item,
                    qty_pakai: qtyPakai,
                    qty_pakai_base: qtyPakaiBase,
                    stock_base: stockBase,
                    stock_display: stockDisplay,
                    stock_cukup: stockBase + 0.000001 >= qtyPakaiBase,
                };
            },

            getStockBase(barangId) {
                const stock = this.stockGudang.find((item) => {
                    const sameBarang = String(item.barang_id) === String(barangId);
                    const sameStockType = item.stock_type === this.stockType;
                    const sameGudang = this.stockType === 'UBS'
                        ? String(item.ubs_id) === String(this.ubsId)
                        : item.ubs_id === null;

                    return sameBarang && sameStockType && sameGudang;
                });

                return Number(stock?.jumlah_stock || 0);
            },

            hasStockKurang() {
                return this.calculatedItems.some(item => !item.stock_cukup);
            },

            formatQty(value) {
                return Number(value || 0).toLocaleString('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 3,
                });
            },
        }
    }
</script>

@endsection
