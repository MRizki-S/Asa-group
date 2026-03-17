@extends('layouts.app')

@section('pageActive', 'DaftarNotaMasuk')

@section('content')

<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-init="$dispatch('sidebar-minimize')">

    <!-- Breadcrumb -->
    <div x-data="{ pageName: 'DaftarNotaMasuk' }">
        @include('partials.breadcrumb')
    </div>

    {{-- Detail Nota --}}
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
        <div class="px-5 py-4 sm:px-6 sm:py-5">
            <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 border-b border-gray-100 dark:border-gray-800">
                Detail Nota
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                {{-- Nomor Nota --}}
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Nomor Nota
                    </label>
                    <div class="w-full bg-gray-100 border border-gray-300 text-gray-600 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-gray-300">
                        {{ $nota->nomor_nota }}
                    </div>
                </div>

                {{-- Tanggal Nota --}}
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Tanggal Nota
                    </label>
                    <div class="w-full bg-gray-100 border border-gray-300 text-gray-600 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-gray-300">
                        {{ $nota->tanggal_nota->format('d-m-Y') }}
                    </div>
                </div>

                {{-- Status --}}
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Status
                    </label>
                    <div class="w-full bg-green-50 border border-green-300 text-green-800 text-sm font-bold rounded-lg p-2.5 dark:bg-green-900/30 dark:border-green-600 dark:text-green-400 text-center">
                        {{ $nota->status }}
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Supplier --}}
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Supplier
                    </label>
                    <div class="w-full bg-gray-50 border border-gray-300 text-gray-800 text-sm rounded-lg p-2.5 dark:bg-gray-700/50 dark:text-gray-200">
                        {{ $nota->supplier }}
                    </div>
                </div>

                {{-- Cara Bayar --}}
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Cara Bayar
                    </label>
                    <div class="w-full bg-gray-50 border border-gray-300 text-gray-800 text-sm rounded-lg p-2.5 dark:bg-gray-700/50 dark:text-gray-200 uppercase">
                        {{ $nota->cara_bayar }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Daftar Barang Masuk --}}
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
        <div class="px-5 py-4 sm:px-6 sm:py-5">
            <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 border-b border-gray-100 dark:border-gray-800">
                Barang yang Diterima
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse border border-gray-300">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                        <tr>
                            <th class="border border-gray-300 px-3 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-200 w-[30%]">Barang</th>
                            <th class="border border-gray-300 px-3 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-200">Merk</th>
                            <th class="border border-gray-300 px-3 py-2 text-center text-sm font-semibold text-gray-700 dark:text-gray-200">Satuan</th>
                            <th class="border border-gray-300 px-3 py-2 text-center text-sm font-semibold text-gray-700 dark:text-gray-200">Jumlah</th>
                            <th class="border border-gray-300 px-3 py-2 text-right text-sm font-semibold text-gray-700 dark:text-gray-200">Harga Satuan</th>
                            <th class="border border-gray-300 px-3 py-2 text-right text-sm font-semibold text-gray-700 dark:text-gray-200">Harga Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($nota->details as $detail)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="border border-gray-300 px-3 py-2">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $detail->barang->nama_barang }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $detail->barang->kode_barang }}
                                </div>
                            </td>
                            <td class="border border-gray-300 px-3 py-2 text-sm text-gray-800 dark:text-white">
                                {{ $detail->merk ?? '-' }}
                            </td>
                            <td class="border border-gray-300 px-3 py-2 text-center text-sm text-gray-800 dark:text-white">
                                {{ $detail->satuan->nama ?? '-' }}
                            </td>
                            <td class="border border-gray-300 px-3 py-2 text-center text-sm font-bold text-gray-900 dark:text-white">
                                {{ number_format($detail->jumlah_input, 0, ',', '.') }}
                            </td>
                            <td class="border border-gray-300 px-3 py-2 text-right text-sm text-gray-800 dark:text-white">
                                Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}
                            </td>
                            <td class="border border-gray-300 px-3 py-2 text-right text-sm font-bold text-gray-900 dark:text-white">
                                Rp {{ number_format($detail->harga_total, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <td colspan="5" class="border border-gray-300 px-3 py-2 text-right text-sm font-bold text-gray-900 dark:text-white">
                                TOTAL KESELURUHAN
                            </td>
                            <td class="border border-gray-300 px-3 py-2 text-right text-sm font-extrabold text-blue-600 dark:text-blue-400">
                                Rp {{ number_format($nota->details->sum('harga_total'), 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Tombol Aksi -->
    <div class="flex justify-start items-center bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <a href="{{ route('gudang.daftarNotaMasuk.index') }}" 
           class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-all dark:text-white dark:bg-gray-700 dark:hover:bg-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Daftar
        </a>
    </div>
</div>

@endsection