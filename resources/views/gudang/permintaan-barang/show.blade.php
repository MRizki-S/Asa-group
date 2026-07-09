@extends('layouts.app')

@php
    $pageActive = [
        'pembangunan_unit' => 'PermintaanBarangUnit',
        'pembangunan_kawasan' => 'PermintaanBarangKawasan',
        'pembangunan_proyek_mangoon' => 'PermintaanBarangProyek',
    ][$category] ?? 'PermintaanBarangUnit';
@endphp

@section('pageActive', $pageActive)

@section('content')
@php
    $pembangunanItem = null;
    $perumahaanLabel = '-';
    $tahapLabel = '-';
    $unitLabel = '-';
    $qcLabel = '-';
    $pengawasLabel = '-';

    if ($category === 'pembangunan_unit') {
        $pembangunanItem = $order->pembangunanUnit;
        $perumahaanLabel = $pembangunanItem?->tahap?->perumahaan?->nama_perumahaan ?? '-';
        $tahapLabel = $pembangunanItem?->tahap?->nama_tahap ?? '-';
        $unitLabel = $pembangunanItem?->unit?->nama_unit ?? '-';
        $qcLabel = $order->qc->nama_qc ?? '-';
        $pengawasLabel = $pembangunanItem?->pengawas?->nama_lengkap ?? $pembangunanItem?->pengawas?->name ?? '-';
    } elseif ($category === 'pembangunan_kawasan') {
        $pembangunanItem = $order->kawasan;
        $perumahaanLabel = $pembangunanItem?->perumahan?->nama_perumahaan ?? '-';
        $tahapLabel = 'Kawasan';
        $unitLabel = $pembangunanItem?->nama_pembangunan ?? '-';
        $qcLabel = 'Kawasan / Umum';
        $pengawasLabel = $pembangunanItem?->pengawas?->nama_lengkap ?? $pembangunanItem?->pengawas?->name ?? '-';
    } elseif ($category === 'pembangunan_proyek_mangoon') {
        $pembangunanItem = $order->proyek;
        $perumahaanLabel = 'Proyek Luar';
        $tahapLabel = 'Proyek';
        $unitLabel = $pembangunanItem?->nama ?? '-';
        $qcLabel = 'Proyek / Umum';
        $pengawasLabel = $pembangunanItem?->pengawas?->nama_lengkap ?? $pembangunanItem?->pengawas?->name ?? '-';
    }

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

    $accRoute = $category === 'pembangunan_unit'
        ? route('gudang.permintaanBarang.pembangunanUnit.acc', ['id' => $order->id])
        : route('gudang.permintaanBarang.acc', ['id' => $order->id, 'jenis_order' => $category]);
@endphp

