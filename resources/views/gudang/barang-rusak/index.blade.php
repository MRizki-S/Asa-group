@extends('layouts.app')

@section('pageActive', 'DaftarBarangRusak')

@section('content')
<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">
    <div x-data="{ pageName: 'DaftarBarangRusak' }">
        @include('partials.breadcrumb')
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-800 dark:bg-gray-800 dark:text-red-400" role="alert">
            <span class="font-medium">Terjadi kesalahan validasi:</span>
            <ul class="mt-1.5 list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="space-y-5 sm:space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white px-5 py-4 sm:px-6 sm:py-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-4 flex flex-col gap-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        Daftar Barang Rusak
                    </h3>

                    <div class="flex flex-wrap items-center gap-2">
                        @php
                            $statusTabs = [
                                'posted' => 'Aktif',
                                'cancelled' => 'Dikembalikan',
                                'all' => 'Semua',
                            ];
                        @endphp

                        <div class="inline-flex rounded-lg border border-gray-200 bg-gray-100 p-1 dark:border-gray-700 dark:bg-gray-800">
                            @foreach ($statusTabs as $tabValue => $tabLabel)
                                <a href="{{ route('gudang.barangRusak.index', [
                                    'status' => $tabValue,
                                    'tanggal_awal' => $tanggal_awal,
                                    'tanggal_akhir' => $tanggal_akhir,
                                ]) }}"
                                    class="rounded-md px-3 py-1.5 text-xs font-semibold transition {{ $status === $tabValue ? 'bg-white text-blue-700 shadow-sm dark:bg-gray-700 dark:text-blue-300' : 'text-gray-600 hover:text-blue-700 dark:text-gray-300 dark:hover:text-blue-300' }}">
                                    {{ $tabLabel }}
                                </a>
                            @endforeach
                        </div>

                        <a href="{{ route('gudang.barangRusak.create') }}"
                            class="inline-block rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">
                            + Tambah Barang Rusak
                        </a>
                    </div>
                </div>

                <form method="GET" action="{{ route('gudang.barangRusak.index') }}"
                    class="flex flex-wrap items-end gap-3 rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-800/50">
                    <input type="hidden" name="status" value="{{ $status }}">

                    <div class="flex flex-col gap-1.5">
                        <label for="tanggal_awal_display" class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Tanggal Dari
                        </label>
                        <div class="relative" x-data="{ tampil: '{{ \Carbon\Carbon::parse($tanggal_awal)->format('d-m-Y') }}', simpan: '{{ $tanggal_awal }}' }">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                                </svg>
                            </div>
                            <input type="text" id="tanggal_awal_display" x-model="tampil"
                                x-init="flatpickr($el, {
                                    dateFormat: 'd-m-Y',
                                    defaultDate: tampil,
                                    allowInput: true,
                                    onChange: (selectedDates, dateStr, instance) => {
                                        tampil = dateStr;
                                        simpan = selectedDates.length ? instance.formatDate(selectedDates[0], 'Y-m-d') : '';
                                    }
                                })"
                                class="min-w-[150px] rounded-lg border border-gray-300 bg-white p-2.5 pl-10 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <input type="hidden" name="tanggal_awal" x-model="simpan">
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="tanggal_akhir_display" class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Tanggal Sampai
                        </label>
                        <div class="relative" x-data="{ tampil: '{{ \Carbon\Carbon::parse($tanggal_akhir)->format('d-m-Y') }}', simpan: '{{ $tanggal_akhir }}' }">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                                </svg>
                            </div>
                            <input type="text" id="tanggal_akhir_display" x-model="tampil"
                                x-init="flatpickr($el, {
                                    dateFormat: 'd-m-Y',
                                    defaultDate: tampil,
                                    allowInput: true,
                                    onChange: (selectedDates, dateStr, instance) => {
                                        tampil = dateStr;
                                        simpan = selectedDates.length ? instance.formatDate(selectedDates[0], 'Y-m-d') : '';
                                    }
                                })"
                                class="min-w-[150px] rounded-lg border border-gray-300 bg-white p-2.5 pl-10 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <input type="hidden" name="tanggal_akhir" x-model="simpan">
                        </div>
                    </div>

                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 active:scale-95">
                        Tampilkan
                    </button>

                    <a href="{{ route('gudang.barangRusak.index') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                        Reset
                    </a>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table id="table-barangRusak" class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Nomor Barang Rusak</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Tanggal Rusak</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Source</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">UBS / HUB</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Nama Barang</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Satuan</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Qty</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Status</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Keterangan</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Created By</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($barangRusaks as $barangRusak)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.03]">
                                <td class="whitespace-nowrap px-4 py-3 font-medium text-gray-900 dark:text-white">
                                    {{ $barangRusak->nomor_barang_rusak }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-300">
                                    {{ $barangRusak->tgl_rusak?->format('d-M-Y') }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-300">
                                    {{ $barangRusak->stock_type }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-300">
                                    {{ $barangRusak->stock_type === 'UBS' ? ($barangRusak->ubs?->nama_ubs ?? '-') : 'HUB' }}
                                </td>
                                <td class="min-w-[180px] px-4 py-3 text-gray-700 dark:text-gray-300">
                                    {{ $barangRusak->barang?->nama_barang ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-300">
                                    {{ $barangRusak->satuan?->nama ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-gray-700 dark:text-gray-300">
                                    {{ rtrim(rtrim(number_format((float) $barangRusak->qty_out, 3, ',', '.'), '0'), ',') }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $barangRusak->status === 'posted' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ strtoupper($barangRusak->status) }}
                                    </span>
                                </td>
                                <td class="min-w-[220px] px-4 py-3 text-gray-700 dark:text-gray-300">
                                    {{ \Illuminate\Support\Str::limit($barangRusak->keterangan ?? '-', 80) }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-300">
                                    {{ $barangRusak->creator?->nama_lengkap ?? $barangRusak->creator?->username ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-center">
                                    <a href="{{ route('gudang.barangRusak.show', $barangRusak->nomor_barang_rusak) }}"
                                        class="inline-flex items-center rounded-md bg-blue-100 px-2.5 py-1.5 text-xs font-medium text-blue-700 transition hover:bg-blue-200 dark:bg-blue-800 dark:text-blue-100 dark:hover:bg-blue-700">
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
    if (document.getElementById("table-barangRusak") && typeof simpleDatatables.DataTable !== 'undefined') {
        const dataTable = new simpleDatatables.DataTable("#table-barangRusak", {
            searchable: true,
            sortable: true,
            perPageSelect: [5, 10, 20, 50],
        });
    }
</script>
@endsection
