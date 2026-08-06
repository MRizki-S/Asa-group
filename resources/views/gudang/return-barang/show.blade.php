@extends('layouts.app')

@php
    $categoryMap = [
        'pembangunan_unit' => [
            'active' => 'BarangReturnUnit',
            'pageName' => 'Detail Retur Barang Unit',
            'indexRoute' => 'gudang.returnBarang.unit.index',
            'accRoute' => 'gudang.permintaanBarang.pembangunanUnit.accReturn',
            'rejectRoute' => 'gudang.permintaanBarang.pembangunanUnit.rejectReturn',
            'prefix' => 'RTN-UNT-',
        ],
        'pembangunan_kawasan' => [
            'active' => 'BarangReturnKawasan',
            'pageName' => 'Detail Retur Barang Kawasan',
            'indexRoute' => 'gudang.returnBarang.kawasan.index',
            'accRoute' => 'gudang.permintaanBarang.pembangunanKawasan.accReturn',
            'rejectRoute' => 'gudang.permintaanBarang.pembangunanKawasan.rejectReturn',
            'prefix' => 'RTN-KWS-',
        ],
        'pembangunan_proyek' => [
            'active' => 'BarangReturnProyek',
            'pageName' => 'Detail Retur Barang Proyek',
            'indexRoute' => 'gudang.returnBarang.proyek.index',
            'accRoute' => 'gudang.permintaanBarang.pembangunanProyek.accReturn',
            'rejectRoute' => 'gudang.permintaanBarang.pembangunanProyek.rejectReturn',
            'prefix' => 'RTN-PRY-',
        ],
    ];

    $cfg = $categoryMap[$category ?? 'pembangunan_unit'] ?? $categoryMap['pembangunan_unit'];

    $perumahaanLabel = '-';
    $mainLocLabel = '-';
    $qcLabel = '-';
    $pengawasLabel = '-';

    if (($category ?? 'pembangunan_unit') === 'pembangunan_unit') {
        $itemUnit = $return->pembangunanUnit;
        $perumahaanLabel = $itemUnit?->perumahaan?->nama_perumahaan ?? $itemUnit?->tahap?->perumahaan?->nama_perumahaan ?? '-';
        $mainLocLabel = $itemUnit?->unit?->nama_unit ?? '-';
        $qcLabel = $return->qc?->nama_qc ?? '-';
        $pengawasLabel = $itemUnit?->pengawas?->nama_lengkap ?? $itemUnit?->pengawas?->name ?? '-';
    } elseif (($category ?? '') === 'pembangunan_kawasan') {
        $kawasan = $return->kawasan;
        $perumahaanLabel = $kawasan?->perumahan?->nama_perumahaan ?? '-';
        $mainLocLabel = $kawasan?->nama ?? '-';
        $pengawasLabel = $kawasan?->pengawas?->nama_lengkap ?? $kawasan?->pengawas?->name ?? '-';
    } else {
        $proyek = $return->proyek;
        $mainLocLabel = $proyek?->nama_project ?? $proyek?->nama ?? '-';
        $pengawasLabel = $proyek?->pengawas?->nama_lengkap ?? $proyek?->pengawas?->name ?? '-';
    }

    $diajukanLabel = $return->createdBy?->nama_lengkap ?? $return->createdBy?->name ?? '-';
    $accLabel = $return->accBy?->nama_lengkap ?? $return->accBy?->name ?? '-';

    $statusMap = [
        'diproses' => 'bg-blue-50 border-blue-300 text-blue-800 dark:bg-blue-900/30 dark:border-blue-600 dark:text-blue-300',
        'selesai' => 'bg-green-50 border-green-300 text-green-800 dark:bg-green-900/30 dark:border-green-600 dark:text-green-300',
        'ditolak' => 'bg-red-50 border-red-300 text-red-800 dark:bg-red-900/30 dark:border-red-600 dark:text-red-300',
    ];
    $statusLabels = [
        'diproses' => 'Menunggu',
        'selesai' => 'Selesai',
        'ditolak' => 'Ditolak',
    ];
    $statusClass = $statusMap[$return->status] ?? 'bg-gray-50 border-gray-300 text-gray-800';
    $statusLabel = $statusLabels[$return->status] ?? strtoupper($return->status);

    $formatQty = function ($value) {
        $number = round((float) $value, 3);
        if (abs($number - round($number)) < 0.000001) {
            return number_format($number, 0, ',', '.');
        }
        return rtrim(rtrim(number_format($number, 3, ',', '.'), '0'), ',');
    };
