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
                <a href="{{ route('perumahaan.show', $item) }}"
                    class="block border rounded-lg shadow-lg p-6 hover:shadow-xl transition
                    bg-white border-gray-200 hover:bg-gray-50 border-l-6
                    {{ $item->slug === 'asa-dreamland' ? 'border-l-blue-500 dark:border-l-blue-700' : 'border-l-yellow-500 dark:border-l-yellow-700' }}
                  dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700">
                    <h2
                        class="text-xl font-semibold mb-2
                        {{ $item->slug === 'asa-dreamland' ? 'text-blue-500 dark:text-blue-700' : 'text-yellow-500 dark:text-yellow-700' }}">
                        {{ $item->nama_perumahaan }}
                    </h2>
                    <p class="text-gray-600 dark:text-gray-300">
                        {{ $item->alamat }}
                    </p>
                </a>
            @endforeach

        </div>

    </div>
    <!-- ===== Main Content End ===== -->
@endsection
