@extends('layouts.app')

@section('pageActive', 'pembangunanLain')

@section('content')
<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-data="pembangunanLainApp()">
    @include('partials.breadcrumb', ['breadcrumbs' => [['label' => 'Pembangunan Lain-lain', 'url' => route('produksi.pembangunanLain.index')]]])

    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: '{{ session('success') }}',
                    showConfirmButton: false,
                    timer: 2000
                });
            });
        </script>
    @endif
    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Perhatian',
                    text: '{{ session('error') }}',
                    showConfirmButton: true
                });
            });
        </script>
    @endif

    {{-- Top Action Bar: Search, Status Filter & Tombol Tambah --}}
    <div class="mb-6 mt-6 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
        <form method="GET" action="{{ route('produksi.pembangunanLain.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 flex-1">
            <div class="relative w-full sm:w-80">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama proyek atau sub..."
                    class="w-full rounded-lg border border-gray-200 bg-white py-2 pl-9 pr-4 text-sm text-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition-all outline-none" />
            </div>

            <div class="flex items-center gap-1.5 p-1 bg-gray-100 dark:bg-gray-800 rounded-lg w-fit">
                <a href="{{ route('produksi.pembangunanLain.index', ['status' => 'all', 'search' => request('search')]) }}"
                    class="px-3 py-1.5 text-xs font-bold rounded-md transition-all uppercase {{ $statusFilter === 'all' ? 'bg-white shadow text-blue-600 dark:bg-gray-700 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400' }}">Semua</a>
                <a href="{{ route('produksi.pembangunanLain.index', ['status' => 'proses', 'search' => request('search')]) }}"
                    class="px-3 py-1.5 text-xs font-bold rounded-md transition-all uppercase {{ $statusFilter === 'proses' ? 'bg-white shadow text-blue-600 dark:bg-gray-700 dark:text-blue-400' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400' }}">Proses</a>
                <a href="{{ route('produksi.pembangunanLain.index', ['status' => 'selesai', 'search' => request('search')]) }}"
                    class="px-3 py-1.5 text-xs font-bold rounded-md transition-all uppercase {{ $statusFilter === 'selesai' ? 'bg-white shadow text-green-600 dark:bg-gray-700 dark:text-green-400' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400' }}">Selesai</a>
                <a href="{{ route('produksi.pembangunanLain.index', ['status' => 'pending', 'search' => request('search')]) }}"
                    class="px-3 py-1.5 text-xs font-bold rounded-md transition-all uppercase {{ $statusFilter === 'pending' ? 'bg-white shadow text-amber-600 dark:bg-gray-700 dark:text-amber-400' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400' }}">Pending</a>
            </div>
        </form>

        @can('produksi.lain.pembangunan-lain.create')
        <div class="flex items-center gap-3">
            <button type="button" @click="openModalCreateProyek()"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg transition shadow-sm whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Buat Proyek Baru
            </button>
        </div>
        @endcan
    </div>

    {{-- Daftar Proyek & Sub-Pekerjaan (Tampilan Accordion) --}}
    <div class="space-y-4">
        @forelse($allPembangunanLain as $proyek)
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-800/80 transition-all hover:border-gray-300 dark:hover:border-gray-700 overflow-hidden"
                x-data="{ isOpen: {{ !empty(request('search')) ? 'true' : 'false' }} }">
                
                {{-- Header Proyek (Clickable Accordion Trigger) --}}
                <div @click="isOpen = !isOpen"
                    class="p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 cursor-pointer hover:bg-gray-50/70 dark:hover:bg-gray-700/30 transition-colors select-none">
                    
                    {{-- Sisi Kiri: Info Proyek --}}
                    <div class="flex items-center gap-3.5">
                        <div class="p-2.5 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2.5 flex-wrap">
                                <h3 class="text-base font-bold text-gray-900 dark:text-white">
                                    {{ $proyek->nama_proyek }}
                                </h3>
                                <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full uppercase
                                    {{ $proyek->status === 'selesai' ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' : ($proyek->status === 'pending' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300') }}">
                                    {{ $proyek->status }}
                                </span>
                                <span class="px-2 py-0.5 text-[10px] font-semibold rounded bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                    {{ $proyek->subs_count }} Sub-Pekerjaan
                                </span>
                                @if($proyek->orders_count > 0)
                                    <span class="px-2 py-0.5 text-[10px] font-semibold rounded bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300">
                                        {{ $proyek->orders_count }} Transaksi Order
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                Terdaftar sejak {{ $proyek->created_at->format('d M Y') }} &bull; Klik kartu untuk <span class="font-medium text-blue-500" x-text="isOpen ? 'sembunyikan' : 'lihat rincian sub-pekerjaan'"></span>
                            </p>
                        </div>
                    </div>

                    {{-- Sisi Kanan: Aksi Proyek & Icon Accordion Chevron --}}
                    <div class="flex items-center gap-2 self-end sm:self-center">
                        @can('produksi.lain.pembangunan-lain.termin')
                        <a href="{{ route('produksi.pembangunanLain.exportTermin', $proyek->id) }}"
                            @click.stop
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-900/40 dark:text-emerald-300 rounded-lg transition"
                            title="Export Laporan Termin Excel">
                            <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Termin
                        </a>
                        @endcan
                        @can('produksi.lain.pembangunan-lain.create')
                        <button type="button" @click.stop="openModalCreateSub(@js(['id' => $proyek->id, 'nama_proyek' => $proyek->nama_proyek]))"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/40 dark:text-blue-300 rounded-lg transition"
                            title="Tambah Sub-Pekerjaan">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Tambah Sub
                        </button>
                        @endcan
                        @can('produksi.lain.pembangunan-lain.edit')
                        <button type="button" @click.stop="openModalEditProyek(@js(['id' => $proyek->id, 'nama_proyek' => $proyek->nama_proyek, 'status' => $proyek->status]))"
                            class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-yellow-700 bg-yellow-50 hover:bg-yellow-100 dark:bg-yellow-900/40 dark:text-yellow-300 rounded-lg transition"
                            title="Edit Status & Nama Proyek">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit
                        </button>
                        @endcan
                        @can('produksi.lain.pembangunan-lain.delete')
                        <button type="button" @click.stop="confirmDeleteProyek(@js(['id' => $proyek->id, 'nama' => $proyek->nama_proyek, 'orders_count' => $proyek->orders_count]))"
                            class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-red-700 bg-red-50 hover:bg-red-100 dark:bg-red-900/40 dark:text-red-300 rounded-lg transition"
                            title="Hapus Proyek">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Hapus
                        </button>
                        @endcan

                        {{-- Chevron Accordion Toggle --}}
                        <div class="ml-1 pl-2 border-l border-gray-200 dark:border-gray-700 flex items-center gap-1">
                            <svg class="w-5 h-5 text-gray-400 transition-transform duration-200"
                                :class="isOpen ? 'rotate-180 text-blue-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Body Accordion (Tabel Sub-Pekerjaan) --}}
                <div x-show="isOpen" x-cloak class="border-t border-gray-100 dark:border-gray-700/60 bg-gray-50/50 dark:bg-gray-900/20 p-4 sm:p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold uppercase text-gray-500 dark:text-gray-400 tracking-wider">
                                Sub-Pekerjaan Proyek
                            </span>
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                                {{ $proyek->subs->count() }} Sub
                            </span>
                        </div>
                        @can('produksi.lain.pembangunan-lain.create')
                        <button type="button" @click.stop="openModalCreateSub(@js(['id' => $proyek->id, 'nama_proyek' => $proyek->nama_proyek]))"
                            class="text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 inline-flex items-center gap-1">
                            + Tambah Sub Lagi
                        </button>
                        @endcan
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-gray-200/80 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-xs">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-50 text-gray-500 uppercase dark:bg-gray-700/50 dark:text-gray-400 font-semibold border-b border-gray-100 dark:border-gray-700">
                                <tr>
                                    <th class="px-4 py-2.5">Nama Sub-Pekerjaan</th>
                                    <th class="px-4 py-2.5">Rentang Waktu</th>
                                    <th class="px-4 py-2.5">Status</th>
                                    <th class="px-4 py-2.5">Catatan</th>
                                    <th class="px-4 py-2.5 text-center">Transaksi Order</th>
                                    <th class="px-4 py-2.5 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60">
                                @forelse($proyek->subs as $sub)
                                    <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-700/20 transition-colors">
                                        <td class="px-4 py-3 font-semibold text-gray-800 dark:text-white">
                                            <div class="flex items-center gap-2">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                                <span>{{ $sub->nama_sub }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                                            {{ $sub->tanggal_mulai ? $sub->tanggal_mulai->format('d M Y') : '-' }} s/d {{ $sub->tanggal_selesai ? $sub->tanggal_selesai->format('d M Y') : 'Sekarang' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-0.5 text-[9px] font-bold rounded uppercase
                                                {{ $sub->status === 'selesai' ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' : ($sub->status === 'pending' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300') }}">
                                                {{ $sub->status }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400 italic max-w-xs truncate">
                                            {{ $sub->catatan ?: '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                                {{ $sub->orders_count }} Order
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="inline-flex items-center gap-1.5">
                                                @can('produksi.lain.pembangunan-lain.termin')
                                                <a href="{{ route('produksi.pembangunanLain.exportTerminSub', $sub->id) }}"
                                                    @click.stop
                                                    class="px-2 py-1 text-[11px] font-semibold text-emerald-700 bg-emerald-100 hover:bg-emerald-200 dark:bg-emerald-900/40 dark:text-emerald-300 rounded transition inline-flex items-center gap-1"
                                                    title="Export Laporan Termin Sub-Pekerjaan Ini">
                                                    <svg class="w-3 h-3 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                    Termin
                                                </a>
                                                @endcan
                                                @can('produksi.lain.pembangunan-lain.edit')
                                                <button type="button" @click.stop="openModalEditSub(@js([
                                                    'id' => $sub->id,
                                                    'nama_sub' => $sub->nama_sub,
                                                    'status' => $sub->status,
                                                    'tanggal_mulai' => $sub->tanggal_mulai ? $sub->tanggal_mulai->format('Y-m-d') : '',
                                                    'tanggal_selesai' => $sub->tanggal_selesai ? $sub->tanggal_selesai->format('Y-m-d') : '',
                                                    'catatan' => $sub->catatan ?? ''
                                                ]))"
                                                    class="px-2 py-1 text-[11px] font-semibold text-yellow-700 bg-yellow-100 hover:bg-yellow-200 dark:bg-yellow-900/40 dark:text-yellow-300 rounded transition">
                                                    Edit
                                                </button>
                                                @endcan
                                                @can('produksi.lain.pembangunan-lain.delete')
                                                <button type="button" @click.stop="confirmDeleteSub(@js([
                                                    'id' => $sub->id,
                                                    'nama' => $sub->nama_sub,
                                                    'sibling_count' => $proyek->subs->count(),
                                                    'orders_count' => $sub->orders_count
                                                ]))"
                                                    class="px-2 py-1 text-[11px] font-semibold text-red-700 bg-red-100 hover:bg-red-200 dark:bg-red-900/40 dark:text-red-300 rounded transition">
                                                    Hapus
                                                </button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-4 text-center text-xs text-gray-400 italic">
                                            Belum ada sub-pekerjaan yang terdaftar.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-12 text-center dark:border-gray-700 dark:bg-gray-800">
                <div class="mx-auto w-12 h-12 flex items-center justify-center rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <h4 class="text-base font-bold text-gray-800 dark:text-white">Belum Ada Proyek Lain-lain</h4>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 max-w-sm mx-auto">
                    Mulai catat proyek non-unit seperti Prive Direksi, Gudang, atau Kantor untuk pelacakan barang keluar & termin.
                </p>
                <button type="button" @click="openModalCreateProyek()"
                    class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 transition">
                    + Buat Proyek Sekarang
                </button>
            </div>
        @endforelse
    </div>

    {{-- MODAL 1: Buat Proyek Baru (Beserta Sub Pertama) --}}
    <div x-show="showModalCreateProyek" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg overflow-hidden" @click.away="showModalCreateProyek = false">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Buat Proyek Baru</h3>
                <button type="button" @click="showModalCreateProyek = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form action="{{ route('produksi.pembangunanLain.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-700 dark:text-gray-300 mb-1.5">Nama Proyek Induk <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_proyek" required placeholder="Contoh: Prive P Saleh, Gudang LHR, Kantor ASA"
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-2.5 text-sm text-gray-900 dark:text-white outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 dark:text-gray-300 mb-1.5">Status Proyek</label>
                        <select name="status" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-2.5 text-sm text-gray-900 dark:text-white outline-none focus:border-blue-500">
                            <option value="proses" selected>Proses</option>
                            <option value="pending">Pending</option>
                            <option value="selesai">Selesai</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 dark:text-gray-300 mb-1.5">Nama Sub-Pekerjaan Awal</label>
                        <input type="text" name="nama_sub" placeholder="Contoh: Studio Musik (opsional)"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-2.5 text-sm text-gray-900 dark:text-white outline-none focus:border-blue-500">
                    </div>
                </div>
                <p class="text-[11px] text-gray-400 italic -mt-2">Jika nama sub dikosongkan, nama sub otomatis menggunakan nama proyek induk.</p>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 dark:text-gray-300 mb-1.5">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" value="{{ date('Y-m-d') }}"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-2 text-sm text-gray-900 dark:text-white outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 dark:text-gray-300 mb-1.5">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-2 text-sm text-gray-900 dark:text-white outline-none focus:border-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-700 dark:text-gray-300 mb-1.5">Catatan (Opsional)</label>
                    <textarea name="catatan" rows="2" placeholder="Catatan pekerjaan..."
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-2.5 text-sm text-gray-900 dark:text-white outline-none focus:border-blue-500"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showModalCreateProyek = false" class="px-4 py-2 text-xs font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 rounded-lg transition">Batal</button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition shadow-sm">Simpan Proyek</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 2: Edit Induk Proyek (Nama & Status) --}}
    <div x-show="showModalEditProyek" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md overflow-hidden" @click.away="showModalEditProyek = false">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Edit Proyek</h3>
                <button type="button" @click="showModalEditProyek = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form :action="'{{ url('produksi/pembangunan-lain') }}/' + editProyekId" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-700 dark:text-gray-300 mb-1.5">Nama Proyek <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_proyek" x-model="editProyekNama" required
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-2.5 text-sm text-gray-900 dark:text-white outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-700 dark:text-gray-300 mb-1.5">Status Proyek <span class="text-red-500">*</span></label>
                    <select name="status" x-model="editProyekStatus" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-2.5 text-sm text-gray-900 dark:text-white outline-none focus:border-blue-500">
                        <option value="proses">Proses</option>
                        <option value="selesai">Selesai</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showModalEditProyek = false" class="px-4 py-2 text-xs font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 rounded-lg transition">Batal</button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition shadow-sm">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 3: Tambah Sub-Pekerjaan Baru --}}
    <div x-show="showModalCreateSub" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg overflow-hidden" @click.away="showModalCreateSub = false">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Tambah Sub-Pekerjaan</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Untuk Proyek: <span class="font-bold text-blue-600" x-text="parentProyekNama"></span></p>
                </div>
                <button type="button" @click="showModalCreateSub = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form :action="'{{ url('produksi/pembangunan-lain') }}/' + parentProyekId + '/sub'" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-700 dark:text-gray-300 mb-1.5">Nama Sub-Pekerjaan <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_sub" x-model="newSubNama" required placeholder="Contoh: Pembangunan Kolam Renang, Renovasi Lantai 2"
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-2.5 text-sm text-gray-900 dark:text-white outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-700 dark:text-gray-300 mb-1.5">Status <span class="text-red-500">*</span></label>
                    <select name="status" x-model="newSubStatus" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-2.5 text-sm text-gray-900 dark:text-white outline-none focus:border-blue-500">
                        <option value="proses">Proses</option>
                        <option value="selesai">Selesai</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 dark:text-gray-300 mb-1.5">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" x-model="newSubMulai"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-2 text-sm text-gray-900 dark:text-white outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 dark:text-gray-300 mb-1.5">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" x-model="newSubSelesai"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-2 text-sm text-gray-900 dark:text-white outline-none focus:border-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-700 dark:text-gray-300 mb-1.5">Catatan (Opsional)</label>
                    <textarea name="catatan" x-model="newSubCatatan" rows="2" placeholder="Catatan spesifik pekerjaan..."
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-2.5 text-sm text-gray-900 dark:text-white outline-none focus:border-blue-500"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showModalCreateSub = false" class="px-4 py-2 text-xs font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 rounded-lg transition">Batal</button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition shadow-sm">Simpan Sub-Pekerjaan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 4: Edit Sub-Pekerjaan --}}
    <div x-show="showModalEditSub" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg overflow-hidden" @click.away="showModalEditSub = false">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Edit Sub-Pekerjaan</h3>
                <button type="button" @click="showModalEditSub = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form :action="'{{ url('produksi/pembangunan-lain/sub') }}/' + editSubId" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-700 dark:text-gray-300 mb-1.5">Nama Sub-Pekerjaan <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_sub" x-model="editSubNama" required
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-2.5 text-sm text-gray-900 dark:text-white outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-700 dark:text-gray-300 mb-1.5">Status <span class="text-red-500">*</span></label>
                    <select name="status" x-model="editSubStatus" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-2.5 text-sm text-gray-900 dark:text-white outline-none focus:border-blue-500">
                        <option value="proses">Proses</option>
                        <option value="selesai">Selesai</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 dark:text-gray-300 mb-1.5">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" x-model="editSubMulai"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-2 text-sm text-gray-900 dark:text-white outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 dark:text-gray-300 mb-1.5">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" x-model="editSubSelesai"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-2 text-sm text-gray-900 dark:text-white outline-none focus:border-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-700 dark:text-gray-300 mb-1.5">Catatan</label>
                    <textarea name="catatan" x-model="editSubCatatan" rows="2"
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-2.5 text-sm text-gray-900 dark:text-white outline-none focus:border-blue-500"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showModalEditSub = false" class="px-4 py-2 text-xs font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 rounded-lg transition">Batal</button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition shadow-sm">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Hidden Form untuk Delete Action (Proyek & Sub) --}}
    <form id="formDeleteAction" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>

<script>
    function pembangunanLainApp() {
        return {
            showModalCreateProyek: false,
            showModalEditProyek: false,
            showModalCreateSub: false,
            showModalEditSub: false,

            // Edit Proyek
            editProyekId: '',
            editProyekNama: '',
            editProyekStatus: 'proses',

            // Create Sub
            parentProyekId: '',
            parentProyekNama: '',
            newSubNama: '',
            newSubStatus: 'proses',
            newSubMulai: '{{ date('Y-m-d') }}',
            newSubSelesai: '',
            newSubCatatan: '',

            // Edit Sub
            editSubId: '',
            editSubNama: '',
            editSubStatus: 'proses',
            editSubMulai: '',
            editSubSelesai: '',
            editSubCatatan: '',

            openModalCreateProyek() {
                this.showModalCreateProyek = true;
            },

            openModalEditProyek(data) {
                this.editProyekId = data.id;
                this.editProyekNama = data.nama_proyek;
                this.editProyekStatus = data.status;
                this.showModalEditProyek = true;
            },

            openModalCreateSub(data) {
                this.parentProyekId = data.id;
                this.parentProyekNama = data.nama_proyek;
                this.newSubNama = '';
                this.newSubStatus = 'proses';
                this.newSubMulai = '{{ date('Y-m-d') }}';
                this.newSubSelesai = '';
                this.newSubCatatan = '';
                this.showModalCreateSub = true;
            },

            openModalEditSub(sub) {
                this.editSubId = sub.id;
                this.editSubNama = sub.nama_sub;
                this.editSubStatus = sub.status;
                this.editSubMulai = sub.tanggal_mulai || '';
                this.editSubSelesai = sub.tanggal_selesai || '';
                this.editSubCatatan = sub.catatan || '';
                this.showModalEditSub = true;
            },

            confirmDeleteProyek(data) {
                if (data.orders_count > 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Tidak Dapat Dihapus',
                        text: `Proyek "${data.nama}" sudah memiliki ${data.orders_count} riwayat transaksi order barang keluar dan tidak dapat dihapus.`,
                        confirmButtonColor: '#3b82f6',
                    });
                    return;
                }

                Swal.fire({
                    title: 'Hapus Proyek?',
                    text: `Apakah Anda yakin ingin menghapus proyek "${data.nama}" beserta seluruh sub-pekerjaannya?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Hapus Proyek',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.getElementById('formDeleteAction');
                        form.action = `{{ url('produksi/pembangunan-lain') }}/${data.id}`;
                        form.submit();
                    }
                });
            },

            confirmDeleteSub(data) {
                if (data.sibling_count <= 1) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Tidak Dapat Dihapus',
                        text: `Sub-pekerjaan "${data.nama}" adalah satu-satunya sub-pekerjaan di proyek ini. Setiap proyek wajib memiliki minimal 1 sub-pekerjaan.`,
                        confirmButtonColor: '#3b82f6',
                    });
                    return;
                }

                if (data.orders_count > 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Tidak Dapat Dihapus',
                        text: `Sub-pekerjaan "${data.nama}" sudah memiliki ${data.orders_count} riwayat transaksi order barang keluar dan tidak dapat dihapus.`,
                        confirmButtonColor: '#3b82f6',
                    });
                    return;
                }

                Swal.fire({
                    title: 'Hapus Sub-Pekerjaan?',
                    text: `Apakah Anda yakin ingin menghapus sub-pekerjaan "${data.nama}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Hapus Sub',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.getElementById('formDeleteAction');
                        form.action = `{{ url('produksi/pembangunan-lain/sub') }}/${data.id}`;
                        form.submit();
                    }
                });
            }
        };
    }
</script>
@endsection
