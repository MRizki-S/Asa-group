@extends('layouts.app')

@section('pageActive', 'RoleHakAkses')

@section('content')
    <!-- ===== Main Content Start ===== -->
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: 'RoleHakAkses' }">
            @include('partials.breadcrumb')
        </div>
        <!-- Breadcrumb End -->

        <!-- Summary Stats -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:gap-6 mb-6">
            <!-- Total Roles -->
            <div
                class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center">
                    <div
                        class="mr-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-500">
                        <svg class="h-8 w-8" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4Zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Jabatan/Role</p>
                        <div class="flex items-baseline gap-2">
                            <h4 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $totalRoles }}</h4>
                            <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Jabatan</span>
                        </div>
                    </div>
                </div>
                <!-- Subtle background element -->
                <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-blue-50/50 dark:bg-blue-500/5"></div>
            </div>

            <!-- Total Permissions -->
            <div
                class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center">
                    <div
                        class="mr-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-purple-50 text-purple-600 dark:bg-purple-500/10 dark:text-purple-500">
                        <svg class="h-8 w-8" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24"
                            height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M12 11c.828 0 1.5-.672 1.5-1.5S12.828 8 12 8s-1.5.672-1.5 1.5.672 1.5 1.5 1.5Z" />
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Hak Akses</p>
                        <div class="flex items-baseline gap-2">
                            <h4 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $totalPermissions }}</h4>
                            <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Akses</span>
                        </div>
                    </div>
                </div>
                <!-- Subtle background element -->
                <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-purple-50/50 dark:bg-purple-500/5"></div>
            </div>
        </div>

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

        @if (session('success'))
            <div class="flex p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400"
                role="alert">
                <svg class="shrink-0 inline w-4 h-4 me-3 mt-[2px]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                    fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                </svg>
                <span class="sr-only">Success</span>
                <div>
                    <span class="font-medium">Berhasil!</span> {{ session('success') }}
                </div>
            </div>
        @endif

        <div class="space-y-5 sm:space-y-6">
            <div
                class="rounded-2xl border border-gray-200 px-5 py-4 sm:px-6 sm:py-5 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        Role & Hak Akses
                    </h3>
                    @can('superadmin.role.create')
                        <a href="{{ route('superadmin.roleHakAkses.create') }}"
                            class="inline-block px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                            + Tambah Role
                        </a>
                    @endcan
                </div>



                <table id="table-roleHakAkses">
                    <thead>
                        <tr>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                                <span class="flex items-center">
                                    Nama Role
                                    <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                    </svg>
                                </span>
                            </th>

                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                                <span class="flex items-center justify-center">
                                    Total Hak Akses
                                    <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                    </svg>
                                </span>
                            </th>

                            @canany(['superadmin.role.update', 'superadmin.role.delete'])
                                <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                                    Aksi
                                </th>
                            @endcanany
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($roles as $item)
                            <tr>
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $item->name }}
                                </td>

                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white text-center">
                                    {{ $item->permissions_count }}
                                </td>

                                @canany(['superadmin.role.update', 'superadmin.role.delete'])
                                    <td class="px-6 py-4 flex flex-wrap gap-2 justify-center items-center text-center">
                                        @can('superadmin.role.update')
                                            <a href="{{ route('superadmin.roleHakAkses.edit', $item->id) }}"
                                                class="btn-edit inline-flex items-center gap-1
                            text-xs font-medium text-yellow-700 bg-yellow-100 hover:bg-yellow-200
                            dark:bg-yellow-800 dark:text-yellow-100 dark:hover:bg-yellow-700
                            px-2.5 py-1.5 rounded-md transition-colors duration-200
                            focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-1
                            active:scale-95">
                                                Edit
                                            </a>
                                        @endcan

                                        @can('superadmin.role.delete')
                                            <form action="{{ route('superadmin.roleHakAkses.destroy', $item->id) }}"
                                                method="POST" class="delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button"
                                                    class="delete-btn px-3 py-1 text-sm text-white bg-red-600 rounded hover:bg-red-700">
                                                    Delete
                                                </button>
                                            </form>
                                        @endcan
                                    </td>
                                @endcanany
                            </tr>
                        @endforeach
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
                    text: "Apakah anda yakin menghapus Akun User & Booking Unit ini?",
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

        if (document.getElementById("table-roleHakAkses") && typeof simpleDatatables.DataTable !== 'undefined') {
            const dataTable = new simpleDatatables.DataTable("#table-roleHakAkses", {
                searchable: true,
                sortable: true,
            });
        }
    </script>
@endsection
