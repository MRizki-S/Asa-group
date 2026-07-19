@extends('layouts.app')

@section('pageActive', 'karyawan')

@section('content')
    <!-- ===== Main Content Start ===== -->
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: 'karyawan' }">
            @include('partials.breadcrumb')
        </div>
        <!-- Breadcrumb End -->

        {{-- Alert Error Validasi --}}
        @if ($errors->any())
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan',
                        html: `<ul class="text-left list-disc list-inside text-sm">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>`,
                        showConfirmButton: true
                    });
                });
            </script>
        @endif

        {{-- Alert Success --}}
        @if (session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: '{{ session('success') }}',
                        showConfirmButton: false,
                        timer: 2000
                    });
                });
            </script>
        @endif

        <div class="space-y-5 sm:space-y-6 mt-6">
            <div
                class="rounded-2xl border border-gray-200 px-5 py-4 sm:px-6 sm:py-5 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        Daftar Karyawan ABM GROUP
                    </h3>

                    <a href="{{ route('superadmin.karyawan.create') }}"
                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition shadow-sm">
                        + Tambah
                    </a>
                </div>

                {{-- Filter Section --}}
                <form method="GET" action="{{ route('superadmin.karyawan.index') }}"
                    class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center">
                    <h3 class="text-sm text-gray-500 whitespace-nowrap font-medium">Filter UBS:</h3>
                    <div class="w-full sm:min-w-[200px] sm:w-auto">
                        <select name="ubs_id" id="selectUbs"
                            class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-sm rounded-lg p-2.5 text-gray-700 dark:text-white outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="">Semua UBS dan HUB</option>
                            <option value="HUB" {{ request('ubs_id') == 'HUB' ? 'selected' : '' }}>HUB (PUSAT)</option>
                            @foreach ($ubs as $u)
                                <option value="{{ $u->id }}" {{ request('ubs_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->nama_ubs }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit"
                            class="flex-1 sm:flex-none px-4 py-2 text-sm font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                            Terapkan
                        </button>
                        <a href="{{ route('superadmin.karyawan.index') }}"
                            class="flex-1 sm:flex-none px-4 py-2 text-sm text-center font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                            Reset
                        </a>
                    </div>
                </form>

                <div class="max-w-full overflow-x-auto custom-scrollbar">
                    <table id="table-karyawan" class="min-w-full" style="min-width: 700px;">
                    <thead>
                        <tr>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Nama</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">No HP</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Jabatan</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">UBS</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($karyawans as $item)
                            <tr>
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $item->nama }}
                                </td>
                                <td class="text-gray-900 dark:text-white">
                                    {{ $item->no_hp }}
                                </td>
                                <td class="text-gray-900 dark:text-white">
                                    <span class="px-2 py-1 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 rounded text-xs font-medium">
                                        {{ $item->role?->name ?? '-' }}
                                    </span>
                                </td>
                                <td class="text-gray-900 dark:text-white">
                                    {{ $item->perumahaan?->nama_ubs ?? 'HUB (PUSAT)' }}
                                </td>
                                <td class="px-6 py-4 flex flex-wrap gap-2 justify-center">
                                    <a href="{{ route('superadmin.karyawan.edit', $item->id) }}"
                                        class="inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        Edit
                                    </a>

                                    <form action="{{ route('superadmin.karyawan.destroy', $item->id) }}" method="POST" class="delete-form inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="delete-btn inline-flex items-center gap-1 text-xs font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            Hapus
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

    <!-- DataTables JS & styling integration -->
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@9.0.3"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (document.getElementById("table-karyawan") && typeof simpleDatatables !== 'undefined') {
                const dataTable = new simpleDatatables.DataTable("#table-karyawan", {
                    searchable: true,
                    perPage: 10,
                    labels: {
                        placeholder: "Cari...",
                        searchTitle: "Cari di dalam tabel",
                        perPage: "data per halaman",
                        noRows: "Tidak ada data ditemukan",
                        info: "Menampilkan {start} sampai {end} dari {rows} data",
                    }
                });
            }
        });

        // SweetAlert 2 for delete data confirmation
        document.addEventListener('click', function(e) {
            if (e.target.closest('.delete-btn')) {
                const btn = e.target.closest('.delete-btn');
                const form = btn.closest('.delete-form');

                Swal.fire({
                    title: 'Yakin hapus data ini?',
                    text: "Apakah anda yakin menghapus data karyawan ini?",
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
