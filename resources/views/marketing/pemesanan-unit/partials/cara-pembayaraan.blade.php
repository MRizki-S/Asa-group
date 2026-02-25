<!-- BAGIAN KPR -->
<div x-show="caraBayar === 'kpr' && hasSelected" x-transition
    class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">

    <div class="px-5 py-4 sm:px-6 sm:py-5">

        <!-- HEADER -->
        <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">
            <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                Cara Pembayaran (KPR)
            </h3>
        </div>

        <!-- TOMBOL PILIHAN KPR -->
        <div class="flex flex-wrap gap-3 mb-5">
            <template x-for="(kpr, index) in caraBayarKpr" :key="kpr.id">
                <button type="button" @click="pilihKpr(kpr)"
                    :class="selectedKpr?.id === kpr.id ?
                        'bg-blue-600 text-white border-blue-600' :
                        'bg-gray-100 text-gray-700 border-gray-300 hover:bg-blue-600 hover:text-white'"
                    class="px-4 py-1.5 text-sm font-medium rounded-md border transition-all duration-150 ease-in-out">
                    <span x-text="kpr.nama_cara_bayar"></span>
                </button>
            </template>
        </div>

        <!-- FORM KPR -->
        <template x-if="selectedKpr">
            <div class="space-y-5">

                <!-- kirim id kpr ke backend -->
                <input type="hidden" name="cara_bayar_id" :value="selectedKpr.id">

                <!-- JUMLAH CICILAN -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Berapa Kali Angsur
                    </label>
                    <input type="text" x-model="jumlahCicilan" readonly
                        class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5">

                    <input type="hidden" name="jumlah_cicilan" :value="jumlahCicilan">
                </div>

                <!-- ANGSURAN -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Angsuran
                    </label>

                    <!-- ALERT DP -->
                    <div x-show="minimalDp"
                        class="mb-3 text-sm text-yellow-800 dark:text-yellow-200 bg-yellow-50 dark:bg-yellow-900/40
                               border border-yellow-200 dark:border-yellow-800 rounded-lg px-4 py-2">
                        Semua nominal dapat diganti,
                        <strong>namun pembayaran pertama (DP)</strong> tidak boleh kurang dari
                        <strong x-text="formatNumber(minimalDp) + ' (minimal DP)'"></strong>.
                    </div>

                    <!-- LIST ANGSURAN -->
                    <template x-for="(angsuran, index) in angsuranList" :key="index">
                        <div
                            class="flex flex-col md:flex-row md:items-center gap-3 pb-4 border-b border-gray-200
                                   dark:border-gray-700 rounded-lg p-3 hover:bg-gray-50
                                   dark:hover:bg-gray-800/40 transition-all">

                            <!-- PEMBAYARAN KE -->
                            <div class="w-full md:w-1/4">
                                <input type="hidden" name="pembayaran_ke[]" :value="index + 1">
                                <input type="text"
                                    :value="index === 0 ?
                                        `Pembayaran ke - ${index + 1} / DP` :
                                        (index === angsuranList.length - 1 ?
                                            `Pembayaran ke - ${index + 1} / Pelunasan` :
                                            `Pembayaran ke - ${index + 1}`)"
                                    readonly class="w-full bg-gray-100 border border-gray-300 text-sm rounded-lg p-2.5">
                            </div>

                            <!-- TANGGAL -->
                            <div class="w-full md:w-1/3 relative">
                                <input type="text" name="tanggal_angsuran[]" x-model="angsuran.tanggal"
                                    x-init="flatpickr($el, {
                                        dateFormat: 'Y-m-d',
                                        defaultDate: angsuran.tanggal,
                                        onChange: (d, v) => angsuran.tanggal = v
                                    })"
                                    class="bg-gray-50 border border-gray-300 text-sm rounded-lg w-full p-2.5">
                            </div>

                            <!-- NOMINAL -->
                            <div class="w-full md:w-1/3">
                                <input type="text" x-model="angsuran.nominalFormatted" @input="formatNominal(index)"
                                    placeholder="Masukkan nominal"
                                    class="w-full bg-gray-50 border border-gray-300 text-sm rounded-lg p-2.5">

                                <input type="hidden" name="nominal_angsuran[]" :value="angsuran.nominal">
                            </div>

                        </div>
                    </template>
                </div>

            </div>
        </template>

    </div>
</div>


{{-- Bagian CASH --}}
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
                    :class="selectedCash === cash.id ?
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

                <!-- kirim id cash ke backend -->
                <input type="hidden" name="cara_bayar_id" :value="selectedCash?.id">

                <!-- Berapa Kali Angsur -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Berapa Kali Angsur
                    </label>
                    <input type="text" x-model="jumlahCicilan" readonly
                        class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5">

                    <input type="hidden" name="jumlah_cicilan" :value="jumlahCicilan">
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
                                <input type="hidden" name="pembayaran_ke[]" :value="index + 1">

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
                                <input type="hidden" name="tanggal_angsuran[]" :value="angsuran.tanggal">

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
                                <input type="hidden" name="nominal_angsuran[]" :value="angsuran.nominal">

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
