<!-- BAGIAN KPR -->
<div x-show="caraBayar === 'kpr'" x-transition
    class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">

    <div class="px-5 py-4 sm:px-6 sm:py-5">

        <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">
            <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                Cara Pembayaran (KPR)
            </h3>
        </div>

        <!-- BAGIAN ISI -->
        <div class="space-y-5">

            <!-- JUMLAH CICILAN -->
            <div>
                <label for="jumlah_cicilan" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Berapa Kali Angsur
                </label>
                <input type="text" id="jumlah_cicilan" name="jumlah_cicilan" x-model="jumlahCicilan" readonly
                    class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5
                        dark:bg-gray-800 dark:text-white dark:border-gray-600">
            </div>

            <!-- DAFTAR ANGSURAN -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Angsuran
                </label>

                <!-- ALERT DP -->
                <div x-show="minimalDp"
                    class="mb-3 text-sm text-yellow-800 dark:text-yellow-200 bg-yellow-50 dark:bg-yellow-900/40
                        border border-yellow-200 dark:border-yellow-800 rounded-lg px-4 py-2 transition-all duration-200">
                    Semua nominal dapat diganti, <strong>namun pembayaran pertama (DP)</strong> tidak boleh
                    kurang dari
                    <strong x-text="formatNumber(minimalDp) + ' (minimal DP)'"></strong>.
                </div>

                <!-- LIST ANGSURAN -->
                <template x-for="(angsuran, index) in angsuranList" :key="index">
                    <div
                        class="flex flex-col md:flex-row md:items-center gap-3 pb-4 mb-0 border-b border-gray-200
                            dark:border-gray-700 transition-all duration-150 hover:bg-gray-50
                            dark:hover:bg-gray-800/40 rounded-lg p-3">

                        <!-- PEMBAYARAN KE -->
                        <div class="w-full md:w-1/4">
                            <input type="hidden" name="pembayaran_ke[]" :value="index + 1">
                            <input type="text"
                                :value="index === 0 ?
                                    `Pembayaran ke - ${index + 1} / DP` :
                                    (index === angsuranList.length - 1 ?
                                        `Pembayaran ke - ${index + 1} / Pelunasan` :
                                        `Pembayaran ke - ${index + 1}`)"
                                readonly
                                class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5
                                    dark:bg-gray-800 dark:text-white dark:border-gray-600 cursor-not-allowed select-none">
                        </div>

                        <!-- TANGGAL PEMBAYARAN -->
                        <div class="relative w-full md:w-1/3">
                            <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                                </svg>
                            </div>

                            <input type="text" name="tanggal_angsuran[]" x-model="angsuran.tanggal"
                                x-init="flatpickr($el, {
                                    dateFormat: 'Y-m-d',
                                    defaultDate: angsuran.tanggal,
                                    onChange: (selectedDates, dateStr) => { angsuran.tanggal = dateStr }
                                })" placeholder="Pilih tanggal"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg
                                    focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5
                                    dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400
                                    dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        </div>

                        <!-- NOMINAL -->
                        <div class="w-full md:w-1/3">
                            <input type="text" x-model="angsuran.nominalFormatted" @input="formatNominal(index)"
                                placeholder="Masukkan nominal"
                                class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5
                                    dark:bg-gray-700 dark:text-white dark:border-gray-600">

                            <!-- Nilai asli dikirim ke server -->
                            <input type="hidden" name="nominal_angsuran[]" :value="angsuran.nominal">
                        </div>

                    </div>
                </template>
            </div>

        </div>
    </div>
</div>



<div x-show="caraBayar === 'cash' && hasSelected" x-transition
    class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
    <div class="px-5 py-4 sm:px-6 sm:py-5">

        <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">
            <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                Cara Pembayaran (CASH)
            </h3>
        </div>

        <!-- Tombol Pilihan Cash -->
        <div class="flex flex-wrap gap-3 mb-5">
            <template x-for="(cash, index) in caraBayarCash" :key="cash.id">
                <button type="button" @click="pilihCash(cash)"
                    :class="selectedCash === cash.nama_cara_bayar ?
                        'bg-blue-600 text-white border-blue-600' :
                        'bg-gray-100 text-gray-700 border-gray-300 hover:bg-blue-600 hover:text-white'"
                    class="px-4 py-1.5 text-sm font-medium rounded-md border transition-all duration-150 ease-in-out">
                    <span x-text="cash.nama_cara_bayar"></span>
                </button>
            </template>
        </div>

        <!-- Form hanya tampil kalau sudah pilih cash -->
        <template x-if="selectedCash">
            <div class="space-y-5">

                <!-- Berapa Kali Angsur -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Berapa Kali Angsur
                    </label>
                    <input type="text" x-model="jumlahCicilan" readonly
                        class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5">
                </div>

                <!-- Angsuran -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Angsuran
                    </label>

                    <div x-show="minimalDp"
                        class="mb-3 text-sm text-yellow-800 bg-yellow-50 border border-yellow-200 rounded-lg px-4 py-2">
                        Semua nominal dapat diganti, <strong>namun pembayaran pertama (DP)</strong> tidak boleh
                        kurang dari
                        <strong x-text="formatNumber(minimalDp) + ' (minimal DP)'"></strong>.
                    </div>

                    <template x-for="(angsuran, index) in angsuranList" :key="index">
                        <div
                            class="flex flex-col md:flex-row md:items-center gap-3 pb-4 border-b border-gray-200 rounded-lg p-3 hover:bg-gray-50 transition-all">

                            <!-- Pembayaran Ke -->
                            <div class="w-full md:w-1/4">
                                <input type="text"
                                    :value="index === 0 ?
                                        `Pembayaran ke - ${index + 1} / DP` :
                                        (index === angsuranList.length - 1 ?
                                            `Pembayaran ke - ${index + 1} / Pelunasan` :
                                            `Pembayaran ke - ${index + 1}`)"
                                    readonly class="w-full bg-gray-100 border border-gray-300 text-sm rounded-lg p-2.5">
                            </div>

                            <!-- Datepicker -->
                            <div class="w-full md:w-1/3 relative">
                                <input type="text" x-model="angsuran.tanggal" x-init="flatpickr($el, {
                                    dateFormat: 'Y-m-d',
                                    defaultDate: angsuran.tanggal,
                                    onChange: (selectedDates, dateStr) => { angsuran.tanggal = dateStr }
                                })"
                                    placeholder="Pilih tanggal"
                                    class="bg-gray-50 border border-gray-300 text-sm rounded-lg w-full ps-3 p-2.5">
                            </div>

                            <!-- Nominal Angsuran -->
                            <div class="w-full md:w-1/3">
                                <input type="text" x-model="angsuran.nominalFormatted" @input="formatNominal(index)"
                                    placeholder="Masukkan nominal"
                                    class="w-full bg-gray-50 border border-gray-300 text-sm rounded-lg p-2.5">
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>
</div>
