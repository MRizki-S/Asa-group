@extends('layouts.app')

@section('pageActive', 'PenamaanUpah')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-data="upahManager({{ $allMasterUpah->map(
            fn($u) => [
                'id' => $u->id,
                'nama_upah' => $u->nama_upah,
                'rap_count' => $u->rapUpah->count(),
            ],
        )->toJson() }})">

        <div x-data="{ pageName: 'Penamaan Upah' }">
            @include('partials.breadcrumb')
        </div>

        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="relative w-full md:w-96">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" x-model="searchQuery" @input="currentPage = 1" placeholder="Cari penamaan upah..."
                    class="w-full text-gray-700 rounded-lg border border-gray-200 bg-white py-2.5 pl-10 pr-4 text-sm focus:border-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition-all" />
            </div>

            <button @click="openModal('create')"
                class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-lg">
                <svg class="w-5 h-5 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Upah
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4" x-show="pagedData.length > 0">
            <template x-for="upah in pagedData" :key="upah.id">
                <div
                    class="group rounded-lg border border-gray-200 bg-white p-5 shadow-sm hover:shadow-md transition-all dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-col h-full justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span
                                    class="rounded-lg bg-blue-50 px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-blue-600 dark:bg-blue-900/30 dark:text-blue-400"
                                    x-text="'ID: ' + upah.id"></span>
                                <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click="openModal('edit', upah)"
                                        class="p-2 text-gray-400 hover:text-blue-600 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                    <button @click="confirmDelete(upah.id)"
                                        class="p-2 text-gray-400 hover:text-red-600 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <h4 class="text-lg font-bold text-gray-800 dark:text-white leading-snug"
                                x-text="upah.nama_upah"></h4>
                        </div>
                        <div
                            class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 flex items-center text-xs text-gray-500 dark:text-gray-400">
                            <svg class="w-4 h-4 me-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <span x-text="'Dipakai di ' + upah.rap_count + ' RAP'"></span>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div x-show="allData.length === 0"
            class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-200 py-16 dark:border-gray-700">
            <div class="rounded-full bg-blue-50 p-4 dark:bg-blue-900/20">
                <svg class="h-12 w-12 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">Belum Ada Data Upah</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Database masih kosong. Silahkan tambahkan data baru.
            </p>
            <button @click="openModal('create')"
                class="mt-4 text-sm font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400">
                + Tambah Data Sekarang
            </button>
        </div>

        <div x-show="allData.length > 0 && filteredData.length === 0"
            class="flex flex-col items-center justify-center rounded-xl border border-gray-200 bg-gray-50 py-16 dark:border-gray-700 dark:bg-gray-800/50">
            <div class="rounded-full bg-gray-200 p-4 dark:bg-gray-700">
                <svg class="h-10 w-10 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">Data Tidak Ditemukan</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tidak ada hasil yang cocok untuk "<span
                    class="font-medium text-gray-900 dark:text-white" x-text="searchQuery"></span>"</p>
            <button @click="searchQuery = ''"
                class="mt-4 text-sm font-medium text-blue-600 hover:underline dark:text-blue-400">
                Bersihkan Pencarian
            </button>
        </div>

        <div class="mt-8 flex flex-col sm:flex-row items-center justify-between border-t border-gray-200 pt-6 dark:border-gray-700"
            x-show="filteredData.length > 0">
            <p class="text-sm text-gray-700 dark:text-gray-400 mb-4 sm:mb-0">
                Menampilkan <span class="font-medium" x-text="((currentPage - 1) * perPage) + 1"></span>
                sampai <span class="font-medium" x-text="Math.min(currentPage * perPage, filteredData.length)"></span>
                dari <span class="font-medium" x-text="filteredData.length"></span> data
            </p>
            <div class="flex gap-2">
                <button @click="currentPage--" :disabled="currentPage === 1"
                    class="rounded-lg text-gray-700 border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50 disabled:opacity-50 dark:border-gray-600 dark:text-white dark:hover:bg-gray-700 transition">
                    Sebelumnya
                </button>
                <button @click="currentPage++" :disabled="currentPage === totalPages"
                    class="rounded-lg text-gray-700 border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50 disabled:opacity-50 dark:border-gray-600 dark:text-white dark:hover:bg-gray-700 transition">
                    Selanjutnya
                </button>
            </div>
        </div>

        @include('produksi.penamaan-upah.partials.modal')

        <template x-for="upah in allData" :key="'form-' + upah.id">
            <form :class="'delete-form-' + upah.id" :action="'/produksi/penamaan-upah/' + upah.id" method="POST"
                style="display:none;">
                @csrf @method('DELETE')
            </form>
        </template>
    </div>

    <script>
        function upahManager(initialData) {
            return {
                allData: initialData,
                searchQuery: '',
                currentPage: 1,
                perPage: 12,
                isModalOpen: false,
                modalMode: 'create',
                modalAction: '',
                formData: {
                    id: '',
                    nama: ''
                },

                get filteredData() {
                    if (!this.searchQuery) return this.allData;
                    return this.allData.filter(item =>
                        item.nama_upah.toLowerCase().includes(this.searchQuery.toLowerCase())
                    );
                },

                get pagedData() {
                    let start = (this.currentPage - 1) * this.perPage;
                    return this.filteredData.slice(start, start + this.perPage);
                },

                get totalPages() {
                    return Math.ceil(this.filteredData.length / this.perPage) || 1;
                },

                openModal(mode, data = null) {
                    this.modalMode = mode;
                    if (mode === 'edit') {
                        this.formData = {
                            id: data.id,
                            nama: data.nama_upah
                        };
                        this.modalAction = `/produksi/penamaan-upah/${data.id}`;
                    } else {
                        this.formData = {
                            id: '',
                            nama: ''
                        };
                        this.modalAction = "{{ route('produksi.masterUpah.store') }}";
                    }
                    this.isModalOpen = true;
                },

                closeModal() {
                    this.isModalOpen = false;
                },

                confirmDelete(id) {
                    Swal.fire({
                        title: 'Hapus Data?',
                        text: "Tindakan ini tidak bisa dibatalkan!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Hapus',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.querySelector(`.delete-form-${id}`).submit();
                        }
                    });
                }
            }
        }
    </script>
@endsection
