@extends('layouts.app')

@section('pageActive', 'MasterSupplier')

@section('content')
    <!-- ===== Main Content Start ===== -->
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: 'MasterSupplier' }">
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

        {{-- Alert Success --}}
        @if (session('success'))
            <div class="flex p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400"
                role="alert">
                <svg class="shrink-0 inline w-4 h-4 me-3 mt-[2px]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                    fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 1 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                </svg>
                <div>
                    <span class="font-medium">Berhasil:</span> {{ session('success') }}
                </div>
            </div>
        @endif

        <div class="space-y-5 sm:space-y-6">
            <div
                class="rounded-2xl border border-gray-200 px-5 py-4 sm:px-6 sm:py-5 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        Master Supplier
                    </h3>

                    @can('gudang.master-gudang.master-supplier.create')
                    <a href="{{ route('gudang.masterSupplier.create') }}"
                        class="inline-block px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        + Tambah    
                    </a>
                    @endcan
                </div>

                <table id="table-masterSupplier">
                    <thead>
                        <tr>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                                <span class="flex items-center">
                                    Kode
                                    <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                    </svg>
                                </span>
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                                <span class="flex items-center">
                                    Nama Supplier
                                    <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                    </svg>
                                </span>
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                                <span class="flex items-center">
                                    Kategori
                                    <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                    </svg>
                                </span>
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                                No. Hp / Email
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                                Status
                            </th>
                            @canany(['gudang.master-gudang.master-supplier.edit', 'gudang.master-gudang.master-supplier.delete'])
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                                Aksi
                            </th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($masterSupplier as $item)
                            <tr>
                                {{-- Kode --}}
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $item->kode_supplier }}
                                </td>

                                {{-- Nama --}}
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $item->nama_supplier }}
                                </td>

                                {{-- Kategori --}}
                                <td class="text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                    {{ $item->kategori_supplier ?? '-' }}
                                </td>

                                {{-- No Hp / Email --}}
                                <td class="text-center font-medium text-gray-900 dark:text-white">
                                    <div class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                                        {{ $item->telepon ?? '-' }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $item->email ?? '-' }}
                                    </div>
                                </td>

                                {{-- Status --}}
                                <td class="text-center">
                                    @if ($item->status == 1)
                                        <span class="inline-flex items-center gap-1.5 py-1 px-2.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-500">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 py-1 px-2.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-500">
                                            Tidak Aktif
                                        </span>
                                    @endif
                                </td>

                                {{-- Aksi --}}
                                @canany(['gudang.master-gudang.master-supplier.edit', 'gudang.master-gudang.master-supplier.delete'])
                                <td class="px-6 py-4 flex flex-wrap gap-2 justify-center">
                                    @can('gudang.master-gudang.master-supplier.edit')
                                    <a href="{{ route('gudang.masterSupplier.edit', $item->id) }}"
                                        class="inline-flex items-center gap-1
                        text-xs font-medium text-yellow-700 bg-yellow-100 hover:bg-yellow-200
                        dark:bg-yellow-800 dark:text-yellow-100 dark:hover:bg-yellow-700
                        px-2.5 py-1.5 rounded-md transition-colors duration-200
                        focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-1
                        active:scale-95">
                                        Edit
                                    </a>
                                    @endcan
 
                                    @can('gudang.master-gudang.master-supplier.delete')
                                    <form action="{{ route('gudang.masterSupplier.destroy', $item->id) }}"
                                        method="POST" class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                            class="delete-btn px-3 py-1.5 text-xs text-white bg-red-600 rounded-md hover:bg-red-700 transition-colors duration-200">
                                            Delete
                                        </button>
                                    </form>
                                    @endcan
                                </td>
                                @endcanany
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

        if (document.getElementById("table-masterSupplier") && typeof simpleDatatables.DataTable !== 'undefined') {
            const dataTable = new simpleDatatables.DataTable("#table-masterSupplier", {
                searchable: true,
                sortable: true,
                perPageSelect: [10, 25, 50, 100]
            });
        }
    </script>
@endsection
