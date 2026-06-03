@extends('layouts.app')

@section('pageActive', 'PermintaanBarang')

@section('content')
@php
    $pembangunan = $order->pembangunanUnit;
    $unit = $pembangunan?->unit;
    $statusMap = [
        'diproses' => 'bg-blue-50 border-blue-300 text-blue-800 dark:bg-blue-900/30 dark:border-blue-600 dark:text-blue-300',
        'selesai' => 'bg-green-50 border-green-300 text-green-800 dark:bg-green-900/30 dark:border-green-600 dark:text-green-300',
        'ditolak' => 'bg-red-50 border-red-300 text-red-800 dark:bg-red-900/30 dark:border-red-600 dark:text-red-300',
        'pengembalian' => 'bg-orange-50 border-orange-300 text-orange-800 dark:bg-orange-900/30 dark:border-orange-600 dark:text-orange-300',
    ];
    $statusLabels = [
        'diproses' => 'Menunggu',
        'selesai' => 'Selesai',
        'ditolak' => 'Ditolak',
        'pengembalian' => 'Pengembalian',
    ];
    $statusClass = $statusMap[$order->status_order] ?? 'bg-gray-50 border-gray-300 text-gray-800';
    $statusLabel = $statusLabels[$order->status_order] ?? str_replace('_', ' ', $order->status_order);
    $formatQty = function ($value) {
        $number = round((float) $value, 3);

        if (abs($number - round($number)) < 0.000001) {
            return number_format($number, 0, ',', '.');
        }

        return rtrim(rtrim(number_format($number, 3, ',', '.'), '0'), ',');
    };
@endphp

