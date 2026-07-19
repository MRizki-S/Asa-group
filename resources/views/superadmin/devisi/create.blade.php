@extends('layouts.app')

@section('pageActive', 'devisi')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb -->
        <div x-data="{ pageName: 'devisi' }">
            @include('partials.breadcrumb')
        </div>

        <!-- Alert Error -->
        @if ($errors->any())
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal menyimpan data',
                        html: `
                            <ul class="text-left list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        `,
                        showConfirmButton: true
                    });
                });
            </script>
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white/90">
                    Tambah Devisi
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Silakan masukkan nama devisi baru untuk ABM GROUP.</p>
            </div>

            <form action="{{ route('superadmin.devisi.store') }}" method="POST" class="p-6">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6 mb-6">
                    <!-- Nama Devisi -->
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700 dark:text-white">
                            Nama Devisi <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v5m-4 0h4" />
                                </svg>
                            </span>
                            <input type="text" name="nama_devisi" required value="{{ old('nama_devisi') }}" 
                                placeholder="Masukkan nama devisi (Contoh: Marketing)"
                                class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block pl-10 p-2.5 dark:bg-gray-800 dark:border-gray-700 dark:text-white transition-all">
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-3 border-t border-gray-100 dark:border-gray-800 pt-5">
                    <a href="{{ route('superadmin.devisi.index') }}"
                        class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 dark:text-white dark:bg-gray-800 dark:hover:bg-gray-700 transition">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 shadow-md transition">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
