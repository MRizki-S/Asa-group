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
            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Pemesanan
                <span class="text-red-500">*</span></label>
            <input type="date" name="tanggal_pemesanan" value="{{ now()->toDateString() }}" readonly
                class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed shadow-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300">
        </div>
    </div>

    {{-- blok perumahaan, tahap, unit --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
        {{-- Perumahaan --}}
        <div>
            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Perumahaan</label>
            <input type="text" readonly placeholder="otomatis dari akun user yang dipilih"
                class="w-full bg-gray-50 border border-gray-200 text-gray-500 text-sm rounded-lg p-2.5 cursor-not-allowed"
                :value="selectedCustomer?.booking?.nama_perumahaan ?? ''">
            <input type="hidden" name="perumahaan_id" :value="selectedCustomer?.booking?.perumahaan_id ?? ''">
        </div>

        {{-- Tahap --}}
        <div>
            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Tahap</label>
            <input type="text" readonly placeholder="otomatis dari akun user yang dipilih"
                class="w-full bg-gray-50 border border-gray-200 text-gray-500 text-sm rounded-lg p-2.5 cursor-not-allowed"
                :value="selectedCustomer?.booking?.nama_tahap ?? ''">
            <input type="hidden" name="tahap_id" :value="selectedCustomer?.booking?.tahap_id ?? ''">
        </div>

        {{-- Unit --}}
        <div>
            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Unit</label>
            <input type="text" readonly placeholder="otomatis dari akun user yang dipilih"
                class="w-full bg-gray-50 border border-gray-200 text-gray-500  text-sm rounded-lg p-2.5 cursor-not-allowed"
                :value="selectedCustomer?.booking?.nama_unit ?? ''">
            <input type="hidden" name="unit_id" :value="selectedCustomer?.booking?.unit_id ?? ''">
        </div>
    </div>
</div>
