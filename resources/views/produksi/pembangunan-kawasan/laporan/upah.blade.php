@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-data="{}">
        {{-- Header Laporan --}}
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">Laporan Perbandingan Upah Kawasan</h2>
                <p class="text-sm text-gray-500">Kawasan: <span
                        class="font-bold text-blue-600 dark:text-blue-400 uppercase">{{ $kawasan->nama ?? '-' }}</span></p>
            </div>
            <div class="flex gap-2">
                <a href="{{ url()->previous() }}"
                    class="inline-flex items-center px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 text-xs font-bold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition uppercase shadow-sm">
                    Kembali
                </a>
            </div>
        </div>

        {{-- Detail --}}
        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden shadow-sm">
                <div class="px-6 py-4 bg-gray-50/70 dark:bg-gray-800/60 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row justify-between sm:items-center gap-2">
                    <h4 class="text-sm font-bold text-gray-800 dark:text-gray-100 uppercase tracking-wider">
                        Rincian Pekerjaan Upah Kawasan
                    </h4>
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Subtotal Realisasi:</span>
                        <span class="text-sm font-bold font-mono text-blue-600 dark:text-blue-400">Rp
                            {{ number_format($laporan['total_real'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse" style="min-width: 650px;">
                        <thead>
                            <tr class="text-xs font-semibold text-gray-500 dark:text-gray-400 bg-gray-50/30 dark:bg-gray-800/30 uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">
                                <th class="px-5 py-3.5 w-[35%]">Nama Pekerjaan</th>
                                <th class="px-5 py-3.5 text-right whitespace-nowrap">Request (Diajukan)</th>
                                <th class="px-5 py-3.5 text-right whitespace-nowrap">Realisasi</th>
                                <th class="px-5 py-3.5 text-right whitespace-nowrap">Selisih</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                            @forelse ($laporan['details'] ?? [] as $detail)
                                @php $selisih = $detail['nominal_request'] - $detail['nominal_real']; @endphp
                                <tr class="text-xs md:text-sm hover:bg-gray-50/60 dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="px-5 py-3.5 font-medium text-gray-800 dark:text-gray-200">
                                        {{ $detail['nama_upah'] }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-mono whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        Rp {{ number_format($detail['nominal_request'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-mono whitespace-nowrap font-bold text-gray-900 dark:text-white">
                                        Rp {{ number_format($detail['nominal_real'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-mono whitespace-nowrap font-bold {{ $selisih < 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                        {{ $selisih < 0 ? '-' : '+' }} Rp {{ number_format(abs($selisih), 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-6 text-center text-gray-500 dark:text-gray-400 text-sm">
                                        Belum ada data upah.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Receipt-style Total Summary Footer --}}
        @php
            $totalRequest = $laporan['total_request'] ?? 0;
            $totalReal = $laporan['total_real'] ?? 0;
            $selisihTotal = $totalRequest - $totalReal;
        @endphp
        <div class="mt-8 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-6 shadow-sm">
            <div class="border-b border-gray-100 dark:border-gray-700 pb-4 mb-4">
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Ringkasan Upah Kawasan</h3>
                <p class="text-xs text-gray-500">Total akumulasi nominal pengajuan request, realisasi upah, dan selisih</p>
            </div>

            <div class="space-y-3 text-sm">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400 font-medium">Total Nominal Request (Diajukan)</span>
                    <span class="font-mono font-semibold text-gray-800 dark:text-gray-200">Rp {{ number_format($totalRequest, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400 font-medium">Total Realisasi Upah</span>
                    <span class="font-mono font-semibold text-gray-800 dark:text-gray-200">Rp {{ number_format($totalReal, 0, ',', '.') }}</span>
                </div>
                
                <div class="pt-3 mt-3 border-t-2 border-dashed border-gray-200 dark:border-gray-700 flex justify-between items-center text-sm font-semibold">
                    <span class="text-gray-800 dark:text-gray-200">Total Selisih</span>
                    <span class="text-base font-bold font-mono {{ $selisihTotal < 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                        {{ $selisihTotal < 0 ? '-' : '+' }} Rp {{ number_format(abs($selisihTotal), 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
    </div>
@endsection
