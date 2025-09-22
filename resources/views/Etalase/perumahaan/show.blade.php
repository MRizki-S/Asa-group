@extends('layouts.app')

@section('pageActive', 'Perumahaan') {{-- ⬅️ ini yang jadi value Alpine.js page --}}

@section('content')
    <!-- ===== Main Content Start ===== -->
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: 'Perumahaan' }">
            @include('partials.breadcrumb', ['breadcrumbs' => $breadcrumbs])
        </div>
        <!-- Breadcrumb End -->

        <div class="space-y-5 sm:space-y-6">
            <div
                class="rounded-2xl border border-gray-200 px-5 py-4 sm:px-6 sm:py-5 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        Daftar Tahap – {{ $perumahaan->nama_perumahaan }}
                    </h3>

                    <a href="{{ route('tahap.create', $perumahaan->slug) }}"
                        class="inline-block px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        + Tambah Tahap
                    </a>

                </div>


                <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-gray-700 bg-gray-200 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3 ">
                                    Nama Tahap
                                </th>
                                <th scope="col" class="px-6 py-3 ">
                                    Type
                                </th>
                                <th scope="col" class="px-6 py-3 ">
                                    Kualifikasi Blok
                                </th>
                                <th scope="col" class="px-6 py-3 text-center">
                                    Total Blok
                                </th>
                                <th scope="col" class="px-6 py-3  text-center">
                                    Total Unit
                                </th>
                                <th scope="col" class="px-6 py-3 text-center ">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tahaps as $tahap)
                                <tr
                                    class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <td scope="row"
                                        class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ $tahap->nama_tahap }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @foreach ($tahap->types as $type)
                                            - {{ $type->nama_type }} <br>
                                        @endforeach
                                    </td>
                                    <td class="px-6 py-4">
                                        @foreach ($tahap->kualifikasiBlok as $kualifikasi)
                                            - {{ $kualifikasi->nama_kualifikasi_blok }} <br>
                                        @endforeach
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('blok.index', [
                                            'perumahaanFil' => $perumahaan->slug,
                                            'tahapFil' => $tahap->slug,
                                        ]) }}"
                                            class="text-blue-600 hover:underline">
                                            {{ $tahap->blok_count }} Blok
                                        </a>
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        <a href="
                                        {{ route('unit.index', [
                                            'perumahaan' => $perumahaan->slug,
                                            'tahapFil' => $tahap->slug,
                                        ]) }}
                                        "
                                            class="text-blue-600 hover:underline">
                                            {{ $tahap->unit_count }} Unit
                                        </a>
                                    </td>
                                    <td class="py-4 flex flex-wrap gap-2 justify-center items-center">
                                        <a href="{{ route('tahap.edit', ['perumahaan' => $perumahaan->slug, 'tahap' => $tahap->slug]) }}"
                                            class="btn-edit inline-flex items-center gap-1
                                        text-xs font-medium text-yellow-700 bg-yellow-100 hover:bg-yellow-200
                                        dark:bg-yellow-800 dark:text-yellow-100 dark:hover:bg-yellow-700
                                        px-2.5 py-1.5 rounded-md transition-colors duration-200
                                        focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-1
                                        active:scale-95">Edit</a>

                                        <form
                                            action="{{ route('tahap.destroy', ['perumahaan' => $perumahaan->slug, 'tahap' => $tahap->slug]) }}"
                                            method="POST" class="delete-form">
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
                    text: "Apakah anda yakin menghapus Tahap ini? Semua data yang terkait dengan Tahap ini akan ikut terhapus.",
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
@endsection
