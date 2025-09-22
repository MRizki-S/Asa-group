@extends('layouts.app')

@section('pageActive', 'TipeUnit') {{-- ⬅️ ini yang jadi value Alpine.js page --}}

@section('content')
    <!-- ===== Main Content Start ===== -->
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-data="typeSearch()">

        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: 'Tipe Unit' }">
            @include('partials.breadcrumb')
        </div>
        <!-- Breadcrumb End -->




        {{-- Alert Error Validasi --}}
        @if ($errors->any())
            <div class="flex p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400"
                role="alert">
                <svg class="shrink-0 inline w-4 h-4 me-3 mt-[2px]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                    fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                </svg>
                <span class="sr-only">Danger</span>
                <div>
                    <span class="font-medium">Terjadi kesalahan validasi:</span>
                    <ul class="mt-1.5 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif


        <div class="rounded bg-white shadow-xl px-4 py-6 dark:bg-gray-800">
            <div class="flex flex-wrap gap-2 justify-between mb-4 items-center">
                {{-- Search --}}
                <div class="flex-1 min-w-[150px] max-w-sm">
                    <label for="table-search-users" class="sr-only">Search</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                            </svg>
                        </div>
                        <input type="text" x-model="search" @input.debounce.500ms="fetchResults" id="table-search-users"
                            class="block w-full p-2 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg
                       bg-gray-50 focus:ring-blue-500 focus:border-blue-500
                       dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white
                       dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            placeholder="Search for users">
                    </div>
                </div>


                {{-- Button Tambah --}}
                <button data-modal-target="modal-create" data-modal-toggle="modal-create"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex-shrink-0">
                    + Tambah Type
                </button>
            </div>


            {{-- Table wrapper agar scrollable --}}
            <div class="overflow-x-auto max-h-[600px]">
                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400 border-collapse">
                    <thead class="text-gray-700 bg-gray-100 dark:text-gray-200 dark:bg-gray-700 sticky top-0 z-10">
                        <tr>
                            {{-- <th scope="col" class="px-6 py-3">No.</th> --}}
                            <th scope="col" class="px-6 py-3">Tipe Unit</th>
                            <th scope="col" class="px-6 py-3 w-64 text-center">Perumahaan</th>
                            <th scope="col" class="px-6 py-3">Luas Bangunan</th>
                            <th scope="col" class="px-6 py-3">Luas Tanah</th>
                            <th scope="col" class="px-6 py-3">Harga - Saat ini (Rp)</th>
                            <th scope="col" class="px-6 py-3">Harga pengajuan - Perubahan (Rp)</th>
                            <th scope="col" class="px-6 py-3">Status Pengajuan</th>
                            <th scope="col" class="px-6 py-3 text-center w-56">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="table-body" x-html="tableHtml">
                        @include('Etalase.tipe-unit.partials.table', ['tipeUnits' => $tipeUnits])
                    </tbody>
                </table>
            </div>
            {{-- Pagination links --}}
            <div class="mt-4">
                {{ $tipeUnits->links() }}
            </div>



        </div>



        {{-- include modal --}}
        @include('Etalase.tipe-unit.modal.modal-cretae-tipeUnit')
        @include('Etalase.tipe-unit.modal.modal-edit-tipeUnit')

    </div>

    <script>
        document.addEventListener('click', function(e) {
            if (e.target.closest('.delete-btn')) {
                const btn = e.target.closest('.delete-btn');
                const form = btn.closest('.delete-form');

                Swal.fire({
                    title: 'Yakin hapus data ini?',
                    text: "Menghapus Type ini akan menghapus seluruh data yang berkaitan, " +
                        "seperti unit, harga, dan relasi lainnya, dan data yang hapus tidak bisa dikembalikan lagi!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            }
        });
    </script>

    {{-- script untuk search --}}
    <script>
        function typeSearch() {
            return {
                search: '',
                tableHtml: @json(view('Etalase.tipe-unit.partials.table', ['tipeUnits' => $tipeUnits])->render()),
                fetchResults() {
                    fetch(`{{ route('tipe-unit.index') }}?search=${this.search}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(res => res.text())
                        .then(html => {
                            this.tableHtml = html; // diisi ke <div x-html="tableHtml"></div> misalnya
                            // setelah DOM baru dimasukkan, komponen Flowbite (dropdown, tooltip, modal)
                            // perlu di-re-init supaya event listener-nya aktif lagi
                            this.$nextTick(() => {
                                window.initFlowbite();
                            });
                        });
                }
            }
        }
    </script>
@endsection
