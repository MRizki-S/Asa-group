@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-data="{}">
        {{-- Header Laporan --}}
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">Laporan Perbandingan Bahan Kawasan</h2>
                <p class="text-sm text-gray-500">Kawasan: <span
                        class="font-bold text-blue-600 uppercase">{{ $kawasan->nama_kawasan ?? '-' }}</span></p>
            </div>
            <div class="flex gap-2">
                <a href="{{ url()->previous() }}"
                    class="px-4 py-2 bg-white border border-gray-200 text-gray-600 text-xs font-bold rounded-lg hover:bg-gray-50 transition uppercase">
                    Kembali </a>
            </div>
        </div>

        {{-- Ringkasan Total --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div
                class="p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800 rounded-2xl md:col-span-2">
                <p class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest">Total
                    Biaya Realisasi Keseluruhan</p>
                <p class="text-xl font-bold text-gray-800 dark:text-white">Rp
                    {{ number_format($laporan['total_harga_real'], 0, ',', '.') }}</p>
            </div>

            @php
                $itemLuarRequest = 0;
                foreach ($laporan['details'] as $detail) {
                    if ($detail['qty_request'] == 0 && $detail['qty_real'] > 0) {
                        $itemLuarRequest++;
                    }
                }
            @endphp
            <div
                class="p-4 {{ $itemLuarRequest > 0 ? 'bg-red-50 border-red-100 dark:bg-red-900/20 dark:border-red-800' : 'bg-blue-50 border-blue-100 dark:bg-blue-900/20 dark:border-blue-800' }} rounded-2xl border">
                <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Item Diluar Request</p>
                <p class="text-xl font-bold {{ $itemLuarRequest > 0 ? 'text-red-600 dark:text-red-400' : 'text-blue-600 dark:text-blue-400' }}">
                    {{ $itemLuarRequest }} Macam Bahan
                </p>
            </div>
        </div>

        {{-- Detail --}}
        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl overflow-hidden shadow-sm">
                <div class="px-5 py-3 bg-gray-50/50 dark:bg-white/5 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h4 class="text-xs font-black text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                        Rincian Pemakaian Bahan</h4>
                    <div class="text-right">
                        <span class="text-[10px] text-gray-400 uppercase font-bold">Subtotal Realisasi Harga:</span>
                        <span class="text-sm font-bold text-blue-600 ms-2">Rp
                            {{ number_format($laporan['total_harga_real'], 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-max">
                        <thead>
                            <tr class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter border-b border-gray-50 dark:border-gray-700">
                                <th class="px-5 py-3">Nama Bahan</th>
                                <th class="px-5 py-3 text-right">Qty Request</th>
                                <th class="px-5 py-3 text-right">Qty Realisasi</th>
                                <th class="px-5 py-3 text-center">Status Qty</th>
                                <th class="px-5 py-3 text-right">Total Harga Real</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                            @forelse ($laporan['details'] as $detail)
                                <tr class="text-sm hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="px-5 py-3 font-medium text-gray-700 dark:text-gray-300">
                                        {{ $detail['nama_barang'] }}
                                        @if ($detail['qty_request'] == 0)
                                            <span class="ml-2 inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                                DI LUAR REQUEST
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-5 py-3 text-right font-mono text-gray-500">
                                        @if ($detail['qty_request'] > 0)
                                            {{ floatval($detail['qty_request']) }} <span class="text-[10px] uppercase">{{ $detail['satuan_request'] }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td class="px-5 py-3 text-right font-mono font-bold text-gray-800 dark:text-white">
                                        @if ($detail['qty_real'] > 0)
                                            <span class="{{ $detail['qty_real'] > $detail['qty_request'] && $detail['qty_request'] > 0 ? 'text-red-500' : '' }}">
                                                {{ floatval($detail['qty_real']) }}
                                            </span>
                                            <span class="text-[10px] text-gray-500 uppercase">{{ $detail['satuan_real'] }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td class="px-5 py-3 text-center font-bold text-xs">
                                        @if ($detail['qty_request'] == 0)
                                            <span class="text-red-500">Unplanned</span>
                                        @elseif($detail['qty_real'] == 0)
                                            <span class="text-gray-400">Belum Dipakai</span>
                                        @elseif($detail['qty_real'] > $detail['qty_request'])
                                            <span class="text-red-500">Over Qty</span>
                                        @else
                                            <span class="text-emerald-500">Aman</span>
                                        @endif
                                    </td>

                                    <td class="px-5 py-3 text-right font-mono font-bold text-gray-800 dark:text-white">
                                        Rp {{ number_format($detail['harga_real'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-4 text-center text-gray-500 dark:text-gray-400 text-sm">
                                        Belum ada data request atau realisasi.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
