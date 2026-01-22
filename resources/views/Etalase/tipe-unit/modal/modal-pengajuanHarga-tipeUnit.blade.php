<!-- ===== Modal Pengajuan Perubahaan Harga  ===== -->
@foreach ($tipeUnits as $item)
    <div id="modal-pengajuanHarga-{{ $item->slug }}" tabindex="-1" aria-hidden="true"
        class="hidden fixed inset-0 z-50 flex items-center justify-center w-full h-full overflow-y-auto overflow-x-hidden">

        <div class="relative w-full max-w-md p-4">
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">

                <!-- Header -->
                <div class="flex items-center justify-between p-4 border-b dark:border-gray-600">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Pengajuan Perubahaan Harga
                    </h3>
                    <button type="button"
                        class="text-gray-400 hover:text-gray-900 hover:bg-gray-200 rounded-lg w-8 h-8 flex items-center justify-center dark:hover:bg-gray-600 dark:hover:text-white"
                        data-modal-toggle="modal-pengajuanHarga-{{ $item->slug }}">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="webm1 1- 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                        </svg>
                        <span class="sr-only">Close</span>
                    </button>
                </div>

                <!-- Body -->
                <form id="editForm" action="{{ route('tipe-unit.ajukanHarga', $item->slug) }}" method="POST" class="p-4 space-y-4">
                    @csrf
                    @method('PUT')

                    {{-- Nama Type --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-white">
                            Nama Type
                        </label>
                        <input type="text" name="nama_type" id="edit_nama_type"
                            class="bg-gray-50 border text-gray-900 text-sm rounded-lg block w-full p-2.5
                   dark:bg-gray-600 dark:text-white dark:placeholder-gray-400
                   focus:ring-primary-600 focus:border-primary-600"
                            value="{{ $item->nama_type }}" placeholder="Masukkan nama type">
                    </div>

                  {{-- HARGA SAAT INI --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-white">
                            Harga Saat Ini
                        </label>
                        <input type="text"
                            class="bg-gray-100 border text-gray-900 text-sm rounded-lg block w-full p-2.5
                   dark:bg-gray-700 dark:text-gray-300 cursor-not-allowed"
                            value="{{ number_format($item->harga_dasar, 0, ',', '.') }}" readonly>
                    </div>

                   {{-- PENGAJUAN PERUBAHAN HARGA --}}
                    <div class="pt-3 border-t border-gray-200 dark:border-gray-600">
                        <label class="block text-sm font-medium text-gray-700 dark:text-white mb-1">
                            Ajukan Harga Baru
                        </label>

                        <div x-data="rupiahInput('{{ old('harga_diajukan') }}')">
                            <input type="text" x-model="display" @input="onInput" placeholder="Contoh: 250.000.000"
                                class="bg-white border text-gray-900 text-sm rounded-lg block w-full p-2.5
                       dark:bg-gray-600 dark:text-white dark:placeholder-gray-400
                       focus:ring-blue-600 focus:border-blue-600">

                            {{-- nilai asli tanpa titik, yang dikirim ke backend --}}
                            <input type="hidden" name="harga_diajukan" :value="value">
                        </div>

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Pengajuan perubahan harga akan diproses melalui approval Manager Dukungan & Layanan.
                        </p>
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" data-modal-toggle="modal-pengajuanHarga-{{ $item->slug }}"
                            class="px-4 py-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-100">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
                            Ajukan Perubahaan
                        </button>
                    </div>
                </form>


            </div>
        </div>
    </div>
@endforeach
