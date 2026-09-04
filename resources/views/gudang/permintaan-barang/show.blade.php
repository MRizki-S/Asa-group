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
        $unitLabel = $pembangunanItem?->nama ?? $pembangunanItem?->nama_pembangunan ?? '-';
        $qcLabel = $pembangunanItem?->nama ?? $pembangunanItem?->nama_pembangunan ?? '-';
        $pengawasLabel = $pembangunanItem?->pengawas?->nama_lengkap ?? $pembangunanItem?->pengawas?->name ?? '-';
    } elseif ($category === 'pembangunan_proyek_mangoon') {
        $pembangunanItem = $order->proyek;
        $perumahaanLabel = 'Proyek Luar';
        $tahapLabel = 'Proyek';
        $unitLabel = $pembangunanItem?->nama_project ?? $pembangunanItem?->nama ?? '-';
        $qcLabel = $pembangunanItem?->nama_project ?? $pembangunanItem?->nama ?? '-';
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

    $tolakRoute = $category === 'pembangunan_unit'
        ? route('gudang.permintaanBarang.pembangunanUnit.tolak', ['id' => $order->id])
        : route('gudang.permintaanBarang.tolak', ['id' => $order->id, 'jenis_order' => $category]);

    $resubmitRoute = $category === 'pembangunan_unit'
        ? route('gudang.permintaanBarang.pembangunanUnit.resubmit', ['id' => $order->id])
        : route('gudang.permintaanBarang.resubmit', ['id' => $order->id, 'jenis_order' => $category]);

    $aksiPermMap = [
        'pembangunan_unit' => 'gudang.permintaan-barang.pemb-unit.aksi',
        'pembangunan_kawasan' => 'gudang.permintaan-barang.pemb-kawasan.aksi',
        'pembangunan_proyek_mangoon' => 'gudang.permintaan-barang.pemb-mangoon.aksi',
        'pembangunan_proyek' => 'gudang.permintaan-barang.pemb-mangoon.aksi',
    ];
    $editPermMap = [
        'pembangunan_unit' => 'gudang.permintaan-barang.pemb-unit.edit',
        'pembangunan_kawasan' => 'gudang.permintaan-barang.pemb-kawasan.edit',
        'pembangunan_proyek_mangoon' => 'gudang.permintaan-barang.pemb-mangoon.edit',
        'pembangunan_proyek' => 'gudang.permintaan-barang.pemb-mangoon.edit',
    ];
    $ajukanKembaliPermMap = [
        'pembangunan_unit' => 'gudang.permintaan-barang.pemb-unit.ajukan-kembali',
        'pembangunan_kawasan' => 'gudang.permintaan-barang.pemb-kawasan.ajukan-kembali',
        'pembangunan_proyek_mangoon' => 'gudang.permintaan-barang.pemb-mangoon.ajukan-kembali',
        'pembangunan_proyek' => 'gudang.permintaan-barang.pemb-mangoon.ajukan-kembali',
    ];

    $permissionAksi = $aksiPermMap[$category ?? 'pembangunan_unit'] ?? 'gudang.permintaan-barang.pemb-unit.aksi';
    $permissionEdit = $editPermMap[$category ?? 'pembangunan_unit'] ?? 'gudang.permintaan-barang.pemb-unit.edit';
    $permissionAjukanKembali = $ajukanKembaliPermMap[$category ?? 'pembangunan_unit'] ?? 'gudang.permintaan-barang.pemb-unit.ajukan-kembali';
@endphp

<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6"
    x-data="{ openAccModal: false, accSubmitting: false, openTolakModal: false, tolakSubmitting: false, openResubmitModal: false, resubmitSubmitting: false, openEditTanggalModal: false, editTanggalSubmitting: false }">

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
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">No Order</label>
                    <div class="w-full bg-gray-100 border border-gray-300 text-gray-700 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-gray-200 font-semibold">
                        {{ $order->nomor_order ?? 'REQ-' . str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-medium text-gray-900 dark:text-white">Tanggal Diajukan</label>
                        @if ($order->status_order === 'diproses')
                            @can($permissionEdit)
                                <button type="button" @click="openEditTanggalModal = true"
                                    class="text-xs font-semibold text-amber-600 hover:text-amber-700 dark:text-amber-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    Edit Tanggal
                                </button>
                            @endcan
                        @endif
                    </div>
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

            @if ($category === 'pembangunan_unit')
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
            @elseif ($category === 'pembangunan_kawasan')
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Perumahan</label>
                        <div class="w-full bg-gray-50 border border-gray-300 text-gray-800 text-sm rounded-lg p-2.5 dark:bg-gray-700/50 dark:text-gray-200">
                            {{ $perumahaanLabel }}
                        </div>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama Kawasan</label>
                        <div class="w-full bg-gray-50 border border-gray-300 text-gray-800 text-sm rounded-lg p-2.5 dark:bg-gray-700/50 dark:text-gray-200 font-semibold">
                            {{ $unitLabel }}
                        </div>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pengawas Kawasan</label>
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
            @else
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama Proyek</label>
                        <div class="w-full bg-gray-50 border border-gray-300 text-gray-800 text-sm rounded-lg p-2.5 dark:bg-gray-700/50 dark:text-gray-200 font-semibold">
                            {{ $unitLabel }}
                        </div>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pengawas Proyek</label>
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
            @endif

            @if ($order->catatan)
                <div class="mt-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Catatan Permintaan</label>
                    <div class="w-full bg-yellow-50 border border-yellow-200 text-yellow-900 text-sm rounded-lg p-3 dark:bg-yellow-900/20 dark:border-yellow-700 dark:text-yellow-200 font-medium">
                        {{ $order->catatan }}
                    </div>
                </div>
            @endif

            @if ($order->status_order === 'ditolak' && $order->alasan_tolak)
                <div class="mt-4">
                    <label class="block mb-2 text-sm font-medium text-red-600 dark:text-red-400">Alasan Penolakan Gudang</label>
                    <div class="w-full bg-red-50 border border-red-200 text-red-900 text-sm rounded-lg p-3 dark:bg-red-900/20 dark:border-red-700 dark:text-red-200 font-semibold">
                        {{ $order->alasan_tolak }}
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

            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full border-collapse border border-gray-300" style="min-width: 750px;">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                        <tr>
                            <th class="border border-gray-300 px-3 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-200 min-w-[250px]">Barang</th>
                            <th class="border border-gray-300 px-3 py-2 text-center text-sm font-semibold text-gray-700 dark:text-gray-200 whitespace-nowrap">Jumlah</th>
                            <th class="border border-gray-300 px-3 py-2 text-center text-sm font-semibold text-gray-700 dark:text-gray-200 whitespace-nowrap">Jumlah Base</th>
                            <th class="border border-gray-300 px-3 py-2 text-center text-sm font-semibold text-gray-700 dark:text-gray-200 whitespace-nowrap">Konfirmasi</th>
                            @if ($order->jenis_order !== 'direct')
                                <th class="border border-gray-300 px-3 py-2 text-center text-sm font-semibold text-gray-700 dark:text-gray-200 whitespace-nowrap">
                                    Stock Saat Ini {{ $ubsCode ? '(' . $ubsCode . ')' : '' }}
                                </th>
                            @endif
                            <th class="border border-gray-300 px-3 py-2 text-right text-sm font-semibold text-gray-700 dark:text-gray-200 whitespace-nowrap">Harga Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($order->details as $detail)
                            @php
                                $isLuarRap = empty($detail->rap_bahan_id);
                                $isOver = false;
                                if ($category === 'pembangunan_unit' && ! $isLuarRap && $detail->rapBahan) {
                                    $standarRap = (float) ($detail->rapBahan->jumlah_standar ?? 0);
                                    $faktorRap = (float) ($detail->rapBahan->faktor_konversi ?? 1);
                                    $baseRap = $standarRap * $faktorRap;
                                    
                                    // Hitung total akumulasi order pada RAP ini hingga order saat ini (kecuali yang ditolak)
                                    $totalOrderedUpToThis = (float) \App\Models\PembangunanUnitBarangOrderDetail::query()
                                        ->where('rap_bahan_id', $detail->rap_bahan_id)
                                        ->whereHas('order', function ($q) use ($order) {
                                            $q->where('status_order', '!=', 'ditolak')
                                              ->where('id', '<=', $order->id);
                                        })
                                        ->sum('jumlah_base');

                                    $isOver = ($totalOrderedUpToThis - $baseRap) > 0.001;
                                }
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="border border-gray-300 px-3 py-2">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $detail->nama_barang ?? $detail->barang?->nama_barang ?? '-' }}
                                    </div>
                                    <div class="text-xs text-gray-500 flex flex-wrap items-center gap-1.5 mt-0.5">
                                        <span>{{ $detail->barang?->kode_barang ?? '-' }}</span>
                                        @if ($category === 'pembangunan_unit')
                                            @if ($isLuarRap)
                                                <span class="px-1.5 py-0.5 rounded bg-yellow-100 text-yellow-700 font-semibold text-[10px]">Luar RAP</span>
                                            @elseif ($isOver)
                                                <span class="px-1.5 py-0.5 rounded bg-red-100 text-red-700 font-semibold text-[10px]">Melebihi RAP</span>
                                            @endif
                                        @endif
                                    </div>
                                    @if ($category === 'pembangunan_unit' && $detail->alasan_permintaan_tidak_sesuai_rap)
                                        <div class="mt-1 text-xs text-red-600 italic">
                                            Ket: {{ $detail->alasan_permintaan_tidak_sesuai_rap }}
                                        </div>
                                    @endif
                                </td>
                                <td class="border border-gray-300 px-3 py-2 text-center text-sm font-bold text-gray-900 dark:text-white">
                                    {{ $formatQty($detail->jumlah_input) }} {{ $detail->satuan }}
                                </td>
                                <td class="border border-gray-300 px-3 py-2 text-center text-sm text-gray-800 dark:text-white">
                                    {{ $formatQty($detail->jumlah_base) }} {{ $detail->barang?->baseUnit?->nama ?? '' }}
                                </td>
                                <td class="border border-gray-300 px-3 py-2 text-center">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $detail->konfirmasi ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                        {{ $detail->konfirmasi ? 'Ya' : 'Belum' }}
                                    </span>
                                </td>
                                @if ($order->jenis_order !== 'direct')
                                    @php
                                        $baseStock = $stocks[$detail->barang_id] ?? 0;
                                        $conv = $conversions[$detail->id] ?? 1.0;
                                        $stockInDefault = $conv > 0 ? ($baseStock / $conv) : $baseStock;
                                        $isStockInsufficient = $detail->jumlah_base > $baseStock && !$detail->konfirmasi;
                                    @endphp
                                    <td
                                        class="border border-gray-300 px-3 py-2 text-center text-sm
                                        {{ $isStockInsufficient
                                            ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 font-semibold'
                                            : 'text-gray-800 dark:text-white'
                                        }}">
                                        {{ $formatQty($stockInDefault) }} {{ $detail->satuan }}
                                    </td>
                                @endif
                                <td class="border border-gray-300 px-3 py-2 text-right text-sm font-bold text-gray-900 dark:text-white">
                                    @if ($order->jenis_order === 'direct' && $order->status_order === 'diproses' && ! $detail->konfirmasi)
                                        <div class="inline-block" x-data="rupiahInput('{{ (int) old('harga_total.' . $detail->id, $detail->harga_total_snapshot ?? '') ? (int) old('harga_total.' . $detail->id, $detail->harga_total_snapshot) : '' }}')">
                                            <input type="text"
                                                x-model="display"
                                                @input="onInput($event)"
                                                placeholder="Masukan nominal"
                                                class="w-36 rounded-lg border border-gray-300 bg-white px-3 py-2 text-right text-sm font-semibold text-gray-900 outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500/20 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                                            <input type="hidden" form="acc-form"
                                                name="harga_total[{{ $detail->id }}]"
                                                :value="value">
                                        </div>
                                    @else
                                        {{ ! is_null($detail->harga_total_snapshot) ? 'Rp ' . number_format((float) $detail->harga_total_snapshot, 0, ',', '.') : '-' }}
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $order->jenis_order === 'direct' ? 5 : 6 }}" class="border border-gray-300 px-3 py-8 text-center text-sm text-gray-500">
                                    Belum ada detail barang.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Catatan Penolakan / Status Info --}}
    @if ($order->status_order === 'ditolak' && $order->alasan_tolak)
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-800/50 dark:bg-red-950/20 mb-6">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h5 class="text-sm font-semibold text-red-800 dark:text-red-300">Alasan Penolakan:</h5>
                    <p class="text-sm text-red-700 dark:text-red-400 mt-1">{{ $order->alasan_tolak }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Action Footer --}}
    <div class="flex flex-wrap items-center justify-between gap-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-5 shadow-sm">
        <a href="{{ route('gudang.permintaanBarang.index', ['jenis_order' => $category]) }}"
            class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-all dark:text-white dark:bg-gray-700 dark:hover:bg-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Daftar
        </a>

        @if ($order->status_order === 'diproses')
            <div class="flex gap-2">
                @can($permissionEdit)
                    <button type="button" @click="openEditTanggalModal = true"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-amber-800 bg-amber-100 rounded-lg hover:bg-amber-200 transition-all dark:bg-amber-900/40 dark:text-amber-300 dark:hover:bg-amber-900/60 focus:outline-none focus:ring-4 focus:ring-amber-300 active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit Tanggal
                    </button>
                @endcan
                @can($permissionAksi)
                    {{-- Tombol Tolak --}}
                    <button type="button" @click="openTolakModal = true"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-all focus:outline-none focus:ring-4 focus:ring-red-300 active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Tolak
                    </button>

                    {{-- Tombol ACC --}}
                    <form id="acc-form" x-ref="accForm" method="POST" action="{{ $accRoute }}"
                        @submit="accSubmitting = true">
                        @csrf
                        @method('PATCH')
                        <button type="button" @click="openAccModal = true"
                            class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-all focus:outline-none focus:ring-4 focus:ring-green-300 active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            ACC
                        </button>
                    </form>
                </div>
            @endcan
        @endif

        @if ($order->status_order === 'ditolak')
            <div class="flex gap-2">
                @can($permissionEdit)
                    <a href="{{ route('gudang.permintaanBarang.edit', ['id' => $order->id, 'category' => $category]) }}"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-amber-600 rounded-lg hover:bg-amber-700 transition-all focus:outline-none focus:ring-4 focus:ring-amber-300 active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit Permintaan
                    </a>
                @endcan

                @can($permissionAjukanKembali)
                    <button type="button" @click="openResubmitModal = true"
                        class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-all focus:outline-none focus:ring-4 focus:ring-blue-300 active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Ajukan Ulang
                    </button>
                @endcan
            </div>
        @endif
    </div>

    @if ($order->status_order === 'diproses')
        <div x-show="openAccModal" x-cloak x-transition
            class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/50 p-4">
            <div @click.away="openAccModal = false"
                class="w-full max-w-md rounded-2xl bg-white shadow-xl border border-gray-100 overflow-hidden dark:bg-gray-800 dark:border-gray-700">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">Konfirmasi ACC Permintaan</h3>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-semibold">
                                {{ $order->nomor_order ?? 'REQ-' . str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
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
                            @if ($order->jenis_order === 'direct')
                                ACC akan memakai harga total manual yang diinput pada tabel detail.
                            @else
                                ACC akan mengurangi stock UBS dan sisa nota barang masuk secara FIFO.
                            @endif
                        </p>
                        <p class="mt-1 text-xs text-green-700/80 dark:text-green-300/80">Data realisasi bahan proyek akan langsung ditambahkan setelah proses berhasil.</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                            <p class="text-[10px] font-black uppercase tracking-wider text-gray-400">Total Item</p>
                            <p class="mt-1 text-lg font-bold text-gray-900 dark:text-white">{{ $order->details->count() }}</p>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                            <p class="text-[10px] font-black uppercase tracking-wider text-gray-400">Jenis Order</p>
                            <p class="mt-1 text-lg font-bold uppercase text-gray-900 dark:text-white">{{ $order->jenis_order }}</p>
                        </div>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                        <p class="text-[10px] font-black uppercase tracking-wider text-gray-400">Unit / Lokasi</p>
                        <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $perumahaanLabel }} / {{ $tahapLabel }} / {{ $unitLabel }}</p>
                    </div>

                    @if ($category === 'pembangunan_unit')
                        @php
                            $itemsOverOrLuar = $order->details->filter(function($detail) use ($order) {
                                $isLuar = empty($detail->rap_bahan_id);
                                if ($isLuar) return true;
                                if ($detail->rapBahan) {
                                    $standarRap = (float) ($detail->rapBahan->jumlah_standar ?? 0);
                                    $faktorRap = (float) ($detail->rapBahan->faktor_konversi ?? 1);
                                    $baseRap = $standarRap * $faktorRap;
                                    $totalOrderedUpToThis = (float) \App\Models\PembangunanUnitBarangOrderDetail::query()
                                        ->where('rap_bahan_id', $detail->rap_bahan_id)
                                        ->whereHas('order', function ($q) use ($order) {
                                            $q->where('status_order', '!=', 'ditolak')
                                              ->where('id', '<=', $order->id);
                                        })
                                        ->sum('jumlah_base');
                                    return ($totalOrderedUpToThis - $baseRap) > 0.001;
                                }
                                return false;
                            });
                        @endphp

                        @if ($itemsOverOrLuar->count() > 0)
                            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:bg-amber-900/20 dark:border-amber-700">
                                <p class="text-xs font-bold text-amber-800 dark:text-amber-300 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    Perhatian: Terdapat {{ $itemsOverOrLuar->count() }} barang tidak sesuai RAP
                                </p>
                            </div>
                        @endif
                    @endif
                </div>
                <div class="flex justify-end gap-3 px-5 py-4 bg-gray-50 border-t border-gray-100 dark:bg-gray-900/40 dark:border-gray-700">
                    <button type="button" @click="openAccModal = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 transition dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">Batal</button>
                    <button type="button" :disabled="accSubmitting"
                        @click="if ($refs.accForm.reportValidity()) { accSubmitting = true; $refs.accForm.requestSubmit() }"
                        class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 shadow-sm transition disabled:opacity-60">
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
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">Tolak Permintaan Barang</h3>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-semibold">
                                {{ $order->nomor_order ?? 'REQ-' . str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                            </p>
                        </div>
                        <button type="button" @click="openTolakModal = false" class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 14 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" /></svg>
                        </button>
                    </div>
                </div>
                <form method="POST" action="{{ $tolakRoute }}" @submit="tolakSubmitting = true">
                    @csrf
                    @method('PATCH')
                    <div class="p-5 space-y-4">
                        <div class="rounded-xl border border-red-100 bg-red-50 p-4 dark:bg-red-900/20 dark:border-red-800">
                            <p class="text-sm font-semibold text-red-800 dark:text-red-300">Permintaan barang akan ditolak dan pengaju dapat mengedit lalu mengajukan kembali.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">Alasan / Catatan Penolakan <span class="text-red-500">*</span></label>
                            <textarea name="alasan_tolak" rows="3" required placeholder="Masukkan alasan penolakan..." class="w-full rounded-xl border-gray-300 bg-gray-50 p-3 text-sm text-gray-800 dark:bg-gray-800 dark:border-gray-700 dark:text-white placeholder:text-gray-400 focus:border-red-500 focus:ring-red-500">{{ $order->alasan_tolak }}</textarea>
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

    @if ($order->status_order === 'ditolak')
        {{-- Modal Ajukan Ulang --}}
        <div x-show="openResubmitModal" x-cloak x-transition
            class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/50 p-4">
            <div @click.away="openResubmitModal = false"
                class="w-full max-w-md rounded-2xl bg-white shadow-xl border border-gray-100 overflow-hidden dark:bg-gray-800 dark:border-gray-700">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">Ajukan Ulang Permintaan</h3>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-semibold">
                                {{ $order->nomor_order ?? 'REQ-' . str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                            </p>
                        </div>
                        <button type="button" @click="openResubmitModal = false" class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 14 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" /></svg>
                        </button>
                    </div>
                </div>
                <form method="POST" action="{{ $resubmitRoute }}" @submit="resubmitSubmitting = true">
                    @csrf
                    @method('PATCH')
                    <div class="p-5 space-y-4">
                        <div class="rounded-xl border border-blue-100 bg-blue-50 p-4 dark:bg-blue-900/20 dark:border-blue-800">
                            <p class="text-sm font-semibold text-blue-800 dark:text-blue-300">Permintaan barang akan diajukan kembali ke status <strong>Menunggu</strong>. Anda dapat mengubah catatan sebelum mengajukan ulang.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">Catatan (Opsional)</label>
                            <textarea name="catatan" rows="3" placeholder="Tambahkan catatan jika perlu..." class="w-full rounded-xl border-gray-300 bg-gray-50 p-3 text-sm text-gray-800 dark:bg-gray-800 dark:border-gray-700 dark:text-white placeholder:text-gray-400 focus:border-blue-500 focus:ring-blue-500">{{ $order->catatan }}</textarea>
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
    @if ($order->status_order === 'diproses')
        {{-- Modal Edit Tanggal --}}
        <div x-show="openEditTanggalModal" x-cloak x-transition
            class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/50 p-4">
            <div @click.away="openEditTanggalModal = false"
                class="w-full max-w-md rounded-2xl bg-white shadow-xl border border-gray-100 overflow-hidden dark:bg-gray-800 dark:border-gray-700">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">Edit Tanggal Permintaan</h3>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-semibold">
                                {{ $order->nomor_order ?? 'REQ-' . str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                            </p>
                        </div>
                        <button type="button" @click="openEditTanggalModal = false" class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 14 14"><path stroke-currentColor stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" /></svg>
                        </button>
                    </div>
                </div>
                <form method="POST" action="{{ route('gudang.permintaanBarang.updateTanggal', ['id' => $order->id]) }}" @submit="editTanggalSubmitting = true">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="category" value="{{ $category }}">

                    <div class="p-5 space-y-4">
                        <div class="rounded-xl border border-amber-100 bg-amber-50 p-4 dark:bg-amber-900/20 dark:border-amber-800">
                            <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">Ubah tanggal pengajuan permintaan barang (status Menunggu).</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">Tanggal Diajukan <span class="text-red-500">*</span></label>
                            <input type="datetime-local" name="tanggal_diajukan" required
                                value="{{ $order->tanggal_diajukan ? $order->tanggal_diajukan->format('Y-m-d\TH:i') : date('Y-m-d\TH:i') }}"
                                class="w-full rounded-xl border-gray-300 bg-gray-50 p-3 text-sm text-gray-800 dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:border-amber-500 focus:ring-amber-500">
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 px-5 py-4 bg-gray-50 border-t border-gray-100 dark:bg-gray-900/40 dark:border-gray-700">
                        <button type="button" @click="openEditTanggalModal = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 transition dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">Batal</button>
                        <button type="submit" :disabled="editTanggalSubmitting"
                            class="px-4 py-2 text-sm font-medium text-white bg-amber-600 rounded-lg hover:bg-amber-700 shadow-sm transition disabled:opacity-60">
                            <span x-text="editTanggalSubmitting ? 'Memproses...' : 'Simpan Tanggal'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

@endsection
