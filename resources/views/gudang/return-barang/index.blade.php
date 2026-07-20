@extends('layouts.app')

@php
    $categoryMap = [
        'pembangunan_unit' => [
            'active' => 'BarangReturnUnit',
            'pageName' => 'Retur Barang Unit',
            'historyName' => 'Riwayat Retur Barang Unit',
            'indexRoute' => 'gudang.returnBarang.unit.index',
            'historyRoute' => 'gudang.returnBarang.unit.history',
            'showRoute' => 'gudang.returnBarang.unit.show',
            'locationHeader' => 'Unit / Perumahan',
            'prefix' => 'RTN-UNT-',
        ],
        'pembangunan_kawasan' => [
            'active' => 'BarangReturnKawasan',
            'pageName' => 'Retur Barang Kawasan',
            'historyName' => 'Riwayat Retur Barang Kawasan',
            'indexRoute' => 'gudang.returnBarang.kawasan.index',
            'historyRoute' => 'gudang.returnBarang.kawasan.history',
            'showRoute' => 'gudang.returnBarang.kawasan.show',
            'locationHeader' => 'Kawasan / Perumahan',
            'prefix' => 'RTN-KWS-',
        ],
        'pembangunan_proyek' => [
            'active' => 'BarangReturnProyek',
            'pageName' => 'Retur Barang Proyek',
            'historyName' => 'Riwayat Retur Barang Proyek',
            'indexRoute' => 'gudang.returnBarang.proyek.index',
            'historyRoute' => 'gudang.returnBarang.proyek.history',
            'showRoute' => 'gudang.returnBarang.proyek.show',
            'locationHeader' => 'Nama Proyek',
            'prefix' => 'RTN-PRY-',
        ],
    ];

    $cfg = $categoryMap[$category ?? 'pembangunan_unit'] ?? $categoryMap['pembangunan_unit'];
@endphp

@section('pageActive', $cfg['active'])

