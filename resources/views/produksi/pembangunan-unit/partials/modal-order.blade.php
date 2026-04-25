<div x-show="openRequest" class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/50 backdrop-blur-sm"
    x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100">

    <div @click.away="openRequest = false"
        class="relative w-full transition-all duration-300 p-4 flex flex-col h-[95vh] max-h-[95vh]"
        :class="showAdditional ? 'max-w-4xl' : 'max-w-xl'">

        <div
            class="relative bg-white rounded-xl shadow-xl dark:bg-gray-700 overflow-hidden border border-gray-100 flex flex-col h-full">

            <div class="flex-none flex items-center justify-between p-4 border-b bg-gray-50/50">
                <h3 class="text-lg font-bold text-gray-900">Buat Order Barang (<span x-text="filterType"></span>)</h3>
                <button type="button" @click="openRequest = false" class="text-gray-400 hover:text-gray-900">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                </button>
            </div>

            <form @submit.prevent="submitRequest" class="flex-1 flex flex-col min-h-0 overflow-hidden">

                <div class="flex-1 overflow-y-auto p-5 space-y-4 custom-scrollbar">

                    <div class="flex p-1 bg-gray-100 dark:bg-gray-800 rounded-xl gap-1">
                        <button type="button"
                            @click="filterType = 'stock'; itemsToOrder.forEach(i => i.checked = false); itemsAdditional = []; showAdditional = false"
                            :class="filterType === 'stock' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-500'"
                            class="flex-1 py-2 text-[10px] font-black uppercase rounded-lg transition-all">Barang
                            Stock</button>
                        <button type="button"
                            @click="filterType = 'direct'; itemsToOrder.forEach(i => i.checked = false); itemsAdditional = []; showAdditional = false"
                            :class="filterType === 'direct' ? 'bg-white shadow-sm text-amber-600' : 'text-gray-500'"
                            class="flex-1 py-2 text-[10px] font-black uppercase rounded-lg transition-all">Barang
                            Direct</button>
                    </div>

                    <div class="grid grid-cols-1 gap-6 transition-all duration-300"
                        :class="showAdditional ? 'lg:grid-cols-2' : 'lg:grid-cols-1'">

                        <div class="md:space-y-3" :class="showAdditional ? 'md:border-r md:pr-4' : ''">
                            <div
                                class="flex items-center justify-between p-3 bg-gray-50 border border-gray-200 rounded-xl">
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="checkbox"
                                        @change="itemsToOrder.filter(i => filterType === 'stock' ? i.is_stock : !i.is_stock).forEach(item => item.checked = $el.checked)"
                                        :checked="itemsToOrder.filter(i => filterType === 'stock' ? i.is_stock : !i.is_stock)
                                            .length > 0 && itemsToOrder.filter(i => filterType === 'stock' ? i
                                                .is_stock : !i.is_stock).every(item => item.checked)"
                                        class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span
                                        class="text-[11px] font-black text-gray-500 uppercase tracking-widest group-hover:text-blue-600">Pilih
                                        Semua RAP</span>
                                </label>
                                <span class="text-[10px] font-bold px-2 py-0.5 bg-blue-100 text-blue-600 rounded-full"
                                    x-text="itemsToOrder.filter(i => i.checked).length + ' Terpilih'"></span>
                            </div>

                            <div class="space-y-3">
                                <template x-for="(item, index) in itemsToOrder" :key="index">
                                    <div x-show="(filterType === 'stock' && item.is_stock) || (filterType === 'direct' && !item.is_stock)"
                                        class="p-4 border rounded-xl transition-all"
                                        :class="item.checked ? 'border-blue-200 bg-blue-50/30' : 'border-gray-100 bg-gray-50/50'">
                                        <div class="flex items-start gap-4">
                                            <input type="checkbox" x-model="item.checked"
                                                class="mt-1.5 w-4 h-4 rounded border-gray-300 text-blue-600">
                                            <div class="flex-1 space-y-3">
                                                <div class="flex justify-between items-start">
                                                    <p class="text-sm font-bold text-gray-700"
                                                        x-text="item.nama_barang"></p>
                                                    <span class="text-[10px] font-bold text-blue-600 font-mono"
                                                        x-text="'RAP: ' + Number(item.jumlah_standar).toLocaleString('id-ID') + ' ' + item.satuan"></span>
                                                </div>
                                                <div x-show="item.checked" x-collapse class="mt-3 space-y-3">
                                                    <div class="grid grid-cols-2 gap-3">
                                                        <div>
                                                            <label
                                                                class="block text-[9px] font-black text-gray-400 uppercase mb-1">Jumlah
                                                                Order</label>
                                                            <input type="number" step="0.001"
                                                                x-model.number="item.jumlah_input"
                                                                class="w-full p-2 bg-white border border-gray-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-blue-500/20 outline-none">
                                                        </div>
                                                        <div>
                                                            <label
                                                                class="block text-[9px] font-black text-gray-400 uppercase mb-1">Satuan</label>
                                                            <select :value="item.satuan_id"
                                                                @change="changeSatuanOrder(item, $event.target.value)"
                                                                class="w-full p-2 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500/20 outline-none">
                                                                <template
                                                                    x-for="s in getAvailableSatuan(item.barang_id)"
                                                                    :key="item.barang_id + '-' + s.id">
                                                                    <option :value="s.id" x-text="s.nama"
                                                                        :selected="s.id == item.satuan_id"></option>
                                                                </template>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <template
                                                        x-if="parseFloat(item.jumlah_input) > parseFloat(item.jumlah_standar)">
                                                        <div class="animate-in fade-in slide-in-from-top-1">
                                                            <label
                                                                class="block text-[9px] font-black text-red-500 uppercase mb-1">Alasan
                                                                Melebihi RAP</label>
                                                            <textarea x-model="item.alasan" placeholder="Wajib diisi karena order melebihi jatah RAP..."
                                                                class="w-full p-2 text-[11px] border border-red-200 rounded-lg bg-red-50/50 outline-none focus:ring-1 focus:ring-red-300"></textarea>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div x-show="showAdditional" x-transition class="space-y-3">
                            <div
                                class="flex items-center justify-between p-3 bg-blue-50 border border-blue-100 rounded-xl">
                                <span class="text-[11px] font-black text-blue-600 uppercase tracking-widest">Barang
                                    Tambahan (Luar RAP)</span>
                                <button type="button"
                                    @click="addAdditionalItem(); $nextTick(() => initSelect2(itemsAdditional.length - 1))"
                                    class="text-[10px] font-bold px-3 py-1 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition shadow-sm">
                                    + TAMBAH BARIS
                                </button>
                            </div>

                            <div class="space-y-3">
                                <template x-for="(extra, eIdx) in itemsAdditional" :key="eIdx">
                                    <div class="p-4 border border-blue-100 bg-white rounded-xl relative shadow-sm">
                                        <button type="button" @click="removeAdditionalItem(eIdx)"
                                            class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs z-10">×</button>

                                        <div class="space-y-3">
                                            <div class="space-y-1">
                                                <label class="block text-[9px] font-black text-gray-400 uppercase">Cari
                                                    Barang</label>
                                                <div wire:ignore class="relative">
                                                    <select :id="'barang-select-' + eIdx" x-init="initSelect2(eIdx)"
                                                        class="w-full">
                                                        <option value="0">-- Pilih Barang --</option>
                                                        <template x-for="b in getFilteredBarang(eIdx)"
                                                            :key="b.id">
                                                            <option :value="b.id"
                                                                x-text="b.kode_barang + ' - ' + b.nama_barang"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-2 gap-2">
                                                <div>
                                                    <label
                                                        class="block text-[9px] font-black text-gray-400 uppercase mb-1">Jumlah</label>
                                                    <input type="number" step="0.001"
                                                        x-model.number="extra.jumlah_input"
                                                        class="w-full text-xs p-2 border border-gray-200 rounded-lg outline-none focus:ring-2 focus:ring-blue-500/20">
                                                </div>
                                                <div>
                                                    <label
                                                        class="block text-[9px] font-black text-gray-400 uppercase mb-1">Satuan</label>
                                                    <select x-model="extra.satuan_id"
                                                        @change="const s = getAvailableSatuan(extra.barang_id).find(opt => opt.id == $el.value); extra.satuan = s ? s.nama : ''; extra.faktor_konversi = s ? s.faktor : 1"
                                                        class="w-full text-xs p-2 border border-gray-200 rounded-lg outline-none focus:ring-2 focus:ring-blue-500/20">
                                                        <template x-for="s in getAvailableSatuan(extra.barang_id)"
                                                            :key="extra.barang_id + '-' + s.id">
                                                            <option :value="s.id" x-text="s.nama"
                                                                :selected="s.id == extra.satuan_id"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex-none p-4 border-t border-gray-100 bg-gray-50/80 space-y-3">

                    <div class="p-2 bg-amber-50 rounded-xl border border-amber-100">
                        <label
                            class="block text-[10px] font-black text-amber-600 uppercase mb-1 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                            </svg>
                            Catatan Order
                        </label>
                        <textarea x-model="catatanGlobal" placeholder="Tulis catatan tambahan untuk keseluruhan order ini..."
                            class="w-full px-3 py-1.5 text-xs border border-amber-200 rounded-lg outline-none focus:ring-2 focus:ring-amber-400 bg-white min-h-[50px] shadow-sm"></textarea>
                    </div>

                    <div class="flex items-center justify-between">
                        <button type="button"
                            @click="showAdditional = !showAdditional; if(showAdditional && itemsAdditional.length === 0) addAdditionalItem()"
                            class="flex items-center gap-2 px-3 py-2 rounded-lg transition-all"
                            :class="showAdditional ? 'bg-amber-50 text-amber-700' : 'bg-blue-50 text-blue-700'">
                            <i class="fa-solid"
                                :class="showAdditional ? 'fa-minus-circle' : 'fa-plus-circle text-xs'"></i>
                            <span class="text-[10px] font-black uppercase tracking-wider"
                                x-text="showAdditional ? 'Sembunyikan Luar RAP' : 'Tambah Barang Luar RAP'"></span>
                        </button>

                        <div class="flex gap-3">
                            <button type="button" @click="openRequest = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 transition">Batal</button>
                            <button type="submit"
                                :disabled="loadingRequest || (!itemsToOrder.some(i => i.checked) && itemsAdditional.length === 0)"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm transition disabled:opacity-50">
                                Kirim Order
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .select2-container--default .select2-selection--single {
        border-color: #e5e7eb !important;
        border-radius: 0.5rem !important;
        height: 38px !important;
        display: flex !important;
        align-items: center !important;
    }
</style>
