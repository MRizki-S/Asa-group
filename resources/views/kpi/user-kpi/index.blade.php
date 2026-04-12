@extends('layouts.app')

@section('pageActive', 'User-KPI')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <div x-data="{ pageName: 'Penilaian KPI Karyawan' }">
            @include('partials.breadcrumb')
        </div>

        {{-- Alert Error --}}
        @if ($errors->any())
            <div class="flex p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400"
                role="alert">
                <ul class="list-disc list-inside text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="space-y-5 sm:space-y-6">
            <div
                class="rounded-2xl border border-gray-200 px-5 py-4 sm:px-6 sm:py-5 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

                {{-- Header --}}
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Daftar Penilaian Periode
                        {{ $bulanFilter }}/{{ $tahunFilter }}</h3>
                    <a href="{{ route('kpi.user.create') }}"
                        class="inline-block px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition shadow-sm">
                        + Input Penilaian Baru
                    </a>
                </div>

                {{-- Filter --}}
                <form method="GET" action="{{ route('kpi.user.index') }}" class="mb-6 flex flex-wrap items-center gap-3">
                    <h3 class="text-sm text-gray-500">Filter:</h3>

                    <select name="bulan"
                        class="bg-gray-50 border border-gray-200 text-sm rounded-lg py-2 px-3 dark:bg-gray-800 dark:border-gray-700 text-gray-700 dark:text-white outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="">Semua Bulan</option>
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $bulanFilter == $m ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                        @endfor
                    </select>

                    <select name="tahun"
                        class="bg-gray-50 border border-gray-200 text-sm rounded-lg py-2 px-3 dark:bg-gray-800 dark:border-gray-700 text-gray-700 dark:text-white outline-none focus:ring-1 focus:ring-blue-500">
                        @for ($y = date('Y'); $y >= date('Y') - 2; $y--)
                            <option value="{{ $y }}" {{ $tahunFilter == $y ? 'selected' : '' }}>
                                {{ $y }}</option>
                        @endfor
                    </select>

                    <select name="role"
                        class="bg-gray-50 border border-gray-200 text-sm rounded-lg
                        py-2 px-3 dark:bg-gray-800 dark:border-gray-700 text-gray-700 dark:text-white outline-none
                        focus:ring-1 focus:ring-blue-500">
                        <option value="">Semua Role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                                {{ strtoupper($role->name) }}
                            </option>
                        @endforeach
                    </select>

                    <select name="status"
                        class="bg-gray-50 border border-gray-200 text-sm rounded-lg py-2 px-3 dark:bg-gray-800 dark:border-gray-700 text-gray-700 dark:text-white outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>DRAFT</option>
                        <option value="final" {{ request('status') == 'final' ? 'selected' : '' }}>FINAL</option>
                    </select>

                    <button type="submit"
                        class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition shadow-sm font-medium">Cari</button>

                    <a href="{{ route('kpi.user.index') }}"
                        class="px-4 py-2 text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">Reset</a>
                </form>

                <div class="overflow-x-auto">
                    <table id="table-user-kpi" class="min-w-full">
                        <thead>
                            <tr class="text-left border-b border-gray-200 dark:border-gray-800">
                                <th class="py-3 px-4 font-medium text-sm text-gray-700 dark:text-gray-400">Nama Karyawan
                                </th>
                                <th class="py-3 px-4 font-medium text-sm text-gray-700 dark:text-gray-400">Jabatan (Role)
                                </th>
                                <th class="py-3 px-4 font-medium text-sm text-gray-700 dark:text-gray-400 text-center">Total
                                    Nilai</th>
                                <th class="py-3 px-4 font-medium text-sm text-gray-700 dark:text-gray-400 text-center">
                                    Status</th>
                                <th class="py-3 px-4 font-medium text-sm text-gray-700 dark:text-gray-400 text-center">Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($allKpiUser as $item)
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] border-b dark:border-gray-800">
                                    <td class="py-4 px-4 text-sm font-medium text-gray-800 dark:text-white">
                                        {{ $item->user->nama_lengkap }}
                                    </td>
                                    <td class="py-4 px-4 text-sm">
                                        <span
                                            class="px-2 py-1 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 rounded text-xs font-medium">
                                            {{ $item->user->getRoleNames()->implode(', ') }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-sm text-center">
                                        <p>{{ (float) $item->total_nilai }}</p>
                                    </td>
                                    <td class="py-4 px-4 text-sm text-center">
                                        <span
                                            class="{{ $item->status == 'draft' ? 'text-yellow-600' : 'text-green-600' }} font-bold text-xs uppercase tracking-wider">
                                            {{ $item->status }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            @if ($item->status == 'draft')
                                                <a href="{{ route('kpi.user.edit', $item->id) }}"
                                                    class="px-2.5 py-1.5 text-xs font-medium text-yellow-700 bg-yellow-100 rounded-md hover:bg-yellow-200 transition">Edit</a>
                                            @else
                                                <a href="{{ route('kpi.user.show', $item->id) }}"
                                                    class="px-2.5 py-1.5 text-xs font-medium text-blue-700 bg-blue-100 rounded-md hover:bg-blue-200 transition">Detail</a>
                                            @endif
                                            <form action="{{ route('kpi.user.destroy', $item->id) }}" method="POST"
                                                class="delete-form inline">
                                                @csrf @method('DELETE')
                                                <button type="button"
                                                    class="delete-btn px-3 py-1 text-xs text-white bg-red-600 rounded hover:bg-red-700 transition">Hapus</button>
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
    </div>

    <script>
        $(document).ready(function() {
            const tableElement = document.getElementById("table-user-kpi");
            if (tableElement) {
                new simpleDatatables.DataTable(tableElement, {
                    searchable: true,
                    sortable: true,
                    fixedHeight: false,
                    perPage: 10
                });
            }
        });

        document.addEventListener('click', function(e) {
            const deleteBtn = e.target.closest('.delete-btn');
            if (deleteBtn) {
                e.preventDefault();
                const form = deleteBtn.closest('.delete-form');
                Swal.fire({
                    title: 'Hapus Penilaian?',
                    text: "Data nilai user pada bulan ini akan dihapus permanen.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            }
        });

        $(document).ready(function() {
            $('#selectRole').select2({
                placeholder: "Semua Role",
                theme: 'bootstrap4',
                allowClear: true,
                width: '100%'
            });
        });
    </script>
@endsection
