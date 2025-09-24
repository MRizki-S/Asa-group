@extends('layouts.app')

@section('pageActive', 'Perumahaan')

@section('content')
    <!-- ===== Main Content Start ===== -->
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: 'Perumahaan' }">
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
                <span class="sr-only">Danger</span>
                <div>
                    <span class="font-medium">Terjadi kesalahan validasi:</span>
                    <ul class="mt-1.5 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif


        {{-- update tahap --}}
        <form action="{{ route('tahap.update', ['perumahaan' => $perumahaan->slug, 'tahap' => $tahap->slug]) }}"
            method="POST">
            @csrf
            @method('PUT')

            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
                <div class="px-5 py-4 sm:px-6 sm:py-5">
                    <h3
                        class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 border-b-2 border-gray-100 dark:border-gray-800">
                        Tahap
                    </h3>
                    {{-- {{$perumahaan}} --}}
                    <div class="flex flex-col md:flex-row md:space-x-4 space-y-4 md:space-y-0">
                        <!-- Select -->
                        <div class="flex-1">
                            <label for="countries" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Perumahaan
                            </label>
                            <select name="perumahaan_id"
                                class="pointer-events-none bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="{{ $perumahaan->id }}" selected>{{ $perumahaan->nama_perumahaan }}</option>
                            </select>

                        </div>

                        <!-- Input -->
                        <div class="flex-1">
                            <label for="nama_tahap" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Nama Tahap
                            </label>
                            <input type="text" id="nama_tahap" name="nama_tahap" placeholder="Masukan nama tahap"
                                value="{{ $tahap->nama_tahap }}" required
                                class="bg-gray-50 border text-gray-900 text-sm rounded-lg block w-full p-2.5
                                focus:ring-blue-500 focus:border-blue-500
                                dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500
                            @error('nama_tahap') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror" />
                            <!-- Pesan error -->
                            @error('nama_tahap')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                </div>

                {{-- button simpan tahap  --}}
                {{-- button submit --}}
                <div class="flex justify-end mb-4 px-5">
                    <button type="submit"
                        class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-14 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Simpan</button>
                </div>
            </div>

        </form>
        {{-- end update tahap --}}

        {{-- tahap - tipe unit & tahap - kualifikasi posisi blok --}}
        <div class="grid grid-cols-1 md:grid-cols-[1fr_2fr] gap-4">

            {{-- Tahap - Tipe Unit --}}
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
                <div class="px-5 py-4 sm:px-6 sm:py-5">
                    <div class="mb-4 flex items-center justify-between">
                        <h3
                            class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 border-b-2 border-gray-100 dark:border-gray-800">
                            Tahap - Tipe Unit
                        </h3>
                        <a href="#" data-modal-target="modal-create-tahapTypeUnit"
                            data-modal-toggle="modal-create-tahapTypeUnit"
                            class="inline-block px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                            +
                        </a>
                    </div>

                    <div class="w-full overflow-x-auto">
                        <table id="table-tahapType" class="w-full table-auto">
                            <thead>
                                <tr>
                                    <th
                                        class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-left px-4 py-2">
                                        Tipe Unit
                                    </th>
                                    <th
                                        class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center px-4 py-2">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tahapType as $type)
                                    <tr class="border-t border-gray-200 dark:border-gray-700">
                                        <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white px-4 py-2">
                                            {{ $type->nama_type }}
                                        </td>
                                        <td class="px-6 py-4 flex flex-wrap gap-2 justify-center">

                                            <form
                                                action="
                                                {{ route('tahapType.destroy', $type->pivot->id) }}
                                                 "
                                                method="POST" class="delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" data-type="Tipe Unit"
                                                    class="delete-btn relative w-10 h-10  flex items-center justify-center rounded-full border border-red-500 bg-red-50 dark:bg-red-700 dark:border-red-600 hover:bg-red-500 hover:text-white transition-all duration-200 shadow-md">

                                                    <svg class="w-6 h-6 text-red-500 dark:text-red-100 hover:text-white transition-colors duration-200"
                                                        aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z" />
                                                    </svg>
                                                </button>


                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>


                </div>

            </div>

            {{-- Tahap - Kualifikasi Posisi --}}
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
                <div class="px-5 py-4 sm:px-6 sm:py-5">
                    <div class="mb-4 flex items-center justify-between">
                        <h3
                            class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 border-b-2 border-gray-100 dark:border-gray-800">
                            Tahap - Kualifikasi Posisi Blok
                        </h3>
                        <a href="#" data-modal-target="modal-create-tahapKualifikasi"
                            data-modal-toggle="modal-create-tahapKualifikasi"
                            class="inline-block px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                            +
                        </a>
                    </div>

                    <div class="flex flex-col md:flex-row md:space-x-4 space-y-4 md:space-y-0">
                        <table id="table-kualifikasiBlok" class="w-full table-auto">
                            <thead>
                                <tr>
                                    <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-start">
                                        Kualifikasi Posisi Blok
                                    </th>
                                    <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                                        Nominal Tambahan
                                    </th>
                                    <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tahapKualifikasi as $tk)
                                    <tr>
                                        <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            {{ $tk->nama_kualifikasi_blok }}</td>
                                        <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white text-center">
                                            Rp {{ number_format($tk->pivot->nominal_tambahan, 0, ',', '.') }}</td>
                                        <td class="px-6 py-4 flex flex-wrap gap-2 justify-center ">
                                            <a href="#" data-modal-target="modal-edit-tahapKualifikasi-{{$tk->pivot->id}}"
                                                data-modal-toggle="modal-edit-tahapKualifikasi-{{$tk->pivot->id}}"
                                                class="btn-edit inline-flex items-center gap-1
                                                text-xs font-medium text-yellow-700 bg-yellow-100 hover:bg-yellow-200
                                                dark:bg-yellow-800 dark:text-yellow-100 dark:hover:bg-yellow-700
                                                px-2.5 py-1.5 rounded-md transition-colors duration-200
                                                focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-1
                                                active:scale-95">Edit</a>
                                            {{-- {{$tk}} --}}
                                            <form
                                                action="
                                            {{ route('tahapKualifikasi.destroy', $tk->pivot->id) }}
                                             "
                                                method="POST" class="delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" data-type="Kualifikasi Posisi Blok"
                                                    class="delete-btn px-3 py-1 text-sm text-white bg-red-600 rounded hover:bg-red-700">
                                                    Delete
                                                </button>
                                            </form>
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
    <!-- ===== Main Content End ===== -->

    {{-- include modal --}}
    @include('Etalase.tahap.modal.modal-create-tahapType')
    @include('Etalase.tahap.modal.modal-create-tahapKualifikasi')
    {{-- @include('Etalase.tahap.modal.modal-edit-tahap-kualifikasi') --}}

    {{-- modal pop up edit tahap - Kualifikasi Posisi  --}}
    @foreach ($tahapKualifikasi as $data)
        <div id="modal-edit-tahapKualifikasi-{{$data->pivot->id}}" tabindex="-1" aria-hidden="true"
            class="hidden fixed inset-0 z-50 flex items-center justify-center w-full h-full overflow-y-auto overflow-x-hidden">

            <div class="relative w-full max-w-md p-4">
                <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">

                    <!-- Header -->
                    <div class="flex items-center justify-between p-4 border-b dark:border-gray-600">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Tambah Tahap - Tipe Unit
                        </h3>
                        <button type="button"
                            class="text-gray-400 hover:text-gray-900 hover:bg-gray-200 rounded-lg w-8 h-8 flex items-center justify-center dark:hover:bg-gray-600 dark:hover:text-white"
                            data-modal-toggle="modal-edit-tahapKualifikasi-{{$data->pivot->id}}">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                            </svg>
                            <span class="sr-only">Close</span>
                        </button>
                    </div>

                    <!-- Body -->
                    <form id="simpleForm" action="{{ route('tahapKualifikasi.update', $data->pivot->id) }}"
                        {{-- ganti sesuai route penyimpanan --}} method="POST" class="p-4 space-y-4">
                        @method('PUT')
                        @csrf

                        {{-- Kualifikasi Posisi --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-white">Kualifikasi
                                Posisi</label>
                            <select name="kualifikasi_blok_id" required
                                class="pointer-events-none bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"">
                                <option value="{{ $data->id }}" selected>{{ $data->nama_kualifikasi_blok }}</option>
                            </select>
                        </div>

                        {{-- Nominal Tambahan --}}
                        <div x-data="rupiahInput('{{ number_format($data->pivot->nominal_tambahan, 0, ',', '.') }}')" class="w-full">
                            <label class="block text-sm font-medium text-gray-700 dark:text-white">Nominal Tambahan</label>

                            <!-- input yang tampil rapi -->
                            <input type="text" x-model="display" @input="onInput($event)"
                                class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 border border-gray-300
                                rounded-e-lg focus:ring-blue-500 focus:border-blue-500
                                dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white
                                dark:focus:border-blue-500" />

                            <!-- hidden: kirim nilai murni, default 0 kalau kosong -->
                            <input type="hidden" name="nominal_tambahan" :value="value || 0" />
                        </div>




                        {{-- Tombol --}}
                        <div class="flex justify-end gap-3 mt-6">
                            <button type="button" data-modal-toggle="modal-edit-tahapKualifikasi-{{$data->pivot->id}}"
                                class="px-4 py-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-100">
                                Batal
                            </button>
                            <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
                                Simpan
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endforeach


    {{-- sweatalert 2 for delete data --}}
    <script>
        document.addEventListener('click', function(e) {
            if (e.target.closest('.delete-btn')) {
                const btn = e.target.closest('.delete-btn');
                const form = btn.closest('.delete-form');

                const type = btn.dataset.type || 'item';

                Swal.fire({
                    title: 'Yakin hapus data ini?',
                    html: `Apakah anda yakin menghapus Hubungan Antara <br>  Tahap - ${type} ini?`,
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
        });
    </script>
@endsection