@section('content')
<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

    <div x-data="{ pageName: '{{ $isHistory ? $cfg['historyName'] : $cfg['pageName'] }}' }">
        @include('partials.breadcrumb')
    </div>

    {{-- Alert Notification --}}
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

    <div class="space-y-5 sm:space-y-6">
        <div class="rounded-2xl border border-gray-200 px-5 py-4 sm:px-6 sm:py-5 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-4 flex flex-col gap-3">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        {{ $titlePage }}
                    </h3>

                    @if ($isHistory)
                        <a href="{{ route($cfg['indexRoute']) }}"
                            class="inline-flex w-fit items-center gap-2 rounded-lg bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300 transition dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                            Kembali ke Konfirmasi Retur
                        </a>
                    @else
                        <a href="{{ route($cfg['historyRoute']) }}"
                            class="inline-flex w-fit items-center gap-2 rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900 transition dark:bg-slate-700 dark:hover:bg-slate-600">
                            Riwayat Retur Barang
                        </a>
                    @endif
                </div>

                <form method="GET" action="{{ $isHistory ? route($cfg['historyRoute']) : route($cfg['indexRoute']) }}"
                    class="flex flex-wrap items-end gap-3 bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl border border-gray-100 dark:border-gray-800">
                    
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</label>
                        <select name="status" class="rounded-lg border-gray-300 bg-white text-gray-800 text-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300 min-w-[150px]">
                            @if ($isHistory)
                                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua Riwayat</option>
                            @else
                                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua Status</option>
                            @endif

                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}" {{ $status === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700 transition focus:ring-4 focus:ring-blue-300 active:scale-95 shadow-sm">
                        Tampilkan
                    </button>

                    <a href="{{ $isHistory ? route($cfg['historyRoute']) : route($cfg['indexRoute']) }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-300 transition dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                        Reset
                    </a>
                </form>
            </div>

            <div class="max-w-full overflow-x-auto custom-scrollbar">
                <table id="table-returnBarangUnified" class="min-w-full" style="min-width: 800px;">
                    <thead>
                        <tr>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">No Retur</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Tanggal</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">{{ $cfg['locationHeader'] }}</th>
                            @if (($category ?? 'pembangunan_unit') === 'pembangunan_unit')
                                <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">QC</th>
                            @endif
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Diajukan Oleh</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">Item</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">Status</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($returns as $ret)
                            @php
                                $locName = '-';
                                $subLocName = '-';
                                $qcName = '-';

                                if (($category ?? 'pembangunan_unit') === 'pembangunan_unit') {
                                    $locName = $ret->pembangunanUnit?->unit?->nama_unit ?? '-';
                                    $subLocName = ($ret->pembangunanUnit?->tahap?->perumahaan?->nama_perumahaan ?? '-') . ($ret->pembangunanUnit?->tahap?->nama_tahap ? ' / ' . $ret->pembangunanUnit->tahap->nama_tahap : '');
                                    $qcName = $ret->qc?->nama_qc ?? '-';
                                } elseif (($category ?? '') === 'pembangunan_kawasan') {
                                    $locName = $ret->kawasan?->nama ?? '-';
                                    $subLocName = $ret->kawasan?->perumahan?->nama_perumahaan ?? '-';
                                } else {
                                    $locName = $ret->proyek?->nama_project ?? $ret->proyek?->nama ?? '-';
                                    $subLocName = '-';
                                }

                                $pengajuName = $ret->createdBy?->nama_lengkap ?? $ret->createdBy?->name ?? '-';

                                $statusMap = [
                                    'diproses' => 'bg-blue-100 text-blue-700',
                                    'selesai' => 'bg-green-100 text-green-700',
                                    'ditolak' => 'bg-red-100 text-red-700',
                                    'draft' => 'bg-gray-100 text-gray-700',
                                ];
                                $statusLabels = [
                                    'diproses' => 'Menunggu',
                                    'selesai' => 'Selesai',
                                    'ditolak' => 'Ditolak',
                                    'draft' => 'Draft',
                                ];
                                $statusClass = $statusMap[$ret->status] ?? 'bg-gray-100 text-gray-700';
                                $statusLabel = $statusLabels[$ret->status] ?? strtoupper($ret->status);
                            @endphp
                            <tr>
                                <td class="font-medium text-gray-900 dark:text-white">
                                    {{ $ret->nomor_return ?? ($cfg['prefix'] . str_pad($ret->id, 5, '0', STR_PAD_LEFT)) }}
                                </td>
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ \Carbon\Carbon::parse($ret->tanggal_return ?? $ret->tanggal_diajukan)->translatedFormat('d-M-Y H:i') }}
                                </td>
                                <td class="font-medium text-gray-900 dark:text-white">
                                    <div>{{ $locName }}</div>
                                    @if ($subLocName !== '-')
                                        <div class="text-xs text-gray-500">{{ $subLocName }}</div>
                                    @endif
                                </td>
                                @if (($category ?? 'pembangunan_unit') === 'pembangunan_unit')
                                    <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ $qcName }}
                                    </td>
                                @endif
                                <td class="font-medium text-gray-900 dark:text-white">
                                    {{ $pengajuName }}
                                </td>
                                <td class="text-center font-medium text-gray-900 dark:text-white">
                                    {{ $ret->details_count ?? $ret->details->count() }}
                                </td>
                                <td class="text-center">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                                        {{ strtoupper($statusLabel) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 flex flex-wrap gap-2 justify-center">
                                    <a href="{{ route($cfg['showRoute'], $ret->id) }}"
                                        class="inline-flex items-center gap-1 text-xs font-medium text-blue-700 bg-blue-100 hover:bg-blue-200 dark:bg-blue-800 dark:text-blue-100 dark:hover:bg-blue-700 px-2.5 py-1.5 rounded-md transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1 active:scale-95">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    if (document.getElementById("table-returnBarangUnified") && typeof simpleDatatables.DataTable !== 'undefined') {
        new simpleDatatables.DataTable("#table-returnBarangUnified", {
            searchable: true,
            sortable: true,
            perPageSelect: [5, 10, 20, 50],
        });
    }
</script>
@endsection
