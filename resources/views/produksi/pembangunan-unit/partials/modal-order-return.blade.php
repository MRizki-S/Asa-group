<template x-teleport="body">
    <div x-show="openReturnModal"
        class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-[2px]"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100">

        <div @click.away="openReturnModal = false"
            class="bg-white dark:bg-gray-900 rounded-2xl max-w-2xl w-full shadow-2xl border border-gray-100 dark:border-gray-800 overflow-hidden flex flex-col max-h-[92vh]">

            {{-- Header --}}
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center bg-red-50/60 dark:bg-red-900/20">
                <div>
                    <h3 class="font-bold text-gray-800 dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-rotate-left text-red-500"></i>
                        Return Barang
                    </h3>
                    <p class="text-[10px] text-gray-400 uppercase tracking-widest mt-0.5">
                        QC : <span class="font-bold text-red-500" x-text="returnQcName"></span>
                    </p>
                </div>
                <button @click="openReturnModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            {{-- Loading state --}}
            <div x-show="returnLoading" class="flex-1 flex items-center justify-center py-20">
                <div class="text-center space-y-3">
                    <svg class="animate-spin h-8 w-8 text-red-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 5.373 22 12h-4z"></path>
                    </svg>
                    <p class="text-xs text-gray-400">Memuat data barang...</p>
                </div>
            </div>

            {{-- Main content --}}
            <div x-show="!returnLoading" class="flex-1 flex flex-col overflow-hidden">
                <div class="p-6 overflow-y-auto custom-scrollbar flex-1 space-y-5">

                    {{-- Info banner --}}
                    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800 p-3 rounded-xl flex gap-3">
                        <i class="fa-solid fa-circle-info text-amber-500 mt-0.5 shrink-0"></i>
                        <p class="text-[11px] text-amber-700 dark:text-amber-400 leading-relaxed">
                            Return bersifat per-QC. Data di bawah merupakan akumulasi seluruh order yang sudah dikonfirmasi gudang pada QC ini.
                        </p>
                    </div>



                    {{-- Tanggal --}}
                    <div>
                        <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Tanggal Return</label>
                        <input type="date" x-model="returnTanggal"
                            class="w-full text-sm border border-gray-200 dark:border-gray-700 rounded-lg p-2.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-red-500/20 focus:border-red-400 outline-none">
                    </div>

                    {{-- Daftar Item Return --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <h5 class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Daftar Barang Return</h5>
                            <span class="text-[9px] text-gray-400" x-show="returnItems.length > 0" x-text="returnItems.length + ' Item'"></span>
                        </div>

                        <div class="space-y-3">
                            <template x-for="(item, index) in returnItems" :key="index">
                                <div class="p-4 border border-gray-100 dark:border-gray-700 rounded-xl bg-gray-50/40 dark:bg-white/[0.02] space-y-3">
                                    {{-- Remove button --}}
                                    <div class="flex items-start justify-between gap-3">
                                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-wider mt-1">Item <span x-text="index + 1"></span></p>
                                        <button type="button" @click="removeReturnItem(index)"
                                            class="text-[9px] text-red-400 hover:text-red-600 font-bold flex items-center gap-1">
                                            <i class="fa-solid fa-trash-can"></i> Hapus
                                        </button>
                                    </div>

                                    {{-- Select Barang --}}
                                    <div>
                                        <label class="block text-[9px] font-black text-gray-400 uppercase mb-1">Barang</label>
                                        <select :id="'return-barang-select-' + index"
                                            class="w-full text-sm border border-gray-200 dark:border-gray-700 rounded-lg p-2.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-red-500/20 outline-none">
                                            <option value="">-- Pilih Barang --</option>
                                            <template x-for="s in getAvailableReturnBarang(index)" :key="s.barang_id">
                                                <option :value="s.barang_id" :selected="item.barang_id == s.barang_id" x-text="s.nama_barang"></option>
                                            </template>
                                        </select>
                                    </div>

                                    {{-- Info sisa (muncul setelah barang dipilih) --}}
                                    <div x-show="item.barang_id" x-transition class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                        <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-lg p-2 text-center">
                                            <p class="text-[8px] text-gray-400 uppercase font-bold mb-0.5">Diterima</p>
                                            <p class="text-xs font-black text-gray-700 dark:text-gray-200">
                                                <span x-text="item.total_diterima"></span>
                                                <span class="text-[9px] font-medium text-gray-400 ml-0.5" x-text="item.satuan_display"></span>
                                            </p>
                                        </div>
                                        <div class="bg-white dark:bg-gray-800 border border-orange-100 dark:border-orange-900/30 rounded-lg p-2 text-center">
                                            <p class="text-[8px] text-orange-400 uppercase font-bold mb-0.5">Sudah Return</p>
                                            <p class="text-xs font-black text-orange-600">
                                                <span x-text="item.sudah_return"></span>
                                                <span class="text-[9px] font-medium text-gray-400 ml-0.5" x-text="item.satuan_display"></span>
                                            </p>
                                        </div>
                                        <div class="bg-white dark:bg-gray-800 border border-red-100 dark:border-red-900/30 rounded-lg p-2 text-center">
                                            <p class="text-[8px] text-red-500 uppercase font-bold mb-0.5">Sisa Return</p>
                                            <p class="text-xs font-black text-red-600">
                                                <span x-text="item.sisa_return"></span>
                                                <span class="text-[9px] font-medium text-gray-400 ml-0.5" x-text="item.satuan_display"></span>
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Satuan & Jumlah --}}
                                    <div x-show="item.barang_id" x-transition class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-[9px] font-black text-gray-400 uppercase mb-1">Satuan</label>
                                            <select x-model="item.satuan_id"
                                                @change="onReturnSatuanChange(index)"
                                                class="w-full text-sm border border-gray-200 dark:border-gray-700 rounded-lg p-2.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-red-500/20 outline-none">
                                                <template x-for="s in getBarangSatuanOptions(item.barang_id)" :key="s.id">
                                                    <option :value="s.id" :selected="item.satuan_id == s.id" x-text="s.nama"></option>
                                                </template>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-[9px] font-black text-gray-400 uppercase mb-1">Jumlah Return</label>
                                            <div class="relative">
                                                <input type="number" x-model.number="item.jumlah_input" step="any" min="0.001"
                                                    :max="item.max_jumlah_input"
                                                    :class="item.jumlah_input > item.max_jumlah_input && item.max_jumlah_input > 0 ? 'border-red-400 text-red-600' : 'border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200'"
                                                    class="w-full text-sm border rounded-lg p-2.5 pr-10 bg-white dark:bg-gray-800 focus:ring-2 focus:ring-red-500/20 outline-none font-mono">
                                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[9px] text-gray-400 font-bold"
                                                    x-text="item.satuan_selected_nama"></span>
                                            </div>
                                            {{-- Over-limit warning --}}
                                            <p x-show="item.jumlah_input > item.max_jumlah_input && item.max_jumlah_input > 0"
                                                class="text-[10px] text-red-500 mt-1">
                                                ⚠ Melebihi sisa yang dapat dikembalikan
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Keterangan --}}
                                    <div x-show="item.barang_id" x-transition>
                                        <label class="block text-[9px] font-black text-gray-400 uppercase mb-1">Keterangan (opsional)</label>
                                        <input type="text" x-model="item.keterangan" placeholder="Contoh: Barang pecah, semen membatu..."
                                            class="w-full text-sm border border-gray-200 dark:border-gray-700 rounded-lg p-2.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-red-500/20 outline-none placeholder:text-gray-400">
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Tambah Barang --}}
                        <button type="button" @click="addReturnItem()"
                            class="mt-3 w-full py-2.5 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl text-[11px] font-bold text-gray-400 hover:border-red-300 hover:text-red-500 hover:bg-red-50/30 transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-plus"></i>
                            Tambah Barang
                        </button>
                    </div>

                </div>

                {{-- Footer --}}
                <div class="p-5 bg-gray-50 dark:bg-white/5 border-t border-gray-100 dark:border-gray-800 flex justify-end gap-3">
                    <button type="button" @click="openReturnModal = false"
                        class="px-5 py-2 text-xs font-bold text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-white transition-colors uppercase">
                        Batal
                    </button>
                    <button type="button" @click="submitReturn()"
                        :disabled="returnSubmitting || returnItems.length === 0"
                        class="px-6 py-2.5 text-xs font-bold text-white bg-red-600 rounded-lg hover:bg-red-700 shadow-lg shadow-red-600/20 transition-all uppercase disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                        <svg x-show="returnSubmitting" class="animate-spin h-3.5 w-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 5.373 22 12h-4z"></path>
                        </svg>
                        <span x-text="returnSubmitting ? 'Menyimpan...' : 'Ajukan Return'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
