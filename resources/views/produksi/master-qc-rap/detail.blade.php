@extends('layouts.app')

@section('pageActive', 'MasterQC-RAP')

@section('content')
<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

    <div x-data="{ pageName: 'Detail Master QC & RAP' }">
        @include('partials.breadcrumb')
    </div>

    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-xl font-bold text-gray-800 dark:text-white">Review Master QC & RAP</h2>
        <div class="flex gap-3">
            <button onclick="window.print()" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
                Cetak Detail
            </button>
            <a href="{{ route('produksi.masterQcRap.edit', $container->id) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                Edit Data
            </a>
        </div>
    </div>

    {{-- 1. INFORMASI UTAMA --}}
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800 mb-8 shadow-sm overflow-hidden">
        <div class="bg-gray-50/50 dark:bg-gray-700/50 px-5 py-3 border-b dark:border-gray-700">
            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Informasi Utama</h3>
        </div>
        <div class="p-5 sm:p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase mb-1">Type Unit</p>
                    <p class="text-lg font-semibold text-gray-800 dark:text-white">{{ $container->type->nama_type }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase mb-1">Nama Container</p>
                    <p class="text-lg font-semibold text-gray-800 dark:text-white">{{ $container->nama_container }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. LANGKAH QC --}}
    <div class="mb-8">
        <div class="flex items-center gap-4 mb-4">
            <div class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></div>
            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500">1. Daftar Langkah QC</h3>
            <div class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></div>
        </div>

        <div class="space-y-4">
            @foreach($container->urutan as $index => $qc)
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="flex-none w-10 h-10 rounded-full bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold">
                            {{ $qc->qc_ke }}
                        </div>
                        <div class="flex-1">
                            <h4 class="text-base font-bold text-gray-800 dark:text-white">{{ $qc->nama_qc }}</h4>
                        </div>
                    </div>

                    <div class="ml-14 space-y-2">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Tugas / Checklist:</p>
                        <ul class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-2">
                            @foreach($qc->tugas as $tIndex => $tugas)
                                <li class="flex items-start gap-3 text-sm text-gray-600 dark:text-gray-300">
                                    <svg class="w-5 h-5 text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $tugas->tugas }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- 3. RAP BAHAN --}}
    <div class="mb-8">
        <div class="flex items-center gap-4 mb-4">
            <div class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></div>
            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500">2. Rencana Anggaran Bahan</h3>
            <div class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></div>
        </div>

        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden shadow-sm">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 uppercase text-xs font-bold">
                    <tr>
                        <th class="px-6 py-4">Langkah QC</th>
                        <th class="px-6 py-4">Nama Barang</th>
                        <th class="px-6 py-4 text-right">Jumlah Standar</th>
                        <th class="px-6 py-4">Satuan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($container->rapBahan as $bahan)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                <span class="px-2 py-1 rounded bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 text-xs">
                                    QC-{{ $bahan->urutan->qc_ke }}: {{ $bahan->urutan->nama_qc }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ $bahan->barang_id ?? 'Semen' }} (dummy)</td>
                            <td class="px-6 py-4 text-right font-mono font-bold text-gray-900 dark:text-white">{{ number_format($bahan->jumlah_kebutuhan_standar, 2) }}</td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $bahan->satuan }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-10 text-center text-gray-400 italic">Data bahan tidak tersedia.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 4. RAP UPAH --}}
    <div class="mb-12">
        <div class="flex items-center gap-4 mb-4">
            <div class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></div>
            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500">3. Rencana Anggaran Upah</h3>
            <div class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></div>
        </div>

        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden shadow-sm">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 uppercase text-xs font-bold">
                    <tr>
                        <th class="px-6 py-4">Langkah QC</th>
                        <th class="px-6 py-4">Jenis Pekerjaan</th>
                        <th class="px-6 py-4 text-right">Nominal Standar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($container->rapUpah as $upah)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                <span class="px-2 py-1 rounded bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400 text-xs">
                                    QC-{{ $upah->urutan->qc_ke }}: {{ $upah->urutan->nama_qc }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ $upah->masterUpah->nama_upah }}</td>
                            <td class="px-6 py-4 text-right font-mono font-bold text-blue-600 dark:text-blue-400">
                                Rp {{ number_format($upah->nominal_standar, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-6 py-10 text-center text-gray-400 italic">Data upah tidak tersedia.</td></tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50 dark:bg-gray-700/50 border-t dark:border-gray-700">
                    <tr>
                        <td colspan="2" class="px-6 py-4 text-right font-bold text-gray-800 dark:text-white uppercase text-xs">Total Anggaran Upah:</td>
                        <td class="px-6 py-4 text-right font-mono font-bold text-lg text-blue-700 dark:text-blue-300">
                            Rp {{ number_format($container->rapUpah->sum('nominal_standar'), 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Footer Nav --}}
    <div class="flex justify-start border-t border-gray-200 dark:border-gray-700 pt-6">
        <a href="{{ route('produksi.masterQcRap.index') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-blue-600 transition">
            <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar Master
        </a>
    </div>
</div>
@endsection
