@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-data="{}">
        {{-- Header Laporan --}}
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">Laporan Perbandingan Bahan</h2>
                <p class="text-sm text-gray-500">Unit: <span
                        class="font-bold text-blue-600 dark:text-blue-400 uppercase">{{ $unit->unit->nama_unit ?? '-' }}</span></p>
            </div>
            <div class="flex gap-2">
                <a href="{{ url()->previous() }}"
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 text-xs font-bold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition uppercase shadow-sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </a>
            </div>
        </div>

        {{-- Ringkasan Total --}}
        <div class="flex flex-wrap gap-4 mb-6">
            {{-- Biaya Realisasi Keseluruhan --}}
            <div class="flex-1 min-w-[240px] max-w-sm p-4 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-800/60 rounded-2xl">
                <p class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest mb-1">Total Biaya Realisasi Keseluruhan</p>
                <p class="text-xl font-bold text-gray-800 dark:text-white">Rp
                    {{ number_format($laporan->sum('total_harga_real'), 0, ',', '.') }}</p>
            </div>

            {{-- Menghitung total item yang di luar RAB sebagai indikator --}}
            @php
                $itemLuarRap = 0;
                foreach ($laporan as $qc) {
                    foreach ($qc['details'] as $detail) {
                        if ($detail['qty_rap'] == 0 && $detail['qty_real'] > 0) {
                            $itemLuarRap++;
                        }
                    }
                }
            @endphp
            <div class="flex-1 min-w-[240px] max-w-sm p-4 rounded-2xl border transition-all duration-300
                {{ $itemLuarRap > 0 
                    ? 'bg-red-50 border-red-100 dark:bg-red-950/20 dark:border-red-800/60' 
                    : 'bg-blue-50 border-blue-100 dark:bg-blue-950/20 dark:border-blue-800/60' }}">
                <p class="text-[10px] font-black uppercase tracking-widest mb-1 
                    {{ $itemLuarRap > 0 ? 'text-red-500 dark:text-red-400' : 'text-blue-500 dark:text-blue-400' }}">
                    Item Diluar RAB
                </p>
                <p class="text-xl font-bold {{ $itemLuarRap > 0 ? 'text-red-600 dark:text-red-400' : 'text-blue-600 dark:text-blue-400' }}">
                    {{ $itemLuarRap }} Macam Bahan
                </p>
            </div>
        </div>

        {{-- Detail per Langkah QC --}}
        <div class="space-y-6">
            @foreach ($laporan as $row)
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden shadow-sm">
                    <div class="px-5 py-4 bg-gray-50/50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row justify-between sm:items-center gap-2">
                        <h4 class="text-xs font-black text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                            {{ $row['nama_qc'] }}
                        </h4>
                        <div>
                            <span class="text-[10px] text-gray-400 dark:text-gray-500 uppercase font-bold">Subtotal Realisasi:</span>
                            <span class="text-sm font-bold text-blue-600 dark:text-blue-400 ms-1.5">Rp
                                {{ number_format($row['total_harga_real'], 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse" style="min-width: 680px;">
                            <thead>
                                <tr class="text-[10px] font-bold text-gray-400 dark:text-gray-500 bg-gray-50/50 dark:bg-gray-800/20 uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">
                                    <th class="px-5 py-3 w-[35%]">Nama Bahan</th>
                                    <th class="px-5 py-3 text-right">Qty RAB</th>
                                    <th class="px-5 py-3 text-right">Qty Realisasi</th>
                                    <th class="px-5 py-3 text-center">Status Qty</th>
                                    <th class="px-5 py-3 text-right">Total Harga Real</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                                @foreach ($row['details'] as $detail)
                                    <tr class="text-xs hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition-colors">
                                        {{-- Kolom Nama Bahan --}}
                                        <td class="px-5 py-3 font-medium text-gray-700 dark:text-gray-300">
                                            {{ $detail['nama_barang'] }}
                                            @if ($detail['qty_rap'] == 0)
                                                <span class="ml-1.5 inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                                    DI LUAR RAB
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Kolom Qty RAB --}}
                                        <td class="px-5 py-3 text-right font-mono text-gray-500">
                                            @if ($detail['qty_rap'] > 0)
                                                {{ floatval($detail['qty_rap']) }} <span
                                                    class="text-[9px] uppercase">{{ $detail['satuan_rap'] }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>

                                        {{-- Kolom Qty Realisasi --}}
                                        <td class="px-5 py-3 text-right font-mono font-bold text-gray-800 dark:text-white">
                                            @if ($detail['qty_real'] > 0)
                                                <span class="{{ $detail['qty_real'] > $detail['qty_rap'] && $detail['qty_rap'] > 0 ? 'text-red-500' : '' }}">
                                                    {{ floatval($detail['qty_real']) }}
                                                </span>
                                                <span class="text-[9px] text-gray-500 uppercase font-normal">{{ $detail['satuan_real'] }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>

                                        {{-- Kolom Status Qty --}}
                                        <td class="px-5 py-3 text-center font-bold text-[10px]">
                                            @if ($detail['qty_rap'] == 0)
                                                <span class="text-red-500">Unplanned</span>
                                            @elseif($detail['qty_real'] == 0)
                                                <span class="text-gray-400">Belum Dipakai</span>
                                            @elseif($detail['qty_real'] > $detail['qty_rap'])
                                                <span class="text-red-500">Over Qty</span>
                                            @else
                                                <span class="text-emerald-500">Aman</span>
                                            @endif
                                        </td>

                                        {{-- Kolom Harga Realisasi --}}
                                        <td class="px-5 py-3 text-right font-mono font-bold text-gray-800 dark:text-white">
                                            Rp {{ number_format($detail['harga_real'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
