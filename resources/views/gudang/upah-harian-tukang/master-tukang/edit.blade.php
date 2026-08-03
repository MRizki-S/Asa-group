@extends('layouts.app')

@section('pageActive', 'MasterTukangHarian')

@section('content')

<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

    <!-- Breadcrumb -->
    <div x-data="{ pageName: 'MasterTukangHarian' }">
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

    <form action="{{ route('gudang.masterTukang.update', $tukang->id) }}" method="POST"
        x-data="masterTukangForm({
            nama_tukang: '{{ old('nama_tukang', $tukang->nama_tukang) }}',
            jenis_referensi: '{{ old('jenis_referensi', $tukang->jenis_referensi) }}',
            gaji_raw: '{{ old('gaji_harian_default', (int)$tukang->gaji_harian_default) }}',
            jam_kerja_default: {{ old('jam_kerja_default', $tukang->jam_kerja_default) }},
            status: {{ old('status', $tukang->status) ? 'true' : 'false' }}
        })">
        @csrf
        @method('PUT')
        
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
            <div class="px-5 py-4 sm:px-6 sm:py-5">
                <h3
                    class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">
                    Edit Master Tukang Harian
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Kode Tukang (Read-Only) --}}
                    <div>
                        <label for="kode" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Kode Tukang
                        </label>
                        <input type="text" id="kode" value="{{ $tukang->kode }}" readonly
                            class="w-full bg-gray-100 border border-gray-300 text-gray-500 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400 cursor-not-allowed">
                    </div>

                    {{-- Nama Tukang --}}
                    <div>
                        <label for="nama_tukang" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Nama Tukang <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="nama_tukang" name="nama_tukang" x-model="nama_tukang" required
                            placeholder="Masukkan nama tukang"
                            class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    {{-- Jenis Tukang --}}
                    <div>
                        <label for="jenis_referensi" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Jenis Tukang <span class="text-red-500">*</span>
                        </label>
                        <select id="jenis_referensi" name="jenis_referensi" x-model="jenis_referensi" required
                            class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Pilih Jenis Tukang</option>
                            <option value="perumahan">ABM</option>
                            <option value="mangoon">Mangoon</option>
                        </select>
                    </div>

                    {{-- Gaji Harian Default --}}
                    <div>
                        <label for="gaji_display" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Gaji Harian Default <span class="text-red-500">*</span>
                        </label>
                        <div class="flex">
                            <span class="inline-flex items-center rounded-l-lg border border-r-0 border-gray-300 bg-gray-100 px-3 text-sm text-gray-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400">
                                Rp
                            </span>
                            {{-- Input Tampilan Format --}}
                            <input type="text" id="gaji_display" x-model="gaji_display" @input="formatGaji($event)" required
                                placeholder="Masukkan nominal gaji"
                                class="w-full rounded-r-lg bg-gray-50 border border-gray-300 text-gray-900 text-sm p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        {{-- Input Riil yang dikirim ke Backend --}}
                        <input type="hidden" name="gaji_harian_default" x-model="gaji_raw">
                    </div>

                    {{-- Jam Kerja Default --}}
                    <div>
                        <label for="jam_kerja_default" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            Jam Kerja Default <span class="text-red-500">*</span>
                        </label>
                        <div class="flex">
                            <input type="number" id="jam_kerja_default" name="jam_kerja_default" x-model.number="jam_kerja_default" required min="1" max="24"
                                class="w-full rounded-l-lg bg-gray-50 border border-gray-300 text-gray-900 text-sm p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                            <span class="inline-flex items-center rounded-r-lg border border-l-0 border-gray-300 bg-gray-100 px-3 text-sm text-gray-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400">
                                Jam / Hari
                            </span>
                        </div>
                    </div>

                    {{-- Status Aktif --}}
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white font-semibold">
                            Status Aktif
                        </label>
                        <div class="mt-3">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="status" value="active" x-model="status" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300" x-text="status ? 'Aktif' : 'Non-Aktif'"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="flex justify-end gap-2">
            <a href="{{ route('gudang.masterTukang.index') }}"
                class="px-8 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 dark:text-white dark:bg-gray-700 dark:hover:bg-gray-600">
                Kembali
            </a>
            <button type="submit"
                class="px-8 py-2.5 text-sm font-medium text-white rounded-lg bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800">
                Simpan
            </button>
        </div>
    </form>
</div>

<script>
    function masterTukangForm(initialData = {}) {
        return {
            nama_tukang: initialData.nama_tukang || '',
            jenis_referensi: initialData.jenis_referensi || '',
            gaji_raw: initialData.gaji_raw || '',
            gaji_display: '',
            jam_kerja_default: initialData.jam_kerja_default || 8,
            status: initialData.status !== undefined ? initialData.status : true,

            init() {
                if (this.gaji_raw) {
                    this.gaji_display = new Intl.NumberFormat('id-ID').format(this.gaji_raw);
                }
            },

            formatGaji(e) {
                let value = e.target.value;
                // Hilangkan semua karakter kecuali angka
                let clean = value.replace(/\D/g, '');
                if (clean === '') {
                    this.gaji_raw = '';
                    this.gaji_display = '';
                    return;
                }
                this.gaji_raw = parseInt(clean, 10);
                // Format ke rupiah tanpa Rp
                this.gaji_display = new Intl.NumberFormat('id-ID').format(this.gaji_raw);
            }
        }
    }
</script>

@endsection
