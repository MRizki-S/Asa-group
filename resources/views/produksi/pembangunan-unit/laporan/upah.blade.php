@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-data="{}">
        {{-- Header Laporan --}}
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">Laporan Perbandingan Upah</h2>
                <p class="text-sm text-gray-500">Unit: <span
                        class="font-bold text-blue-600 dark:text-blue-400 uppercase">{{ $unit->unit->nama_unit }}</span></p>
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
            <div class="flex-1 min-w-[240px] max-w-sm p-4 bg-blue-50 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-800/60 rounded-2xl">
                <p class="text-[10px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest mb-1">Total Budget (RAB)</p>
                <p class="text-xl font-bold text-gray-800 dark:text-white">Rp
                    {{ number_format($laporan->sum('total_rap'), 0, ',', '.') }}</p>
            </div>
            <div class="flex-1 min-w-[240px] max-w-sm p-4 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-800/60 rounded-2xl">
                <p class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest mb-1">Total Realisasi</p>
                <p class="text-xl font-bold text-gray-800 dark:text-white">Rp
                    {{ number_format($laporan->sum('total_real'), 0, ',', '.') }}</p>
            </div>
            @php $selisihTotal = $laporan->sum('total_rap') - $laporan->sum('total_real'); @endphp
            <div class="flex-1 min-w-[240px] max-w-sm p-4 rounded-2xl border transition-all duration-300
                {{ $selisihTotal < 0 
                    ? 'bg-red-50 border-red-100 dark:bg-red-950/20 dark:border-red-800/60' 
                    : 'bg-gray-50 border-gray-200 dark:bg-gray-800/30 dark:border-gray-700' }}">
                <p class="text-[10px] font-black uppercase tracking-widest mb-1 
                    {{ $selisihTotal < 0 ? 'text-red-500 dark:text-red-400' : 'text-gray-500 dark:text-gray-400' }}">
                    Sisa / Over Budget
                </p>
                <p class="text-xl font-bold {{ $selisihTotal < 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-800 dark:text-white' }}">
                    Rp {{ number_format($selisihTotal, 0, ',', '.') }}
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
                                {{ number_format($row['total_real'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse" style="min-width: 600px;">
                            <thead>
                                <tr class="text-[10px] font-bold text-gray-400 dark:text-gray-500 bg-gray-50/50 dark:bg-gray-800/20 uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">
                                    <th class="px-5 py-3 w-[40%]">Nama Pekerjaan</th>
                                    <th class="px-5 py-3 text-right">Budget RAB</th>
                                    <th class="px-5 py-3 text-right">Realisasi</th>
                                    <th class="px-5 py-3 text-right">Selisih</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                                @foreach ($row['details'] as $detail)
                                    @php $selisih = $detail['nominal_rap'] - $detail['nominal_real']; @endphp
                                    <tr class="text-xs hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition-colors">
                                        <td class="px-5 py-3 font-medium text-gray-700 dark:text-gray-300">
                                            {{ $detail['nama_upah'] }}</td>
                                        <td class="px-5 py-3 text-right font-mono text-gray-500">Rp
                                            {{ number_format($detail['nominal_rap'], 0, ',', '.') }}</td>
                                        <td class="px-5 py-3 text-right font-mono font-bold text-gray-800 dark:text-white">Rp
                                            {{ number_format($detail['nominal_real'], 0, ',', '.') }}</td>
                                        <td class="px-5 py-3 text-right font-mono font-bold {{ $selisih < 0 ? 'text-red-500' : 'text-emerald-500' }}">
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
    </div>
@endsection
