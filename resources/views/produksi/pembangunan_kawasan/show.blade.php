@extends('layouts.app')

@section('pageActive', 'pembangunanKawasan')

@section('content')
<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-data="{
    tab: new URLSearchParams(window.location.search).get('tab') || 'order',
    init() {
        this.$watch('tab', value => {
            const url = new URL(window.location.href);
            url.searchParams.set('tab', value);
            window.history.replaceState(null, '', url.toString());
        });
    },
    kawasanStatus: '{{ $data->status_pembangunan ?? 'proses' }}',
    confirmChangeKawasanStatus(newStatus) {
        if (this.kawasanStatus === newStatus) return;
        Swal.fire({
            title: 'Konfirmasi Ubah Status',
            text: `Apakah Anda yakin ingin mengubah status pembangunan kawasan menjadi '${newStatus}'?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2563EB',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Ya, Ubah Status',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const formElement = document.getElementById('status-kawasan-form-' + newStatus);
                if (formElement) {
                    formElement.submit();
                }
            }
        });
    },
    itemsAdditional: [],
    filterType: 'stock',
    openCancelOrderModal: false,
    cancelOrderActionUrl: '',
    openCancelUpahModal: false,
    cancelUpahActionUrl: '',
    
    allBarang: {{ json_encode($allBarang) }},
    returnableBarang: {{ json_encode($returnableBarang) }},
    returnKawasanItems: [],
    addReturnKawasanItem() {
        this.returnKawasanItems.push({
            barang_id: '',
            nama_barang: '',
            jumlah_input: 0,
            satuan_id: '',
            satuan_options: [],
            total_diterima_base: 0,
            sudah_retur_base: 0,
            sisa_retur_base: 0,
            diterima_disp: 0,
            sudah_retur_disp: 0,
            sisa_retur_disp: 0,
            satuan_nama: '',
            keterangan: ''
        });
    },
    removeReturnKawasanItem(index) {
        this.returnKawasanItems.splice(index, 1);
    },
    updateReturnKawasanBarang(index, e) {
        let val = e && e.target ? e.target.value : e;
        this.returnKawasanItems[index].barang_id = val;
        let selected = this.returnableBarang.find(b => b.barang_id == val);
        if (selected) {
            this.returnKawasanItems[index].nama_barang = selected.nama_barang;
            this.returnKawasanItems[index].satuan_options = selected.satuan_options;
            this.returnKawasanItems[index].total_diterima_base = selected.total_diterima_base;
            this.returnKawasanItems[index].sudah_retur_base = selected.sudah_retur_base;
            this.returnKawasanItems[index].sisa_retur_base = selected.sisa_retur_base;

            if (selected.satuan_options && selected.satuan_options.length > 0) {
                this.returnKawasanItems[index].satuan_id = selected.satuan_options[0].satuan_id;
            } else {
                this.returnKawasanItems[index].satuan_id = '';
            }
            this.recalculateReturnKawasanItem(index);
        }
    },
    updateReturnKawasanSatuan(index) {
        this.recalculateReturnKawasanItem(index);
    },
    recalculateReturnKawasanItem(index) {
        let item = this.returnKawasanItems[index];
        let opt = item.satuan_options ? item.satuan_options.find(s => s.satuan_id == item.satuan_id) : null;
        let faktor = opt ? parseFloat(opt.konversi_ke_base) : 1.0;
        if (faktor <= 0) faktor = 1.0;

        item.satuan_nama = opt ? opt.nama_satuan : '';
        item.diterima_disp = Math.round((item.total_diterima_base / faktor) * 1000) / 1000;
        item.sudah_retur_disp = Math.round((item.sudah_retur_base / faktor) * 1000) / 1000;
        item.sisa_retur_disp = Math.max(0, Math.round((item.sisa_retur_base / faktor) * 1000) / 1000);

        if (item.jumlah_input > item.sisa_retur_disp || item.jumlah_input <= 0) {
            item.jumlah_input = item.sisa_retur_disp;
        }
    },
    isBarangSelectedInReturn(currentIndex, barangId) {
        return this.returnKawasanItems.some((item, idx) => idx !== currentIndex && item.barang_id == barangId);
    },

    addAdditionalItem() {
        this.itemsAdditional.push({
            barang_id: '',
            nama_barang: '',
            jumlah_input: 1,
            satuan_id: '',
            satuans: []
        });
    },
    removeAdditionalItem(index) {
        this.itemsAdditional.splice(index, 1);
    },
    updateBarangDetail(index, e) {
        let val = e.target.value;
        this.itemsAdditional[index].barang_id = val;
        let selected = this.allBarang.find(b => b.id == val);
        if (selected) {
            this.itemsAdditional[index].nama_barang = selected.nama_barang;
            this.itemsAdditional[index].satuans = selected.satuans;
            if (selected.satuans.length > 0) {
                this.itemsAdditional[index].satuan_id = selected.satuans[0].id;
            }
        }
    },
    isBarangSelected(currentIndex, barangId) {
        return this.itemsAdditional.some((item, idx) => idx !== currentIndex && item.barang_id == barangId);
    }
}">
    @include('partials.breadcrumb', ['breadcrumbs' => [
        ['label' => 'Pembangunan Kawasan', 'url' => route('produksi.pembangunanKawasan.index')],
        ['label' => $data->nama, 'url' => '']
    ]])

    {{-- Flash notification --}}
    @if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: @json(session('success')),
                timer: 2500,
                timerProgressBar: true,
                showConfirmButton: false
            });
        });
    </script>
    @endif
    @if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: @json(session('error')),
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false
            });
        });
    </script>
    @endif

    <!-- Hidden Status Update Forms -->
    <form id="status-kawasan-form-proses" action="{{ route('produksi.pembangunanKawasan.update', $data->id) }}" method="POST" class="hidden">
        @csrf
        @method('PUT')
        <input type="hidden" name="status_pembangunan" value="proses">
    </form>
    <form id="status-kawasan-form-selesai" action="{{ route('produksi.pembangunanKawasan.update', $data->id) }}" method="POST" class="hidden">
        @csrf
        @method('PUT')
        <input type="hidden" name="status_pembangunan" value="selesai">
    </form>



    <!-- Header Info -->
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow-sm mb-6 p-5">
        <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-5">

            {{-- Kiri: Identitas --}}
            <div class="flex-1 space-y-3">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white leading-tight">{{ $data->nama }}</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex flex-wrap items-center gap-x-3 gap-y-1">
                        <span><i class="fa-solid fa-location-dot me-1"></i>{{ $data->perumahan->nama_perumahaan ?? '-' }}</span>
                        <span class="text-gray-300 dark:text-gray-600">|</span>
                        <span><i class="fa-solid fa-user-gear me-1"></i><span class="font-semibold text-gray-600 dark:text-gray-300">Pengawas:</span> {{ $data->pengawas->nama_lengkap ?? '-' }}</span>
                    </p>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    {{-- Status Dropdown --}}
                    <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700/60 relative" x-data="{ openStatus: false }">
                        <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Status Pembangunan</p>
                        <div class="relative">
                            <button @click="openStatus = !openStatus"
                                class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase transition-all border border-transparent hover:border-gray-300 dark:hover:border-gray-600 shadow-sm"
                                :class="{
                                    'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400': kawasanStatus === 'proses',
                                    'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400': kawasanStatus === 'selesai',
                                    'bg-yellow-100 text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400': kawasanStatus === 'selesai dengan catatan'
                                }">
                                <span x-text="kawasanStatus"></span>
                                <i class="fa-solid fa-chevron-down text-[8px] transition-transform" :class="openStatus ? 'rotate-180' : ''"></i>
                            </button>
                            <div x-show="openStatus" @click.away="openStatus = false" x-transition x-cloak
                                class="absolute left-0 mt-2 w-52 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl z-50 overflow-hidden text-left">
                                <div class="p-1 space-y-1">
                                    <template x-for="opt in ['proses', 'selesai']">
                                        <button @click="confirmChangeKawasanStatus(opt); openStatus = false"
                                            class="w-full text-left px-3 py-2 text-[10px] font-bold uppercase rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                                            :class="kawasanStatus === opt ? 'bg-gray-50 text-blue-600 dark:bg-gray-750 dark:text-blue-400' : 'text-gray-600 dark:text-gray-300'"
                                            x-text="opt">
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Tanggal Mulai --}}
                    <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700/60">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Tgl Mulai</p>
                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-200">{{ $data->tanggal_mulai ? \Carbon\Carbon::parse($data->tanggal_mulai)->format('d M Y') : '-' }}</p>
                    </div>
                    {{-- Tanggal Selesai --}}
                    <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700/60">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Tgl Selesai</p>
                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-200">{{ $data->tanggal_selesai ? \Carbon\Carbon::parse($data->tanggal_selesai)->format('d M Y') : '-' }}</p>
                    </div>
                </div>
            </div>

            {{-- Kanan: Aksi --}}
            <div class="flex flex-row lg:flex-col items-center lg:items-end gap-2 shrink-0">
                <a href="{{ route('produksi.pembangunanKawasan.laporanTermin.export', $data->id) }}"
                    class="inline-flex items-center gap-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 px-4 py-2.5 rounded-xl text-xs font-bold shadow-sm transition-all">
                    <i class="fa-solid fa-file-excel text-green-600"></i> Laporan Termin
                </a>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="mb-6">
        <div class="flex border-b border-gray-200 dark:border-gray-700 w-full">
            <button @click="tab = 'order'"
                :class="tab === 'order' ? 'border-blue-600 text-blue-600 dark:text-blue-400 bg-white dark:bg-transparent' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                class="flex-1 inline-flex flex-col sm:flex-row items-center justify-center gap-1 sm:gap-2 px-2 sm:px-5 py-2.5 sm:py-3 text-[11px] sm:text-xs font-bold border-b-2 uppercase tracking-wider transition-all text-center">
                <i class="fa-solid fa-box text-xs sm:text-sm"></i>
                <span class="leading-tight">Order<br class="sm:hidden"> Barang</span>
            </button>
            @if(false)
            <button @click="tab = 'upah'"
                :class="tab === 'upah' ? 'border-blue-600 text-blue-600 dark:text-blue-400 bg-white dark:bg-transparent' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                class="flex-1 inline-flex flex-col sm:flex-row items-center justify-center gap-1 sm:gap-2 px-2 sm:px-5 py-2.5 sm:py-3 text-[11px] sm:text-xs font-bold border-b-2 uppercase tracking-wider transition-all text-center">
                <i class="fa-solid fa-money-bill-wave text-xs sm:text-sm"></i>
                <span class="leading-tight">Pengajuan<br class="sm:hidden"> Upah</span>
            </button>
            @endif
            <button @click="tab = 'retur'"
                :class="tab === 'retur' ? 'border-blue-600 text-blue-600 dark:text-blue-400 bg-white dark:bg-transparent' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                class="flex-1 inline-flex flex-col sm:flex-row items-center justify-center gap-1 sm:gap-2 px-2 sm:px-5 py-2.5 sm:py-3 text-[11px] sm:text-xs font-bold border-b-2 uppercase tracking-wider transition-all text-center">
                <i class="fa-solid fa-rotate-left text-xs sm:text-sm"></i>
                <span class="leading-tight">Retur<br class="sm:hidden"> Barang</span>
            </button>
        </div>
    </div>

    <!-- Tab Content: Order Barang -->
    <div x-show="tab === 'order'" style="display: none;">
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            
            @if ($data->status_pembangunan !== 'selesai')
            <!-- Kiri: Form Order Baru -->
            <div class="xl:col-span-1 rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h3 class="font-bold text-gray-900 dark:text-white mb-4">Buat Order Barang</h3>
                <form action="{{ route('produksi.pembangunanKawasan.orderStore') }}" method="POST" x-data="{ submittingOrder: false }" @submit="if(submittingOrder) { $event.preventDefault(); return; }; submittingOrder = true">
                    @csrf
                    <input type="hidden" name="pembangunan_kawasan_id" value="{{ $data->id }}">
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Catatan Tambahan</label>
                                <textarea name="catatan" rows="4" class="bg-white block w-full rounded-lg border border-gray-300 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Jenis Order <span class="text-red-500">*</span></label>
                                <select name="jenis_order" x-model="filterType" required class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    <option value="stock">Stock (Gudang)</option>
                                    <option value="direct">Direct (Langsung)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Dynamic Items -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
                            <div class="flex justify-between items-center mb-2">
                                <h4 class="font-semibold text-sm text-gray-900 dark:text-white">Daftar Barang</h4>
                                <button type="button" @click="addAdditionalItem()" class="text-sm text-blue-600 hover:underline"><i class="fa-solid fa-plus"></i> Tambah</button>
                            </div>

                            <div class="max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                                <template x-for="(item, index) in itemsAdditional" :key="index">
                                    <div class="bg-gray-50 dark:bg-gray-900 p-3 rounded-lg border border-gray-200 dark:border-gray-700 mb-3 space-y-3 relative">
                                        <button type="button" @click="removeAdditionalItem(index)" class="absolute top-2 right-2 flex items-center justify-center w-6 h-6 rounded-full bg-red-100 text-red-500 hover:bg-red-200 transition-colors font-bold text-xs" title="Hapus Barang">X</button>
                                        
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Barang</label>
                                            <select :name="'barang['+index+'][id]'" 
                                                class="select2-dynamic block w-full rounded border-gray-300 bg-white text-xs text-gray-900 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-blue-500 focus:border-blue-500" 
                                                required
                                                x-init="$nextTick(() => { 
                                                    $($el).select2({theme: 'bootstrap4', width: '100%'})
                                                    .on('change', (e) => { updateBarangDetail(index, e); }); 
                                                })">
                                                <option value="">-- Pilih Barang --</option>
                                                <template x-for="b in allBarang.filter(x => (filterType === 'stock' ? x.is_stock : !x.is_stock))" :key="b.id">
                                                    <option :value="b.id" x-text="b.nama_barang" :disabled="isBarangSelected(index, b.id)"></option>
                                                </template>
                                            </select>
                                        </div>
                                        <div class="flex gap-2">
                                            <div class="flex-1">
                                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Jumlah</label>
                                                <input type="number" step="0.01" :name="'barang['+index+'][jumlah_input]'" x-model="item.jumlah_input" required class="block w-full rounded border-gray-300 bg-white text-xs text-gray-900 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                            </div>
                                            <div class="flex-1">
                                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Satuan</label>
                                                <select :name="'barang['+index+'][satuan_id]'" x-model="item.satuan_id" required class="block w-full rounded border-gray-300 bg-white text-xs text-gray-900 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                                    <template x-for="s in item.satuans" :key="s.id">
                                                        <option :value="s.id" x-text="s.nama_satuan"></option>
                                                    </template>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            
                            <div x-show="itemsAdditional.length === 0" class="text-sm text-gray-500 text-center py-4 italic">Belum ada barang ditambahkan.</div>
                        </div>

                        <div x-show="itemsAdditional.length > 0" class="flex justify-end mt-4">
                            <button type="submit" :disabled="submittingOrder" :class="submittingOrder ? 'opacity-50 cursor-not-allowed' : ''" class="inline-flex items-center justify-center px-5 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-lg hover:bg-blue-700 transition shadow-sm">
                                <span x-text="submittingOrder ? 'Memproses...' : 'Kirim Order'"></span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            @else
            <div class="xl:col-span-1 rounded-lg border border-gray-200 bg-gray-50 dark:bg-gray-800/50 p-5 shadow-sm dark:border-gray-700 text-center flex flex-col items-center justify-center min-h-[300px]">
                <i class="fa-solid fa-circle-check text-4xl text-green-500 mb-3"></i>
                <h3 class="font-bold text-gray-900 dark:text-white mb-1">Pembangunan Selesai</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Kawasan ini sudah berstatus selesai. Tidak dapat membuat order barang lagi.</p>
            </div>
            @endif

            <!-- Kanan: Riwayat Order -->
            <div class="xl:col-span-1 rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 flex flex-col h-full">
                <h3 class="font-bold text-gray-900 dark:text-white mb-4 flex-none">Riwayat Order</h3>
                <div class="relative flex-1 min-h-[300px]">
                    <div class="absolute inset-0 overflow-y-auto pr-2 custom-scrollbar space-y-4">
                        @if($data->orders && $data->orders->count() > 0)
                        @php $lastSesiSeen = null; @endphp
                        @foreach($data->orders->sortByDesc('created_at') as $order)
                        @php
                            $currentPeriodeObj = null;
                            if ($data->periodes && $data->periodes->count() > 0) {
                                $sortedAsc = $data->periodes->sortBy('created_at')->values();
                                if ($order->pembangunan_kawasan_periode_id) {
                                    foreach ($sortedAsc as $idx => $per) {
                                        if ($per->id == $order->pembangunan_kawasan_periode_id) {
                                            $currentPeriodeObj = $per;
                                            break;
                                        }
                                    }
                                }
                                if (!$currentPeriodeObj) {
                                    $sortedDesc = $data->periodes->sortByDesc('created_at')->values();
                                    $orderTime = \Carbon\Carbon::parse($order->created_at ?? $order->tanggal_diajukan);
                                    foreach ($sortedDesc as $per) {
                                        $perCreated = \Carbon\Carbon::parse($per->created_at);
                                        if ($orderTime->gte($perCreated)) {
                                            $currentPeriodeObj = $per;
                                            break;
                                        }
                                    }
                                    if (!$currentPeriodeObj) {
                                        $currentPeriodeObj = $sortedAsc->first();
                                    }
                                }
                            }

                            $dateText = '';
                            if ($currentPeriodeObj) {
                                $tglMulai = $currentPeriodeObj->tanggal_mulai ? \Carbon\Carbon::parse($currentPeriodeObj->tanggal_mulai)->format('d M Y') : '-';
                                $tglSelesai = $currentPeriodeObj->tanggal_selesai ? \Carbon\Carbon::parse($currentPeriodeObj->tanggal_selesai)->format('d M Y') : 'Sekarang';
                                $dateText = "$tglMulai s/d $tglSelesai";
                            }
                        @endphp

                        @if($dateText && $lastSesiSeen !== $dateText)
                            @php
                                $lastSesiSeen = $dateText;
                            @endphp
                            <div class="flex items-center gap-3 my-4">
                                <div class="h-px bg-gray-200 dark:bg-gray-700 flex-1"></div>
                                <span class="text-[10px] font-black text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-900/40 px-3.5 py-1.5 rounded-full border border-purple-100 dark:border-purple-800 uppercase tracking-wider shadow-sm flex items-center gap-1.5">
                                    <i class="fa-regular fa-calendar text-xs"></i> <span>{{ $dateText }}</span>
                                </span>
                                <div class="h-px bg-gray-200 dark:bg-gray-700 flex-1"></div>
                            </div>
                        @endif
                        <div x-data="{ open: false }" class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm bg-white dark:bg-gray-800/40">
                            <div @click="open = !open" class="flex flex-col gap-2 p-4 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors cursor-pointer border-b border-gray-200 dark:border-gray-700">
                                {{-- Baris 1: Tanggal + Nomor Order + Chevron --}}
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex flex-col gap-0.5 min-w-0">
                                        <p class="text-[9px] text-gray-400 font-medium uppercase tracking-wider">
                                            {{ \Carbon\Carbon::parse($order->tanggal_diajukan)->translatedFormat('d M Y, H:i') }}
                                        </p>
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            <p class="text-xs font-bold text-gray-700 dark:text-gray-200 truncate">
                                                {{ $order->nomor_order }}
                                            </p>
                                        </div>
                                    </div>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-gray-400 transition-transform duration-300 shrink-0 mt-1" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                                {{-- Baris 2: Status (Kiri) dan Tipe Stock/Direct (Pojok Kanan Bawah) --}}
                                <div class="flex items-center justify-between gap-2 pt-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        @php
                                            $statusMap = [
                                                'diproses'     => 'bg-blue-50 text-blue-600 border-blue-100',
                                                'selesai'      => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                                'ditolak'      => 'bg-red-50 text-red-600 border-red-100',
                                                'return_pending'=> 'bg-orange-50 text-orange-600 border-orange-100',
                                                'pengembalian' => 'bg-orange-50 text-orange-600 border-orange-100',
                                            ];
                                            $style = $statusMap[$order->status_order] ?? 'bg-gray-50 text-gray-500 border-gray-100';
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[8px] font-black uppercase border {{ $style }}">
                                            {{ str_replace('_', ' ', $order->status_order) }}
                                        </span>
                                    </div>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider border shrink-0 {{ $order->jenis_order === 'stock' ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-amber-50 text-amber-600 border-amber-100' }}">
                                        {{ $order->jenis_order }}
                                    </span>
                                </div>
                            </div>
                            
                            <div x-show="open" x-collapse x-cloak class="bg-gray-50/50 dark:bg-gray-900/40 p-4 border-t border-gray-100 dark:border-gray-800">
                                <div class="flex items-center justify-between mb-2">
                                    <h5 class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Detail Item Barang</h5>
                                    <span class="text-[9px] font-black text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full">{{ $order->details->count() }} Item</span>
                                </div>
                                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden mb-3">
                                    <div class="max-h-[350px] overflow-y-auto custom-scrollbar">
                                        <table class="w-full text-left border-collapse">
                                            <thead class="bg-gray-50/80 dark:bg-gray-700/50 sticky top-0 z-10 backdrop-blur-sm">
                                                <tr>
                                                    <th class="px-3 py-2 text-[9px] font-bold text-gray-400 uppercase border-b border-gray-100 dark:border-gray-700">Nama Barang</th>
                                                    <th class="px-3 py-2 text-[9px] font-bold text-gray-400 uppercase text-right border-b border-gray-100 dark:border-gray-700">Jumlah</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                                @foreach($order->details as $d)
                                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/50 transition-colors">
                                                        <td class="px-3 py-3">
                                                            <p class="text-xs font-bold text-gray-700 dark:text-gray-200">{{ $d->nama_barang }}</p>
                                                            @if($d->returnDetail && $d->returnDetail->jumlah_return > 0)
                                                                <div class="mt-1 text-[10px] text-orange-600 dark:text-orange-400 font-medium bg-orange-50 dark:bg-orange-900/30 inline-block px-2 py-1 rounded">
                                                                    <span class="font-bold">Retur:</span> {{ (float) $d->returnDetail->jumlah_return }} {{ $d->satuanModel->nama_satuan ?? $d->satuan }}
                                                                    @if($d->returnDetail->keterangan_return)
                                                                        <br><span class="font-bold">Alasan:</span> {{ $d->returnDetail->keterangan_return }}
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        </td>
                                                        <td class="px-3 py-3 text-right">
                                                            <div class="text-xs font-bold text-gray-900 dark:text-white">{{ (float) $d->jumlah_input }} <span class="text-[9px] font-medium text-gray-500 uppercase">{{ $d->satuanModel->nama_satuan ?? $d->satuan }}</span></div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                                @if($order->catatan)
                                <div class="mt-2 text-xs text-gray-600 dark:text-gray-300 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-100 dark:border-yellow-900/50 p-3 rounded-lg">
                                    <span class="font-bold uppercase tracking-wider text-[9px] text-yellow-600 block mb-1">Catatan Pengajuan:</span> 
                                    {{ $order->catatan }}
                                </div>
                                @endif

                                <div class="mt-3 text-[10px] space-y-1 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800/60 p-2.5 rounded-lg border border-gray-100 dark:border-gray-700">
                                    <div>
                                        <span class="font-bold text-gray-700 dark:text-gray-300">Diajukan oleh:</span>
                                        {{ $order->pembuat->nama_lengkap ?? $order->pembuat->name ?? $order->pembuat->email ?? '-' }}
                                    </div>
                                    <div>
                                        <span class="font-bold text-gray-700 dark:text-gray-300">Dikonfirmasi oleh:</span>
                                        {{ $order->accUser->nama_lengkap ?? $order->accUser->name ?? $order->accUser->email ?? '-' }}
                                    </div>
                                </div>



                                @if ($order->status_order == 'diproses')
                                     <div class="pt-4 flex justify-end">
                                         <button type="button"
                                             @click="openCancelOrderModal = true; cancelOrderActionUrl = '{{ route('produksi.pembangunanKawasan.orderDestroy', $order->id) }}'"
                                             class="px-4 py-2 text-[10px] font-black bg-red-50 hover:bg-red-100 dark:bg-red-950/20 text-red-600 rounded-xl uppercase border border-red-200 dark:border-red-800/50 transition-all duration-300 flex items-center justify-center gap-2 shadow-sm">
                                             <i class="fa-solid fa-trash-can"></i>
                                             Batalkan Orderan
                                         </button>
                                     </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                        @else
                            <div class="text-center py-10 text-gray-500 dark:text-gray-400 text-sm">Belum ada order barang.</div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>

    @if(false)
    <!-- Tab Content: Pengajuan Upah -->
    <div x-show="tab === 'upah'" style="display: none;">
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            
            @if ($data->status_pembangunan !== 'selesai')
            <!-- Kiri: Form Pengajuan Upah -->
            <div class="xl:col-span-1 rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h3 class="font-bold text-gray-900 dark:text-white mb-4">Buat Pengajuan Upah</h3>
                <form action="{{ route('produksi.pembangunanKawasan.upahStore') }}" method="POST">
                    @csrf
                    <input type="hidden" name="pembangunan_kawasan_id" value="{{ $data->id }}">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Nama Upah <span class="text-red-500">*</span></label>
                            <select name="nama_upah" required class="select2 block w-full rounded-lg border border-gray-300 bg-white p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white" style="width: 100%;" x-init="$(document).ready(function() { $($el).select2({ theme: 'bootstrap4', width: '100%' }); });">
                                <option value="">-- Pilih Upah --</option>
                                @foreach($penamaanUpah as $u)
                                    <option value="{{ $u->nama_upah }}">{{ $u->nama_upah }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div x-data="{ 
                            nominal_display: '', 
                            nominal_raw: '',
                            formatRupiah(val) {
                                if (!val) return '';
                                return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                            },
                            parseNumber(val) {
                                if (!val) return '';
                                return val.toString().replace(/\./g, '').replace(/[^0-9]/g, '');
                            }
                        }">
                            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Nominal Diajukan (Rp) <span class="text-red-500">*</span></label>
                            <input type="hidden" name="nominal_diajukan" :value="nominal_raw">
                            <input type="text" x-model="nominal_display" @input="nominal_raw = parseNumber(nominal_display); nominal_display = formatRupiah(nominal_raw)" required class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Catatan Pengawas</label>
                            <textarea name="catatan_pengawas" rows="4" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                        </div>
                        <div class="md:col-span-2 flex justify-end">
                            <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 bg-green-600 text-white text-sm font-bold rounded-lg hover:bg-green-700 transition shadow-sm">Kirim Pengajuan</button>
                        </div>
                    </div>
                </form>
            </div>
            @else
            <div class="xl:col-span-1 rounded-lg border border-gray-200 bg-gray-50 dark:bg-gray-800/50 p-5 shadow-sm dark:border-gray-700 text-center flex flex-col items-center justify-center min-h-[250px]">
                <i class="fa-solid fa-circle-check text-4xl text-green-500 mb-3"></i>
                <h3 class="font-bold text-gray-900 dark:text-white mb-1">Pembangunan Selesai</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Kawasan ini sudah berstatus selesai. Tidak dapat mengajukan upah lagi.</p>
            </div>
            @endif

            <!-- Kanan: Riwayat Pengajuan Upah -->
            <div class="xl:col-span-1 rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 flex flex-col h-full">
                <h3 class="font-bold text-gray-900 dark:text-white mb-4 flex-none">Riwayat Pengajuan Upah</h3>
                <div class="relative flex-1 min-h-[300px]">
                    <div class="absolute inset-0 overflow-y-auto pr-2 custom-scrollbar space-y-4">
                        @if($data->pengajuanUpah && $data->pengajuanUpah->count() > 0)
                        @foreach($data->pengajuanUpah->sortByDesc('created_at') as $u)
                        <div x-data="{ open: false }" class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm bg-white dark:bg-gray-800/40">
                            <div @click="open = !open" class="flex flex-col gap-2 p-4 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors cursor-pointer border-b border-gray-200 dark:border-gray-700">
                                {{-- Baris 1: Tanggal + Nama Upah --}}
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex flex-col gap-0.5 min-w-0">
                                        <p class="text-[9px] text-gray-400 font-medium uppercase tracking-wider">
                                            {{ \Carbon\Carbon::parse($u->tanggal_diajukan)->translatedFormat('d M Y, H:i') }}
                                        </p>
                                        <p class="text-xs font-bold text-gray-700 dark:text-gray-200 truncate">
                                            {{ $u->nama_upah }}
                                        </p>
                                    </div>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-gray-400 transition-transform duration-300 shrink-0 mt-1" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                                {{-- Baris 2: Nominal + Badge Status --}}
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-black text-gray-800 dark:text-white font-mono">
                                        Rp {{ number_format($u->nominal_diajukan, 0, ',', '.') }}
                                    </span>
                                    <span class="text-gray-300 dark:text-gray-600 text-xs">·</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[8px] font-black uppercase border {{ $u->status_style ?? 'bg-gray-50 text-gray-500 border-gray-100' }}">
                                        {{ $u->status_label ?? str_replace('_', ' ', $u->status_pengajuan) }}
                                    </span>
                                </div>
                            </div>

                            <div x-show="open" x-collapse x-cloak class="bg-gray-50/50 dark:bg-gray-900/40 p-5 border-t border-gray-100 dark:border-gray-800">
                                <div class="space-y-4">
                                    @if($u->catatan_pengawas)
                                    <div class="text-xs text-gray-600 dark:text-gray-300 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-100 dark:border-yellow-900/50 p-3 rounded-lg">
                                        <span class="font-bold uppercase tracking-wider text-[9px] text-yellow-600 block mb-1">Catatan Pengawas:</span>
                                        {{ $u->catatan_pengawas }}
                                    </div>
                                    @endif
                                    
                                    <h5 class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-4">Log Persetujuan</h5>
                                    <div class="relative border-l-2 border-gray-200 dark:border-gray-700 ml-2 space-y-5 pb-2">
                                        
                                        {{-- 1. Diajukan --}}
                                        <div class="relative pl-6">
                                            <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-blue-500 border-4 border-white dark:border-gray-900 shadow-sm"></div>
                                            <p class="text-[10px] font-black text-gray-700 dark:text-gray-200 uppercase">Pengajuan Dikirim</p>
                                            <p class="text-[9px] text-gray-400">{{ \Carbon\Carbon::parse($u->tanggal_diajukan)->format('d M Y, H:i') }}</p>
                                        </div>

                                        {{-- 2. MGR Produksi --}}
                                        <div class="relative pl-6">
                                            @php
                                                $isRejectedProduksi = $u->status_pengajuan === 'ditolak_mgr_produksi';
                                                $isApprovedProduksi = !empty($u->disetujui_mgr_produksi);
                                                $isRejectedAny = $isRejectedProduksi;
                                                $dotColorProduksi = $isApprovedProduksi ? 'bg-emerald-500' : ($isRejectedProduksi ? 'bg-red-500' : 'bg-gray-300 dark:bg-gray-700');
                                                $textColorProduksi = $isApprovedProduksi ? 'text-emerald-600' : ($isRejectedProduksi ? 'text-red-600' : 'text-gray-400');
                                            @endphp
                                            <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full border-4 border-white dark:border-gray-900 shadow-sm {{ $dotColorProduksi }}"></div>
                                            <p class="text-[10px] font-black uppercase {{ $textColorProduksi }}">Manager Produksi</p>
                                            @if($isApprovedProduksi)
                                                <p class="text-[9px] text-gray-400">Disetujui: {{ \Carbon\Carbon::parse($u->disetujui_mgr_produksi)->format('d M Y, H:i') }}</p>
                                            @elseif($isRejectedProduksi)
                                                <p class="text-[9px] text-red-500">Ditolak: {{ \Carbon\Carbon::parse($u->ditolak_pada)->format('d M Y, H:i') }}</p>
                                            @else
                                                <p class="text-[9px] text-gray-400 italic">Menunggu Persetujuan</p>
                                            @endif
                                        </div>

                                        {{-- 3. MGR Dukungan --}}
                                        <div class="relative pl-6">
                                            @php
                                                $isRejectedDukungan = $u->status_pengajuan === 'ditolak_mgr_dukungan';
                                                $isApprovedDukungan = !empty($u->disetujui_mgr_dukungan);
                                                $dotColorDukungan = $isApprovedDukungan ? 'bg-emerald-500' : ($isRejectedDukungan ? 'bg-red-500' : 'bg-gray-300 dark:bg-gray-700');
                                                $textColorDukungan = $isApprovedDukungan ? 'text-emerald-600' : ($isRejectedDukungan ? 'text-red-600' : 'text-gray-400');
                                            @endphp
                                            <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full border-4 border-white dark:border-gray-900 shadow-sm {{ $dotColorDukungan }}"></div>
                                            <p class="text-[10px] font-black uppercase {{ $textColorDukungan }}">Manager Dukungan</p>
                                            @if($isApprovedDukungan)
                                                <p class="text-[9px] text-gray-400">Disetujui: {{ \Carbon\Carbon::parse($u->disetujui_mgr_dukungan)->format('d M Y, H:i') }}</p>
                                            @elseif($isRejectedDukungan)
                                                <p class="text-[9px] text-red-500">Ditolak: {{ \Carbon\Carbon::parse($u->ditolak_pada)->format('d M Y, H:i') }}</p>
                                            @elseif($isRejectedAny)
                                                <p class="text-[9px] text-gray-400 italic">Dibatalkan</p>
                                            @else
                                                <p class="text-[9px] text-gray-400 italic">Menunggu Persetujuan</p>
                                            @endif
                                            @php $isRejectedAny = $isRejectedAny || $isRejectedDukungan; @endphp
                                        </div>

                                        {{-- 4. Akuntan (Final) --}}
                                        <div class="relative pl-6">
                                            @php
                                                $isRejectedAkuntan = $u->status_pengajuan === 'ditolak_akuntan';
                                                $isApprovedAkuntan = !empty($u->disetujui_akuntan);
                                                $dotColorAkuntan = $isApprovedAkuntan ? 'bg-emerald-500' : ($isRejectedAkuntan ? 'bg-red-500' : 'bg-gray-300 dark:bg-gray-700');
                                                $textColorAkuntan = $isApprovedAkuntan ? 'text-emerald-600' : ($isRejectedAkuntan ? 'text-red-600' : 'text-gray-400');
                                            @endphp
                                            <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full border-4 border-white dark:border-gray-900 shadow-sm {{ $dotColorAkuntan }}"></div>
                                            <p class="text-[10px] font-black uppercase {{ $textColorAkuntan }}">Akuntan (Final)</p>
                                            @if($isApprovedAkuntan)
                                                <p class="text-[9px] text-gray-400">Cair pada: {{ \Carbon\Carbon::parse($u->disetujui_akuntan)->format('d M Y, H:i') }}</p>
                                            @elseif($isRejectedAkuntan)
                                                <p class="text-[9px] text-red-500">Ditolak: {{ \Carbon\Carbon::parse($u->ditolak_pada)->format('d M Y, H:i') }}</p>
                                            @elseif($isRejectedAny)
                                                <p class="text-[9px] text-gray-400 italic">Dibatalkan</p>
                                            @else
                                                <p class="text-[9px] text-gray-400 italic">Menunggu Pencairan</p>
                                            @endif
                                        </div>
                                    </div>

                                     @if($u->alasan_ditolak)
                                     <div class="mt-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-900/30 rounded-lg">
                                         <div class="flex items-center gap-2 mb-1">
                                             <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                             </svg>
                                             <span class="text-[9px] font-black text-red-600 uppercase">Alasan Penolakan:</span>
                                         </div>
                                         <p class="text-xs text-red-700 dark:text-red-400 font-medium">{{ $u->alasan_ditolak }}</p>
                                     </div>
                                     @endif

                                     @if(is_null($u->disetujui_mgr_produksi) && is_null($u->disetujui_mgr_dukungan) && is_null($u->disetujui_akuntan) && is_null($u->ditolak_pada))
                                     <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex justify-end">
                                         <button type="button"
                                             @click="openCancelUpahModal = true; cancelUpahActionUrl = '{{ route('produksi.pembangunanKawasan.upahDestroy', $u->id) }}'"
                                             class="px-4 py-2 text-[10px] font-black bg-red-50 hover:bg-red-100 dark:bg-red-950/20 text-red-600 rounded-xl uppercase border border-red-200 dark:border-red-800/50 transition-all duration-300 flex items-center justify-center gap-2 shadow-sm">
                                             <i class="fa-solid fa-trash-can"></i>
                                             Batalkan Pengajuan Upah
                                         </button>
                                     </div>
                                     @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                        @else
                            <div class="text-center py-10 text-gray-500 dark:text-gray-400 text-sm">Belum ada pengajuan upah.</div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
    @endif

    <!-- Tab Content: Retur Barang -->
    <div x-show="tab === 'retur'" style="display: none;">
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            @if ($data->status_pembangunan !== 'selesai')
            <!-- Kiri (50%): Form Buat Retur Barang -->
            <div class="xl:col-span-1 rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h3 class="font-bold text-gray-900 dark:text-white mb-4">Buat Retur Barang</h3>
                <form action="{{ route('produksi.pembangunanKawasan.returnStore') }}" method="POST" x-data="{ submittingRetur: false }" @submit="if(submittingRetur) { $event.preventDefault(); return; }; submittingRetur = true">
                    @csrf
                    <input type="hidden" name="pembangunan_kawasan_id" value="{{ $data->id }}">

                    <div class="space-y-4">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Catatan Retur</label>
                            <textarea name="catatan" rows="3" placeholder="Contoh: Sisa material penataan saluran air kawasan..."
                                class="bg-white block w-full rounded-lg border border-gray-300 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                        </div>

                        <!-- Dynamic Items Retur -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
                            <div class="flex justify-between items-center mb-2">
                                <h4 class="font-semibold text-sm text-gray-900 dark:text-white">Daftar Barang Retur</h4>
                                <button type="button" @click="addReturnKawasanItem()" class="text-sm text-blue-600 hover:underline"><i class="fa-solid fa-plus"></i> Tambah Item</button>
                            </div>

                            <div class="max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                                <template x-for="(item, index) in returnKawasanItems" :key="index">
                                    <div class="bg-gray-50 dark:bg-gray-900 p-3 rounded-lg border border-gray-200 dark:border-gray-700 mb-3 space-y-3 relative">
                                        <button type="button" @click="removeReturnKawasanItem(index)" class="absolute top-2 right-2 flex items-center justify-center w-6 h-6 rounded-full bg-red-100 text-red-500 hover:bg-red-200 transition-colors font-bold text-xs" title="Hapus Barang">X</button>
                                        
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Barang</label>
                                            <select :name="'items['+index+'][barang_id]'" 
                                                class="select2-dynamic block w-full rounded border-gray-300 bg-white text-xs text-gray-900 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-blue-500 focus:border-blue-500" 
                                                required
                                                x-init="$nextTick(() => { 
                                                    $($el).select2({theme: 'bootstrap4', width: '100%'})
                                                    .on('change', (e) => { updateReturnKawasanBarang(index, e); }); 
                                                })">
                                                <option value="">-- Pilih Barang Order --</option>
                                                @foreach($returnableBarang as $rb)
                                                    <option value="{{ $rb['barang_id'] }}">{{ $rb['nama_barang'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Live Info Badge Diterima, Sudah Retur, Sisa Retur -->
                                        <template x-if="item.barang_id">
                                            <div class="p-2.5 bg-blue-50/60 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800/50 rounded-lg text-xs space-y-1">
                                                <div class="flex flex-wrap items-center justify-between text-[11px] gap-2 font-semibold">
                                                    <span class="text-blue-800 dark:text-blue-300">Diterima: <strong class="font-bold" x-text="item.diterima_disp"></strong> <span x-text="item.satuan_nama"></span></span>
                                                    <span class="text-amber-800 dark:text-amber-300">Sudah Retur: <strong class="font-bold" x-text="item.sudah_retur_disp"></strong> <span x-text="item.satuan_nama"></span></span>
                                                    <span class="text-emerald-800 dark:text-emerald-300">Sisa Maks: <strong class="font-bold" x-text="item.sisa_retur_disp"></strong> <span x-text="item.satuan_nama"></span></span>
                                                </div>
                                            </div>
                                        </template>

                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Satuan</label>
                                                <select :name="'items['+index+'][satuan_id]'" x-model="item.satuan_id" @change="updateReturnKawasanSatuan(index)" required class="block w-full rounded border-gray-300 bg-white text-xs text-gray-900 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                                    <template x-for="s in item.satuan_options" :key="s.satuan_id">
                                                        <option :value="s.satuan_id" x-text="s.nama_satuan"></option>
                                                    </template>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Jumlah Retur</label>
                                                <input type="number" step="any" min="0.001" :max="item.sisa_retur_disp" :name="'items['+index+'][jumlah_input]'" x-model.number="item.jumlah_input" required class="block w-full rounded border-gray-300 bg-white text-xs font-bold text-gray-900 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Keterangan / Alasan Item</label>
                                            <input type="text" :name="'items['+index+'][keterangan]'" x-model="item.keterangan" placeholder="Contoh: Sisa pengerjaan / Rusak / Bengkok..." class="block w-full rounded border-gray-300 bg-white text-xs text-gray-900 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                    </div>
                                </template>
                            </div>
                            
                            @if(count($returnableBarang) === 0)
                                <div class="text-xs text-amber-600 bg-amber-50 dark:bg-amber-900/20 p-3 rounded-lg border border-amber-200 dark:border-amber-700 text-center font-medium mt-2">
                                    Belum ada barang yang selesai diorder/diterima dari gudang untuk dilakukan retur.
                                </div>
                            @endif
                            
                            <div x-show="returnKawasanItems.length === 0" class="text-sm text-gray-500 text-center py-4 italic">Belum ada barang retur ditambahkan.</div>
                        </div>

                        <div x-show="returnKawasanItems.length > 0" class="flex justify-end mt-4">
                            <button type="submit" :disabled="submittingRetur" :class="submittingRetur ? 'opacity-50 cursor-not-allowed' : ''" class="inline-flex items-center justify-center px-5 py-2.5 bg-orange-600 text-white text-sm font-bold rounded-lg hover:bg-orange-700 transition shadow-sm">
                                <span x-text="submittingRetur ? 'Memproses...' : 'Kirim Retur Barang'"></span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            @else
            <div class="xl:col-span-1 rounded-lg border border-gray-200 bg-gray-50 dark:bg-gray-800/50 p-5 shadow-sm dark:border-gray-700 text-center flex flex-col items-center justify-center min-h-[300px]">
                <i class="fa-solid fa-circle-check text-4xl text-green-500 mb-3"></i>
                <h3 class="font-bold text-gray-900 dark:text-white mb-1">Pembangunan Selesai</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Kawasan ini sudah berstatus selesai. Tidak dapat membuat pengajuan retur barang lagi.</p>
            </div>
            @endif

            <!-- Kanan (50%): Riwayat Retur Barang (Accordion layout) -->
            <div class="xl:col-span-1 rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 flex flex-col h-full">
                <h3 class="font-bold text-gray-900 dark:text-white mb-4 flex-none">Riwayat Retur Barang</h3>
                <div class="relative flex-1 min-h-[300px]">
                    <div class="absolute inset-0 overflow-y-auto pr-2 custom-scrollbar space-y-4">
                        @if(isset($returns) && $returns->count() > 0)
                        @php $lastRetSesiSeen = null; @endphp
                        @foreach($returns as $ret)
                        @php
                            $currentRetPeriodeObj = null;
                            if ($data->periodes && $data->periodes->count() > 0) {
                                $sortedAsc = $data->periodes->sortBy('created_at')->values();
                                if ($ret->pembangunan_kawasan_periode_id) {
                                    foreach ($sortedAsc as $idx => $per) {
                                        if ($per->id == $ret->pembangunan_kawasan_periode_id) {
                                            $currentRetPeriodeObj = $per;
                                            break;
                                        }
                                    }
                                }
                                if (!$currentRetPeriodeObj) {
                                    $sortedDesc = $data->periodes->sortByDesc('created_at')->values();
                                    $returTime = \Carbon\Carbon::parse($ret->created_at ?? $ret->tanggal_return);
                                    foreach ($sortedDesc as $per) {
                                        $perCreated = \Carbon\Carbon::parse($per->created_at);
                                        if ($returTime->gte($perCreated)) {
                                            $currentRetPeriodeObj = $per;
                                            break;
                                        }
                                    }
                                    if (!$currentRetPeriodeObj) {
                                        $currentRetPeriodeObj = $sortedAsc->first();
                                    }
                                }
                            }

                            $dateRetText = '';
                            if ($currentRetPeriodeObj) {
                                $tglMulai = $currentRetPeriodeObj->tanggal_mulai ? \Carbon\Carbon::parse($currentRetPeriodeObj->tanggal_mulai)->format('d M Y') : '-';
                                $tglSelesai = $currentRetPeriodeObj->tanggal_selesai ? \Carbon\Carbon::parse($currentRetPeriodeObj->tanggal_selesai)->format('d M Y') : 'Sekarang';
                                $dateRetText = "$tglMulai s/d $tglSelesai";
                            }
                        @endphp

                        @if($dateRetText && $lastRetSesiSeen !== $dateRetText)
                            @php
                                $lastRetSesiSeen = $dateRetText;
                            @endphp
                            <div class="flex items-center gap-3 my-4">
                                <div class="h-px bg-gray-200 dark:bg-gray-700 flex-1"></div>
                                <span class="text-[10px] font-black text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-900/40 px-3.5 py-1.5 rounded-full border border-purple-100 dark:border-purple-800 uppercase tracking-wider shadow-sm flex items-center gap-1.5">
                                    <i class="fa-regular fa-calendar text-xs"></i> <span>{{ $dateRetText }}</span>
                                </span>
                                <div class="h-px bg-gray-200 dark:bg-gray-700 flex-1"></div>
                            </div>
                        @endif
                        <div x-data="{ open: false }" class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm bg-white dark:bg-gray-800/40">
                            <div @click="open = !open" class="flex flex-col gap-2 p-4 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors cursor-pointer border-b border-gray-200 dark:border-gray-700">
                                {{-- Baris 1: Tanggal + Nomor Retur --}}
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex flex-col gap-0.5 min-w-0">
                                        <p class="text-[9px] text-gray-400 font-medium uppercase tracking-wider">
                                            {{ \Carbon\Carbon::parse($ret->tanggal_return ?? $ret->tanggal_diajukan)->translatedFormat('d M Y, H:i') }}
                                        </p>
                                        <p class="text-xs font-bold text-gray-700 dark:text-gray-200 truncate">
                                            {{ $ret->nomor_return ?? ('RTN-KWS-' . str_pad($ret->id, 5, '0', STR_PAD_LEFT)) }}
                                        </p>
                                    </div>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-gray-400 transition-transform duration-300 shrink-0 mt-1" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                                {{-- Baris 2: Badge Status --}}
                                <div class="flex flex-wrap items-center gap-2">
                                    @php
                                        $statusMap = [
                                            'diproses' => 'bg-blue-50 text-blue-600 border-blue-100',
                                            'selesai'  => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                            'ditolak'  => 'bg-red-50 text-red-600 border-red-100',
                                        ];
                                        $style = $statusMap[$ret->status] ?? 'bg-gray-50 text-gray-500 border-gray-100';
                                        $label = [
                                            'diproses' => 'Menunggu',
                                            'selesai'  => 'Selesai',
                                            'ditolak'  => 'Ditolak',
                                        ][$ret->status] ?? strtoupper($ret->status);
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[8px] font-black uppercase border {{ $style }}">
                                        {{ $label }}
                                    </span>
                                </div>
                            </div>
                            
                            <div x-show="open" x-collapse x-cloak class="bg-gray-50/50 dark:bg-gray-900/40 p-4 border-t border-gray-100 dark:border-gray-800">
                                <div class="flex items-center justify-between mb-2">
                                    <h5 class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Detail Item Retur</h5>
                                    <span class="text-[9px] font-black text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full">{{ $ret->details->count() }} Item</span>
                                </div>
                                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden mb-3">
                                    <table class="w-full text-left border-collapse text-xs">
                                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                                            <tr>
                                                <th class="px-3 py-2 font-bold text-gray-500">Barang</th>
                                                <th class="px-3 py-2 font-bold text-gray-500 text-center">Retur</th>
                                                @if ($ret->status === 'selesai')
                                                    <th class="px-3 py-2 font-bold text-emerald-600 text-center">Layak</th>
                                                    <th class="px-3 py-2 font-bold text-red-600 text-center">Rusak</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                            @foreach ($ret->details as $rDet)
                                                <tr>
                                                    <td class="px-3 py-2">
                                                        <p class="font-bold text-gray-800 dark:text-white leading-tight">
                                                            {{ $rDet->nama_barang ?? $rDet->barang?->nama_barang ?? '-' }}
                                                        </p>
                                                        @if ($rDet->keterangan)
                                                            <p class="text-[10px] text-gray-400 dark:text-gray-400 italic mt-0.5">
                                                                Ket: {{ $rDet->keterangan }}
                                                            </p>
                                                        @endif
                                                    </td>
                                                    <td class="px-3 py-2 text-center font-bold text-gray-900 dark:text-white">
                                                        {{ (float)$rDet->jumlah_input }} {{ $rDet->satuan }}
                                                    </td>
                                                    @if ($ret->status === 'selesai')
                                                        <td class="px-3 py-2 text-center font-bold text-emerald-600">
                                                            {{ (float)$rDet->jumlah_layak_base }} {{ $rDet->barang?->baseUnit?->nama ?? '' }}
                                                        </td>
                                                        <td class="px-3 py-2 text-center font-bold text-red-600">
                                                            {{ (float)$rDet->jumlah_rusak_base }} {{ $rDet->barang?->baseUnit?->nama ?? '' }}
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                @if ($ret->catatan)
                                    <div class="p-2.5 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700/50 rounded-lg text-xs text-yellow-800 dark:text-yellow-200 mb-2">
                                        <span class="font-bold">Catatan:</span> {{ $ret->catatan }}
                                    </div>
                                @endif

                                @if ($ret->status === 'ditolak' && $ret->alasan_tolak)
                                    <div class="p-2.5 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700/50 rounded-lg text-xs text-red-800 dark:text-red-200 mb-2">
                                        <span class="font-bold">Alasan Ditolak:</span> {{ $ret->alasan_tolak }}
                                    </div>
                                @endif

                                <div class="mt-3 text-[10px] space-y-1 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800/60 p-2.5 rounded-lg border border-gray-100 dark:border-gray-700">
                                    <div>
                                        <span class="font-bold text-gray-700 dark:text-gray-300">Diajukan oleh:</span>
                                        {{ $ret->createdBy->nama_lengkap ?? $ret->createdBy->name ?? $ret->createdBy->email ?? '-' }}
                                    </div>
                                    <div>
                                        <span class="font-bold text-gray-700 dark:text-gray-300">Dikonfirmasi oleh:</span>
                                        {{ $ret->accBy->nama_lengkap ?? $ret->accBy->name ?? $ret->accBy->email ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        @else
                        <div class="text-center py-8 text-sm text-gray-400">Belum ada riwayat retur barang</div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
    


    <!-- Modal Konfirmasi Batal Order -->
    <template x-teleport="body">
        <div x-show="openCancelOrderModal"
            class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/50 dark:bg-black/80"
            x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100">
            <div @click.away="openCancelOrderModal = false" class="relative w-full max-w-md p-4">
                <div class="relative bg-white rounded-xl shadow-xl dark:bg-gray-800 overflow-hidden border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between p-4 border-b dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Batalkan Order Barang</h3>
                        <button type="button" @click="openCancelOrderModal = false" class="text-gray-400 hover:text-gray-900 dark:hover:text-white">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                            </svg>
                        </button>
                    </div>

                    <form :action="cancelOrderActionUrl" method="POST" class="p-5 space-y-4">
                        @csrf
                        @method('DELETE')
                        
                        <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                            Apakah Anda yakin ingin membatalkan order barang ini? Tindakan ini tidak dapat dibatalkan.
                        </p>

                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-600">
                            <button type="button" @click="openCancelOrderModal = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                Kembali
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:ring-red-300 shadow-sm transition">
                                Ya, Batalkan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

    <!-- Modal Konfirmasi Batal Upah -->
    <template x-teleport="body">
        <div x-show="openCancelUpahModal"
            class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/50 dark:bg-black/80"
            x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100">
            <div @click.away="openCancelUpahModal = false" class="relative w-full max-w-md p-4">
                <div class="relative bg-white rounded-xl shadow-xl dark:bg-gray-800 overflow-hidden border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between p-4 border-b dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Batalkan Pengajuan Upah</h3>
                        <button type="button" @click="openCancelUpahModal = false" class="text-gray-400 hover:text-gray-900 dark:hover:text-white">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                            </svg>
                        </button>
                    </div>

                    <form :action="cancelUpahActionUrl" method="POST" class="p-5 space-y-4">
                        @csrf
                        @method('DELETE')
                        
                        <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                            Apakah Anda yakin ingin membatalkan pengajuan upah ini? Tindakan ini tidak dapat dibatalkan.
                        </p>

                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-600">
                            <button type="button" @click="openCancelUpahModal = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                Kembali
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:ring-red-300 shadow-sm transition">
                                Ya, Batalkan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        if ($('.select2').length) {
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%'
            });
        }

    });
</script>
<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f1f1; 
        border-radius: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #c1c1c1; 
        border-radius: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8; 
    }
    .dark .custom-scrollbar::-webkit-scrollbar-track {
        background: #374151; 
    }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #4B5563; 
    }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #6B7280; 
    }
</style>
@endpush
