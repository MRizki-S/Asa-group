@extends('layouts.app')

@section('content')
<style>
    @keyframes floating {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-20px); }
        100% { transform: translateY(0px); }
    }

    .animate-float {
        animation: floating 3s ease-in-out infinite;
    }
</style>

<div class="flex flex-col items-center justify-center min-h-[70vh] p-6 text-center">

    <div class="mb-4">
        <img src="{{ asset('images/ilustration/under-development-ilustration.png') }}"
             alt="Under Development"
             class="w-full max-w-md mx-auto h-auto object-contain animate-float">
    </div>

    <div class="space-y-4">
        <h1 class="text-3xl font-bold text-gray-800 uppercase tracking-wide">
            Oops! Fitur Sedang Dikembangkan
        </h1>
        <p class="text-lg text-gray-600 max-w-lg mx-auto">
            Mohon bersabar, fitur ini akan segera hadir untuk Anda.
        </p>
    </div>

    <div class="mt-8">
        <a href="{{ url()->previous() }}"
           class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200 shadow-lg shadow-blue-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Pekerjaan
        </a>
    </div>

</div>
@endsection
