<div x-show="isModalOpen" class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/50"
    x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

    <div @click.away="closeModal()" class="relative w-full max-w-md p-4"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="scale-95 opacity-0"
        x-transition:enter-end="scale-100 opacity-100">

        <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">

            <div class="flex items-center justify-between p-4 border-b dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white"
                    x-text="modalMode === 'create' ? 'Tambah Penamaan Upah' : 'Edit Penamaan Upah'">
                </h3>
                <button type="button" @click="closeModal()"
                    class="text-gray-400 hover:text-gray-900 hover:bg-gray-200 rounded-lg w-8 h-8 flex items-center justify-center dark:hover:bg-gray-600 dark:hover:text-white">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                    <span class="sr-only">Close</span>
                </button>
            </div>

            <form :action="modalAction" method="POST" class="p-4 space-y-4" x-data="{ submitting: false }" @submit="if(submitting) { $event.preventDefault(); return; }; submitting = true">
                @csrf
                <template x-if="modalMode === 'edit'">
                    @method('PUT')
                </template>

                <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-white mb-1">
                        Nama Upah Pekerjaan <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_upah" x-model="formData.nama_upah" required
                        placeholder="Contoh: Pekerjaan Pondasi, Pasang Keramik..."
                        class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white p-2.5 
                            focus:ring-blue-600 focus:border-blue-600 outline-none transition">
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="closeModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 transition">
                        Batal
                    </button>
                    <button type="submit" :disabled="submitting" :class="submitting ? 'opacity-50 cursor-not-allowed' : ''"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 shadow-sm transition">
                        <span x-text="submitting ? 'Memproses...' : 'Simpan Data'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
