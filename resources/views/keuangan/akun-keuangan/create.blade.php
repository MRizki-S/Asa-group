@extends('layouts.app')

@section('pageActive', 'AkunKeuangan')

@section('content')

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">


    <!-- ===== Main Content Start ===== -->
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: 'AkunKeuangan' }">
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

        <form action="{{ route('keuangan.akunKeuangan.store') }}" method="POST">
            @csrf

            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
                <div class="px-5 py-4 sm:px-6 sm:py-5">
                    <h3
                        class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 border-b-2 border-gray-100 dark:border-gray-800">
                        Akun Keuangan
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-white">
                                Parent Akun
                            </label>



                            <select name="parent_id" id="parentSelect"
                                class="select-unit w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                                dark:bg-gray-700 dark:text-white
                                @error('parent_id') border-red-500 @else border-gray-300 @enderror">
                                <option value="">Pilih Parent</option>

                                @foreach ($akunParent as $parent)
                                    <option value="{{ $parent->id }}">
                                        {{ $parent->kode_akun }} - {{ $parent->nama_akun }}
                                    </option>
                                @endforeach
                            </select>


                            @error('parent_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror

                            <div class="flex p-3 mt-4 text-sm text-blue-800 border border-blue-100 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400 dark:border-blue-800"
                                role="alert">
                                <svg class="flex-shrink-0 inline w-4 h-4 me-3 mt-[2px]" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                                </svg>
                                <div>
                                    <span class="font-medium">Info Hirarki:</span> Biarkan tetap <strong>"Pilih
                                        Parent"</strong> jika akun ini adalah Akun Induk (Level 1).
                                </div>
                            </div>
                        </div>

                        <!-- Select Kategori Akun -->
                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-white">Kategori
                                Akun</label>
                            <select name="kategori_akun_id" required
                                class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
           dark:bg-gray-600 dark:text-white
           @error('kategori_akun_id') border-red-500 @else border-gray-300 @enderror">
                                <option value="">Pilih Kategori</option>

                                @foreach ($kategoriAkun as $kategori)
                                    <option value="{{ $kategori->id }}"
                                        {{ old('kategori_akun_id') == $kategori->id ? 'selected' : '' }}>
                                        {{ $kategori->kode }} - {{ $kategori->nama }}
                                    </option>
                                @endforeach
                            </select>

                            @error('kategori_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Input Kode Akun -->
                        <div>
                            <label for="kode_akun" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Kode Akun
                            </label>
                            <input type="text" id="kode_akun" name="kode_akun" required placeholder="Contoh: 1.1.1"
                                value="{{ old('kode_akun') }}"
                                class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                   dark:bg-gray-700 dark:text-white
                   @error('kode_akun') border-red-500 @else border-gray-300 @enderror" />
                            @error('kode_akun')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Input Nama Akun -->
                        <div>
                            <label for="nama_akun" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Nama Akun
                            </label>
                            <input type="text" id="nama_akun" name="nama_akun" required placeholder="Contoh: Kas"
                                value="{{ old('nama_akun') }}"
                                class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                   dark:bg-gray-700 dark:text-white
                   @error('nama_akun') border-red-500 @else border-gray-300 @enderror" />
                            @error('nama_akun')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Checkbox akun leaf -->
                        <div>
                            <label for="akun_leaf" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Akun Leaf
                            </label>
                            <input type="checkbox" id="akun_leaf" name="akun_leaf" value="1"
                                {{ old('akun_leaf') ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" />
                            <label for="akun_leaf" class="ml-2 text-sm font-medium text-gray-900 dark:text-white">
                                Centang jika akun ini adalah akun leaf
                            </label>
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

            {{-- button submit --}}
        </form>


    </div>
    <!-- ===== Main Content End ===== -->


    <script>
        $(document).ready(function() {
            $('#parentSelect').select2({
                placeholder: "Pilih Parent",
                theme: 'bootstrap4',
                allowClear: true,
                width: '100%'
            });
        });
    </script>
@endsection
