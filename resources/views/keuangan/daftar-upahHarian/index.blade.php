@extends('layouts.app')

@section('pageActive', $pageActive)

@section('content')
<!-- ===== Main Content Start ===== -->
<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

    <!-- Breadcrumb Start -->
    <div x-data="{ pageName: '{{ $pageActive }}' }">
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

    <div class="space-y-5 sm:space-y-6">
        <div
            class="rounded-2xl border border-gray-200 px-5 py-4 sm:px-6 sm:py-5 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

            {{-- Header & Filter --}}
            <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                        Daftar Upah Harian Tukang
                    </h3>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                        Menampilkan pengajuan dengan status <span class="font-medium text-yellow-600 dark:text-yellow-400">"Diajukan"</span>
                    </p>
                </div>

                {{-- Filter Referensi --}}
               <div class="flex flex-wrap items-center gap-3">
    <span class="text-xs font-medium text-gray-500 dark:text-gray-400 whitespace-nowrap">
        Filter Referensi:
    </span>

    <div class="flex rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm">
        <a href="{{ route('keuangan.daftarUpahHarian.index') }}"
            id="filter-all"
            class="px-4 py-2 transition-colors
                {{ $referensi === 'all'
                    ? 'bg-blue-600 text-white'
                    : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' }}">
            Semua
        </a>

        <a href="{{ route('keuangan.daftarUpahHarian.index', ['referensi' => 'perumahan']) }}"
            id="filter-abm"
            class="px-4 py-2 border-l border-gray-200 dark:border-gray-700 transition-colors
                {{ $referensi === 'perumahan'
                    ? 'bg-blue-600 text-white'
                    : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' }}">
            ABM (Perumahan)
        </a>

        <a href="{{ route('keuangan.daftarUpahHarian.index', ['referensi' => 'mangoon']) }}"
            id="filter-mangoon"
            class="px-4 py-2 border-l border-gray-200 dark:border-gray-700 transition-colors
                {{ $referensi === 'mangoon'
                    ? 'bg-blue-600 text-white'
                    : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' }}">
            Mangoon
        </a>
    </div>
</div>
            </div>

            {{-- Summary Badge --}}
            <div class="mb-4">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium
                    {{ $referensi === 'perumahan' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' :
                       ($referensi === 'mangoon' ? 'bg-teal-50 text-teal-700 dark:bg-teal-900/30 dark:text-teal-300' :
                        'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                    </svg>
                    {{ $pengajuan->count() }} data ditemukan
                    @if($referensi === 'perumahan')
                        &mdash; Referensi: ABM / Perumahan
                    @elseif($referensi === 'mangoon')
                        &mdash; Referensi: Mangoon
                    @else
                        &mdash; Semua Referensi
                    @endif
                </span>
            </div>

            {{-- Table --}}
            <table id="table-daftarUpahHarianKeuangan">
                <thead>
                    <tr>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                            Nomor Pengajuan
                        </th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                            Referensi
                        </th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                            Periode
                        </th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                            Jumlah Tukang
                        </th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                            Total Upah
                        </th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                            Status
                        </th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                            Dibuat Oleh
                        </th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                            Dibuat Pada
                        </th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pengajuan as $index => $item)
                    <tr>
                        {{-- Nomor Pengajuan --}}
                        <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            {{ $item->nomor_upah_harian }}
                        </td>

                        {{-- Referensi --}}
                        <td class="text-center whitespace-nowrap">
                            @if($item->jenis_referensi === 'perumahan')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300">
                                ABM (Perumahan)
                            </span>
                            @elseif($item->jenis_referensi === 'mangoon')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-300">
                                Mangoon
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                {{ $item->jenis_referensi ?? '-' }}
                            </span>
                            @endif
                        </td>

                        {{-- Periode --}}
                        <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            @php
                                $mulai   = $item->tanggal_mulai;
                                $selesai = $item->tanggal_selesai;
                                if ($mulai && $selesai) {
                                    if ($mulai->format('Y-m') === $selesai->format('Y-m')) {
                                        $periode = $mulai->translatedFormat('d') . ' – ' . $selesai->translatedFormat('d F Y');
                                    } elseif ($mulai->format('Y') === $selesai->format('Y')) {
                                        $periode = $mulai->translatedFormat('d F') . ' – ' . $selesai->translatedFormat('d F Y');
                                    } else {
                                        $periode = $mulai->translatedFormat('d F Y') . ' – ' . $selesai->translatedFormat('d F Y');
                                    }
                                } else {
                                    $periode = '-';
                                }
                            @endphp
                            {{ $periode }}
                        </td>

                        {{-- Jumlah Tukang --}}
                        <td class="font-medium text-center text-gray-900 whitespace-nowrap dark:text-white">
                            {{ $item->rekap->count() }} Orang
                        </td>

                        {{-- Total Upah --}}
                        <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            Rp {{ number_format($item->rekap->sum('total_upah'), 0, ',', '.') }}
                        </td>

                        {{-- Status --}}
                        <td class="p-2 whitespace-nowrap align-middle text-center">
                            @if($item->status === 'disetujui')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                Disetujui
                            </span>
                            @elseif($item->status === 'diajukan')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                Diajukan
                            </span>
                            @elseif($item->status === 'ditolak')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                                Ditolak
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                Draft
                            </span>
                            @endif
                        </td>

                        {{-- Dibuat Oleh --}}
                        <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            {{ $item->createdBy->username ?? $item->createdBy->name ?? '-' }}
                        </td>

                        {{-- Terakhir Diubah --}}
                        <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            {{ $item->updated_at ? $item->updated_at->translatedFormat('d F Y H:i') : '-' }}
                        </td>

                        {{-- Aksi --}}
                        <td class="px-4 py-3">
                            <div class="flex justify-center gap-2">
                                @can('keuangan.upah-harian-tukang.daftar-pengajuan.detail')
                                <a href="{{ route('keuangan.daftarUpahHarian.detail', $item->id) }}"
                                    id="btn-detail-{{ $item->id }}"
                                    class="inline-flex items-center gap-1 text-xs font-medium text-blue-700 bg-blue-100 hover:bg-blue-200 dark:bg-blue-800 dark:text-blue-100 dark:hover:bg-blue-700 px-2.5 py-1.5 rounded-md transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1 active:scale-95">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.573-3.007-9.964-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    Detail
                                </a>
                                @else
                                <span class="text-xs text-gray-400">-</span>
                                @endcan
                            </div>
                        </td>

                    </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>

        </div>
    </div>

</div>
<!-- ===== Main Content End ===== -->

<script>
    if (document.getElementById("table-daftarUpahHarianKeuangan") && typeof simpleDatatables.DataTable !== 'undefined') {
        const dataTable = new simpleDatatables.DataTable("#table-daftarUpahHarianKeuangan", {
            searchable: true,
            sortable: true,
            perPageSelect: [10, 25, 50, 100],
        });
    }
</script>
@endsection
