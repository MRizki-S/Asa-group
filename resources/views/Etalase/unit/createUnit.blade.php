@extends('layouts.app')

@section('pageActive', 'unitLayout')

@section('content')

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">

    <!-- ===== Main Content Start ===== -->
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: 'unitLayout' }">
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

        <form x-data="unitForm" method="POST" action="{{ route('unit.store', $perumahaan->slug) }}">
            @csrf

            {{-- inputan  --}}
            <div
                class="rounded-2xl border border-gray-200 shadow-sm bg-white/90 dark:border-gray-700 dark:bg-gray-900 mb-8">
                <div class="px-6 py-6 space-y-8">

                    <!-- Bagian Master Data -->
                    <fieldset class="space-y-1">
                        <legend class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            Master Data
                            <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
                                Informasi utama perumahaan & tahap
                            </span>
                        </legend>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Perumahaan -->
                            <div>
                                <label
                                    class="flex items-center gap-1 text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">
                                    Perumahaan
                                    <span class="text-red-500">*</span>
                                </label>
                                <select name="perumahaan_id" required
                                    class="pointer-events-none bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5
           dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="{{ $perumahaan->id }}" selected>{{ $perumahaan->nama_perumahaan }}
                                    </option>
                                </select>
                                @error('perumahaan_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Tahap -->
                            <div>
                                <label
                                    class="flex items-center gap-1 text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">
                                    Tahap
                                    <span class="text-red-500">*</span>
                                </label>
                                <select x-model="selectedTahap" name="tahap_id" required
                                    class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                                    dark:bg-gray-700 dark:text-white
                                    @error('tahap_id') border-red-500 @else border-gray-300 @enderror">
                                    <option value="">Pilih Tahap</option>
                                    @foreach ($tahapPerumahaan as $tahap)
                                        <option value="{{ $tahap->id }}">{{ $tahap->nama_tahap }}</option>
                                    @endforeach
                                </select>
                                @error('tahap_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>


                        </div>
                    </fieldset>

                    <!-- Bagian Relasi Tahap -->
                    <fieldset class="space-y--1">
                        <legend class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            Tahap – Blok & Tipe
                            <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
                                Pilih blok dan tipe unit yang sesuai dengan tahap
                            </span>
                        </legend>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Blok -->
                            <div>
                                <label
                                    class="flex items-center gap-1 text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">
                                    Blok
                                    <span class="text-red-500">*</span></label>
                                <select x-model="selectedBlok" name="blok_id" required
                                    class="select-blok w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                               dark:bg-gray-700 dark:text-white
                               @error('blok_id') border-red-500 @else border-gray-300 @enderror">
                                    <option value="">Pilih Blok</option>
                                    <template x-for="b in filteredBlok" :key="b.id">
                                        <option :value="b.id" x-text="b.nama_blok"></option>
                                    </template>
                                </select>
                                @error('blok_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Tipe Unit -->
                            <div>
                                <label
                                    class="flex items-center gap-1 text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">
                                    Tipe Unit
                                    <span class="text-red-500">*</span></label>
                                <select x-model="selectedType" name="type_id" required
                                    class="select-unit w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                                dark:bg-gray-700 dark:text-white
                                @error('type_id') border-red-500 @else border-gray-300 @enderror">
                                    <option value="">Pilih Tipe</option>
                                    <template x-for="t in filteredType" :key="t.id">
                                        <option :value="t.id"
                                            x-text="`${t.nama_type} — Rp${Number(t.harga_dasar).toLocaleString('id-ID')}`">
                                        </option>
                                    </template>
                                </select>
                                @error('type_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>


                            <!-- Input Nama Unit -->
                            <div class="md:col-span-2">
                                <label for="nama_unit"
                                    class="flex items-center gap-1 text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">
                                    Nama Unit <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="nama_unit" name="nama_unit" required
                                    placeholder="Contoh: A-1, AB-2, CC-3" value="{{ old('nama_unit') }}"
                                    class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                                dark:bg-gray-700 dark:text-white
                                @error('nama_unit') border-red-500 @else border-gray-300 @enderror" />
                                @error('nama_unit')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </fieldset>

                    <!-- Bagian Kualifikasi Dasar -->
                    <fieldset x-data="{ kualifikasi: '{{ old('kualifikasi_dasar') ?? '' }}' }" class="space-y-4">
                        <legend class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            Kualifikasi Dasar
                            <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
                                Pilih kualifikasi dasar dari unit tersebut
                            </span>
                        </legend>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Baris khusus Kualifikasi Dasar -->
                            <div>
                                <label
                                    class="flex items-center gap-1 text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">
                                    Kualifikasi Dasar <span class="text-red-500">*</span>
                                </label>
                                <select name="kualifikasi_dasar" x-model="kualifikasi" required
                                    class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                            dark:bg-gray-700 dark:text-white
                            @error('kualifikasi_dasar') border-red-500 @else border-gray-300 @enderror">
                                    <option value="">Pilih Kualifikasi</option>
                                    <option value="standar">Standar</option>
                                    <option value="kelebihan_tanah">Kelebihan Tanah</option>
                                </select>
                                @error('kualifikasi_dasar')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Luas & Nominal hanya muncul bila kelebihan_tanah -->
                            <template x-if="kualifikasi === 'kelebihan_tanah'">
                                <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Luas Kelebihan Tanah -->
                                    <div>
                                        <label for="luas_kelebihan"
                                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                            Luas Kelebihan Tanah
                                        </label>
                                        <input type="text" id="luas_kelebihan" name="luas_kelebihan"
                                            placeholder="Masukkan luas kelebihan tanah (contoh: 1 x 1)"
                                            value="{{ old('luas_kelebihan') }}"
                                            class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                               dark:bg-gray-700 dark:text-white
                               @error('luas_kelebihan') border-red-500 @else border-gray-300 @enderror" />
                                        @error('luas_kelebihan')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Nominal Kelebihan -->
                                    <div x-data="rupiahInput('')" class="w-full">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-white mb-1">
                                            Nominal Kelebihan (Rp)
                                            <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" x-model="display" @input="onInput($event)"
                                            placeholder="Masukkan nominal kelebihan"
                                            class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                                            dark:bg-gray-700 dark:text-white
                                            @error('nominal_kelebihan') border-red-500 @else border-gray-300 @enderror" />
                                        <input type="hidden" name="nominal_kelebihan" :value="value || 0" />
                                        @error('nominal_kelebihan')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                </div>
                            </template>
                        </div>
                    </fieldset>

                    <!-- Bagian Kualifikasi Posisi -->
                    <fieldset x-data="{
                        selectedKualifikasi: '{{ old('tahap_kualifikasi_id') ?? ($unit->tahap_kualifikasi_id ?? '') }}'
                    }" class="space-y-4">
                        <legend class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            Kualifikasi Posisi
                            <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
                                Pilih kualifikasi posisi dari unit tersebut
                            </span>
                        </legend>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Kualifikasi Posisi -->
                            <div>
                                <label
                                    class="flex items-center gap-1 text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">
                                    Kualifikasi Posisi <span class="text-red-500">*</span>
                                </label>

                                <select name="tahap_kualifikasi_id" x-model="selectedKualifikasi" required
                                    class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                       dark:bg-gray-700 dark:text-white
                       @error('tahap_kualifikasi_id') border-red-500 @else border-gray-300 @enderror">
                                    <option value="">Pilih Kualifikasi</option>
                                    <template x-for="k in filteredKualifikasi" :key="k.pivot.id">
                                        <option :value="k.pivot.id" :selected="k.pivot.id == selectedKualifikasi"
                                            x-text="`${k.nama_kualifikasi_blok} — Rp${Number(k.pivot.nominal_tambahan).toLocaleString('id-ID')}`">
                                        </option>
                                    </template>
                                </select>

                                @error('tahap_kualifikasi_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Info Tambahan SBUM -->
                            <div
                                class="mt-3 flex items-center gap-2 px-3 py-2 rounded-lg border border-blue-200 bg-blue-50
                       dark:bg-blue-900/30 dark:border-blue-700 animate-fade-in">
                                <div
                                    class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-500 text-white font-bold">
                                    +
                                </div>
                                <div>
                                    <p class="text-sm text-blue-700 dark:text-blue-300 font-medium">
                                        SBUM dari Pemerintah
                                    </p>
                                    <p class="text-xs text-blue-500 dark:text-blue-400">
                                        Tambahan harga: Rp <span>4.000.000</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                </div>

                <!-- Card Penjelasan Komponen Harga -->
                <div class="px-6 pb-8 mt-6">
                    <div
                        class="rounded-2xl border border-gray-200 bg-gray-50 dark:bg-gray-800 dark:border-gray-700 shadow-sm p-6 space-y-4">

                        <!-- Judul -->
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            Rincian Komponen Harga
                            <span class="text-sm font-normal text-gray-500 dark:text-gray-400">(informasi tambahan)</span>
                        </h3>

                        <!-- Daftar Penjelasan -->
                        <ul class="list-disc pl-5 text-sm text-gray-700 dark:text-gray-300 space-y-0 leading-relaxed">
                            <li><span class="font-medium">Harga Type</span> — nilai dasar unit berdasarkan tipe rumah yang
                                dipilih.</li>
                            <li><span class="font-medium">Kualifikasi Dasar</span> — tambahan biaya bila terdapat
                                <i>kelebihan tanah</i> dibandingkan ukuran standar.
                            </li>
                            <li><span class="font-medium">Kualifikasi Posisi</span> — tambahan harga sesuai posisi unit.
                            </li>
                            <li><span class="font-medium">SBUM Pemerintah</span> — penyesuaian biaya sesuai ketentuan
                                pemerintah.</li>
                        </ul>

                        <!-- Catatan Akhir -->
                        <p class="mt-4 text-sm text-gray-600 dark:text-gray-400 font-medium">
                            Total harga unit merupakan hasil penjumlahan otomatis dari seluruh komponen di atas.
                        </p>
                    </div>
                </div>

                <!-- Tombol Submit & Kembali -->
                @can('etalase.unit.create')
                    <div class="flex justify-end px-6 pb-6 gap-2">
                        <!-- Tombol Kembali -->
                        <button type="button" onclick="history.back()"
                            class="px-10 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-300 dark:text-white dark:bg-gray-700 dark:hover:bg-gray-600 dark:focus:ring-gray-600">
                            Kembali
                        </button>

                        <!-- Tombol Simpan -->
                        <button type="submit"
                            class="px-10 py-2.5 text-sm font-medium text-white rounded-lg bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800">
                            Simpan
                        </button>
                    </div>
                @endcan

            </div>

        </form>


    </div>
    <!-- ===== Main Content End ===== -->


    {{-- === Alpine.js Logic === --}}
    <script>
        $('.select-blok').select2({
            theme: 'bootstrap4', // penting!
            width: '100%' // biar full seperti w-full Tailwind
        });
        $('.select-unit').select2({
            theme: 'bootstrap4', // penting!
            width: '100%' // biar full seperti w-full Tailwind
        });

        document.addEventListener('alpine:init', () => {
            Alpine.data('unitForm', () => ({
                selectedTahap: '',
                selectedBlok: '',
                selectedType: '',
                selectedKualifikasi: '',
                blokAll: @json($blokPerumahaan),
                tahapAll: @json($tahapPerumahaan), // sudah include types + kualifikasiBlok

                get filteredBlok() {
                    if (!this.selectedTahap) return [];
                    return this.blokAll.filter(b => b.tahap_id === parseInt(this.selectedTahap));
                },

                get filteredType() {
                    if (!this.selectedTahap) return [];
                    const t = this.tahapAll.find(tt => tt.id === parseInt(this.selectedTahap));
                    return t ? t.types : [];
                },

                // 🔑 filter kualifikasi posisi
                get filteredKualifikasi() {
                    if (!this.selectedTahap) return [];
                    const t = this.tahapAll.find(tt => tt.id === parseInt(this.selectedTahap));
                    return t ? t.kualifikasi_blok : [];
                },
            }));
        });
    </script>
@endsection
