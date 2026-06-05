@extends('layouts.app')

@section('pageActive', 'MasterQC-RAP')

@section('content')
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">
    <!-- ===== Main Content Start ===== -->
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: 'Master QC RAP' }">
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
                        List Master QC & RAP
                    </h3>

                    @can('etalase.blok.create')
                        <a href="{{ route('produksi.masterQcRap.create') }}"
                            class="inline-block px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                            + Tambah QC & RAP
                        </a>
                    @endcan
                </div>

                <form method="GET" action="{{ route('produksi.masterQcRap.index') }}"
                    class="mb-4 flex items-center gap-3">
                    <h3 class="text-sm text-gray-500 dark:text-white/90">Filter -</h3>

                    <!-- Select Type -->
                    <div>
                        <select name="typeFil" id="selectType"
                            class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                            dark:bg-gray-600 dark:text-white">
                            <option value="">Semua Type</option>
                            @foreach ($allType as $item)
                                <option value="{{ $item->slug }}" {{ $item->slug === $typeSlug ? 'selected' : '' }}>
                                    {{ $item->nama_type }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <script>
                        $(document).ready(function() {
                            $('#selectType').select2({
                                placeholder: "Semua Type",
                                theme: 'bootstrap4',
                                allowClear: true,
                                width: '100%'
                            });
                        });
                    </script>

                    <button type="submit" class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        Terapkan
                    </button>

                    <a href="{{ route('produksi.masterQcRap.index') }}"
                        class="px-4 py-2 text-sm bg-gray-200 rounded-lg hover:bg-gray-300">
                        Reset
                    </a>
                </form>
                <table id="table-qc">
                    <thead>
                        <tr>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                                Type Unit
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                                Nama QC
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                                Jumlah Langkah QC
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                                Dibuat Pada
                            </th>
                            @canany(['etalase.blok.update', ' etalase.blok.delete'])
                                <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                                    Aksi
                                </th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($allQcContainer as $item)
                            <tr>
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white text-center">
                                    {{ $item->type->nama_type }}</td>
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white text-center">
                                    {{ $item->nama_container }}</td>
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white text-center">
                                    {{ $item->urutan->count() }}</td>
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white text-center">
                                    {{ $item->created_at->format('d M Y H:i:s') }}</td>

                                {{-- @canany(['produksi.qc-urutan.update', 'produksi.qc-urutan.delete']) --}}
                                <td class="px-6 py-4 flex flex-wrap gap-2 justify-center">
                                    {{-- @can('produksi.qc-urutan.update') --}}
                                    <a href="{{ route('produksi.masterQcRap.show', $item->id) }}"
                                        class="btn-edit inline-flex items-center gap-1
                                    text-xs font-medium text-blue-700 bg-blue-100 hover:bg-blue-200
                                    dark:bg-blue-800 dark:text-blue-100 dark:hover:bg-blue-700
                                    px-2.5 py-1.5 rounded-md transition-colors duration-200
                                    focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1
                                    active:scale-95">
                                        Detail
                                    </a>
                                    <a href="{{ route('produksi.masterQcRap.edit', $item) }}"
                                        class="btn-edit inline-flex items-center gap-1
                                    text-xs font-medium text-yellow-700 bg-yellow-100 hover:bg-yellow-200
                                    dark:bg-yellow-800 dark:text-yellow-100 dark:hover:bg-yellow-700
                                    px-2.5 py-1.5 rounded-md transition-colors duration-200
                                    focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-1
                                    active:scale-95">
                                        Edit
                                    </a>
                                    {{-- @endcan --}}

                                    {{-- @can('produksi.qc-urutan.delete') --}}
                                    <form action="{{ route('produksi.masterQcRap.destroy', $item->id) }}" method="POST"
                                        class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                            class="delete-btn px-3 py-1 text-sm text-white bg-red-600 rounded hover:bg-red-700">
                                            Delete
                                        </button>
                                    </form>
                                    {{-- @endcan --}}
                                </td>
                                {{-- @endcanany --}}
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    <!-- ===== Main Content End ===== -->


    {{-- @include('Etalase.kualifikasi-blok.modal.modal-create-Blok') --}}

    <script>
        document.addEventListener('click', function(e) {
            if (e.target.closest('.delete-btn')) {
                const btn = e.target.closest('.delete-btn');
                const form = btn.closest('.delete-form');

                Swal.fire({
                    title: 'Yakin hapus data ini?',
                    text: "Apakah anda yakin menghapus qc ini? Semua data yang terkait dengan qc akan ikut terhapus.",
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
        if (document.getElementById("table-qc") && typeof simpleDatatables.DataTable !== 'undefined') {
            const dataTable = new simpleDatatables.DataTable("#table-qc", {
                searchable: true,
                sortable: false
            });
        }
    </script>
@endsection
