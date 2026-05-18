@php
    $isCompact = $compact ?? false;
    $barWidth = min((float) ($metric['percentage'] ?? 0), 100);
@endphp

<div
    class="{{ $isCompact ? 'rounded-xl border border-gray-100 p-4 dark:border-gray-800' : 'rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]' }}">
    <div class="mb-4 flex items-start justify-between gap-3">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-gray-400">
                {{ $metric['title'] }}
            </p>
            <h4 class="{{ $isCompact ? 'text-xl' : 'text-2xl' }} mt-2 font-black text-gray-800 dark:text-white/90">
                {{ $formatMetricValue($metric, 'actual') }}
            </h4>
        </div>
        <span class="rounded-full bg-blue-50 px-3 py-1 text-sm font-black text-blue-600 dark:bg-blue-500/10">
            {{ $formatPercent($metric['percentage']) }}
        </span>
    </div>

    <div class="mb-4 h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
        <div class="h-full rounded-full bg-blue-600" style="width: {{ $barWidth }}%"></div>
    </div>

    <div class="grid grid-cols-2 gap-3 text-sm">
        <div class="rounded-xl bg-gray-50 p-3 dark:bg-gray-800/50">
            <p class="text-xs font-bold uppercase text-gray-400">
                {{ ($metric['unit'] ?? '') === 'Rp' ? 'Anggaran' : 'Target' }}
            </p>
            <p class="mt-1 font-bold text-gray-700 dark:text-gray-200">
                {{ $formatMetricValue($metric, 'target') }}
            </p>
        </div>
        <div class="rounded-xl bg-gray-50 p-3 dark:bg-gray-800/50">
            <p class="text-xs font-bold uppercase text-gray-400">
                {{ $metric['actual_label'] }}
            </p>
            <p class="mt-1 font-bold text-gray-700 dark:text-gray-200">
                {{ $formatMetricValue($metric, 'actual') }}
            </p>
        </div>
    </div>
</div>