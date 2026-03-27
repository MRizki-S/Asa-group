@extends('layouts.app')

@section('pageActive', 'MasterBarang')

@section('content')
{{-- select 2  --}}
<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">

<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

    <!-- Breadcrumb -->
    <div x-data="{ pageName: 'MasterBarang' }">
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

    <form action="{{ route('gudang.masterBarang.store') }}" method="POST">
        @csrf

        <!-- input table master Barang -->
        <div x-data="{ is_stock: '{{ old('is_stock', '') }}' }" class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
            <div class="px-5 py-4 sm:px-6 sm:py-5">
                <h3
                    class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 border-b border-gray-100 dark:border-gray-800">
                    Material Barang
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <!-- Kode Barang -->
                    <div>
                        <label for="kode_barang" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Kode Barang <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="kode_barang" name="kode_barang" value="{{ $newKodeBarang }}"
                            placeholder="BRG-XXXX"
                            class="w-full bg-gray-100 border text-gray-500 text-sm rounded-lg p-2.5
                 dark:bg-gray-700 dark:text-gray-400 border-gray-300" readonly>

                        @error('kode_barang')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>


                    <!-- Nama Barang -->
                    <div>
                        <label for="nama_barang" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Nama Barang <span class="text-red-500">*</span>
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


                    <!-- Satuan -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-white">
                            Satuan Stok Utama <span class="text-red-500">*</span>
                        </label>

                        <select name="satuan_id" id="satuanSelect" required
                            class="select-unit w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                                dark:bg-gray-700 dark:text-white
                                @error('satuan_id') border-red-500 @else border-gray-300 @enderror">
                            <option value="">Pilih Satuan</option>
                            @foreach ($satuan as $item)
                            <option value="{{ $item->id }}" {{ old('satuan_id') == $item->id ? 'selected' : '' }}>
                                {{ $item->nama }}
                            </option>
                            @endforeach
                        </select>


                        @error('satuan_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror

                        <div class="flex p-3 mt-4 text-sm text-blue-800 border border-blue-100 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400 dark:border-blue-800"
                            role="alert">
                            <svg class="flex-shrink-0 inline w-4 h-4 me-3 mt-[2px]" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                            </svg>
                            <div>
                                <span class="font-medium">Info Satuan:</span> Satuan ini adalah satuan terkecil dari barang tersebut.
                            </div>
                        </div>
                    </div>

                    <!-- Select tipe barang (is_stock or direct) -->
                    <div>
                        <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-white">Tipe
                            Barang</label>
                        <select name="is_stock" required x-model="is_stock"
                            class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                                   dark:bg-gray-800 dark:text-white
                                   @error('is_stock') border-red-500 @else border-gray-300 @enderror">
                            <option value="">Pilih Tipe Barang</option>
                            <option value="1">Stock (Disimpan Gudang)</option>
                            <option value="0">Direct (Langsung Pakai)</option>
                        </select>

                        @error('is_stock')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Bagian form khusus jika tipe barang adalah Stock -->
                <div x-show="is_stock === '1'" x-transition class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <!-- Minimal Stock HUB -->
                    <div>
                        <label for="minimal_stock_hub" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Minimal Stock Pusat (HUB) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="minimal_stock_hub" name="minimal_stock_hub" step="0.01" min="0"
                            x-bind:required="is_stock === '1'" value="{{ old('minimal_stock_hub', 0) }}"
                            class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-800 dark:text-white border-gray-300">
                    </div>

                    <!-- Minimal Stock UBS -->
                    <div>
                        <label for="minimal_stock_ubs" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Minimal Stock Unit (UBS) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="minimal_stock_ubs" name="minimal_stock_ubs" step="0.01" min="0"
                            x-bind:required="is_stock === '1'" value="{{ old('minimal_stock_ubs', 0) }}"
                            class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-800 dark:text-white border-gray-300">
                    </div>
                </div>
            </div>
        </div>

        {{-- Iput Barang Satuan Konversi--}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
            <div class="px-5 py-4">
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 border-b border-gray-100 dark:border-gray-800">
                    Satuan Konversi
                </h3>

                <div x-data="satuanKonversi({{ Js::from($satuan) }})" class="overflow-x-auto">

                    <table class="w-full border border-gray-300 dark:border-gray-700">
                        <thead class="bg-gray-100 dark:bg-gray-800">
                            <tr>
                                <th class="border border-gray-300 dark:border-gray-700 px-2 py-2 text-gray-900 dark:text-gray-100">Satuan</th>
                                <th class="border border-gray-300 dark:border-gray-700 px-2 py-2 text-gray-900 dark:text-gray-100">Nilai Konversi</th>
                                <th class="border border-gray-300 dark:border-gray-700 px-2 py-2 w-24 text-gray-900 dark:text-gray-100">Default</th>
                                <th class="border border-gray-300 dark:border-gray-700 px-2 py-2 w-24 text-gray-900 dark:text-gray-100">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            <template x-for="(item, index) in items" :key="item.id">
                                <tr>

                                    <!-- Satuan -->
                                    <td class="border border-gray-300 dark:border-gray-700 p-1 w-1/3">
                                        <div class="w-full">
                                            <select :name="`items[${index}][satuan_id]`"
                                                x-model="item.satuan_id"
                                                class="w-full border rounded p-2 bg-gray-50 border-gray-300 text-gray-900 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                                style="width: 100%"
                                                required
                                                x-init="
                                                        $nextTick(() => {
                                                            $($el).select2({
                                                                theme: 'bootstrap4',
                                                                width: '100%',
                                                                placeholder: 'Pilih Satuan'
                                                            }).on('change', function(e) {
                                                                item.satuan_id = e.target.value;
                                                            });

                                                            $watch('item.satuan_id', (value) => {
                                                                if ($($el).val() !== value) {
                                                                    $($el).val(value).trigger('change.select2');
                                                                }
                                                            });
                                                        });
                                                    ">

                                                <option value="">Pilih Satuan</option>

                                                <template x-for="sat in satuanList" :key="sat.id">
                                                    <option :value="sat.id" x-text="sat.nama"></option>
                                                </template>

                                            </select>
                                        </div>
                                    </td>

                                    <!-- Nilai Konversi -->
                                    <td class="border border-gray-300 dark:border-gray-700 p-1">
                                        <input type="number"
                                            step="0.01"
                                            min="0"
                                            x-model="item.konversi"
                                            :name="`items[${index}][konversi_ke_base]`"
                                            class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white text-center"
                                            required>
                                    </td>

                                    <!-- Default -->
                                    <td class="border border-gray-300 dark:border-gray-700 p-1 text-center">
                                        <label class="inline-flex items-center justify-center cursor-pointer">
                                            <input
                                                type="checkbox"
                                                :value="index"
                                                name="default_row"
                                                :checked="item.is_default"
                                                @click="setDefault(index)"
                                                class="w-5 h-5 cursor-pointer text-blue-600 bg-gray-100 border-gray-300 rounded
                                                    focus:ring-2 focus:ring-blue-500
                                                    dark:bg-gray-800 dark:border-gray-600">
                                        </label>
                                    </td>



                                    <!-- Aksi -->
                                    <td class="border border-gray-300 dark:border-gray-700 p-1 text-center">
                                        <button type="button"
                                            x-show="index !== 0"
                                            @click="removeRow(index)"
                                            class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">
                                            Hapus
                                        </button>
                                    </td>

                                </tr>
                            </template>
                        </tbody>
                    </table>

                    <button type="button"
                        @click="addRow"
                        class="mt-3 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600">
                        + Tambah Baris
                    </button>

                </div>
            </div>
        </div>


        <!-- Tombol Aksi -->
        <div class="flex justify-end gap-2">
            <button type="button" onclick="window.location.replace('{{ route('gudang.masterBarang.index') }}')"
                class="px-8 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300
                       dark:text-white dark:bg-gray-700 dark:hover:bg-gray-600">
                Kembali
            </button>
            <button type="submit"
                class="px-8 py-2.5 text-sm font-medium text-white rounded-lg bg-blue-600 hover:bg-blue-700
                       focus:outline-none focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800">
                Simpan
            </button>
        </div>
    </form>
</div>

<script>
    function satuanKonversi(satuanList) {
        return {
            satuanList: satuanList,

            items: [{
                id: Date.now(),
                satuan_id: '',
                konversi: null,
                is_default: true
            }],

            addRow() {
                this.items.push({
                    id: Date.now() + Math.random(),
                    satuan_id: '',
                    konversi: '',
                    is_default: false
                });
            },

            removeRow(index) {
                if (index === 0) return; // Prevent deleting first row
                if (this.items.length > 1) {
                    this.items.splice(index, 1);
                }
            },

            setDefault(index) {
                this.items.forEach((item, i) => {
                    item.is_default = i === index;
                });
            }
        }
    }

    $(document).ready(function() {
        $('#satuanSelect').select2({
            placeholder: "Pilih Satuan",
            theme: 'bootstrap4',
            allowClear: true,
            width: '100%'
        });
    });
</script>

@endsection
