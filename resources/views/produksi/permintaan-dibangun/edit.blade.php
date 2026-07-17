@extends('layouts.app')

@section('pageActive', 'PengajuanPembangunan')

@section('content')
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <div x-data="{ pageName: 'Edit Pembangunan Unit' }">
            @include('partials.breadcrumb')
        </div>

        <form action="{{ route('produksi.pengajuanPembangunanUnit.update', $pembangunan->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">

                <div class="px-5 py-4 sm:px-6 sm:py-5">
                    <h3
                        class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 border-b-2 border-gray-100 dark:border-gray-800">
                        Lokasi & Pengawas
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-white">Perumahaan</label>
                            <input type="text" readonly value="{{ $pembangunan->perumahaan->nama_perumahaan ?? 'N/A' }}"
                                class="w-full bg-gray-100 border border-gray-300 text-gray-500 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400 cursor-not-allowed outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-white">Tahap</label>
                            <input type="text" readonly value="{{ $pembangunan->tahap->nama_tahap ?? 'N/A' }}"
                                class="w-full bg-gray-100 border border-gray-300 text-gray-500 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400 cursor-not-allowed outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-white">Unit</label>
                            <input type="text" readonly value="{{ $pembangunan->unit->nama_unit ?? 'N/A' }}"
                                class="w-full bg-gray-100 border border-gray-300 text-gray-500 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400 cursor-not-allowed outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-white">SPV Drafting, Teknis & Estimasi</label>
                            <input type="text" readonly value="{{ $pembangunan->spv->nama_lengkap ?? '-' }}"
                                class="w-full bg-gray-100 border border-gray-300 text-gray-500 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400 cursor-not-allowed outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-white">Pengawas</label>
                            <select name="pengawas_id" required id="selectPengawas"
                                class="w-full text-gray-800 bg-gray-50 border border-gray-300 text-sm rounded-lg p-2.5 dark:bg-gray-600 dark:text-white">
                                <option value="">Pilih Pengawas</option>
                                @foreach ($allPengawas as $user)
                                    <option value="{{ $user->id }}"
                                        {{ $pembangunan->pengawas_id == $user->id ? 'selected' : '' }}>
                                        {{ $user->nama_lengkap }}
                                    </option>
                                @endforeach
                            </select>
                            <script>
                                $(document).ready(function() {
                                    $('#selectPengawas').select2({
                                        placeholder: "-- Pilih Pengawas --",
                                        theme: 'bootstrap4',
                                        allowClear: true,
                                        width: '100%'
                                    });
                                });
                            </script>
                        </div>
                    </div>

                    <h3
                        class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 mt-8 border-b-2 border-gray-100 dark:border-gray-800">
                        Detail Pembangunan & QC
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-white">Master QC Container</label>
                            <input type="text" readonly value="{{ $pembangunan->qcContainer->nama_container ?? '-' }}"
                                class="w-full bg-gray-100 border border-gray-300 text-gray-500 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400 cursor-not-allowed outline-none">
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Tanggal Mulai <span class="text-red-500">*</span>
                            </label>
                            <div class="relative" x-data="{
                                simpan: '{{ \Carbon\Carbon::parse($pembangunan->tanggal_mulai)->format('Y-m-d') }}'
                            }">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path
                                            d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                                    </svg>
                                </div>

                                <input type="text" x-init="flatpickr($el, {
                                    dateFormat: 'd-m-Y',
                                    defaultDate: '{{ \Carbon\Carbon::parse($pembangunan->tanggal_mulai)->format('d-m-Y') }}',
                                    onChange: (selectedDates, dateStr, instance) => {
                                        simpan = instance.formatDate(selectedDates[0], 'Y-m-d');
                                    }
                                })"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white outline-none"
                                    placeholder="Pilih tanggal mulai">

                                <input type="hidden" name="tanggal_mulai" x-model="simpan">
                            </div>
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Estimasi Selesai <span class="text-red-500">*</span>
                            </label>
                            <div class="relative" x-data="{
                                simpan: '{{ \Carbon\Carbon::parse($pembangunan->tanggal_selesai)->format('Y-m-d') }}'
                            }">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path
                                            d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                                    </svg>
                                </div>

                                <input type="text" x-init="flatpickr($el, {
                                    dateFormat: 'd-m-Y',
                                    defaultDate: '{{ \Carbon\Carbon::parse($pembangunan->tanggal_selesai)->format('d-m-Y') }}',
                                    onChange: (selectedDates, dateStr, instance) => {
                                        simpan = instance.formatDate(selectedDates[0], 'Y-m-d');
                                    }
                                })"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white outline-none"
                                    placeholder="Pilih tanggal selesai">

                                <input type="hidden" name="tanggal_selesai" x-model="simpan">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end px-6 pb-6 gap-2">
                    <button type="button"
                        onclick="window.location.href='{{ route('produksi.pengajuanPembangunanUnit.index') }}'"
                        class="px-10 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Batal</button>
                    <button type="submit"
                        class="px-10 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-md">Simpan
                        Perubahan</button>
                </div>
            </div>
        </form>
    </div>
@endsection
