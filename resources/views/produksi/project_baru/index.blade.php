@extends('layouts.app')

@section('pageActive', 'projectBaru')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">
<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-data="{}">
    @include('partials.breadcrumb', ['breadcrumbs' => [['label' => 'Proyek Baru Kontraktor', 'url' => route('produksi.projectBaru.index')]]])

    @if(session('success'))
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
    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: '{{ session('error') }}',
                    showConfirmButton: true
                });
            });
        </script>
    @endif

    <div class="flex flex-col gap-6 mt-6">
        <!-- Form Create -->
        @can('produksi.kontraktor.proyek-baru.create')
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-white">Buat Proyek Kontraktor Baru</h3>
            <form action="{{ route('produksi.projectBaru.store') }}" method="POST" id="form-create-project" x-data="{ submitting: false }" @submit="if(submitting) { $event.preventDefault(); return; }; submitting = true">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-data="{ simpanMulai: '{{ date('Y-m-d') }}', simpanSelesai: '', endPickerProject: null }">
                    <div>
                        <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Nama Proyek <span class="text-red-500">*</span></label>
                        <input type="text" name="nama" required class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Pengawas Proyek Mangoon</label>
                        <select name="pengawas_id" id="selectPengawas" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="">Pilih Pengawas</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->nama_lengkap ?? $user->name ?? $user->email }}</option>
                            @endforeach
                        </select>
                        <script>
                            $(document).ready(function() {
                                $('#selectPengawas').select2({
                                    placeholder: "Pilih Pengawas",
                                    theme: 'bootstrap4',
                                    width: '100%'
                                });
                            });
                        </script>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Tanggal Mulai</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none z-10">
                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0 2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" /></svg>
                            </div>
                            <input type="text" x-init="flatpickr($el, { dateFormat: 'd-m-Y', defaultDate: '{{ date('d-m-Y') }}', onChange: (selectedDates, dateStr, instance) => { simpanMulai = instance.formatDate(selectedDates[0], 'Y-m-d'); if(endPickerProject) { endPickerProject.set('minDate', selectedDates[0]); } } })" class="w-full pl-10 pr-3 py-2 text-gray-700 rounded-lg border border-gray-300 bg-gray-50 text-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white outline-none" placeholder="Pilih Tanggal Mulai">
                            <input type="hidden" name="tanggal_mulai" x-model="simpanMulai">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Tanggal Selesai</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none z-10">
                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0 2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" /></svg>
                            </div>
                            <input type="text" x-init="endPickerProject = flatpickr($el, { dateFormat: 'd-m-Y', minDate: '{{ date('d-m-Y') }}', onChange: (selectedDates, dateStr, instance) => { simpanSelesai = instance.formatDate(selectedDates[0], 'Y-m-d'); } })" class="w-full pl-10 pr-3 py-2 text-gray-700 rounded-lg border border-gray-300 bg-gray-50 text-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white outline-none" placeholder="Pilih Tanggal Selesai">
                            <input type="hidden" name="tanggal_selesai" x-model="simpanSelesai">
                        </div>
                    </div>

                    <div class="md:col-span-2 flex justify-end pt-2">
                        <button type="submit" :disabled="submitting" :class="submitting ? 'opacity-50 cursor-not-allowed' : ''" class="inline-flex items-center justify-center px-5 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-lg hover:bg-blue-700 transition shadow-sm">
                            <span x-text="submitting ? 'Memproses...' : 'Simpan Proyek'"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
        @endcan

        <!-- Tabel -->
        <div>
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-white">Daftar Proyek Kontraktor</h3>
                <div class="overflow-x-auto">
                    <table id="table-project-baru" class="w-full text-left text-sm text-gray-500 dark:text-gray-400" style="min-width: 680px;">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th class="px-6 py-3">Nama Proyek</th>
                                <th class="px-6 py-3">Pengawas</th>
                                <th class="px-6 py-3">Rentang Waktu</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($projects as $p)
                            <tr class="border-b bg-white dark:border-gray-700 dark:bg-gray-800">
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                                    <a href="{{ route('produksi.pembangunanProyek.show', $p->id) }}" class="hover:underline hover:text-blue-600 dark:hover:text-blue-400 cursor-pointer transition-colors">
                                        {{ $p->nama }}
                                    </a>
                                </td>
                                <td class="px-6 py-4">{{ $p->pengawas->nama_lengkap ?? $p->pengawas->name ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    {{ $p->tanggal_mulai ? \Carbon\Carbon::parse($p->tanggal_mulai)->format('d M Y') : '-' }} s/d {{ $p->tanggal_selesai ? \Carbon\Carbon::parse($p->tanggal_selesai)->format('d M Y') : 'Sekarang' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-[10px] font-bold rounded-md uppercase
                                        {{ $p->status_pembangunan == 'pending' ? 'bg-gray-100 text-gray-700' : ($p->status_pembangunan == 'proses' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700') }}">
                                        {{ $p->status_pembangunan }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        @if($p->status_pembangunan === 'pending')
                                            @can('produksi.kontraktor.proyek-baru.proses')
                                            <form action="{{ route('produksi.projectBaru.proses', $p->id) }}" method="POST" class="inline confirm-process-form">
                                                @csrf
                                                <button type="button" class="btn-process inline-flex items-center justify-center text-xs font-semibold text-green-700 bg-green-100 hover:bg-green-200 dark:bg-green-800/30 dark:text-green-300 px-2.5 py-1.5 rounded-md transition-all duration-200 focus:outline-none active:scale-95">Proses</button>
                                            </form>
                                            @endcan
                                            @can('produksi.kontraktor.proyek-baru.edit')
                                            <a href="{{ route('produksi.projectBaru.edit', $p->id) }}" class="inline-flex items-center justify-center text-xs font-semibold text-yellow-700 bg-yellow-100 hover:bg-yellow-200 dark:bg-yellow-800/30 dark:text-yellow-300 px-2.5 py-1.5 rounded-md transition-all duration-200 focus:outline-none active:scale-95">Edit</a>
                                            @endcan
                                            @can('produksi.kontraktor.proyek-baru.delete')
                                            <form action="{{ route('produksi.projectBaru.destroy', $p->id) }}" method="POST" class="inline confirm-delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn-delete inline-flex items-center justify-center text-xs font-semibold text-red-700 bg-red-100 hover:bg-red-200 dark:bg-red-800/30 dark:text-red-300 px-2.5 py-1.5 rounded-md transition-all duration-200 focus:outline-none active:scale-95">Hapus</button>
                                            </form>
                                            @endcan
                                        @else
                                            <span class="text-xs italic text-gray-400">Tidak ada aksi</span>
                                        @endif
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

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById("table-project-baru") && typeof simpleDatatables.DataTable !== 'undefined') {
            new simpleDatatables.DataTable("#table-project-baru", {
                searchable: true,
                sortable: true
            });
        }
    });

    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-delete')) {
            const form = e.target.closest('.confirm-delete-form');
            Swal.fire({
                title: 'Yakin hapus proyek ini?',
                text: "Data yang terhapus tidak dapat dikembalikan!",
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

        if (e.target.closest('.btn-process')) {
            const form = e.target.closest('.confirm-process-form');
            Swal.fire({
                title: 'Mulai proses proyek ini?',
                text: "Proyek akan masuk ke daftar pembangunan berjalan.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10B981',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Ya, Proses!',
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
