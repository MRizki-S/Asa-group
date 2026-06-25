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
            <div class="flex p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400"
                role="alert">
                <svg class="shrink-0 inline w-4 h-4 me-3 mt-[2px]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                    fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                </svg>
                <div>
                    <span class="font-medium">Terjadi kesalahan:</span>
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
                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                </svg>
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
                        Daftar Karyawan ABM GROUP
                    </h3>

                    <a href="{{ route('superadmin.karyawan.create') }}"
                        class="inline-block px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        + Tambah Karyawan
                    </a>
                </div>

                <table id="table-karyawan">
                    <thead>
                        <tr>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Nama</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">No HP</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Jabatan</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Unit Bisnis / Perumahaan</th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">Akun User Login</th>
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
                                <td class="text-gray-900 dark:text-white font-mono text-xs">
                                    @if($item->users->isNotEmpty())
                                        @foreach($item->users as $u)
                                            <div class="mb-1 last:mb-0">
                                                {{ $u->username }} ({{ $u->nama_lengkap }})
                                            </div>
                                        @endforeach
                                    @else
                                        <span class="text-red-500 italic">Belum dikaitkan</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('superadmin.karyawan.edit', $item->id) }}"
                                            class="inline-flex items-center px-2.5 py-1.5 text-xs font-semibold text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-200 transition-colors">
                                            Edit
                                        </a>
                                        <form action="{{ route('superadmin.karyawan.destroy', $item->id) }}" method="POST"
                                            class="inline-block"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus data karyawan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center px-2.5 py-1.5 text-xs font-semibold text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-200 transition-colors">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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
    </script>
@endsection
