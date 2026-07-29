@extends('layouts.app')

@section('pageActive', 'buatPembangunanKawasan')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">
<div class="mx-auto max-w-[--breakpoint-md] p-4 md:p-6" x-data="{}">
    @include('partials.breadcrumb', ['breadcrumbs' => [
        ['label' => 'Buat Pembangunan Kawasan', 'url' => route('produksi.buatPembangunanKawasan.index')],
        ['label' => 'Edit', 'url' => '']
    ]])

    <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 mt-6">
        <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-white">Edit Pembangunan Kawasan</h3>
        <form action="{{ route('produksi.buatPembangunanKawasan.update', $kawasan->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Nama Pembangunan <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" value="{{ $kawasan->nama }}" required class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Perumahan <span class="text-red-500">*</span></label>
                    <select name="perumahaan_id" id="selectPerumahan" required class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Pilih Perumahan</option>
                        @foreach($perumahaans as $perumahan)
                            <option value="{{ $perumahan->id }}" {{ $kawasan->perumahaan_id == $perumahan->id ? 'selected' : '' }}>{{ $perumahan->nama_perumahaan }}</option>
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

                <div class="md:col-span-2 flex flex-row justify-end gap-3 pt-2">
                    <a href="{{ route('produksi.buatPembangunanKawasan.index') }}" class="w-1/2 sm:w-auto inline-flex items-center justify-center px-5 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-sm font-bold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition shadow-sm text-center">Batal</a>
                    <button type="submit" class="w-1/2 sm:w-auto inline-flex items-center justify-center px-5 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-lg hover:bg-blue-700 transition shadow-sm text-center">Update</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
