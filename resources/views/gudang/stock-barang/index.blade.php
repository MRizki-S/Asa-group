@extends('layouts.app')

@section('pageActive', 'StockBarang')

@section('content')
    <!-- ===== Main Content Start ===== -->
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: 'StockBarang' }">
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
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        Stock Barang
                    </h3>

                    {{-- <a href="{{ route('gudang.masterBarang.create') }}"
                        class="inline-block px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        + Tambah Master Barang
                    </a> --}}
                </div>



                <table class="w-full border-collapse border border-gray-300 text-sm">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="border px-3 py-2 w-12">No.</th>
                            <th class="border px-3 py-2 text-left">Nama Barang</th>
                            <th class="border px-3 py-2">Total Stock</th>
                            <th class="border px-3 py-2">Harga Total Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="bg-blue-50 font-bold text-gray-900 border-t-2 border-blue-200">
                            <td class="border px-3 py-2 text-center">1</td>
                            <td class="border px-3 py-2 text-blue-800">Semen</td>
                            <td class="border px-3 py-2 text-center">50</td>
                            <td class="border px-3 py-2 text-right">2.100.000</td>
                        </tr>
                        <tr>
                            <td class="border bg-gray-50"></td>
                            <td colspan="3" class="border p-0">
                                <table class="w-full text-xs bg-white">
                                    <thead class="bg-gray-100 text-gray-600">
                                        <tr>
                                            <th class="border-b px-3 py-1 text-left">No. Nota</th>
                                            <th class="border-b px-3 py-1 text-left">Merk</th>
                                            <th class="border-b px-3 py-1 text-left">Supplier</th>
                                            <th class="border-b px-3 py-1 text-center">Masuk</th>
                                            <th class="border-b px-3 py-1 text-center">Sisa</th>
                                            <th class="border-b px-3 py-1 text-right">Harga Satuan</th>
                                            <th class="border-b px-3 py-1 text-right">Harga Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="hover:bg-yellow-50 border-b border-gray-100">
                                            <td class="px-3 py-1 font-medium">nota-0001</td>
                                            <td class="px-3 py-1">Gresik</td>
                                            <td class="px-3 py-1">PT. Semen Indonesia</td>
                                            <td class="px-3 py-1 text-center">30</td>
                                            <td class="px-3 py-1 text-center">30</td>
                                            <td class="px-3 py-1 text-right">40.000</td>
                                            <td class="px-3 py-1 text-right font-semibold">1.200.000</td>
                                        </tr>
                                        <tr class="hover:bg-yellow-50 border-b border-gray-100">
                                            <td class="px-3 py-1 font-medium">nota-0002</td>
                                            <td class="px-3 py-1">3 Roda</td>
                                            <td class="px-3 py-1">CV. Bangun Jaya</td>
                                            <td class="px-3 py-1 text-center">20</td>
                                            <td class="px-3 py-1 text-center">20</td>
                                            <td class="px-3 py-1 text-right">45.000</td>
                                            <td class="px-3 py-1 text-right font-semibold">900.000</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>

                        <tr class="bg-blue-50 font-bold text-gray-900 border-t-2 border-blue-200">
                            <td class="border px-3 py-2 text-center">2</td>
                            <td class="border px-3 py-2 text-blue-800">Cat Interior</td>
                            <td class="border px-3 py-2 text-center">40</td>
                            <td class="border px-3 py-2 text-right">6.000.000</td>
                        </tr>
                        <tr>
                            <td class="border bg-gray-50"></td>
                            <td colspan="3" class="border p-0">
                                <table class="w-full text-xs bg-white">
                                    <thead class="bg-gray-100 text-gray-600">
                                        <tr>
                                            <th class="border-b px-3 py-1 text-left">No. Nota</th>
                                            <th class="border-b px-3 py-1 text-left">Merk</th>
                                            <th class="border-b px-3 py-1 text-left">Supplier</th>
                                            <th class="border-b px-3 py-1 text-center">Masuk</th>
                                            <th class="border-b px-3 py-1 text-center">Sisa</th>
                                            <th class="border-b px-3 py-1 text-right">Harga Satuan</th>
                                            <th class="border-b px-3 py-1 text-right">Harga Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="hover:bg-yellow-50">
                                            <td class="px-3 py-1 font-medium">nota-0003</td>
                                            <td class="px-3 py-1">Avitex</td>
                                            <td class="px-3 py-1">Toko Cat Makmur</td>
                                            <td class="px-3 py-1 text-center">40</td>
                                            <td class="px-3 py-1 text-center">40</td>
                                            <td class="px-3 py-1 text-right">150.000</td>
                                            <td class="px-3 py-1 text-right font-semibold">6.000.000</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>




            </div>
        </div>

    </div>
    <!-- ===== Main Content End ===== -->

    {{-- sweatalert 2 for delete data --}}
    {{-- <script>
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

        if (document.getElementById("table-masterBarang") && typeof simpleDatatables.DataTable !== 'undefined') {
            const dataTable = new simpleDatatables.DataTable("#table-masterBarang", {
                searchable: true,
                sortable: true,
            });
        }
    </script> --}}
@endsection
