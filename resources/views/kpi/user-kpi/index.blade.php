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
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Daftar Penilaian Periode
                        {{ $bulanFilter }}/{{ $tahunFilter }}</h3>
                    <div class="flex items-center gap-2">
                        @can('kpi.kpi-user.export')
                            <button type="button" id="btnExportModal"
                                class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-green-700 bg-green-100 rounded-lg hover:bg-green-200 transition shadow-sm">
                                Export Excel
                            </button>
                        @endcan
                        @can('kpi.kpi-user.create')
                            <a href="{{ route('kpi.user.create') }}"
                                class="inline-block px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition shadow-sm">
                                + Penilaian Baru
                            </a>
                        @endcan
                    </div>
                </div>

                <form method="GET" action="{{ route('kpi.user.index') }}"
                    class="mb-6 flex flex-wrap items-center gap-2 sm:gap-3">

                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400 whitespace-nowrap">Filter:</span>

                    <select name="bulan"
                        class="flex-1 min-w-[130px] max-w-[180px] bg-gray-50 border border-gray-200 text-sm rounded-lg py-2 pl-3 pr-8
               dark:bg-gray-800 dark:border-gray-700 text-gray-700 dark:text-white outline-none
               focus:ring-1 focus:ring-blue-500 appearance-none cursor-pointer">
                        <option value="">Semua Bulan</option>
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $bulanFilter == $m ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
                        @endfor
                    </select>

                    <select name="tahun"
                        class="flex-1 min-w-[100px] max-w-[140px] bg-gray-50 border border-gray-200 text-sm rounded-lg py-2 pl-3 pr-8
               dark:bg-gray-800 dark:border-gray-700 text-gray-700 dark:text-white outline-none
               focus:ring-1 focus:ring-blue-500 appearance-none cursor-pointer">
                        @for ($y = date('Y'); $y >= date('Y') - 2; $y--)
                            <option value="{{ $y }}" {{ $tahunFilter == $y ? 'selected' : '' }}>
                                {{ $y }}</option>
                        @endfor
                    </select>

                    <select name="role"
                        class="flex-1 min-w-[130px] max-w-[200px] bg-gray-50 border border-gray-200 text-sm rounded-lg py-2 pl-3 pr-8
               dark:bg-gray-800 dark:border-gray-700 text-gray-700 dark:text-white outline-none
               focus:ring-1 focus:ring-blue-500 appearance-none cursor-pointer">
                        <option value="">Semua Role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                                {{ strtoupper($role->name) }}
                            </option>
                        @endforeach
                    </select>

                    <select name="status"
                        class="flex-1 min-w-[130px] max-w-[160px] bg-gray-50 border border-gray-200 text-sm rounded-lg py-2 pl-3 pr-8
               dark:bg-gray-800 dark:border-gray-700 text-gray-700 dark:text-white outline-none
               focus:ring-1 focus:ring-blue-500 appearance-none cursor-pointer">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>DRAFT</option>
                        <option value="final" {{ request('status') == 'final' ? 'selected' : '' }}>FINAL</option>
                    </select>

                    <div class="flex gap-2 flex-shrink-0">
                        <button type="submit"
                            class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition shadow-sm font-medium whitespace-nowrap">
                            Cari
                        </button>
                        <a href="{{ route('kpi.user.index') }}"
                            class="px-4 py-2 text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg
                   hover:bg-gray-200 dark:hover:bg-gray-600 transition whitespace-nowrap">
                            Reset
                        </a>
                    </div>
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

                                @canany(['kpi.kpi-user.update', 'kpi.kpi-user.delete', 'kpi.kpi-user.detail'])
                                    <th class="py-3 px-4 font-medium text-sm text-gray-700 dark:text-gray-400 text-center">Aksi
                                    </th>
                                @endcanany
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
                                    @canany(['kpi.kpi-user.update', 'kpi.kpi-user.delete', 'kpi.kpi-user.detail'])
                                        <td class="py-4 px-4 text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                @if ($item->status == 'draft')
                                                    @can('kpi.kpi-user.update')
                                                        <a href="{{ route('kpi.user.edit', $item->id) }}"
                                                            class="px-2.5 py-1.5 text-xs font-medium text-yellow-700 bg-yellow-100 rounded-md hover:bg-yellow-200 transition">Edit</a>
                                                    @endcan
                                                @else
                                                    @can('kpi.kpi-user.detail')
                                                        <a href="{{ route('kpi.user.show', $item->id) }}"
                                                            class="px-2.5 py-1.5 text-xs font-medium text-blue-700 bg-blue-100 rounded-md hover:bg-blue-200 transition">Detail</a>
                                                    @endcan
                                                @endif

                                                @can('kpi.kpi-user.delete')
                                                    <form action="{{ route('kpi.user.destroy', $item->id) }}" method="POST"
                                                        class="delete-form inline">
                                                        @csrf @method('DELETE')
                                                        <button type="button"
                                                            class="delete-btn px-3 py-1 text-xs text-white bg-red-600 rounded hover:bg-red-700 transition">Hapus</button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                    @endcanany
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="modalExport" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl w-full max-w-md">

            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                <div>
                    <h4 class="text-base font-semibold text-gray-800 dark:text-white">Export Excel KPI</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Periode: {{ date('F', mktime(0, 0, 0, $bulanFilter, 1)) }} {{ $tahunFilter }}
                    </p>
                </div>
                <button type="button" id="btnCloseModal"
                    class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div
                class="flex items-center justify-between px-5 py-3 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" id="checkAll" checked
                        class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500 cursor-pointer">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Pilih Semua</span>
                </label>
                <span id="selectedCount" class="text-xs text-gray-500 dark:text-gray-400">
                    {{ count($allKpiUser) }} dari {{ count($allKpiUser) }} dipilih
                </span>
            </div>
            <div class="px-5 py-3 max-h-72 overflow-y-auto space-y-1">
                @forelse ($allKpiUser as $item)
                    @if ($item->status == 'final')
                        <label
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer transition group">
                            <input type="checkbox" name="kpi_ids[]" value="{{ $item->id }}"
                                class="kpi-checkbox w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500 cursor-pointer"
                                checked>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 dark:text-white truncate">
                                    {{ $item->user->nama_lengkap }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                    {{ $item->user->getRoleNames()->implode(', ') }}
                                    <span
                                        class="ml-1.5 {{ $item->status == 'draft' ? 'text-yellow-600' : 'text-green-600' }} font-medium uppercase">
                                        · {{ $item->status }}
                                    </span>
                                </p>
                            </div>
                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400 shrink-0">
                                {{ number_format((float) $item->total_nilai, 2) }}
                            </span>
                        </label>
                    @endif
                @empty
                    <p class="text-sm text-gray-500 text-center py-6">Tidak ada data KPI pada periode ini.</p>
                @endforelse
            </div>

            <div class="flex items-center justify-end gap-2 px-5 py-4 border-t border-gray-200 dark:border-gray-700">
                <button type="button" id="btnCancelModal"
                    class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition font-medium">
                    Batal
                </button>
                <button type="button" id="btnDoExport"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                    Download Excel
                </button>
            </div>
        </div>
    </div>

    <form id="formExport" method="POST" action="{{ route('kpi.user.export') }}">
        @csrf
        <input type="hidden" name="bulan" value="{{ $bulanFilter }}">
        <input type="hidden" name="tahun" value="{{ $tahunFilter }}">
        <div id="exportIdsContainer"></div>
    </form>

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

        const modal = document.getElementById('modalExport');
        const checkboxes = document.querySelectorAll('.kpi-checkbox');
        const checkAll = document.getElementById('checkAll');
        const selectedCount = document.getElementById('selectedCount');
        const btnDoExport = document.getElementById('btnDoExport');
        const totalCount = checkboxes.length;

        function updateCount() {
            const checked = document.querySelectorAll('.kpi-checkbox:checked').length;
            selectedCount.textContent = `${checked} dari ${totalCount} dipilih`;
            checkAll.checked = checked === totalCount;
            checkAll.indeterminate = checked > 0 && checked < totalCount;
            btnDoExport.disabled = checked === 0;
        }

        document.getElementById('btnExportModal').addEventListener('click', function() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            updateCount();
        });

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        document.getElementById('btnCloseModal').addEventListener('click', closeModal);
        document.getElementById('btnCancelModal').addEventListener('click', closeModal);

        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeModal();
        });

        checkAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateCount();
        });

        checkboxes.forEach(cb => cb.addEventListener('change', updateCount));

        document.getElementById('btnDoExport').addEventListener('click', function() {
            const container = document.getElementById('exportIdsContainer');
            container.innerHTML = '';

            const checked = document.querySelectorAll('.kpi-checkbox:checked');
            if (checked.length === 0) {
                Swal.fire('Perhatian', 'Pilih minimal 1 karyawan untuk di-export.', 'warning');
                return;
            }

            checked.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'kpi_ids[]';
                input.value = cb.value;
                container.appendChild(input);
            });

            document.getElementById('formExport').submit();
            closeModal();
        });
    </script>
@endsection