<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-init="$dispatch('sidebar-minimize')">

    <div x-data="{ pageName: 'Detail Permintaan Barang' }">
        @include('partials.breadcrumb')
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
        <div class="px-5 py-4 sm:px-6 sm:py-5">
            <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 border-b border-gray-100 dark:border-gray-800 pb-3">
                Detail Permintaan
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">No Request</label>
                    <div class="w-full bg-gray-100 border border-gray-300 text-gray-700 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-gray-200">
                        REQ-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                    </div>
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tanggal Diajukan</label>
                    <div class="w-full bg-gray-100 border border-gray-300 text-gray-700 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-gray-200">
                        {{ $order->tanggal_diajukan?->format('d-m-Y H:i') ?? '-' }}
                    </div>
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Jenis Order</label>
                    <div class="w-full bg-gray-100 border border-gray-300 text-gray-700 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-gray-200 uppercase">
                        {{ $order->jenis_order }}
                    </div>
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Status</label>
                    <div class="w-full border text-sm font-bold rounded-lg p-2.5 text-center uppercase {{ $statusClass }}">
                        {{ $statusLabel }}
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Perumahan</label>
                    <div class="w-full bg-gray-50 border border-gray-300 text-gray-800 text-sm rounded-lg p-2.5 dark:bg-gray-700/50 dark:text-gray-200">
                        {{ $pembangunan?->tahap?->perumahaan?->nama_perumahaan ?? '-' }}
                    </div>
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tahap</label>
                    <div class="w-full bg-gray-50 border border-gray-300 text-gray-800 text-sm rounded-lg p-2.5 dark:bg-gray-700/50 dark:text-gray-200">
                        {{ $pembangunan?->tahap?->nama_tahap ?? '-' }}
                    </div>
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Unit</label>
                    <div class="w-full bg-gray-50 border border-gray-300 text-gray-800 text-sm rounded-lg p-2.5 dark:bg-gray-700/50 dark:text-gray-200">
                        {{ $unit->nama_unit ?? '-' }}
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">QC / Termin</label>
                    <div class="w-full bg-gray-50 border border-gray-300 text-gray-800 text-sm rounded-lg p-2.5 dark:bg-gray-700/50 dark:text-gray-200">
                        {{ $order->qc->nama_qc ?? '-' }}
                    </div>
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pengawas</label>
                    <div class="w-full bg-gray-50 border border-gray-300 text-gray-800 text-sm rounded-lg p-2.5 dark:bg-gray-700/50 dark:text-gray-200">
                        {{ $pembangunan?->pengawas?->nama_lengkap ?? $pembangunan?->pengawas?->name ?? '-' }}
                    </div>
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Diajukan Oleh</label>
                    <div class="w-full bg-gray-50 border border-gray-300 text-gray-800 text-sm rounded-lg p-2.5 dark:bg-gray-700/50 dark:text-gray-200">
                        {{ $order->user->nama_lengkap ?? $order->user->name ?? '-' }}
                    </div>
                </div>
            </div>

            @if ($order->catatan)
                <div class="mt-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Catatan</label>
                    <div class="w-full bg-yellow-50 border border-yellow-200 text-yellow-900 text-sm rounded-lg p-3 dark:bg-yellow-900/20 dark:border-yellow-700 dark:text-yellow-200">
                        {{ $order->catatan }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
        <div class="px-5 py-4 sm:px-6 sm:py-5">
            <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 border-b border-gray-100 dark:border-gray-800 pb-3">
                Barang yang Diminta
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse border border-gray-300">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                        <tr>
                            <th class="border border-gray-300 px-3 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-200 w-[28%]">Barang</th>
                            <th class="border border-gray-300 px-3 py-2 text-center text-sm font-semibold text-gray-700 dark:text-gray-200">Jumlah</th>
                            <th class="border border-gray-300 px-3 py-2 text-center text-sm font-semibold text-gray-700 dark:text-gray-200">Jumlah Base</th>
                            <th class="border border-gray-300 px-3 py-2 text-center text-sm font-semibold text-gray-700 dark:text-gray-200">Konfirmasi</th>
                            <th class="border border-gray-300 px-3 py-2 text-center text-sm font-semibold text-gray-700 dark:text-gray-200">Retur</th>
                            <th class="border border-gray-300 px-3 py-2 text-right text-sm font-semibold text-gray-700 dark:text-gray-200">Harga Satuan</th>
                            <th class="border border-gray-300 px-3 py-2 text-right text-sm font-semibold text-gray-700 dark:text-gray-200">Harga Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($order->details as $detail)
                            @php
                                $isLuarRap = empty($detail->rap_bahan_id);
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="border border-gray-300 px-3 py-2">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $detail->nama_barang ?? $detail->barang?->nama_barang ?? '-' }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $detail->barang?->kode_barang ?? '-' }}
                                        @if ($isLuarRap)
                                            <span class="ml-2 px-1.5 py-0.5 rounded bg-yellow-100 text-yellow-700 font-semibold">Luar RAP</span>
                                        @endif
                                    </div>
                                    @if ($detail->alasan_permintaan_tidak_sesuai_rap)
                                        <div class="mt-1 text-xs text-red-600">
                                            {{ $detail->alasan_permintaan_tidak_sesuai_rap }}
                                        </div>
                                    @endif
                                </td>
                                <td class="border border-gray-300 px-3 py-2 text-center text-sm font-bold text-gray-900 dark:text-white">
                                    {{ $formatQty($detail->jumlah_input) }} {{ $detail->satuan }}
                                </td>
                                <td class="border border-gray-300 px-3 py-2 text-center text-sm text-gray-800 dark:text-white">
                                    {{ $formatQty($detail->jumlah_base) }}
                                </td>
                                <td class="border border-gray-300 px-3 py-2 text-center">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $detail->konfirmasi ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                        {{ $detail->konfirmasi ? 'Ya' : 'Belum' }}
                                    </span>
                                </td>
                                <td class="border border-gray-300 px-3 py-2 text-center text-sm text-gray-800 dark:text-white">
                                    @if ((float) $detail->jumlah_return > 0)
                                        <div class="font-bold text-orange-700">{{ $formatQty($detail->jumlah_return) }} {{ $detail->satuan }}</div>
                                        @if ($detail->keterangan_return)
                                            <div class="text-xs text-gray-500">{{ $detail->keterangan_return }}</div>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="border border-gray-300 px-3 py-2 text-right text-sm text-gray-800 dark:text-white">
                                    {{ $detail->harga_satuan_snapshot ? 'Rp ' . number_format((float) $detail->harga_satuan_snapshot, 0, ',', '.') : '-' }}
                                </td>
                                <td class="border border-gray-300 px-3 py-2 text-right text-sm font-bold text-gray-900 dark:text-white">
                                    {{ $detail->harga_total_snapshot ? 'Rp ' . number_format((float) $detail->harga_total_snapshot, 0, ',', '.') : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="border border-gray-300 px-3 py-8 text-center text-sm text-gray-500">
                                    Belum ada detail barang.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <td colspan="6" class="border border-gray-300 px-3 py-2 text-right text-sm font-bold text-gray-900 dark:text-white">
                                TOTAL NILAI SNAPSHOT
                            </td>
                            <td class="border border-gray-300 px-3 py-2 text-right text-sm font-extrabold text-blue-600 dark:text-blue-400">
                                Rp {{ number_format((float) $order->details->sum('harga_total_snapshot'), 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="flex flex-wrap justify-between gap-3 items-center bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <a href="{{ route('gudang.permintaanBarang.index') }}"
            class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-all dark:text-white dark:bg-gray-700 dark:hover:bg-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Daftar
        </a>

        @if ($order->status_order === 'diproses')
            <form method="POST" action="{{ route('gudang.permintaanBarang.acc', $order->id) }}"
                onsubmit="return confirm('ACC permintaan barang ini? Status akan berubah menjadi selesai.');">
                @csrf
                @method('PATCH')
                <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-all focus:outline-none focus:ring-4 focus:ring-green-300 active:scale-95">
                    ACC
                </button>
            </form>
        @endif
    </div>
</div>

@endsection
