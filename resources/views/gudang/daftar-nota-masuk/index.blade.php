@extends('layouts.app')

@section('pageActive', 'DaftarNotaMasuk')

@section('content')
<!-- ===== Main Content Start ===== -->
<div class="mx-auto max-w-[--breakpoint-2xl] p-3 sm:p-4 md:p-6">

    <!-- Breadcrumb Start -->
    <div x-data="{ pageName: 'DaftarNotaMasuk' }">
        @include('partials.breadcrumb')
    </div>
    <!-- Breadcrumb End -->

    {{-- Alert Error Validasi --}}
    @if ($errors->any())
    <div class="flex p-4 mb-4 text-sm text-red-800 rounded-xl bg-red-50 dark:bg-gray-800 dark:text-red-400 border border-red-200 dark:border-red-900 shadow-xs"
        role="alert">
        <svg class="shrink-0 inline w-4 h-4 me-3 mt-[2px]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
            fill="currentColor" viewBox="0 0 20 20">
            <path
                d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
        </svg>
        <span class="sr-only">Danger</span>
        <div>
            <span class="font-bold">Terjadi kesalahan validasi:</span>
            <ul class="mt-1.5 list-disc list-inside text-xs">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <div class="space-y-4 sm:space-y-6" x-data="{ mainTab: 'supplier', supplierTab: 'all' }">
        
        <div class="rounded-2xl border border-gray-200 p-4 sm:p-6 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow-xs">
            
            {{-- Header Title & Top Action --}}
            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 dark:border-gray-800 pb-4">
                <div>
                    <h3 class="text-base sm:text-lg font-bold text-gray-800 dark:text-white flex flex-wrap items-center gap-2">
                        <span>Daftar Nota Barang Masuk</span>
                        <span class="px-2.5 py-0.5 text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300 rounded-full">
                            {{ $notasSupplier->count() + $notasInternal->count() }} Total Nota
                        </span>
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Daftar nota barang masuk yang telah berstatus <strong>Posted (ACC)</strong> ke stok gudang.
                    </p>
                </div>

                @can('gudang.nota-masuk.draft-nota-masuk.read')
                <div class="flex items-center gap-2">
                    <a href="{{ route('gudang.draftNotaMasuk.index') }}"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-yellow-400 hover:bg-yellow-500 text-yellow-950 font-bold px-4 py-2 text-xs sm:text-sm transition-all shadow-sm active:scale-95">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        <span>Draft Nota Masuk</span>
                    </a>
                </div>
                @endcan
            </div>

            {{-- Form Filter Card (Dedicated responsive section) --}}
            <div class="mb-6 bg-gray-50/80 dark:bg-gray-800/40 p-3.5 sm:p-4 rounded-2xl border border-gray-200/80 dark:border-gray-800">
                <form method="GET" action="{{ route('gudang.daftarNotaMasuk.index') }}" class="flex flex-wrap items-end gap-3">
                    
                    {{-- Filter Bulan --}}
                    <div class="flex-1 min-w-[130px] sm:min-w-[150px]">
                        <label class="block text-[11px] sm:text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-1.5">Bulan</label>
                        <select name="bulan" class="w-full rounded-xl border-gray-300 bg-white text-gray-800 text-xs sm:text-sm py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300 shadow-2xs">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ (int)$bulan === $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Tahun --}}
                    <div class="w-28 sm:w-32">
                        <label class="block text-[11px] sm:text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-1.5">Tahun</label>
                        <select name="tahun" class="w-full rounded-xl border-gray-300 bg-white text-gray-800 text-xs sm:text-sm py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300 shadow-2xs">
                            @php $currentYear = now()->year; @endphp
                            @foreach(range($currentYear - 3, $currentYear + 1) as $y)
                                <option value="{{ $y }}" {{ (int)$tahun === $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Per Hari (Flatpickr) --}}
                    <div class="flex-1 min-w-[160px] sm:min-w-[190px]">
                        <label class="block text-[11px] sm:text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-1.5">Filter Per Hari (Opsional)</label>
                        <div class="relative" x-data="{ tanggal: '{{ request('tanggal') }}' }">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                                </svg>
                            </div>
                            <input type="text" name="tanggal" x-ref="tanggal" x-init="flatpickr($refs.tanggal, {
                                    defaultDate: tanggal,
                                    dateFormat: 'Y-m-d',
                                    altInput: true,
                                    altFormat: 'd-m-Y',
                                    allowInput: true
                                })"
                                placeholder="Semua Tanggal"
                                class="bg-white border border-gray-300 text-gray-900 text-xs sm:text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full pl-9 p-2 dark:bg-gray-900 dark:border-gray-700 dark:text-white shadow-2xs">
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <button type="submit"
                            class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2 text-xs sm:text-sm font-semibold text-white hover:bg-blue-700 transition focus:ring-4 focus:ring-blue-300 active:scale-95 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                            <span>Filter</span>
                        </button>

                        <a href="{{ route('gudang.daftarNotaMasuk.index') }}"
                            class="inline-flex items-center justify-center rounded-xl bg-gray-200 px-3.5 py-2 text-xs sm:text-sm font-medium text-gray-700 hover:bg-gray-300 transition dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- ====== TAB NAVIGASI UTAMA (SUPPLIER vs INTERNAL) ====== -->
            <div class="flex border-b border-gray-200 dark:border-gray-700 mb-4 gap-2 overflow-x-auto pb-1 custom-scrollbar">
                <button @click="mainTab = 'supplier'"
                    :class="mainTab === 'supplier' ? 'border-b-2 border-blue-600 text-blue-600 font-bold dark:border-blue-400 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
                    class="py-2.5 px-3 sm:px-4 text-xs sm:text-sm font-semibold flex items-center gap-2 transition-colors whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    <span>Nota Supplier ({{ $notasSupplier->count() }})</span>
                </button>
                <button @click="mainTab = 'internal'"
                    :class="mainTab === 'internal' ? 'border-b-2 border-blue-600 text-blue-600 font-bold dark:border-blue-400 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
                    class="py-2.5 px-3 sm:px-4 text-xs sm:text-sm font-semibold flex items-center gap-2 transition-colors whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                    <span>Nota Internal / Lainnya ({{ $notasInternal->count() }})</span>
                </button>
            </div>

            <!-- ====== SUB TAB UNTUK NOTA SUPPLIER (ALL / HUTANG / LUNAS) ====== -->
            <div x-show="mainTab === 'supplier'" class="flex flex-wrap gap-2 mb-4 bg-gray-100 dark:bg-gray-800/60 p-1.5 rounded-xl w-fit">
                <button @click="supplierTab = 'all'"
                    :class="supplierTab === 'all' ? 'bg-white text-gray-900 font-bold shadow-sm dark:bg-gray-700 dark:text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
                    class="px-3 sm:px-3.5 py-1.5 text-xs font-semibold rounded-lg transition-all duration-200">
                    Semua ({{ $notasSupplier->count() }})
                </button>
                <button @click="supplierTab = 'hutang'"
                    :class="supplierTab === 'hutang' ? 'bg-yellow-500 text-yellow-950 font-bold shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
                    class="px-3 sm:px-3.5 py-1.5 text-xs font-semibold rounded-lg transition-all duration-200">
                    Hutang ({{ $notasSupplierHutang->count() }})
                </button>
                <button @click="supplierTab = 'lunas'"
                    :class="supplierTab === 'lunas' ? 'bg-green-600 text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
                    class="px-3 sm:px-3.5 py-1.5 text-xs font-semibold rounded-lg transition-all duration-200">
                    Lunas ({{ $notasSupplierLunas->count() }})
                </button>
            </div>

            <!-- ====== TABEL NOTA SUPPLIER (ALL) ====== -->
            <div x-show="mainTab === 'supplier' && supplierTab === 'all'" x-transition:enter="transition ease-out duration-200">
                <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800 custom-scrollbar">
                    <table id="table-supplier-all" class="w-full text-xs text-left text-gray-700 dark:text-gray-300 border-collapse min-w-[700px]">
                        <thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 uppercase font-bold text-[11px] sm:text-xs">
                            <tr>
                                <th class="px-3 py-3 border-b border-gray-200 dark:border-gray-700">Nomor Nota</th>
                                <th class="px-3 py-3 border-b border-gray-200 dark:border-gray-700">Tanggal Masuk</th>
                                <th class="px-3 py-3 border-b border-gray-200 dark:border-gray-700">Supplier</th>
                                <th class="px-3 py-3 border-b border-gray-200 dark:border-gray-700">Daftar Barang</th>
                                <th class="px-3 py-3 border-b border-gray-200 dark:border-gray-700">Gudang Tujuan</th>
                                <th class="px-3 py-3 border-b border-gray-200 dark:border-gray-700">Cara Bayar</th>
                                <th class="px-3 py-3 border-b border-gray-200 dark:border-gray-700">Tanggal Posting</th>
                                @can('gudang.nota-masuk.daftar-nota-masuk.detail')
                                <th class="px-3 py-3 border-b border-gray-200 dark:border-gray-700 text-center">Aksi</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($notasSupplier as $nota)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800/60 transition-colors">
                                <td class="px-3 py-3 font-mono font-bold text-gray-900 whitespace-nowrap dark:text-white">{{ $nota->nomor_nota }}</td>
                                <td class="px-3 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ \Carbon\Carbon::parse($nota->tanggal_nota)->format('d-M-Y') }}</td>
                                <td class="px-3 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ $nota->supplier->nama_supplier ?? '-' }}</td>
                                <td class="px-2.5 py-2 min-w-[180px]">
                                    <div class="max-h-20 overflow-y-auto custom-scrollbar space-y-0.5 pr-0.5">
                                        @foreach ($nota->details as $detail)
                                            <div class="text-[11px] font-normal leading-tight text-gray-700 dark:text-gray-300 flex items-center justify-between gap-1.5 border-b border-gray-100/70 dark:border-gray-800/30 pb-0.5 last:border-0">
                                                <span>{{ $detail->barang->nama_barang ?? '-' }}</span>
                                                <span class="text-[10px] text-gray-500 dark:text-gray-400 font-medium whitespace-nowrap shrink-0">
                                                    {{ (float)$detail->jumlah_input + 0 }} {{ $detail->satuan->nama ?? '' }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-3 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    @if ($nota->stock_type === 'HUB')
                                        <span class="px-2 py-0.5 bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 rounded-md text-xs font-bold">Gudang HUB</span>
                                    @else
                                        {{ $nota->ubs->nama_ubs ?? '-' }}
                                    @endif
                                </td>
                                <td class="px-3 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    @if($nota->cara_bayar)
                                        <span class="px-2 py-0.5 rounded-md text-xs font-bold {{ $nota->cara_bayar === 'cash' ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300' }}">
                                            {{ strtoupper($nota->cara_bayar) }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap">
                                    @if($nota->posted_at)
                                        <span class="text-xs font-medium text-green-700 dark:text-green-400">{{ \Carbon\Carbon::parse($nota->posted_at)->format('d-M-Y') }}</span>
                                        <span class="text-[10px] text-gray-400 dark:text-gray-500 ml-1">{{ \Carbon\Carbon::parse($nota->posted_at)->format('H:i') }}</span>
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                </td>
                                @can('gudang.nota-masuk.daftar-nota-masuk.detail')
                                <td class="px-3 py-3 text-center">
                                    <a href="{{ route('gudang.daftarNotaMasuk.show', $nota->nomor_nota) }}"
                                        class="inline-flex items-center gap-1 text-xs font-semibold text-blue-700 bg-blue-100 hover:bg-blue-200 dark:bg-blue-900/50 dark:text-blue-300 dark:hover:bg-blue-800 px-2.5 py-1.5 rounded-lg transition-colors active:scale-95">
                                        Detail
                                    </a>
                                </td>
                                @endcan
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Ringkasan Total Tab Semua --}}
                <div class="mt-5 rounded-2xl border border-gray-200 bg-gray-50/80 dark:border-gray-800 dark:bg-gray-800/40 p-4 sm:p-5">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-3 mb-4 border-b border-gray-200 dark:border-gray-700/60 gap-2">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            </span>
                            <span class="text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                                Total Rekapitulasi Nota Masuk (ACC)
                            </span>
                            <span class="text-xs px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 font-semibold">
                                {{ $periodeLabel }}
                            </span>
                        </div>
                        <div class="text-xs sm:text-sm font-semibold text-gray-600 dark:text-gray-300">
                            Total Keseluruhan: <span class="font-extrabold text-blue-600 dark:text-blue-400 text-sm sm:text-base">Rp {{ number_format($totalSemua, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                        {{-- Transaksi --}}
                        <div class="flex items-center gap-3.5 bg-white dark:bg-gray-900/80 p-3.5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-2xs">
                            <div class="p-2.5 rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <div>
                                <p class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Transaksi</p>
                                <p class="text-base sm:text-lg font-extrabold text-gray-800 dark:text-white">
                                    {{ $notasSupplier->count() }} <span class="text-xs font-normal text-gray-500">Nota</span>
                                </p>
                            </div>
                        </div>

                        {{-- Total Hutang --}}
                        <div class="flex items-center gap-3.5 bg-white dark:bg-gray-900/80 p-3.5 rounded-xl border border-yellow-200 dark:border-yellow-800/50 shadow-2xs">
                            <div class="p-2.5 rounded-xl bg-yellow-50 text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <p class="text-[11px] font-bold text-yellow-600 dark:text-yellow-400 uppercase tracking-wider">Total Hutang</p>
                                <p class="text-base sm:text-lg font-extrabold text-yellow-700 dark:text-yellow-400">
                                    Rp {{ number_format($totalHutang, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>

                        {{-- Total Cash --}}
                        <div class="flex items-center gap-3.5 bg-white dark:bg-gray-900/80 p-3.5 rounded-xl border border-green-200 dark:border-green-800/50 shadow-2xs">
                            <div class="p-2.5 rounded-xl bg-green-50 text-green-600 dark:bg-green-900/30 dark:text-green-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <p class="text-[11px] font-bold text-green-600 dark:text-green-400 uppercase tracking-wider">Total Cash</p>
                                <p class="text-base sm:text-lg font-extrabold text-green-700 dark:text-green-400">
                                    Rp {{ number_format($totalCash, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ====== TABEL NOTA SUPPLIER (HUTANG) ====== -->
            <div x-show="mainTab === 'supplier' && supplierTab === 'hutang'" x-transition:enter="transition ease-out duration-200" style="display: none;">
                <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800 custom-scrollbar">
                    <table id="table-supplier-hutang" class="w-full text-xs text-left text-gray-700 dark:text-gray-300 border-collapse min-w-[700px]">
                        <thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 uppercase font-bold text-[11px] sm:text-xs">
                            <tr>
                                <th class="px-3 py-3 border-b border-gray-200 dark:border-gray-700">Nomor Nota</th>
                                <th class="px-3 py-3 border-b border-gray-200 dark:border-gray-700">Tanggal Masuk</th>
                                <th class="px-3 py-3 border-b border-gray-200 dark:border-gray-700">Supplier</th>
                                <th class="px-3 py-3 border-b border-gray-200 dark:border-gray-700">Daftar Barang</th>
                                <th class="px-3 py-3 border-b border-gray-200 dark:border-gray-700">Gudang Tujuan</th>
                                <th class="px-3 py-3 border-b border-gray-200 dark:border-gray-700">Cara Bayar</th>
                                <th class="px-3 py-3 border-b border-gray-200 dark:border-gray-700">Tanggal Posting</th>
                                @can('gudang.nota-masuk.daftar-nota-masuk.detail')
                                <th class="px-3 py-3 border-b border-gray-200 dark:border-gray-700 text-center">Aksi</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($notasSupplierHutang as $nota)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800/60 transition-colors">
                                <td class="px-3 py-3 font-mono font-bold text-gray-900 whitespace-nowrap dark:text-white">{{ $nota->nomor_nota }}</td>
                                <td class="px-3 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ \Carbon\Carbon::parse($nota->tanggal_nota)->format('d-M-Y') }}</td>
                                <td class="px-3 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ $nota->supplier->nama_supplier ?? '-' }}</td>
                                <td class="px-2.5 py-2 min-w-[180px]">
                                    <div class="max-h-20 overflow-y-auto custom-scrollbar space-y-0.5 pr-0.5">
                                        @foreach ($nota->details as $detail)
                                            <div class="text-[11px] font-normal leading-tight text-gray-700 dark:text-gray-300 flex items-center justify-between gap-1.5 border-b border-gray-100/70 dark:border-gray-800/30 pb-0.5 last:border-0">
                                                <span>{{ $detail->barang->nama_barang ?? '-' }}</span>
                                                <span class="text-[10px] text-gray-500 dark:text-gray-400 font-medium whitespace-nowrap shrink-0">
                                                    {{ (float)$detail->jumlah_input + 0 }} {{ $detail->satuan->nama ?? '' }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-3 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    @if ($nota->stock_type === 'HUB')
                                        <span class="px-2 py-0.5 bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 rounded-md text-xs font-bold">Gudang HUB</span>
                                    @else
                                        {{ $nota->ubs->nama_ubs ?? '-' }}
                                    @endif
                                </td>
                                <td class="px-3 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300">
                                        HUTANG
                                    </span>
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap">
                                    @if($nota->posted_at)
                                        <span class="text-xs font-medium text-green-700 dark:text-green-400">{{ \Carbon\Carbon::parse($nota->posted_at)->format('d-M-Y') }}</span>
                                        <span class="text-[10px] text-gray-400 dark:text-gray-500 ml-1">{{ \Carbon\Carbon::parse($nota->posted_at)->format('H:i') }}</span>
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                </td>
                                @can('gudang.nota-masuk.daftar-nota-masuk.detail')
                                <td class="px-3 py-3 text-center">
                                    <a href="{{ route('gudang.daftarNotaMasuk.show', $nota->nomor_nota) }}"
                                        class="inline-flex items-center gap-1 text-xs font-semibold text-blue-700 bg-blue-100 hover:bg-blue-200 dark:bg-blue-900/50 dark:text-blue-300 dark:hover:bg-blue-800 px-2.5 py-1.5 rounded-lg transition-colors active:scale-95">
                                        Detail
                                    </a>
                                </td>
                                @endcan
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Ringkasan Total Tab Hutang --}}
                <div class="mt-5 rounded-2xl border border-gray-200 bg-gray-50/80 dark:border-gray-800 dark:bg-gray-800/40 p-4 sm:p-5">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-3 mb-4 border-b border-gray-200 dark:border-gray-700/60 gap-2">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-yellow-100 text-yellow-700 dark:bg-yellow-900/50 dark:text-yellow-300">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </span>
                            <span class="text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                                Total Rekapitulasi Nota Hutang (ACC)
                            </span>
                            <span class="text-xs px-2.5 py-0.5 rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300 font-semibold">
                                {{ $periodeLabel }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        {{-- Transaksi Hutang --}}
                        <div class="flex items-center gap-3.5 bg-white dark:bg-gray-900/80 p-3.5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-2xs">
                            <div class="p-2.5 rounded-xl bg-yellow-50 text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <div>
                                <p class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Transaksi Hutang</p>
                                <p class="text-base sm:text-lg font-extrabold text-gray-800 dark:text-white">
                                    {{ $notasSupplierHutang->count() }} <span class="text-xs font-normal text-gray-500">Nota</span>
                                </p>
                            </div>
                        </div>

                        {{-- Total Hutang --}}
                        <div class="flex items-center gap-3.5 bg-white dark:bg-gray-900/80 p-3.5 rounded-xl border border-yellow-200 dark:border-yellow-800/50 shadow-2xs">
                            <div class="p-2.5 rounded-xl bg-yellow-50 text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <p class="text-[11px] font-bold text-yellow-600 dark:text-yellow-400 uppercase tracking-wider">Total Nominal Hutang</p>
                                <p class="text-base sm:text-lg font-extrabold text-yellow-700 dark:text-yellow-400">
                                    Rp {{ number_format($totalHutang, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ====== TABEL NOTA SUPPLIER (LUNAS) ====== -->
            <div x-show="mainTab === 'supplier' && supplierTab === 'lunas'" x-transition:enter="transition ease-out duration-200" style="display: none;">
                <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800 custom-scrollbar">
                    <table id="table-supplier-lunas" class="w-full text-xs text-left text-gray-700 dark:text-gray-300 border-collapse min-w-[700px]">
                        <thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 uppercase font-bold text-[11px] sm:text-xs">
                            <tr>
                                <th class="px-3 py-3 border-b border-gray-200 dark:border-gray-700">Nomor Nota</th>
                                <th class="px-3 py-3 border-b border-gray-200 dark:border-gray-700">Tanggal Masuk</th>
                                <th class="px-3 py-3 border-b border-gray-200 dark:border-gray-700">Supplier</th>
                                <th class="px-3 py-3 border-b border-gray-200 dark:border-gray-700">Daftar Barang</th>
                                <th class="px-3 py-3 border-b border-gray-200 dark:border-gray-700">Gudang Tujuan</th>
                                <th class="px-3 py-3 border-b border-gray-200 dark:border-gray-700">Cara Bayar</th>
                                <th class="px-3 py-3 border-b border-gray-200 dark:border-gray-700">Tanggal Posting</th>
                                @can('gudang.nota-masuk.daftar-nota-masuk.detail')
                                <th class="px-3 py-3 border-b border-gray-200 dark:border-gray-700 text-center">Aksi</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($notasSupplierLunas as $nota)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800/60 transition-colors">
                                <td class="px-3 py-3 font-mono font-bold text-gray-900 whitespace-nowrap dark:text-white">{{ $nota->nomor_nota }}</td>
                                <td class="px-3 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ \Carbon\Carbon::parse($nota->tanggal_nota)->format('d-M-Y') }}</td>
                                <td class="px-3 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ $nota->supplier->nama_supplier ?? '-' }}</td>
                                <td class="px-2.5 py-2 min-w-[180px]">
                                    <div class="max-h-20 overflow-y-auto custom-scrollbar space-y-0.5 pr-0.5">
                                        @foreach ($nota->details as $detail)
                                            <div class="text-[11px] font-normal leading-tight text-gray-700 dark:text-gray-300 flex items-center justify-between gap-1.5 border-b border-gray-100/70 dark:border-gray-800/30 pb-0.5 last:border-0">
                                                <span>{{ $detail->barang->nama_barang ?? '-' }}</span>
                                                <span class="text-[10px] text-gray-500 dark:text-gray-400 font-medium whitespace-nowrap shrink-0">
                                                    {{ (float)$detail->jumlah_input + 0 }} {{ $detail->satuan->nama ?? '' }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-3 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    @if ($nota->stock_type === 'HUB')
                                        <span class="px-2 py-0.5 bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 rounded-md text-xs font-bold">Gudang HUB</span>
                                    @else
                                        {{ $nota->ubs->nama_ubs ?? '-' }}
                                    @endif
                                </td>
                                <td class="px-3 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300">
                                        {{ $nota->cara_bayar ? strtoupper($nota->cara_bayar) : 'CASH' }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap">
                                    @if($nota->posted_at)
                                        <span class="text-xs font-medium text-green-700 dark:text-green-400">{{ \Carbon\Carbon::parse($nota->posted_at)->format('d-M-Y') }}</span>
                                        <span class="text-[10px] text-gray-400 dark:text-gray-500 ml-1">{{ \Carbon\Carbon::parse($nota->posted_at)->format('H:i') }}</span>
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                </td>
                                @can('gudang.nota-masuk.daftar-nota-masuk.detail')
                                <td class="px-3 py-3 text-center">
                                    <a href="{{ route('gudang.daftarNotaMasuk.show', $nota->nomor_nota) }}"
                                        class="inline-flex items-center gap-1 text-xs font-semibold text-blue-700 bg-blue-100 hover:bg-blue-200 dark:bg-blue-900/50 dark:text-blue-300 dark:hover:bg-blue-800 px-2.5 py-1.5 rounded-lg transition-colors active:scale-95">
                                        Detail
                                    </a>
                                </td>
                                @endcan
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Ringkasan Total Tab Lunas --}}
                <div class="mt-5 rounded-2xl border border-gray-200 bg-gray-50/80 dark:border-gray-800 dark:bg-gray-800/40 p-4 sm:p-5">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-3 mb-4 border-b border-gray-200 dark:border-gray-700/60 gap-2">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-300">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </span>
                            <span class="text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                                Total Rekapitulasi Nota Lunas / Cash (ACC)
                            </span>
                            <span class="text-xs px-2.5 py-0.5 rounded-full bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300 font-semibold">
                                {{ $periodeLabel }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        {{-- Transaksi Cash --}}
                        <div class="flex items-center gap-3.5 bg-white dark:bg-gray-900/80 p-3.5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-2xs">
                            <div class="p-2.5 rounded-xl bg-green-50 text-green-600 dark:bg-green-900/30 dark:text-green-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <div>
                                <p class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Transaksi Cash (Lunas)</p>
                                <p class="text-base sm:text-lg font-extrabold text-gray-800 dark:text-white">
                                    {{ $notasSupplierLunas->count() }} <span class="text-xs font-normal text-gray-500">Nota</span>
                                </p>
                            </div>
                        </div>

                        {{-- Total Cash --}}
                        <div class="flex items-center gap-3.5 bg-white dark:bg-gray-900/80 p-3.5 rounded-xl border border-green-200 dark:border-green-800/50 shadow-2xs">
                            <div class="p-2.5 rounded-xl bg-green-50 text-green-600 dark:bg-green-900/30 dark:text-green-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <p class="text-[11px] font-bold text-green-600 dark:text-green-400 uppercase tracking-wider">Total Nominal Cash</p>
                                <p class="text-base sm:text-lg font-extrabold text-green-700 dark:text-green-400">
                                    Rp {{ number_format($totalCash, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ====== TABEL NOTA INTERNAL ====== -->
            <div x-show="mainTab === 'internal'" x-transition:enter="transition ease-out duration-200" style="display: none;">
                <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800 custom-scrollbar">
                    <table id="table-internal" class="w-full text-xs text-left text-gray-700 dark:text-gray-300 border-collapse min-w-[700px]">
                        <thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 uppercase font-bold text-[11px] sm:text-xs">
                            <tr>
                                <th class="px-3 py-3 border-b border-gray-200 dark:border-gray-700">Nomor Nota</th>
                                <th class="px-3 py-3 border-b border-gray-200 dark:border-gray-700">Tanggal Masuk</th>
                                <th class="px-3 py-3 border-b border-gray-200 dark:border-gray-700 text-center">Jenis Nota</th>
                                <th class="px-3 py-3 border-b border-gray-200 dark:border-gray-700">Daftar Barang</th>
                                <th class="px-3 py-3 border-b border-gray-200 dark:border-gray-700">Gudang Tujuan</th>
                                <th class="px-3 py-3 border-b border-gray-200 dark:border-gray-700">Tanggal Posting</th>
                                @can('gudang.nota-masuk.daftar-nota-masuk.detail')
                                <th class="px-3 py-3 border-b border-gray-200 dark:border-gray-700 text-center">Aksi</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($notasInternal as $nota)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800/60 transition-colors">
                                <td class="px-3 py-3 font-mono font-bold text-gray-900 whitespace-nowrap dark:text-white">{{ $nota->nomor_nota }}</td>
                                <td class="px-3 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ \Carbon\Carbon::parse($nota->tanggal_nota)->format('d-M-Y') }}</td>
                                <td class="px-3 py-3 text-center whitespace-nowrap">
                                    @php
                                        $jenisMap = [
                                            'supplier' => ['label' => 'Supplier', 'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'],
                                            'produksi_rakitan' => ['label' => 'Produksi Rakitan', 'class' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400'],
                                            'return_barang' => ['label' => 'Return Barang', 'class' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400'],
                                            'adjustment_stock' => ['label' => 'Adjustment', 'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'],
                                        ];
                                        $jenis = $jenisMap[$nota->jenis_nota] ?? ['label' => $nota->jenis_nota, 'class' => 'bg-gray-100 text-gray-600'];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $jenis['class'] }}">
                                        {{ $jenis['label'] }}
                                    </span>
                                </td>
                                <td class="px-2.5 py-2 min-w-[180px]">
                                    <div class="max-h-20 overflow-y-auto custom-scrollbar space-y-0.5 pr-0.5">
                                        @foreach ($nota->details as $detail)
                                            <div class="text-[11px] font-normal leading-tight text-gray-700 dark:text-gray-300 flex items-center justify-between gap-1.5 border-b border-gray-100/70 dark:border-gray-800/30 pb-0.5 last:border-0">
                                                <span>{{ $detail->barang->nama_barang ?? '-' }}</span>
                                                <span class="text-[10px] text-gray-500 dark:text-gray-400 font-medium whitespace-nowrap shrink-0">
                                                    {{ (float)$detail->jumlah_input + 0 }} {{ $detail->satuan->nama ?? '' }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-3 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    @if ($nota->stock_type === 'HUB')
                                        <span class="px-2 py-0.5 bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 rounded-md text-xs font-bold">Gudang HUB</span>
                                    @else
                                        {{ $nota->ubs->nama_ubs ?? '-' }}
                                    @endif
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap">
                                    @if($nota->posted_at)
                                        <span class="text-xs font-medium text-green-700 dark:text-green-400">{{ \Carbon\Carbon::parse($nota->posted_at)->format('d-M-Y') }}</span>
                                        <span class="text-[10px] text-gray-400 dark:text-gray-500 ml-1">{{ \Carbon\Carbon::parse($nota->posted_at)->format('H:i') }}</span>
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                </td>
                                @can('gudang.nota-masuk.daftar-nota-masuk.detail')
                                <td class="px-3 py-3 text-center">
                                    <a href="{{ route('gudang.daftarNotaMasuk.show', $nota->nomor_nota) }}"
                                        class="inline-flex items-center gap-1 text-xs font-semibold text-blue-700 bg-blue-100 hover:bg-blue-200 dark:bg-blue-900/50 dark:text-blue-300 dark:hover:bg-blue-800 px-2.5 py-1.5 rounded-lg transition-colors active:scale-95">
                                        Detail
                                    </a>
                                </td>
                                @endcan
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

</div>
<!-- ===== Main Content End ===== -->

<script>
    function initTable(id) {
        if (document.getElementById(id) && typeof simpleDatatables.DataTable !== 'undefined') {
            new simpleDatatables.DataTable("#" + id, {
                searchable: true,
                sortable: true,
                perPageSelect: [5, 10, 20, 50],
            });
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        initTable("table-supplier-all");
        initTable("table-supplier-hutang");
        initTable("table-supplier-lunas");
        initTable("table-internal");
    });
</script>
@endsection