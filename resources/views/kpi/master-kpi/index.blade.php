@extends('layouts.app')

@section('pageActive', 'Master-KPI')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-data="{ openModal: false, modalContent: { name: '', tasks: [] } }">
        <div x-data="{ pageName: 'Master KPI' }">
            @include('partials.breadcrumb')
        </div>

        {{-- Alert Error --}}
        @if ($errors->any())
            <div class="flex p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400"
                role="alert">
                <ul class="list-disc list-inside">
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
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">List Master Komponen KPI</h3>
                    <a href="{{ route('kpi.komponen.create') }}"
                        class="inline-block px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition shadow-sm">
                        + Tambah Komponen
                    </a>
                </div>

                {{-- Filter Section --}}
                <form method="GET" action="{{ route('kpi.komponen.index') }}" class="mb-6 flex items-center gap-3">
                    <h3 class="text-sm text-gray-500">Filter Role:</h3>
                    <div class="min-w-[200px]">
                        <select name="roleFil" id="selectRole"
                            class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-sm rounded-lg p-2.5 text-gray-700 dark:text-white outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="">Semua Role</option>
                            @foreach ($allRoles as $role)
                                <option value="{{ $role->id }}" {{ $role->id == $roleFilter ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <script>
                        $(document).ready(function() {
                            $('#selectRole').select2({
                                placeholder: "Semua Role",
                                theme: 'bootstrap4',
                                allowClear: true,
                                width: '100%'
                            });
                        });
                    </script>
                    <button type="submit"
                        class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">Terapkan</button>
                    <a href="{{ route('kpi.komponen.index') }}"
                        class="px-4 py-2 text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg transition">Reset</a>
                </form>

                <div class="overflow-x-auto">
                    <table id="table-kpi" class="min-w-full">
                        <thead>
                            <tr class="text-left border-b border-gray-200 dark:border-gray-800">
                                <th class="py-3 px-4 font-medium text-sm text-gray-700 dark:text-gray-400">Role</th>
                                <th class="py-3 px-4 font-medium text-sm text-gray-700 dark:text-gray-400">Nama Komponen
                                </th>
                                <th class="py-3 px-4 font-medium text-sm text-gray-700 dark:text-gray-400 text-center">Tipe
                                    Perhitungan</th>
                                <th class="py-3 px-4 font-medium text-sm text-gray-700 dark:text-gray-400 text-center">Jml
                                    Task</th>
                                <th class="py-3 px-4 font-medium text-sm text-gray-700 dark:text-gray-400 text-center">
                                    Status</th>
                                <th class="py-3 px-4 font-medium text-sm text-gray-700 dark:text-gray-400 text-center">Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($allKpi as $item)
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] border-b dark:border-gray-800">
                                    <td class="py-4 px-4 text-sm text-gray-600 dark:text-gray-400">{{ $item->role->name }}
                                    </td>
                                    <td class="py-4 px-4 text-sm font-medium text-gray-800 dark:text-white">
                                        {{ $item->nama_komponen }}</td>
                                    <td class="py-4 px-4 text-sm text-center">
                                        <span
                                            class="px-2 py-1 text-xs bg-gray-100 dark:bg-gray-800 rounded text-gray-600 dark:text-gray-400 font-medium">
                                            {{ str_replace('_', ' ', $item->tipe_perhitungan) }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-sm text-center">
                                        {{-- TRIGGER MODAL TASK --}}
                                        <button type="button"
                                            @click="modalContent = { name: '{{ $item->nama_komponen }}', tasks: {{ $item->tasks->toJson() }} }; openModal = true"
                                            class="inline-flex items-center gap-1 font-bold text-blue-600 hover:text-blue-800 px-3 py-1 rounded-md hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                                            {{ $item->tasks->count() }} Task
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                    </td>
                                    <td class="py-4 px-4 text-sm text-center">
                                        @if ($item->is_active)
                                            <span
                                                class="text-green-600 font-bold text-xs uppercase tracking-wider">Aktif</span>
                                        @else
                                            <span
                                                class="text-red-600 font-bold text-xs uppercase tracking-wider">Non-Aktif</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('kpi.komponen.edit', $item->id) }}"
                                                class="px-2.5 py-1.5 text-xs font-medium text-yellow-700 bg-yellow-100 rounded-md hover:bg-yellow-200 transition">Edit</a>
                                            <form action="{{ route('kpi.komponen.destroy', $item->id) }}" method="POST"
                                                class="delete-form inline">
                                                @csrf @method('DELETE')
                                                <button type="button"
                                                    class="delete-btn px-3 py-1 text-xs text-white bg-red-600 rounded hover:bg-red-700 transition">
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

        <div x-show="openModal"
            class="fixed inset-0 z-[99] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            style="display: none;" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            <div class="bg-white dark:bg-gray-900 rounded-2xl max-w-lg w-full p-6 shadow-2xl relative"
                @click.away="openModal = false">
                <div class="flex justify-between items-center mb-6 border-b dark:border-gray-800 pb-3">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white"
                        x-text="'Daftar Task: ' + modalContent.name"></h3>
                    <button @click="openModal = false" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
                </div>

                <div class="space-y-2 max-h-[60vh] overflow-y-auto pr-2 custom-scrollbar">
                    <template x-for="(task, index) in modalContent.tasks" :key="index">
                        <div
                            class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-white/[0.02] border border-gray-100 dark:border-gray-800 rounded-xl">
                            <span
                                class="flex-shrink-0 w-6 h-6 flex items-center justify-center bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-xs font-bold rounded-full"
                                x-text="index + 1"></span>
                            <span class="text-sm text-gray-700 dark:text-gray-300" x-text="task.nama_task"></span>
                        </div>
                    </template>
                    <template x-if="modalContent.tasks.length === 0">
                        <p class="text-sm text-gray-400 italic text-center py-4">Tidak ada task untuk komponen ini.</p>
                    </template>
                </div>

                <div class="mt-8 flex justify-end">
                    <button @click="openModal = false"
                        class="px-5 py-2.5 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            const tableElement = document.getElementById("table-kpi");
            if (tableElement) {
                new simpleDatatables.DataTable(tableElement, {
                    searchable: true,
                    sortable: true,
                    fixedHeight: false,
                    perPage: 10,
                    labels: {
                        // placeholder: "Cari komponen...",
                        // noRows: "Data tidak ditemukan",
                        // info: "Menampilkan {start} sampai {end} dari {rows} entri",
                    }
                });
            }
        });

        document.addEventListener('click', function(e) {
            const deleteBtn = e.target.closest('.delete-btn');
            if (deleteBtn) {
                e.preventDefault();
                const form = deleteBtn.closest('.delete-form');
                Swal.fire({
                    title: 'Hapus Komponen KPI?',
                    text: "Seluruh task terkait juga akan terhapus secara permanen.",
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
    </script>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #E5E7EB;
            border-radius: 10px;
        }

        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #374151;
        }
    </style>
@endsection
