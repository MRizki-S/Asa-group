@extends('layouts.app')

@section('pageActive', 'DaftarNotaMasuk')

@section('content')
    <!-- ===== Main Content Start ===== -->
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: 'DaftarNotaMasuk' }">
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

        <div class="space-y-5 sm:space-y-6">
            <div
                class="rounded-2xl border border-gray-200 px-5 py-4 sm:px-6 sm:py-5 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                    {{-- Judul --}}
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        Daftar Nota Barang Masuk
                    </h3>

                    {{-- Filter Tanggal --}}
                    <form method="GET" action="{{ route('gudang.notaBarangMasuk.index') }}"
                        class="flex items-center gap-2">

                        <div class="relative" x-data="{ tanggal: '{{ request('tanggal', now()->toDateString()) }}' }">

                            <!-- Icon Kalender -->
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"> <svg
                                    class="w-5 h-5 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                                </svg>
                            </div>

                            <!-- INPUT (Flatpickr handle format) -->
                            <input type="text" name="tanggal" x-ref="tanggal" x-init="flatpickr($refs.tanggal, {
                                defaultDate: tanggal,
                                dateFormat: 'Y-m-d', // format untuk backend
                                altInput: true,
                                altFormat: 'd-m-Y', // format tampil UI
                            })"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg
            focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5
            dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <button type="submit"
                            class="inline-flex items-center gap-1
        rounded-md bg-blue-600 px-3 py-1.5 text-sm font-medium text-white
        hover:bg-blue-700 transition">
                            Terapkan
                        </button>
                    </form>

                </div>




                <table id="table-daftarNotaMasuk">
                    <thead>
                        <tr>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                                <span class="flex items-center">
                                    Nomor Nota
                                    <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                    </svg>
                                </span>
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                                <span class="flex items-center">
                                    Tanggal Masuk
                                    <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                    </svg>
                                </span>
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                                Supplier
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                                Cara Bayar
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($notas as $nota)
                            <tr>
                                {{-- Nomor Nota --}}
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $nota->nomor_nota }}
                                </td>

                                {{-- Tanggal Masuk --}}
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ \Carbon\Carbon::parse($nota->tanggal_nota)->format('d-M-Y') }}
                                </td>

                                {{-- Supplier --}}
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $nota->supplier ?? '-' }}
                                </td>

                                {{-- Cara Bayar --}}
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    <span
                                        class="px-2 py-1 rounded-full text-xs font-semibold
                                        {{ $nota->cara_bayar === 'cash' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                        {{ strtoupper($nota->cara_bayar) }}
                                    </span>

                                </td>

                                {{-- Aksi --}}
                                <td class="px-6 py-4 flex flex-wrap gap-2 justify-center">

                                    {{-- SHOW / DETAIL --}}
                                    <a href="{{ route('gudang.notaBarangMasuk.show', $nota->nomor_nota) }}"
                                        class="inline-flex items-center gap-1
                                        text-xs font-medium text-blue-700 bg-blue-100 hover:bg-blue-200
                                        dark:bg-blue-800 dark:text-blue-100 dark:hover:bg-blue-700
                                        px-2.5 py-1.5 rounded-md transition-colors duration-200
                                        focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1
                                        active:scale-95">
                                        Detail
                                    </a>

                                    {{-- EDIT --}}
                                    {{-- <a href=""
                                        class="inline-flex items-center gap-1
                                        text-xs font-medium text-yellow-700 bg-yellow-100 hover:bg-yellow-200
                                        dark:bg-yellow-800 dark:text-yellow-100 dark:hover:bg-yellow-700
                                        px-2.5 py-1.5 rounded-md transition-colors duration-200
                                        focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-1
                                        active:scale-95">
                                        Edit
                                    </a> --}}

                                    {{-- DELETE --}}
                                    <form action="{{ route('gudang.notaBarangMasuk.destroy', $nota->nomor_nota) }}" method="POST" class="delete-form">
                                        @csrf
                                        @method('DELETE')

                                        <button type="button"
                                            class="delete-btn inline-flex items-center gap-1
                                            text-xs font-medium text-white bg-red-600 hover:bg-red-700
                                            px-2.5 py-1.5 rounded-md transition-colors duration-200
                                            focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-1
                                            active:scale-95">
                                            Delete
                                        </button>
                                    </form>

                                </td>

                            </tr>
                        @empty

                        @endforelse
                    </tbody>
                </table>

            </div>
        </div>

    </div>
    <!-- ===== Main Content End ===== -->

    {{-- sweatalert 2 for delete data --}}
    <script>
        document.addEventListener('click', function(e) {
            if (e.target.closest('.delete-btn')) {
                const btn = e.target.closest('.delete-btn');
                const form = btn.closest('.delete-form');

                Swal.fire({
                    title: 'Yakin hapus data ini?',
                    text: "Data yang sudah dihapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            }
        });

        if (document.getElementById("table-daftarNotaMasuk") && typeof simpleDatatables.DataTable !== 'undefined') {
            const dataTable = new simpleDatatables.DataTable("#table-daftarNotaMasuk", {
                searchable: true,
                sortable: true,
                perPageSelect: [5, 10, 20, 50],
            });
        }
    </script>
@endsection
