@extends('layouts.app')

@section('pageActive', 'DaftarNotaMasuk')

@section('content')
<!-- ===== Main Content Start ===== -->
<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

    <!-- Breadcrumb Start -->
    <div x-data="{ pageName: 'DaftarNotaMasuk' }">
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

    <div class="space-y-5 sm:space-y-6" x-data="{ mainTab: 'supplier', supplierTab: 'all' }">
        <div
            class="rounded-2xl border border-gray-200 px-5 py-4 sm:px-6 sm:py-5 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                {{-- Judul --}}
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                    Daftar Nota Barang Masuk
                </h3>

                {{-- Filter --}}
                <form method="GET" action="{{ route('gudang.daftarNotaMasuk.index') }}"
                    class="flex flex-wrap items-end gap-3 mb-6 bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl border border-gray-100 dark:border-gray-800">
                    
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Bulan</label>
                        <select name="bulan" class="rounded-lg border-gray-300 bg-white text-gray-800 text-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300 min-w-[140px]">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ (int)$bulan === $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Tahun</label>
                        <select name="tahun" class="rounded-lg border-gray-300 bg-white text-gray-800 text-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300 min-w-[100px]">
                            @php $currentYear = now()->year; @endphp
                            @foreach(range($currentYear - 3, $currentYear + 1) as $y)
                                <option value="{{ $y }}" {{ (int)$tahun === $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Filter Per Hari (Opsional)</label>
                        <div class="relative" x-data="{ tanggal: '{{ request('tanggal') }}' }">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
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
                                placeholder="Pilih Tanggal"
                                class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 dark:bg-gray-900 dark:border-gray-700 dark:text-white min-w-[150px]">
                        </div>
                    </div>

                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition focus:ring-4 focus:ring-blue-300 active:scale-95 shadow-sm">
                        Tampilkan
                    </button>

                    <a href="{{ route('gudang.daftarNotaMasuk.index') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300 transition dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                        Reset
                    </a>

                    @can('gudang.nota-masuk.draft-nota-masuk.read')
                    <div class="h-10 w-[1px] bg-gray-300 dark:bg-gray-700 mx-1 hidden sm:block"></div>
 
                    <a href="{{route('gudang.draftNotaMasuk.index') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-yellow-400 px-4 py-2 text-sm font-bold text-yellow-900 hover:bg-yellow-500 transition shadow-sm">
                        Draft Nota
                    </a>
                    @endcan
                </form>


            </div>




            <!-- Tab Navigation (Main) -->
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 dark:border-gray-800 pb-4">
                <div class="flex p-1 bg-gray-100 dark:bg-gray-800 rounded-xl w-fit">
                    <button @click="mainTab = 'supplier'"
                        :class="mainTab === 'supplier' ? 'bg-white dark:bg-gray-700 shadow-sm text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
                        class="px-4 py-2 text-sm font-bold rounded-lg transition-all duration-200">
                        Nota Supplier ({{ $notasSupplier->count() }})
                    </button>
                    <button @click="mainTab = 'internal'"
                        :class="mainTab === 'internal' ? 'bg-white dark:bg-gray-700 shadow-sm text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
                        class="px-4 py-2 text-sm font-bold rounded-lg transition-all duration-200">
                        Nota Internal ({{ $notasInternal->count() }})
                    </button>
                </div>
            </div>

            <!-- Sub Tab Navigation (Only for Supplier) -->
            <div x-show="mainTab === 'supplier'" x-transition class="mb-6 flex flex-wrap gap-2 items-center p-1 bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-xl w-fit">
                <button @click="supplierTab = 'all'"
                    :class="supplierTab === 'all' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
                    class="px-3.5 py-1.5 text-xs font-semibold rounded-lg transition-all duration-200">
                    Semua ({{ $notasSupplier->count() }})
                </button>
                <button @click="supplierTab = 'hutang'"
                    :class="supplierTab === 'hutang' ? 'bg-yellow-500 text-yellow-950 font-bold shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
                    class="px-3.5 py-1.5 text-xs font-semibold rounded-lg transition-all duration-200">
                    Hutang ({{ $notasSupplierHutang->count() }})
                </button>
                <button @click="supplierTab = 'lunas'"
                    :class="supplierTab === 'lunas' ? 'bg-green-600 text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
                    class="px-3.5 py-1.5 text-xs font-semibold rounded-lg transition-all duration-200">
                    Lunas ({{ $notasSupplierLunas->count() }})
                </button>
            </div>

            <!-- ====== TABEL NOTA SUPPLIER (ALL) ====== -->
            <div x-show="mainTab === 'supplier' && supplierTab === 'all'" x-transition:enter="transition ease-out duration-200">
                <table id="table-supplier-all">
                    <thead>
                        <tr>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Nomor Nota</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Tanggal Masuk</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Supplier</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Gudang Tujuan</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Cara Bayar</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Tanggal Posting</th>
                            @can('gudang.nota-masuk.daftar-nota-masuk.detail')
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">Aksi</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($notasSupplier as $nota)
                        <tr>
                            <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ $nota->nomor_nota }}</td>
                            <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ \Carbon\Carbon::parse($nota->tanggal_nota)->format('d-M-Y') }}</td>
                            <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ $nota->supplier->nama_supplier ?? '-' }}</td>
                            <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                @if ($nota->stock_type === 'HUB')
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-bold">Gudang HUB</span>
                                @else
                                    {{ $nota->ubs->nama_ubs ?? '-' }}
                                @endif
                            </td>
                            <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                @if($nota->cara_bayar)
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $nota->cara_bayar === 'cash' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                        {{ strtoupper($nota->cara_bayar) }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap">
                                @if($nota->posted_at)
                                    <span class="text-xs font-medium text-green-700 dark:text-green-400">{{ \Carbon\Carbon::parse($nota->posted_at)->format('d-M-Y') }}</span>
                                    <br>
                                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ \Carbon\Carbon::parse($nota->posted_at)->format('H:i:s') }}</span>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            @can('gudang.nota-masuk.daftar-nota-masuk.detail')
                            <td class="px-6 py-4 flex flex-wrap gap-2 justify-center">
                                <a href="{{ route('gudang.daftarNotaMasuk.show', $nota->nomor_nota) }}"
                                    class="inline-flex items-center gap-1 text-xs font-medium text-blue-700 bg-blue-100 hover:bg-blue-200 dark:bg-blue-800 dark:text-blue-100 dark:hover:bg-blue-700 px-2.5 py-1.5 rounded-md transition-colors duration-200 active:scale-95">
                                    Detail
                                </a>
                            </td>
                            @endcan
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- ====== TABEL NOTA SUPPLIER (HUTANG) ====== -->
            <div x-show="mainTab === 'supplier' && supplierTab === 'hutang'" x-transition:enter="transition ease-out duration-200" style="display: none;">
                <table id="table-supplier-hutang">
                    <thead>
                        <tr>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Nomor Nota</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Tanggal Masuk</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Supplier</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Gudang Tujuan</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Cara Bayar</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Tanggal Posting</th>
                            @can('gudang.nota-masuk.daftar-nota-masuk.detail')
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">Aksi</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($notasSupplierHutang as $nota)
                        <tr>
                            <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ $nota->nomor_nota }}</td>
                            <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ \Carbon\Carbon::parse($nota->tanggal_nota)->format('d-M-Y') }}</td>
                            <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ $nota->supplier->nama_supplier ?? '-' }}</td>
                            <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                @if ($nota->stock_type === 'HUB')
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-bold">Gudang HUB</span>
                                @else
                                    {{ $nota->ubs->nama_ubs ?? '-' }}
                                @endif
                            </td>
                            <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                                    HUTANG
                                </span>
                            </td>
                            <td class="whitespace-nowrap">
                                @if($nota->posted_at)
                                    <span class="text-xs font-medium text-green-700 dark:text-green-400">{{ \Carbon\Carbon::parse($nota->posted_at)->format('d-M-Y') }}</span>
                                    <br>
                                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ \Carbon\Carbon::parse($nota->posted_at)->format('H:i:s') }}</span>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            @can('gudang.nota-masuk.daftar-nota-masuk.detail')
                            <td class="px-6 py-4 flex flex-wrap gap-2 justify-center">
                                <a href="{{ route('gudang.daftarNotaMasuk.show', $nota->nomor_nota) }}"
                                    class="inline-flex items-center gap-1 text-xs font-medium text-blue-700 bg-blue-100 hover:bg-blue-200 dark:bg-blue-800 dark:text-blue-100 dark:hover:bg-blue-700 px-2.5 py-1.5 rounded-md transition-colors duration-200 active:scale-95">
                                    Detail
                                </a>
                            </td>
                            @endcan
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- ====== TABEL NOTA SUPPLIER (LUNAS) ====== -->
            <div x-show="mainTab === 'supplier' && supplierTab === 'lunas'" x-transition:enter="transition ease-out duration-200" style="display: none;">
                <table id="table-supplier-lunas">
                    <thead>
                        <tr>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Nomor Nota</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Tanggal Masuk</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Supplier</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Gudang Tujuan</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Cara Bayar</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Tanggal Posting</th>
                            @can('gudang.nota-masuk.daftar-nota-masuk.detail')
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">Aksi</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($notasSupplierLunas as $nota)
                        <tr>
                            <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ $nota->nomor_nota }}</td>
                            <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ \Carbon\Carbon::parse($nota->tanggal_nota)->format('d-M-Y') }}</td>
                            <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ $nota->supplier->nama_supplier ?? '-' }}</td>
                            <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                @if ($nota->stock_type === 'HUB')
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-bold">Gudang HUB</span>
                                @else
                                    {{ $nota->ubs->nama_ubs ?? '-' }}
                                @endif
                            </td>
                            <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                    {{ $nota->cara_bayar ? strtoupper($nota->cara_bayar) : 'CASH' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap">
                                @if($nota->posted_at)
                                    <span class="text-xs font-medium text-green-700 dark:text-green-400">{{ \Carbon\Carbon::parse($nota->posted_at)->format('d-M-Y') }}</span>
                                    <br>
                                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ \Carbon\Carbon::parse($nota->posted_at)->format('H:i:s') }}</span>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            @can('gudang.nota-masuk.daftar-nota-masuk.detail')
                            <td class="px-6 py-4 flex flex-wrap gap-2 justify-center">
                                <a href="{{ route('gudang.daftarNotaMasuk.show', $nota->nomor_nota) }}"
                                    class="inline-flex items-center gap-1 text-xs font-medium text-blue-700 bg-blue-100 hover:bg-blue-200 dark:bg-blue-800 dark:text-blue-100 dark:hover:bg-blue-700 px-2.5 py-1.5 rounded-md transition-colors duration-200 active:scale-95">
                                    Detail
                                </a>
                            </td>
                            @endcan
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- ====== TABEL NOTA INTERNAL ====== -->
            <div x-show="mainTab === 'internal'" x-transition:enter="transition ease-out duration-200" style="display: none;">
                <table id="table-internal">
                    <thead>
                        <tr>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Nomor Nota</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Tanggal Masuk</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">Jenis Nota</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Gudang Tujuan</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Tanggal Posting</th>
                            @can('gudang.nota-masuk.daftar-nota-masuk.detail')
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">Aksi</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($notasInternal as $nota)
                        <tr>
                            <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ $nota->nomor_nota }}</td>
                            <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ \Carbon\Carbon::parse($nota->tanggal_nota)->format('d-M-Y') }}</td>
                            <td class="text-center">
                                @php
                                    $jenisMap = [
                                        'supplier' => ['label' => 'Supplier', 'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'],
                                        'produksi_rakitan' => ['label' => 'Produksi Rakitan', 'class' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400'],
                                        'return_barang' => ['label' => 'Return Barang', 'class' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400'],
                                        'adjustment_stock' => ['label' => 'Adjustment', 'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'],
                                    ];
                                    $jenis = $jenisMap[$nota->jenis_nota] ?? ['label' => $nota->jenis_nota, 'class' => 'bg-gray-100 text-gray-600'];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $jenis['class'] }}">
                                    {{ $jenis['label'] }}
                                </span>
                            </td>
                            <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                @if ($nota->stock_type === 'HUB')
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-bold">Gudang HUB</span>
                                @else
                                    {{ $nota->ubs->nama_ubs ?? '-' }}
                                @endif
                            </td>
                            <td class="whitespace-nowrap">
                                @if($nota->posted_at)
                                    <span class="text-xs font-medium text-green-700 dark:text-green-400">{{ \Carbon\Carbon::parse($nota->posted_at)->format('d-M-Y') }}</span>
                                    <br>
                                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ \Carbon\Carbon::parse($nota->posted_at)->format('H:i:s') }}</span>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            @can('gudang.nota-masuk.daftar-nota-masuk.detail')
                            <td class="px-6 py-4 flex flex-wrap gap-2 justify-center">
                                <a href="{{ route('gudang.daftarNotaMasuk.show', $nota->nomor_nota) }}"
                                    class="inline-flex items-center gap-1 text-xs font-medium text-blue-700 bg-blue-100 hover:bg-blue-200 dark:bg-blue-800 dark:text-blue-100 dark:hover:bg-blue-700 px-2.5 py-1.5 rounded-md transition-colors duration-200 active:scale-95">
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