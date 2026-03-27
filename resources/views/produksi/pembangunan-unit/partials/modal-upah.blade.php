<div x-show="openUpahModal" class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/50 backdrop-blur-sm"
    x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100">

    <div @click.away="openUpahModal = false" class="relative w-full max-w-xl p-4">
        <div class="relative bg-white rounded-xl shadow-xl dark:bg-gray-700 overflow-hidden border border-gray-100">
            <div class="flex items-center justify-between p-4 border-b bg-gray-50/50">
                <h3 class="text-lg font-bold text-gray-900">Form Pengajuan Upah</h3>
                <button type="button" @click="openUpahModal = false" class="text-gray-400 hover:text-gray-900">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                </button>
            </div>

            <form @submit.prevent="submitUpah" class="p-5 space-y-4">

                {{-- Header Action: Select All --}}
                <div class="flex items-center justify-between px-1">
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="checkbox" @change="itemsToPay.forEach(i => i.checked = $el.checked)"
                            :checked="itemsToPay.length > 0 && itemsToPay.every(i => i.checked)"
                            class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span
                            class="text-[11px] font-black text-gray-500 uppercase tracking-widest group-hover:text-blue-600">Pilih
                            Semua</span>
                    </label>
                    <span class="text-[10px] text-gray-400 font-bold"
                        x-text="itemsToPay.filter(i => i.checked).length + ' Item Terpilih'"></span>
                </div>

                {{-- Container List Items --}}
                <div class="space-y-3 max-h-[45vh] overflow-y-auto pr-1 custom-scrollbar">
                    <template x-for="(item, index) in itemsToPay" :key="index">
                        <div class="p-4 border rounded-xl transition-all"
                            :class="item.checked ? 'border-blue-200 bg-blue-50/30' : 'border-gray-100 bg-gray-50/50'">

                            <div class="flex items-start gap-4">
                                <input type="checkbox" x-model="item.checked"
                                    class="mt-1.5 w-4 h-4 rounded border-gray-300 text-blue-600">

                                <div class="flex-1 space-y-3">
                                    <div class="flex justify-between items-start">
                                        <p class="text-sm font-bold text-gray-700" x-text="item.nama_upah"></p>
                                        <span class="text-[10px] font-bold text-blue-600 font-mono"
                                            x-text="'Maks: Rp' + Number(item.nominal_standar).toLocaleString('id-ID')"></span>
                                    </div>

                                    <div x-show="item.checked" x-collapse class="space-y-3">
                                        <input type="number" x-model.number="item.nominal_pengajuan"
                                            placeholder="Masukkan nominal Rp..."
                                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:border-blue-600 outline-none font-bold">

                                        <textarea x-model="item.catatan_pengawas" placeholder="Catatan khusus pekerjaan ini..."
                                            class="w-full px-3 py-2 text-[11px] border border-gray-200 rounded-lg outline-none bg-white min-h-[50px]"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- FEATURE TERBAIK: Catatan Global Auto-Fill --}}
                <div class="p-3 bg-amber-50 rounded-xl border border-amber-100"
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
                        class="w-full px-3 py-2 text-xs border border-amber-200 rounded-lg outline-none focus:ring-2 focus:ring-amber-400 bg-white min-h-[60px] shadow-sm"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button" @click="openUpahModal = false"
                        class="px-5 py-2 text-xs font-bold text-gray-500 uppercase">Batal</button>
                    <button type="submit" :disabled="loadingUpah || !itemsToPay.some(i => i.checked)"
                        class="px-6 py-2 text-xs font-bold text-white bg-blue-600 rounded-xl hover:bg-blue-700 shadow-md disabled:opacity-50 uppercase tracking-widest">
                        Kirim Pengajuan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
