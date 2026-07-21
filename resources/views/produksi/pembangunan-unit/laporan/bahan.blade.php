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
                    class="inline-flex items-center px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 text-xs font-bold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition uppercase shadow-sm">
                    Kembali
                </a>
            </div>
        </div>

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

        {{-- Detail per Langkah QC --}}
        <div class="space-y-6">
            @foreach ($laporan as $row)
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden shadow-sm">
                    <div class="px-6 py-4 bg-gray-50/70 dark:bg-gray-800/60 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row justify-between sm:items-center gap-2">
                        <h4 class="text-sm font-bold text-gray-800 dark:text-gray-100 uppercase tracking-wider">
                            {{ $row['nama_qc'] }}
                        </h4>
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Subtotal Realisasi:</span>
                            <span class="text-sm font-bold font-mono text-blue-600 dark:text-blue-400">Rp
                                {{ number_format($row['total_harga_real'], 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse" style="min-width: 720px;">
                            <thead>
                                <tr class="text-xs font-semibold text-gray-500 dark:text-gray-400 bg-gray-50/30 dark:bg-gray-800/30 uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">
                                    <th class="px-6 py-3.5 w-[35%]">Nama Bahan</th>
                                    <th class="px-6 py-3.5 text-right">Qty RAB</th>
                                    <th class="px-6 py-3.5 text-right">Qty Realisasi</th>
                                    <th class="px-6 py-3.5 text-center">Status Qty</th>
                                    <th class="px-6 py-3.5 text-right">Total Harga Real</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                                @foreach ($row['details'] as $detail)
                                    <tr class="text-sm hover:bg-gray-50/60 dark:hover:bg-white/[0.02] transition-colors">
                                        {{-- Kolom Nama Bahan --}}
                                        <td class="px-6 py-4 font-medium text-gray-800 dark:text-gray-200">
                                            <div class="flex items-center gap-2">
                                                <span>{{ $detail['nama_barang'] }}</span>
                                                @if ($detail['qty_rap'] == 0)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-400 border border-red-200 dark:border-red-800/50">
                                                        Di luar RAB
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- Kolom Qty RAB --}}
                                        <td class="px-6 py-4 text-right font-mono text-gray-500 dark:text-gray-400">
                                            @if ($detail['qty_rap'] > 0)
                                                <span class="font-semibold text-gray-700 dark:text-gray-300">{{ floatval($detail['qty_rap']) }}</span>
                                                <span class="text-xs uppercase text-gray-400 ms-1">{{ $detail['satuan_rap'] }}</span>
                                            @else
                                                <span class="text-gray-300 dark:text-gray-600">-</span>
                                            @endif
                                        </td>

                                        {{-- Kolom Qty Realisasi --}}
                                        <td class="px-6 py-4 text-right font-mono font-semibold">
                                            @if ($detail['qty_real'] > 0)
                                                <span class="{{ $detail['qty_real'] > $detail['qty_rap'] && $detail['qty_rap'] > 0 ? 'text-red-600 dark:text-red-400 font-bold' : 'text-gray-900 dark:text-white' }}">
                                                    {{ floatval($detail['qty_real']) }}
                                                </span>
                                                <span class="text-xs text-gray-400 font-normal uppercase ms-1">{{ $detail['satuan_real'] }}</span>
                                            @else
                                                <span class="text-gray-300 dark:text-gray-600 font-normal">-</span>
                                            @endif
                                        </td>

                                        {{-- Kolom Status Qty --}}
                                        <td class="px-6 py-4 text-center">
                                            @if ($detail['qty_rap'] == 0)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-400">Unplanned</span>
                                            @elseif($detail['qty_real'] == 0)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500 dark:bg-gray-700/50 dark:text-gray-400">Belum Dipakai</span>
                                            @elseif($detail['qty_real'] > $detail['qty_rap'])
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 dark:bg-red-950/30 dark:text-red-400">Over Qty</span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400">Sesuai RAB</span>
                                            @endif
                                        </td>

                                        {{-- Kolom Harga Realisasi --}}
                                        <td class="px-6 py-4 text-right font-mono font-bold whitespace-nowrap text-gray-900 dark:text-white">
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

        {{-- Receipt-style Total Summary Footer di Paling Bawah --}}
        <div class="mt-8 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-6 shadow-sm">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 border-b border-gray-100 dark:border-gray-700 pb-4 mb-4">
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Ringkasan Biaya Realisasi Bahan</h3>
                    <p class="text-xs text-gray-500">Total akumulasi biaya realisasi bahan dari seluruh tahapan QC</p>
                </div>
                @if($itemLuarRap > 0)
                    <div class="inline-flex items-center px-3 py-1.5 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60 text-amber-800 dark:text-amber-300 text-xs font-semibold">
                        {{ $itemLuarRap }} Jenis Bahan Di Luar RAB
                    </div>
                @endif
            </div>

            <div class="space-y-3">
                @foreach ($laporan as $row)
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-600 dark:text-gray-400 font-medium">{{ $row['nama_qc'] }}</span>
                        <span class="font-mono font-semibold text-gray-800 dark:text-gray-200">Rp {{ number_format($row['total_harga_real'], 0, ',', '.') }}</span>
                    </div>
                @endforeach
                
                <div class="pt-3 mt-3 border-t-2 border-dashed border-gray-200 dark:border-gray-700 flex justify-between items-center text-sm font-semibold">
                    <span class="text-gray-800 dark:text-gray-200">Total Biaya Keseluruhan</span>
                    <span class="text-base font-bold font-mono text-emerald-600 dark:text-emerald-400">Rp {{ number_format($laporan->sum('total_harga_real'), 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>
@endsection
