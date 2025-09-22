    <div id="modal-create-tahapKualifikasi" tabindex="-1" aria-hidden="true"
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
                        data-modal-toggle="modal-create-tahapKualifikasi">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                        </svg>
                        <span class="sr-only">Close</span>
                    </button>
                </div>

                <!-- Body -->
                <form id="simpleForm" action="{{ route('tahapKualifikasi.store', ['tahap' => $tahap]) }}" {{-- ganti sesuai route penyimpanan --}}
                    method="POST" class="p-4 space-y-4">
                    @csrf

                    {{-- Kualifikasi Posisi --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-white">Kualifikasi Posisi</label>
                        <select name="kualifikasi_blok_id" required
                            class="bg-gray-50 border text-gray-900 text-sm rounded-lg block w-full p-2.5
                   dark:bg-gray-600 dark:text-white dark:placeholder-gray-400
                   focus:ring-primary-600 focus:border-primary-600
                   @error('kualifikasi_blok_id') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                            <option value="">Pilih Kualifikasi Posisi</option>
                            @foreach ($availableKualifikasiBlok as $tipe)
                                <option value="{{ $tipe->id }}">
                                    {{ $tipe->nama_kualifikasi_blok }}
                                </option>
                            @endforeach
                        </select>
                        @error('kualifikasi_blok_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div x-data="rupiahInput('')" class="w-full">
                        <label class="block text-sm font-medium text-gray-700 dark:text-white">Nominal Tambahaan</label>
                        <input type="text" x-model="display" @input="onInput($event)" placeholder="Nominal tambahan"
                            class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 border border-gray-300
                                                rounded-e-lg focus:ring-blue-500 focus:border-blue-500
                                                dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white
                                                dark:focus:border-blue-500" />

                        <!-- hidden: kirim 0 kalau kosong -->
                        <input type="hidden" name="nominal_tambahan" :value="value || 0" />
                    </div>



                    {{-- Tombol --}}
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" data-modal-toggle="modal-create-tahapKualifikasi"
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
