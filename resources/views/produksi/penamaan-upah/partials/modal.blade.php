<div x-show="isModalOpen"
     class="fixed inset-0 z-[999] flex items-center justify-center bg-black/50 backdrop-blur-sm"
     x-cloak x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 scale-90"
     x-transition:enter-end="opacity-100 scale-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 scale-100"
     x-transition:leave-end="opacity-0 scale-90">

    <div @click.away="closeModal()"
         class="w-full max-w-lg rounded-2xl bg-white p-8 shadow-2xl dark:bg-gray-800 border border-gray-100 dark:border-gray-700">

        <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-6"
            x-text="modalMode === 'create' ? 'Tambah Penamaan Upah' : 'Edit Penamaan Upah'">
        </h3>

        <form :action="modalAction" method="POST">
            @csrf
            <template x-if="modalMode === 'edit'">
                @method('PUT')
            </template>

            <div class="space-y-4">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-300">
                        Nama Upah / Kategori Pekerjaan
                    </label>
                    <input type="text" name="nama_upah" x-model="formData.nama" required
                        placeholder="Contoh: Upah Pasang Keramik"
                        class="w-full rounded-xl border border-gray-200 bg-white py-3 px-5 outline-none transition focus:border-blue-600 focus:ring-1 focus:ring-blue-600 dark:border-gray-600 dark:bg-gray-700 text-gray-800 dark:text-white" />
                </div>
            </div>

            <div class="mt-8 flex gap-3">
                <button type="button" @click="closeModal()"
                    class="flex-1 rounded-xl border border-gray-200 py-3 text-center font-bold text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700 transition">
                    Batal
                </button>
                <button type="submit"
                    class="flex-1 rounded-xl bg-blue-600 py-3 font-bold text-white hover:bg-blue-700 shadow-md active:scale-95 transition-all">
                    Simpan Data
                </button>
            </div>
        </form>
    </div>
</div>
