@extends('layouts.app')

@section('pageActive', 'PengajuanPembangunan')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <div x-data="{ pageName: 'Edit Pembangunan Unit' }">
            @include('partials.breadcrumb')
        </div>

        <form action="{{ route('produksi.pengajuanPembangunanUnit.update', $pembangunan->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6"
                x-data="{
                    tahap: [],
                    units: [],
                    selectedTahap: '{{ $pembangunan->tahap_id }}',
                    selectedUnit: '{{ $pembangunan->unit_id }}',

                    async fetchTahap(perumahaanSlug) {
                        if (!perumahaanSlug) return;
                        const res = await fetch(`/etalase/perumahaan/${perumahaanSlug}/tahap-json`);
                        this.tahap = await res.json();
                    },
                    async fetchUnit(tahapId) {
                        if (!tahapId) return;
                        const res = await fetch(`/etalase/tahap/${tahapId}/unit-json`);
                        this.units = await res.json();
                    }
                }" x-init="await fetchTahap('{{ $pembangunan->perumahaan->slug }}');
                await fetchUnit('{{ $pembangunan->tahap_id }}');">

                <div class="px-5 py-4 sm:px-6 sm:py-5">
                    <h3
                        class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 border-b-2 border-gray-100 dark:border-gray-800">
                        Lokasi & Pengawas
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-white">Perumahaan</label>
                            <select name="perumahaan_id" required
                                @change="fetchTahap($event.target.options[$event.target.selectedIndex].getAttribute('data-slug'))"
                                class="w-full text-gray-800 bg-gray-50 border border-gray-300 text-sm rounded-lg p-2.5 dark:bg-gray-600 dark:text-white">
                                @foreach ($allPerumahaan as $p)
                                    <option value="{{ $p->id }}" data-slug="{{ $p->slug }}"
                                        {{ $pembangunan->perumahaan_id == $p->id ? 'selected' : '' }}>
                                        {{ $p->nama_perumahaan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-white">Tahap</label>
                            <select name="tahap_id" required x-model="selectedTahap"
                                @change="fetchUnit($event.target.value)"
                                class="w-full text-gray-800 bg-gray-50 border border-gray-300 text-sm rounded-lg p-2.5 dark:bg-gray-600 dark:text-white">
                                <template x-for="t in tahap" :key="t.id">
                                    <option :value="t.id" x-text="t.nama_tahap" :selected="t.id == selectedTahap">
                                    </option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-white">Unit</label>
                            <select name="unit_id" required x-model="selectedUnit"
                                class="w-full text-gray-800 bg-gray-50 border border-gray-300 text-sm rounded-lg p-2.5 dark:bg-gray-600 dark:text-white">
                                <template x-for="u in units" :key="u.id">
                                    <option :value="u.id" x-text="u.nama_unit" :selected="u.id == selectedUnit">
                                    </option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-white">Pengawas</label>
                            <select name="pengawas_id" required
                                class="w-full text-gray-800 bg-gray-50 border border-gray-300 text-sm rounded-lg p-2.5 dark:bg-gray-600 dark:text-white">
                                <option value="">Pilih Pengawas</option>
                                @foreach ($allPengawas as $user)
                                    <option value="{{ $user->id }}"
                                        {{ $pembangunan->pengawas_id == $user->id ? 'selected' : '' }}>
                                        {{ $user->nama_lengkap }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <h3
                        class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 mt-8 border-b-2 border-gray-100 dark:border-gray-800">
                        Detail Pembangunan & QC
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-white">Master QC
                                Container</label>
                            <select name="qc_container_id" required
                                class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white focus:ring-blue-500 focus:border-blue-500 outline-none">
                                @foreach ($allQcContainer as $qc)
                                    <option value="{{ $qc->id }}"
                                        {{ $pembangunan->qc_container_id == $qc->id ? 'selected' : '' }}>
                                        {{ $qc->nama_container }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-white">Tanggal
                                Mulai</label>
                            <input type="date" name="tanggal_mulai" required
                                value="{{ \Carbon\Carbon::parse($pembangunan->tanggal_mulai)->format('Y-m-d') }}"
                                class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-white">Estimasi
                                Selesai</label>
                            <input type="date" name="tanggal_selesai" required
                                value="{{ \Carbon\Carbon::parse($pembangunan->tanggal_selesai)->format('Y-m-d') }}"
                                class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end px-6 pb-6 gap-2">
                    <button type="button" onclick="window.location.href='{{ route('produksi.pengajuanPembangunanUnit.index') }}'"
                        class="px-10 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Batal</button>
                    <button type="submit"
                        class="px-10 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-md">Simpan
                        Perubahan</button>
                </div>
            </div>
        </form>
    </div>
@endsection
