<template x-teleport="body">
    <div x-show="openReturnModal"
        class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-[2px]" x-cloak>

        <div @click.away="openReturnModal = false"
            class="bg-white dark:bg-gray-900 rounded-2xl max-w-3xl w-full shadow-2xl border border-gray-100 dark:border-gray-800 overflow-hidden flex flex-col max-h-[90vh]">

            <div
                class="px-6 py-5 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center bg-gray-50/50 dark:bg-white/5">
                <div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">Ajukan Retur Barang</h3>
                    <p class="text-xs text-gray-400 uppercase tracking-widest mt-1">Order ID: #<span
                            x-text="returnOrderId"></span></p>
                </div>
                <button @click="openReturnModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <form :action="'{{ route('produksi.order.storeReturn', ':orderId') }}'.replace(':orderId', returnOrderId)" method="POST"
                class="flex-1 flex flex-col overflow-hidden">
                @csrf
                <div class="p-6 overflow-y-auto custom-scrollbar flex-1 space-y-5">

                    <div
                        class="bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800 p-4 rounded-xl flex gap-3">
                        <i class="fa-solid fa-circle-info text-amber-500 mt-0.5 text-lg"></i>
                        <p class="text-sm text-amber-700 dark:text-amber-400 leading-relaxed">
                            Masukkan jumlah barang yang <strong>rusak atau ingin dikembalikan</strong>. Pastikan jumlah
                            tidak melebihi total barang yang diterima.
                        </p>
                    </div>

                    <div class="space-y-4">
                        <template x-for="(item, index) in returnItems" :key="index">
                            <div
                                class="p-5 border border-gray-100 dark:border-gray-800 rounded-xl bg-gray-50/30 dark:bg-white/[0.02] space-y-4">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="flex-1">
                                        <p class="text-base font-bold text-gray-700 dark:text-gray-200"
                                            x-text="item.nama"></p>
                                        <p class="text-xs text-gray-500 mt-1.5">
                                            Diterima: <span class="font-mono font-medium text-gray-700 dark:text-gray-300"
                                                x-text="parseFloat(item.jumlah)"></span>
                                            <span x-text="item.satuan"></span>
                                        </p>
                                    </div>
                                    <div class="w-40">
                                        <label
                                            class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase mb-1.5">
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
                                                class="w-full p-2.5 pr-12 text-sm font-mono font-bold border rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">

                                            <span
                                                class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-gray-400 group-hover:text-gray-500"
                                                x-text="item.satuan"></span>
                                        </div>
                                    </div>
                                </div>

                                <div x-show="item.retur > 0" x-transition>
                                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1.5">Alasan /
                                        Detail Kerusakan</label>
                                    <textarea :name="'items[' + index + '][keterangan_return]'" x-model="item.keterangan" rows="2"
                                        class="w-full p-3 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 focus:ring-2 focus:ring-red-500/10 outline-none placeholder:text-gray-400"
                                        placeholder="Contoh: Keramik pecah di pojok, Semen membatu, dll..."></textarea>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div
                    class="p-6 bg-gray-50 dark:bg-white/5 border-t border-gray-100 dark:border-gray-800 flex justify-end gap-3">
                    <button type="button" @click="openReturnModal = false"
                        class="px-6 py-2.5 text-sm font-bold text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-white transition-colors uppercase">Batal</button>
                    <button type="submit"
                        class="px-6 py-2.5 text-sm font-bold text-white bg-red-600 rounded-lg hover:bg-red-700 uppercase">
                        Kirim Pengajuan Retur
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
