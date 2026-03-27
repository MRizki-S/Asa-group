<div x-show="openRequest" class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/50 backdrop-blur-sm"
    x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

    <div @click.away="openRequest = false" class="relative w-full max-w-2xl p-4"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="scale-95 opacity-0"
        x-transition:enter-end="scale-100 opacity-100">

        <div class="relative bg-white rounded-xl shadow-lg dark:bg-gray-700 overflow-hidden">
            <div class="flex items-center justify-between p-4 border-b dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Buat Order Barang Gudang
                </h3>
                <button type="button" @click="openRequest = false"
                    class="text-gray-400 hover:text-gray-900 hover:bg-gray-200 rounded-lg w-8 h-8 flex items-center justify-center dark:hover:bg-gray-600 dark:hover:text-white">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                </button>
            </div>

            <form @submit.prevent="submitRequest" class="p-4 space-y-4">
                <div class="max-h-[50vh] overflow-y-auto space-y-3 pr-1 custom-scrollbar">
                    <template x-for="(item, index) in itemsToOrder" :key="index">
                        <div class="p-3 rounded-lg border transition-all"
                            :class="item.checked ? 'border-blue-200 bg-blue-50/30' : 'border-gray-100 bg-gray-50/30'">
                            <div class="flex items-start gap-3">
                                <input type="checkbox" x-model="item.checked"
                                    class="mt-1 w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <div class="flex-1">
                                    <div class="flex justify-between items-center mb-2">
                                        <p class="text-sm font-bold text-gray-700 dark:text-white"
                                            x-text="item.nama_barang"></p>
                                        <span class="text-[10px] text-gray-500 font-mono" x-text="item.satuan"></span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3" x-show="item.checked">
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-700 uppercase mb-1">Jml
                                                Request</label>
                                            <input type="number" step="0.001" x-model.number="item.jumlah_input"
                                                class="w-full px-2 py-1.5 rounded-md border border-gray-300 text-sm text-gray-700 focus:ring-blue-600 focus:border-blue-600 outline-none">
                                        </div>
                                        <div>
                                            <label
                                                class="block text-[10px] font-bold text-gray-700 uppercase mb-1">Limit
                                                RAP</label>
                                            <p class="py-1.5 text-sm font-bold text-gray-400"
                                                x-text="Number(item.jumlah_standar).toLocaleString('id-ID')"></p>
                                        </div>
                                    </div>
                                    <div class="mt-2"
                                        x-show="item.checked && parseFloat(item.jumlah_input) > parseFloat(item.jumlah_standar)">
                                        <textarea x-model="item.alasan" placeholder="Alasan melebihi RAP..."
                                            class="w-full text-[11px] p-2 rounded-md border-red-200 bg-red-50 text-gray-700" rows="1"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Catatan Tambahan</label>
                    <textarea x-model="catatanGlobal"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 outline-none focus:ring-blue-600 focus:border-blue-600"
                        rows="2" placeholder="Tulis catatan jika ada..."></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" @click="openRequest = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 transition">
                        Batal
                    </button>
                    <button type="submit" :disabled="loadingRequest"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 shadow-sm transition disabled:opacity-50">
                        <span x-show="!loadingRequest">Buat Request</span>
                        <span x-show="loadingRequest" class="flex items-center gap-2"><i
                                class="fa-solid fa-spinner animate-spin"></i> Loading</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
