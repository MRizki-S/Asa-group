<!-- ===== Modal Create Periode Keuangan ===== -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<div id="modal-create" tabindex="-1" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex items-center justify-center w-full h-full overflow-y-auto overflow-x-hidden">

    <div class="relative w-full max-w-md p-4">
        <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">

            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Tambah Periode Keuangan
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
            <form id="simpleForm" action="{{ route('keuangan.periodeKeuangan.store') }}" method="POST"
                class="p-4 space-y-4">
                @csrf


                {{-- Nama Periode Keuangan --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white">Nama Periode</label>
                    <input type="text" name="nama_periode" value="{{ old('nama_periode') }}"
                        class="bg-gray-50 border text-gray-900 text-sm rounded-lg block w-full p-2.5
                   dark:bg-gray-600 dark:text-white dark:placeholder-gray-400
                   focus:ring-primary-600 focus:border-primary-600
                   @error('nama_periode') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                        placeholder="Masukan nama type">
                    @error('nama_periode')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>


                {{-- Tanggal Mulai --}}
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-white">
                        Tanggal Mulai
                    </label>

                    <div class="relative cursor-pointer" id="datepicker-container">
                        <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path
                                    d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                            </svg>
                        </div>

                        <input type="text" id="tanggal_mulai" name="tanggal_mulai" placeholder="Pilih Tanggal..."
                            class="flatpickr bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white cursor-pointer @error('tanggal_mulai') border-red-500 @enderror">
                    </div>
                </div>


                {{-- Tanggal Selesai --}}
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-white">
                        Tanggal Selesai
                    </label>

                    <div class="relative cursor-pointer" id="datepicker-container">
                        <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path
                                    d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                            </svg>
                        </div>

                        <input type="text" id="tanggal_selesai" name="tanggal_selesai" placeholder="Pilih Tanggal..."
                            class="flatpickr bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white cursor-pointer @error('tanggal_selesai') border-red-500 @enderror">
                    </div>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr("#tanggal_mulai", {
            dateFormat: "Y-m-d",
            defaultDate: "today",
            allowInput: true,
            // Membuat pop-up muncul saat kotak diklik
            disableMobile: "true"
        });

         flatpickr("#tanggal_selesai", {
            dateFormat: "Y-m-d",
            defaultDate: "today",
            allowInput: true,
            // Membuat pop-up muncul saat kotak diklik
            disableMobile: "true"
        });
    });
</script>
