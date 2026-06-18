@extends('layouts.app')

@section('pageActive', 'KomposisiRakitan')

@section('content')
<!-- ===== Main Content Start ===== -->
<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

    <!-- Breadcrumb Start -->
    <div x-data="{ pageName: 'KomposisiRakitan' }">
        @include('partials.breadcrumb')
    </div>
    <!-- Breadcrumb End -->

    {{-- Alert Error Validasi --}}
    @if ($errors->any())
    <div class="flex p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400"
        role="alert">
        <svg class="shrink-0 inline w-4 h-4 me-3 mt-[2px]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
            fill="currentColor" viewBox="0 0 20 20">
            <path
                d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
        </svg>
        <span class="sr-only">Danger</span>
        <div>
            <span class="font-medium">Terjadi kesalahan validasi:</span>
            <ul class="mt-1.5 list-disc list-inside">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <div class="space-y-5 sm:space-y-6" x-data="{ activeTab: 'active' }">
        <div
            class="rounded-2xl border border-gray-200 px-5 py-4 sm:px-6 sm:py-5 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                {{-- Tab Navigation --}}
                <div class="flex p-1 bg-gray-100 dark:bg-gray-800 rounded-xl w-fit">
                    <button @click="activeTab = 'active'"
                        :class="activeTab === 'active' ? 'bg-white dark:bg-gray-700 shadow-sm text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
                        class="px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200">
                        Aktif ({{ $produksiActive->count() }})
                    </button>
                    <button @click="activeTab = 'cancelled'"
                        :class="activeTab === 'cancelled' ? 'bg-white dark:bg-gray-700 shadow-sm text-red-600 dark:text-red-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
                        class="px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200">
                        Dibatalkan ({{ $produksiCancelled->count() }})
                    </button>
                </div>

                <a href="{{ route('gudang.produksiRakitan.create') }}"
                    class="inline-block px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 w-fit">
                    + Tambah Produksi
                </a>
            </div>

            {{-- Tab Aktif --}}
            <div x-show="activeTab === 'active'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                <table id="table-active" class="min-w-full">
                    <thead>
                        <tr>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">No Rakitan</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Tanggal</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Gudang</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Barang Hasil</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Qty</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-right">Total Biaya</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">Petugas</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($produksiActive as $item)
                        <tr>
                            <td class="font-medium text-gray-900">{{ $item->nomor_rakitan }}</td>
                            <td>{{ $item->tanggal_rakitan->format('d/m/Y') }}</td>
                            <td>{{ $item->ubs->nama_ubs ?? 'HUB' }}</td>
                            <td>
                                <div class="font-medium">{{ $item->barangHasil->nama_barang }}</div>
                                <div class="text-xs text-gray-500">{{ $item->barangHasil->kode_barang }}</div>
                            </td>
                            <td>{{ rtrim(rtrim(number_format($item->qty_hasil, 3, ',', '.'), '0'), ',') }} {{ $item->satuanHasil->nama }}</td>
                            <td class="text-right font-semibold">Rp {{ rtrim(rtrim(number_format($item->total_biaya, 2, ',', '.'), '0'), ',') }}</td>
                            <td class="text-center text-sm">{{ $item->creator->username ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('gudang.produksiRakitan.show', $item) }}" class="p-1.5 text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <form action="{{ route('gudang.produksiRakitan.destroy', $item) }}" method="POST" class="cancel-form">
                                        @csrf @method('DELETE')
                                        <button type="button" class="cancel-btn p-1.5 text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Tab Dibatalkan --}}
            <div x-show="activeTab === 'cancelled'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" style="display: none;">
                <table id="table-cancelled" class="min-w-full">
                    <thead>
                        <tr>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">No Rakitan</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Tgl Batal</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Barang Hasil</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Alasan Pembatalan</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">Oleh</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($produksiCancelled as $item)
                        <tr class="opacity-75 grayscale-[0.5]">
                            <td class="font-medium text-gray-500 italic">{{ $item->nomor_rakitan }}</td>
                            <td class="text-sm">{{ $item->cancelled_at ? $item->cancelled_at->format('d/m/Y H:i') : '-' }}</td>
                            <td>
                                <div class="text-sm font-medium">{{ $item->barangHasil->nama_barang }}</div>
                                <div class="text-xs text-gray-400">{{ $item->barangHasil->kode_barang }}</div>
                            </td>
                            <td class="max-w-xs text-sm text-red-600 italic truncate" title="{{ $item->cancel_reason }}">
                                {{ $item->cancel_reason ?: 'Tanpa alasan' }}
                            </td>
                            <td class="text-center text-sm">{{ $item->canceller->username ?? '-' }}</td>
                            <td class="text-center">
                                <a href="{{ route('gudang.produksiRakitan.show', $item) }}" class="inline-flex p-1.5 text-gray-500 bg-gray-50 rounded-lg hover:bg-gray-100">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>
<!-- ===== Main Content End ===== -->

{{-- sweatalert 2 for cancel data --}}
<script>
    document.addEventListener('click', function(e) {
        if (e.target.closest('.cancel-btn')) {
            const btn = e.target.closest('.cancel-btn');
            const form = btn.closest('.cancel-form');

            Swal.fire({
                title: 'Batalkan Produksi?',
                text: "Silahkan masukkan alasan pembatalan:",
                input: 'text',
                inputPlaceholder: 'Contoh: Salah input Qty / Salah Barang',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Batalkan!',
                cancelButtonText: 'Batal',
                inputValidator: (value) => {
                    if (!value) {
                        return 'Alasan pembatalan wajib diisi!'
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'cancel_reason';
                    hiddenInput.value = result.value;
                    form.appendChild(hiddenInput);
                    form.submit();
                }
            });
        }
    });

    function initTable(id) {
        if (document.getElementById(id) && typeof simpleDatatables.DataTable !== 'undefined') {
            new simpleDatatables.DataTable("#" + id, {
                searchable: true,
                sortable: true,
                perPage: 10,
                perPageSelect: [10, 25, 50]
            });
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        initTable("table-active");
        initTable("table-cancelled");
    });
</script>
@endsection
