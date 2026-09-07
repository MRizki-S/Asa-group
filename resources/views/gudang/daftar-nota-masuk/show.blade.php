@extends('layouts.app')

@section('pageActive', 'DaftarNotaMasuk')

@section('content')

<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-data="{ showEditModal: false, isSubmitting: false }">

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

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
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
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-medium text-gray-900 dark:text-white">
                            Tanggal Nota
                        </label>
                        @if(auth()->user()->hasRole(['Superadmin', 'superadmin']))
                        <button type="button" @click="showEditModal = true"
                            class="text-xs font-semibold text-amber-600 dark:text-amber-400 hover:underline flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                            Edit
                        </button>
                        @endif
                    </div>
                    <div class="w-full bg-gray-100 border border-gray-300 text-gray-600 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-gray-300">
                        {{ $nota->tanggal_nota->format('d-m-Y') }}
                    </div>
                </div>

                {{-- Jenis Nota --}}
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Jenis Nota
                    </label>
                    @php
                        $jenisMap = [
                            'supplier' => ['label' => 'Supplier', 'class' => 'bg-blue-100 border-blue-300 text-blue-800 dark:bg-blue-900/30 dark:border-blue-600 dark:text-blue-400'],
                            'produksi_rakitan' => ['label' => 'Produksi Rakitan', 'class' => 'bg-purple-100 border-purple-300 text-purple-800 dark:bg-purple-900/30 dark:border-purple-600 dark:text-purple-400'],
                            'return_barang' => ['label' => 'Return Barang', 'class' => 'bg-orange-100 border-orange-300 text-orange-800 dark:bg-orange-900/30 dark:border-orange-600 dark:text-orange-400'],
                            'adjustment_stock' => ['label' => 'Adjustment Stock', 'class' => 'bg-gray-100 border-gray-300 text-gray-800 dark:bg-gray-700 dark:border-gray-500 dark:text-gray-300'],
                        ];
                        $jenis = $jenisMap[$nota->jenis_nota] ?? ['label' => $nota->jenis_nota, 'class' => 'bg-gray-100 border-gray-300 text-gray-700'];
                    @endphp
                    <div class="w-full border text-sm font-semibold rounded-lg p-2.5 text-center {{ $jenis['class'] }}">
                        {{ $jenis['label'] }}
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


            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Supplier --}}
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Supplier
                    </label>
                    <div class="w-full bg-gray-50 border border-gray-300 text-gray-800 text-sm rounded-lg p-2.5 dark:bg-gray-700/50 dark:text-gray-200">
                        {{ $nota->supplier->kode_supplier ?? '' }} - {{ $nota->supplier->nama_supplier ?? '-' }}
                    </div>
                </div>

                {{-- Gudang Tujuan --}}
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Gudang Tujuan
                    </label>
                    <div class="w-full bg-gray-50 border border-gray-300 text-gray-800 text-sm rounded-lg p-2.5 dark:bg-gray-700/50 dark:text-gray-200">
                        @if ($nota->stock_type === 'HUB')
                            Gudang HUB
                        @else
                            {{ $nota->ubs->kode_ubs ?? '' }} - {{ $nota->ubs->nama_ubs ?? '-' }}
                        @endif
                    </div>
                </div>

                {{-- Cara Bayar --}}
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Cara Bayar
                    </label>
                    <div class="w-full bg-gray-50 border border-gray-300 text-gray-800 text-sm rounded-lg p-2.5 dark:bg-gray-700/50 dark:text-gray-200 uppercase">
                        {{ $nota->cara_bayar ?? '-' }}
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
    <div class="flex flex-wrap justify-between items-center bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm gap-3">
        <a href="{{ route('gudang.daftarNotaMasuk.index') }}" 
           class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-all dark:text-white dark:bg-gray-700 dark:hover:bg-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Daftar
        </a>

        @if(auth()->user()->hasRole(['Superadmin', 'superadmin']))
        <button type="button" @click="showEditModal = true"
            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-amber-600 hover:bg-amber-700 rounded-lg shadow-sm transition active:scale-95">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit Tanggal Nota
        </button>
        @endif
    </div>

    {{-- Modal Edit Tanggal Nota (Khusus Superadmin) --}}
    @if(auth()->user()->hasRole(['Superadmin', 'superadmin']))
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4" x-transition>
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl max-w-md w-full p-5 border border-gray-200 dark:border-gray-800" @click.outside="showEditModal = false">
            <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-800 pb-3 mb-4">
                <div class="flex items-center gap-2">
                    <span class="p-2 rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Edit Tanggal Nota Masuk</h3>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">Khusus Role Superadmin</p>
                    </div>
                </div>
                <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-lg">
                    ✕
                </button>
            </div>

            <form method="POST" action="{{ route('gudang.daftarNotaMasuk.updateTanggal', $nota->nomor_nota) }}" @submit="isSubmitting = true">
                @csrf
                @method('PATCH')

                <div class="space-y-4">
                    <div>
                        <label class="block mb-1 text-xs font-semibold text-gray-700 dark:text-gray-300">
                            Nomor Nota
                        </label>
                        <input type="text" value="{{ $nota->nomor_nota }}" readonly
                            class="w-full bg-gray-100 border border-gray-300 text-gray-700 text-xs font-mono font-bold rounded-lg p-2.5 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block mb-1 text-xs font-semibold text-gray-700 dark:text-gray-300">
                            Tanggal Nota Baru <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="tanggal_nota" value="{{ \Carbon\Carbon::parse($nota->tanggal_nota)->format('Y-m-d') }}" required
                            class="w-full bg-white border border-gray-300 text-gray-900 text-xs rounded-lg p-2.5 focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white dark:focus:ring-amber-500 dark:focus:border-amber-500">
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-1">
                            * Perubahan tanggal ini otomatis menyinkronkan data di Stock Ledger dan Rekap Nota.
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 mt-5 pt-3 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" @click="showEditModal = false"
                        class="px-4 py-2 text-xs font-semibold text-gray-700 bg-gray-100 dark:bg-gray-800 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                        Batal
                    </button>
                    <button type="submit" :disabled="isSubmitting"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-lg shadow-sm transition disabled:opacity-50">
                        <span x-show="!isSubmitting">Simpan Perubahan</span>
                        <span x-show="isSubmitting" x-cloak>Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>

@endsection