<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">

    <!-- 🔘 Pilihan Cara Bayar -->
    <div class="px-5 py-4 sm:px-6 sm:py-5">
        <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">
            <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                Sistem Pembayaran
            </h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Select Cara Bayar -->
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    Pilih Cara Bayar <span class="text-red-500">*</span>
                </label>
                <select name="cara_bayar" x-model="caraBayar" required
                    class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                                    dark:bg-gray-700 dark:text-white
                                    @error('cara_bayar') border-red-500 @else border-gray-300 @enderror">
                    <option value="">Pilih Cara Bayar</option>
                    <option value="cash">CASH</option>
                    <option value="kpr">KPR</option>
                </select>
                @error('cara_bayar')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <!-- 💵 FORM CASH -->
    <div x-show="caraBayar === 'cash'" x-transition
        class="px-5 py-4 sm:px-6 sm:py-5 border-t border-gray-100 dark:border-gray-800">
        <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">
            <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Sistem Pembayaran</h3>
            <span
                class="inline-flex items-center px-3 py-1 text-sm font-semibold text-yellow-800 bg-yellow-100 rounded-full border border-yellow-300 dark:bg-yellow-900/30 dark:text-yellow-300">
                CASH
            </span>
        </div>

        <div x-data="{
            hargaRumah: '',
            nominalKelebihan: 0,
            get hargaJadi() {
                const rumah = parseInt(this.hargaRumah.replace(/\D/g, '')) || 0;
                return formatRupiah((rumah + this.nominalKelebihan).toString());
            },
            updateHargaRumah(e) {
                let raw = e.target.value.replace(/\D/g, '');
                this.hargaRumah = formatRupiah(raw);
            },
        }" x-init="$watch('selectedCustomer', value => {
            // hanya ambil nominal kelebihan
            if (value?.booking) {
                nominalKelebihan = parseInt((value.booking.nominal_kelebihan || '0').toString().split('.')[0]);
            }
        });

        // kalau page direload tapi selectedCustomer masih ada
        if (selectedCustomer?.booking) {
            nominalKelebihan = parseInt((selectedCustomer.booking.nominal_kelebihan || '0').toString().split('.')[0]);
        }" class='space-y-4'>

            <!-- Harga Rumah -->
            <div>
                <label for="no_hp" class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    Harga Rumah<span class="text-red-500">*</span>
                </label>
                <input type="text" x-model="hargaRumah" @input="updateHargaRumah" placeholder="Masukkan harga rumah"
                    class="w-full bg-white border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5
                   dark:bg-gray-700 dark:text-white dark:border-gray-600">
                <!-- Input hidden dikirim ke server -->
                <input type="hidden" name="cash_harga_rumah" :value="hargaRumah.replace(/\D/g, '')">
            </div>

            <!-- Kelebihan Tanah -->
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                    Kelebihan Tanah
                </label>
                <div class="grid grid-cols-2 gap-4">
                    <input type="text" readonly name="cash_luas_kelebihan"
                        class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed
                       dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600"
                        :value="selectedCustomer?.booking?.luas_kelebihan ?? '-'">

                    <input type="text" readonly
                        class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5
                       dark:bg-gray-700 dark:text-white dark:border-gray-600"
                        :value="formatRupiah(nominalKelebihan.toString())">

                    <!-- Input hidden dikirim ke server -->
                    <input type="hidden" name="cash_nominal_kelebihan" :value="nominalKelebihan">
                </div>
            </div>

            <!-- Harga Jadi -->
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                    Harga Jadi
                </label>
                <input type="text" readonly
                    class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed
                   dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600"
                    :value="hargaJadi">

                <!-- Hidden untuk dikirim ke server -->
                <input type="hidden" name="cash_harga_jadi"
                    :value="(parseInt(hargaRumah.replace(/\D/g, '')) || 0) + nominalKelebihan">
            </div>
        </div>
    </div>


    <!-- 🏦 FORM KPR -->
    <div x-show="caraBayar === 'kpr'" x-transition class="px-5 py-4 sm:px-6 sm:py-5">
        <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">
            <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                Sistem Pembayaran
            </h3>
            <span
                class="inline-flex items-center px-3 py-1 text-sm font-semibold text-blue-800 bg-blue-100 rounded-full border border-blue-300 dark:bg-blue-900/30 dark:text-blue-300">
                KPR
            </span>
        </div>


        <div x-data="{
            sbumPemerintah: 4000000,
            dpRumahInduk: '',
            nominalKelebihan: 0,
            hargaTotal: 0,

            // Getter angka bersih
            get dpRumahIndukNumber() {
                return parseInt(this.dpRumahInduk.replace(/\D/g, '')) || 0;
            },

            // total_dp = dp rumah induk + nominal kelebihan tanah
            get totalDpNumber() {
                return this.dpRumahIndukNumber + this.nominalKelebihan;
            },

            // dp_pembeli = total_dp - sbum (minimal 0)
            get dpPembeliNumber() {
                const hasil = this.totalDpNumber - this.sbumPemerintah;
                return hasil > 0 ? hasil : 0;
            },

            // harga_kpr = harga_total - total_dp
            get hargaKprNumber() {
                const total = this.hargaTotal - this.totalDpNumber;
                return total > 0 ? total : 0;
            },

            // Format tampilan
            get totalDp() {
                return formatRupiah(this.totalDpNumber.toString());
            },
            get dpPembeli() {
                return formatRupiah(this.dpPembeliNumber.toString());
            },
            get hargaKpr() {
                return formatRupiah(this.hargaKprNumber.toString());
            },
            get hargaTotalFormatted() {
                return formatRupiah(this.hargaTotal.toString());
            },

            // Event input
            updateDpRumahInduk(e) {
                let raw = e.target.value.replace(/\D/g, '');
                this.dpRumahInduk = formatRupiah(raw);
            },
        }" x-init="$watch('selectedCustomer', value => {
            if (value?.booking) {
                hargaTotal = parseInt((value.booking.harga_final || 0).toString().split('.')[0]);
                nominalKelebihan = parseInt((value.booking.nominal_kelebihan || 0).toString().split('.')[0]);
            }
        });

        if (selectedCustomer?.booking) {
            hargaTotal = parseInt((selectedCustomer.booking.harga_final || 0).toString().split('.')[0]);
            nominalKelebihan = parseInt((selectedCustomer.booking.nominal_kelebihan || 0).toString().split('.')[0]);
        }" class="space-y-5">

            <!-- Info SBUM Pemerintah -->
            <div
                class="mt-3 flex items-center gap-3 px-3 py-2 rounded-lg border border-yellow-200 bg-yellow-50
        dark:bg-yellow-900/30 dark:border-yellow-700 transition-all duration-300 hover:shadow-sm">
                <div
                    class="flex items-center justify-center w-7 h-7 rounded-full bg-yellow-500 text-white font-bold text-sm">
                    💡
                </div>
                <div>
                    <p class="text-sm text-yellow-800 dark:text-yellow-300 font-medium">SBUM dari Pemerintah</p>
                    <p class="text-xs text-yellow-600 dark:text-yellow-400">
                        Tambahan harga: Rp <span x-text="formatRupiah(sbumPemerintah.toString())"></span>
                    </p>
                </div>
            </div>

            <!-- DP Rumah Induk -->
            <div>
                <label class="block mt-4 mb-1 text-sm font-medium text-gray-900 dark:text-white">
                    DP Rumah Induk <span class="text-red-500">*</span>
                </label>
                <input type="text" x-model="dpRumahInduk" @input="updateDpRumahInduk"
                    class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5
                dark:bg-gray-700 dark:text-white dark:border-gray-600"
                    placeholder="Masukkan DP Rumah Induk">

                <!-- Hidden -->
                <input type="hidden" name="kpr_dp_rumah_induk" :value="dpRumahIndukNumber">

                <p class="text-xs text-gray-500 mt-1">
                    Nominal termasuk <b>SBUM Pemerintah</b> akan dijumlah otomatis ke <b>Total DP</b>.
                </p>
            </div>

            <!-- Kelebihan Tanah -->
            <div class="grid grid-cols-2 gap-4 items-end mt-4">
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Luas Kelebihan Tanah (m²)
                    </label>
                    <input type="text" readonly name="kpr_luas_kelebihan"
                        class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed
                    dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600"
                        :value="selectedCustomer?.booking?.luas_kelebihan ?? '-'">
                </div>
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Nominal Kelebihan (Rp)
                    </label>
                    <input type="text" readonly :value="formatRupiah(nominalKelebihan.toString())"
                        class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5
                    dark:bg-gray-700 dark:text-white dark:border-gray-600">

                    <!-- Hidden -->
                    <input type="hidden" name="kpr_nominal_kelebihan" :value="nominalKelebihan">
                </div>
            </div>

            <!-- Total DP -->
            <div class="mt-4">
                <label class="block mb-1 text-sm font-semibold text-gray-900 dark:text-white">
                    Total DP
                </label>
                <input type="text" readonly :value="totalDp"
                    class="w-full bg-green-50 border border-green-300 text-green-700 text-sm font-semibold rounded-lg p-2.5
                dark:bg-green-900/30 dark:border-green-700 cursor-not-allowed"
                    placeholder="Rp 0">

                <!-- Hidden -->
                <input type="hidden" name="kpr_total_dp" :value="totalDpNumber">
            </div>

            <!-- DP Dibayarkan Pembeli -->
            <div>
                <label class="block mb-1 text-sm font-semibold text-gray-900 dark:text-white">
                    DP Dibayarkan Pembeli
                </label>
                <input type="text" readonly :value="dpPembeli"
                    class="w-full bg-gray-100 border border-gray-300 text-gray-600 text-sm rounded-lg p-2.5
                dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700 cursor-not-allowed"
                    placeholder="Rp 0">

                <!-- Hidden -->
                <input type="hidden" name="kpr_dp_dibayarkan_pembeli" :value="dpPembeliNumber">

                <p class="text-xs text-gray-500 mt-1">
                    Otomatis dihitung dari <b>Total DP - SBUM Pemerintah</b>.
                </p>
            </div>

            <!-- Harga Total Rumah & Nilai KPR -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block mb-1 text-sm font-semibold text-gray-900 dark:text-white">
                        Harga Total Rumah
                    </label>
                    <input type="text" readonly :value="hargaTotalFormatted"
                        class="w-full bg-indigo-50 border border-indigo-300 text-indigo-700 text-sm font-semibold rounded-lg p-2.5
                    dark:bg-indigo-900/30 dark:border-indigo-700 cursor-not-allowed"
                        placeholder="Rp 0">

                    <input type="hidden" name="kpr_harga_total" :value="hargaTotal">
                </div>

                <div>
                    <label class="block mb-1 text-sm font-semibold text-gray-900 dark:text-white">
                        Nilai KPR
                    </label>
                    <input type="text" readonly :value="hargaKpr"
                        class="w-full bg-blue-50 border border-blue-300 text-blue-700 text-sm font-semibold rounded-lg p-2.5
                    dark:bg-blue-900/30 dark:border-blue-700 cursor-not-allowed"
                        placeholder="Rp 0">

                    <input type="hidden" name="kpr_harga_kpr" :value="hargaKprNumber">
                </div>
            </div>
        </div>

    </div>

    <!-- 💸 Bonus Cash (muncul kalau cash dipilih) -->
    <div x-show="caraBayar === 'cash'" x-transition
        class="px-5 py-4 sm:px-6 sm:py-5 space-y-3 border-t border-gray-100 dark:border-gray-800">
        <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-2">Bonus Cash</h3>

        <template x-for="(bonus, index) in bonusList" :key="index">
            <div class="flex gap-2 items-center">
                <select x-model="bonus.nama_bonus" name="nama_bonus[]"
                    class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                           dark:bg-gray-700 dark:text-white border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- Pilih Bonus --</option>
                    <template x-for="item in availableOptions(index)" :key="item.nama_bonus">
                        <option :value="item.nama_bonus" x-text="item.nama_bonus"></option>
                    </template>
                </select>

                <!-- Tombol Hapus -->
                <button type="button" @click="bonusList.splice(index, 1)"
                    class="p-2 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition-all duration-200"
                    x-show="bonusList.length > 1 && index > 0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <input type="hidden" :value="getNominal(bonus.nama_bonus)" name="nominal_bonus[]">
            </div>
        </template>

        <!-- Tombol Tambah -->
        <div class="pt-2">
            <button type="button" @click="addBonus()"
                class="flex items-center gap-2 px-3 py-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-all duration-200 text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Bonus
            </button>
        </div>
    </div>
</div>
