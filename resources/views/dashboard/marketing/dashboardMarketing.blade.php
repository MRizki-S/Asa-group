@extends('layouts.app')

@section('pageActive', 'Dashboard-Marketing')

@section('content')
    @php
        $formatNumber = fn($value) => number_format((float) $value, 0, ',', '.');
        $formatPercent = fn($value) => number_format((float) $value, 1, ',', '.') . '%';
        $formatMetricValue = function ($metric, $key) use ($formatNumber) {
            $value = $metric[$key] ?? 0;
            return ($metric['unit'] ?? '') === 'Rp'
                ? 'Rp ' . $formatNumber($value)
                : $formatNumber($value) . ' Unit';
        };
    @endphp

    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">
        <!-- Breadcrumb -->
        <div x-data="{ pageName: 'Dashboard Marketing' }">
            @include('partials.breadcrumb')
        </div>

        <!-- UBS Info Header -->
        <div class="mb-6 flex items-center justify-between rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03] shadow-sm">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-600 text-white">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xs font-bold uppercase tracking-widest text-gray-400">UBS Terpilih</h2>
                    <h1 class="text-lg font-bold text-gray-800 dark:text-white/90">
                        {{ $perumahaan->nama_perumahaan ?? 'Pilih Perumahaan' }}
                    </h1>
                </div>
            </div>
        </div>

        <!-- Filter Form dengan Auto-Submit (Tanpa Button & Switcher Lokal) -->
        <form method="GET"
            class="mb-6 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-gray-500">Tahun</label>
                    <select name="tahun" onchange="this.form.submit()"
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 p-2.5 text-sm font-medium text-gray-700 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        @foreach ($years as $year)
                            <option value="{{ $year }}" @selected($selectedYear == $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-gray-500">Kuartal</label>
                    <select name="quarter" onchange="this.form.submit()"
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 p-2.5 text-sm font-medium text-gray-700 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        @foreach ($quarters as $q)
                            <option value="{{ $q['value'] }}" @selected($selectedQuarter == $q['value'])>Q{{ $q['value'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>

        @if (! $perumahaan)
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm font-medium text-amber-800 dark:border-amber-900/60 dark:bg-amber-900/20 dark:text-amber-200">
                Silakan pilih perumahaan terlebih dahulu agar dashboard marketing dapat menampilkan data target,
                penjualan, anggaran, dan realisasi KPR.
            </div>
        @else

            {{-- ═══ SECTION: Target & Anggaran Quarter ═══ --}}
            <div class="mb-6 grid grid-cols-1 gap-5 lg:grid-cols-2">
                @foreach ($metrics as $metric)
                    @include('dashboard.marketing._partials.metric-card', ['metric' => $metric])
                @endforeach
            </div>

            {{-- ═══ SECTION: Target Bulanan ═══ --}}
            <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-gray-400">Target Bulanan</p>
                        <h3 class="mt-1 text-lg font-bold text-gray-800 dark:text-white/90">
                            Target dan Penjualan per Bulan Q{{ $selectedQuarter }}
                        </h3>
                    </div>
                    {{-- $quarterMonthLabel sudah dihitung di controller --}}
                    <div class="text-sm font-semibold text-gray-500 dark:text-gray-400">
                        {{ $quarterMonthLabel }}
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    @foreach ($targetBulananMetrics as $metric)
                        @include('dashboard.marketing._partials.metric-card', ['metric' => $metric, 'compact' => true])
                    @endforeach
                </div>
            </div>

            {{-- ═══ SECTION: Kinerja KPR ═══ --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-gray-400">Kinerja KPR</p>
                        <h3 class="mt-1 text-lg font-bold text-gray-800 dark:text-white/90">
                            Penjualan KPR ACC vs Realisasi
                        </h3>
                    </div>
                    <div class="text-sm font-semibold text-gray-500 dark:text-gray-400">
                        {{ $selectedYear }} / Q{{ $selectedQuarter }}
                    </div>
                </div>

                <div class="mb-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                    @foreach ($kprMetrics as $metric)
                        @include('dashboard.marketing._partials.metric-card', ['metric' => $metric])
                    @endforeach
                </div>

                <div class="border-t border-gray-100 pt-5 dark:border-gray-800">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <h4 class="text-sm font-bold uppercase tracking-widest text-gray-500">
                            Realisasi KPR Bulanan Q{{ $selectedQuarter }}
                        </h4>
                        <span class="text-sm font-semibold text-gray-500">{{ $quarterMonthLabel }}</span>
                    </div>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                        @foreach ($kprBulananMetrics as $metric)
                            @include('dashboard.marketing._partials.metric-card', ['metric' => $metric, 'compact' => true])
                        @endforeach
                    </div>
                </div>
            </div>

        @endif
    </div>
@endsection
