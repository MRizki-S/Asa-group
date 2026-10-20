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
            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 dark:border-gray-800 pb-4">
                <div>
                    <h3 class="text-base sm:text-lg font-bold text-gray-800 dark:text-white flex flex-wrap items-center gap-2">
                        <span>Stock Barang</span>
                        <span class="px-2.5 py-0.5 text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300 rounded-full">
                            {{ $titleGudang }}
                        </span>
                    </h3>
                </div>

                <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
                    @can('gudang.stock-barang.freeze-stock')
                        @php $isFreeze = \App\Models\AppSetting::isFreeze(); @endphp
                        <form id="form-toggle-freeze" action="{{ route('gudang.stockBarang.toggleFreeze') }}" method="POST" class="w-full sm:w-auto">
                            @csrf
                            @if($isFreeze)
                                <button type="button" onclick="confirmToggleFreeze(true)" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-3.5 sm:px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs sm:text-sm font-bold rounded-xl transition-all shadow-xs active:scale-95 whitespace-nowrap">
                                    <i class="fa-solid fa-snowflake"></i>
                                    <span>Nonaktifkan Freeze</span>
                                </button>
                            @else
                                <button type="button" onclick="confirmToggleFreeze(false)" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-3.5 sm:px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs sm:text-sm font-medium rounded-xl transition-all shadow-xs active:scale-95 whitespace-nowrap">
                                    <i class="fa-regular fa-snowflake"></i>
                                    <span>Freeze Stok Opname</span>
                                </button>
                            @endif
                        </form>
                    @endcan

                    @can('gudang.transfer-stock.read')
                        <a href="{{ route('gudang.transferStockBarang.daftar.index') }}"
                            target="_blank"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-3.5 sm:px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-medium rounded-xl transition-all shadow-xs active:scale-95 whitespace-nowrap focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-white">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                            </svg>
                            <span>Transfer Stock</span>
                        </a>
                    @endcan
                </div>
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
                    <!-- Multi-Select Pilih Gudang -->
                    <div class="flex-1 min-w-[260px] relative" @click.away="open = false" x-data="{
                        open: false,
                        selected: {{ json_encode($selectedUbs) }},
                        allCodes: {{ json_encode($ubsData->pluck('kode_ubs')->toArray()) }},
                        ubsMap: {
                            @foreach($ubsData as $u)
                                '{{ $u->kode_ubs }}': '{{ $u->nama_ubs }}',
                            @endforeach
                        },
                        isAllSelected() {
                            return this.allCodes.length > 0 && this.selected.length === this.allCodes.length;
                        },
                        toggleAll() {
                            if (this.isAllSelected()) {
                                this.selected = [];
                            } else {
                                this.selected = [...this.allCodes];
                            }
                        },
                        getLabel() {
                            if (this.isAllSelected()) {
                                return 'Semua Gudang (' + this.allCodes.length + ')';
                            }
                            if (this.selected.length === 0) {
                                return 'Pilih Gudang (0)';
                            }
                            if (this.selected.length === 1) {
                                return (this.ubsMap[this.selected[0]] || this.selected[0]);
                            }
                            return this.selected.join(' + ') + ' (' + this.selected.length + ' Gudang)';
                        }
                    }">
                        <label class="block mb-1.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                            Pilih Gudang
                        </label>

                        <button @click="open = !open" type="button"
                            class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white text-sm rounded-lg p-2.5 flex items-center justify-between text-left focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-xs">
                            <span class="truncate font-medium" x-text="getLabel()"></span>
                            <svg class="w-4 h-4 text-gray-500 ms-2 transition-transform duration-200 shrink-0"
                                :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        {{-- Dropdown Checkbox Menu --}}
                        <div x-show="open" x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            style="display: none;"
                            class="absolute left-0 z-30 mt-1 w-full min-w-[280px] bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 p-2 space-y-1">
                            
                            {{-- Checkbox Master: Pilih Semua --}}
                            <label class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/30 cursor-pointer font-bold text-xs text-gray-800 dark:text-gray-200 border-b border-gray-100 dark:border-gray-700 pb-2 mb-1">
                                <input type="checkbox" :checked="isAllSelected()" @change="toggleAll()"
                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                                <span class="flex-1">Pilih Semua Gudang</span>
                                <span class="text-[10px] text-gray-400 font-normal" x-text="'(' + selected.length + '/' + allCodes.length + ')'"></span>
                            </label>

                            {{-- Checkbox Tiap UBS --}}
                            @foreach ($ubsData as $ubs)
                            <label class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/60 cursor-pointer text-xs text-gray-700 dark:text-gray-300">
                                <input type="checkbox" name="ubs_id[]" value="{{ $ubs->kode_ubs }}"
                                    x-model="selected"
                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                                <div class="flex-1 flex items-center justify-between">
                                    <span class="font-medium">{{ $ubs->nama_ubs }}</span>
                                    <span class="px-1.5 py-0.2 rounded font-mono font-bold text-[10px] bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                        {{ $ubs->kode_ubs }}
                                    </span>
                                </div>
                            </label>
                            @endforeach

                            <div class="pt-2 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center px-1">
                                <span class="text-[11px] text-gray-400" x-text="selected.length + ' dipilih'"></span>
                                <button type="button" @click="open = false" class="px-2.5 py-1 text-xs font-semibold text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/40 rounded">
                                    Selesai
                                </button>
                            </div>
                        </div>
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




            <div class="w-full max-h-[600px] overflow-auto border border-gray-300 dark:border-gray-700 rounded-lg shadow-sm">
                <table class="w-full min-w-full border-collapse text-sm">

                    <thead class="bg-gray-800 text-white sticky top-0 z-10">
                        <tr>
                            <th class="border px-3 py-2.5 text-left whitespace-nowrap w-44 md:w-48">Kode Barang</th>
                            <th class="border px-3 py-2.5 text-left min-w-[200px]">Nama Barang</th>
                            <th class="border px-3 py-2.5 text-center whitespace-nowrap w-24 md:w-28">Satuan</th>
                            <th class="border px-3 py-2.5 text-center whitespace-nowrap w-28 md:w-36">Total Stock</th>
                            @if(count($selectedUbs) === 1)
                            <th class="border px-3 py-2.5 text-center whitespace-nowrap w-28 md:w-36">Minimal Stock</th>
                            @endif
                        </tr>
                    </thead>

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

                    // Total stok dari gudang-gudang yang terpilih
                    $stockVal = $barang->stock->sum('jumlah_stock');
                    $minStockVal = 0;

                    if (count($selectedUbs) === 1) {
                        $s = $barang->stock->first();
                        $minStockVal = $s->minimal_stock ?? 0;
                    }

                    // Divide by conversion rate
                    $stockDisplay = $stockVal / $konversiRate;
                    $minStockDisplay = $minStockVal / $konversiRate;

                    // Cek apakah stock di bawah minimal (hanya relevan saat filter 1 gudang tertentu)
                    $isBelowMin = count($selectedUbs) === 1 && $minStockVal > 0 && $stockVal < $minStockVal;
                    $rowBg = $isBelowMin
                        ? 'bg-red-50 border-red-200 hover:bg-red-100 dark:bg-red-950/20 dark:hover:bg-red-900/30'
                        : 'bg-blue-50 border-blue-200 hover:bg-blue-100 dark:bg-gray-800/60 dark:hover:bg-gray-800';
                    $textColor = $isBelowMin ? 'text-red-700 dark:text-red-400' : 'text-blue-800 dark:text-blue-300';
                    @endphp

                    <tbody x-data="{ open: false }" class="border-b">
                        {{-- ROW UTAMA --}}
                        <tr @click="open = !open"
                            class="font-bold text-gray-900 dark:text-white border-t-2 cursor-pointer transition-colors {{ $rowBg }}">
                            <td class="border px-3 py-2 text-left {{ $textColor }} w-44 md:w-48">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <svg class="w-4 h-4 transition-transform duration-200 shrink-0 text-blue-600 dark:text-blue-400"
                                        :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                    <span class="font-mono text-xs sm:text-sm">{{ $barang->kode_barang }}</span>
                                    @if($isBelowMin)
                                        <span class="inline-flex items-center text-[9px] font-black uppercase tracking-wider bg-red-100 text-red-600 border border-red-300 px-1 py-0.2 rounded">
                                            ⚠ Rendah
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="border px-3 py-2 {{ $textColor }}">
                                <div class="font-medium text-xs sm:text-sm leading-snug">
                                    {{ $barang->nama_barang }}
                                </div>
                            </td>
                            <td class="border px-3 py-2 text-center text-gray-700 dark:text-gray-300 text-xs sm:text-sm w-24 md:w-28 whitespace-nowrap">
                                {{ $satuanNama }}
                            </td>
                            <td class="border px-3 py-2 text-center font-bold text-xs sm:text-sm w-28 md:w-36 whitespace-nowrap {{ $isBelowMin ? 'text-red-600' : '' }}">
                                {{ formatStock($stockDisplay) }}
                            </td>
                            @if(count($selectedUbs) === 1)
                            <td class="border px-3 py-2 text-center italic text-xs sm:text-sm w-28 md:w-36 whitespace-nowrap {{ $isBelowMin ? 'text-red-500 font-semibold' : 'text-gray-500' }}">
                                {{ formatStock($minStockDisplay) }}
                            </td>
                            @endif
                        </tr>

                        {{-- ROW DETAIL (DROPDOWN) - RINCIAN PER GUDANG TERPILIH & DETAIL FIFO (COMPACT & EFISIEN) --}}
                        <tr x-show="open" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                            style="display: none;">
                            <td colspan="{{ count($selectedUbs) === 1 ? 5 : 4 }}" class="border p-0 bg-slate-50/60 dark:bg-gray-900/40">
                                <div class="px-3 py-2 bg-white dark:bg-gray-800/90 border-l-4 border-blue-500 m-1.5 rounded-r-lg shadow-2xs space-y-2">
                                    
                                    {{-- 1. RINCIAN STOK PER GUDANG TERPILIH (INLINE PILLS - SANGAT EFISIEN) --}}
                                    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                                        <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center gap-1 me-1 shrink-0">
                                            <i class="fa-solid fa-warehouse text-blue-500 text-xs"></i> Stok Gudang:
                                        </span>

                                        @foreach($selectedDbUbs as $ubs)
                                            @php
                                                $stkGudang = $barang->stock->firstWhere('ubs_id', $ubs->id);
                                                $stkQty = ($stkGudang->jumlah_stock ?? 0) / $konversiRate;
                                                $stkMin = ($stkGudang->minimal_stock ?? 0) / $konversiRate;
                                                $isGudangLow = $stkMin > 0 && $stkQty < $stkMin;
                                            @endphp
                                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border text-xs {{ $isGudangLow ? 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/30 dark:border-red-800 dark:text-red-300' : 'bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200' }}">
                                                <span class="font-bold text-[11px] text-gray-600 dark:text-gray-400">{{ $ubs->nama_ubs }} ({{ $ubs->kode_ubs }}):</span>
                                                <span class="font-mono font-bold {{ $isGudangLow ? 'text-red-600' : 'text-blue-600 dark:text-blue-400' }}">{{ formatStock($stkQty) }} {{ $satuanNama }}</span>
                                                @if($stkMin > 0)
                                                    <span class="text-[10px] {{ $isGudangLow ? 'text-red-500 font-bold' : 'text-gray-400' }}" title="Minimal Stok">(Min: {{ formatStock($stkMin) }})</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>

                                    {{-- 2. TABEL FIFO NOTA MASUK (COLLAPSIBLE / RINGKAS) --}}
                                    @if($isAll && $barang->notaDetails && $barang->notaDetails->count() > 0)
                                        <div class="pt-1.5 border-t border-gray-100 dark:border-gray-700" x-data="{ showFifo: false }">
                                            <div class="flex items-center justify-between">
                                                <button type="button" @click.stop="showFifo = !showFifo" 
                                                    class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1.5 cursor-pointer">
                                                    <i class="fa-solid fa-file-invoice text-emerald-600 text-[10px]"></i>
                                                    <span x-text="showFifo ? 'Sembunyikan Rincian Nota FIFO ({{ $barang->notaDetails->count() }}) ▲' : 'Lihat Rincian Nota FIFO ({{ $barang->notaDetails->count() }} Nota) ▼'"></span>
                                                </button>
                                                <span class="text-[10px] text-gray-400">Total: {{ $barang->notaDetails->count() }} Nota Aktif</span>
                                            </div>

                                            <div x-show="showFifo" x-transition style="display: none;" class="mt-1.5 overflow-x-auto max-h-48 rounded border border-gray-200 dark:border-gray-700">
                                                <table class="w-full min-w-[650px] text-[11px] border-collapse bg-white dark:bg-gray-900">
                                                    <thead class="bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300 font-semibold sticky top-0">
                                                        <tr>
                                                            <th class="border px-2 py-1 text-left">No. Nota</th>
                                                            <th class="border px-2 py-1 text-left">Merk</th>
                                                            <th class="border px-2 py-1 text-left">Supplier</th>
                                                            <th class="border px-2 py-1 text-center">Masuk</th>
                                                            <th class="border px-2 py-1 text-center text-blue-700 dark:text-blue-400">Sisa</th>
                                                            <th class="border px-2 py-1 text-center">Satuan</th>
                                                            <th class="border px-2 py-1 text-right">Harga Satuan</th>
                                                            <th class="border px-2 py-1 text-right">Harga Total Nota</th>
                                                            <th class="border px-2 py-1 text-right">Nilai Sisa</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($barang->notaDetails as $detail)
                                                        @php
                                                         $dMasuk = ($detail->jumlah_base ?? 0) / $konversiRate;
                                                         $dSisa = ($detail->jumlah_sisa ?? 0) / $konversiRate;
                                                         $dHargaUnit = (($detail->harga_total ?? 0) / max($detail->jumlah_base ?? 1, 1)) * $konversiRate;
                                                         $dHargaTotalNota = $detail->harga_total ?? 0;
                                                         $dNilaiSisa = (($detail->harga_total ?? 0) / max($detail->jumlah_base ?? 1, 1)) * ($detail->jumlah_sisa ?? 0);
                                                        @endphp
                                                        <tr class="hover:bg-yellow-50/50 dark:hover:bg-yellow-950/20 border-b border-gray-100 dark:border-gray-800 text-[11px]">
                                                            <td class="px-2 py-0.5 font-medium">{{ $detail->nota->nomor_nota ?? '-' }}</td>
                                                            <td class="px-2 py-0.5">{{ !empty($detail->merk) ? $detail->merk : '-' }}</td>
                                                            <td class="px-2 py-0.5 truncate max-w-[150px]">
                                                                {{ $detail->nota->supplier->nama_supplier ?? '-' }}
                                                            </td>
                                                            <td class="px-2 py-0.5 text-center font-mono">{{ formatStock($dMasuk) }}</td>
                                                            <td class="px-2 py-0.5 text-center font-mono font-bold text-blue-600 dark:text-blue-400">{{ formatStock($dSisa) }}</td>
                                                            <td class="px-2 py-0.5 text-center">{{ $satuanNama }}</td>
                                                            <td class="px-2 py-0.5 text-right font-mono">Rp {{ formatStock($dHargaUnit) }}</td>
                                                            <td class="px-2 py-0.5 text-right font-mono">Rp {{ formatStock($dHargaTotalNota) }}</td>
                                                            <td class="px-2 py-0.5 text-right font-mono font-bold text-blue-700 dark:text-blue-400">Rp {{ formatStock($dNilaiSisa) }}</td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endif

                                </div>
                            </td>
                        </tr>
                    </tbody>
                    @endforeach
                </table>
            </div>



        </div>
    </div>

</div>
<!-- ===== Main Content End ===== -->

<script>
function confirmToggleFreeze(isCurrentFreeze) {
    const titleText = isCurrentFreeze ? 'Nonaktifkan Freeze Mode?' : 'Aktifkan Freeze Mode?';
    const bodyText = isCurrentFreeze 
        ? 'Seluruh transaksi order, retur, transfer, dan edit barang akan dibuka kembali.'
        : 'Seluruh transaksi order, retur, transfer stok, dan edit barang sementara akan DIBEKUKAN selama pencocokan stok opname.';

    Swal.fire({
        title: titleText,
        text: bodyText,
        icon: isCurrentFreeze ? 'question' : 'warning',
        showCancelButton: true,
        confirmButtonColor: isCurrentFreeze ? '#10B981' : '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: isCurrentFreeze ? 'Ya, Buka Transaksi' : 'Ya, Freeze Sekarang!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('form-toggle-freeze').submit();
        }
    });
}
</script>

@endsection
