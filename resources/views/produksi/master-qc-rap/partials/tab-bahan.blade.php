<div x-show="tab === 'bahan'" class="pt-6" x-cloak>
    <div class="space-y-4">
        <template x-if="qcGroups.length === 0">
            <div
                class="flex flex-col items-center justify-center p-10 border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-xl bg-gray-50/50 dark:bg-gray-800/50">
                <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                    </path>
                </svg>
                <p class="text-gray-500 dark:text-gray-400 font-medium text-center">
                    Belum ada langkah QC yang ditambahkan.<br>
                    <span class="text-xs">Silahkan tambahkan langkah QC terlebih dahulu pada tab Langkah QC.</span>
                </p>
            </div>
        </template>

        <template x-for="(qc, qIndex) in qcGroups" :key="qIndex">
            <div
                class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden">
                <div @click="toggleAccordion(qIndex)"
                    class="flex justify-between items-center p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors bg-gray-50/50 dark:bg-gray-800/50">
                    <div class="flex items-center gap-3">
                        <span
                            class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-600 text-[10px] font-bold"
                            x-text="qc.qc_ke"></span>
                        <h4 class="font-bold text-gray-700 dark:text-white" x-text="'RAP Bahan: ' + qc.nama_qc"></h4>
                    </div>
                    <div class="flex items-center gap-4">
                        <span
                            class="text-[10px] px-2 py-0.5 bg-gray-200 dark:bg-gray-600 rounded-full text-gray-600 dark:text-gray-300"
                            x-text="bahanGroups.filter(b => b.urutan_idx == qIndex).length + ' Item'"></span>
                        <svg class="w-5 h-5 text-gray-400 transition-transform duration-200"
                            :class="openAccordions[qIndex] ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>

                <div x-show="openAccordions[qIndex]" x-collapse>
                    <div class="p-5 border-t border-gray-200 dark:border-gray-700 overflow-x-auto">
                        <div class="flex justify-end mb-4">
                            <button type="button" @click="addBahan(qIndex)"
                                class="px-3 py-1.5 bg-green-600 text-white rounded-lg text-xs hover:bg-green-700 shadow-sm transition flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                Tambah Bahan
                            </button>
                        </div>

                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-white">
                                <tr>
                                    <th class="px-4 py-3 border-b dark:border-gray-600">Barang</th>
                                    <th class="px-4 py-3 border-b dark:border-gray-600">Jumlah</th>
                                    <th class="px-4 py-3 border-b dark:border-gray-600">Satuan</th>
                                    <th class="px-4 py-3 text-center border-b dark:border-gray-600">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(bahan, bIndex) in bahanGroups" :key="bIndex">
                                    <template x-if="bahan.urutan_idx == qIndex">
                                        <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700">
                                            <input type="hidden" :name="`bahan[${bIndex}][urutan_idx]`"
                                                :value="qIndex">
                                            <td class="px-2 py-3 w-[30%]">
                                                <select :name="`bahan[${bIndex}][barang_id]`" x-model="bahan.barang_id"
                                                    x-select2="bahan.barang_id"
                                                    class="w-full p-2 bg-white dark:bg-gray-700 ...">
                                                    <option value="0">-- Pilih Barang --</option>
                                                    @foreach ($allBarang as $item)
                                                        <option value="{{ $item->id }}">{{ $item->kode_barang }} -
                                                            {{ $item->nama_barang }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="px-2 py-3">
                                                <input type="number"
                                                    :name="`bahan[${bIndex}][jumlah_kebutuhan_standar]`"
                                                    x-model="bahan.jumlah_kebutuhan_standar" step="0.01"
                                                    class="w-full p-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-lg">
                                            </td>
                                            <td class="px-2 py-3 w-[30%]">
                                                <select :name="`bahan[${bIndex}][satuan_id]`" x-model="bahan.satuan_id"
                                                    x-select2="bahan.satuan_id"
                                                    class="w-full p-2 bg-white dark:bg-gray-700 ...">
                                                    <template x-for="s in getAvailableSatuan(bahan.barang_id)"
                                                        :key="s.id">
                                                        <option :value="s.id" x-text="s.nama"
                                                            :selected="s.id == bahan.satuan_id"></option>
                                                    </template>
                                                </select>
                                            </td>
                                            <td class="px-2 py-3 text-center">
                                                <button type="button" @click="removeBahan(bIndex)"
                                                    class="text-red-500 hover:text-red-700 transition">
                                                    <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.directive('select2', (el, {
            expression
        }, {
            evaluateLater,
            cleanup
        }) => {
            const setValue = evaluateLater(expression);

            $(el).select2({
                placeholder: "-- Pilih --",
                theme: 'bootstrap4',
                allowClear: true,
                width: '100%'
            }).on('change', function() {
                let value = $(this).val();
                Alpine.evaluate(el, `${expression} = '${value}'`);
            });

            const observer = new MutationObserver(() => {
                $(el).trigger('change.select2');
            });
            observer.observe(el, {
                childList: true
            });

            const watcher = Alpine.watch(() => el._x_model ? el._x_model.get() : null, (val) => {
                $(el).val(val).trigger('change.select2');
            });

            cleanup(() => {
                $(el).select2('destroy');
            });
        });
    });
</script>