@endphp

@section('pageActive', $cfg['active'])

@section('content')
<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6"
    x-data="showReturComponent()">

    <div x-data="{ pageName: '{{ $cfg['pageName'] }}' }">
        @include('partials.breadcrumb')
    </div>

    {{-- Alert Flash --}}
    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: "{{ session('success') }}",
                    timer: 3000,
                    showConfirmButton: false
                });
            });
        </script>
    @endif
    @if (session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: "{{ session('error') }}",
                });
            });
        </script>
    @endif

    {{-- Header Info Card --}}
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
        <div class="px-5 py-4 sm:px-6 sm:py-5">
            <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 border-b border-gray-100 dark:border-gray-800 pb-3">
                Detail Pengajuan Retur
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">No Retur</label>
                    <div class="w-full bg-gray-100 border border-gray-300 text-gray-700 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-gray-200 font-semibold">
                        {{ $return->nomor_return ?? ($cfg['prefix'] . str_pad($return->id, 5, '0', STR_PAD_LEFT)) }}
                    </div>
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tanggal Retur</label>
                    <div class="w-full bg-gray-100 border border-gray-300 text-gray-700 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-gray-200">
                        {{ \Carbon\Carbon::parse($return->tanggal_return ?? $return->tanggal_diajukan)->translatedFormat('d-m-Y H:i') }}
                    </div>
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Diajukan Oleh</label>
                    <div class="w-full bg-gray-100 border border-gray-300 text-gray-700 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-gray-200">
                        {{ $diajukanLabel }}
                    </div>
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Status Retur</label>
                    <div class="w-full border text-sm rounded-lg p-2.5 font-bold uppercase {{ $statusClass }}">
                        {{ $statusLabel }}
                    </div>
                </div>
            </div>

            @if (($category ?? 'pembangunan_unit') === 'pembangunan_unit')
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Perumahan</label>
                        <div class="w-full bg-gray-50 border border-gray-300 text-gray-800 text-sm rounded-lg p-2.5 dark:bg-gray-700/50 dark:text-gray-200">
                            {{ $perumahaanLabel }}
                        </div>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Unit / Lokasi</label>
                        <div class="w-full bg-gray-50 border border-gray-300 text-gray-800 text-sm rounded-lg p-2.5 dark:bg-gray-700/50 dark:text-gray-200">
                            {{ $mainLocLabel }}
                        </div>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">QC</label>
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
                </div>
            @elseif (($category ?? '') === 'pembangunan_kawasan')
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Perumahan</label>
                        <div class="w-full bg-gray-50 border border-gray-300 text-gray-800 text-sm rounded-lg p-2.5 dark:bg-gray-700/50 dark:text-gray-200">
                            {{ $perumahaanLabel }}
                        </div>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama Kawasan</label>
                        <div class="w-full bg-gray-50 border border-gray-300 text-gray-800 text-sm rounded-lg p-2.5 dark:bg-gray-700/50 dark:text-gray-200 font-semibold">
                            {{ $mainLocLabel }}
                        </div>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pengawas Kawasan</label>
                        <div class="w-full bg-gray-50 border border-gray-300 text-gray-800 text-sm rounded-lg p-2.5 dark:bg-gray-700/50 dark:text-gray-200">
                            {{ $pengawasLabel }}
                        </div>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama Proyek</label>
                        <div class="w-full bg-gray-50 border border-gray-300 text-gray-800 text-sm rounded-lg p-2.5 dark:bg-gray-700/50 dark:text-gray-200 font-semibold">
                            {{ $mainLocLabel }}
                        </div>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pengawas Proyek</label>
                        <div class="w-full bg-gray-50 border border-gray-300 text-gray-800 text-sm rounded-lg p-2.5 dark:bg-gray-700/50 dark:text-gray-200">
                            {{ $pengawasLabel }}
                        </div>
                    </div>
                </div>
            @endif

            @if ($return->catatan)
                <div class="mt-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Catatan</label>
                    <div class="w-full bg-yellow-50 border border-yellow-200 text-yellow-900 text-sm rounded-lg p-3 dark:bg-yellow-900/20 dark:border-yellow-700 dark:text-yellow-200">
                        {{ $return->catatan }}
                    </div>
                </div>
            @endif

            @if ($return->status === 'ditolak' && $return->alasan_tolak)
                <div class="mt-4">
                    <label class="block mb-2 text-sm font-medium text-red-600 dark:text-red-400">Alasan Penolakan Gudang</label>
                    <div class="w-full bg-red-50 border border-red-200 text-red-900 text-sm rounded-lg p-3 dark:bg-red-900/20 dark:border-red-700 dark:text-red-200">
                        {{ $return->alasan_tolak }}
                    </div>
                </div>
            @endif

            @if ($return->status !== 'diproses' && $return->accBy)
                <div class="mt-4 text-xs text-gray-500">
                    Dikonfirmasi oleh <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $accLabel }}</span> pada {{ \Carbon\Carbon::parse($return->acc_at)->translatedFormat('d-m-Y H:i') }}
                </div>
            @endif
        </div>
    </div>

    {{-- Main Form & Tabel Detail Item Barang --}}
    @if ($return->status === 'diproses')
        <form id="form-acc-retur" action="{{ route($cfg['accRoute'], $return->id) }}" method="POST">
            @csrf
            @method('PATCH')
    @endif

        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
            <div class="px-5 py-4 sm:px-6 sm:py-5">
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 border-b border-gray-100 dark:border-gray-800 pb-3">
                    Barang yang Diretur ({{ $return->details->count() }} Item)
                </h3>

                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full border-collapse border border-gray-300" style="min-width: 750px;">
                        <thead class="bg-gray-100 dark:bg-gray-800">
                            <tr>
                                <th class="border border-gray-300 px-3 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-200 min-w-[220px]">Barang</th>
                                <th class="border border-gray-300 px-3 py-2 text-center text-sm font-semibold text-gray-700 dark:text-gray-200 whitespace-nowrap">Jumlah Retur</th>
                                @if ($return->status === 'diproses')
                                    <th class="border border-gray-300 px-3 py-2 text-center text-sm font-semibold text-gray-700 dark:text-gray-200 whitespace-nowrap w-[150px]">Pilih Satuan</th>
                                @endif
                                <th class="border border-gray-300 px-3 py-2 text-center text-sm font-semibold text-emerald-700 dark:text-emerald-400 whitespace-nowrap w-[150px]">Barang Layak</th>
                                <th class="border border-gray-300 px-3 py-2 text-center text-sm font-semibold text-red-700 dark:text-red-400 whitespace-nowrap w-[150px]">Barang Rusak</th>
                                <th class="border border-gray-300 px-3 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-200 whitespace-nowrap">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($return->details as $index => $det)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                    <td class="border border-gray-300 px-3 py-2">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $det->nama_barang ?? $det->barang?->nama_barang ?? '-' }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $det->barang?->kode_barang ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="border border-gray-300 px-3 py-2 text-center text-sm font-bold text-gray-900 dark:text-white whitespace-nowrap">
                                        @if ($return->status === 'diproses')
                                            <span x-text="items[{{ $index }}].total"></span>
                                            <span class="text-xs font-normal text-gray-500" x-text="getSelectedSatuanNama({{ $index }})"></span>
                                        @else
                                            {{ $formatQty($det->jumlah_input) }} {{ $det->satuan }}
                                        @endif
                                    </td>

                                    {{-- Kolom Input Satuan, Layak & Rusak --}}
                                    @if ($return->status === 'diproses')
                                        <input type="hidden" name="items[{{ $index }}][id]" value="{{ $det->id }}">
                                        
                                        {{-- Select Satuan Input --}}
                                        <td class="border border-gray-300 px-2 py-2 text-center">
                                            <select name="items[{{ $index }}][satuan_id]"
                                                x-model.number="items[{{ $index }}].selected_satuan_id"
                                                @change="onSatuanChange({{ $index }})"
                                                class="w-full rounded-lg border border-gray-300 bg-white px-2 py-1.5 text-center text-xs font-bold text-gray-800 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                                                <template x-for="opt in items[{{ $index }}].satuan_options" :key="opt.id">
                                                    <option :value="opt.id" x-text="opt.nama" :selected="opt.id == items[{{ $index }}].selected_satuan_id"></option>
                                                </template>
                                            </select>
                                        </td>

                                        {{-- Input Jumlah Layak --}}
                                        <td class="border border-gray-300 px-2 py-2 text-center">
                                            <input type="number"
                                                name="items[{{ $index }}][jumlah_layak_input]"
                                                x-model.number="items[{{ $index }}].layak"
                                                step="any" min="0" :max="items[{{ $index }}].total"
                                                @input="updateLayak({{ $index }})"
                                                class="w-28 rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-center text-sm font-bold text-emerald-700 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:bg-gray-800 dark:border-gray-600 dark:text-emerald-300">
                                        </td>

                                        {{-- Input Jumlah Rusak --}}
                                        <td class="border border-gray-300 px-2 py-2 text-center">
                                            <input type="number"
                                                name="items[{{ $index }}][jumlah_rusak_input]"
                                                x-model.number="items[{{ $index }}].rusak"
                                                step="any" min="0" :max="items[{{ $index }}].total"
                                                @input="updateRusak({{ $index }})"
                                                class="w-28 rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-center text-sm font-bold text-red-700 outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:bg-gray-800 dark:border-gray-600 dark:text-red-300">
                                        </td>
                                    @else
                                        <td class="border border-gray-300 px-3 py-2 text-center text-sm font-bold text-emerald-700 dark:text-emerald-400 whitespace-nowrap">
                                            {{ $formatQty($det->jumlah_layak_base) }} {{ $det->barang?->baseUnit?->nama ?? '' }}
                                        </td>
                                        <td class="border border-gray-300 px-3 py-2 text-center text-sm font-bold text-red-700 dark:text-red-400 whitespace-nowrap">
                                            {{ $formatQty($det->jumlah_rusak_base) }} {{ $det->barang?->baseUnit?->nama ?? '' }}
                                        </td>
                                    @endif

                                    <td class="border border-gray-300 px-3 py-2 text-xs text-gray-500 italic">
                                        {{ $det->keterangan ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="border border-gray-300 px-3 py-8 text-center text-sm text-gray-500">
                                        Belum ada detail barang retur.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    @if ($return->status === 'diproses')
        </form>
    @endif

    {{-- Form Reject Hidden --}}
    @if ($return->status === 'diproses')
        <form id="form-reject-retur" action="{{ route($cfg['rejectRoute'], $return->id) }}" method="POST" class="hidden">
            @csrf
            @method('PATCH')
            <input type="hidden" name="alasan_tolak" id="reject-alasan-input">
        </form>
    @endif

    {{-- Action Buttons --}}
    <div class="flex items-center justify-between">
        <a href="{{ route($cfg['indexRoute']) }}"
            class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-all dark:text-white dark:bg-gray-700 dark:hover:bg-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar
        </a>

        @if ($return->status === 'diproses')
            <div class="flex gap-2">
                {{-- Tombol Tolak --}}
                <button type="button" @click="openTolakModal = true"
                    class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-all focus:outline-none focus:ring-4 focus:ring-red-300 active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Tolak
                </button>

                {{-- Tombol ACC --}}
                <button type="button" @click="openAccModal = true"
                    class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-all focus:outline-none focus:ring-4 focus:ring-green-300 active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    ACC
                </button>
            </div>
        @endif

        @if ($return->status === 'ditolak')
            <div class="flex gap-2">
                <a href="{{ route('gudang.returnBarang.edit', ['id' => $return->id, 'category' => $category]) }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-amber-600 rounded-lg hover:bg-amber-700 transition-all focus:outline-none focus:ring-4 focus:ring-amber-300 active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit Retur
                </a>
                <button type="button" @click="openResubmitModal = true"
                    class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-all focus:outline-none focus:ring-4 focus:ring-blue-300 active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Ajukan Ulang
                </button>
            </div>
        @endif
    </div>

    @if ($return->status === 'diproses')
        {{-- Modal ACC --}}
        <div x-show="openAccModal" x-cloak x-transition
            class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/50 p-4">
            <div @click.away="openAccModal = false"
                class="w-full max-w-md rounded-2xl bg-white shadow-xl border border-gray-100 overflow-hidden dark:bg-gray-800 dark:border-gray-700">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">Konfirmasi ACC Retur Barang</h3>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-semibold">
                                {{ $return->nomor_return ?? ($cfg['prefix'] . str_pad($return->id, 5, '0', STR_PAD_LEFT)) }}
                            </p>
                        </div>
                        <button type="button" @click="openAccModal = false" class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 14 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" /></svg>
                        </button>
                    </div>
                </div>
                <div class="p-5 space-y-4">
                    <div class="rounded-xl border border-green-100 bg-green-50 p-4 dark:bg-green-900/20 dark:border-green-800">
                        <p class="text-sm font-semibold text-green-800 dark:text-green-300">
                            ACC retur akan memproses kuantitas barang Layak dan Rusak yang telah diinput ke dalam stok gudang & pencatatan barang.
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                            <p class="text-[10px] font-black uppercase tracking-wider text-gray-400">Total Item</p>
                            <p class="mt-1 text-lg font-bold text-gray-900 dark:text-white">{{ $return->details->count() }} Item</p>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                            <p class="text-[10px] font-black uppercase tracking-wider text-gray-400">Status Baru</p>
                            <p class="mt-1 text-lg font-bold uppercase text-green-600">SELESAI</p>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-3 px-5 py-4 bg-gray-50 border-t border-gray-100 dark:bg-gray-900/40 dark:border-gray-700">
                    <button type="button" @click="openAccModal = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 transition dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">Batal</button>
                    <button type="button" @click="submitAccForm()" :disabled="accSubmitting"
                        class="px-5 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 shadow-sm transition disabled:opacity-60">
                        <span x-text="accSubmitting ? 'Memproses...' : 'Ya, ACC'"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Modal Tolak --}}
        <div x-show="openTolakModal" x-cloak x-transition
            class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/50 p-4">
            <div @click.away="openTolakModal = false"
                class="w-full max-w-md rounded-2xl bg-white shadow-xl border border-gray-100 overflow-hidden dark:bg-gray-800 dark:border-gray-700">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">Tolak Retur Barang</h3>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-semibold">
                                {{ $return->nomor_return ?? ($cfg['prefix'] . str_pad($return->id, 5, '0', STR_PAD_LEFT)) }}
                            </p>
                        </div>
                        <button type="button" @click="openTolakModal = false" class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 14 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" /></svg>
                        </button>
                    </div>
                </div>
                <form method="POST" action="{{ route($cfg['rejectRoute'], $return->id) }}" @submit="tolakSubmitting = true">
                    @csrf
                    @method('PATCH')
                    <div class="p-5 space-y-4">
                        <div class="rounded-xl border border-red-100 bg-red-50 p-4 dark:bg-red-900/20 dark:border-red-800">
                            <p class="text-sm font-semibold text-red-800 dark:text-red-300">Pengajuan retur barang akan ditolak dan pengaju dapat mengedit lalu mengajukan kembali.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">Alasan / Catatan Penolakan <span class="text-red-500">*</span></label>
                            <textarea name="alasan_tolak" rows="3" required placeholder="Masukkan alasan penolakan..." class="w-full rounded-xl border-gray-300 bg-gray-50 p-3 text-sm text-gray-800 dark:bg-gray-800 dark:border-gray-700 dark:text-white placeholder:text-gray-400 focus:border-red-500 focus:ring-red-500">{{ $return->alasan_tolak }}</textarea>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 px-5 py-4 bg-gray-50 border-t border-gray-100 dark:bg-gray-900/40 dark:border-gray-700">
                        <button type="button" @click="openTolakModal = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 transition dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">Batal</button>
                        <button type="submit" :disabled="tolakSubmitting"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 shadow-sm transition disabled:opacity-60">
                            <span x-text="tolakSubmitting ? 'Memproses...' : 'Ya, Tolak'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($return->status === 'ditolak')
        {{-- Modal Ajukan Ulang --}}
        <div x-show="openResubmitModal" x-cloak x-transition
            class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/50 p-4">
            <div @click.away="openResubmitModal = false"
                class="w-full max-w-md rounded-2xl bg-white shadow-xl border border-gray-100 overflow-hidden dark:bg-gray-800 dark:border-gray-700">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">Ajukan Ulang Retur Barang</h3>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-semibold">
                                {{ $return->nomor_return ?? ($cfg['prefix'] . str_pad($return->id, 5, '0', STR_PAD_LEFT)) }}
                            </p>
                        </div>
                        <button type="button" @click="openResubmitModal = false" class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 14 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" /></svg>
                        </button>
                    </div>
                </div>
                <form method="POST" action="{{ route('gudang.returnBarang.resubmit', $return->id) }}" @submit="resubmitSubmitting = true">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="category" value="{{ $category }}">
                    <div class="p-5 space-y-4">
                        <div class="rounded-xl border border-blue-100 bg-blue-50 p-4 dark:bg-blue-900/20 dark:border-blue-800">
                            <p class="text-sm font-semibold text-blue-800 dark:text-blue-300">Pengajuan retur barang akan diajukan kembali ke status <strong>Menunggu (Diproses)</strong>. Anda dapat mengubah catatan sebelum mengajukan ulang.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">Catatan (Opsional)</label>
                            <textarea name="catatan" rows="3" placeholder="Tambahkan catatan jika perlu..." class="w-full rounded-xl border-gray-300 bg-gray-50 p-3 text-sm text-gray-800 dark:bg-gray-800 dark:border-gray-700 dark:text-white placeholder:text-gray-400 focus:border-blue-500 focus:ring-blue-500">{{ $return->catatan }}</textarea>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 px-5 py-4 bg-gray-50 border-t border-gray-100 dark:bg-gray-900/40 dark:border-gray-700">
                        <button type="button" @click="openResubmitModal = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 transition dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">Batal</button>
                        <button type="submit" :disabled="resubmitSubmitting"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm transition disabled:opacity-60">
                            <span x-text="resubmitSubmitting ? 'Memproses...' : 'Ya, Ajukan Ulang'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

