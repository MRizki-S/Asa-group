<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6 p-6"
    x-init="initSelect2()">

    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-6 border-b pb-1">
        Akun User & Booking Unit
    </h3>

    {{-- akun user dan tanggal pemesanan --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-2">
        {{-- Akun Customer --}}
        <div>
            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                Akun Customer <span class="text-red-500">*</span>
            </label>
            <select id="selectUser" name="user_id" required
                class="select-user w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5">
                <option value="">Pilih Akun Customer</option>
                <template x-for="c in customers" :key="c.id">
                    <option :value="c.id" x-text="c.username + ' — ' + c.no_hp"></option>
                </template>
            </select>
        </div>

        <!-- Tanggal Pemesanan -->
        <div>
            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                Tanggal Pemesanan <span class="text-red-500">*</span>
            </label>

            <div class="relative" x-data="{
                tampil: '{{ now()->format('d-m-Y') }}',
                simpan: '{{ now()->format('Y-m-d') }}'
            }">
                <!-- Icon Kalender -->
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                    </svg>
                </div>

                <!-- Input tampilan -->
                <input type="text" x-model="tampil" x-init="flatpickr($el, {
                    dateFormat: 'd-m-Y',
                    defaultDate: tampil,
                    onChange: (dates, dateStr) => {
                        tampil = dateStr;
                        // ubah ke format Y-m-d untuk dikirim
                        const d = dates[0];
                        simpan = d.getFullYear() + '-' +
                            ('0' + (d.getMonth() + 1)).slice(-2) + '-' +
                            ('0' + d.getDate()).slice(-2);
                    }
                })" placeholder="Pilih tanggal"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg
               focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5
               dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400
               dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">

                <!-- Input hidden format Y-m-d -->
                <input type="hidden" name="tanggal_pemesanan" x-model="simpan">
            </div>
        </div>


    </div>

    {{-- blok perumahaan, tahap, unit --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
        {{-- Perumahaan --}}
        <div>
            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Perumahaan</label>
            <input type="text" readonly placeholder="otomatis dari akun user yang dipilih"
                class="w-full bg-gray-50 border border-gray-200 text-gray-500 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300"
                :value="selectedCustomer?.booking?.nama_perumahaan ?? ''">
            <input type="hidden" name="perumahaan_id" :value="selectedCustomer?.booking?.perumahaan_id ?? ''">
        </div>

        {{-- Tahap --}}
        <div>
            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Tahap</label>
            <input type="text" readonly placeholder="otomatis dari akun user yang dipilih"
                class="w-full bg-gray-50 border border-gray-200 text-gray-500 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300"
                :value="selectedCustomer?.booking?.nama_tahap ?? ''">
            <input type="hidden" name="tahap_id" :value="selectedCustomer?.booking?.tahap_id ?? ''">
        </div>

        {{-- Unit --}}
        <div>
            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Unit</label>
            <input type="text" readonly placeholder="otomatis dari akun user yang dipilih"
                class="w-full bg-gray-50 border border-gray-200 text-gray-500  text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300"
                :value="selectedCustomer?.booking?.nama_unit ?? ''">
            <input type="hidden" name="unit_id" :value="selectedCustomer?.booking?.unit_id ?? ''">
        </div>
    </div>


    {{-- <div date-rangepicker class="flex items-center">
        <div class="relative">
            <input name="tanggal" type="text" datepicker datepicker-format="dd-mm-yyyy"
                class="border border-gray-300 text-sm rounded-lg p-2.5 w-full focus:ring-blue-500 focus:border-blue-500"
                placeholder="Pilih tanggal">
        </div>
    </div> --}}

</div>
