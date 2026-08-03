{{-- ==================== MODAL: LEMBUR ==================== --}}
<div x-show="modalLemburOpen" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="closeModalLembur">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] flex flex-col" @click.stop>
        <!-- Header -->
        <div class="flex items-start justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Input Lembur</h3>
                <p class="text-sm text-gray-500 mt-0.5" x-text="(modalLemburTukang?.nama_tukang ?? '') + ' — ' + formatTanggalLong(modalLemburDetail?.tanggal ?? '')"></p>
            </div>
            <button @click="closeModalLembur" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <!-- Body -->
        <div class="px-6 py-4 overflow-y-auto flex-1">
            <!-- Tambah Baris Lembur -->
            <div class="mb-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-xl">
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Tambah Lembur</h4>
                <div class="grid grid-cols-12 gap-3 col-select2-container">
                    <div class="col-span-3">
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Referensi</label>                        <select x-model="newLembur.referensi_jenis" @change="onLemburJenisChange()"
                            class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-white">
                            <option value="">-- Pilih --</option>
                            @if($isAbm)
                                <option value="pembangunan_unit">Pembangunan Unit</option>
                                <option value="pembangunan_kawasan">Pembangunan Kawasan</option>
                            @else
                                <option value="pembangunan_proyek">Pembangunan Proyek</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-span-3">
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Target</label>
                        <select id="select-lembur-referensi-id" :disabled="!newLembur.referensi_jenis"
                            class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-white">
                            <option value="">-- Pilih --</option>
                            <template x-for="item in getLemburReferensiOptions(newLembur.referensi_jenis)" :key="item.id">
                                <option :value="item.id" x-text="item.label"></option>
                            </template>
                        </select>
                    </div>
                    <div class="col-span-3">
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Jam Lembur</label>
                        <input type="number" x-model.number="newLembur.jam" min="1" placeholder="0"
                            class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div class="col-span-3">
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Tarif/Jam (Rp)</label>
                        <input type="text"
                            :value="formatRupiah(newLembur.tarif)"
                            @input="e => { let c = e.target.value.replace(/\D/g,''); newLembur.tarif = parseInt(c)||0; e.target.value = formatRupiah(newLembur.tarif) }"
                            placeholder="0"
                            class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-white">
                    </div>
                </div>
                <div class="mt-3 flex justify-end">
                    <button type="button" @click="addLembur"
                        class="px-4 py-2 text-sm font-medium text-white bg-orange-500 rounded-lg hover:bg-orange-600 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Lembur
                    </button>
                </div>
            </div>
 
            <!-- List Lembur -->
            <div x-show="modalLemburDetail?.alokasi_lembur.length > 0">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">Referensi</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">Nama</th>
                            <th class="px-3 py-2 text-center text-xs font-semibold text-gray-600 dark:text-gray-400">Jam</th>
                            <th class="px-3 py-2 text-center text-xs font-semibold text-gray-600 dark:text-gray-400">Tarif/Jam</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold text-gray-600 dark:text-gray-400">Subtotal</th>
                            <th class="px-3 py-2 text-center text-xs font-semibold text-gray-600 dark:text-gray-400">Hapus</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <template x-for="(lem, lIdx) in (modalLemburDetail?.alokasi_lembur ?? [])" :key="lIdx">
                            <tr>
                                <td class="px-3 py-2 text-xs text-gray-600 dark:text-gray-400" x-text="lem.referensi_jenis === 'pembangunan_unit' ? 'Unit' : (lem.referensi_jenis === 'pembangunan_kawasan' ? 'Kawasan' : 'Proyek')"></td>
                                <td class="px-3 py-2 text-sm font-medium text-gray-900 dark:text-white" x-text="getReferensiLabel(lem.referensi_jenis, lem.referensi_id)"></td>
                                <td class="px-3 py-2 text-center" x-text="lem.jam + ' Jam'"></td>
                                <td class="px-3 py-2 text-center text-xs" x-text="'Rp ' + formatRupiah(lem.tarif)"></td>
                                <td class="px-3 py-2 text-right font-semibold text-orange-600" x-text="'Rp ' + formatRupiah(lem.jam * lem.tarif)"></td>
                                <td class="px-3 py-2 text-center">
                                    <button type="button" @click="removeLembur(lIdx)" class="text-red-500 hover:text-red-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <p x-show="!modalLemburDetail?.alokasi_lembur.length" class="text-center text-sm text-gray-400 py-4">Belum ada lembur ditambahkan</p>

            <!-- Totals Lembur -->
            <div class="mt-4 p-3 bg-orange-50 dark:bg-orange-900/20 rounded-xl flex justify-between items-center">
                <div class="text-sm">
                    <span class="text-gray-500">Total Jam Lembur:</span>
                    <span class="font-bold text-orange-600 ml-1" x-text="getTotalJamLembur() + ' Jam'"></span>
                </div>
                <div class="text-sm">
                    <span class="text-gray-500">Total Nominal Lembur:</span>
                    <span class="font-bold text-orange-600 ml-1" x-text="'Rp ' + formatRupiah(getTotalNominalLembur())"></span>
                </div>
            </div>
        </div>
        <!-- Footer -->
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
            <button type="button" @click="closeModalLembur" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Tutup</button>
            <button type="button" @click="saveModalLembur"
                class="px-4 py-2 text-sm font-semibold text-white bg-orange-500 rounded-lg hover:bg-orange-600">
                Simpan Lembur
            </button>
        </div>
    </div>
</div>