<script>
    function showReturComponent() {
        return {
            openAccModal: false,
            openTolakModal: false,
            openResubmitModal: false,
            accSubmitting: false,
            tolakSubmitting: false,
            resubmitSubmitting: false,
            items: [
                @foreach($return->details as $index => $det)
                    @php
                        $initFaktor = 1.0;
                        if ($det->barang && $det->barang->base_unit_id == $det->satuan_id) {
                            $initFaktor = 1.0;
                        } else {
                            $initFaktor = (float)(\App\Models\BarangSatuanKonversi::where('barang_id', $det->barang_id)->where('satuan_id', $det->satuan_id)->value('konversi_ke_base') ?? 1.0);
                        }
                    @endphp
                    {
                        id: {{ $det->id }},
                        nama_barang: '{{ addslashes($det->nama_barang) }}',
                        jumlah_base: {{ (float)$det->jumlah_base }},
                        satuan_options: @json($det->satuan_options ?? []),
                        selected_satuan_id: {{ $det->satuan_id }},
                        faktor: {{ $initFaktor }},
                        total: {{ (float)$det->jumlah_input }},
                        layak: {{ (float)$det->jumlah_input }},
                        rusak: 0
                    },
                @endforeach
            ],
            getSelectedSatuanNama(index) {
                let item = this.items[index];
                if (!item || !item.satuan_options) return '';
                let opt = item.satuan_options.find(o => o.id == item.selected_satuan_id);
                return opt ? opt.nama : '';
            },
            onSatuanChange(index) {
                let item = this.items[index];
                let opt = item.satuan_options.find(o => o.id == item.selected_satuan_id);
                let newFaktor = opt ? parseFloat(opt.faktor) : 1.0;
                if (newFaktor <= 0) newFaktor = 1.0;

                item.total = Math.round((item.jumlah_base / newFaktor) * 1000) / 1000;
                item.faktor = newFaktor;

                item.layak = item.total;
                item.rusak = 0;
            },
            updateLayak(index) {
                let item = this.items[index];
                if (item.layak === null || item.layak === undefined || isNaN(item.layak)) item.layak = 0;
                if (item.layak < 0) item.layak = 0;
                if (item.layak > item.total) item.layak = item.total;
                item.rusak = Math.max(0, Math.round((item.total - item.layak) * 1000) / 1000);
            },
            updateRusak(index) {
                let item = this.items[index];
                if (item.rusak === null || item.rusak === undefined || isNaN(item.rusak)) item.rusak = 0;
                if (item.rusak < 0) item.rusak = 0;
                if (item.rusak > item.total) item.rusak = item.total;
                item.layak = Math.max(0, Math.round((item.total - item.rusak) * 1000) / 1000);
            },
            submitAccForm() {
                this.accSubmitting = true;
                const form = document.getElementById('form-acc-retur');
                if (form) form.submit();
            }
        };
    }
</script>
@endsection
