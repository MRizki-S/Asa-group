@extends('layouts.app')

@section('pageActive', 'AkunUser')

@section('content')
    <!-- ===== Main Content Start ===== -->
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: 'AkunUser' }">
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

                    @can('marketing.customer.create')
                        <a href="{{ route('marketing.akunUser.create') }}"
                            class="inline-block px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                            + Tambah Akun User - Booking
                        </a>
                    @endcan
                </div>



                <table id="table-akunUser">
                    <thead>
                        <tr>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                                <span class="flex items-center">
                                    Username
                                    <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                    </svg>
                                </span>
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                                No Hp
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                                <span class="flex items-center">
                                    Sales Terkait
                                    <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                    </svg>
                                </span>
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                                <span class="flex items-center">
                                    Perumahaan
                                    <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                    </svg>
                                </span>
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 ">
                                Tahap
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 ">
                                Keep Unit
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 ">
                                <span class="flex items-center">
                                    Tanggal Booking
                                    <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                    </svg>
                                </span>
                            </th>
                            @canany(['marketing.customer.update', 'marketing.customer.delete'])
                                <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                                    Aksi
                                </th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($akunUser as $item)
                            <tr>
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $item->username }}
                                </td>
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $item->no_hp }}
                                </td>
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $item->booking?->sales?->username ?? '-' }}
                                </td>
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white text-center">
                                    @php
                                        $perum = $item->booking?->unit?->perumahaan?->nama_perumahaan;
                                        $badgeClass = 'bg-gray-400'; // default
                                        if ($perum === 'Asa Dreamland') {
                                            $badgeClass = 'bg-green-500';
                                        } elseif ($perum === 'Lembah Hijau Residence') {
                                            $badgeClass = 'bg-blue-500';
                                        }
                                    @endphp

                                    @if ($perum)
                                        <span
                                            class="px-2 py-1 text-xs font-semibold text-white rounded {{ $badgeClass }}">
                                            {{ $perum }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>

                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $item->booking?->unit?->tahap?->nama_tahap ?? '-' }}
                                </td>
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $item->booking?->unit?->blok?->nama_blok ?? '' }}
                                    ({{ $item->booking?->unit?->nama_unit ?? '-' }})
                                </td>
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $item->booking->tanggal_booking->format('d M Y') }}
                                </td>

                                @canany(['marketing.customer.update', 'marketing.customer.delete'])
                                    <td class="px-6 py-4 flex flex-wrap gap-2 justfy-ceniter">
                                        @can('marketing.customer.update')
                                            <a href="{{ route('marketing.akunUser.edit', $item->id) }}"
                                                class="btn-edit inline-flex items-center gap-1
                                            text-xs font-medium text-yellow-700 bg-yellow-100 hover:bg-yellow-200
                                            dark:bg-yellow-800 dark:text-yellow-100 dark:hover:bg-yellow-700
                                            px-2.5 py-1.5 rounded-md transition-colors duration-200
                                            focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-1
                                            active:scale-95">
                                                Edit
                                            </a>
                                        @endcan

                                        @can('marketing.customer.delete')
                                            <form action="{{ route('marketing.akunUser.destroy', $item->id) }}" method="POST"
                                                class="delete-form">
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

        if (document.getElementById("table-akunUser") && typeof simpleDatatables.DataTable !== 'undefined') {
            const dataTable = new simpleDatatables.DataTable("#table-akunUser", {
                searchable: true,
                sortable: true,
            });
        }
    </script>
@endsection