<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6"
    x-data="{ openAccModal: false, accSubmitting: false }"
    x-init="$dispatch('sidebar-minimize')">

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
                        {{ $perumahaanLabel }}
                    </div>
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tahap</label>
                    <div class="w-full bg-gray-50 border border-gray-300 text-gray-800 text-sm rounded-lg p-2.5 dark:bg-gray-700/50 dark:text-gray-200">
                        {{ $tahapLabel }}
                    </div>
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Unit / Lokasi</label>
                    <div class="w-full bg-gray-50 border border-gray-300 text-gray-800 text-sm rounded-lg p-2.5 dark:bg-gray-700/50 dark:text-gray-200">
                        {{ $unitLabel }}
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Keterangan / QC</label>
                    <div class="w-full bg-gray-50 border border-gray-300 text-gray-800 text-sm rounded-lg p-2.5 dark:bg-gray-700/50 dark:text-gray-200">
                        {{ $qcLabel }}
                    </div>
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pengawas</label>
                    <div class="w-full bg-gray-50 border border-gray-300 text-gray-800 text-sm rounded-lg p-2.5 dark:bg-gray-700/50 dark:text-gray-200">
                        {{ $pengawasLabel }}
                    </div>
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Diajukan Oleh</label>
                    <div class="w-full bg-gray-50 border border-gray-300 text-gray-800 text-sm rounded-lg p-2.5 dark:bg-gray-700/50 dark:text-gray-200">
                        {{ $order->user->nama_lengkap ?? $order->user->name ?? $order->pembuat->nama_lengkap ?? '-' }}
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
                                <td class="border border-gray-300 px-3 py-2 text-right text-sm font-bold text-gray-900 dark:text-white">
                                    @if ($order->jenis_order === 'direct' && $order->status_order === 'diproses' && ! $detail->konfirmasi)
                                        <input type="number" form="acc-form"
                                            name="harga_total[{{ $detail->id }}]" min="0" step="0.01"
                                            value="{{ old('harga_total.' . $detail->id, $detail->harga_total_snapshot) }}"
                                            placeholder="0" required
                                            class="w-36 rounded-lg border border-gray-300 bg-white px-3 py-2 text-right text-sm font-semibold text-gray-900 outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500/20 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                                    @else
                                        {{ ! is_null($detail->harga_total_snapshot) ? 'Rp ' . number_format((float) $detail->harga_total_snapshot, 0, ',', '.') : '-' }}
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="border border-gray-300 px-3 py-8 text-center text-sm text-gray-500">
                                    Belum ada detail barang.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="flex flex-wrap justify-between gap-3 items-center bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <a href="{{ route('gudang.permintaanBarang.index', ['jenis_order' => $category]) }}"
            class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-all dark:text-white dark:bg-gray-700 dark:hover:bg-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Daftar
        </a>

        @if ($order->status_order === 'diproses')
            <form id="acc-form" x-ref="accForm" method="POST" action="{{ $accRoute }}"
                @submit="accSubmitting = true">
                @csrf
                @method('PATCH')
                <button type="button" @click="openAccModal = true"
                    class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-all focus:outline-none focus:ring-4 focus:ring-green-300 active:scale-95">
                    ACC
                </button>
            </form>
        @endif
    </div>

    @if ($order->status_order === 'diproses')
        <div x-show="openAccModal" x-cloak x-transition
            class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div @click.away="openAccModal = false"
                class="w-full max-w-md rounded-2xl bg-white shadow-xl border border-gray-100 overflow-hidden dark:bg-gray-800 dark:border-gray-700">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">
                                Konfirmasi ACC Permintaan
                            </h3>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                REQ-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                            </p>
                        </div>
                        <button type="button" @click="openAccModal = false"
                            class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="p-5 space-y-4">
                    <div class="rounded-xl border border-green-100 bg-green-50 p-4 dark:bg-green-900/20 dark:border-green-800">
                        <p class="text-sm font-semibold text-green-800 dark:text-green-300">
                            @if ($order->jenis_order === 'direct')
                                ACC akan memakai harga total manual yang diinput pada tabel detail.
                            @else
                                ACC akan mengurangi stock UBS dan sisa nota barang masuk secara FIFO.
                            @endif
                        </p>
                        <p class="mt-1 text-xs text-green-700/80 dark:text-green-300/80">
                            Data realisasi bahan proyek akan langsung ditambahkan setelah proses berhasil.
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                            <p class="text-[10px] font-black uppercase tracking-wider text-gray-400">Total Item</p>
                            <p class="mt-1 text-lg font-bold text-gray-900 dark:text-white">
                                {{ $order->details->count() }}
                            </p>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                            <p class="text-[10px] font-black uppercase tracking-wider text-gray-400">Jenis Order</p>
                            <p class="mt-1 text-lg font-bold uppercase text-gray-900 dark:text-white">
                                {{ $order->jenis_order }}
                            </p>
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                        <p class="text-[10px] font-black uppercase tracking-wider text-gray-400">Unit / Lokasi</p>
                        <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-gray-200">
                            {{ $perumahaanLabel }} /
                            {{ $tahapLabel }} /
                            {{ $unitLabel }}
                        </p>
                    </div>
                </div>

                <div class="flex justify-end gap-3 px-5 py-4 bg-gray-50 border-t border-gray-100 dark:bg-gray-900/40 dark:border-gray-700">
                    <button type="button" @click="openAccModal = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 transition dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                        Batal
                    </button>
                    <button type="button" :disabled="accSubmitting"
                        @click="if ($refs.accForm.reportValidity()) { accSubmitting = true; $refs.accForm.requestSubmit() }"
                        class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 shadow-sm transition disabled:opacity-60">
                        <span x-text="accSubmitting ? 'Memproses...' : 'Ya, ACC'"></span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

@endsection
