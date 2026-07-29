@extends('layouts.app')

@section('pageActive', 'pembangunanProyek')

@section('content')
<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-data="proyekManager({{ $allPembangunanProyek->map(
        fn($p) => [
            'id' => $p->id,
            'nama' => $p->nama,
            'pengawas' => $p->pengawas->nama_lengkap ?? '-',
            'status' => $p->status_pembangunan,
            'tanggal_mulai' => $p->tanggal_mulai ? \Carbon\Carbon::parse($p->tanggal_mulai)->format('d M Y') : '-',
            'tanggal_selesai' => $p->tanggal_selesai ? \Carbon\Carbon::parse($p->tanggal_selesai)->format('d M Y') : '-',
        ]
    )->toJson() }})">

    @include('partials.breadcrumb', ['breadcrumbs' => [['label' => 'Pembangunan Proyek Kontraktor', 'url' => route('produksi.pembangunanProyek.index')]]])

    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: '{{ session('success') }}',
                    showConfirmButton: false,
                    timer: 2000
                });
            });
        </script>
    @endif
    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: '{{ session('error') }}',
                    showConfirmButton: true
                });
            });
        </script>
    @endif

    <div class="mb-6 mt-6">
        <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
            <div class="flex flex-col md:flex-row items-stretch md:items-center gap-4 flex-1">
                <div class="relative w-full md:w-80">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text" x-model="searchQuery" @input="currentPage = 1" placeholder="Cari nama proyek atau pengawas..." class="w-full text-gray-700 rounded-lg border border-gray-200 bg-white py-2.5 pl-10 pr-4 text-sm focus:border-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition-all" />
                </div>

                <!-- Month & Year Filter Form -->
                <form action="" method="GET" class="flex items-center gap-2">
                    <select name="month" onchange="this.form.submit()" class="text-gray-700 rounded-lg border border-gray-200 bg-white py-2.5 pl-3 pr-8 text-sm focus:border-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition-all">
                        @foreach($months as $key => $val)
                            <option value="{{ $key }}" {{ $selectedMonth == $key ? 'selected' : '' }}>{{ $val }}</option>
                        @endforeach
                    </select>

                    <select name="year" onchange="this.form.submit()" class="text-gray-700 rounded-lg border border-gray-200 bg-white py-2.5 pl-3 pr-8 w-36 text-sm focus:border-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition-all">
                        @foreach($years as $key => $val)
                            <option value="{{ $key }}" {{ $selectedYear == $key ? 'selected' : '' }}>{{ $val }}</option>
                        @endforeach
                    </select>
                </form>

                <div class="flex p-1 bg-gray-100 dark:bg-gray-700 rounded-lg w-fit overflow-x-auto">
                    <button @click="filterStatus = 'all'; currentPage = 1" :class="filterStatus === 'all' ? 'bg-white shadow text-blue-600' : 'text-gray-500 hover:text-gray-700'" class="px-4 py-1.5 text-xs font-bold rounded-md transition-all uppercase whitespace-nowrap">Semua</button>
                    <button @click="filterStatus = 'proses'; currentPage = 1" :class="filterStatus === 'proses' ? 'bg-white shadow text-blue-600' : 'text-gray-500 hover:text-gray-700'" class="px-4 py-1.5 text-xs font-bold rounded-md transition-all uppercase whitespace-nowrap">Proses</button>
                    <button @click="filterStatus = 'selesai'; currentPage = 1" :class="filterStatus === 'selesai' ? 'bg-white shadow text-green-600' : 'text-gray-500 hover:text-gray-700'" class="px-4 py-1.5 text-xs font-bold rounded-md transition-all uppercase whitespace-nowrap">Selesai</button>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4" x-show="pagedData.length > 0">
        <template x-for="item in pagedData" :key="item.id">
            <div class="group rounded-lg border border-gray-200 bg-white p-5 shadow-sm hover:shadow-md transition-all dark:border-gray-700 dark:bg-gray-800">
                <div class="flex flex-col h-full justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span :class="{
                                    'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400': item.status === 'proses',
                                    'bg-green-50 text-green-600 dark:bg-green-900/30 dark:text-green-400': item.status === 'selesai'
                                }"
                                class="rounded-lg px-2 py-1 text-[10px] font-bold uppercase tracking-wider"
                                x-text="item.status"></span>
                        </div>

                        <h4 class="text-lg font-bold text-gray-800 dark:text-white leading-tight" x-text="item.nama"></h4>

                        <div class="mt-4 space-y-2 border-t border-gray-50 pt-3 dark:border-gray-700">
                            <div class="flex justify-between text-[11px] items-center">
                                <span class="text-gray-400">Pengawas:</span>
                                <span class="text-gray-700 dark:text-gray-300 font-medium" x-text="item.pengawas"></span>
                            </div>
                            <div class="flex justify-between text-[11px] items-center">
                                <span class="text-gray-400">Waktu:</span>
                                <span class="text-gray-700 dark:text-gray-300 font-medium" x-text="item.tanggal_mulai + ' s.d ' + item.tanggal_selesai"></span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <a :href="'{{ route('produksi.pembangunanProyek.index') }}/' + item.id" class="block w-full text-center py-2 text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors border border-blue-100 dark:bg-blue-900/20 dark:border-blue-800 dark:text-blue-400 shadow-sm">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <div x-show="pagedData.length === 0" class="text-center py-10" x-cloak>
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 mb-4">
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Tidak ada data ditemukan</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Coba sesuaikan kata kunci pencarian atau filter.</p>
    </div>

    <!-- Pagination -->
    <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4" x-show="totalPages > 1">
        <div class="text-sm text-gray-500 dark:text-gray-400">
            Menampilkan <span class="font-medium text-gray-900 dark:text-white" x-text="startIndex + 1"></span> -
            <span class="font-medium text-gray-900 dark:text-white" x-text="Math.min(endIndex, filteredData.length)"></span>
            dari <span class="font-medium text-gray-900 dark:text-white" x-text="filteredData.length"></span> data
        </div>
        <div class="flex items-center gap-1">
            <button @click="prevPage" :disabled="currentPage === 1" class="p-2 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:border-gray-700 dark:hover:bg-gray-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            </button>
            <div class="flex items-center gap-1 px-2">
                <template x-for="page in getPageNumbers()" :key="page">
                    <button @click="typeof page === 'number' ? goToPage(page) : null"
                        :class="currentPage === page ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-200 text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800'"
                        :disabled="typeof page !== 'number'"
                        class="px-3 py-1.5 text-sm font-medium rounded-lg border transition-colors min-w-[32px]"
                        x-text="page"></button>
                </template>
            </div>
            <button @click="nextPage" :disabled="currentPage === totalPages" class="p-2 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:border-gray-700 dark:hover:bg-gray-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('proyekManager', (initialData) => ({
        allData: initialData,
        searchQuery: '',
        filterStatus: 'proses',
        currentPage: 1,
        itemsPerPage: 12,

        get filteredData() {
            return this.allData.filter(item => {
                const search = this.searchQuery.toLowerCase();
                const matchesSearch = item.nama.toLowerCase().includes(search) ||
                                      item.pengawas.toLowerCase().includes(search);
                const matchesStatus = this.filterStatus === 'all' || 
                                      (this.filterStatus === 'selesai' ? item.status.startsWith('selesai') : item.status === this.filterStatus);
                return matchesSearch && matchesStatus;
            });
        },

        get totalPages() {
            return Math.ceil(this.filteredData.length / this.itemsPerPage) || 1;
        },

        get startIndex() {
            return (this.currentPage - 1) * this.itemsPerPage;
        },

        get endIndex() {
            return this.currentPage * this.itemsPerPage;
        },

        get pagedData() {
            return this.filteredData.slice(this.startIndex, this.endIndex);
        },

        nextPage() {
            if (this.currentPage < this.totalPages) this.currentPage++;
        },

        prevPage() {
            if (this.currentPage > 1) this.currentPage--;
        },

        goToPage(page) {
            this.currentPage = page;
        },

        getPageNumbers() {
            const pages = [];
            const maxVisible = 5;

            if (this.totalPages <= maxVisible) {
                for (let i = 1; i <= this.totalPages; i++) pages.push(i);
            } else {
                pages.push(1);
                if (this.currentPage > 3) pages.push('...');

                let start = Math.max(2, this.currentPage - 1);
                let end = Math.min(this.totalPages - 1, this.currentPage + 1);

                for (let i = start; i <= end; i++) pages.push(i);

                if (this.currentPage < this.totalPages - 2) pages.push('...');
                pages.push(this.totalPages);
            }
            return pages;
        }
    }));
});
</script>
@endsection
