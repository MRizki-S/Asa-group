@extends('layouts.app')

@section('pageActive', 'PengajuanPembangunan')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-data="pengajuanManager({{ $allPengajuan->map(
            fn($p) => [
                'id' => $p->id,
                'perumahaan' => $p->perumahaan->nama_perumahaan ?? 'N/A',
                'unit' => $p->pembangunanUnit->unit->nama_unit ?? 'N/A',
                'tahap' => $p->pembangunanUnit->tahap->nama_tahap ?? 'N/A',
                'pengaju' => $p->diajukanOleh->nama_lengkap ?? '-',
                'penerima' => $p->diresponOleh->nama_lengkap ?? '-',
                'status' => $p->status_pengajuan,
                'qcContainerName' => $p->pembangunanUnit->qcContainer->nama_container ?? '-',
                'pengawas' => $p->pembangunanUnit->pengawas->nama_lengkap ?? '-',
                'tanggal' => \Carbon\Carbon::parse($p->tanggal_diajukan)->format('d M Y H:i:s'),
                'tanggal_respon' => $p->tanggal_direspon
                    ? \Carbon\Carbon::parse($p->tanggal_direspon)->format('d M Y H:i:s')
                    : '-',
                'tanggal_mulai' => $p->pembangunanUnit->tanggal_mulai
                    ? \Carbon\Carbon::parse($p->pembangunanUnit->tanggal_mulai)->format('d M Y')
                    : '',
                'tanggal_selesai' => $p->pembangunanUnit->tanggal_selesai
                    ? \Carbon\Carbon::parse($p->pembangunanUnit->tanggal_selesai)->format('d M Y')
                    : '',
            ],
        )->toJson() }})">

        <div x-data="{ pageName: 'Pengajuan Pembangunan Unit' }">
            @include('partials.breadcrumb')
        </div>

        <div class="mb-6 flex flex-col xl:flex-row xl:items-center justify-between gap-4">
            <div class="flex flex-col md:flex-row gap-4 flex-1">
                <div class="relative w-full md:w-80">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text" x-model="searchQuery" @input="currentPage = 1"
                        placeholder="Cari unit atau nama qc..."
                        class="w-full text-gray-700 rounded-lg border border-gray-200 bg-white py-2.5 pl-10 pr-4 text-sm focus:border-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition-all" />
                </div>

                <div class="flex p-1 bg-gray-100 dark:bg-gray-700 rounded-lg w-fit">
                    <button @click="filterStatus = 'all'; currentPage = 1"
                        :class="filterStatus === 'all' ? 'bg-white shadow text-blue-600' :
                            'text-gray-500 hover:text-gray-700'"
                        class="px-4 py-1.5 text-xs font-bold rounded-md transition-all uppercase">Semua</button>
                    <button @click="filterStatus = 'pending'; currentPage = 1"
                        :class="filterStatus === 'pending' ? 'bg-white shadow text-yellow-600' :
                            'text-gray-500 hover:text-gray-700'"
                        class="px-4 py-1.5 text-xs font-bold rounded-md transition-all uppercase">Pending</button>
                    <button @click="filterStatus = 'dibangun'; currentPage = 1"
                        :class="filterStatus === 'dibangun' ? 'bg-white shadow text-green-600' :
                            'text-gray-500 hover:text-gray-700'"
                        class="px-4 py-1.5 text-xs font-bold rounded-md transition-all uppercase">Dibangun</button>
                </div>
            </div>
{{--
            <a href="{{ route('produksi.pengajuanPembangunanUnit.create') }}"
                class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-lg transition-all">
                <svg class="w-5 h-5 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Buat Pengajuan
            </a> --}}
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 2xl:grid-cols-4 gap-4"
            x-show="pagedData.length > 0">
            <template x-for="item in pagedData" :key="item.id">
                <div
                    class="group rounded-lg border border-gray-200 bg-white p-5 shadow-sm hover:shadow-md transition-all dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-col h-full justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span
                                    :class="{
                                        'bg-yellow-50 text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400': item
                                            .status === 'pending',
                                        'bg-green-50 text-green-600 dark:bg-green-900/30 dark:text-green-400': item
                                            .status === 'dibangun'
                                    }"
                                    class="rounded-lg px-2 py-1 text-[10px] font-bold uppercase tracking-wider"
                                    x-text="item.status"></span>

                                <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a :href="'/produksi/pengajuan-pembangunan/' + item.id + '/edit'"
                                        class="p-2 text-gray-400 hover:text-blue-600 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </a>
                                    <button @click="confirmDelete(item.id)"
                                        class="p-2 text-gray-400 hover:text-red-600 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <h4 class="text-lg font-bold text-gray-800 dark:text-white leading-tight"
                                x-text="'Unit: ' + item.unit"></h4>
                            <p class="text-xs text-gray-500 mt-1 font-medium" x-text="item.perumahaan"></p>

                            <div class="mt-4 space-y-2 border-t border-gray-50 pt-3 dark:border-gray-700">
                                <div class="flex justify-between text-[11px] items-center">
                                    <span class="text-gray-400">Tahap:</span>
                                    <span
                                        class="px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-bold"
                                        x-text="item.tahap"></span>
                                </div>
                                <div class="flex justify-between text-[11px] items-center">
                                    <span class="text-gray-400">QC:</span>
                                    <span class="text-blue-600 dark:text-blue-400 font-bold"
                                        x-text="item.qcContainerName"></span>
                                </div>
                                <div class="flex justify-between text-[11px] items-center">
                                    <span class="text-gray-400">Pengawas:</span>
                                    <span class="text-gray-700 dark:text-gray-300 font-medium"
                                        x-text="item.pengawas"></span>
                                </div>
                                <div class="flex justify-between text-[11px] items-center">
                                    <span class="text-gray-400">Waktu:</span>
                                    <span class="text-gray-700 dark:text-gray-300 font-medium"
                                        x-text="item.tanggal_mulai + ' - ' + item.tanggal_selesai"></span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 pt-4 border-t border-gray-100 dark:border-gray-700 space-y-3">
                            <div class="flex items-center justify-between text-[10px] text-gray-400">
                                <div class="flex items-center">
                                    <svg class="w-3 h-3 me-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <span x-text="item.pengaju"></span>
                                </div>
                                <span x-text="item.tanggal"></span>
                            </div>

                            <template x-if="item.status === 'pending'">
                                <button @click="openModal(item)"
                                    class="w-full py-2.5 text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors border border-blue-100 dark:bg-blue-900/20 dark:border-blue-800 dark:text-blue-400 shadow-sm">
                                    Kofirmasi Pembangunan
                                </button>
                            </template>

                            <template x-if="item.status !== 'pending'">
                                <div
                                    class="p-2 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-100 dark:border-gray-700">
                                    <p class="text-[9px] text-gray-400 uppercase font-bold mb-1">Direspon Oleh:</p>
                                    <div class="flex items-center justify-between text-[10px]">
                                        <span class="font-bold text-gray-700 dark:text-gray-300 truncate pr-2"
                                            x-text="item.penerima"></span>
                                        <span class="text-gray-400 whitespace-nowrap" x-text="item.tanggal_respon"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div x-show="allData.length === 0"
            class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-200 py-20 dark:border-gray-700">
            <div class="rounded-full bg-gray-200 p-4 dark:bg-gray-700 text-gray-500">
                <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">Belum Ada Pengajuan</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 italic">Database masih kosong. Silahkan menunggu pengajuan
                baru oleh projek manager.</p>
            {{-- <a href="{{ route('produksi.pengajuanPembangunanUnit.create') }}"
                class="mt-4 px-6 py-2 bg-blue-600 text-white text-sm font-bold rounded-lg hover:bg-blue-700 shadow-lg">+
                Buat Sekarang</a> --}}
        </div>

        <div x-show="allData.length > 0 && filteredData.length === 0"
            class="flex flex-col items-center justify-center rounded-xl border border-gray-200 bg-gray-50 py-20 dark:border-gray-700 dark:bg-gray-800/50">
            <div class="rounded-full bg-gray-200 p-4 dark:bg-gray-700 text-gray-500">
                <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">Data Tidak Ditemukan</h3>
            <p class="mt-1 text-sm text-gray-500 italic">Tidak ada hasil yang cocok di status <span
                    class="font-bold uppercase" x-text="filterStatus"></span></p>
            <button @click="searchQuery = ''; filterStatus = 'all'"
                class="mt-4 text-sm font-medium text-blue-600 hover:underline">Reset Semua Filter</button>
        </div>

        <div class="mt-8 flex flex-col sm:flex-row items-center justify-between border-t border-gray-200 pt-6 dark:border-gray-700"
            x-show="filteredData.length > 0">
            <p class="text-sm text-gray-700 dark:text-gray-400">
                Menampilkan <span class="font-medium" x-text="((currentPage - 1) * perPage) + 1"></span> sampai <span
                    class="font-medium" x-text="Math.min(currentPage * perPage, filteredData.length)"></span> data
            </p>
            <div class="flex gap-2">
                <button @click="currentPage--" :disabled="currentPage === 1"
                    class="rounded-lg text-gray-700 border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50 disabled:opacity-50 dark:border-gray-600 dark:text-white dark:hover:bg-gray-700 transition">Sebelumnya</button>
                <button @click="currentPage++" :disabled="currentPage === totalPages"
                    class="rounded-lg text-gray-700 border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50 disabled:opacity-50 dark:border-gray-600 dark:text-white dark:hover:bg-gray-700 transition">Selanjutnya</button>
            </div>
        </div>

        <div x-show="isModalOpen"
            class="fixed inset-0 z-[999] flex items-center justify-center bg-black/50 backdrop-blur-sm" x-cloak
            x-transition>
            <div @click.away="closeModal()" class="relative w-full max-w-md p-4">
                <div class="bg-white rounded-xl shadow-2xl dark:bg-gray-800">
                    <div class="flex items-center justify-between p-4 border-b dark:border-gray-700">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Assign Pengawas Proyek</h3>
                        <button @click="closeModal()" class="text-gray-400 hover:text-gray-900"><svg class="w-5 h-5"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg></button>
                    </div>
                    <form :action="'{{ route('produksi.pembangunanUnit.store') }}'" method="POST" class="p-4 space-y-4">
                        @csrf
                        <input type="hidden" name="pengajuan_id" :value="selectedItem?.id">
                        <div
                            class="p-3 bg-blue-50 dark:bg-blue-900/30 rounded-lg border border-blue-100 dark:border-blue-800">
                            <p class="text-sm font-bold text-blue-800 dark:text-blue-300"
                                x-text="'Unit: ' + selectedItem?.unit"></p>
                            <p class="text-xs text-blue-600 dark:text-blue-400" x-text="selectedItem?.perumahaan"></p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Pengawas Proyek</label>
                                <select name="pengawas_id" required
                                    class="w-full text-gray-700 rounded-lg border-gray-200 bg-gray-50 text-sm focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all">
                                    <option value="">-- Pilih Pengawas --</option>
                                    @foreach ($allPengawas as $pm)
                                        <option value="{{ $pm->id }}">{{ $pm->nama_lengkap }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Template QC (Quality
                                    Control)</label>
                                <select name="qc_container_id" required
                                    class="w-full text-gray-700 rounded-lg border-gray-200 bg-gray-50 text-sm focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all">
                                    <option value="">-- Pilih Template QC --</option>
                                    @foreach ($allQcContainer as $qc)
                                        <option value="{{ $qc->id }}">{{ $qc->nama_container }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tanggal Mulai</label>
                                <input type="date" name="tanggal_mulai" required value="{{ date('Y-m-d') }}"
                                    class="w-full text-gray-700 rounded-lg border-gray-200 bg-gray-50 text-sm focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Estimasi
                                    Selesai</label>
                                <input type="date" name="tanggal_selesai" required
                                    class="w-full text-gray-700 rounded-lg border-gray-200 bg-gray-50 text-sm focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all">
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 mt-6">
                            <button type="button" @click="closeModal()"
                                class="px-4 py-2 text-sm text-gray-500 font-medium">Batal</button>
                            <button type="submit"
                                class="px-5 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 shadow-md transition">Konfirmasi
                                & Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <template x-for="item in allData" :key="'form-' + item.id">
            <form :class="'delete-form-' + item.id" :action="'/produksi/pengajuan-pembangunan/' + item.id" method="POST"
                style="display:none;">
                @csrf @method('DELETE')
            </form>
        </template>
    </div>

    <script>
        function pengajuanManager(initialData) {
            return {
                allData: initialData,
                searchQuery: '',
                filterStatus: 'pending',
                currentPage: 1,
                perPage: 12,
                isModalOpen: false,
                selectedItem: null,

                get filteredData() {
                    return this.allData.filter(item => {
                        const matchesSearch = item.unit.toLowerCase().includes(this.searchQuery
                        .toLowerCase()) ||
                            item.qcContainerName.toLowerCase().includes(this.searchQuery.toLowerCase());
                        const matchesStatus = this.filterStatus === 'all' || item.status === this.filterStatus;
                        return matchesSearch && matchesStatus;
                    });
                },
                get pagedData() {
                    let start = (this.currentPage - 1) * this.perPage;
                    return this.filteredData.slice(start, start + this.perPage);
                },
                get totalPages() {
                    return Math.ceil(this.filteredData.length / this.perPage) || 1;
                },
                openModal(item) {
                    this.selectedItem = item;
                    this.isModalOpen = true;
                },
                closeModal() {
                    this.isModalOpen = false;
                    this.selectedItem = null;
                },
                confirmDelete(id) {
                    Swal.fire({
                        title: 'Hapus Pengajuan?',
                        text: "Data tidak bisa dikembalikan!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Hapus',
                        confirmButtonColor: '#d33',
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
