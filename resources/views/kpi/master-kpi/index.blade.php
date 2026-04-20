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
                class="rounded-2xl border border-gray-200 px-4 py-4 sm:px-6 sm:py-5 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

                {{-- Header: Title + Tambah Button --}}
                @can('kpi.master-kpi.create')
                    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">List Master Komponen KPI</h3>
                        <a href="{{ route('kpi.komponen.create') }}"
                            class="inline-flex items-center justify-center gap-1 w-full sm:w-auto px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Tambah Komponen
                        </a>
                    </div>
                @endcan

                {{-- Filter Section --}}
                <form method="GET" action="{{ route('kpi.komponen.index') }}"
                    class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center">
                    <h3 class="text-sm text-gray-500 whitespace-nowrap">Filter Role:</h3>
                    <div class="w-full sm:min-w-[200px] sm:w-auto">
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
                    <div class="flex gap-2">
                        <button type="submit"
                            class="flex-1 sm:flex-none px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                            Terapkan
                        </button>
                        <a href="{{ route('kpi.komponen.index') }}"
                            class="flex-1 sm:flex-none px-4 py-2 text-sm text-center bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg transition">
                            Reset
                        </a>
                    </div>
                </form>

                {{-- ============================================================
                     DESKTOP TABLE (hidden on mobile)
                     ============================================================ --}}
                <div class="hidden sm:block overflow-x-auto">
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

                                @canany(['kpi.master-kpi.update', 'kpi.master-kpi.delete'])
                                    <th class="py-3 px-4 font-medium text-sm text-gray-700 dark:text-gray-400 text-center">Aksi
                                    </th>
                                @endcanany
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
                                        <button type="button"
                                            @click="modalContent = { name: '{{ $item->nama_komponen }}', tasks: {{ $item->tasks->toJson() }} }; openModal = true"
                                            class="inline-flex items-center gap-1 font-bold text-blue-600 hover:text-blue-800 px-3 py-1 rounded-md hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                                            {{ $item->tasks->count() }}
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
                                    @canany(['kpi.master-kpi.update', 'kpi.master-kpi.delete'])
                                        <td class="py-4 px-4 text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                @can('kpi.master-kpi.update')
                                                    <a href="{{ route('kpi.komponen.edit', $item->id) }}"
                                                        class="px-2.5 py-1.5 text-xs font-medium text-yellow-700 bg-yellow-100 rounded-md hover:bg-yellow-200 transition">Edit</a>
                                                @endcan
                                                @can('kpi.master-kpi.delete')
                                                    <form action="{{ route('kpi.komponen.destroy', $item->id) }}" method="POST"
                                                        class="delete-form inline">
                                                        @csrf @method('DELETE')
                                                        <button type="button"
                                                            class="delete-btn px-3 py-1 text-xs text-white bg-red-600 rounded hover:bg-red-700 transition">
                                                            Hapus
                                                        </button>
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

                {{-- ============================================================
                     MOBILE CARD LIST (visible only on mobile)
                     ============================================================ --}}
                <div class="sm:hidden space-y-3">
                    @forelse ($allKpi as $item)
                        <div
                            class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-white/[0.02] p-4 space-y-3">

                            {{-- Top row: Role + Status badge --}}
                            <div class="flex items-center justify-between gap-2">
                                <span
                                    class="text-xs font-medium text-blue-600 bg-blue-50 dark:bg-blue-900/20 dark:text-blue-400 px-2 py-0.5 rounded-full">
                                    {{ $item->role->name }}
                                </span>
                                @if ($item->is_active)
                                    <span class="text-green-600 font-bold text-xs uppercase tracking-wider">Aktif</span>
                                @else
                                    <span class="text-red-600 font-bold text-xs uppercase tracking-wider">Non-Aktif</span>
                                @endif
                            </div>

                            {{-- Nama Komponen --}}
                            <p class="text-sm font-semibold text-gray-800 dark:text-white leading-snug">
                                {{ $item->nama_komponen }}
                            </p>

                            {{-- Tipe Perhitungan --}}
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-500 dark:text-gray-400">Tipe:</span>
                                <span
                                    class="px-2 py-0.5 text-xs bg-gray-200 dark:bg-gray-700 rounded text-gray-600 dark:text-gray-300 font-medium">
                                    {{ str_replace('_', ' ', $item->tipe_perhitungan) }}
                                </span>
                            </div>

                            {{-- Divider --}}
                            <div class="border-t border-gray-200 dark:border-gray-700"></div>

                            {{-- Bottom row: Task button + Actions --}}
                            <div class="flex items-center justify-between gap-2">
                                <button type="button"
                                    @click="modalContent = { name: '{{ $item->nama_komponen }}', tasks: {{ $item->tasks->toJson() }} }; openModal = true"
                                    class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-800 px-3 py-1.5 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors border border-blue-200 dark:border-blue-800">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    {{ $item->tasks->count() }} Task
                                </button>

                                <div class="flex items-center gap-2">
                                    <a href="{{ route('kpi.komponen.edit', $item->id) }}"
                                        class="px-3 py-1.5 text-xs font-medium text-yellow-700 bg-yellow-100 rounded-lg hover:bg-yellow-200 transition">
                                        Edit
                                    </a>
                                    <form action="{{ route('kpi.komponen.destroy', $item->id) }}" method="POST"
                                        class="delete-form inline">
                                        @csrf @method('DELETE')
                                        <button type="button"
                                            class="delete-btn px-3 py-1.5 text-xs text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10 text-sm text-gray-400 italic">
                            Belum ada data komponen KPI.
                        </div>
                    @endforelse
                </div>

            </div>
        </div>

        {{-- ============================================================
             MODAL TASK LIST
             ============================================================ --}}
        <div x-show="openModal"
            class="fixed inset-0 z-[99] flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/50 backdrop-blur-sm"
            style="display: none;" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">

            {{-- Modal panel: bottom sheet on mobile, centered on desktop --}}
            <div class="bg-white dark:bg-gray-900 w-full sm:max-w-lg sm:w-full rounded-t-2xl sm:rounded-2xl p-5 sm:p-6 shadow-2xl relative max-h-[85vh] flex flex-col"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="translate-y-full sm:translate-y-0 sm:scale-95 sm:opacity-0"
                x-transition:enter-end="translate-y-0 sm:scale-100 sm:opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="translate-y-0 sm:scale-100 sm:opacity-100"
                x-transition:leave-end="translate-y-full sm:translate-y-0 sm:scale-95 sm:opacity-0"
                @click.away="openModal = false">

                {{-- Bottom sheet handle (mobile only) --}}
                <div class="sm:hidden flex justify-center mb-3">
                    <div class="w-10 h-1 bg-gray-300 dark:bg-gray-600 rounded-full"></div>
                </div>

                {{-- Modal header --}}
                <div class="flex justify-between items-center mb-4 border-b dark:border-gray-800 pb-3 flex-shrink-0">
                    <h3 class="text-base font-bold text-gray-800 dark:text-white pr-4"
                        x-text="'Daftar Task: ' + modalContent.name"></h3>
                    <button @click="openModal = false"
                        class="flex-shrink-0 w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition text-xl leading-none">
                        &times;
                    </button>
                </div>

                {{-- Task list (scrollable) --}}
                <div class="space-y-2 overflow-y-auto pr-1 custom-scrollbar flex-1">
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
                        <p class="text-sm text-gray-400 italic text-center py-8">Tidak ada task untuk komponen ini.</p>
                    </template>
                </div>

                {{-- Footer --}}
                <div class="mt-4 flex-shrink-0">
                    <button @click="openModal = false"
                        class="w-full sm:w-auto sm:float-right px-5 py-2.5 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

    </div>

    <script>
        $(document).ready(function() {
            // DataTable hanya diinisialisasi di desktop
            if (window.innerWidth >= 640) {
                const tableElement = document.getElementById("table-kpi");
                if (tableElement) {
                    new simpleDatatables.DataTable(tableElement, {
                        searchable: true,
                        sortable: true,
                        fixedHeight: false,
                        perPage: 10,
                    });
                }
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

        $(document).ready(function() {
            $('#selectRole').select2({
                placeholder: "Semua Role",
                theme: 'bootstrap4',
                allowClear: true,
                width: '100%'
            });
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
