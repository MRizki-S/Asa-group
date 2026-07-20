<template x-teleport="body">
    <div x-show="openReturnModal"
        class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100">

        <div @click.away="openReturnModal = false"
            class="bg-white dark:bg-gray-900 rounded-3xl max-w-2xl w-full shadow-2xl border border-gray-100 dark:border-gray-800 overflow-hidden flex flex-col max-h-[92vh]">

            {{-- Header --}}
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center bg-gradient-to-r from-red-500/10 via-red-500/5 to-transparent dark:from-red-950/40 dark:to-transparent">
                <div>
                    <h3 class="font-bold text-gray-800 dark:text-white text-base">
                        Retur Barang
                    </h3>
                    <p class="text-[10px] text-gray-400 font-medium uppercase tracking-widest mt-0.5">
                        QC : <span class="font-bold text-red-500" x-text="returnQcName"></span>
                    </p>
                </div>
                <button @click="openReturnModal = false"
                    class="px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-400 hover:text-gray-600 dark:hover:text-white text-xs font-bold transition-colors">
                    Tutup
                </button>
            </div>

            {{-- Loading state --}}
            <div x-show="returnLoading" class="flex-1 flex items-center justify-center py-20">
                <div class="text-center space-y-3">
                    <p class="text-xs text-gray-400 font-medium">Memuat data barang QC...</p>
                </div>
            </div>

            {{-- Main content --}}
            <div x-show="!returnLoading" class="flex-1 flex flex-col overflow-hidden">
                <div class="p-6 overflow-y-auto custom-scrollbar flex-1 space-y-5">

                    {{-- Info banner --}}
                    <div class="bg-amber-500/10 border border-amber-500/20 p-3.5 rounded-2xl">
                        <p class="text-[11px] text-amber-700 dark:text-amber-400 leading-relaxed font-medium">
                            Retur bersifat per-QC. Data barang di bawah merupakan akumulasi dari seluruh order yang telah dikonfirmasi gudang pada QC ini.
                        </p>
                    </div>

                    {{-- Tanggal & Catatan Pengajuan Retur --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-gray-50/50 dark:bg-gray-800/30 p-4 rounded-2xl border border-gray-100 dark:border-gray-800">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1.5">Tanggal Retur</label>
                            <input type="date" x-model="returnTanggal"
                                class="w-full text-xs font-semibold border border-gray-200 dark:border-gray-700 rounded-xl p-2.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all">
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1.5">Catatan Retur (Pengajuan)</label>
                            <textarea x-model="returnCatatan" rows="1" placeholder="Contoh: Sisa semen dan bata proyek pengerjaan QC..."
                                class="w-full text-xs border border-gray-200 dark:border-gray-700 rounded-xl p-2.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none resize-none placeholder:text-gray-400 transition-all"></textarea>
                        </div>
                    </div>

                    {{-- Daftar Item Retur --}}
                    <div>
                        <div class="flex items-center justify-between mb-3 px-1">
                            <h5 class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Daftar Barang Retur</h5>
                            <span class="text-[10px] font-bold text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-md"
                                x-show="returnItems.length > 0" x-text="returnItems.length + ' Item'"></span>
                        </div>

                        <div class="space-y-3.5">
                            <template x-for="(item, index) in returnItems" :key="index">
                                <div class="p-4 border border-gray-200 dark:border-gray-800 rounded-2xl bg-white dark:bg-gray-800/40 shadow-sm space-y-3.5 transition-all">
                                    {{-- Remove button --}}
                                    <div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-800">
                                        <span class="text-[10px] font-black text-red-500 uppercase tracking-wider bg-red-50 dark:bg-red-950/40 px-2 py-0.5 rounded-md border border-red-100 dark:border-red-900/30">
                                            Item #<span x-text="index + 1"></span>
                                        </span>
                                        <button type="button" @click="removeReturnItem(index)"
                                            class="text-[10px] text-red-400 hover:text-red-600 font-bold transition-colors">
                                            Hapus
                                        </button>
                                    </div>

                                    {{-- Select Barang --}}
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Pilih Barang</label>
                                        <select :id="'return-barang-select-' + index"
                                            class="w-full text-xs font-semibold border border-gray-200 dark:border-gray-700 rounded-xl p-2.5 bg-gray-50/50 dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-red-500/20 outline-none">
                                            <option value="">-- Pilih Barang --</option>
                                            <template x-for="s in getAvailableReturnBarang(index)" :key="s.barang_id">
                                                <option :value="s.barang_id" :selected="item.barang_id == s.barang_id" x-text="s.nama_barang"></option>
                                            </template>
                                        </select>
                                    </div>

                                    {{-- Info sisa (muncul setelah barang dipilih, terkonversi sesuai satuan yang dipilih) --}}
                                    <div x-show="item.barang_id" x-transition class="grid grid-cols-3 gap-2">
                                        <div class="bg-gray-50 dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700 rounded-xl p-2.5 text-center">
                                            <p class="text-[8px] text-gray-400 uppercase font-black tracking-wider mb-0.5">Diterima</p>
                                            <p class="text-xs font-black text-gray-700 dark:text-gray-200">
                                                <span x-text="item.total_diterima_display"></span>
                                                <span class="text-[9px] font-medium text-gray-400 ml-0.5" x-text="item.satuan_selected_nama"></span>
                                            </p>
                                        </div>
                                        <div class="bg-orange-50/50 dark:bg-orange-950/20 border border-orange-200 dark:border-orange-900/40 rounded-xl p-2.5 text-center">
                                            <p class="text-[8px] text-orange-500 uppercase font-black tracking-wider mb-0.5">Sudah Retur</p>
                                            <p class="text-xs font-black text-orange-600 dark:text-orange-400">
                                                <span x-text="item.sudah_retur_display"></span>
                                                <span class="text-[9px] font-medium text-gray-400 ml-0.5" x-text="item.satuan_selected_nama"></span>
                                            </p>
                                        </div>
                                        <div class="bg-red-50/50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/40 rounded-xl p-2.5 text-center">
                                            <p class="text-[8px] text-red-500 uppercase font-black tracking-wider mb-0.5">Sisa Retur</p>
                                            <p class="text-xs font-black text-red-600 dark:text-red-400">
                                                <span x-text="item.sisa_retur_display"></span>
                                                <span class="text-[9px] font-medium text-gray-400 ml-0.5" x-text="item.satuan_selected_nama"></span>
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Satuan & Jumlah --}}
                                    <div x-show="item.barang_id" x-transition class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Satuan Retur</label>
                                            <select x-model="item.satuan_id"
                                                @change="onReturnSatuanChange(index)"
                                                class="w-full text-xs font-semibold border border-gray-200 dark:border-gray-700 rounded-xl p-2.5 bg-gray-50/50 dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-red-500/20 outline-none">
                                                <template x-for="s in item.satuan_options" :key="s.satuan_id">
                                                    <option :value="s.satuan_id" :selected="item.satuan_id == s.satuan_id" x-text="s.nama_satuan"></option>
                                                </template>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Jumlah Retur</label>
                                            <div class="relative">
                                                <input type="number" x-model.number="item.jumlah_input" step="any" min="0.001"
                                                    :max="item.max_jumlah_input"
                                                    :class="item.jumlah_input > item.max_jumlah_input && item.max_jumlah_input > 0 ? 'border-red-400 text-red-600 ring-2 ring-red-500/20' : 'border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200'"
                                                    class="w-full text-xs font-semibold border rounded-xl p-2.5 pr-12 bg-gray-50/50 dark:bg-gray-800 focus:ring-2 focus:ring-red-500/20 outline-none font-mono">
                                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[9px] text-gray-400 font-bold uppercase"
                                                    x-text="item.satuan_selected_nama"></span>
                                            </div>
                                            {{-- Over-limit warning --}}
                                            <p x-show="item.jumlah_input > item.max_jumlah_input && item.max_jumlah_input > 0"
                                                class="text-[10px] text-red-500 font-bold mt-1">
                                                ⚠ Melebihi sisa yang dapat dikembalikan
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Keterangan --}}
                                    <div x-show="item.barang_id" x-transition>
                                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Keterangan Item (opsional)</label>
                                        <input type="text" x-model="item.keterangan" placeholder="Contoh: Barang pecah, semen membatu..."
                                            class="w-full text-xs border border-gray-200 dark:border-gray-700 rounded-xl p-2.5 bg-gray-50/50 dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-red-500/20 outline-none placeholder:text-gray-400">
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Tambah Barang --}}
                        <button type="button" @click="addReturnItem()"
                            class="mt-3.5 w-full py-3 border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-2xl text-xs font-bold text-gray-400 hover:border-red-400 hover:text-red-600 hover:bg-red-50/30 dark:hover:bg-red-950/20 transition-all flex items-center justify-center">
                            + Tambah Barang
                        </button>
                    </div>

                </div>

                {{-- Footer --}}
                <div class="p-5 bg-gray-50/80 dark:bg-gray-900/60 border-t border-gray-100 dark:border-gray-800 flex justify-end gap-3">
                    <button type="button" @click="openReturnModal = false"
                        class="px-5 py-2.5 text-xs font-bold text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-white transition-colors uppercase tracking-wider">
                        Batal
                    </button>
                    <button type="button" @click="submitReturn()"
                        :disabled="returnSubmitting || returnItems.length === 0"
                        class="px-6 py-2.5 text-xs font-bold text-white bg-red-600 rounded-xl hover:bg-red-700 shadow-lg shadow-red-600/20 transition-all uppercase tracking-wider disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                        <span x-text="returnSubmitting ? 'Menyimpan...' : 'Ajukan Retur'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
