@extends('layouts.app')

@section('pageActive', 'MasterSupplier')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb -->
        <div x-data="{ pageName: 'MasterSupplier' }">
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

        <form action="{{ route('gudang.masterSupplier.update', $supplier->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
                <div class="px-5 py-4 sm:px-6 sm:py-5">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">
                        Edit Master Supplier
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Kode Supplier -->
                        <div>
                            <label for="kode_supplier" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Kode Supplier <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="kode_supplier" name="kode_supplier" required
                                value="{{ old('kode_supplier', $supplier->kode_supplier) }}" placeholder="Contoh: SPL-001"
                                class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                            dark:bg-gray-700 dark:text-white
                            @error('kode_supplier') border-red-500 @else border-gray-300 @enderror">
                            @error('kode_supplier')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Nama Supplier -->
                        <div>
                            <label for="nama_supplier" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Nama Supplier <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="nama_supplier" name="nama_supplier" required
                                value="{{ old('nama_supplier', $supplier->nama_supplier) }}" placeholder="Contoh: PT Asa Abadi"
                                class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                            dark:bg-gray-700 dark:text-white
                            @error('nama_supplier') border-red-500 @else border-gray-300 @enderror">
                            @error('nama_supplier')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Kategori Supplier -->
                        <div>
                            <label for="kategori_supplier" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Kategori Supplier
                            </label>
                            <input type="text" id="kategori_supplier" name="kategori_supplier"
                                value="{{ old('kategori_supplier', $supplier->kategori_supplier) }}" placeholder="Contoh: Bahan Bangunan, Logam"
                                class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                            dark:bg-gray-700 dark:text-white border-gray-300">
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select id="status" name="status" required
                                class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                            dark:bg-gray-700 dark:text-white border-gray-300">
                                <option value="1" {{ old('status', $supplier->status) == '1' ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ old('status', $supplier->status) == '0' ? 'selected' : '' }}>Tidak Aktif</option>
                            </select>
                        </div>

                        <!-- Telepon -->
                        <div>
                            <label for="telepon" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Nomor Telepon / HP
                            </label>
                            <input type="text" id="telepon" name="telepon"
                                value="{{ old('telepon', $supplier->telepon) }}" placeholder="Contoh: 08123456789"
                                class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                            dark:bg-gray-700 dark:text-white border-gray-300">
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Email
                            </label>
                            <input type="email" id="email" name="email"
                                value="{{ old('email', $supplier->email) }}" placeholder="Contoh: supplier@mail.com"
                                class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                            dark:bg-gray-700 dark:text-white border-gray-300">
                        </div>

                        <!-- NPWP -->
                        <div>
                            <label for="npwp" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                NPWP
                            </label>
                            <input type="text" id="npwp" name="npwp"
                                value="{{ old('npwp', $supplier->npwp) }}" placeholder="Contoh: 12.345.678.9-012.345"
                                class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                            dark:bg-gray-700 dark:text-white border-gray-300">
                        </div>

                        <!-- Rekening Bank -->
                        <div>
                            <label for="rekening_bank" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Rekening Bank
                            </label>
                            <input type="text" id="rekening_bank" name="rekening_bank"
                                value="{{ old('rekening_bank', $supplier->rekening_bank) }}" placeholder="Contoh: BCA / Mandiri / BRI"
                                class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                            dark:bg-gray-700 dark:text-white border-gray-300">
                        </div>

                        <!-- No Rekening -->
                        <div>
                            <label for="no_rekening" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Nomor Rekening
                            </label>
                            <input type="text" id="no_rekening" name="no_rekening"
                                value="{{ old('no_rekening', $supplier->no_rekening) }}" placeholder="Contoh: 1234567890"
                                class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                            dark:bg-gray-700 dark:text-white border-gray-300">
                        </div>

                        <!-- Alamat -->
                        <div class="md:col-span-2">
                            <label for="alamat" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Alamat
                            </label>
                            <textarea id="alamat" name="alamat" rows="3" placeholder="Alamat lengkap supplier..."
                                class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                            dark:bg-gray-700 dark:text-white border-gray-300">{{ old('alamat', $supplier->alamat) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex justify-end gap-2">
                <button type="button" onclick="window.location.replace('{{ route('gudang.masterSupplier.index') }}')"
                    class="px-8 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300
                       dark:text-white dark:bg-gray-700 dark:hover:bg-gray-600">
                    Kembali
                </button>
                <button type="submit"
                    class="px-8 py-2.5 text-sm font-medium text-white rounded-lg bg-blue-600 hover:bg-blue-700
                       focus:outline-none focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800">
                    Perbarui
                </button>
            </div>
        </form>
    </div>
@endsection
