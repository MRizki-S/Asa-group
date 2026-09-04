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
<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6"
    x-data="{ isEditTanggalOpen: false, editOrderId: null, editTanggalVal: '', editNomorOrder: '', editSubmitting: false, openEditTanggalModal(id, tgl, nomor) { this.editOrderId = id; this.editTanggalVal = tgl; this.editNomorOrder = nomor; this.isEditTanggalOpen = true; } }">

    <div x-data="{ pageName: '{{ $isHistory ? 'Riwayat Permintaan Barang' : 'Permintaan Barang' }}' }">
        @include('partials.breadcrumb')
    </div>

    <div class="space-y-5 sm:space-y-6">
        <div class="rounded-2xl border border-gray-200 px-5 py-4 sm:px-6 sm:py-5 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-4 flex flex-col gap-3">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        {{ $titlePage }}
                    </h3>

                    <div class="flex flex-wrap items-center gap-2">
                        @if (!$isHistory)
                            <a href="{{ route('gudang.permintaanBarang.pembangunanUnit.create', ['category' => $category]) }}"
                                class="inline-flex w-fit items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-bold text-white hover:bg-blue-700 transition shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg> Tambah Barang Keluar
                            </a>
                        @endif

                        @if ($isHistory)
                            <a href="{{ route('gudang.permintaanBarang.index', ['jenis_order' => $category]) }}"
                                class="inline-flex w-fit items-center gap-2 rounded-lg bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300 transition dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                Kembali ke Permintaan
                            </a>
                        @else
                            <a href="{{ route('gudang.permintaanBarang.history', ['jenis_order' => $category]) }}"
                                class="inline-flex w-fit items-center gap-2 rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900 transition dark:bg-slate-700 dark:hover:bg-slate-600">
                                Riwayat Permintaan Barang
                            </a>
                        @endif
                    </div>
                </div>

                <form method="GET" action="{{ $isHistory ? route('gudang.permintaanBarang.history') : route('gudang.permintaanBarang.index') }}"
                    class="flex flex-wrap items-end gap-3 bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl border border-gray-100 dark:border-gray-800">

                    <input type="hidden" name="category" value="{{ $category }}">

                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Jenis Order</label>
                        <select name="jenis_order" class="rounded-lg border-gray-300 bg-white text-gray-800 text-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300 min-w-[140px]">
                            <option value="all" {{ $jenisOrder === 'all' ? 'selected' : '' }}>Semua</option>
                            <option value="stock" {{ $jenisOrder === 'stock' ? 'selected' : '' }}>Stock</option>
                            <option value="direct" {{ $jenisOrder === 'direct' ? 'selected' : '' }}>Direct</option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</label>
                        <select name="status" class="rounded-lg border-gray-300 bg-white text-gray-800 text-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300 min-w-[150px]">
                            @if ($isHistory)
                                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua Riwayat</option>
                            @else
                                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua</option>
                            @endif

                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}" {{ $status === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Filter Per Hari (Opsional)</label>
                        <div class="relative" x-data="{ tanggal: '{{ request('tanggal') }}' }">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                                </svg>
                            </div>
                            <input type="text" name="tanggal" x-ref="tanggal" x-init="flatpickr($refs.tanggal, {
                                    defaultDate: tanggal,
                                    dateFormat: 'Y-m-d',
                                    altInput: true,
                                    altFormat: 'd-m-Y',
                                    allowInput: true
                                })"
                                placeholder="Pilih Tanggal"
                                class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 dark:bg-gray-900 dark:border-gray-700 dark:text-white min-w-[150px]">
                        </div>
                    </div>

                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700 transition focus:ring-4 focus:ring-blue-300 active:scale-95 shadow-sm">
                        Tampilkan
                    </button>

                    <a href="{{ $isHistory ? route('gudang.permintaanBarang.history', ['jenis_order' => $category]) : route('gudang.permintaanBarang.index', ['jenis_order' => $category]) }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-300 transition dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                        Reset
                    </a>
                </form>
            <div class="overflow-x-auto custom-scrollbar">
                <table id="table-permintaanBarang" class="min-w-full" style="min-width: 800px;">
                <thead>
                    <tr>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">No Order</th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Tanggal</th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Lokasi / Proyek</th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Keterangan</th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">Jenis</th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">Item</th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">Status</th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        @php
                            $locationLabel = '-';
                            $subLocationLabel = '-';
                            $qcLabel = '-';

                            if ($category === 'pembangunan_unit') {
                                $pembangunan = $order->pembangunanUnit;
                                $unit = $pembangunan?->unit;
                                $locationLabel = $unit->nama_unit ?? '-';
                                $subLocationLabel = ($pembangunan?->tahap?->perumahaan?->nama_perumahaan ?? '-') . ($pembangunan?->tahap?->nama_tahap ? ' / ' . $pembangunan->tahap->nama_tahap : '');
                                $qcLabel = $order->qc->nama_qc ?? '-';
                            } elseif ($category === 'pembangunan_kawasan') {
                                $locationLabel = $order->kawasan?->nama ?? $order->kawasan?->nama_pembangunan ?? 'Kawasan';
                                $subLocationLabel = $order->kawasan?->perumahan?->nama_perumahaan ?? '-';
                                $qcLabel = '-';
                            } elseif ($category === 'pembangunan_proyek_mangoon') {
                                $locationLabel = $order->proyek?->nama_project ?? $order->proyek?->nama ?? 'Proyek';
                                $subLocationLabel = 'Proyek Mangoon';
                                $qcLabel = '-';
                            }

                            $statusMap = [
                                'diproses' => 'bg-blue-100 text-blue-700',
                                'selesai' => 'bg-green-100 text-green-700',
                                'ditolak' => 'bg-red-100 text-red-700',
                            ];
                            $statusLabels = [
                                'diproses' => 'Menunggu',
                                'selesai' => 'Selesai',
                                'ditolak' => 'Ditolak',
                            ];
                            $statusClass = $statusMap[$order->status_order] ?? 'bg-gray-100 text-gray-700';
                            $statusLabel = $statusLabels[$order->status_order] ?? str_replace('_', ' ', $order->status_order);
                        @endphp
                        <tr>
                            <td class="font-medium text-gray-900 dark:text-white">
                                {{ $order->nomor_order ?? 'REQ-' . str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ $order->tanggal_diajukan?->format('d-M-Y H:i') ?? '-' }}
                            </td>
                            <td class="font-medium text-gray-900 dark:text-white">
                                <div>{{ $locationLabel }}</div>
                                <div class="text-xs text-gray-500">{{ $subLocationLabel }}</div>
                            </td>
                            <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ $qcLabel }}
                            </td>
                            <td class="text-center">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $order->jenis_order === 'stock' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ strtoupper($order->jenis_order) }}
                                </span>
                            </td>
                            <td class="text-center font-medium text-gray-900 dark:text-white">
                                {{ $order->details_count }}
                            </td>
                            <td class="text-center">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                                    {{ strtoupper($statusLabel) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 flex flex-wrap gap-2 justify-center">
                                <a href="{{ route('gudang.permintaanBarang.show', ['id' => $order->id, 'jenis_order' => $category]) }}"
                                    class="inline-flex items-center gap-1 text-xs font-medium text-blue-700 bg-blue-100 hover:bg-blue-200 dark:bg-blue-800 dark:text-blue-100 dark:hover:bg-blue-700 px-2.5 py-1.5 rounded-md transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1 active:scale-95">
                                    Detail
                                </a>
                                @php
                                    $editPermMap = [
                                        'pembangunan_unit' => 'gudang.permintaan-barang.pemb-unit.edit',
                                        'pembangunan_kawasan' => 'gudang.permintaan-barang.pemb-kawasan.edit',
                                        'pembangunan_proyek_mangoon' => 'gudang.permintaan-barang.pemb-mangoon.edit',
                                    ];
                                    $editPermission = $editPermMap[$category] ?? 'gudang.permintaan-barang.pemb-unit.edit';
                                @endphp

                                @can($editPermission)
                                    @if ($order->status_order === 'diproses')
                                        <button type="button" @click="openEditTanggalModal({{ $order->id }}, '{{ $order->tanggal_diajukan ? $order->tanggal_diajukan->format('Y-m-d\TH:i') : '' }}', '{{ $order->nomor_order ?? 'REQ-' . str_pad($order->id, 5, '0', STR_PAD_LEFT) }}')"
                                            class="inline-flex items-center gap-1 text-xs font-medium text-amber-700 bg-amber-100 hover:bg-amber-200 dark:bg-amber-800 dark:text-amber-100 dark:hover:bg-amber-700 px-2.5 py-1.5 rounded-md transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-1 active:scale-95">
                                            Edit Tanggal
                                        </button>
                                    @endif
                                @endcan
                                @php
                                    $deletePermMap = [
                                        'pembangunan_unit' => 'gudang.permintaan-barang.pemb-unit.delete',
                                        'pembangunan_kawasan' => 'gudang.permintaan-barang.pemb-kawasan.delete',
                                        'pembangunan_proyek_mangoon' => 'gudang.permintaan-barang.pemb-mangoon.delete',
                                    ];
                                    $deletePermission = $deletePermMap[$category] ?? 'gudang.permintaan-barang.pemb-unit.delete';
                                @endphp

                                @can($deletePermission)
                                    @if ($order->status_order !== 'selesai')
                                        <form action="{{ route('gudang.permintaanBarang.destroy', ['id' => $order->id, 'category' => $category]) }}" method="POST" class="inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="delete-btn inline-flex items-center gap-1 text-xs font-medium text-red-700 bg-red-100 hover:bg-red-200 dark:bg-red-800 dark:text-red-100 dark:hover:bg-red-700 px-2.5 py-1.5 rounded-md transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-1 active:scale-95">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Edit Tanggal --}}
<div x-show="isEditTanggalOpen" x-cloak x-transition
    class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/50 p-4">
    <div @click.away="isEditTanggalOpen = false"
        class="w-full max-w-md rounded-2xl bg-white shadow-xl border border-gray-100 overflow-hidden dark:bg-gray-800 dark:border-gray-700">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Edit Tanggal Permintaan</h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-semibold" x-text="editNomorOrder"></p>
                </div>
                <button type="button" @click="isEditTanggalOpen = false" class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 14 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" /></svg>
                </button>
            </div>
        </div>
        <form method="POST" :action="'/gudang/permintaan-barang/' + editOrderId + '/update-tanggal'" @submit="editSubmitting = true">
            @csrf
            @method('PATCH')
            <input type="hidden" name="category" value="{{ $category }}">

            <div class="p-5 space-y-4">
                <div class="rounded-xl border border-amber-100 bg-amber-50 p-4 dark:bg-amber-900/20 dark:border-amber-800">
                    <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">Ubah tanggal pengajuan permintaan barang (status Menunggu).</p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">Tanggal Diajukan <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="tanggal_diajukan" required :value="editTanggalVal"
                        class="w-full rounded-xl border-gray-300 bg-gray-50 p-3 text-sm text-gray-800 dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:border-amber-500 focus:ring-amber-500">
                </div>
            </div>
            <div class="flex justify-end gap-3 px-5 py-4 bg-gray-50 border-t border-gray-100 dark:bg-gray-900/40 dark:border-gray-700">
                <button type="button" @click="isEditTanggalOpen = false"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 transition dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">Batal</button>
                <button type="submit" :disabled="editSubmitting"
                    class="px-4 py-2 text-sm font-medium text-white bg-amber-600 rounded-lg hover:bg-amber-700 shadow-sm transition disabled:opacity-60">
                    <span x-text="editSubmitting ? 'Memproses...' : 'Simpan Tanggal'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    if (document.getElementById("table-permintaanBarang") && typeof simpleDatatables.DataTable !== 'undefined') {
        new simpleDatatables.DataTable("#table-permintaanBarang", {
            searchable: true,
            sortable: true,
            perPageSelect: [5, 10, 20, 50],
        });
    }

    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-btn')) {
            const btn = e.target.closest('.delete-btn');
            const form = btn.closest('.delete-form');

            Swal.fire({
                title: 'Yakin hapus permintaan barang ini?',
                text: "Data permintaan barang akan dihapus. Langkah ini tidak dapat dibatalkan.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    });
</script>
@endsection
