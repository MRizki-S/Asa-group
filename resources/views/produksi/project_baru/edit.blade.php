@extends('layouts.app')

@section('pageActive', 'projectBaru')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">
<div class="mx-auto max-w-[--breakpoint-md] p-4 md:p-6" x-data="{}">
    @include('partials.breadcrumb', ['breadcrumbs' => [
        ['label' => 'Project Baru Kontraktor', 'url' => route('produksi.projectBaru.index')],
        ['label' => 'Edit', 'url' => '']
    ]])

    <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 mt-6">
        <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-white">Edit Project Kontraktor</h3>
        <form action="{{ route('produksi.projectBaru.update', $project->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Nama Project <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" value="{{ $project->nama }}" required class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Pengawas Unit</label>
                    <select name="pengawas_id" id="selectPengawas" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Pilih Pengawas</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ $project->pengawas_id == $user->id ? 'selected' : '' }}>{{ $user->nama_lengkap ?? $user->name ?? $user->email }}</option>
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
                    <div class="relative" x-data="{ simpan: '{{ $project->tanggal_mulai ? \Carbon\Carbon::parse($project->tanggal_mulai)->format('Y-m-d') : '' }}' }">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none z-10">
                            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" /></svg>
                        </div>
                        <input type="text" x-init="flatpickr($el, { dateFormat: 'd-m-Y', defaultDate: '{{ $project->tanggal_mulai ? \Carbon\Carbon::parse($project->tanggal_mulai)->format('d-m-Y') : '' }}', onChange: (selectedDates, dateStr, instance) => { simpan = instance.formatDate(selectedDates[0], 'Y-m-d'); } })" class="w-full pl-10 pr-3 py-2 text-gray-700 rounded-lg border border-gray-300 bg-gray-50 text-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white outline-none" placeholder="Pilih Tanggal Mulai">
                        <input type="hidden" name="tanggal_mulai" x-model="simpan">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Tanggal Selesai</label>
                    <div class="relative" x-data="{ simpan: '{{ $project->tanggal_selesai ? \Carbon\Carbon::parse($project->tanggal_selesai)->format('Y-m-d') : '' }}' }">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none z-10">
                            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" /></svg>
                        </div>
                        <input type="text" x-init="flatpickr($el, { dateFormat: 'd-m-Y', defaultDate: '{{ $project->tanggal_selesai ? \Carbon\Carbon::parse($project->tanggal_selesai)->format('d-m-Y') : '' }}', onChange: (selectedDates, dateStr, instance) => { simpan = instance.formatDate(selectedDates[0], 'Y-m-d'); } })" class="w-full pl-10 pr-3 py-2 text-gray-700 rounded-lg border border-gray-300 bg-gray-50 text-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white outline-none" placeholder="Pilih Tanggal Selesai">
                        <input type="hidden" name="tanggal_selesai" x-model="simpan">
                    </div>
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="submit" class="flex-1 rounded-lg bg-blue-700 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Update Project</button>
                    <a href="{{ route('produksi.projectBaru.index') }}" class="flex-1 rounded-lg bg-gray-500 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-gray-600 focus:outline-none focus:ring-4 focus:ring-gray-300 dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800">Batal</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
