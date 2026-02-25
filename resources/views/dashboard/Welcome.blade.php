@extends('layouts.app')

@section('content')
  <div class="grid grid-cols-12 gap-4 md:gap-6">
    <div class="col-span-12">
      <div class="p-4 md:p-6 bg-white dark:bg-gray-800 rounded-lg shadow-md">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Selamat Datang <span class="text-blue-700 underline">{{ Auth::user()->nama_lengkap  }}</span></h1>
        <h3>Anda Login dengan Role: <span  class="bg-success-soft text-fg-success-strong font-medium px-1.5 py-0.5 rounded-full">{{ Auth::user()->roles->first()->name }}</span></h3>
      </div>
    </div>
  </div>
@endsection
