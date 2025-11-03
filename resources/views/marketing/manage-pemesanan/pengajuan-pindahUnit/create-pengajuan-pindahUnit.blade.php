@extends('layouts.app')

@section('pageActive', 'ManagePemesanan')

@section('content')

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb -->
        <div x-data="{ pageName: 'ManagePemesanan' }">
            @include('partials.breadcrumb')
        </div>

        <!-- Alert Error Validasi -->
        @if ($errors->any())
            <div class="flex p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400"
                role="alert">
                <svg class="shrink-0 inline w-4 h-4 me-3 mt-[2px]" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                    viewBox="0 0 20 20">
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

        <form action="" method="POST">
            @csrf

            {{-- akun user & unit dipesan --}}
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6 p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-6 border-b pb-1">
                    Akun User & Booking Unit
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-2">
                    {{-- Akun Customer --}}
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Akun Customer <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                            value="{{ $pemesanan->customer->username ?? '-' }} — {{ $pemesanan->customer->no_hp ?? '-' }}"
                            readonly
                            class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed shadow-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300">
                        <input type="hidden" name="user_id" value="{{ $pemesanan->customer->id ?? '' }}">
                    </div>

                    {{-- Tanggal Pemesanan --}}
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Tanggal Pemesanan <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="tanggal_pemesanan"
                            value="{{ $pemesanan->tanggal_pemesanan->format('Y-m-d') }}" readonly
                            class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed shadow-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                    {{-- Perumahaan --}}
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Perumahaan</label>
                        <input type="text" value="{{ $pemesanan->perumahaan->nama_perumahaan ?? '-' }}" readonly
                            class="w-full bg-gray-100 border border-gray-200 text-gray-500 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300">
                        <input type="hidden" name="perumahaan_id" value="{{ $pemesanan->perumahaan_id }}">
                    </div>

                    {{-- Tahap --}}
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Tahap</label>
                        <input type="text" value="{{ $pemesanan->tahap->nama_tahap ?? '-' }}" readonly
                            class="w-full bg-gray-100 border border-gray-200 text-gray-500 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300">
                        <input type="hidden" name="tahap_id" value="{{ $pemesanan->tahap_id }}">
                    </div>

                    {{-- Unit --}}
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Unit</label>
                        <input type="text" value="{{ $pemesanan->unit->nama_unit ?? '-' }}" readonly
                            class="w-full bg-gray-100 border border-gray-200 text-gray-500 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300">
                        <input type="hidden" name="unit_id" value="{{ $pemesanan->unit_id }}">
                    </div>
                </div>
            </div>

            {{-- pilih unit baru --}}
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
                <div class="px-5 py-4 sm:px-6 sm:py-5">
                    <h3
                        class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 border-b border-gray-100 dark:border-gray-800">
                        Pindah Unit Baru
                    </h3>

                    <div x-data="{
                        tahap: [],
                        unit: [],
                        async fetchTahap(perumahaanSlug) {
                            this.unit = [];
                            if (!perumahaanSlug) {
                                this.tahap = [];
                                return;
                            }

                            try {
                                const res = await fetch(`/etalase/perumahaan/${perumahaanSlug}/tahap-json`);
                                if (res.ok) {
                                    this.tahap = await res.json();
                                }
                            } catch (error) {
                                console.error('Gagal fetch tahap:', error);
                            }
                        },
                        async fetchUnit(tahapId) {
                            if (!tahapId) {
                                this.unit = [];
                                return;
                            }

                            try {
                                const currentUnit = '{{ $pemesanan->unit_id ?? '' }}';
                                const res = await fetch(`/etalase/tahap/${tahapId}/unit-json?current_unit_id=${currentUnit}`);
                                if (res.ok) {
                                    this.unit = await res.json();
                                }
                            } catch (error) {
                                console.error('Gagal fetch unit:', error);
                            }
                        }
                    }" class="grid grid-cols-1 md:grid-cols-3 gap-4">

                        <!-- 🏡 Select Perumahaan -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Perumahaan
                                Baru</label>
                            <select name="perumahaan_baru_id" required
                                @change="fetchTahap($event.target.options[$event.target.selectedIndex].dataset.slug)"
                                class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                    dark:bg-gray-700 dark:text-white
                    @error('perumahaan_baru_id') border-red-500 @else border-gray-300 @enderror">

                                <option value="">Pilih Perumahaan</option>
                                @foreach ($allPerumahaan as $p)
                                    <option value="{{ $p->id }}" data-slug="{{ $p->slug }}"
                                        {{ old('perumahaan_baru_id') == $p->id ? 'selected' : '' }}>
                                        {{ $p->nama_perumahaan }}
                                    </option>
                                @endforeach
                            </select>
                            @error('perumahaan_baru_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- 📍 Select Tahap -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tahap Baru</label>
                            <select name="tahap_baru_id" required @change="fetchUnit($event.target.value)"
                                class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                    dark:bg-gray-700 dark:text-white
                    @error('tahap_baru_id') border-red-500 @else border-gray-300 @enderror">

                                <option value="">Pilih Tahap</option>
                                <template x-for="t in tahap" :key="t.id">
                                    <option :value="t.id" x-text="t.nama_tahap"></option>
                                </template>
                            </select>
                            @error('tahap_baru_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- 🏠 Select Unit -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Unit Baru</label>
                            <select name="unit_baru_id" required
                                class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                    dark:bg-gray-700 dark:text-white
                    @error('unit_baru_id') border-red-500 @else border-gray-300 @enderror">

                                <option value="">Pilih Unit</option>
                                <template x-for="u in unit" :key="u.id">
                                    <option :value="u.id" x-text="u.nama_unit"></option>
                                </template>
                            </select>
                            @error('unit_baru_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex justify-end gap-2">
                <button type="button" onclick="history.back()"
                    class="px-8 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300
                dark:text-white dark:bg-gray-700 dark:hover:bg-gray-600">
                    Kembali
                </button>
                <button type="subemit"
                    class="px-8 py-2.5 text-sm font-medium text-white rounded-lg bg-yellow-500 hover:bg-yellow-600
                focus:outline-none focus:ring-4 focus:ring-yellow-300 dark:focus:ring-yellow-800">
                    Ajukan Pindah Unit
                </button>
            </div>
        </form>

    </div>

    {{-- js  --}}
    <script>
        document.addEventListener('alpine:init', () => {

        });
    </script>
@endsection
