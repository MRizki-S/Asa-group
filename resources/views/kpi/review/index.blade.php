@extends('layouts.app')

@section('pageActive', 'Review-KPI')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <div x-data="{ pageName: 'Review Materialitas KPI' }">
            @include('partials.breadcrumb')
        </div>

        {{-- Alert Success --}}
        @if (session('success'))
            <div class="flex p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400"
                role="alert">
                <svg class="flex-shrink-0 inline w-4 h-4 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                </svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        <div class="space-y-5 sm:space-y-6">
            <div
                class="rounded-2xl border border-gray-200 px-5 py-4 sm:px-6 sm:py-5 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

                {{-- Header --}}
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Daftar Review Kepatuhan (<
                                90%)</h3>
                                <p class="text-xs text-gray-500 mt-1 italic">* Menajer dapat melakukan penyesuaian skor
                                    untuk komponen yang tidak tercapai.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table id="table-review-kpi" class="min-w-full">
                        <thead>
                            <tr class="text-left border-b border-gray-200 dark:border-gray-800">
                                <th class="py-3 px-4 font-medium text-sm text-gray-700 dark:text-gray-400">Karyawan &
                                    Periode</th>
                                <th class="py-3 px-4 font-medium text-sm text-gray-700 dark:text-gray-400">Komponen
                                    Bermasalah</th>
                                <th class="py-3 px-4 font-medium text-sm text-gray-700 dark:text-gray-400 text-center">
                                    Status Request</th>
                                @can('kpi.kpi-riview.riview-skor')
                                    <th class="py-3 px-4 font-medium text-sm text-gray-700 dark:text-gray-400 text-center">Aksi
                                    </th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($reviews as $kpi)
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] border-b dark:border-gray-800">
                                    <td class="py-4 px-4 align-top">
                                        <div class="text-sm font-bold text-gray-800 dark:text-white">
                                            {{ $kpi->user->nama_lengkap }}
                                        </div>
                                        <div class="text-[11px] text-blue-600 font-medium uppercase tracking-tight">
                                            {{ date('F Y', mktime(0, 0, 0, $kpi->bulan, 1, $kpi->tahun)) }}
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-sm">
                                        <ul class="space-y-1">
                                            @foreach ($kpi->details->where('skor', 0) as $det)
                                                <li class="flex items-center gap-2">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                                    <span
                                                        class="text-gray-600 dark:text-gray-400 text-xs">{{ $det->nama_komponen }}</span>
                                                </li>
                                            @endforeach
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
                            @empty
                                <tr>
                                    <td colspan="4" class="py-12 text-center">
                                        <p class="text-gray-400 italic text-sm">Tidak ada penilaian yang memerlukan review.
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <script>
        $(document).ready(function() {
            const tableElement = document.getElementById("table-review-kpi");
            if (tableElement && typeof simpleDatatables !== 'undefined') {
                new simpleDatatables.DataTable(tableElement, {
                    searchable: true,
                    perPage: 10
                });
            }
        });
    </script>
@endsection
