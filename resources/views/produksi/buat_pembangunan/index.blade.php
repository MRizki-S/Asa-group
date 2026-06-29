@extends('layouts.app')

@section('pageActive', 'buatPembangunanKawasan')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">
<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-data="{}">
    @include('partials.breadcrumb', ['breadcrumbs' => [['label' => 'Buat Pembangunan Kawasan', 'url' => route('produksi.buatPembangunanKawasan.index')]]])

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800 dark:bg-gray-800 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-800 dark:bg-gray-800 dark:text-red-400">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
        <!-- Form Kiri -->
        <div class="lg:col-span-1">
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-white">Buat Pembangunan Kawasan Baru</h3>
                <form action="{{ route('produksi.buatPembangunanKawasan.store') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Nama Pembangunan <span class="text-red-500">*</span></label>
                            <input type="text" name="nama" required class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Perumahan <span class="text-red-500">*</span></label>
                            <select name="perumahaan_id" id="selectPerumahan" required class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <option value="">Pilih Perumahan</option>
                                @foreach($perumahaans as $perumahan)
                                    <option value="{{ $perumahan->id }}">{{ $perumahan->nama_perumahaan }}</option>
                                @endforeach
                            </select>
                            <script>
                                $(document).ready(function() {
                                    $('#selectPerumahan').select2({
                                        placeholder: "Pilih Perumahan",
                                        theme: 'bootstrap4',
                                        width: '100%'
                                    });
                                });
                            </script>
                        </div>
                        
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Pengawas Kawasan</label>
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
                            <div class="relative" x-data="{ simpan: '{{ date('Y-m-d') }}' }">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none z-10">
                                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" /></svg>
                                </div>
                                <input type="text" x-init="flatpickr($el, { dateFormat: 'd-m-Y', defaultDate: '{{ date('d-m-Y') }}', onChange: (selectedDates, dateStr, instance) => { simpan = instance.formatDate(selectedDates[0], 'Y-m-d'); } })" class="w-full pl-10 pr-3 py-2 text-gray-700 rounded-lg border border-gray-300 bg-gray-50 text-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white outline-none" placeholder="Pilih Tanggal Mulai">
                                <input type="hidden" name="tanggal_mulai" x-model="simpan">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Tanggal Selesai</label>
                            <div class="relative" x-data="{ simpan: '' }">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none z-10">
                                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" /></svg>
                                </div>
                                <input type="text" x-init="flatpickr($el, { dateFormat: 'd-m-Y', onChange: (selectedDates, dateStr, instance) => { simpan = instance.formatDate(selectedDates[0], 'Y-m-d'); } })" class="w-full pl-10 pr-3 py-2 text-gray-700 rounded-lg border border-gray-300 bg-gray-50 text-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white outline-none" placeholder="Pilih Tanggal Selesai">
                                <input type="hidden" name="tanggal_selesai" x-model="simpan">
                            </div>
                        </div>

                        <button type="submit" class="w-full rounded-lg bg-blue-700 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Simpan Pembangunan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel Kanan -->
        <div class="lg:col-span-2">
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-white">Daftar Pembangunan Kawasan</h3>
                <div class="overflow-x-auto">
                    <table id="table-pembangunan-kawasan" class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th class="px-6 py-3">Nama</th>
                                <th class="px-6 py-3">Perumahan</th>
                                <th class="px-6 py-3">Pengawas</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kawasans as $p)
                            <tr class="border-b bg-white dark:border-gray-700 dark:bg-gray-800">
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $p->nama }}</td>
                                <td class="px-6 py-4">{{ $p->perumahan->nama_perumahaan ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $p->pengawas->nama_lengkap ?? $p->pengawas->name ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-[10px] font-bold rounded-md uppercase 
                                        {{ $p->status_pembangunan == 'pending' ? 'bg-gray-100 text-gray-700' : ($p->status_pembangunan == 'proses' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700') }}">
                                        {{ $p->status_pembangunan }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        @if($p->status_pembangunan === 'pending')
                                            <form action="{{ route('produksi.buatPembangunanKawasan.proses', $p->id) }}" method="POST" class="inline confirm-process-form">
                                                @csrf
                                                <button type="button" class="btn-process rounded bg-green-500 px-3 py-1 text-xs font-bold text-white hover:bg-green-600">Proses</button>
                                            </form>
                                            <a href="{{ route('produksi.buatPembangunanKawasan.edit', $p->id) }}" class="inline-flex items-center justify-center rounded bg-yellow-500 px-3 py-1 text-xs font-bold text-white hover:bg-yellow-600">Edit</a>
                                            <form action="{{ route('produksi.buatPembangunanKawasan.destroy', $p->id) }}" method="POST" class="inline confirm-delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn-delete rounded bg-red-500 px-3 py-1 text-xs font-bold text-white hover:bg-red-600">Hapus</button>
                                            </form>
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
        if (document.getElementById("table-pembangunan-kawasan") && typeof simpleDatatables.DataTable !== 'undefined') {
            new simpleDatatables.DataTable("#table-pembangunan-kawasan", {
                searchable: true,
                sortable: true
            });
        }
    });

    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-delete')) {
            const form = e.target.closest('.confirm-delete-form');
            Swal.fire({
                title: 'Yakin hapus pembangunan ini?',
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
                title: 'Mulai proses pembangunan ini?',
                text: "Pembangunan akan masuk ke daftar pembangunan berjalan.",
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
