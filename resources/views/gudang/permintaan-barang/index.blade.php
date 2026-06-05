@extends('layouts.app')

@section('pageActive', 'PermintaanBarang')

@section('content')
<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

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

                    @if ($isHistory)
                        <a href="{{ route('gudang.permintaanBarang.index') }}"
                            class="inline-flex w-fit items-center gap-2 rounded-lg bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300 transition dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                            Kembali ke Permintaan
                        </a>
                    @else
                        <a href="{{ route('gudang.permintaanBarang.history') }}"
                            class="inline-flex w-fit items-center gap-2 rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900 transition dark:bg-slate-700 dark:hover:bg-slate-600">
                            Riwayat Permintaan Barang
                        </a>
                    @endif
                </div>

                <form method="GET" action="{{ $isHistory ? route('gudang.permintaanBarang.history') : route('gudang.permintaanBarang.index') }}"
                    class="flex flex-wrap items-end gap-3 bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl border border-gray-100 dark:border-gray-800">

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

                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700 transition focus:ring-4 focus:ring-blue-300 active:scale-95 shadow-sm">
                        Tampilkan
                    </button>

                    <a href="{{ $isHistory ? route('gudang.permintaanBarang.history') : route('gudang.permintaanBarang.index') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-300 transition dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                        Reset
                    </a>
                </form>
            </div>

            <table id="table-permintaanBarang">
                <thead>
                    <tr>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">No Request</th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Tanggal</th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Unit</th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">QC</th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">Jenis</th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">Item</th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">Status</th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        @php
                            $pembangunan = $order->pembangunanUnit;
                            $unit = $pembangunan?->unit;
                            $statusMap = [
                                'diproses' => 'bg-blue-100 text-blue-700',
                                'selesai' => 'bg-green-100 text-green-700',
                                'ditolak' => 'bg-red-100 text-red-700',
                                'pengembalian' => 'bg-orange-100 text-orange-700',
                            ];
                            $statusLabels = [
                                'diproses' => 'Menunggu',
                                'selesai' => 'Selesai',
                                'ditolak' => 'Ditolak',
                                'pengembalian' => 'Pengembalian',
                            ];
                            $statusClass = $statusMap[$order->status_order] ?? 'bg-gray-100 text-gray-700';
                            $statusLabel = $statusLabels[$order->status_order] ?? str_replace('_', ' ', $order->status_order);
                        @endphp
                        <tr>
                            <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                REQ-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ $order->tanggal_diajukan?->format('d-M-Y H:i') ?? '-' }}
                            </td>
                            <td class="font-medium text-gray-900 dark:text-white">
                                <div>{{ $unit->nama_unit ?? '-' }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ $pembangunan?->tahap?->perumahaan?->nama_perumahaan ?? '-' }}
                                    @if ($pembangunan?->tahap?->nama_tahap)
                                        / {{ $pembangunan->tahap->nama_tahap }}
                                    @endif
                                </div>
                            </td>
                            <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ $order->qc->nama_qc ?? '-' }}
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
                                <a href="{{ route('gudang.permintaanBarang.show', $order->id) }}"
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

<script>
    if (document.getElementById("table-permintaanBarang") && typeof simpleDatatables.DataTable !== 'undefined') {
        new simpleDatatables.DataTable("#table-permintaanBarang", {
            searchable: true,
            sortable: true,
            perPageSelect: [5, 10, 20, 50],
        });
    }
</script>
@endsection
