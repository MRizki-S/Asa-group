<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
        {{ last($breadcrumbs)['label'] ?? '' }}
    </h2>

    <nav>
        <ol class="flex items-center gap-1.5">
            @foreach ($breadcrumbs as $i => $crumb)
                <li class="flex items-center">
                    @if ($crumb['url'] && $i !== array_key_last($breadcrumbs))
                        <a href="{{ $crumb['url'] }}"
                            class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400 hover:underline hover:text-blue-400">
                            {{ $crumb['label'] }}
                        </a>
                        <svg class="mx-1 stroke-gray-800 dark:stroke-white" width="17" height="16"
                            viewBox="0 0 17 16" fill="none">
                            <path d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366" stroke-width="1.2"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    @else
                        <span class="text-sm text-gray-800 dark:text-white/90">
                            {{ $crumb['label'] }}
                        </span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
</div>
