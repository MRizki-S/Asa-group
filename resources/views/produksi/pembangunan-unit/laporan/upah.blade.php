@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-data="{}">
        {{-- Header Laporan --}}
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">Laporan Perbandingan Upah</h2>
                <p class="text-sm text-gray-500">Unit: <span
                        class="font-bold text-blue-600 uppercase">{{ $unit->unit->nama_unit }}</span></p>
            </div>
            <div class="flex gap-2">
                <button onclick="window.print()"
                    class="px-4 py-2 bg-gray-100 text-gray-700 text-xs font-bold rounded-lg hover:bg-gray-200 transition uppercase">
                    <i class="fa-solid fa-print me-2"></i>Cetak Laporan
                </button>
                <a href="{{ url()->previous() }}"
                    class="px-4 py-2 bg-white border border-gray-200 text-gray-600 text-xs font-bold rounded-lg hover:bg-gray-50 transition uppercase">
                    Kembali </a>
            </div>
        </div>

        {{-- Ringkasan Total --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-2xl">
                <p class="text-[10px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest">Total Budget
                    (RAP)</p>
                <p class="text-xl font-bold text-gray-800 dark:text-white">Rp
                    {{ number_format($laporan->sum('total_rap'), 0, ',', '.') }}</p>
            </div>
            <div
                class="p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800 rounded-2xl">
                <p class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest">Total
                    Realisasi</p>
                <p class="text-xl font-bold text-gray-800 dark:text-white">Rp
                    {{ number_format($laporan->sum('total_real'), 0, ',', '.') }}</p>
            </div>
            @php $selisihTotal = $laporan->sum('total_rap') - $laporan->sum('total_real'); @endphp
            <div
                class="p-4 {{ $selisihTotal < 0 ? 'bg-red-50 border-red-100' : 'bg-gray-50 border-gray-100' }} rounded-2xl border">
                <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Sisa / Over Budget</p>
                <p class="text-xl font-bold {{ $selisihTotal < 0 ? 'text-red-600' : 'text-gray-800' }}">
                    Rp {{ number_format($selisihTotal, 0, ',', '.') }}
                </p>
            </div>
        </div>

        {{-- Detail per Langkah QC --}}
        <div class="space-y-6">
            @foreach ($laporan as $row)
                <div
                    class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl overflow-hidden shadow-sm">
                    <div
                        class="px-5 py-3 bg-gray-50/50 dark:bg-white/5 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                        <h4 class="text-xs font-black text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                            {{ $row['nama_qc'] }}</h4>
                        <div class="text-right">
                            <span class="text-[10px] text-gray-400 uppercase font-bold">Subtotal Realisasi:</span>
                            <span class="text-sm font-bold text-blue-600 ms-2">Rp
                                {{ number_format($row['total_real'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter border-b border-gray-50 dark:border-gray-700">
                                <th class="px-5 py-3">Nama Pekerjaan</th>
                                <th class="px-5 py-3 text-right">Budget RAP</th>
                                <th class="px-5 py-3 text-right">Realisasi</th>
                                <th class="px-5 py-3 text-right">Selisih</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                            @foreach ($row['details'] as $detail)
                                @php $selisih = $detail['nominal_rap'] - $detail['nominal_real']; @endphp
                                <tr class="text-sm hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="px-5 py-3 font-medium text-gray-700 dark:text-gray-300">
                                        {{ $detail['nama_upah'] }}</td>
                                    <td class="px-5 py-3 text-right font-mono text-gray-500">Rp
                                        {{ number_format($detail['nominal_rap'], 0, ',', '.') }}</td>
                                    <td class="px-5 py-3 text-right font-mono font-bold text-gray-800 dark:text-white">Rp
                                        {{ number_format($detail['nominal_real'], 0, ',', '.') }}</td>
                                    <td
                                        class="px-5 py-3 text-right font-mono font-bold {{ $selisih < 0 ? 'text-red-500' : 'text-emerald-500' }}">
                                        {{ $selisih < 0 ? '-' : '+' }} Rp {{ number_format(abs($selisih), 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
    </div>
@endsection
