<div x-show="isModalOpen" class="fixed inset-0 z-[999] flex items-center justify-center bg-black/50 backdrop-blur-sm"
    x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

    <div class="relative w-full max-w-md p-4" @click.away="closeModal()">
        <div class="bg-white rounded-xl shadow-2xl dark:bg-gray-800 overflow-hidden">
            <div class="flex items-center justify-between p-4 border-b dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white uppercase tracking-tight">
                    Review Persetujuan Upah
                </h3>
                <button type="button" @click="closeModal()"
                    class="text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-4 space-y-4">
                <div class="p-3 bg-blue-50 dark:bg-blue-900/30 rounded-lg border border-blue-100 dark:border-blue-800">
                    <p class="text-sm font-bold text-blue-800 dark:text-blue-300"
                        x-text="'Unit: ' + selectedItem?.unit_nama"></p>
                    <p class="text-xs text-blue-600 dark:text-blue-400 font-medium mt-1"
                        x-text="selectedItem?.upah_nama"></p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Pengawas Unit</label>
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-200"
                            x-text="selectedItem?.pengawas"></p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1 text-right">Nominal
                            Diajukan</label>
                        <p class="text-sm font-bold text-emerald-600 text-right" x-text="selectedItem?.nominal"></p>
                    </div>
                </div>

                <div class="pt-2">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Catatan Pengawas</label>
                    <div
                        class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-100 dark:border-gray-600 italic text-xs text-gray-600 dark:text-gray-400">
                        <span x-text="selectedItem?.catatan || 'Tidak ada catatan lampiran'"></span>
                    </div>
                </div>

                <div x-show="showRejectReason" x-transition class="pt-2">
                    <label class="block text-[10px] font-bold text-red-500 uppercase mb-1">Alasan Penolakan <span
                            class="text-red-500">*</span></label>
                    <textarea x-model="rejectReason" rows="3"
                        class="w-full rounded-lg border-2 border-red-500 bg-white p-3 text-sm text-gray-900 placeholder-red-400 focus:ring-2 focus:ring-red-500 focus:outline-none dark:bg-gray-900 dark:text-red-100 transition-all shadow-sm"
                        placeholder="Alasan penolakan wajib diisi..."></textarea>
                </div>

                <div class="flex flex-col gap-2 mt-6">
                    <template x-if="!showRejectReason">
                        <div class="flex gap-3">
                            <button type="button" @click="showRejectReason = true"
                                class="flex-1 px-4 py-2.5 text-xs font-bold text-red-600 border border-red-200 rounded-lg hover:bg-red-50 transition-all">
                                TOLAK
                            </button>
                            <button type="button" @click="confirmAction('approve')"
                                class="flex-[2] px-4 py-2.5 bg-emerald-600 text-white rounded-lg text-xs font-bold hover:bg-emerald-700 shadow-md transition-all active:scale-95">
                                SETUJUI & TERUSKAN
                            </button>
                        </div>
                    </template>

                    <template x-if="showRejectReason">
                        <div class="flex gap-3">
                            <button type="button" @click="showRejectReason = false"
                                class="flex-1 px-4 py-2.5 text-xs font-bold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-all">
                                BATAL
                            </button>
                            <button type="button" @click="confirmAction('reject')"
                                class="flex-[2] px-4 py-2.5 bg-red-600 text-white rounded-lg text-xs font-bold hover:bg-red-700 shadow-md transition-all active:scale-95">
                                KONFIRMASI TOLAK
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
