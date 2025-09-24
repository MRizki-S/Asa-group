@extends('layouts.app')

@section('pageActive', 'akunUser')

@section('content')
    <!-- ===== Main Content Start ===== -->
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: 'akunUser' }">
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
                        Akun User
                    </h3>

                    <a href="#" data-modal-target="modal-create" data-modal-toggle="modal-create"
                        class="inline-block px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        + Tambah Akun User
                    </a>
                </div>



                <table id="table-akunUser">
                    <thead>
                        <tr>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                                Username
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                                No Hp
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                                Perumahaan
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 ">
                                Tahap
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 ">
                                Keep Unit
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($akunUser as $item)
                            <tr>
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $item->username }}</td>
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $item->no_hp }}</td>
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white text-center">
                                    {{ $item->type }}</td>
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $item->type }}</td>
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $item->type }}</td>
                                <td class="px-6 py-4 flex flex-wrap gap-2 justify-center">
                                    <form action="{{ route('kualifikasi-blok.destroy', $item->id) }}" method="POST"
                                        class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                            class="delete-btn px-3 py-1 text-sm text-white bg-red-600 rounded hover:bg-red-700">
                                            Delete
                                        </button>
                                    </form>
                                </td>

                            </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>

    </div>
    <!-- ===== Main Content End ===== -->


    {{-- modal create kualifikasi-blok --}}
    {{-- @include('Etalase.kualifikasi-blok.modal.modal-create-akunUser') --}}

    {{-- sweatalert 2 for delete data --}}
    <script>
        document.addEventListener('click', function(e) {
            if (e.target.closest('.delete-btn')) {
                const btn = e.target.closest('.delete-btn');
                const form = btn.closest('.delete-form');

                Swal.fire({
                    title: 'Yakin hapus data ini?',
                    text: "Apakah anda yakin menghapus kualifikasi blok ini? Semua data yang terkait dengan kualifikasi blok akan ikut terhapus.",
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
    </script>


    <script>
        if (document.getElementById("table-akunUser") && typeof simpleDatatables.DataTable !== 'undefined') {
            const dataTable = new simpleDatatables.DataTable("#table-akunUser", {
                searchable: true,
                sortable: false
            });
        }
    </script>
@endsection
