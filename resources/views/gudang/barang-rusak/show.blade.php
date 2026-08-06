@extends('layouts.app')

@section('pageActive', 'DaftarBarangRusak')

@section('content')
@php
    if (!function_exists('formatQtyBarangRusak')) {
        function formatQtyBarangRusak($value) {
            $formatted = number_format((float) $value, 3, ',', '.');
            $formatted = rtrim($formatted, '0');
            return rtrim($formatted, ',');
        }
    }
@endphp

<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">
    <div x-data="{ pageName: 'DetailBarangRusak' }">
        @include('partials.breadcrumb')
    </div>

    <div class="mb-6 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="px-5 py-4 sm:px-6 sm:py-5">
            <div class="mb-4 flex flex-col gap-3 border-b border-gray-100 pb-4 sm:flex-row sm:items-center sm:justify-between dark:border-gray-800">
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                    Detail Barang Rusak
                </h3>
                <span class="w-fit rounded-full px-3 py-1 text-xs font-bold {{ $barangRusak->status === 'posted' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ strtoupper($barangRusak->status) }}
                </span>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Nomor Barang Rusak</label>
                    <div class="rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-sm font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                        {{ $barangRusak->nomor_barang_rusak }}
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Tanggal Rusak</label>
                    <div class="rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-800 dark:bg-gray-700/50 dark:text-gray-200">
                        {{ $barangRusak->tgl_rusak?->format('d-m-Y') }}
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Gudang Sumber</label>
                    <div class="rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-800 dark:bg-gray-700/50 dark:text-gray-200">
                        {{ $barangRusak->stock_type === 'UBS' ? 'UBS - ' . ($barangRusak->ubs?->nama_ubs ?? '-') : 'HUB (Pusat)' }}
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Created By</label>
                    <div class="rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-800 dark:bg-gray-700/50 dark:text-gray-200">
                        {{ $barangRusak->creator?->nama_lengkap ?? $barangRusak->creator?->username ?? '-' }}
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Posted At</label>
                    <div class="rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-800 dark:bg-gray-700/50 dark:text-gray-200">
                        {{ $barangRusak->posted_at?->format('d-m-Y H:i') ?? '-' }}
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Keterangan</label>
                    <div class="rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-800 dark:bg-gray-700/50 dark:text-gray-200">
                        {{ $barangRusak->keterangan ?? '-' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-6 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="px-5 py-4 sm:px-6 sm:py-5">
            <h3 class="mb-4 border-b border-gray-100 pb-4 text-base font-medium text-gray-800 dark:border-gray-800 dark:text-white/90">
                Barang Yang Rusak
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse border border-gray-300">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                        <tr>
                            <th class="border border-gray-300 px-3 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-200">Kode Barang</th>
                            <th class="border border-gray-300 px-3 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-200">Nama Barang</th>
                            <th class="border border-gray-300 px-3 py-2 text-center text-sm font-semibold text-gray-700 dark:text-gray-200">Satuan</th>
                            <th class="border border-gray-300 px-3 py-2 text-right text-sm font-semibold text-gray-700 dark:text-gray-200">Qty Rusak</th>
                            <th class="border border-gray-300 px-3 py-2 text-right text-sm font-semibold text-gray-700 dark:text-gray-200">Qty Base</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="border border-gray-300 px-3 py-2 text-sm font-medium text-gray-900 dark:text-white">
                                {{ $barangRusak->barang?->kode_barang ?? '-' }}
                            </td>
                            <td class="border border-gray-300 px-3 py-2 text-sm text-gray-800 dark:text-white">
                                {{ $barangRusak->barang?->nama_barang ?? '-' }}
                            </td>
                            <td class="border border-gray-300 px-3 py-2 text-center text-sm text-gray-800 dark:text-white">
                                {{ $barangRusak->satuan?->nama ?? '-' }}
                            </td>
                            <td class="border border-gray-300 px-3 py-2 text-right text-sm font-bold text-gray-900 dark:text-white">
                                {{ formatQtyBarangRusak($barangRusak->qty_out) }}
                            </td>
                            <td class="border border-gray-300 px-3 py-2 text-right text-sm font-bold text-gray-900 dark:text-white">
                                {{ formatQtyBarangRusak($barangRusak->qty_base) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mb-6 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="px-5 py-4 sm:px-6 sm:py-5">
            <h3 class="mb-4 border-b border-gray-100 pb-4 text-base font-medium text-gray-800 dark:border-gray-800 dark:text-white/90">
                FIFO Nota Barang Masuk Yang Dipakai
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse border border-gray-300">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                        <tr>
                            <th class="border border-gray-300 px-3 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-200">No. Nota</th>
                            <th class="border border-gray-300 px-3 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-200">Tanggal Nota</th>
                            <th class="border border-gray-300 px-3 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-200">Supplier</th>
                            <th class="border border-gray-300 px-3 py-2 text-right text-sm font-semibold text-gray-700 dark:text-gray-200">Qty Diambil</th>
                            <th class="border border-gray-300 px-3 py-2 text-right text-sm font-semibold text-gray-700 dark:text-gray-200">Harga Satuan</th>
                            <th class="border border-gray-300 px-3 py-2 text-right text-sm font-semibold text-gray-700 dark:text-gray-200">Harga Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($barangRusak->fifoDetails as $fifo)
                            <tr class="hover:bg-yellow-50 dark:hover:bg-gray-800/50">
                                <td class="border border-gray-300 px-3 py-2 text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $fifo->notaDetail?->nota?->nomor_nota ?? '-' }}
                                </td>
                                <td class="border border-gray-300 px-3 py-2 text-sm text-gray-800 dark:text-white">
                                    {{ $fifo->notaDetail?->nota?->tanggal_nota?->format('d-m-Y') ?? '-' }}
                                </td>
                                <td class="border border-gray-300 px-3 py-2 text-sm text-gray-800 dark:text-white">
                                    {{ $fifo->notaDetail?->nota?->supplier->nama_supplier ?? '-' }}
                                </td>
                                <td class="border border-gray-300 px-3 py-2 text-right text-sm font-bold text-gray-900 dark:text-white">
                                    {{ formatQtyBarangRusak($fifo->qty_base_diambil) }}
                                </td>
                                <td class="border border-gray-300 px-3 py-2 text-right text-sm text-gray-800 dark:text-white">
                                    Rp {{ number_format((float) $fifo->harga_satuan_base, 0, ',', '.') }}
                                </td>
                                <td class="border border-gray-300 px-3 py-2 text-right text-sm font-bold text-blue-700">
                                    Rp {{ number_format((float) $fifo->harga_total, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="border border-gray-300 px-3 py-3 text-center text-sm text-gray-500">
                                    Belum ada detail FIFO.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <td colspan="5" class="border border-gray-300 px-3 py-2 text-right text-sm font-bold text-gray-900 dark:text-white">
                                Total Nilai Barang Rusak
                            </td>
                            <td class="border border-gray-300 px-3 py-2 text-right text-sm font-extrabold text-blue-700">
                                Rp {{ number_format((float) $barangRusak->fifoDetails->sum('harga_total'), 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div x-data="{ cancelOpen: false, cancelSubmitting: false }" class="flex flex-col gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between dark:border-gray-700 dark:bg-gray-800">
        <a href="{{ route('gudang.barangRusak.index') }}"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-gray-100 px-6 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-200 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">
            Kembali ke Daftar
        </a>

        @if ($barangRusak->status === 'posted')
            <button type="button" @click="cancelOpen = true"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-red-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-red-700">
                Cancel / Kembalikan ke Stock
            </button>

            <div x-show="cancelOpen" x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-gray-900/50" @click="cancelOpen = false"></div>
                <div class="relative w-full max-w-lg rounded-xl bg-white p-6 shadow-xl dark:bg-gray-800">
                    <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">
                        Batalkan Barang Rusak?
                    </h3>
                    <p class="mb-4 text-sm text-gray-600 dark:text-gray-300">
                        Sistem akan mengembalikan qty ke stock {{ $barangRusak->stock_type }} dan mengembalikan qty ke layer nota FIFO yang dulu dipakai.
                    </p>

                    <form method="POST" action="{{ route('gudang.barangRusak.cancel', $barangRusak->nomor_barang_rusak) }}" x-ref="cancelForm">
                        @csrf
                        @method('PATCH')

                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                            Alasan Pembatalan
                        </label>
                        <textarea name="cancel_reason" rows="3"
                            class="mb-4 w-full rounded-lg border border-gray-300 bg-white p-2.5 text-sm text-gray-900 focus:border-red-500 focus:ring-red-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            placeholder="Opsional"></textarea>

                        <div class="flex justify-end gap-3">
                            <button type="button" @click="cancelOpen = false"
                                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                                Batal
                            </button>
                            <button type="button"
                                @click="cancelSubmitting = true; $refs.cancelForm.requestSubmit()"
                                :disabled="cancelSubmitting"
                                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-bold text-white hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60">
                                <span x-show="!cancelSubmitting">Ya, Kembalikan Stock</span>
                                <span x-show="cancelSubmitting">Memproses...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                Sudah dibatalkan oleh {{ $barangRusak->canceller?->nama_lengkap ?? $barangRusak->canceller?->username ?? '-' }}
                pada {{ $barangRusak->cancelled_at?->format('d-m-Y H:i') ?? '-' }}.
            </div>
        @endif
    </div>
</div>
@endsection
