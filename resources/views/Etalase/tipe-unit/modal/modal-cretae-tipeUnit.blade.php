<!-- ===== Modal Create Tipe Unit ===== -->
<div id="modal-create" tabindex="-1" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex items-center justify-center w-full h-full overflow-y-auto overflow-x-hidden">

    <div class="relative w-full max-w-md p-4">
        <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">

            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Tambah Tipe Unit
                </h3>
                <button type="button"
                    class="text-gray-400 hover:text-gray-900 hover:bg-gray-200 rounded-lg w-8 h-8 flex items-center justify-center dark:hover:bg-gray-600 dark:hover:text-white"
                    data-modal-toggle="modal-create">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                    <span class="sr-only">Close</span>
                </button>
            </div>

            <!-- Body -->
            <form id="simpleForm" action="{{ route('tipe-unit.store') }}" {{-- ganti sesuai route penyimpanan --}} method="POST"
                class="p-4 space-y-4">
                @csrf

                {{-- Perumahaan --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white">Perumahaan</label>
                    <select name="perumahaan_id"
                        class="bg-gray-50 border text-gray-900 text-sm rounded-lg block w-full p-2.5
                   dark:bg-gray-600 dark:text-white dark:placeholder-gray-400
                   focus:ring-primary-600 focus:border-primary-600
                   @error('perumahaan_id') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                        <option value="">Pilih Perumahaan</option>
                        @foreach ($perumahaan as $p)
                            <option value="{{ $p->id }}" {{ old('perumahaan_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->nama_perumahaan }}
                            </option>
                        @endforeach
                    </select>
                    @error('perumahaan_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Nama Type --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white">Nama Type</label>
                    <input type="text" name="nama_type" value="{{ old('nama_type') }}"
                        class="bg-gray-50 border text-gray-900 text-sm rounded-lg block w-full p-2.5
                   dark:bg-gray-600 dark:text-white dark:placeholder-gray-400
                   focus:ring-primary-600 focus:border-primary-600
                   @error('nama_type') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                        placeholder="Masukan nama type">
                    @error('nama_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Luas Bangunan & Luas Tanah --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-white">Luas Bangunan</label>
                        <input type="number" step="0.01" name="luas_bangunan" value="{{ old('luas_bangunan') }}"
                            class="bg-gray-50 border text-gray-900 text-sm rounded-lg block w-full p-2.5
                       dark:bg-gray-600 dark:text-white dark:placeholder-gray-400
                       focus:ring-primary-600 focus:border-primary-600
                       @error('luas_bangunan') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                            placeholder="Masukan luas bangunan">
                        @error('luas_bangunan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-white">Luas Tanah</label>
                        <input type="number" step="0.01" name="luas_tanah" value="{{ old('luas_tanah') }}"
                            class="bg-gray-50 border text-gray-900 text-sm rounded-lg block w-full p-2.5
                       dark:bg-gray-600 dark:text-white dark:placeholder-gray-400
                       focus:ring-primary-600 focus:border-primary-600
                       @error('luas_tanah') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                            placeholder="Masukan luas tanah">
                        @error('luas_tanah')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Harga Dasar --}}
                {{-- <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white">Harga Unit</label>
                    <input type="number" name="harga_dasar" value="{{ old('harga_dasar') }}"
                        class="bg-gray-50 border text-gray-900 text-sm rounded-lg block w-full p-2.5
                   dark:bg-gray-600 dark:text-white dark:placeholder-gray-400
                   focus:ring-primary-600 focus:border-primary-600
                   @error('harga_dasar') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                        placeholder="Masukan harga unit">
                    @error('harga_dasar')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div> --}}

                {{-- Harga Dasar --}}
                <div x-data="rupiahInput('{{ old('harga_dasar') }}')">
                    <label class="block text-sm font-medium text-gray-700 dark:text-white">Harga Unit</label>

                    <!-- Input tampilan berformat Rupiah -->
                    <input type="text" x-model="display" @input="onInput" placeholder="Masukkan harga unit"
                        class="bg-gray-50 border text-gray-900 text-sm rounded-lg block w-full p-2.5
               dark:bg-gray-600 dark:text-white dark:placeholder-gray-400
               focus:ring-primary-600 focus:border-primary-600
               @error('harga_dasar') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">

                    <!-- Hidden input yang dikirim ke server sebagai angka murni -->
                    <input type="hidden" name="harga_dasar" x-model="value">

                    @error('harga_dasar')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>


                {{-- Tombol --}}
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" data-modal-toggle="modal-create"
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
