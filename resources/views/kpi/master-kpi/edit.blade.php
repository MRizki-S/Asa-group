@extends('layouts.app')

@section('pageActive', 'Master-KPI')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">
        <div x-data="{ pageName: 'Edit Master KPI' }">
            @include('partials.breadcrumb')
        </div>

        <form action="{{ route('kpi.komponen.update', $komponen->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 gap-6">

                {{-- Informasi Komponen --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <h3 class="mb-5 text-lg font-semibold text-gray-800 dark:text-white/90">Informasi Komponen</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="mb-2.5 block text-sm font-medium text-gray-800 dark:text-white/90">Role</label>
                            <select name="role_id" id="selectRole" required
                                class="w-full rounded-lg border border-gray-300 bg-white dark:bg-gray-900 py-2 px-3 outline-none focus:border-blue-600 dark:border-gray-700 text-gray-700 dark:text-white/80 transition">
                                @foreach ($allRoles as $role)
                                    <option value="{{ $role->id }}"
                                        {{ old('role_id', $komponen->role_id) == $role->id ? 'selected' : '' }}
                                        class="bg-white dark:bg-gray-900 text-gray-700 dark:text-white">
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-2.5 block text-sm font-medium text-gray-800 dark:text-white/90">Nama
                                Komponen</label>
                            <input type="text" name="nama_komponen"
                                value="{{ old('nama_komponen', $komponen->nama_komponen) }}"
                                class="w-full rounded-lg border border-gray-300 bg-white dark:bg-gray-900 py-2 px-3 outline-none focus:border-blue-600 dark:border-gray-700 text-gray-700 dark:text-white/80 transition">
                        </div>

                        <div>
                            <label class="mb-2.5 block text-sm font-medium text-gray-800 dark:text-white/90">Tipe
                                Perhitungan</label>
                            <select name="tipe_perhitungan"
                                class="w-full rounded-lg border border-gray-300 bg-white dark:bg-gray-900 py-2 px-3 outline-none focus:border-blue-600 dark:border-gray-700 text-gray-700 dark:text-white/80 transition">
                                @foreach ($tipePerhitungan as $tipe)
                                    <option value="{{ $tipe }}"
                                        {{ old('tipe_perhitungan', $komponen->tipe_perhitungan) == $tipe ? 'selected' : '' }}
                                        class="bg-white dark:bg-gray-900 text-gray-700 dark:text-white">
                                        {{ str_replace('_', ' ', $tipe) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-center mt-8">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" class="sr-only peer"
                                    {{ $komponen->is_active ? 'checked' : '' }}>
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600">
                                </div>
                                <span class="ml-3 text-sm font-medium text-gray-800 dark:text-white/90">Status Aktif</span>
                            </label>
                        </div>
                    </div>

                    <hr class="my-6 border-gray-200 dark:border-gray-700">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="mb-2.5 block text-sm font-medium text-gray-500 dark:text-gray-400">Label
                                Total</label>
                            <input type="text" name="label_total"
                                value="{{ old('label_total', $komponen->label_total) }}"
                                class="w-full rounded-lg border border-gray-300 bg-white dark:bg-gray-900 py-2.5 px-4 outline-none focus:border-blue-600 dark:border-gray-700 text-gray-700 dark:text-white/80 transition">
                        </div>
                        <div>
                            <label class="mb-2.5 block text-sm font-medium text-green-600 dark:text-green-500">Label
                                Tercapai</label>
                            <input type="text" name="label_tercapai"
                                value="{{ old('label_tercapai', $komponen->label_tercapai) }}"
                                class="w-full rounded-lg border border-gray-300 bg-white dark:bg-gray-900 py-2.5 px-4 outline-none focus:border-green-600 dark:border-gray-700 text-gray-700 dark:text-white/80 transition">
                        </div>
                        <div>
                            <label class="mb-2.5 block text-sm font-medium text-red-600 dark:text-red-500">Label Tidak
                                Tercapai</label>
                            <input type="text" name="label_tidak_tercapai"
                                value="{{ old('label_tidak_tercapai', $komponen->label_tidak_tercapai) }}"
                                class="w-full rounded-lg border border-gray-300 bg-white dark:bg-gray-900 py-2.5 px-4 outline-none focus:border-red-600 dark:border-gray-700 text-gray-700 dark:text-white/80 transition">
                        </div>
                    </div>
                </div>

                {{-- Daftar Task --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Daftar Task KPI</h3>
                        <button type="button" id="add-task-btn"
                            class="px-4 py-2 text-sm font-medium text-blue-600 border border-blue-600 rounded-lg hover:bg-blue-50 dark:border-blue-500/50 dark:text-blue-500 dark:hover:bg-blue-500/10 transition">
                            + Tambah Baris Task
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse" id="task-table">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400">
                                    <th class="py-3 px-4 font-medium text-sm w-16">No</th>
                                    <th class="py-3 px-4 font-medium text-sm">Nama Task</th>
                                    <th class="py-3 px-4 font-medium text-sm w-20 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="task-container">
                                @foreach ($komponen->tasks as $index => $task)
                                    <tr
                                        class="task-row border-b border-gray-100 dark:border-gray-800 transition hover:bg-gray-50/50 dark:hover:bg-white/[0.01]">
                                        <td class="py-4 px-4 text-sm text-gray-500 dark:text-gray-400 row-number">
                                            {{ $index + 1 }}</td>
                                        <td class="py-2 px-2">
                                            <input type="text" name="tasks[]" value="{{ $task->nama_task }}" required
                                                placeholder="Masukkan deskripsi task..."
                                                class="w-full border-b border-transparent focus:border-blue-500 focus:ring-0 bg-gray-100/50 dark:bg-transparent text-sm text-gray-800 dark:text-white/80 placeholder-gray-400 dark:placeholder-gray-500 py-2 px-3 rounded-md transition-all">
                                        </td>
                                        <td class="py-4 px-4 text-center">
                                            <button type="button"
                                                class="remove-task-btn text-gray-400 hover:text-red-600 dark:text-gray-600 dark:hover:text-red-500 transition">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex flex-col-reverse md:flex-row justify-end gap-3 mt-4">
                    <a href="{{ route('kpi.komponen.index') }}"
                        class="px-6 py-2 w-full md:w-fit text-sm text-center font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-6 py-2 w-full md:w-fit text-sm text-center font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        Perbarui Master KPI
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const taskContainer = document.getElementById('task-container');
            const addTaskBtn = document.getElementById('add-task-btn');

            function updateRowNumbers() {
                document.querySelectorAll('.row-number').forEach((td, index) => {
                    td.innerText = index + 1;
                });
            }

            addTaskBtn.addEventListener('click', function() {
                const newRow = `
                <tr class="task-row border-b border-gray-100 dark:border-gray-800 transition hover:bg-gray-50/50 dark:hover:bg-white/[0.01]">
                    <td class="py-4 px-4 text-sm text-gray-500 dark:text-gray-400 row-number"></td>
                    <td class="py-2 px-2">
                        <input type="text" name="tasks[]" required placeholder="Masukkan deskripsi task..."
                            class="w-full border-b border-transparent focus:border-blue-500 focus:ring-0 bg-gray-100/50 dark:bg-transparent text-sm text-gray-800 dark:text-white/80 placeholder-gray-400 dark:placeholder-gray-500 py-2 px-3 rounded-md transition-all">
                    </td>
                    <td class="py-4 px-4 text-center">
                        <button type="button" class="remove-task-btn text-gray-400 hover:text-red-600 dark:text-gray-600 dark:hover:text-red-500 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </td>
                </tr>`;
                taskContainer.insertAdjacentHTML('beforeend', newRow);
                updateRowNumbers();
            });

            taskContainer.addEventListener('click', function(e) {
                if (e.target.closest('.remove-task-btn')) {
                    const rows = document.querySelectorAll('.task-row');
                    if (rows.length > 1) {
                        e.target.closest('.task-row').remove();
                        updateRowNumbers();
                    } else {
                        alert('Minimal harus ada 1 task.');
                    }
                }
            });
        });

        $(document).ready(function() {
            $('#selectRole').select2({
                placeholder: "Pilih Role",
                theme: 'bootstrap4',
                allowClear: true,
                width: '100%'
            });
        });
    </script>
@endsection
