@extends('layouts.app')

@section('pageActive', 'KategoriAkun')

@section('content')
    <!-- ===== Main Content Start ===== -->
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: 'KategoriAkun' }">
            @include('partials.breadcrumb')
        </div>
        <!-- Breadcrumb End -->

        <div class="space-y-5 sm:space-y-6">
            <div
                class="rounded-2xl border border-gray-200 px-5 py-4 sm:px-6 sm:py-5 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        Kategori Akun
                    </h3>
                </div>



                <table id="table-kualifikasiBlok">
                    <thead>
                        <tr class="text-center">
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                                Kode
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                                Nama Kategori
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                                Normal Balance
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                                Laporan
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($kategoriAkun as $item)
                            <tr class="text-center">
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $item->kode }}</td>
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $item->nama }}</td>
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $item->normal_balance }}</td>
                                <td class="whitespace-nowrap">
                                    @if ($item->laporan === 'neraca')
                                        <span class="px-2 py-1 text-xs font-semibold rounded bg-green-100 text-green-700">
                                            Neraca
                                        </span>
                                    @elseif ($item->laporan === 'laba_rugi')
                                        <span class="px-2 py-1 text-xs font-semibold rounded bg-purple-100 text-purple-700">
                                            Laba Rugi
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    <!-- ===== Main Content End ===== -->

    <script>
        if (document.getElementById("table-kualifikasiBlok") && typeof simpleDatatables.DataTable !== 'undefined') {
            const dataTable = new simpleDatatables.DataTable("#table-kualifikasiBlok", {
                searchable: true,
                sortable: false,
            });
        }
    </script>
@endsection
