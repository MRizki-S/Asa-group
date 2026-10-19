{{-- PARTIAL: SISTEM PEMBAYARAN (CASH & KPR) --}}
<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">

    <!-- 🔘 PILIHAN CARA BAYAR -->
    <div class="px-5 py-4 sm:px-6 sm:py-5">
        <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                Sistem & Skema Pembayaran
            </h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    Pilih Cara Bayar <span class="text-red-500">*</span>
                </label>
                <select name="cara_bayar" x-model="caraBayar" required
                    class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                           dark:bg-gray-700 dark:text-white
                           @error('cara_bayar') border-red-500 @else border-gray-300 @enderror">
                    <option value="">-- Pilih Cara Bayar --</option>
                    <option value="cash">CASH (TUNAI)</option>
                    <option value="kpr">KPR</option>
                </select>
                @error('cara_bayar')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 💵 BLOK CARA BAYAR: CASH                                                 --}}
    {{-- ========================================================================= --}}
    <div x-show="caraBayar === 'cash' && hasSelected" x-transition
        class="px-5 py-4 sm:px-6 sm:py-5 border-t border-gray-100 dark:border-gray-800 space-y-6">

        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-2">
            <h4 class="text-base font-medium text-gray-800 dark:text-white/90">
                1. Rincian Harga Rumah (CASH)
            </h4>
            <span class="inline-flex items-center px-3 py-1 text-xs font-semibold text-yellow-800 bg-yellow-100 rounded-full border border-yellow-300 dark:bg-yellow-900/30 dark:text-yellow-300">
                CASH
            </span>
        </div>

        <!-- 1. Input Komponen Pokok Cash -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Harga Pokok Rumah -->
            <div>
                <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                    Harga Pokok Rumah <span class="text-red-500">*</span>
                </label>
                <input type="text" x-model="cashHargaRumah" @input="updateCashHargaRumah($event)"
                    placeholder="Masukkan harga rumah"
                    class="w-full bg-white border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-white dark:border-gray-600">
                <input type="hidden" name="cash_harga_rumah" :value="cashHargaRumahNumber">
            </div>

            <!-- Kelebihan Tanah -->
            <div>
                <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                    Kelebihan Tanah (Luas m² & Nominal Rp)
                </label>
                <div class="grid grid-cols-2 gap-2">
                    <input type="text" name="cash_luas_kelebihan" x-model="cashLuasKelebihan"
                        placeholder="Luas (m²)"
                        class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-white dark:border-gray-600">
                    <input type="text" x-model="cashNominalKelebihan" @input="updateCashNominalKelebihan($event)"
                        placeholder="Nominal (Rp)"
                        class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-white dark:border-gray-600">
                    <input type="hidden" name="cash_nominal_kelebihan" :value="cashNominalKelebihanNumber">
                </div>
            </div>
        </div>

        <!-- Biaya Tambahan Cash -->
        <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-700 p-4 bg-gray-50/50 dark:bg-gray-800/30 space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h5 class="text-sm font-semibold text-gray-800 dark:text-white">Biaya Tambahan</h5>
                </div>
                <button type="button" @click="addCashTambahan()"
                    class="px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg dark:bg-blue-900/30 dark:text-blue-300 flex items-center gap-1 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Biaya
                </button>
            </div>

            <template x-for="(item, idx) in cashTambahanList" :key="idx">
                <div class="flex items-center gap-2">
                    <input type="text" x-model="item.keterangan" name="cash_tambahan_keterangan[]"
                        placeholder="Keterangan biaya tambahan"
                        class="w-1/2 bg-white border border-gray-300 text-gray-900 text-sm rounded-lg p-2 dark:bg-gray-700 dark:text-white dark:border-gray-600">
                    <input type="text" x-model="item.nominalFormatted" @input="formatNominalCashTambahan(idx)"
                        placeholder="Nominal (Rp)"
                        class="w-1/2 bg-white border border-gray-300 text-gray-900 text-sm rounded-lg p-2 text-right font-medium dark:bg-gray-700 dark:text-white dark:border-gray-600">
                    <input type="hidden" name="cash_tambahan_nominal[]" :value="item.nominal">

                    <button type="button" @click="removeCashTambahan(idx)"
                        class="p-2 text-red-500 hover:bg-red-50 rounded-lg dark:hover:bg-red-900/30 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </template>

            <template x-if="cashTambahanList.length === 0">
                <p class="text-xs text-gray-400 italic">Tidak ada biaya tambahan (klik tombol Tambah Biaya jika ada tambahan).</p>
            </template>
        </div>

        <!-- TOTAL / HARGA JADI CASH -->
        <div class="p-4 rounded-xl bg-blue-50/80 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
            <div>
                <span class="text-xs uppercase font-semibold text-blue-700 dark:text-blue-300 tracking-wider">Total / Harga Jadi Cash</span>
                <p class="text-xs text-blue-600 dark:text-blue-400">Harga Rumah + Kelebihan Tanah + Biaya Tambahan</p>
            </div>
            <div class="text-left sm:text-right pt-2 sm:pt-0 border-t border-blue-200/60 dark:border-blue-800/60 sm:border-t-0 flex items-center justify-between sm:block">
                <span class="text-xs font-medium text-blue-700 dark:text-blue-300 sm:hidden">Total Harga Jadi:</span>
                <span class="text-lg sm:text-xl font-bold text-blue-800 dark:text-blue-200" x-text="'Rp ' + (cashHargaJadi || '0')"></span>
                <input type="hidden" name="cash_harga_jadi" :value="cashHargaJadiNumber">
                <input type="hidden" name="cash_total_tambahan" :value="cashTotalTambahanNumber">
            </div>
        </div>

        <!-- 2. Skema Cicilan CASH (1 Tabel) -->
        <div class="border-t border-gray-100 dark:border-gray-800 pt-5 space-y-4">
            <div class="flex items-center justify-between pb-2">
                <h4 class="text-base font-medium text-gray-800 dark:text-white/90">
                    2. Skema Cicilan CASH
                </h4>
            </div>

            <!-- Tombol Opsi Cara Bayar Cash dari Setting Master -->
            <div class="flex flex-wrap gap-2">
                <template x-for="item in caraBayarCash" :key="item.id">
                    <button type="button" @click="pilihCash(item)"
                        :class="selectedCash?.id === item.id ?
                            'bg-blue-600 text-white border-blue-600 ring-2 ring-blue-300 dark:ring-blue-800' :
                            'bg-gray-100 text-gray-700 border-gray-300 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600'"
                        class="px-4 py-2 text-xs font-semibold rounded-lg border transition">
                        <span x-text="item.nama_cara_bayar"></span>
                        <span class="ml-1 opacity-75" x-text="'(' + item.jumlah_cicilan + 'x)'"></span>
                    </button>
                </template>
            </div>

            <template x-if="selectedCash">
                <div class="space-y-4">
                    <input type="hidden" name="cara_bayar_id" :value="selectedCash?.id">
                    <input type="hidden" name="jumlah_cicilan" :value="cashJumlahCicilan">

                    <!-- Alert Minimal DP -->
                    <div x-show="cashMinimalDp"
                        class="text-xs text-yellow-800 dark:text-yellow-200 bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-lg p-3">
                        Ketentuan minimal DP / Pembayaran ke-1: <strong x-text="'Rp ' + formatNumber(cashMinimalDp)"></strong>.
                    </div>

                    <!-- Tabel Angsuran Cash -->
                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <table class="w-full min-w-[360px] text-left text-sm text-gray-700 dark:text-gray-200">
                            <thead class="bg-gray-100 dark:bg-gray-700 text-[11px] sm:text-xs uppercase text-gray-600 dark:text-gray-300">
                                <tr>
                                    <th class="px-2.5 py-2.5 sm:px-4 sm:py-3 w-1/3 sm:w-1/4">Pembayaran</th>
                                    <th class="px-2 py-2.5 sm:px-4 sm:py-3 w-1/3">Tgl Estimasi</th>
                                    <th class="px-2.5 py-2.5 sm:px-4 sm:py-3 w-1/3 text-right sm:text-left">Nominal (Rp)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                <template x-for="(ang, idx) in cashAngsuranList" :key="idx">
                                    <tr :class="isCashRowOver(idx) ? 'bg-red-50/80 dark:bg-red-950/40' : ''" class="transition-colors">
                                        <td class="px-2.5 py-2 sm:px-4 sm:py-2.5">
                                            <input type="hidden" name="pembayaran_ke[]" :value="idx + 1">
                                            <input type="hidden" name="tipe_cicilan[]" value="dp">
                                            <input type="text" name="keterangan_angsuran[]" x-model="ang.keterangan" readonly
                                                class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded p-1.5 text-xs font-medium dark:bg-gray-700 dark:text-white dark:border-gray-600">
                                        </td>
                                        <td class="px-2 py-2 sm:px-4 sm:py-2.5">
                                            <input type="text" name="tanggal_angsuran[]" x-model="ang.tanggal" readonly
                                                x-init="if ($el._flatpickr) { $el._flatpickr.destroy(); } flatpickr($el, { dateFormat: 'Y-m-d', defaultDate: ang.tanggal, disableMobile: true, onChange: (d, v) => ang.tanggal = v })"
                                                class="w-full bg-white border border-gray-300 text-gray-900 rounded p-1.5 text-xs dark:bg-gray-700 dark:text-white dark:border-gray-600 cursor-pointer">
                                        </td>
                                        <td class="px-2.5 py-2 sm:px-4 sm:py-2.5">
                                            <input type="text" x-model="ang.nominalFormatted" @input="formatNominalCashAngsuran(idx)"
                                                placeholder="Nominal Rp"
                                                :class="isCashRowOver(idx) ? 
                                                    'w-full border-2 border-red-500 bg-red-50 text-red-700 font-bold focus:ring-red-500 focus:border-red-500 rounded p-1.5 text-xs text-right dark:bg-red-900/30 dark:border-red-500 dark:text-red-300' : 
                                                    'w-full bg-white border border-gray-300 text-gray-900 rounded p-1.5 text-xs text-right font-semibold dark:bg-gray-700 dark:text-white dark:border-gray-600'">
                                            <input type="hidden" name="nominal_angsuran[]" :value="ang.nominal">
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>
        </div>

        <!-- 3. Harga Sudah Termasuk (Promo Cash) -->
        <div class="border-t border-gray-100 dark:border-gray-800 pt-5 space-y-3">
            <h4 class="text-base font-medium text-gray-800 dark:text-white/90">
                3. Harga Sudah Termasuk (Fasilitas & Legalitas Cash)
            </h4>
            <p class="text-xs text-gray-500">Centang fasilitas/legalitas yang berlaku untuk pemesanan ini:</p>

            @if ($promoCash && $promoCash->items->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 bg-gray-50 dark:bg-gray-800/40 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
                    @foreach ($promoCash->items as $item)
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer hover:text-blue-600">
                            <input type="checkbox" name="promo_cash_items[]" value="{{ $item->nama_promo }}" checked
                                :disabled="caraBayar !== 'cash'"
                                class="w-4 h-4 rounded border border-gray-300 bg-white text-blue-600 shadow-sm focus:ring-blue-500 cursor-pointer"
                                style="accent-color: #2563eb; color: #2563eb; background-color: #ffffff;">
                            <span>{{ $item->nama_promo }}</span>
                        </label>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-gray-400 italic">Tidak ada master "Harga Sudah Termasuk" aktif untuk tipe Cash.</p>
            @endif
        </div>

        <!-- Bonus Cash -->
        <div class="border-t border-gray-100 dark:border-gray-800 pt-5 space-y-3">
            <h4 class="text-base font-medium text-gray-800 dark:text-white/90">Bonus Cash (Opsional)</h4>
            <template x-for="(bonus, index) in bonusCashList" :key="index">
                <div class="flex gap-2 items-center">
                    <select x-model="bonus.nama_bonus" name="nama_bonus_cash[]"
                        class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2 dark:bg-gray-700 dark:text-white border-gray-300">
                        <option value="">-- Pilih Bonus --</option>
                        <template x-for="item in availableBonusCash(index)" :key="item.nama_bonus">
                            <option :value="item.nama_bonus" x-text="item.nama_bonus"></option>
                        </template>
                    </select>
                    <button type="button" @click="bonusCashList.splice(index, 1)"
                        class="p-2 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 dark:bg-red-900/30 dark:hover:bg-red-900/50"
                        x-show="bonusCashList.length > 1 && index > 0">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    <input type="hidden" :value="getNominalCash(bonus.nama_bonus)" name="nominal_bonus_cash[]">
                </div>
            </template>
            <button type="button" @click="addBonusCash()"
                class="px-3 py-1.5 text-xs text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-300 flex items-center gap-1">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Bonus
            </button>
        </div>
    </div>


    {{-- ========================================================================= --}}
    {{-- 🏦 BLOK CARA BAYAR: KPR                                                  --}}
    {{-- ========================================================================= --}}
    <div x-show="caraBayar === 'kpr' && hasSelected" x-transition
        class="px-5 py-4 sm:px-6 sm:py-5 border-t border-gray-100 dark:border-gray-800 space-y-6">

        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-2">
            <h4 class="text-base font-medium text-gray-800 dark:text-white/90">
                1. Komponen Rumah Pokok & DP KPR
            </h4>
            <span class="inline-flex items-center px-3 py-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded-full border border-blue-300 dark:bg-blue-900/30 dark:text-blue-300">
                KPR
            </span>
        </div>

        <!-- 1. Input Komponen Rumah Pokok KPR -->
        <div class="space-y-4">
            <!-- Info SBUM -->
            <div class="flex items-center gap-3 px-3 py-2.5 rounded-lg border border-yellow-200 bg-yellow-50 dark:bg-yellow-900/30 dark:border-yellow-700">
                <span class="text-lg">💡</span>
                <div>
                    <p class="text-xs font-semibold text-yellow-800 dark:text-yellow-300">SBUM dari Pemerintah: Rp <span x-text="formatNumber(kprSbumPemerintah)"></span></p>
                    <p class="text-[11px] text-yellow-600 dark:text-yellow-400">Otomatis diperhitungkan untuk memotong nilai KPR Bank.</p>
                </div>
                <input type="hidden" name="kpr_sbum_pemerintah" :value="kprSbumPemerintah">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Harga Rumah Asli -->
                <div>
                    <label class="block mb-1 text-sm font-semibold text-gray-900 dark:text-white">
                        Harga Rumah Asli <span class="text-red-500">*</span>
                    </label>
                    <input type="text" x-model="kprHargaTotal" @input="updateKprHargaTotal($event)"
                        placeholder="Contoh: 166.000.000"
                        class="w-full bg-white border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-white dark:border-gray-600 font-medium">
                    <input type="hidden" name="kpr_harga_total" :value="kprHargaTotalNumber">
                </div>

                <!-- DP Rumah -->
                <div>
                    <label class="block mb-1 text-sm font-semibold text-gray-900 dark:text-white">
                        DP Rumah <span class="text-red-500">*</span>
                    </label>
                    <input type="text" x-model="kprDpRumahInduk" @input="updateKprDpRumahInduk($event)"
                        placeholder="Contoh: 2.000.000"
                        class="w-full bg-white border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-white dark:border-gray-600 font-medium">
                    <input type="hidden" name="kpr_dp_rumah_induk" :value="kprDpRumahIndukNumber">
                    <input type="hidden" name="kpr_total_dp" :value="kprDpRumahIndukNumber">
                    <input type="hidden" name="kpr_dp_dibayarkan_pembeli" :value="kprDpRumahIndukNumber">
                </div>

                <!-- Nilai KPR (Bank) -->
                <div>
                    <label class="block mb-1 text-sm font-semibold text-blue-700 dark:text-blue-300">
                        Nilai KPR (Plafon Bank)
                    </label>
                    <input type="text" readonly :value="kprNilaiKpr"
                        class="w-full bg-blue-50 border border-blue-300 text-blue-800 text-sm font-bold rounded-lg p-2.5 dark:bg-blue-900/30 dark:border-blue-700 dark:text-blue-200 cursor-not-allowed">
                    <input type="hidden" name="kpr_harga_kpr" :value="kprNilaiKprNumber">
                    <p class="text-[11px] text-gray-500 mt-1">Rumus: Harga Asli - SBUM (4jt) - DP Rumah</p>
                </div>
            </div>
        </div>

        <!-- 2. Skema Cicilan 1: DP Rumah Pokok -->
        <div class="border-t border-gray-100 dark:border-gray-800 pt-5 space-y-4">
            <div class="flex items-center justify-between pb-2">
                <h4 class="text-base font-medium text-gray-800 dark:text-white/90">
                    2. Skema Cicilan DP Rumah
                </h4>
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400"
                    x-text="'Total DP diangsur: Rp ' + formatNumber(kprDpRumahIndukNumber)"></span>
            </div>

            <!-- Tombol Opsi Cara Bayar KPR dari Setting Master -->
            <div class="flex flex-wrap gap-2">
                <template x-for="item in caraBayarKpr" :key="item.id">
                    <button type="button" @click="pilihKpr(item)"
                        :class="selectedKpr?.id === item.id ?
                            'bg-blue-600 text-white border-blue-600 ring-2 ring-blue-300 dark:ring-blue-800' :
                            'bg-gray-100 text-gray-700 border-gray-300 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600'"
                        class="px-4 py-2 text-xs font-semibold rounded-lg border transition">
                        <span x-text="item.nama_cara_bayar"></span>
                        <span class="ml-1 opacity-75" x-text="'(' + item.jumlah_cicilan + 'x)'"></span>
                    </button>
                </template>
            </div>

            <template x-if="selectedKpr">
                <div class="space-y-4">
                    <input type="hidden" name="cara_bayar_id" :value="selectedKpr?.id">
                    <input type="hidden" name="jumlah_cicilan" :value="jumlahCicilan">

                    <!-- Alert Minimal DP jika ada -->
                    <div x-show="minimalDp"
                        class="text-xs text-yellow-800 dark:text-yellow-200 bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-lg p-3">
                        Ketentuan minimal pembayaran ke-1: <strong x-text="'Rp ' + formatNumber(minimalDp)"></strong>.
                    </div>

                    <!-- Tabel Angsuran DP KPR -->
                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <table class="w-full min-w-[360px] text-left text-sm text-gray-700 dark:text-gray-200">
                            <thead class="bg-gray-100 dark:bg-gray-700 text-[11px] sm:text-xs uppercase text-gray-600 dark:text-gray-300">
                                <tr>
                                    <th class="px-2.5 py-2.5 sm:px-4 sm:py-3 w-1/3 sm:w-1/4">Pembayaran</th>
                                    <th class="px-2 py-2.5 sm:px-4 sm:py-3 w-1/3">Tgl Estimasi</th>
                                    <th class="px-2.5 py-2.5 sm:px-4 sm:py-3 w-1/3 text-right sm:text-left">Nominal (Rp)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                <template x-for="(ang, idx) in angsuranList" :key="idx">
                                    <tr :class="isKprDpRowOver(idx) ? 'bg-red-50/80 dark:bg-red-950/40' : ''" class="transition-colors">
                                        <td class="px-2.5 py-2 sm:px-4 sm:py-2.5">
                                            <input type="hidden" name="pembayaran_ke[]" :value="idx + 1">
                                            <input type="hidden" name="tipe_cicilan[]" value="dp">
                                            <input type="text" name="keterangan_angsuran[]" x-model="ang.keterangan" readonly
                                                class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded p-1.5 text-xs font-medium dark:bg-gray-700 dark:text-white dark:border-gray-600">
                                        </td>
                                        <td class="px-2 py-2 sm:px-4 sm:py-2.5">
                                            <input type="text" name="tanggal_angsuran[]" x-model="ang.tanggal" readonly
                                                x-init="if ($el._flatpickr) { $el._flatpickr.destroy(); } flatpickr($el, { dateFormat: 'Y-m-d', defaultDate: ang.tanggal, disableMobile: true, onChange: (d, v) => ang.tanggal = v })"
                                                class="w-full bg-white border border-gray-300 text-gray-900 rounded p-1.5 text-xs dark:bg-gray-700 dark:text-white dark:border-gray-600 cursor-pointer">
                                        </td>
                                        <td class="px-2.5 py-2 sm:px-4 sm:py-2.5">
                                            <input type="text" x-model="ang.nominalFormatted" @input="formatNominal(idx)"
                                                placeholder="Nominal Rp"
                                                :class="isKprDpRowOver(idx) ? 
                                                    'w-full border-2 border-red-500 bg-red-50 text-red-700 font-bold focus:ring-red-500 focus:border-red-500 rounded p-1.5 text-xs text-right dark:bg-red-900/30 dark:border-red-500 dark:text-red-300' : 
                                                    'w-full bg-white border border-gray-300 text-gray-900 rounded p-1.5 text-xs text-right font-semibold dark:bg-gray-700 dark:text-white dark:border-gray-600'">
                                            <input type="hidden" name="nominal_angsuran[]" :value="ang.nominal">
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>
        </div>

        <!-- 3. Biaya Administrasi / Tambahan KPR (Checklist Toggle) -->
        <div class="border-t border-gray-100 dark:border-gray-800 pt-5 space-y-4">
            <div class="p-4 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-xs">
                <label class="flex items-center gap-3 cursor-pointer select-none">
                    <input type="checkbox" x-model="adaBiayaAdmin" name="ada_biaya_admin" value="1"
                        class="w-5 h-5 rounded border border-gray-300 bg-white text-blue-600 focus:ring-blue-500 cursor-pointer"
                        style="accent-color: #2563eb; color: #2563eb; background-color: #ffffff;">
                    <div>
                        <span class="text-sm font-semibold text-gray-800 dark:text-white">Ada Biaya Administrasi / Tambahan?</span>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Centang jika terdapat biaya administrasi / tambahan (administrasi, kelebihan tanah, dsb).</p>
                    </div>
                </label>
            </div>

            <!-- Form Dinamis Biaya Administrasi / Tambahan (Muncul saat dicentang) -->
            <div x-show="adaBiayaAdmin" x-transition class="space-y-4 pl-2 md:pl-4 border-l-2 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <h5 class="text-sm font-semibold text-gray-800 dark:text-white">Daftar Biaya Administrasi / Tambahan</h5>
                        <p class="text-xs text-gray-500">Masukkan item rincian biaya administrasi / tambahan.</p>
                    </div>
                    <button type="button" @click="addBiayaAdmin()"
                        class="px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg dark:bg-blue-900/30 dark:text-blue-300 flex items-center gap-1 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Tambah Baris Biaya
                    </button>
                </div>

                <!-- List Baris Biaya Administrasi / Tambahan -->
                <div class="space-y-2">
                    <template x-for="(admin, aIdx) in biayaAdminList" :key="aIdx">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-2 items-center bg-white dark:bg-gray-800 p-2.5 rounded-lg border border-gray-200 dark:border-gray-700">
                            <!-- Keterangan -->
                            <div class="md:col-span-7">
                                <label class="block text-[11px] font-medium text-gray-500 md:hidden mb-0.5">Keterangan Biaya</label>
                                <input type="text" x-model="admin.keterangan" name="admin_keterangan[]" required
                                    placeholder="Keterangan biaya administrasi / tambahan"
                                    class="w-full bg-white border border-gray-300 text-gray-900 text-xs rounded-lg p-2 dark:bg-gray-700 dark:text-white dark:border-gray-600">
                            </div>

                            <!-- Nominal -->
                            <div class="md:col-span-4">
                                <label class="block text-[11px] font-medium text-gray-500 md:hidden mb-0.5">Nominal (Rp)</label>
                                <input type="text" x-model="admin.nominalFormatted" @input="formatNominalAdmin(aIdx)" required
                                    placeholder="Nominal Rp"
                                    class="w-full bg-white border border-gray-300 text-gray-900 text-xs rounded-lg p-2 text-right font-medium dark:bg-gray-700 dark:text-white dark:border-gray-600">
                                <input type="hidden" name="admin_nominal[]" :value="admin.nominal">
                            </div>

                            <!-- Hapus -->
                            <div class="md:col-span-1 text-center">
                                <button type="button" @click="removeBiayaAdmin(aIdx)"
                                    class="p-2 text-red-500 hover:bg-red-50 rounded-lg dark:hover:bg-red-900/30 transition">
                                    <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Total Biaya Administrasi / Tambahan -->
                <div class="p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 dark:border-amber-800 flex flex-col sm:flex-row sm:items-center justify-between gap-1 sm:gap-4">
                    <span class="text-xs font-semibold text-amber-800 dark:text-amber-300 uppercase">Total Biaya Administrasi / Tambahan</span>
                    <div class="text-left sm:text-right flex items-center justify-between sm:block">
                        <span class="text-xs font-medium text-amber-700 dark:text-amber-300 sm:hidden">Total Biaya:</span>
                        <span class="text-base font-bold text-amber-900 dark:text-amber-200" x-text="'Rp ' + (totalBiayaAdmin || '0')"></span>
                    </div>
                    <input type="hidden" name="total_biaya_administrasi" :value="totalBiayaAdminNumber">
                </div>

                <!-- 4. Skema Cicilan Biaya Administrasi / Tambahan -->
                <div class="pt-4 border-t border-gray-200 dark:border-gray-700 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h5 class="text-sm font-semibold text-gray-800 dark:text-white">Skema Cicilan Biaya Administrasi / Tambahan</h5>
                            <p class="text-xs text-gray-500">Tentukan berapa kali biaya administrasi / tambahan ini akan dibayarkan.</p>
                        </div>
                        <div class="flex items-center justify-between sm:justify-end gap-2 w-full sm:w-auto bg-gray-50 dark:bg-gray-800/60 p-2 sm:p-0 rounded-lg sm:bg-transparent">
                            <label class="text-xs font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">Berapa Kali Bayar:</label>
                            <select x-model="adminJumlahCicilan" @change="generateAngsuranAdmin($event.target.value)"
                                class="bg-white border border-gray-300 text-gray-900 text-xs rounded-lg p-2 sm:p-1.5 dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500 font-medium">
                                <option value="1">1x (Lunas di Awal)</option>
                                <option value="2">2x Pembayaran</option>
                                <option value="3">3x Pembayaran</option>
                                <option value="4">4x Pembayaran</option>
                                <option value="5">5x Pembayaran</option>
                                <option value="6">6x Pembayaran</option>
                            </select>
                        </div>
                    </div>

                    <!-- Tabel Angsuran Biaya Administrasi / Tambahan -->
                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <table class="w-full min-w-[360px] text-left text-sm text-gray-700 dark:text-gray-200">
                            <thead class="bg-gray-100 dark:bg-gray-700 text-[11px] sm:text-xs uppercase text-gray-600 dark:text-gray-300">
                                <tr>
                                    <th class="px-2.5 py-2.5 sm:px-4 sm:py-2.5 w-1/3 sm:w-1/4">Pembayaran</th>
                                    <th class="px-2 py-2.5 sm:px-4 sm:py-2.5 w-1/3">Tanggal Bayar</th>
                                    <th class="px-2.5 py-2.5 sm:px-4 sm:py-2.5 w-1/3 text-right sm:text-left">Nominal (Rp)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                <template x-for="(adminAng, aIdx) in adminAngsuranList" :key="aIdx">
                                    <tr :class="isAdminRowOver(aIdx) ? 'bg-red-50/80 dark:bg-red-950/40' : ''" class="transition-colors">
                                        <td class="px-2.5 py-2 sm:px-4 sm:py-2">
                                            <input type="hidden" name="admin_pembayaran_ke[]" :value="aIdx + 1">
                                            <input type="hidden" name="admin_tipe_cicilan[]" value="administrasi">
                                            <input type="text" name="admin_keterangan_angsuran[]" x-model="adminAng.keterangan" readonly
                                                class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded p-1.5 text-xs font-medium dark:bg-gray-700 dark:text-white dark:border-gray-600">
                                        </td>
                                        <td class="px-2 py-2 sm:px-4 sm:py-2">
                                            <input type="text" name="admin_tanggal_angsuran[]" x-model="adminAng.tanggal" readonly
                                                x-init="if ($el._flatpickr) { $el._flatpickr.destroy(); } flatpickr($el, { dateFormat: 'Y-m-d', defaultDate: adminAng.tanggal, disableMobile: true, onChange: (d, v) => adminAng.tanggal = v })"
                                                class="w-full bg-white border border-gray-300 text-gray-900 rounded p-1.5 text-xs dark:bg-gray-700 dark:text-white dark:border-gray-600 cursor-pointer">
                                        </td>
                                        <td class="px-2.5 py-2 sm:px-4 sm:py-2">
                                            <input type="text" x-model="adminAng.nominalFormatted" @input="formatNominalAdminAngsuran(aIdx)"
                                                placeholder="Nominal Rp"
                                                :class="isAdminRowOver(aIdx) ? 
                                                    'w-full border-2 border-red-500 bg-red-50 text-red-700 font-bold focus:ring-red-500 focus:border-red-500 rounded p-1.5 text-xs text-right dark:bg-red-900/30 dark:border-red-500 dark:text-red-300' : 
                                                    'w-full bg-white border border-gray-300 text-gray-900 rounded p-1.5 text-xs text-right font-semibold dark:bg-gray-700 dark:text-white dark:border-gray-600'">
                                            <input type="hidden" name="admin_nominal_angsuran[]" :value="adminAng.nominal">
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Catatan / Note Biaya Administrasi (Opsional) -->
                <div class="pt-3 border-t border-gray-200 dark:border-gray-700 space-y-1.5">
                    <label class="block text-xs font-semibold text-gray-800 dark:text-white uppercase">
                        Catatan / Note Biaya Administrasi (Opsional)
                    </label>
                    <p class="text-xs text-gray-500">Catatan ini akan tercetak di bawah rincian biaya administrasi pada dokumen PPJB (contoh: <em>Tidak termasuk PBB dan Materai</em>).</p>
                    <input type="text" name="catatan_biaya_administrasi"
                        :disabled="caraBayar !== 'kpr' || !adaBiayaAdmin"
                        placeholder="Contoh: Tidak termasuk PBB dan Materai"
                        class="w-full bg-white border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-white dark:border-gray-600">
                </div>
            </div>
        </div>

        <!-- 4. FASILITAS & LEGALITAS KPR (MOBILE CHIPS SELECTOR) -->
        <div class="border-t border-gray-100 dark:border-gray-800 pt-5 space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-sm sm:text-base font-semibold text-gray-800 dark:text-white/90">
                        4. Fasilitas & Legalitas KPR
                    </h4>
                    <p class="text-[11px] text-gray-500">Sentuh chip untuk alokasi ke DP atau Biaya Admin.</p>
                    <div class="flex flex-wrap items-center gap-2.5 mt-1 text-[11px]">
                        <span class="inline-flex items-center gap-1 font-medium text-blue-700 dark:text-blue-400">
                            <span class="w-2 h-2 rounded-full bg-blue-500 inline-block"></span> Biru: Masuk DP
                        </span>
                        <span class="inline-flex items-center gap-1 font-medium text-amber-700 dark:text-amber-400">
                            <span class="w-2 h-2 rounded-full bg-amber-500 inline-block"></span> Kuning: Biaya Admin
                        </span>
                        <span class="inline-flex items-center gap-1 font-medium text-gray-400 dark:text-gray-500">
                            <span class="w-2 h-2 rounded-full bg-gray-300 dark:bg-gray-600 inline-block"></span> Abu-abu: Belum Dipilih
                        </span>
                    </div>
                </div>

                <!-- Tombol Aksi Cepat -->
                <div class="flex items-center gap-1">
                    <button type="button" @click="setAllTabPromo('select_all')"
                        class="px-2 py-1 text-[11px] font-medium rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-300 transition">
                        Pilih Semua
                    </button>
                    <button type="button" @click="setAllTabPromo('reset')"
                        class="px-2 py-1 text-[11px] font-medium rounded-md bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 transition">
                        Reset
                    </button>
                </div>
            </div>

            <!-- TAB SELECTOR (DP RUMAH vs BIAYA ADMIN) -->
            <div class="grid grid-cols-2 gap-2 p-1 bg-gray-100 dark:bg-gray-800 rounded-xl">
                <!-- Tab 1: Termasuk DP -->
                <button type="button" @click="activePromoTab = 'dp'"
                    :class="activePromoTab === 'dp' 
                        ? 'bg-white dark:bg-gray-700 text-blue-700 dark:text-blue-300 shadow-xs font-semibold border border-blue-200/80 dark:border-blue-800' 
                        : 'text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white font-medium border border-transparent'"
                    class="py-2 px-3 rounded-lg text-xs flex items-center justify-center gap-1.5 transition-all">
                    <span>Termasuk DP</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px]"
                        :class="activePromoTab === 'dp' 
                            ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800 font-bold' 
                            : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                        x-text="countPromo('dp')">
                    </span>
                </button>

                <!-- Tab 2: Termasuk Biaya Admin -->
                <button type="button"
                    @click="if (adaBiayaAdmin) activePromoTab = 'admin'"
                    :disabled="!adaBiayaAdmin"
                    :class="!adaBiayaAdmin 
                        ? 'opacity-40 cursor-not-allowed text-gray-400' 
                        : (activePromoTab === 'admin' 
                            ? 'bg-white dark:bg-gray-700 text-amber-700 dark:text-amber-300 shadow-xs font-semibold border border-amber-200/80 dark:border-amber-800' 
                            : 'text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white font-medium border border-transparent')"
                    class="py-2 px-3 rounded-lg text-xs flex items-center justify-center gap-1.5 transition-all">
                    <template x-if="!adaBiayaAdmin">
                        <svg class="w-3 h-3 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </template>
                    <span>Biaya Admin</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px]"
                        :class="activePromoTab === 'admin' 
                            ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800 font-bold' 
                            : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                        x-text="countPromo('admin')">
                    </span>
                </button>
            </div>

            <!-- CONTAINER CHIPS FASILITAS (SUPER MOBILE-FRIENDLY) -->
            @if ($promoKpr && $promoKpr->items->count() > 0)
                <div class="p-3 bg-gray-50/70 dark:bg-gray-800/40 rounded-xl border border-gray-200 dark:border-gray-700">
                    <div class="flex flex-wrap gap-2">
                        @foreach ($promoKpr->items as $item)
                            @php $safeName = addslashes($item->nama_promo); @endphp
                            <button type="button"
                                @click="togglePromoChip('{{ $safeName }}')"
                                :class="getPromoChipClass('{{ $safeName }}')"
                                class="inline-flex items-center gap-1.5 px-3 py-2 text-xs rounded-lg border transition-all select-none active:scale-95 cursor-pointer">
                                
                                <template x-if="promoMatrix['{{ $safeName }}'] === 'dp'">
                                    <svg class="w-3.5 h-3.5 shrink-0 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </template>
                                <template x-if="promoMatrix['{{ $safeName }}'] === 'admin'">
                                    <svg class="w-3.5 h-3.5 shrink-0 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </template>
                                <template x-if="promoMatrix['{{ $safeName }}'] !== 'dp' && promoMatrix['{{ $safeName }}'] !== 'admin'">
                                    <svg class="w-3 h-3 shrink-0 opacity-40 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </template>

                                <span>{{ $item->nama_promo }}</span>

                                <template x-if="promoMatrix['{{ $safeName }}'] === 'dp'">
                                    <span class="text-[9px] px-1.5 py-0.5 rounded font-bold ml-0.5 transition-colors bg-blue-200/70 text-blue-800 dark:bg-blue-900/60 dark:text-blue-200">
                                        DP
                                    </span>
                                </template>
                                <template x-if="promoMatrix['{{ $safeName }}'] === 'admin'">
                                    <span class="text-[9px] px-1.5 py-0.5 rounded font-bold ml-0.5 transition-colors bg-amber-200/70 text-amber-900 dark:bg-amber-900/60 dark:text-amber-200">
                                        Admin
                                    </span>
                                </template>
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Hidden inputs synced with Alpine promoMatrix -->
                <template x-for="(status, name) in promoMatrix" :key="name">
                    <div>
                        <input type="hidden" name="promo_kpr_items[]" :value="name"
                            :disabled="status !== 'dp' || caraBayar !== 'kpr'">
                        <input type="hidden" name="promo_admin_items[]" :value="name"
                            :disabled="status !== 'admin' || !adaBiayaAdmin || caraBayar !== 'kpr'">
                    </div>
                </template>
            @else
                <p class="text-xs text-gray-400 italic">Tidak ada master fasilitas aktif untuk tipe KPR.</p>
            @endif
        </div>

        <!-- Bonus KPR -->
        <div class="border-t border-gray-100 dark:border-gray-800 pt-5 space-y-3">
            <h4 class="text-base font-medium text-gray-800 dark:text-white/90">Bonus KPR (Opsional)</h4>
            <template x-for="(bonus, index) in bonusKprList" :key="index">
                <div class="flex gap-2 items-center">
                    <select x-model="bonus.nama_bonus" name="nama_bonus_kpr[]"
                        class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2 dark:bg-gray-700 dark:text-white border-gray-300">
                        <option value="">-- Pilih Bonus --</option>
                        <template x-for="item in availableBonusKpr(index)" :key="item.nama_bonus">
                            <option :value="item.nama_bonus" x-text="item.nama_bonus"></option>
                        </template>
                    </select>
                    <button type="button" @click="bonusKprList.splice(index, 1)"
                        class="p-2 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 dark:bg-red-900/30 dark:hover:bg-red-900/50"
                        x-show="bonusKprList.length > 1 && index > 0">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    <input type="hidden" :value="getNominalKpr(bonus.nama_bonus)" name="nominal_bonus_kpr[]">
                </div>
            </template>
            <button type="button" @click="addBonusKpr()"
                class="px-3 py-1.5 text-xs text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-300 flex items-center gap-1">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Bonus
            </button>
        </div>
    </div>

</div>
