@extends('layouts.app')

@section('pageActive', 'MasterBarang')

@section('content')
    {{-- select 2  --}}
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">

    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb -->
        <div x-data="{ pageName: 'MasterBarang' }">
            @include('partials.breadcrumb')
        </div>

        <!-- Alert Error Validasi -->
        @if ($errors->any())
            <div class="flex p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400"
                role="alert">
                <svg class="shrink-0 inline w-4 h-4 me-3 mt-[2px]" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                    viewBox="0 0 20 20">
                    <path
                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                </svg>
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

        <form action="{{ route('gudang.masterBarang.store') }}" method="POST">
            @csrf

            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
                <div class="px-5 py-4 sm:px-6 sm:py-5">
                    <h3
                        class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 border-b border-gray-100 dark:border-gray-800">
                        Barang Material
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <!-- Kode Barang -->
                        <div>
                            <label for="kode_barang" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Kode Barang <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="kode_barang" name="kode_barang" readonly value="{{ $newKodeBarang }}"
                                placeholder="BRG-XXXX"
                                class="w-full bg-gray-100 border text-gray-500 text-sm rounded-lg p-2.5
               dark:bg-gray-700 dark:text-gray-400 border-gray-300 cursor-not-allowed">

                            @error('kode_barang')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>


                        <!-- Nama Barang -->
                        <div>
                            <label for="nama_barang" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Nama Barang <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="nama_barang" name="nama_barang" required
                                value="{{ old('nama_barang') }}" placeholder="Contoh: Semen"
                                class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                            dark:bg-gray-700 dark:text-white
                            @error('nama_barang') border-red-500 @else border-gray-300 @enderror">
                            @error('nama_barang')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>


                        <!-- Satuan -->
                        <div>
                            <label for="satuan" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Satuan <span class="text-red-500">*</span>
                            </label>

                            <div class="relative">
                                <input type="text" id="satuan" name="satuan" required value="{{ old('satuan') }}"
                                    placeholder="Input satuan barang"
                                    class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5 pr-10
                   focus:ring-blue-500 focus:border-blue-500
                   dark:bg-gray-700 dark:text-white
                   @error('satuan') border-red-500 @else border-gray-300 @enderror">

                                <!-- Icon unik di kanan input -->
                                <svg class="absolute right-3 top-2.5 w-5 h-5 text-gray-400 dark:text-gray-300"
                                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 4v1m0 14v1m8-8h1M4 12H3m15.364-6.364l.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707" />
                                </svg>
                            </div>

                            @error('satuan')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex justify-end gap-2">
                <button type="button" onclick="history.back()"
                    class="px-8 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300
                       dark:text-white dark:bg-gray-700 dark:hover:bg-gray-600">
                    Kembali
                </button>
                <button type="submit"
                    class="px-8 py-2.5 text-sm font-medium text-white rounded-lg bg-blue-600 hover:bg-blue-700
                       focus:outline-none focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800">
                    Simpan
                </button>
            </div>
        </form>
    </div>
@endsection
