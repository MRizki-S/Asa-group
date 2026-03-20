@extends('layouts.app')

@section('pageActive', 'PengajuanPembangunan')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <div x-data="{ pageName: 'Tambah Pembangunan Unit' }">
            @include('partials.breadcrumb')
        </div>
        {{-- Alert Error Validasi --}}
        @if ($errors->any())
            <div class="flex p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400"
                role="alert">
                <svg class="shrink-0 inline w-4 h-4 me-3 mt-[2px]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                    fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                </svg>
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

        <form action="{{ route('produksi.pengajuanPembangunanUnit.store') }}" method="POST">
            @csrf

            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6"
                x-data="{
                    tahap: [],
                    units: [],
                    async fetchTahap(perumahaanSlug) {
                        if (!perumahaanSlug) { this.tahap = [];
                            this.units = []; return; }
                        const res = await fetch(`/etalase/perumahaan/${perumahaanSlug}/tahap-json`);
                        this.tahap = await res.json();
                        this.units = [];
                    },
                    async fetchUnit(tahapId) {
                        if (!tahapId) { this.units = []; return; }
                        const res = await fetch(`/etalase/tahap/${tahapId}/unit-json`);
                        this.units = await res.json();
                    }
                }">

                <div class="px-5 py-4 sm:px-6 sm:py-5">
                    <h3
                        class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 border-b-2 border-gray-100 dark:border-gray-800">
                        Informasi Pembangunan & QC
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-white">Perumahaan</label>
                            <select name="perumahaan_id" required
                                @change="fetchTahap($event.target.options[$event.target.selectedIndex].getAttribute('data-slug'))"
                                class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                                <option value="">Pilih Perumahaan</option>
                                @foreach ($allPerumahaan as $p)
                                @if ($p->id == $perumahanId)
                                    <option value="{{ $p->id }}" data-slug="{{ $p->slug }}"
                                        {{ old('perumahaan_id') == $p->id ? 'selected' : '' }}>
                                        {{ $p->nama_perumahaan }}
                                    </option>
                                @endif
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-white">Tahap</label>
                            <select name="tahap_id" required @change="fetchUnit($event.target.value)"
                                class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                                <option value="">Pilih Tahap</option>
                                <template x-for="t in tahap" :key="t.id">
                                    <option :value="t.id" x-text="t.nama_tahap"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-white">Unit</label>
                            <select name="unit_id" required
                                class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                                <option value="">Pilih Unit</option>
                                <template x-for="u in units" :key="u.id">
                                    <option :value="u.id" x-text="u.nama_unit"></option>
                                </template>
                            </select>
                        </div>

                        {{-- <div>
                            <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-white">Pengawas
                                Lapangan</label>
                            <select name="pengawas_id" required
                                class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                                <option value="">Pilih Pengawas</option>
                                @foreach ($allPengawas as $user)
                                    <option value="{{ $user->id }}"
                                        {{ old('pengawas_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->nama_lengkap }}
                                    </option>
                                @endforeach
                            </select>
                        </div> --}}
                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-white">Master QC
                                Container</label>
                            <select name="qc_container_id" required
                                class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Pilih Container QC</option>
                                @foreach ($allQcContainer as $qc)
                                    <option value="{{ $qc->id }}"
                                        {{ old('qc_container_id') == $qc->id ? 'selected' : '' }}>
                                        {{ $qc->nama_container }}
                                    </option>
                                @endforeach
                            </select>
                            @error('qc_container_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <h3
                        class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 mt-8 border-b-2 border-gray-100 dark:border-gray-800">
                        Tanggal Pembangunan
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-white">Tanggal
                                Mulai</label>
                            <input type="datetime-local" name="tanggal_mulai" required value="{{ old('tanggal_mulai') }}"
                                class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                            @error('tanggal_mulai')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-white">Estimasi
                                Selesai</label>
                            <input type="datetime-local" name="tanggal_selesai" required
                                value="{{ old('tanggal_selesai') }}"
                                class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                            @error('tanggal_selesai')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="flex justify-end px-6 pb-6 gap-2">
                    <button type="button" onclick="window.location.href='{{ route('produksi.pengajuanPembangunanUnit.index') }}'"
                        class="px-10 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 dark:text-white dark:bg-gray-700 dark:hover:bg-gray-600">
                        Kembali
                    </button>
                    <button type="submit"
                        class="px-10 py-2.5 text-sm font-medium text-white rounded-lg bg-blue-600 hover:bg-blue-700 shadow-md active:scale-95 transition-all">
                        Buat Pengajuan
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
