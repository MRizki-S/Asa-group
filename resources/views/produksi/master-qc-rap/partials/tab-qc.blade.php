<div x-show="tab === 'qc'" class="pt-6 space-y-4">
    <template x-for="(qc, index) in qcGroups" :key="index">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-end gap-4 mb-6">
                <div class="w-full md:w-24">
                    <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Urutan</label>
                    <input type="text" :name="`qc[${index}][qc_ke]`" x-model="qc.qc_ke" readonly
                        class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-center font-bold text-gray-600 dark:text-gray-300 rounded-lg p-2.5 cursor-not-allowed" />
                </div>
                <div class="flex-1">
                    <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Nama Langkah QC</label>
                    <input type="text" :name="`qc[${index}][nama_qc]`" x-model="qc.nama_qc" required
                        class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white text-sm rounded-lg p-2.5" />
                </div>
                <div class="flex gap-2 pb-1">
                    <button type="button" @click="addTugas(index)" class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-700 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 border border-blue-100 dark:border-blue-800 transition">
                        <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Tambah Tugas
                    </button>
                    <button type="button" @click="removeQc(index)" class="inline-flex items-center px-4 py-2 text-sm font-medium text-red-700 bg-red-50 dark:bg-red-900/20 rounded-lg hover:bg-red-100 border border-red-100 dark:border-red-800 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>
            <div class="space-y-3 bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg border border-dashed border-gray-200 dark:border-gray-600">
                <template x-for="(tugas, tIndex) in qc.tugas" :key="tIndex">
                    <div class="flex items-center gap-2 group">
                        <div class="flex-none text-xs text-gray-400 font-mono" x-text="`${tIndex + 1}.`"></div>
                        <input type="text" :name="`qc[${index}][tugas][]`" x-model="qc.tugas[tIndex]" placeholder="Deskripsi tugas..." required
                            class="flex-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-900 dark:text-white text-sm rounded-lg p-2 focus:ring-1 focus:ring-blue-500" />
                        <button type="button" @click="removeTugas(index, tIndex)" class="p-2 text-gray-400 hover:text-red-600 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </template>
    <button type="button" @click="addQc()" class="w-full py-3 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
        + Tambah Langkah QC Baru
    </button>
</div>
