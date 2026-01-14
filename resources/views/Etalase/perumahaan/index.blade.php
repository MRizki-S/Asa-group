@extends('layouts.app')

@section('pageActive', 'Perumahaan')

@section('content')
    <!-- ===== Main Content Start ===== -->
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: 'Perumahaan ' }">
            @include('partials.breadcrumb', ['breadcrumbs' => $breadcrumbs])
        </div>
        <!-- Breadcrumb End -->

        <div class="rounded-2xl grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Card perumahaan --}}
            @foreach ($perumahaan as $item)
                @php
                    $user = auth()->user();

                    $canAccess = $user->is_global || $user->perumahaan_id === $item->id;
                @endphp

                @if ($canAccess)
                    {{-- AKTIF (BISA DIKLIK) --}}
                    <a href="{{ route('perumahaan.show', $item) }}"
                        class="block border rounded-lg shadow-lg p-6 hover:shadow-xl transition
                    bg-white border-gray-200 hover:bg-gray-50 border-l-6
                    {{ $item->slug === 'asa-dreamland'
                        ? 'border-l-blue-500 dark:border-l-blue-700 text-blue-500 dark:text-blue-700'
                        : 'border-l-lime-500 dark:border-l-lime-700 text-lime-500 dark:text-lime-700' }}
                    dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700">
                    @else
                    {{-- DISABLED (TIDAK BISA DIKLIK) --}}
                    <div
                        class="block border rounded-lg shadow p-6 cursor-not-allowed opacity-60
                        bg-gray-100 border-gray-300 border-l-6
                        {{ $item->slug === 'asa-dreamland' ? 'border-l-blue-300 text-blue-300' : 'border-l-lime-300 text-lime-300' }}
                        dark:bg-gray-900 dark:border-gray-700">
                @endif

                <h2 class="text-xl font-semibold mb-2">
                    {{ $item->nama_perumahaan }}
                </h2>

                <p class="text-gray-600 dark:text-gray-400">
                    {{ $item->alamat }}
                </p>

                @unless ($canAccess)
                    <p class="mt-2 text-sm text-red-500">
                        🔒 Anda tidak memiliki akses ke perumahaan ini
                    </p>
                @endunless

                @if ($canAccess)
                    </a>
                @else
            </div>
            @endif
        @endforeach


    </div>

    </div>
    <!-- ===== Main Content End ===== -->
@endsection
