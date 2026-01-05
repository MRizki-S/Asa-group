@extends('layouts.app')

@section('pageActive', 'DaftarNotaMasuk')

@section('content')
    {{-- select 2  --}}
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">


    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb -->
        <div x-data="{ pageName: 'DaftarNotaMasuk' }">
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

        <form action="{{ route('gudang.notaBarangMasuk.store') }}" method="POST">
            @csrf

            {{-- Detail Nota --}}
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
                <div class="px-5 py-4 sm:px-6 sm:py-5">
                    <h3
                        class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 border-b border-gray-100 dark:border-gray-800">
                        Nota Barang Masuk
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <!-- Nomor Nota -->
                        <div>
                            <label for="nomor_nota" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Nomor Nota <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="nomor_nota" name="nomor_nota" readonly value="{{ $nota->nomor_nota }}"
                                class="w-full bg-gray-100 border text-gray-500 text-sm rounded-lg p-2.5
                                  dark:bg-gray-700 dark:text-gray-400 border-gray-300 cursor-not-allowed">
                        </div>

                        <!-- Tanggal Nota -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Tanggal Nota <span class="text-red-500">*</span>
                            </label>

                            <div class="relative" x-data="{
                                tampil: '{{ now()->format('d-m-Y') }}',
                                simpan: '{{ now()->format('Y-m-d') }}'
                            }">
                                <!-- Icon Kalender -->
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                                    </svg>
                                </div>

                                <!-- Input Date -->
                                <input type="date" name="tanggal_nota" x-data="{ tanggal: '{{ now()->format('d-m-Y') }}' }" x-init="flatpickr($el, {
                                    dateFormat: 'd-m-Y',
                                    defaultDate: tanggal,
                                    onChange: (selectedDates, dateStr) => { tanggal = dateStr }
                                })"
                                    placeholder="Pilih tanggal"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500
                                focus:border-blue-500 block w-full pl-10 p-2.5 dark:bg-gray-700 dark:border-gray-600
                                dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500
                                dark:focus:border-blue-500">
                                <!-- Input hidden format Y-m-d -->
                                <input type="hidden" name="tanggal_nota" x-model="simpan">
                            </div>
                        </div>

                        <!-- Supplier -->
                        <div>
                            <label for="supplier" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Supplier <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="supplier" name="supplier" readonly value="{{ $nota->supplier }}"
                                class="w-full bg-gray-100 border text-gray-500 text-sm rounded-lg p-2.5
                                  dark:bg-gray-700 dark:text-gray-400 border-gray-300 cursor-not-allowed">
                        </div>

                        <!-- Cara Bayar -->
                        <div>
                            <label for="cara_bayar" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Cara Bayar <span class="text-red-500">*</span>
                            </label>

                            <input type="text" id="cara_bayar" name="cara_bayar" readonly value="{{ $nota->cara_bayar }}"
                                class="w-full bg-gray-100 border text-gray-500 text-sm rounded-lg p-2.5
                                  dark:bg-gray-700 dark:text-gray-400 border-gray-300 cursor-not-allowed">
                        </div>


                    </div>
                </div>
            </div>
            
            {{-- Detail Nota Barang Masuk --}}
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
                <div class="px-5 py-4 sm:px-6 sm:py-5">
                    <h3
                        class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 border-b border-gray-100 dark:border-gray-800">
                        Detail Barang Masuk
                    </h3>

                    <div class="overflow-x-auto">
                        <table class="w-full border border-gray-300">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="border px-2 py-2 w-[25%]">Barang</th>
                                    <th class="border px-2 py-2">Merk</th>
                                    <th class="border px-2 py-2">Jumlah</th>
                                    <th class="border px-2 py-2">Harga Satuan</th>
                                    <th class="border px-2 py-2">Harga Total</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($nota->details as $detail)
                                    <tr>
                                        {{-- Nama Barang --}}
                                        <td class="border p-1">
                                            <input type="text" value="{{ $detail->barang->nama_barang }}"
                                                class="w-full border rounded p-1 bg-gray-50" readonly>
                                        </td>

                                        {{-- Merk --}}
                                        <td class="border p-1">
                                            <input type="text" value="{{ $detail->merk }}"
                                                class="w-full border rounded p-1 bg-gray-50" readonly>
                                        </td>

                                        {{-- Jumlah --}}
                                        <td class="border p-1 text-center">
                                            <input type="text"
                                                value="{{ rtrim(rtrim($detail->jumlah_masuk, '0'), '.') }}"
                                                class="w-20 text-center border rounded p-1 bg-gray-50" readonly>
                                        </td>

                                        {{-- Harga Satuan --}}
                                        <td class="border p-1">
                                            <input type="text"
                                                value="Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}"
                                                class="w-full border rounded p-1 bg-gray-50" readonly>
                                        </td>

                                        {{-- Harga Total --}}
                                        <td class="border p-1">
                                            <input type="text"
                                                value="Rp {{ number_format($detail->harga_total, 0, ',', '.') }}"
                                                class="w-full border rounded p-1 bg-gray-50" readonly>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-gray-500">
                                            Tidak ada detail barang
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
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
            </div>
        </form>
    </div>

@endsection
