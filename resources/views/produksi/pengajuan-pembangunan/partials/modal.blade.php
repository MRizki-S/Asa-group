<div x-show="isModalOpen" class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/50 backdrop-blur-sm"
    x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

    <div class="relative w-full max-w-md p-4">
        <div class="bg-white rounded-xl shadow-2xl dark:bg-gray-800">
            <div class="flex items-center justify-between p-4 border-b dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Assign Pengawas Unit</h3>
                <button type="button" @click="closeModal()" class="text-gray-400 hover:text-gray-900">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form :action="'{{ route('produksi.pembangunanUnit.store') }}'" method="POST" class="p-4 space-y-4">
                @csrf
                <input type="hidden" name="pengajuan_id" :value="selectedItem?.id">

                <div class="p-3 bg-blue-50 dark:bg-blue-900/30 rounded-lg border border-blue-100 dark:border-blue-800">
                    <p class="text-sm font-bold text-blue-800 dark:text-blue-300"
                        x-text="'Unit: ' + selectedItem?.unit"></p>
                    <p class="text-xs text-blue-600 dark:text-blue-400" x-text="selectedItem?.perumahaan"></p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Pengawas Unit</label>
                        <select name="pengawas_id" required id="selectPengawas"
                            class="w-full text-gray-700 rounded-lg border-gray-200 bg-gray-50 text-sm focus:ring-blue-500 dark:bg-gray-700 dark:text-white transition-all">
                            <option value="">-- Pilih Pengawas --</option>
                            @foreach ($allPengawas as $pm)
                                <option value="{{ $pm->id }}">{{ $pm->nama_lengkap }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">QC (Quality Control)</label>
                        <select name="qc_container_id" required id="selectQC"
                            class="w-full text-gray-700 rounded-lg border-gray-200 bg-gray-50 text-sm focus:ring-blue-500 dark:bg-gray-700 dark:text-white transition-all">
                            <option value="">-- Pilih QC --</option>
                            @foreach ($allQcContainer as $qc)
                                <option value="{{ $qc->id }}">{{ $qc->nama_container }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Tanggal Mulai <span
                                class="text-red-500">*</span></label>
                        <div class="relative" x-data="{ simpan: '{{ date('Y-m-d') }}' }">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none z-10">
                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                                </svg>
                            </div>
                            <input type="text" required x-init="flatpickr($el, {
                                dateFormat: 'd-m-Y',
                                defaultDate: '{{ date('d-m-Y') }}',
                                static: false,
                                position: 'above',
                                disableMobile: true,
                                onChange: (selectedDates, dateStr, instance) => {
                                    simpan = instance.formatDate(selectedDates[0], 'Y-m-d');
                                }
                            })"
                                class="w-full pl-10 pr-3 py-2 text-gray-700 rounded-lg border border-gray-200 bg-gray-50 text-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all outline-none"
                                placeholder="Pilih Tanggal">
                            <input type="hidden" name="tanggal_mulai" x-model="simpan">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Estimasi Selesai <span
                                class="text-red-500">*</span></label>
                        <div class="relative" x-data="{ simpan: '' }">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none z-10">
                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                                </svg>
                            </div>
                            <input type="text" required x-init="flatpickr($el, {
                                dateFormat: 'd-m-Y',
                                static: false,
                                position: 'above',
                                disableMobile: true,
                                onChange: (selectedDates, dateStr, instance) => {
                                    simpan = instance.formatDate(selectedDates[0], 'Y-m-d');
                                }
                            })"
                                class="w-full pl-10 pr-3 py-2 text-gray-700 rounded-lg border border-gray-200 bg-gray-50 text-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all outline-none"
                                placeholder="Estimasi Selesai">
                            <input type="hidden" name="tanggal_selesai" x-model="simpan">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="closeModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100">Batal</button>
                    <button type="submit"
                        class="px-5 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 shadow-md">
                        Konfirmasi & Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
