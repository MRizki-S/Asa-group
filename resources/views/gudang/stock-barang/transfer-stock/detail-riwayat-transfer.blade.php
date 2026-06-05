@extends('layouts.app')

@section('pageActive', 'StokBarangGudang')

@section('content')
{{-- select 2  --}}
<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">

<style>
    .select2-results__option[aria-disabled=true] {
        background-color: #e5e7eb;
        /* gray-200 */
        color: #6b7280;
        /* gray-500 */
        cursor: not-allowed;
    }
</style>

<!-- ===== Main Content Start ===== -->
<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-init="$dispatch('sidebar-minimize')">

    <!-- Breadcrumb Start -->
    <div x-data="{ pageName: 'StokBarangGudang' }">
        @include('partials.breadcrumb')
    </div>
    <!-- Breadcrumb End -->


    {{-- header detail transfer --}}
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6 shadow-sm">
        <div class="px-5 py-4 sm:px-6 sm:py-5">
            <div class="flex items-center justify-between mb-6 border-b border-gray-100 dark:border-gray-800 pb-4">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">
                    Detail Transfer Stock
                </h3>
                <span class="px-3 py-1 bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 rounded-full text-xs font-bold ring-1 ring-blue-200 dark:ring-blue-800">
                    {{ $transfer->nomor_transfer }}
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Dari Gudang --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-gray-500 uppercase dark:text-gray-400">Dari Gudang</label>
                    <div class="flex items-center gap-2 text-sm font-semibold text-gray-800 dark:text-gray-200">
                        <div class="p-2 bg-gray-100 dark:bg-gray-800 rounded-lg">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        @if($transfer->dari_stock_type === 'HUB')
                            <span>Gudang HUB (Pusat)</span>
                        @else
                            <span>{{ $transfer->fromUbs->nama_ubs ?? '-' }}</span>
                        @endif
                    </div>
                </div>

                {{-- Ke Gudang --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-gray-500 uppercase dark:text-gray-400">Gudang Tujuan</label>
                    <div class="flex items-center gap-2 text-sm font-semibold text-gray-800 dark:text-gray-200">
                        <div class="p-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </div>
                        @if($transfer->ke_stock_type === 'HUB')
                            <span>Gudang HUB (Pusat)</span>
                        @else
                            <span>{{ $transfer->toUbs->nama_ubs ?? '-' }} {{ $transfer->toUbs->kode_ubs ? '('.$transfer->toUbs->kode_ubs.')' : '' }}</span>
                        @endif
                    </div>
                </div>

                {{-- Tanggal --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-gray-500 uppercase dark:text-gray-400">Tanggal Transfer</label>
                    <div class="flex items-center gap-2 text-sm font-semibold text-gray-800 dark:text-gray-200">
                        <div class="p-2 bg-gray-100 dark:bg-gray-800 rounded-lg">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <span>{{ \Carbon\Carbon::parse($transfer->tanggal_transfer)->translatedFormat('d F Y') }}</span>
                    </div>
                </div>

                {{-- Pembuat --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-gray-500 uppercase dark:text-gray-400">Dibuat Oleh</label>
                    <div class="flex items-center gap-2 text-sm font-semibold text-gray-800 dark:text-gray-200">
                        <div class="p-2 bg-gray-100 dark:bg-gray-800 rounded-lg">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <span>{{ $transfer->creator->nama_lengkap ?? 'System' }}</span>
                    </div>
                </div>

                {{-- Keterangan --}}
                <div class="md:col-span-2 space-y-1.5">
                    <label class="text-xs font-bold text-gray-500 uppercase dark:text-gray-400">Keterangan</label>
                    <p class="text-sm text-gray-600 dark:text-gray-400 italic">
                        {{ $transfer->keterangan ?? 'Tidak ada keterangan.' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Detail Items --}}
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden shadow-sm">
        <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/10">
            <h3 class="text-sm font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                Daftar Barang yang Ditransfer
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-800/50 dark:text-gray-400 border-b border-gray-100 dark:border-gray-800">
                    <tr>
                        <th class="px-6 py-4 font-bold">No</th>
                        <th class="px-6 py-4 font-bold">Nama Barang</th>
                        <th class="px-6 py-4 font-bold text-right">Jumlah</th>
                        <th class="px-6 py-4 font-bold">Satuan</th>
                        <th class="px-6 py-4 font-bold text-right text-gray-400 italic">Base Qty</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($details as $index => $detail)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20 transition-colors">
                        <td class="px-6 py-4 text-gray-500">{{ $index + 1 }}</td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="font-bold text-gray-800 dark:text-gray-200">{{ $detail->barang->nama_barang ?? $detail->nama_barang_snapshot }}</span>
                                <span class="text-xs text-gray-400">{{ $detail->barang->kode_barang ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-bold text-right text-blue-600 dark:text-blue-400">
                            {{ number_format($detail->qty, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-600 dark:text-gray-400">
                            {{ $detail->satuan->nama ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-right text-gray-400 italic">
                            {{ (float)$detail->qty_base }} base unit
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6 flex justify-end">
        <a href="{{ route('gudang.transferStockBarang.riwayatTransferStock') }}" 
           class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-gray-800 hover:bg-gray-900 dark:bg-blue-600 dark:hover:bg-blue-700 rounded-xl transition-all active:scale-95">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Riwayat
        </a>
    </div>

</div>
<!-- ===== Main Content End ===== -->

@endsection