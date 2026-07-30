<div x-show="openUpahModal" class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/50 dark:bg-black/80"
    x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100">

    <div @click.away="openUpahModal = false" class="relative w-full max-w-xl p-4">
        <div class="relative bg-white rounded-xl shadow-xl dark:bg-gray-800 overflow-hidden border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between p-4 border-b dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Form Pengajuan Upah</h3>
                <button type="button" @click="openUpahModal = false" class="text-gray-400 hover:text-gray-900 dark:hover:text-white">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                </button>
            </div>

            <form @submit.prevent="submitUpah" class="p-5 space-y-4">

                <div
                    class="flex items-center justify-between p-3 mb-3 bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-600 rounded-xl">
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="checkbox" @change="itemsToPay.forEach(i => i.checked = $el.checked)"
                            :checked="itemsToPay.length > 0 && itemsToPay.every(i => i.checked)"
                            class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span
                            class="text-[11px] font-black text-gray-500 uppercase tracking-widest group-hover:text-blue-600">Pilih
                            Semua</span>
                    </label>
                    <span class="text-[10px] font-bold px-2 py-0.5 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full"
                        x-text="itemsToPay.filter(i => i.checked).length + ' Item Terpilih'"></span>
                </div>

                {{-- Container List Items --}}
                <div class="space-y-3 max-h-[45vh] overflow-y-auto pr-1 custom-scrollbar">
                    <template x-for="(item, index) in itemsToPay" :key="index">
                        <div class="p-4 border rounded-xl transition-all"
                            :class="item.checked ? 'border-blue-200 bg-blue-50/30 dark:border-blue-800 dark:bg-blue-900/20' : 'border-gray-100 bg-gray-50/50 dark:border-gray-600 dark:bg-gray-800/50'">

                            <div class="flex items-start gap-4">
                                <input type="checkbox" x-model="item.checked"
                                    class="mt-1.5 w-4 h-4 rounded border-gray-300 text-blue-600">

                                <div class="flex-1 space-y-3">
                                    <div class="flex justify-between items-start">
                                        <p class="text-sm font-bold text-gray-700 dark:text-gray-200" x-text="item.nama_upah"></p>
                                        <div class="flex flex-col items-end">
                                            <span class="text-[10px] font-bold text-blue-600 dark:text-blue-400 font-mono"
                                                x-text="'Limit RAP: Rp' + Number(item.nominal_standar).toLocaleString('id-ID')"></span>
                                            <span class="text-[9px] text-gray-500 font-mono"
                                                x-text="'Sudah Diajukan: Rp' + Number(item.total_ordered_upah).toLocaleString('id-ID')"></span>
                                        </div>
                                    </div>

                                    <div x-show="item.checked" x-collapse class="space-y-3">

                                        <input type="hidden" x-model.number="item.nominal_pengajuan">

                                        <input type="text" x-bind:value="formatRupiah(item.nominal_pengajuan)"
                                            @input="item.nominal_pengajuan = parseNumber($event.target.value)"
                                            placeholder="0"
                                            class="w-full p-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-lg text-left font-mono">

                                        <template x-if="(parseFloat(item.nominal_pengajuan || 0) + parseFloat(item.total_ordered_upah || 0)) > (parseFloat(item.nominal_standar || 0) + 0.01)">
                                             <div class="text-[9px] font-black text-red-500 uppercase">
                                                 ⚠ Pengajuan melebihi budget RAP. Catatan pengawas wajib diisi sebagai alasan.
                                             </div>
                                        </template>

                                        <textarea x-model="item.catatan_pengawas" placeholder="Catatan khusus pekerjaan ini..."
                                            :class="(parseFloat(item.nominal_pengajuan || 0) + parseFloat(item.total_ordered_upah || 0)) > (parseFloat(item.nominal_standar || 0) + 0.01) ? 'border-red-300 focus:ring-red-500 bg-red-50/50 dark:bg-red-900/20' : 'border-gray-200 dark:border-gray-600'"
                                            class="w-full px-3 py-2 text-[11px] border dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg outline-none bg-white min-h-[50px]"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="p-3 bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-100 dark:border-amber-800"
                    x-show="itemsToPay.some(i => i.checked)">
                    <label class="block text-[10px] font-black text-amber-600 uppercase mb-1 flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                            <path fill-rule="evenodd"
                                d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"
                                clip-rule="evenodd" />
                        </svg>
                        Tulis Catatan Sekaligus
                    </label>
                    <textarea @input="itemsToPay.forEach(i => { if(i.checked) i.catatan_pengawas = $el.value })"
                        placeholder="Ketik di sini untuk mengisi semua catatan item yang tercentang..."
                        class="w-full px-3 py-2 text-xs border border-amber-200 dark:border-amber-800 rounded-lg outline-none focus:ring-2 focus:ring-amber-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-white min-h-[60px] shadow-sm"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-600">
                    <button type="button" @click="openUpahModal = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">Batal</button>
                    <button type="submit" :disabled="loadingUpah || !itemsToPay.some(i => i.checked)"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 shadow-sm transition disabled:opacity-50">
                        <span x-text="loadingUpah ? 'Memproses...' : 'Kirim Pengajuan'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
