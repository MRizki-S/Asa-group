@extends('layouts.app')

@section('pageActive', 'karyawan')

@section('content')
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">

    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb -->
        <div x-data="{ pageName: 'karyawan' }">
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
                    Edit Karyawan
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Silakan perbarui data karyawan ABM GROUP.</p>
            </div>

            <form action="{{ route('superadmin.karyawan.update', $karyawan->id) }}" method="POST" class="p-6">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                    
                    <!-- Nama Karyawan -->
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700 dark:text-white">
                            Nama Karyawan <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </span>
                            <input type="text" name="nama" required value="{{ old('nama', $karyawan->nama) }}" 
                                placeholder="Masukkan nama sesuai KTP"
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
                            @php
                                $noHpClean = $karyawan->no_hp;
                                if (str_starts_with($noHpClean, '62')) {
                                    $noHpClean = substr($noHpClean, 2);
                                }
                            @endphp
                            <input type="number" name="no_hp" required value="{{ old('no_hp', $noHpClean) }}" placeholder="8123xxxx"
                                class="rounded-none rounded-r-xl bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5 dark:bg-gray-800 dark:border-gray-700 dark:text-white transition-all">
                        </div>
                        <p class="mt-1.5 text-[10px] text-gray-500 italic font-medium">*Masukkan nomor tanpa angka 0 di depan (Contoh: 812xxxx)</p>
                    </div>

                    <!-- Role / Jabatan -->
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700 dark:text-white">
                            Jabatan / Hak Akses <span class="text-red-500">*</span>
                        </label>
                        <select name="role_id" id="select-role" required class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-white">
                            <option value="">-- Pilih Jabatan --</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role_id', $karyawan->role_id) == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1.5 text-[10px] text-gray-400 italic font-medium">*Ketik untuk mencari jabatan dengan cepat.</p>
                    </div>

                    <!-- UBS / HUB -->
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700 dark:text-white">
                            Unit Bisnis (UBS) / HUB <span class="text-red-500">*</span>
                        </label>
                        <select name="ubs_id" required class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-blue-500 block p-2.5 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                            <option value="">-- Pilih Unit Bisnis --</option>
                            <option value="HUB" {{ old('ubs_id', $karyawan->ubs_id ? '' : 'HUB') == 'HUB' ? 'selected' : '' }}>HUB (PUSAT)</option>
                            @foreach ($ubs as $u)
                                <option value="{{ $u->id }}" 
                                    {{ old('ubs_id', $karyawan->ubs_id) == $u->id ? 'selected' : '' }}>
                                    {{ $u->nama_ubs }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Akun User (Optional) -->
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700 dark:text-white">
                            Kaitkan dengan Akun User <span class="text-xs text-gray-400 font-normal">(Opsional)</span>
                        </label>
                        <select name="user_ids[]" id="select-user" class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-white" multiple="multiple">
                            @foreach ($users as $user)
                                @php
                                    $isSelected = is_array(old('user_ids')) 
                                        ? in_array($user->id, old('user_ids')) 
                                        : ($user->karyawan_id == $karyawan->id);
                                @endphp
                                <option value="{{ $user->id }}" {{ $isSelected ? 'selected' : '' }}>
                                    {{ $user->username }} ({{ $user->nama_lengkap }})
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1.5 text-[10px] text-gray-400 italic font-medium">*Daftar user di atas adalah user login bertipe karyawan yang belum dikaitkan atau sedang dikaitkan dengan karyawan ini (Bisa memilih lebih dari satu akun).</p>
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3 border-t pt-6 dark:border-gray-800">
                    <button type="button" onclick="history.back()"
                        class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-10 py-2.5 text-sm font-bold text-white bg-blue-600 rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-200 dark:shadow-none transition-all">
                        Perbarui Karyawan
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

            $('#select-user').select2({
                placeholder: "-- Pilih Akun User --",
                allowClear: true,
                theme: 'bootstrap4',
                width: '100%'
            });
        });
    </script>
@endsection
