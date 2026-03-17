<div x-show="tab === 'upah'" class="pt-6" x-cloak>
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm overflow-x-auto">
        <div class="flex justify-between items-center mb-6">
            <h4 class="font-bold text-gray-700 dark:text-white">Daftar RAP Upah</h4>
            <button type="button" @click="addUpah()" class="px-4 py-2 bg-yellow-600 text-white rounded-lg text-sm hover:bg-yellow-700 shadow-sm transition">
                + Tambah Upah
            </button>
        </div>
        <table class="w-full text-sm text-left">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-white">
                <tr>
                    <th class="px-4 py-3 border-b dark:border-gray-600 w-1/4">Langkah QC</th>
                    <th class="px-4 py-3 border-b dark:border-gray-600">Tipe Upah</th>
                    <th class="px-4 py-3 border-b dark:border-gray-600">Nominal (Rp)</th>
                    <th class="px-4 py-3 text-center border-b dark:border-gray-600">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(upah, uIndex) in upahGroups" :key="uIndex">
                    <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700">
                        <td class="px-2 py-3">
                            <select :name="`upah[${uIndex}][urutan_idx]`" x-model="upah.urutan_idx"
                                class="w-full p-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-lg">
                                <option value="">-- Pilih --</option>
                                <template x-for="(qc, qIndex) in qcGroups" :key="qIndex">
                                    <option :value="qIndex"
                                            x-text="qc.nama_qc"
                                            :selected="upah.urutan_idx == qIndex"></option>
                                </template>
                            </select>
                        </td>
                        <td class="px-2 py-3">
                            <select :name="`upah[${uIndex}][master_upah_id]`" x-model="upah.master_upah_id" class="w-full p-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-lg">
                                <option value="">-- Pilih --</option>
                                @foreach($allUpah ?? [] as $u)
                                    <option value="{{ $u->id }}">{{ $u->nama_upah }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-2 py-3">
                            <input type="number" :name="`upah[${uIndex}][nominal_standar]`" x-model="upah.nominal_standar" class="w-full p-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-lg">
                        </td>
                        <td class="px-2 py-3 text-center">
                            <button type="button" @click="removeUpah(uIndex)" class="text-red-500 hover:text-red-700 transition">
                                <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>
