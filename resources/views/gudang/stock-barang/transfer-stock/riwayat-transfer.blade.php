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
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                {{-- Judul --}}
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                    Riwayat Transfer Stock Gudang
                </h3>

            </div>

            {{-- Filter by bulan dan tahun --}}
            <form method="GET" action="{{ route('gudang.transferStockBarang.riwayatTransferStock') }}"
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

                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition focus:ring-4 focus:ring-blue-300 active:scale-95 shadow-sm shadow-blue-200 dark:shadow-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Tampilkan
                </button>

                @if(request()->has('bulan') || request()->has('tahun'))
                    <a href="{{ route('gudang.transferStockBarang.riwayatTransferStock') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300 transition dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                        Reset
                    </a>
                @endif
            </form>


            <table id="table-transferStock">
                <thead>
                    <tr>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                            <span class="flex items-center">
                                No Transfer
                                <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                    width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                </svg>
                            </span>
                        </th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                            <span class="flex items-center">
                                Tanggal Transfer
                                <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                    width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                </svg>
                            </span>
                        </th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                            <span class="flex items-center">
                                Gudang Asal
                                <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                    width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                </svg>
                            </span>
                        </th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                            <span class="flex items-center">
                                Gudang Tujuan
                                <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                    width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                </svg>
                            </span>
                        </th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transferStocks as $transferStock)
                    <tr>
                        {{-- Nomor Transfer --}}
                        <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            {{ $transferStock->nomor_transfer }}
                        </td>

                        {{-- Tanggal Transfer --}}
                        <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            {{ \Carbon\Carbon::parse($transferStock->tanggal_transfer)->format('d-M-Y') }}
                        </td>

                        {{-- Gudang Asal --}}
                        <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            @if($transferStock->dari_stock_type === 'HUB')
                            <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded dark:bg-gray-700 dark:text-gray-300 text-xs font-bold">HUB (Pusat)</span>
                            @else
                            {{ $transferStock->fromUbs->nama_ubs ?? '-' }} ({{ $transferStock->fromUbs->kode_ubs ?? '-' }})
                            @endif
                        </td>

                        {{-- Gudang Tujuan --}}
                        <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            @if($transferStock->ke_stock_type === 'HUB')
                            <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded dark:bg-gray-700 dark:text-gray-300 text-xs font-bold">HUB (Pusat)</span>
                            @else
                            {{ $transferStock->toUbs->nama_ubs ?? '-' }} ({{ $transferStock->toUbs->kode_ubs ?? '-' }})
                            @endif
                        </td>

                        {{-- Aksi --}}
                        <td class="px-6 py-4 flex flex-wrap gap-2 justify-center">

                            {{-- SHOW / DETAIL --}}
                            <a href="{{ route('gudang.transferStockBarang.riwayatTransferStock.show', $transferStock->nomor_transfer) }}"
                                class="inline-flex items-center gap-1
                                        text-xs font-medium text-blue-700 bg-blue-100 hover:bg-blue-200
                                        dark:bg-blue-800 dark:text-blue-100 dark:hover:bg-blue-700
                                        px-2.5 py-1.5 rounded-md transition-colors duration-200
                                        focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1
                                        active:scale-95">
                                Detail
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>

</div>
<!-- ===== Main Content End ===== -->

{{-- sweatalert 2 for delete data --}}
<script>
    if (document.getElementById("table-transferStock") && typeof simpleDatatables.DataTable !== 'undefined') {
        const dataTable = new simpleDatatables.DataTable("#table-transferStock", {
            searchable: true,
            sortable: true,
            perPageSelect: [5, 10, 20, 50],
        });
    }
</script>
@endsection