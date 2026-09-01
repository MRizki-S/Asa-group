@extends('layouts.app')

@section('pageActive', 'StokBarangGudang')

@section('content')
<!-- ===== Main Content Start ===== -->
<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

    <!-- Breadcrumb Start -->
    <div x-data="{ pageName: 'StokBarangGudang' }">
        @include('partials.breadcrumb')
    </div>
    <!-- Breadcrumb End -->

    {{-- Flash Messages --}}
    @if(session('error'))
    <div class="flex p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
        <svg class="shrink-0 inline w-4 h-4 me-3 mt-[2px]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
        </svg>
        <div><span class="font-bold">Error!</span> {{ session('error') }}</div>
    </div>
    @endif

    <div class="space-y-5 sm:space-y-6">
        <div class="rounded-2xl border border-gray-200 px-5 py-4 sm:px-6 sm:py-5 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

            {{-- Header --}}
            <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                <div>
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                        Detail Transfer Stock
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ $transfer->nomor_transfer }}
                        &bull;
                        {{ \Carbon\Carbon::parse($transfer->tanggal_transfer)->format('d-M-Y') }}
                    </p>
                </div>
                <div class="flex gap-2">
                    @can('gudang.transfer-stock.print-pdf')
                    <a href="{{ route('gudang.transferStockBarang.daftar.pdf', $transfer->nomor_transfer) }}"
                        target="_blank"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Cetak PDF
                    </a>
                    @endcan

                    <a href="{{ route('gudang.transferStockBarang.daftar.index') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali
                    </a>
                </div>
            </div>

            {{-- Status Badge --}}
            <div class="mb-6">
                @if($transfer->status === 'pending')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                        Menunggu Persetujuan SPV
                    </span>
                @elseif($transfer->status === 'disetujui')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-semibold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                        Disetujui &mdash;
                        oleh {{ $transfer->approvedBy->username ?? $transfer->approvedBy->name ?? '-' }}
                        pada {{ $transfer->approved_at?->format('d-M-Y H:i') }}
                    </span>
                @elseif($transfer->status === 'ditolak')
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-semibold bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                            Ditolak
                        </span>
                        {{-- Tombol Edit — hanya untuk Admin --}}
                        @if(Auth::user()->hasRole(['superadmin', 'STAF ADMINISTRASI GUDANG']))
                        <a href="{{ route('gudang.transferStockBarang.edit', $transfer->nomor_transfer) }}"
                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-orange-500 rounded-lg hover:bg-orange-600 focus:ring-4 focus:ring-orange-300 transition active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit & Ajukan Ulang
                        </a>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Info Transfer --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 p-4 bg-gray-50 dark:bg-gray-800/40 rounded-xl">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Dari Gudang</p>
                    <p class="text-sm font-semibold text-gray-800 dark:text-white">
                        {{ $transfer->fromUbs->nama_ubs ?? '-' }}
                        @if($transfer->fromUbs?->kode_ubs)
                            <span class="text-gray-500 font-normal">({{ $transfer->fromUbs->kode_ubs }})</span>
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Ke Gudang</p>
                    <p class="text-sm font-semibold text-gray-800 dark:text-white">
                        {{ $transfer->toUbs->nama_ubs ?? '-' }}
                        @if($transfer->toUbs?->kode_ubs)
                            <span class="text-gray-500 font-normal">({{ $transfer->toUbs->kode_ubs }})</span>
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Diajukan Oleh</p>
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        {{ $transfer->creator->username ?? $transfer->creator->name ?? '-' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Keterangan</p>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $transfer->keterangan ?? '-' }}</p>
                </div>
            </div>

            {{-- Tabel Barang --}}
            <div class="mb-6">
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Daftar Barang yang Ditransfer</h4>
                <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-100 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">No</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Barang</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase text-center">Jumlah</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase text-center">Satuan</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase text-center">Qty Base</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($transfer->details as $no => $detail)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/60 transition">
                                <td class="px-4 py-3 text-gray-500">{{ $no + 1 }}</td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-800 dark:text-white">
                                        {{ $detail->nama_barang_snapshot ?? $detail->barang->nama_barang ?? '-' }}
                                    </p>
                                    @if($detail->barang?->kode_barang)
                                        <p class="text-xs text-gray-400">{{ $detail->barang->kode_barang }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center font-semibold text-gray-800 dark:text-white">
                                    {{ (float)$detail->qty }}
                                </td>
                                <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-400">
                                    {{ $detail->satuan->nama ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-center text-gray-500 text-xs">
                                    {{ (float)$detail->qty_base }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Tombol ACC / Tolak — hanya jika pending --}}
            @if($transfer->status === 'pending')
            <div
                x-data="{
                    showModal: false,
                    modalType: '',
                    openModal(type) { this.modalType = type; this.showModal = true; },
                    closeModal() { this.showModal = false; }
                }"
                class="pt-4 border-t border-gray-100 dark:border-gray-800">

                {{-- Trigger Buttons --}}
                @can('gudang.transfer-stock.action')
                    <div class="flex justify-end gap-3">
                        {{-- Tombol Tolak --}}
                        <button type="button" @click="openModal('tolak')"
                            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-red-600 rounded-xl hover:bg-red-700 focus:ring-4 focus:ring-red-300 dark:bg-red-500 dark:hover:bg-red-600 transition-all duration-200 active:scale-95 shadow-sm shadow-red-200 dark:shadow-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Tolak
                        </button>

                        {{-- Tombol ACC --}}
                        <button type="button" @click="openModal('acc')"
                            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-green-600 rounded-xl hover:bg-green-700 focus:ring-4 focus:ring-green-300 dark:bg-green-500 dark:hover:bg-green-600 transition-all duration-200 active:scale-95 shadow-sm shadow-green-200 dark:shadow-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            ACC (Setujui)
                        </button>
                    </div>
                @endcan

                {{-- Modal Overlay --}}
                <div x-show="showModal"
                    x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
                    style="display:none;">

                    {{-- ===== MODAL TOLAK ===== --}}
                    <div x-show="modalType === 'tolak'"
                        x-transition:enter="ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-md p-6 border border-red-100 dark:border-red-900/40"
                        @click.outside="closeModal()"
                        style="display:none;">

                        {{-- Icon --}}
                        <div class="flex items-center justify-center w-14 h-14 rounded-full bg-red-100 dark:bg-red-900/30 mx-auto mb-4">
                            <svg class="w-7 h-7 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>

                        <h3 class="text-lg font-bold text-gray-800 dark:text-white text-center mb-1">Tolak Pengajuan Transfer?</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-4">
                            Pengajuan ini akan ditolak dan Admin Gudang dapat melakukan perbaikan serta mengajukan ulang.
                        </p>

                        {{-- Info Ringkas --}}
                        <div class="bg-red-50 dark:bg-red-900/20 rounded-xl p-3 mb-5 text-xs space-y-1.5 border border-red-200 dark:border-red-800">
                            <div class="flex justify-between">
                                <span class="text-red-600 dark:text-red-400 font-semibold">No. Transfer</span>
                                <span class="text-gray-700 dark:text-gray-300 font-mono">{{ $transfer->nomor_transfer }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-red-600 dark:text-red-400 font-semibold">Dari</span>
                                <span class="text-gray-700 dark:text-gray-300">{{ $transfer->fromUbs->nama_ubs ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-red-600 dark:text-red-400 font-semibold">Ke</span>
                                <span class="text-gray-700 dark:text-gray-300">{{ $transfer->toUbs->nama_ubs ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-red-600 dark:text-red-400 font-semibold">Jml Barang</span>
                                <span class="text-gray-700 dark:text-gray-300">{{ $transfer->details->count() }} item</span>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <button type="button" @click="closeModal()"
                                class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition active:scale-95">
                                Batal
                            </button>
                            <form action="{{ route('gudang.transferStockBarang.daftar.reject', $transfer->nomor_transfer) }}" method="POST" class="flex-1">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="w-full px-4 py-2.5 text-sm font-semibold text-white bg-red-600 rounded-xl hover:bg-red-700 focus:ring-4 focus:ring-red-300 transition active:scale-95">
                                    Ya, Tolak
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- ===== MODAL ACC ===== --}}
                    <div x-show="modalType === 'acc'"
                        x-transition:enter="ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-md p-6 border border-green-100 dark:border-green-900/40"
                        @click.outside="closeModal()"
                        style="display:none;">

                        {{-- Icon --}}
                        <div class="flex items-center justify-center w-14 h-14 rounded-full bg-green-100 dark:bg-green-900/30 mx-auto mb-4">
                            <svg class="w-7 h-7 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>

                        <h3 class="text-lg font-bold text-gray-800 dark:text-white text-center mb-1">Setujui Transfer Ini?</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-4">
                            Stok gudang akan <span class="font-semibold text-gray-700 dark:text-gray-300">langsung berubah</span> setelah disetujui. Pastikan data sudah benar.
                        </p>

                        {{-- Info Ringkas --}}
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-xl p-3 mb-3 text-xs space-y-1.5 border border-green-200 dark:border-green-800">
                            <div class="flex justify-between">
                                <span class="text-green-700 dark:text-green-400 font-semibold">No. Transfer</span>
                                <span class="text-gray-700 dark:text-gray-300 font-mono">{{ $transfer->nomor_transfer }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-green-700 dark:text-green-400 font-semibold">Dari</span>
                                <span class="text-gray-700 dark:text-gray-300">{{ $transfer->fromUbs->nama_ubs ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-green-700 dark:text-green-400 font-semibold">Ke</span>
                                <span class="text-gray-700 dark:text-gray-300">{{ $transfer->toUbs->nama_ubs ?? '-' }}</span>
                            </div>
                        </div>

                        {{-- Daftar Barang --}}
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-3 mb-5 text-xs border border-gray-200 dark:border-gray-700 max-h-36 overflow-y-auto">
                            <p class="font-semibold text-gray-600 dark:text-gray-300 mb-2">Barang yang Ditransfer:</p>
                            @foreach($transfer->details as $no => $d)
                            <div class="flex justify-between py-0.5 border-b border-gray-100 dark:border-gray-700 last:border-0">
                                <span class="text-gray-700 dark:text-gray-300">{{ $no+1 }}. {{ $d->nama_barang_snapshot ?? $d->barang->nama_barang ?? '-' }}</span>
                                <span class="font-semibold text-gray-800 dark:text-white ml-2 whitespace-nowrap">{{ (float)$d->qty }} {{ $d->satuan->nama ?? '' }}</span>
                            </div>
                            @endforeach
                        </div>

                        <div class="flex gap-3">
                            <button type="button" @click="closeModal()"
                                class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition active:scale-95">
                                Batal
                            </button>
                            <form action="{{ route('gudang.transferStockBarang.daftar.approve', $transfer->nomor_transfer) }}" method="POST" class="flex-1">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="w-full px-4 py-2.5 text-sm font-semibold text-white bg-green-600 rounded-xl hover:bg-green-700 focus:ring-4 focus:ring-green-300 transition active:scale-95">
                                    Ya, Setujui
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
            @endif

        </div>
    </div>

</div>
<!-- ===== Main Content End ===== -->
@endsection

