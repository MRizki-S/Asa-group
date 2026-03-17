    <div x-show="tab === 'bahan'" class="pt-6" x-cloak>
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm overflow-x-auto">
        <div class="flex justify-between items-center mb-6">
            <h4 class="font-bold text-gray-700 dark:text-white">Daftar RAP Bahan</h4>
            <button type="button" @click="addBahan()" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700 shadow-sm transition">
                + Tambah Bahan
            </button>
        </div>
        <table class="w-full text-sm text-left">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-white">
                <tr>
                    <th class="px-4 py-3 border-b dark:border-gray-600 w-1/4">Langkah QC</th>
                    <th class="px-4 py-3 border-b dark:border-gray-600">Barang</th>
                    <th class="px-4 py-3 border-b dark:border-gray-600">Jumlah</th>
                    <th class="px-4 py-3 border-b dark:border-gray-600">Satuan</th>
                    <th class="px-4 py-3 text-center border-b dark:border-gray-600">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(bahan, bIndex) in bahanGroups" :key="bIndex">
                    <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700">
                        <td class="px-2 py-3">
                            <select :name="`bahan[${bIndex}][urutan_idx]`" x-model="bahan.urutan_idx"
                                class="w-full p-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-lg">
                                <option value="">-- Pilih --</option>
                                <template x-for="(qc, qIndex) in qcGroups" :key="qIndex">
                                    <option :value="qIndex"
                                            x-text="qc.nama_qc"
                                            :selected="bahan.urutan_idx == qIndex"></option>
                                </template>
                            </select>
                        </td>
                        <td class="px-2 py-3">
                            <select :name="`bahan[${bIndex}][barang_id]`" x-model="bahan.barang_id" class="w-full p-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-lg">
                                <option value="1">Semen (Dummy)</option>
                                <option value="2">Pasir (Dummy)</option>
                            </select>
                        </td>
                        <td class="px-2 py-3">
                            <input type="number" :name="`bahan[${bIndex}][jumlah_kebutuhan_standar]`" x-model="bahan.jumlah_kebutuhan_standar" step="0.01" class="w-full p-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-lg">
                        </td>
                        <td class="px-2 py-3">
                            <input type="text" :name="`bahan[${bIndex}][satuan]`" x-model="bahan.satuan" class="w-full p-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-lg">
                        </td>
                        <td class="px-2 py-3 text-center">
                            <button type="button" @click="removeBahan(bIndex)" class="text-red-500 hover:text-red-700 transition">
                                <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>
