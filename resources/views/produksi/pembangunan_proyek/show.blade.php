@extends('layouts.app')

@section('pageActive', 'pembangunanProyek')

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
    itemsAdditional: [],
    filterType: 'stock',
    allBarang: {{ json_encode($allBarang) }},
    openReturnModal: false,
    returnItems: [],
    returnOrderId: null,
    openCancelOrderModal: false,
    cancelOrderActionUrl: '',
    openCancelUpahModal: false,
    cancelUpahActionUrl: '',

    prepareReturn(orderId, items) {
        this.returnOrderId = orderId;
        this.returnItems = items.map(i => ({
            ...i,
            retur: i.retur ? parseFloat(i.retur) : 0,
            keterangan: i.keterangan || ''
        }));
        this.openReturnModal = true;
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
        ['label' => 'Pembangunan Proyek', 'url' => route('produksi.pembangunanProyek.index')],
        ['label' => $data->nama, 'url' => '']
    ]])



    <!-- Header Info -->
    <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 mb-6 flex flex-wrap gap-6 justify-between items-center">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">{{ $data->nama }}</h2>
            <div class="text-sm text-gray-500 dark:text-gray-400 space-y-1">
                <p>Pengawas: <span class="font-medium text-gray-900 dark:text-white">{{ $data->pengawas->nama_lengkap ?? '-' }}</span></p>
                <p>Tanggal: <span class="font-medium text-gray-900 dark:text-white">{{ $data->tanggal_mulai ? \Carbon\Carbon::parse($data->tanggal_mulai)->format('d M Y') : '-' }} s/d {{ $data->tanggal_selesai ? \Carbon\Carbon::parse($data->tanggal_selesai)->format('d M Y') : '-' }}</span></p>
                <p>Status: <span class="font-bold uppercase
                    {{ $data->status_pembangunan == 'proses' ? 'text-blue-600' : 'text-green-600' }}">{{ $data->status_pembangunan }}</span></p>
            </div>
        </div>
        <div class="flex flex-col gap-4 items-end">
            @if ($data->status_pembangunan !== 'selesai')
            <form action="{{ route('produksi.pembangunanProyek.update', $data->id) }}" method="POST"
                @submit.prevent="
                    Swal.fire({
                        title: 'Konfirmasi',
                        text: 'Apakah Anda yakin ingin menyelesaikan pembangunan proyek ini?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#059669',
                        confirmButtonText: 'Ya, Selesaikan',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $el.submit();
                        }
                    })
                ">
                @csrf
                @method('PUT')
                <input type="hidden" name="status_pembangunan" value="selesai">
                <button type="submit" class="inline-flex items-center gap-1.5 bg-green-600 text-white text-sm px-4 py-2.5 rounded-lg hover:bg-green-700 font-bold shadow-sm transition active:scale-95">
                    <i class="fa-solid fa-circle-check"></i> Selesaikan
                </button>
            </form>
            @endif
            <div class="flex flex-wrap gap-2 justify-end">
                <a href="{{ route('produksi.pembangunanProyek.laporanTermin.export', $data->id) }}" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-sm transition-all duration-200">
                    <i class="fa-solid fa-file-excel"></i> Export Excel
                </a>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center text-gray-500 dark:text-gray-400">
            <li class="me-2">
                <button @click="tab = 'order'" :class="tab === 'order' ? 'text-blue-600 border-blue-600 dark:text-blue-500 dark:border-blue-500' : 'border-transparent hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300'" class="inline-flex items-center justify-center p-4 border-b-2 rounded-t-lg group">
                    <i class="fa-solid fa-box mr-2"></i> Order Barang
                </button>
            </li>
            <li class="me-2">
                <button @click="tab = 'upah'" :class="tab === 'upah' ? 'text-blue-600 border-blue-600 dark:text-blue-500 dark:border-blue-500' : 'border-transparent hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300'" class="inline-flex items-center justify-center p-4 border-b-2 rounded-t-lg group">
                    <i class="fa-solid fa-money-bill-wave mr-2"></i> Pengajuan Upah
                </button>
            </li>
        </ul>
    </div>

    <!-- Tab Content: Order Barang -->
    <div x-show="tab === 'order'" style="display: none;">
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

            @if ($data->status_pembangunan !== 'selesai')
            <!-- Kiri: Form Order Baru -->
            <div class="xl:col-span-1 rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h3 class="font-bold text-gray-900 dark:text-white mb-4">Buat Order Barang</h3>
                <form action="{{ route('produksi.pembangunanProyek.orderStore') }}" method="POST">
                    @csrf
                    <input type="hidden" name="pembangunan_proyek_id" value="{{ $data->id }}">

                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Catatan Tambahan</label>
                                <textarea name="catatan" rows="2" class="bg-white block w-full rounded-lg border border-gray-300 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
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

                        <button type="submit" x-show="itemsAdditional.length > 0" class="w-full rounded-lg bg-blue-700 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 mt-4">Kirim Order</button>
                    </div>
                </form>
            </div>
            @else
            <div class="xl:col-span-1 rounded-lg border border-gray-200 bg-gray-50 dark:bg-gray-800/50 p-5 shadow-sm dark:border-gray-700 text-center flex flex-col items-center justify-center min-h-[300px]">
                <i class="fa-solid fa-circle-check text-4xl text-green-500 mb-3"></i>
                <h3 class="font-bold text-gray-900 dark:text-white mb-1">Pembangunan Selesai</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Proyek ini sudah berstatus selesai. Tidak dapat membuat order barang lagi.</p>
            </div>
            @endif

            <!-- Kanan: Riwayat Order -->
            <div class="xl:col-span-1 rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 flex flex-col h-full">
                <h3 class="font-bold text-gray-900 dark:text-white mb-4 flex-none">Riwayat Order</h3>
                <div class="relative flex-1 min-h-[300px]">
                    <div class="absolute inset-0 overflow-y-auto pr-2 custom-scrollbar space-y-4">
                        @if($data->orders && $data->orders->count() > 0)
                        @foreach($data->orders->sortByDesc('created_at') as $order)
                        <div x-data="{ open: false }" class="border border-gray-100 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm bg-white dark:bg-transparent">
                            <div @click="open = !open" class="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors cursor-pointer border-b border-gray-100 dark:border-gray-800">
                                <div class="flex flex-col gap-1 w-1/2">
                                    <p class="text-[9px] text-gray-400 font-medium uppercase tracking-wider">
                                        {{ \Carbon\Carbon::parse($order->tanggal_diajukan)->translatedFormat('d M Y, H:i') }}
                                    </p>
                                    <div class="flex items-center gap-2">
                                        <p class="text-xs font-bold text-gray-700 dark:text-gray-200">
                                            ORDER-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                                        </p>
                                        @if($order->returns && $order->returns->count() > 0)
                                            <span class="bg-orange-100 text-orange-600 text-[8px] font-black px-1.5 py-0.5 rounded uppercase tracking-tighter">Ada Retur</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center justify-end gap-4 w-1/2">
                                    <div class="flex flex-col items-center gap-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider {{ $order->jenis_order === 'stock' ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-amber-50 text-amber-600 border-amber-100' }}">
                                            {{ $order->jenis_order }}
                                        </span>
                                        <span class="text-[9px] text-gray-400 font-medium">{{ $order->details->count() }} Item</span>
                                    </div>
                                    <div class="flex flex-col items-center gap-1 w-20">
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
                                        <span class="inline-flex items-center px-2 py-1 rounded text-[8px] font-black uppercase border {{ $style }}">
                                            {{ str_replace('_', ' ', $order->status_order) }}
                                        </span>
                                    </div>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-gray-400 transition-transform duration-300" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>

                            <div x-show="open" x-collapse x-cloak class="bg-gray-50/50 dark:bg-gray-900/40 p-4 border-t border-gray-100 dark:border-gray-800">
                                <h5 class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2">Detail Item Barang</h5>
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

                                @if ($order->status_order == 'selesai' || $order->status_order == 'pengembalian')
                                    @php
                                        $lastReturn = $order->returns->last();
                                        $returnItemsForModal = $order->details->map(function ($d) use ($lastReturn) {
                                            $rd = $lastReturn ? $lastReturn->details->where('order_detail_id', $d->id)->first() : null;
                                            return [
                                                'id'         => $d->id,
                                                'nama'       => $d->nama_barang,
                                                'jumlah'     => $d->jumlah_input,
                                                'satuan'     => $d->satuanModel->nama_satuan ?? $d->satuan,
                                                'retur'      => $rd ? (float) $rd->jumlah_return : 0,
                                                'keterangan' => $rd ? $rd->keterangan_return : '',
                                            ];
                                        });
                                    @endphp
                                    @if ($data->status_pembangunan !== 'selesai')
                                    <div class="pt-4">
                                        <button type="button"
                                            @click="prepareReturn({{ $order->id }}, {{ $returnItemsForModal->toJson() }})"
                                            class="w-full py-2.5 text-[10px] font-black bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 rounded-xl uppercase border border-gray-200 dark:border-gray-700 hover:bg-orange-50 hover:text-orange-600 hover:border-orange-200 transition-all duration-300 flex items-center justify-center gap-2 shadow-sm">
                                            <i class="fa-solid fa-rotate-left"></i>
                                            {{ $order->status_order == 'pengembalian' ? 'Tambah / Perbarui Retur' : 'Ajukan Pengembalian' }}
                                        </button>
                                    </div>
                                    @endif
                                @endif

                                @if ($order->status_order == 'diproses')
                                    <div class="pt-4">
                                        <button type="button"
                                            @click="openCancelOrderModal = true; cancelOrderActionUrl = '{{ route('produksi.pembangunanProyek.orderDestroy', $order->id) }}'"
                                            class="w-full py-2.5 text-[10px] font-black bg-red-50 hover:bg-red-100 dark:bg-red-950/20 text-red-600 rounded-xl uppercase border border-red-200 dark:border-red-800/50 transition-all duration-300 flex items-center justify-center gap-2 shadow-sm">
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

    <!-- Tab Content: Pengajuan Upah -->
    <div x-show="tab === 'upah'" style="display: none;">
        <div class="grid grid-cols-1 xl:grid-cols-10 gap-6">

            @if ($data->status_pembangunan !== 'selesai')
            <!-- Kiri: Form Pengajuan Upah -->
            <div class="xl:col-span-3 rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h3 class="font-bold text-gray-900 dark:text-white mb-4">Buat Pengajuan Upah</h3>
                <form action="{{ route('produksi.pembangunanProyek.upahStore') }}" method="POST">
                    @csrf
                    <input type="hidden" name="pembangunan_proyek_id" value="{{ $data->id }}">

                    <div class="space-y-4">
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
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Catatan Pengawas</label>
                            <textarea name="catatan_pengawas" rows="2" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                        </div>
                        <button type="submit" class="w-full rounded-lg bg-green-600 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-4 focus:ring-green-300 dark:bg-green-500 dark:hover:bg-green-600 dark:focus:ring-green-800 mt-4">Kirim Pengajuan</button>
                    </div>
                </form>
            </div>
            @else
            <div class="xl:col-span-3 rounded-lg border border-gray-200 bg-gray-50 dark:bg-gray-800/50 p-5 shadow-sm dark:border-gray-700 text-center flex flex-col items-center justify-center min-h-[250px]">
                <i class="fa-solid fa-circle-check text-4xl text-green-500 mb-3"></i>
                <h3 class="font-bold text-gray-900 dark:text-white mb-1">Pembangunan Selesai</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Proyek ini sudah berstatus selesai. Tidak dapat mengajukan upah lagi.</p>
            </div>
            @endif

            <!-- Kanan: Riwayat Pengajuan Upah -->
            <div class="xl:col-span-7 rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 flex flex-col h-full">
                <h3 class="font-bold text-gray-900 dark:text-white mb-4 flex-none">Riwayat Pengajuan Upah</h3>
                <div class="relative flex-1 min-h-[300px]">
                    <div class="absolute inset-0 overflow-y-auto pr-2 custom-scrollbar space-y-4">
                        @if($data->pengajuanUpah && $data->pengajuanUpah->count() > 0)
                        @foreach($data->pengajuanUpah->sortByDesc('created_at') as $u)
                        <div x-data="{ open: false }" class="border border-gray-100 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm bg-white dark:bg-transparent">
                            <div @click="open = !open" class="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors cursor-pointer border-b border-gray-100 dark:border-gray-800">
                                <div class="flex flex-col gap-1 w-1/2">
                                    <p class="text-[9px] text-gray-400 font-medium uppercase tracking-wider">
                                        {{ \Carbon\Carbon::parse($u->tanggal_diajukan)->translatedFormat('d M Y, H:i') }}
                                    </p>
                                    <p class="text-xs font-bold text-gray-700 dark:text-gray-200">
                                        {{ $u->nama_upah }}
                                    </p>
                                </div>
                                <div class="flex items-center justify-end gap-4 w-1/2">
                                    <div class="text-right">
                                        <p class="text-[10px] font-medium text-gray-500 uppercase tracking-wider mb-0.5">Nominal</p>
                                        <p class="text-xs font-black text-gray-800 dark:text-white font-mono">
                                            Rp {{ number_format($u->nominal_diajukan, 0, ',', '.') }}
                                        </p>
                                    </div>
                                    <div class="flex flex-col items-center gap-1 w-24">
                                        <span class="inline-flex items-center px-2 py-1 rounded text-[8px] font-black uppercase border {{ $u->status_style ?? 'bg-gray-50 text-gray-500 border-gray-100' }}">
                                            {{ $u->status_label ?? str_replace('_', ' ', $u->status_pengajuan) }}
                                        </span>
                                    </div>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-gray-400 transition-transform duration-300" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" />
                                    </svg>
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
                                     <div class="pt-4 border-t border-gray-100 dark:border-gray-800">
                                         <button type="button"
                                             @click="openCancelUpahModal = true; cancelUpahActionUrl = '{{ route('produksi.pembangunanProyek.upahDestroy', $u->id) }}'"
                                             class="w-full py-2.5 text-[10px] font-black bg-red-50 hover:bg-red-100 dark:bg-red-950/20 text-red-600 rounded-xl uppercase border border-red-200 dark:border-red-800/50 transition-all duration-300 flex items-center justify-center gap-2 shadow-sm">
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

    <!-- Modal Return Barang -->
    <template x-teleport="body">
        <div x-show="openReturnModal"
            class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-[2px]" x-cloak>
            <div @click.away="openReturnModal = false"
                class="bg-white dark:bg-gray-900 rounded-2xl max-w-3xl w-full shadow-2xl border border-gray-100 dark:border-gray-800 overflow-hidden flex flex-col max-h-[90vh]">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center bg-gray-50/50 dark:bg-white/5">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white">Ajukan Retur Barang</h3>
                        <p class="text-xs text-gray-400 uppercase tracking-widest mt-1">Order ID: #<span x-text="returnOrderId"></span></p>
                    </div>
                    <button @click="openReturnModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>
                <form action="{{ route('produksi.pembangunanProyek.returnStore') }}" method="POST" class="flex-1 flex flex-col overflow-hidden">
                    @csrf
                    <input type="hidden" name="order_id" :value="returnOrderId">
                    <div class="p-6 overflow-y-auto custom-scrollbar flex-1 space-y-5">
                        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800 p-4 rounded-xl flex gap-3">
                            <i class="fa-solid fa-circle-info text-amber-500 mt-0.5 text-lg"></i>
                            <p class="text-sm text-amber-700 dark:text-amber-400 leading-relaxed">
                                Masukkan jumlah barang yang <strong>rusak atau ingin dikembalikan</strong>. Pastikan jumlah tidak melebihi total barang yang diterima.
                            </p>
                        </div>
                        <div class="space-y-4">
                            <template x-for="(item, index) in returnItems" :key="index">
                                <div class="p-5 border border-gray-100 dark:border-gray-800 rounded-xl bg-gray-50/30 dark:bg-white/[0.02] space-y-4">
                                    <div class="flex items-center justify-between gap-4">
                                        <div class="flex-1">
                                            <p class="text-base font-bold text-gray-700 dark:text-gray-200" x-text="item.nama"></p>
                                            <p class="text-xs text-gray-500 mt-1.5">
                                                Diterima: <span class="font-mono font-medium text-gray-700 dark:text-gray-300" x-text="parseFloat(item.jumlah)"></span>
                                                <span x-text="item.satuan"></span>
                                            </p>
                                        </div>
                                        <div class="w-40">
                                            <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase mb-1.5">Jumlah Retur</label>
                                            <div class="relative group">
                                                <input type="hidden" :name="'returns[' + index + '][order_detail_id]'" :value="item.id">
                                                <input type="number" step="any" :name="'returns[' + index + '][jumlah_return]'" x-model.number="item.retur" :max="item.jumlah" min="0"
                                                    :class="item.retur > 0 ? 'border-orange-400 dark:border-orange-500/50 text-orange-600 bg-orange-50/30' : 'border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800'"
                                                    class="w-full p-2.5 pr-12 text-sm font-mono font-bold border rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-gray-400 group-hover:text-gray-500" x-text="item.satuan"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div x-show="item.retur > 0" x-transition>
                                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1.5">Alasan / Detail Kerusakan</label>
                                        <textarea :name="'returns[' + index + '][keterangan]'" x-model="item.keterangan" rows="2"
                                            class="w-full p-3 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 focus:ring-2 focus:ring-red-500/10 outline-none placeholder:text-gray-400"
                                            placeholder="Contoh: Keramik pecah di pojok, Semen membatu, dll..."></textarea>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div class="p-6 bg-gray-50 dark:bg-white/5 border-t border-gray-100 dark:border-gray-800 flex justify-end gap-3">
                        <button type="button" @click="openReturnModal = false" class="px-6 py-2.5 text-sm font-bold text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-white transition-colors uppercase">Batal</button>
                        <button type="submit" class="px-6 py-2.5 text-sm font-bold text-white bg-red-600 rounded-lg hover:bg-red-700 uppercase">
                            Kirim Pengajuan Retur
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- Modal Konfirmasi Batal Order -->
    <template x-teleport="body">
        <div x-show="openCancelOrderModal"
            class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/50 dark:bg-black/80 backdrop-blur-sm"
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
            class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/50 dark:bg-black/80 backdrop-blur-sm"
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
    </template>
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
