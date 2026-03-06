@extends('layouts.app')

@section('pageActive', 'StockBarang')

@section('content')
    <!-- ===== Main Content Start ===== -->
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: 'StockBarang' }">
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
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        Stock Barang
                    </h3>

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

                                {{-- Opsi HUB (All) default --}}
                                <option value="all" {{ request('ubs_id', 'all') == 'all' ? 'selected' : '' }}>
                                    HUB (Pusat)
                                </option>
                                @foreach ($ubsData as $ubs)
                                    <option value="{{ $ubs->id }}" {{ request('ubs_id') == $ubs->id ? 'selected' : '' }}>
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
                    </div>
                </form>




                <div class="w-full max-h-[600px] overflow-auto border border-gray-300 rounded-lg shadow-sm">
                    <table class="min-w-max w-full border-collapse text-sm">

                        <thead class="bg-gray-800 text-white sticky top-0 z-10">
                            <tr>
                                <th class="border px-3 py-2 text-left whitespace-nowrap w-[20%]">Kode Barang</th>
                                <th class="border px-3 py-2 text-left whitespace-nowrap w-[50%]">Nama Barang</th>
                                <th class="border px-3 py-2 text-center whitespace-nowrap w-[15%]">Total Stock</th>
                                <th class="border px-3 py-2 text-center whitespace-nowrap w-[15%]">Minimal Stock</th>
                            </tr>
                        </thead>

                        {{--
                        PENTING: Kita menggunakan @foreach di luar <tbody>
                            dan membuat satu
                        <tbody> per barang agar x-data bekerja mandiri per baris
                            --}}
                            @foreach($stocks as $barang)
                                <tbody x-data="{ open: false }" class="border-b">
                                    {{-- ROW UTAMA --}}
                                    <tr @click="open = !open"
                                        class="bg-blue-50 font-bold text-gray-900 border-t-2 border-blue-200 cursor-pointer hover:bg-blue-100 transition-colors">
                                        <td class="border px-3 py-2 text-blue-800">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 transition-transform duration-200"
                                                    :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                                {{ $barang->kode_barang }}
                                            </div>
                                        </td>
                                        <td class="border px-3 py-2 text-blue-800">
                                            {{ $barang->nama_barang }}
                                        </td>
                                        <td class="border px-3 py-2 text-center">
                                            {{ ($barang->stockHub->jumlah_stock ?? 0) + 0 }}
                                        </td>
                                        <td class="border px-3 py-2 text-center">
                                            {{ ($barang->stockHub->minimal_stock ?? 0) + 0 }}
                                        </td>
                                    </tr>

                                    {{-- ROW DETAIL (DROPDOWN) --}}
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
                                                            <th class="border px-3 py-1 text-right">Harga Satuan</th>
                                                            <th class="border px-3 py-1 text-right">Harga Total</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($barang->notaDetails as $detail)
                                                            <tr class="hover:bg-yellow-50 border-b border-gray-100">
                                                                <td class="px-3 py-1 font-medium">{{ $detail->nota->nomor_nota }}</td>
                                                                <td class="px-3 py-1">{{ $detail->merk }}</td>
                                                                <td class="px-3 py-1">{{ $detail->nota->supplier }}</td>
                                                                <td class="px-3 py-1 text-center">{{ $detail->jumlah_masuk + 0 }}</td>
                                                                <td class="px-3 py-1 text-center font-bold text-blue-600">
                                                                    {{ $detail->jumlah_sisa + 0 }}</td>
                                                                <td class="px-3 py-1 text-right tabular-nums">Rp
                                                                    {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                                                                <td class="px-3 py-1 text-right font-semibold tabular-nums">
                                                                    Rp
                                                                    {{ number_format($detail->jumlah_sisa * $detail->harga_satuan, 0, ',', '.') }}
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="7" class="px-3 py-2 text-center text-gray-400">Tidak ada
                                                                    stock FIFO aktif</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
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

@endsection