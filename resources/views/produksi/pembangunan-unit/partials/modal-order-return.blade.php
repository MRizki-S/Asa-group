<template x-teleport="body">
    <div x-show="openReturnModal"
        class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-[2px]" x-cloak>

        <div @click.away="openReturnModal = false"
            class="bg-white dark:bg-gray-900 rounded-2xl max-w-2xl w-full shadow-2xl border border-gray-100 dark:border-gray-800 overflow-hidden flex flex-col max-h-[90vh]">

            <div
                class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center bg-gray-50/50 dark:bg-white/5">
                <div>
                    <h3 class="font-bold text-gray-800 dark:text-white">Ajukan Retur Barang</h3>
                    <p class="text-[10px] text-gray-400 uppercase tracking-widest mt-0.5">Order ID: #<span
                            x-text="returnOrderId"></span></p>
                </div>
                <button @click="openReturnModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form :action="'/produksi/order/' + returnOrderId + '/return'" method="POST"
                class="flex-1 flex flex-col overflow-hidden">
                @csrf
                <div class="p-6 overflow-y-auto custom-scrollbar flex-1 space-y-4">
                    <div
                        class="bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800 p-3 rounded-xl flex gap-3">
                        <i class="fa-solid fa-circle-info text-amber-500 mt-0.5"></i>
                        <p class="text-[11px] text-amber-700 dark:text-amber-400 leading-relaxed">
                            Masukkan jumlah barang yang <strong>rusak atau ingin dikembalikan</strong>. Pastikan jumlah
                            tidak melebihi total barang yang diterima.
                        </p>
                    </div>

                    <div class="space-y-3">
                        <template x-for="(item, index) in returnItems" :key="index">
                            <div
                                class="p-4 border border-gray-100 dark:border-gray-800 rounded-xl bg-gray-50/30 dark:bg-white/[0.02] space-y-3">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="flex-1">
                                        <p class="text-xs font-bold text-gray-700 dark:text-gray-200"
                                            x-text="item.nama"></p>
                                        <p class="text-[10px] text-gray-400 mt-1">
                                            Diterima: <span class="font-mono text-gray-600 dark:text-gray-300"
                                                x-text="parseFloat(item.jumlah)"></span>
                                            <span x-text="item.satuan"></span>
                                        </p>
                                    </div>
                                    <div class="w-32">
                                        <label
                                            class="block text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase mb-1">
                                            Jumlah Retur
                                        </label>
                                        <div class="relative group">
                                            <input type="hidden" :name="'items[' + index + '][detail_id]'"
                                                :value="item.id">

                                            <input type="number" step="any"
                                                :name="'items[' + index + '][jumlah_return]'"
                                                x-model.number="item.retur" :max="item.jumlah" min="0"
                                                :class="item.retur > 0 ?
                                                    'border-orange-400 dark:border-orange-500/50 text-orange-600 bg-orange-50/30' :
                                                    'border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800'"
                                                class="w-full p-2 pr-9 text-xs font-mono font-bold border rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">

                                            <span
                                                class="absolute right-2 top-1/2 -translate-y-1/2 text-[9px] font-bold text-gray-400 group-hover:text-gray-500"
                                                x-text="item.satuan"></span>
                                        </div>
                                    </div>
                                </div>

                                <div x-show="item.retur > 0" x-transition>
                                    <label class="block text-[9px] font-black text-gray-400 uppercase mb-1">Alasan /
                                        Detail Kerusakan</label>
                                    <textarea :name="'items[' + index + '][keterangan_return]'" x-model="item.keterangan" rows="2"
                                        class="w-full p-2 text-xs border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 focus:ring-2 focus:ring-red-500/10 outline-none placeholder:text-gray-400"
                                        placeholder="Contoh: Keramik pecah di pojok, Semen membatu, dll..."></textarea>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div
                    class="p-6 bg-gray-50 dark:bg-white/5 border-t border-gray-100 dark:border-gray-800 flex justify-end gap-3">
                    <button type="button" @click="openReturnModal = false"
                        class="px-5 py-2 text-xs font-bold text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-white transition-colors uppercase">Batal</button>
                    <button type="submit"
                        class="px-6 py-2 text-xs font-bold text-white bg-red-600 rounded-lg hover:bg-red-700 shadow-lg shadow-red-600/20 transition-all uppercase">
                        Kirim Pengajuan Retur
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
