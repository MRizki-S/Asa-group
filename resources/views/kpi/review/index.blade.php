@extends('layouts.app')

@section('pageActive', 'Review-KPI')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <div x-data="{ pageName: 'Review Materialitas KPI' }">
            @include('partials.breadcrumb')
        </div>

        {{-- Alert Success --}}
        @if (session('success'))
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

        <div class="space-y-5 sm:space-y-6">
            <div
                class="rounded-2xl border border-gray-200 px-5 py-4 sm:px-6 sm:py-5 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

                {{-- Header --}}
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Daftar Review Penilaian KPI</h3>
                        <p class="text-xs text-gray-500 mt-1 italic">* Manajer dapat melakukan penyesuaian skor untuk komponen yang memerlukan review.</p>
                    </div>
                </div>

                @if ($reviews->isEmpty())
                    <div class="py-16 text-center">
                        <svg class="mx-auto mb-3 w-10 h-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-gray-400 dark:text-gray-500 italic text-sm">Tidak ada penilaian yang memerlukan review.</p>
                    </div>
                @else
                <div class="max-w-full overflow-x-auto custom-scrollbar">
                    <table id="table-review-kpi" class="min-w-full" style="min-width: 680px;">
                        <thead>
                            <tr class="text-left border-b border-gray-200 dark:border-gray-800">
                                <th class="py-3 px-4 font-medium text-sm text-gray-700 dark:text-gray-400">Karyawan &
                                    Periode</th>
                                <th class="py-3 px-4 font-medium text-sm text-gray-700 dark:text-gray-400">Komponen yang Direview</th>
                                <th class="py-3 px-4 font-medium text-sm text-gray-700 dark:text-gray-400 text-center">
                                    Status Request</th>
                                @can('kpi.kpi-riview.riview-skor')
                                    <th class="py-3 px-4 font-medium text-sm text-gray-700 dark:text-gray-400 text-center">Aksi
                                    </th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reviews as $kpi)
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] border-b dark:border-gray-800">
                                    <td class="py-4 px-4 align-top">
                                        <div class="text-sm font-bold text-gray-800 dark:text-white">
                                            {{ $kpi->karyawan->nama }}
                                        </div>
                                        <div class="text-[11px] text-blue-600 font-medium uppercase tracking-tight">
                                            {{ date('F Y', mktime(0, 0, 0, $kpi->bulan, 1, $kpi->tahun)) }}
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-sm align-top">
                                        @php
                                            $komponenBermasalah = $kpi->details->filter(function($d) {
                                                return ($d->skor == 0 && !$d->nilai_tetap) || $d->is_review_khusus;
                                            });
                                        @endphp
                                        <ul class="space-y-1.5">
                                            @forelse ($komponenBermasalah as $det)
                                                <li class="flex items-center gap-2">
                                                    @if ($det->is_review_khusus)
                                                        <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span>
                                                        <span class="text-gray-800 dark:text-gray-200 text-xs font-semibold">{{ $det->nama_komponen }}</span>
                                                        <span class="text-[9px] font-bold text-amber-700 bg-amber-100 dark:bg-amber-900/40 dark:text-amber-300 border border-amber-300 px-1.5 py-0.2 rounded">Khusus</span>
                                                    @else
                                                        <span class="w-2 h-2 rounded-full bg-red-500 shrink-0"></span>
                                                        <span class="text-gray-700 dark:text-gray-300 text-xs">{{ $det->nama_komponen }}</span>
                                                        <span class="text-[9px] font-bold text-red-700 bg-red-100 dark:bg-red-900/40 dark:text-red-300 border border-red-300 px-1.5 py-0.2 rounded">&lt;90%</span>
                                                    @endif
                                                </li>
                                            @empty
                                                <li class="text-xs text-gray-400 italic">Tidak ada rincian komponen</li>
                                            @endforelse
                                        </ul>
                                    </td>
                                    <td class="py-4 px-4 text-center align-middle">
                                        @php $latestReq = $kpi->reviewRequests->first(); @endphp
                                        <span
                                            class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide bg-yellow-100 text-yellow-700 border border-yellow-200">
                                            Menunggu Review
                                        </span>
                                        <p class="text-[9px] text-gray-400 mt-1 italic">Dikirim:
                                            {{ $latestReq->created_at->diffForHumans() }}</p>
                                    </td>
                                    @can('kpi.kpi-riview.riview-skor')
                                        <td class="py-4 px-4 text-center align-middle">
                                            <a href="{{ route('kpi.review.edit', $kpi->id) }}"
                                                class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition shadow-sm uppercase tracking-tighter">
                                                Review Skor
                                            </a>
                                        </td>
                                    @endcan
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tableElement = document.getElementById("table-review-kpi");
            if (tableElement && typeof simpleDatatables !== 'undefined') {
                new simpleDatatables.DataTable(tableElement, {
                    searchable: true,
                    perPage: 10,
                    labels: {
                        placeholder: "Cari...",
                        searchTitle: "Cari di dalam tabel",
                        perPage: "data per halaman",
                        noRows: "Tidak ada data ditemukan",
                        info: "Menampilkan {start} sampai {end} dari {rows} data",
                    }
                });
            }
        });
    </script>
@endsection
