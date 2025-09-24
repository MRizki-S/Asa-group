@extends('layouts.app')

@section('pageActive', 'Perumahaan') 

@section('content')
    <!-- ===== Main Content Start ===== -->
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: 'Perumahaan ' }">
            @include('partials.breadcrumb')
        </div>
        <!-- Breadcrumb End -->

        {{-- Alert Error Validasi --}}
        @if ($errors->any())
            <div class="flex p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400"
                role="alert">
                <svg class="shrink-0 inline w-4 h-4 me-3 mt-[2px]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                    fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                </svg>
                <span class="sr-only">Danger</span>
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


        <form action="{{ route('tahap.store', ['perumahaan' => $perumahaan->slug]) }}" method="POST">
            @csrf

            {{-- Tahap --}}
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
                <div class="px-5 py-4 sm:px-6 sm:py-5">
                    <h3
                        class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 border-b-2 border-gray-100 dark:border-gray-800">
                        Tahap
                    </h3>

                    {{-- {{$perumahaan}} --}}
                    <div class="flex flex-col md:flex-row md:space-x-4 space-y-4 md:space-y-0">
                        <!-- Select -->
                        <div class="flex-1">
                            <label for="countries" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Perumahaan
                            </label>
                            <select name="perumahaan_id"
                                class="pointer-events-none bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="{{ $perumahaan->id }}" selected>{{ $perumahaan->nama_perumahaan }}</option>
                            </select>

                        </div>

                        <!-- Input -->
                        <div class="flex-1">
                            <label for="nama_tahap" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Nama Tahap
                            </label>
                            <input type="text" id="nama_tahap" name="nama_tahap" placeholder="Masukan nama tahap"
                                value="{{ old('nama_tahap') }}" required
                                class="bg-gray-50 border text-gray-900 text-sm rounded-lg block w-full p-2.5
                            focus:ring-blue-500 focus:border-blue-500
                            dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500
                            @error('nama_tahap') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror" />
                            <!-- Pesan error -->
                            @error('nama_tahap')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>

                </div>

                <!-- Tombol Submit & Kembali -->
                <div class="flex justify-end px-6 pb-6 gap-2">
                    <!-- Tombol Kembali -->
                    <button type="button" onclick="history.back()"
                        class="px-10 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-300 dark:text-white dark:bg-gray-700 dark:hover:bg-gray-600 dark:focus:ring-gray-600">
                        Kembali
                    </button>

                    <!-- Tombol Simpan -->
                    <button type="submit"
                        class="px-10 py-2.5 text-sm font-medium text-white rounded-lg bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800">
                        Simpan
                    </button>
                </div>
            </div>

        </form>


    </div>
    <!-- ===== Main Content End ===== -->


    <script>
        $(document).ready(function() {
            $('.select2_types').select2({
                placeholder: 'Pilih Tipe Unit',
                allowClear: true,
                width: '100%',
            });
        });
    </script>
@endsection
