@extends('layouts.app')

@section('pageActive', 'AkunKeuangan')

@section('content')
    <!-- ===== Main Content Start ===== -->
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: 'AkunKeuangan' }">
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
                {{-- tambah data --}}
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        Akun Keuangan
                    </h3>

                    @can('keuangan.akun-keuangan.create')
                        <a href="{{ route('keuangan.akunKeuangan.create') }}"
                            class="inline-block px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                            + Tambah Akun Keuangan
                        </a>
                    @endcan
                </div>

                <table id="table-Blok">
                    <thead>
                        <tr>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                                Kode Akun
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                                Nama Akun
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                                Kategori
                            </th>
                            @canany(['keuangan.akun-keuangan.update', 'keuangan.akun-keuangan.delete'])
                                <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                                    Aksi
                                </th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            function renderAkun($akun, $level = 0)
                            {
                                // Tentukan padding berdasarkan level untuk indentasi teks
                                $paddingLeft = $level * 1.5;

                                echo '<tr class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">';

                                // Kolom Kode Akun (Tetap Sejajar sesuai permintaan)
                                echo '<td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">' .
                                    $akun->kode_akun .
                                    '</td>';

                                // Kolom Nama Akun dengan Garis Hirarki
                                echo '<td class="px-4 py-3">';
                                echo '<div class="flex items-center" style="margin-left: ' . $paddingLeft . 'rem;">';

                                // Tambahkan Icon atau Garis Cabang jika bukan level 0
                                if ($level > 0) {
                                    echo '<span class="text-gray-300 mr-2">├─</span>';
                                }

                                // Ikon Folder jika punya anak, Ikon File jika tidak
                                if ($akun->is_leaf == 0) {
                                    echo '<svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>';
                                    echo '<span class="font-bold text-gray-800 dark:text-white">' .
                                        $akun->nama_akun .
                                        '</span>';
                                } else {
                                    echo '<svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>';
                                    echo '<span class="text-gray-700 dark:text-gray-300">' .
                                        $akun->nama_akun .
                                        '</span>';
                                }

                                echo '</div>';
                                echo '</td>';

                                // Kolom Kategori dengan Badge
                                echo '<td class="px-4 py-3 text-center">';
                                $katNama = $akun->kategori->nama ?? '-';
                                echo '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">' .
                                    $katNama .
                                    '</span>';
                                echo '</td>';

                                // Kolom Aksi
                                if (auth()->user()->can('keuangan.akun-keuangan.update') || auth()->user()->can('keuangan.akun-keuangan.delete')) {
                                    echo '<td class="px-4 py-3 flex gap-2 justify-center">';

                                    // Tombol Edit
                                    echo '<a href="' .
                                        route('keuangan.akunKeuangan.edit', $akun) .
                                        '" class="p-1.5 text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors" title="Edit">';
                                    echo '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>';
                                    echo '</a>';

                                    // Tombol Delete
                                    echo '<form action="' .
                                        route('keuangan.akunKeuangan.destroy', $akun) .
                                        '" method="POST" class="delete-form inline">';
                                    echo csrf_field();
                                    echo method_field('DELETE');
                                    echo '<button type="button" class="delete-btn p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">';
                                    echo '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>';
                                    echo '</button>';
                                    echo '</form>';
                                    echo '</td>';
                                }
                                echo '</tr>';

                                // Rekursif untuk anak
                                if ($akun->children) {
                                    foreach ($akun->children as $child) {
                                        renderAkun($child, $level + 1);
                                    }
                                }
                            }
                        @endphp

                        @foreach ($akunKeuangan as $akun)
                            @php renderAkun($akun); @endphp
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
                    text: "Apakah anda yakin menghapus Akun Keuangan ini? Semua data yang terkait dengan akun keuangan ini akan ikut terhapus.",
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
        if (document.getElementById("table-Blok") && typeof simpleDatatables.DataTable !== 'undefined') {
            const dataTable = new simpleDatatables.DataTable("#table-Blok", {
                searchable: true,
                sortable: false,
                perPageSelect: [5, 10, 20, 50, 100],
            });
        }
    </script>
@endsection
