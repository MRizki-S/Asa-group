@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-data="{}">
        {{-- Header Laporan --}}
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">Laporan Perbandingan Upah</h2>
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
                                {{ number_format($row['total_real'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse" style="min-width: 650px;">
                            <thead>
                                <tr class="text-xs font-semibold text-gray-500 dark:text-gray-400 bg-gray-50/30 dark:bg-gray-800/30 uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">
                                    <th class="px-6 py-3.5 w-[40%]">Nama Pekerjaan</th>
                                    <th class="px-6 py-3.5 text-right">Budget RAB</th>
                                    <th class="px-6 py-3.5 text-right">Realisasi</th>
                                    <th class="px-6 py-3.5 text-right">Selisih</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                                @foreach ($row['details'] as $detail)
                                    @php $selisih = $detail['nominal_rap'] - $detail['nominal_real']; @endphp
                                    <tr class="text-sm hover:bg-gray-50/60 dark:hover:bg-white/[0.02] transition-colors">
                                        <td class="px-6 py-4 font-medium text-gray-800 dark:text-gray-200">
                                            {{ $detail['nama_upah'] }}
                                        </td>
                                        <td class="px-6 py-4 text-right font-mono text-gray-500 dark:text-gray-400">
                                            Rp {{ number_format($detail['nominal_rap'], 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 text-right font-mono font-bold text-gray-900 dark:text-white">
                                            Rp {{ number_format($detail['nominal_real'], 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 text-right font-mono font-bold {{ $selisih < 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                            {{ $selisih < 0 ? '-' : '+' }} Rp {{ number_format(abs($selisih), 0, ',', '.') }}
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
        @php
            $totalBudgetRap = $laporan->sum('total_rap');
            $totalRealisasi = $laporan->sum('total_real');
            $selisihTotal = $totalBudgetRap - $totalRealisasi;
        @endphp
        <div class="mt-8 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-6 shadow-sm">
            <div class="border-b border-gray-100 dark:border-gray-700 pb-4 mb-4">
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Ringkasan Finansial Upah Pekerjaan</h3>
                <p class="text-xs text-gray-500">Total akumulasi budget RAB, realisasi upah, dan selisih anggaran</p>
            </div>

            <div class="space-y-3 text-sm">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400 font-medium">Total Budget Upah (RAB)</span>
                    <span class="font-mono font-semibold text-gray-800 dark:text-gray-200">Rp {{ number_format($totalBudgetRap, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400 font-medium">Total Realisasi Upah (Terbayar)</span>
                    <span class="font-mono font-semibold text-gray-800 dark:text-gray-200">Rp {{ number_format($totalRealisasi, 0, ',', '.') }}</span>
                </div>
                
                <div class="pt-4 mt-3 border-t-2 border-dashed border-gray-200 dark:border-gray-700 flex justify-between items-center text-base font-bold">
                    <span class="text-gray-900 dark:text-white uppercase tracking-wider">
                        Total Selisih
                    </span>
                    <span class="text-xl font-bold font-mono {{ $selisihTotal < 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                        {{ $selisihTotal < 0 ? '-' : '+' }} Rp {{ number_format(abs($selisihTotal), 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
    </div>
@endsection
