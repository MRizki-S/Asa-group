@extends('layouts.app')

@section('pageActive', 'akunKaryawan')

@section('content')
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">

    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-data="{ showPassword: false }">

        <!-- Breadcrumb -->
        <div x-data="{ pageName: 'akunKaryawan' }">
            @include('partials.breadcrumb')
        </div>

        <!-- Alert Error -->
        @if (session('error'))
            <div class="flex p-4 mb-6 text-sm text-red-800 rounded-2xl bg-red-50 dark:bg-gray-800 dark:text-red-400 border border-red-100 dark:border-red-900">
                <span class="font-bold mr-2">Error!</span> {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="flex p-4 mb-6 text-sm text-red-800 rounded-2xl bg-red-50 dark:bg-gray-800 dark:text-red-400 border border-red-100 dark:border-red-900"
                role="alert">
                <div>
                    <span class="font-bold">Gagal menyimpan data:</span>
                    <ul class="mt-1.5 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white/90">
                    Tambah Akun Karyawan
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Silakan lengkapi data karyawan kelompok ASA GROUP.</p>
            </div>

            <form action="{{ route('superadmin.akunKaryawan.store') }}" method="POST" class="p-6">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                    
                    <!-- Nama Lengkap -->
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700 dark:text-white">
                            Nama Lengkap Karyawan <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </span>
                            <input type="text" name="nama_lengkap" required value="{{ old('nama_lengkap') }}" 
                                placeholder="Masukkan nama sesuai KTP"
                                class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block pl-10 p-2.5 dark:bg-gray-800 dark:border-gray-700 dark:text-white transition-all">
                        </div>
                    </div>

                    <!-- Username -->
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700 dark:text-white">
                            Username <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                                </svg>
                            </span>
                            <input type="text" name="username" required value="{{ old('username') }}" 
                                placeholder="Contoh: budi_abm"
                                class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block pl-10 p-2.5 dark:bg-gray-800 dark:border-gray-700 dark:text-white transition-all">
                        </div>
                    </div>

                    <!-- No HP -->
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700 dark:text-white">
                            Nomor HP/WhatsApp <span class="text-red-500">*</span>
                        </label>
                        <div class="flex">
                            <span class="inline-flex items-center px-3 text-sm font-bold text-gray-600 bg-gray-100 border border-r-0 border-gray-300 rounded-l-xl dark:bg-gray-700 dark:text-gray-400 dark:border-gray-600">
                                +62
                            </span>
                            <input type="number" name="no_hp" required value="{{ old('no_hp') }}" placeholder="8123xxxx"
                                class="rounded-none rounded-r-xl bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5 dark:bg-gray-800 dark:border-gray-700 dark:text-white transition-all">
                        </div>
                        <p class="mt-1.5 text-[10px] text-gray-500 italic font-medium">*Masukkan nomor tanpa angka 0 di depan (Contoh: 812xxxx)</p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700 dark:text-white">
                            Password Login <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </span>
                            <input :type="showPassword ? 'text' : 'password'" name="password" required placeholder="••••••••"
                                class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block pl-10 p-2.5 dark:bg-gray-800 dark:border-gray-700 dark:text-white transition-all">
                            <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-blue-600 transition-colors">
                                <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.046m2.458-2.588A9.958 9.958 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.059 10.059 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.892 7.892L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- UBS / HUB -->
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700 dark:text-white">
                            Unit Bisnis (UBS) / HUB <span class="text-red-500">*</span>
                        </label>
                        <select name="perumahaan_id" required class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 block p-2.5 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                            <option value="">-- Pilih Unit Bisnis --</option>
                            <option value="HUB" {{ old('perumahaan_id') == 'HUB' ? 'selected' : '' }}>HUB (PUSAT)</option>
                            @foreach ($ubs as $u)
                                @php
                                    $isMangoon = str_contains(strtolower($u->nama_ubs), 'mangoon');
                                @endphp
                                <option value="{{ $u->id }}" 
                                    {{ $isMangoon ? 'disabled' : '' }}
                                    {{ old('perumahaan_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->nama_ubs }} {{ $isMangoon ? '(Belum Siap)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Role / Jabatan -->
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700 dark:text-white">
                            Hak Akses / Jabatan <span class="text-red-500">*</span>
                        </label>
                        <select name="role" id="select-role" required class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
               dark:bg-gray-700 dark:text-white">
                            <option value="">-- Pilih Jabatan --</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1.5 text-[10px] text-gray-400 italic font-medium">*Ketik untuk mencari jabatan dengan cepat.</p>
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3 border-t pt-6 dark:border-gray-800">
                    <button type="button" onclick="history.back()"
                        class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-10 py-2.5 text-sm font-bold text-white bg-blue-600 rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-200 dark:shadow-none transition-all">
                        Simpan Akun
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Select2 JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#select-role').select2({
                placeholder: "-- Pilih Jabatan --",
                allowClear: true,
                theme: 'bootstrap4', 
                width: '100%'
            });
        });
    </script>
@endsection
