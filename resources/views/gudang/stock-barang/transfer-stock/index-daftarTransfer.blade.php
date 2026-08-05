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

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="flex p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
        <svg class="shrink-0 inline w-4 h-4 me-3 mt-[2px]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
        </svg>
        <div><span class="font-bold">Berhasil!</span> {{ session('success') }}</div>
    </div>
    @endif

    @if(session('error'))
    <div class="flex p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
        <svg class="shrink-0 inline w-4 h-4 me-3 mt-[2px]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
        </svg>
        <div><span class="font-bold">Error!</span> {{ session('error') }}</div>
    </div>
    @endif

    <div class="space-y-5 sm:space-y-6">
        <div class="rounded-2xl border border-gray-200 px-5 py-4 sm:px-6 sm:py-5 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

            {{-- Header --}}
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                        Daftar Transfer Stock Barang
                    </h3>
                </div>
                <a href="{{ route('gudang.transferStockBarang.create') }}"
                    target="_blank"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition focus:ring-4 focus:ring-blue-300 active:scale-95 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Ajukan Transfer Baru
                </a>
            </div>

            {{-- Filter Bulan & Tahun --}}
            <form method="GET" action="{{ route('gudang.transferStockBarang.daftar.index') }}"
                class="flex flex-wrap items-end gap-3 mb-5 bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl border border-gray-100 dark:border-gray-800">

                {{-- Preserve current status filter --}}
                <input type="hidden" name="status" value="{{ $currentStatus }}">

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
                            <option value="{{ $y }}" {{ (int)$tahun === $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition focus:ring-4 focus:ring-blue-300 active:scale-95 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Tampilkan
                </button>
            </form>

            {{-- Tab Filter Status --}}
            <div class="flex gap-2 mb-5 flex-wrap">
                <a href="{{ route('gudang.transferStockBarang.daftar.index', ['status' => 'all', 'bulan' => $bulan, 'tahun' => $tahun]) }}"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-all
                    {{ $currentStatus === 'all' ? 'bg-blue-600 text-white shadow' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' }}">
                    Semua
                    <span class="ml-1 text-xs">{{ $transfers->count() }}</span>
                </a>
                <a href="{{ route('gudang.transferStockBarang.daftar.index', ['status' => 'pending', 'bulan' => $bulan, 'tahun' => $tahun]) }}"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-all
                    {{ $currentStatus === 'pending' ? 'bg-yellow-500 text-white shadow' : 'bg-yellow-50 text-yellow-700 hover:bg-yellow-100 dark:bg-yellow-900/20 dark:text-yellow-400' }}">
                    Pending
                    <span class="ml-1 text-xs">{{ $transfers->where('status', 'pending')->count() }}</span>
                </a>
                <a href="{{ route('gudang.transferStockBarang.daftar.index', ['status' => 'disetujui', 'bulan' => $bulan, 'tahun' => $tahun]) }}"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-all
                    {{ $currentStatus === 'disetujui' ? 'bg-green-600 text-white shadow' : 'bg-green-50 text-green-700 hover:bg-green-100 dark:bg-green-900/20 dark:text-green-400' }}">
                    Disetujui
                    <span class="ml-1 text-xs">{{ $transfers->where('status', 'disetujui')->count() }}</span>
                </a>
                <a href="{{ route('gudang.transferStockBarang.daftar.index', ['status' => 'ditolak', 'bulan' => $bulan, 'tahun' => $tahun]) }}"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-all
                    {{ $currentStatus === 'ditolak' ? 'bg-red-600 text-white shadow' : 'bg-red-50 text-red-700 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400' }}">
                    Ditolak
                    <span class="ml-1 text-xs">{{ $transfers->where('status', 'ditolak')->count() }}</span>
                </a>
            </div>

            {{-- Table --}}
            <table id="table-daftar-transfer">
                <thead>
                    <tr>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                            <span class="flex items-center">No Transfer</span>
                        </th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                            <span class="flex items-center">Tanggal</span>
                        </th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                            <span class="flex items-center">Dari UBS</span>
                        </th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                            <span class="flex items-center">Ke UBS</span>
                        </th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                            <span class="flex items-center">Pengaju</span>
                        </th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">Status</th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transfers as $transfer)
                    <tr>
                        {{-- Nomor Transfer --}}
                        <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white text-sm">
                            {{ $transfer->nomor_transfer }}
                        </td>

                        {{-- Tanggal --}}
                        <td class="text-gray-700 dark:text-gray-300 text-sm whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($transfer->tanggal_transfer)->format('d-M-Y') }}
                        </td>

                        {{-- Dari UBS --}}
                        <td class="text-gray-700 dark:text-gray-300 text-sm whitespace-nowrap">
                            {{ $transfer->fromUbs->nama_ubs ?? '-' }}
                            @if($transfer->fromUbs?->kode_ubs)
                                <span class="text-gray-400 text-xs">({{ $transfer->fromUbs->kode_ubs }})</span>
                            @endif
                        </td>

                        {{-- Ke UBS --}}
                        <td class="text-gray-700 dark:text-gray-300 text-sm whitespace-nowrap">
                            {{ $transfer->toUbs->nama_ubs ?? '-' }}
                            @if($transfer->toUbs?->kode_ubs)
                                <span class="text-gray-400 text-xs">({{ $transfer->toUbs->kode_ubs }})</span>
                            @endif
                        </td>

                        {{-- Pengaju --}}
                        <td class="text-gray-700 dark:text-gray-300 text-sm whitespace-nowrap">
                            {{ $transfer->creator->username ?? $transfer->creator->name ?? '-' }}
                        </td>

                        {{-- Status --}}
                        <td class="text-center">
                            @if($transfer->status === 'pending')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                                    Pending
                                </span>
                            @elseif($transfer->status === 'disetujui')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                    Disetujui
                                </span>
                            @elseif($transfer->status === 'ditolak')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                    Ditolak
                                </span>
                            @endif
                        </td>

                        {{-- Aksi --}}
                        <td class="px-4 py-3">
                            <div class="flex gap-2 justify-center flex-wrap">

                                {{-- Detail / Tinjau --}}
                                <a href="{{ route('gudang.transferStockBarang.daftar.show', $transfer->nomor_transfer) }}"
                                    class="inline-flex items-center gap-1
                                            text-xs font-medium text-blue-700 bg-blue-100 hover:bg-blue-200
                                            dark:bg-blue-800 dark:text-blue-100 dark:hover:bg-blue-700
                                            px-2.5 py-1.5 rounded-md transition-colors duration-200
                                            focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1
                                            active:scale-95">
                                    @if($transfer->status === 'pending')
                                        Tinjau
                                    @else
                                        Detail
                                    @endif
                                </a>

                                {{-- Edit — hanya Admin, hanya jika ditolak --}}
                                @if($transfer->status === 'ditolak')
                                <a href="{{ route('gudang.transferStockBarang.edit', $transfer->nomor_transfer) }}"
                                    class="inline-flex items-center gap-1
                                            text-xs font-medium text-orange-700 bg-orange-100 hover:bg-orange-200
                                            dark:bg-orange-900/40 dark:text-orange-300 dark:hover:bg-orange-900/60
                                            px-2.5 py-1.5 rounded-md transition-colors duration-200
                                            focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-1
                                            active:scale-95">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit & Ajukan
                                </a>
                                @endif

                            </div>
                        </td>
                    </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>

        </div>
    </div>

</div>
<!-- ===== Main Content End ===== -->

<script>
    if (document.getElementById("table-daftar-transfer") && typeof simpleDatatables.DataTable !== 'undefined') {
        const dataTable = new simpleDatatables.DataTable("#table-daftar-transfer", {
            searchable: true,
            sortable: true,
            perPageSelect: [10, 20, 50],
        });
    }
</script>
@endsection
