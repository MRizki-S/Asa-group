@extends('layouts.app')

@section('pageActive', 'StokBarangGudang')

@section('content')
<!-- ===== Main Content Start ===== -->
<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

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

    <div class="space-y-5 sm:space-y-6">
        <div
            class="rounded-2xl border border-gray-200 px-5 py-4 sm:px-6 sm:py-5 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-4 flex items-center justify-between gap-4">
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90 whitespace-nowrap">
                    Stock Barang - <span class="text-blue-600 dark:text-blue-400 font-bold ml-1">{{ $titleGudang }}</span>
                </h3>

                <a href="{{ route('gudang.transferStockBarang.create') }}"
                    target="_blank"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-white">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                    </svg>
                    <span>Transfer Stock</span>
                </a>
            </div>

            <!-- Pilih Gudang -->
            <form method="GET" action="{{ route('gudang.stockBarang.index') }}"
                x-data="{ tipe: '{{ request('tipe', 'bulan') }}' }" class="relative mb-6 p-6 pt-8 bg-white dark:bg-gray-800 rounded-xl border border-gray-200
                                                                        dark:border-gray-700 shadow-sm">
                <span class="absolute -top-3 left-5 px-3 py-1 text-xs font-semibold 
                                                    uppercase tracking-widest rounded-md
                                                    bg-white dark:bg-gray-800 
                                                    text-blue-600 dark:text-blue-400">
                    Filter
                </span>

                <div class="flex flex-wrap items-end gap-4">
                    <!-- pilih ubs  -->
                    <div class="flex-1 min-w-[250px]">
                        <label class="block mb-1.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                            Pilih Gudang
                        </label>

                        <select name="ubs_id"
                            class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                                                                            dark:bg-gray-700 dark:text-white border-gray-300" required>

                            {{-- Opsi Semua Gudang --}}
                            <option value="all" {{ request('ubs_id', 'all') == 'all' ? 'selected' : '' }}>
                                Semua Gudang
                            </option>
                            {{-- Opsi HUB (Pusat) --}}
                            <option value="hub" {{ request('ubs_id') == 'hub' ? 'selected' : '' }}>
                                HUB (Pusat)
                            </option>
                            {{-- Opsi UBS --}}
                            @foreach ($ubsData as $ubs)
                            <option value="{{ $ubs->kode_ubs }}" {{ request('ubs_id') == $ubs->kode_ubs ? 'selected' : '' }}>
                                {{ $ubs->nama_ubs }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Cari Material -->
                    <div class="flex-1 min-w-[160px]">
                        <label
                            class="block mb-1.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Cari
                            Material</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400 group-focus-within:text-blue-500 transition-colors"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" id="cariMaterial" name="cariMaterial" autocomplete="off"
                                value="{{ request('cariMaterial') }}" placeholder="Cari Material..."
                                class="flatpickr bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 block w-full ps-10 p-2.5 transition-all outline-none">
                        </div>
                    </div>

                    <!-- Terapkan -->
                    <div class="flex items-center gap-2">
                        <button type="submit"
                            class="inline-flex items-center px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 transition-all shadow-sm shadow-blue-200 dark:shadow-none">
                            <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Terapkan
                        </button>

                        <a href="{{ route('gudang.stockBarang.index') }}"
                            class="px-5 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-all">
                            Reset
                        </a>
                    </div>

                    <div class="hidden lg:block w-px h-10 bg-gray-200 dark:bg-gray-700 mx-2"></div>

                    <div class="relative inline-block text-left" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false" type="button"
                            class="inline-flex items-center px-5 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-all shadow-sm">
                            <svg class="w-4 h-4 me-2 text-gray-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Export
                            <svg class="w-4 h-4 ms-2 transition-transform" :class="open ? 'rotate-180' : ''"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="open" x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            class="absolute right-0 z-20 mt-2 w-48 origin-top-right bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl ring-1 ring-black ring-opacity-5 focus:outline-none">
                            <div class="p-1">
                                <a href="{{ route('gudang.stockBarang.exportPdf', request()->all()) }}"
                                    class="flex items-center w-full px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400 rounded-lg transition-colors group">
                                    <svg class="w-5 h-5 me-3 text-gray-400 group-hover:text-red-500" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path
                                            d="M9 2a2 2 0 00-2 2v8a2 2 0 002 2h6a2 2 0 002-2V6.414A2 2 0 0016.414 5L14 2.586A2 2 0 0012.586 2H9z" />
                                        <path d="M3 8a2 2 0 012-2v10h8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z" />
                                    </svg>
                                    Export as PDF
                                </a>
                                <a href="{{ route('gudang.stockBarang.exportExcel', request()->all()) }}"
                                    class="flex items-center w-full px-4 py-3 text-sm text-gray-700 dark:text-gray-300
                                    hover:bg-green-50 dark:hover:bg-green-900/20
                                    hover:text-green-600 dark:hover:text-green-400 rounded-lg transition-colors group">
                                    <svg class="w-5 h-5 me-3 text-gray-400 group-hover:text-green-500"
                                        fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm2 10a1 1 0 10-2 0v3a1 1 0 102 0v-3zm2-3a1 1 0 011 1v5a1 1 0 11-2 0v-5a1 1 0 011-1zm4-1a1 1 0 10-2 0v7a1 1 0 102 0V8z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Export as Excel
                                </a>

                            </div>
                        </div>
                    </div>
                </div>
            </form>




            <div class="w-full max-h-[600px] overflow-auto border border-gray-300 rounded-lg shadow-sm">
                <table class="min-w-max w-full border-collapse text-sm">

                    <thead class="bg-gray-800 text-white sticky top-0 z-10">
                        <tr>
                            <th class="border px-3 py-2 text-left whitespace-nowrap w-[20%]">Kode Barang</th>
                            <th class="border px-3 py-2 text-left whitespace-nowrap w-[40%]">Nama Barang</th>
                            <th class="border px-3 py-2 text-center whitespace-nowrap w-[15%]">Satuan</th>
                            <th class="border px-3 py-2 text-center whitespace-nowrap w-[15%]">Total Stock</th>
                            @if($selectedUbs != 'all')
                            <th class="border px-3 py-2 text-center whitespace-nowrap w-[10%]">Minimal Stock</th>
                            @endif
                        </tr>
                    </thead>

                    {{--
                        PENTING: Kita menggunakan @foreach di luar <tbody>
                            dan membuat satu
                        <tbody> per barang agar x-data bekerja mandiri per baris
                            --}}
                    @php
                    if (!function_exists('formatStock')) {
                    function formatStock($val) {
                    $formatted = number_format((float)$val, 2, ',', '.');
                    $formatted = rtrim($formatted, '0');
                    $formatted = rtrim($formatted, ',');
                    return $formatted;
                    }
                    }
                    @endphp
                    @foreach($stocks as $barang)
                    @php
                    $defaultKonversi = $barang->satuanKonversi->where('is_default', true)->first();

                    if ($defaultKonversi) {
                    $satuanNama = $defaultKonversi->satuan->nama ?? 'Unknown';
                    $konversiRate = (float)$defaultKonversi->konversi_ke_base;
                    } else {
                    $satuanNama = $barang->baseUnit->nama ?? '-';
                    $konversiRate = 1;
                    }

                    $stockVal = 0;
                    $minStockVal = 0;

                    if($selectedUbs == 'all') {
                    // Secara default base stock
                    $stockVal = $barang->stock->sum('jumlah_stock');
                    } elseif ($selectedUbs == 'hub') {
                    $stockVal = $barang->stockHub->jumlah_stock ?? 0;
                    $minStockVal = $barang->stockHub->minimal_stock ?? 0;
                    } else {
                    $s = $barang->stock->first();
                    $stockVal = $s->jumlah_stock ?? 0;
                    $minStockVal = $s->minimal_stock ?? 0;
                    }

                    // Divide by conversion rate
                    $stockDisplay = $stockVal / $konversiRate;
                    $minStockDisplay = $minStockVal / $konversiRate;
                    @endphp

                    <tbody x-data="{ open: false }" class="border-b">
                        {{-- ROW UTAMA --}}
                        <tr @click="open = !open"
                            class="bg-blue-50 font-bold text-gray-900 border-t-2 border-blue-200 cursor-pointer hover:bg-blue-100 transition-colors">
                            <td class="border px-3 py-2 text-center text-blue-800">
                                <div class="flex items-center gap-2">
                                    @if($selectedUbs == 'all')
                                    <svg class="w-4 h-4 transition-transform duration-200"
                                        :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                    @endif
                                    {{ $barang->kode_barang }}
                                </div>
                            </td>
                            <td class="border px-3 py-2 text-blue-800">
                                {{ $barang->nama_barang }}
                            </td>
                            <td class="border px-3 py-2 text-center text-gray-700">
                                {{ $satuanNama }}
                            </td>
                            <td class="border px-3 py-2 text-center font-bold">
                                {{ formatStock($stockDisplay) }}
                            </td>
                            @if($selectedUbs != 'all')
                            <td class="border px-3 py-2 text-center italic text-gray-500">
                                {{ formatStock($minStockDisplay) }}
                            </td>
                            @endif
                        </tr>

                        @if($selectedUbs == 'all')
                        {{-- ROW DETAIL (DROPDOWN) - HANYA MUNCUL DI OPSI SEMUA GUDANG --}}
                        <tr x-show="open" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                            style="display: none;">
                            <td colspan="4" class="border p-0 bg-gray-50">
                                <div class="p-4 bg-white border-l-4 border-blue-500 m-2 shadow-inner">
                                    <table class="w-full text-xs border">
                                        <thead class="bg-gray-100 text-gray-600">
                                            <tr>
                                                <th class="border px-3 py-1 text-left">No. Nota</th>
                                                <th class="border px-3 py-1 text-left">Merk</th>
                                                <th class="border px-3 py-1 text-left">Supplier</th>
                                                <th class="border px-3 py-1 text-center">Masuk</th>
                                                <th class="border px-3 py-1 text-center text-blue-700">Sisa</th>
                                                <th class="border px-3 py-1 text-center">Satuan</th>
                                                <th class="border px-3 py-1 text-right">Harga Satuan</th>
                                                <th class="border px-3 py-1 text-right">Harga Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($barang->notaDetails as $detail)
                                            @php
                                            $dMasuk = ($detail->jumlah_base ?? 0) / $konversiRate;
                                            $dSisa = ($detail->jumlah_sisa ?? 0) / $konversiRate;
                                            $dHargaUnit = (($detail->harga_total ?? 0) / max($detail->jumlah_base ?? 1, 1)) * $konversiRate;
                                            $dHargaTotal = (($detail->harga_total ?? 0) / max($detail->jumlah_base ?? 1, 1)) * ($detail->jumlah_sisa ?? 0);
                                            @endphp
                                            <tr class="hover:bg-yellow-50 border-b border-gray-100">
                                                <td class="px-3 py-1 font-medium">{{ $detail->nota->nomor_nota }}</td>
                                                <td class="px-3 py-1">{{ $detail->merk }}</td>
                                                <td class="px-3 py-1">{{ $detail->nota->supplier }}</td>
                                                <td class="px-3 py-1 text-center">{{ formatStock($dMasuk) }}</td>
                                                <td class="px-3 py-1 text-center font-bold text-blue-600">{{ formatStock($dSisa) }}</td>
                                                <td class="px-3 py-1 text-center font-medium">{{ $satuanNama }}</td>
                                                <td class="px-3 py-1 text-right tabular-nums">Rp {{ formatStock($dHargaUnit) }}</td>
                                                <td class="px-3 py-1 text-right font-semibold tabular-nums">Rp {{ formatStock($dHargaTotal) }}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="8" class="px-3 py-2 text-center text-gray-400">Tidak ada
                                                    stock FIFO aktif</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        @endif
                    </tbody>
                    @endforeach
                </table>
            </div>



        </div>
    </div>

</div>
<!-- ===== Main Content End ===== -->

@endsection