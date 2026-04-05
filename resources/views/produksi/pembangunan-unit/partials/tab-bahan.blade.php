<div x-show="tab === 'bahan'" class="space-y-4">
    {{-- Header --}}
    <div class="flex justify-between items-center px-1">
        <div class="flex items-center gap-3">
            <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Riwayat Order Bahan</h4>
            <span class="bg-blue-100 text-blue-600 text-[9px] font-bold px-2 py-0.5 rounded-full">
                {{ \App\Models\PembangunanUnitBarangOrder::where('pembangunan_unit_qc_id', $qc->id)->count() }} Total
            </span>
        </div>

        @if ($qc->pembangunanUnitRapBahan->count() > 0)
            <button @click="prepareOrder({{ json_encode($qc->pembangunanUnitRapBahan) }}, {{ $qc->id }})"
                class="px-4 py-2 bg-blue-600 text-white text-[10px] font-bold rounded-lg hover:bg-blue-700 shadow-sm transition-all uppercase flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                Order Barang
            </button>
        @endif
    </div>

    {{-- Tabel Order Bahan --}}
    @php
        $orders = \App\Models\PembangunanUnitBarangOrder::with('details.barang')
            ->where('pembangunan_unit_qc_id', $qc->id)
            ->latest()
            ->get();
    @endphp

    @if ($orders->count() > 0)
        <div
            class="overflow-hidden border border-gray-100 dark:border-gray-800 rounded-xl shadow-sm bg-white dark:bg-transparent">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="w-10 px-4 py-3"></th>
                        <th class="px-4 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider">ID Request
                        </th>
                        <th class="px-4 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider text-center">
                            Item</th>
                        <th
                            class="w-40 px-4 py-3 text-[10px] font-bold text-gray-500 uppercase text-center tracking-wider">
                            Status</th>
                    </tr>
                </thead>

                @foreach ($orders as $order)
                    <tbody x-data="{ open: false }" class="border-t border-gray-100 dark:border-gray-800">
                        {{-- Row Utama --}}
                        <tr @click="open = !open"
                            class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors cursor-pointer">
                            <td class="px-4 py-4 text-center">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="w-3 h-3 text-gray-400 transition-transform duration-300 mx-auto"
                                    :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </td>
                            <td class="px-4 py-4">
                                <p class="text-[9px] text-gray-400 font-medium uppercase tracking-wider mb-0.5">
                                    {{ \Carbon\Carbon::parse($order->tanggal_diajukan)->translatedFormat('d M Y') }}
                                </p>
                                <p class="text-xs font-bold text-gray-700 dark:text-gray-200">
                                    REQ-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                                </p>
                            </td>
                            <td class="px-4 py-4 text-xs text-center">
                                <div class="flex items-center flex-col gap-1">
                                    @if ($order->jenis_order === 'stock')
                                        <span
                                            class="inline-flex items-center w-fit px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-blue-50 text-blue-600 border border-blue-100 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800">
                                            Stock
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center w-fit px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-amber-50 text-amber-600 border border-amber-100 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800">
                                            Direct
                                        </span>
                                    @endif

                                    <span class="text-[10px] text-gray-400 font-medium">
                                        {{ $order->details->count() }} Item
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-center">
                                @php
                                    $statusMap = [
                                        'menunggu' => 'bg-amber-50 text-amber-600 border-amber-100',
                                        'diproses' => 'bg-blue-50 text-blue-600 border-blue-100',
                                        'selesai' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                        'ditolak' => 'bg-red-50 text-red-600 border-red-100',
                                    ];
                                    $style =
                                        $statusMap[$order->status_order] ?? 'bg-gray-50 text-gray-500 border-gray-100';
                                @endphp
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded text-[8px] font-black uppercase border {{ $style }}">
                                    {{ $order->status_order }}
                                </span>
                            </td>
                        </tr>

                        {{-- Accordion Detail --}}
                        <tr x-show="open" x-cloak>
                            <td colspan="4" class="p-0 border-none bg-gray-50/50 dark:bg-gray-900/40">
                                <div x-show="open" x-collapse
                                    class="px-10 py-6 border-t border-gray-100 dark:border-gray-800">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                                        {{-- Daftar Barang yang di-order --}}
                                        <div class="space-y-4">
                                            <div class="flex justify-between items-end mb-2">
                                                <h5
                                                    class="text-[9px] font-black text-gray-400 uppercase tracking-widest">
                                                    Detail Item Barang</h5>
                                                <span
                                                    class="text-[9px] font-bold text-blue-500 bg-blue-50 px-2 py-0.5 rounded-full">
                                                    {{ $order->details->count() }} Items
                                                </span>
                                            </div>

                                            {{-- Container dengan Scroll Overflow --}}
                                            <div
                                                class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                                                <div class="max-h-[280px] overflow-y-auto custom-scrollbar">
                                                    <table class="w-full text-left border-collapse">
                                                        <thead
                                                            class="bg-gray-50/80 dark:bg-gray-700/50 sticky top-0 z-10 backdrop-blur-sm">
                                                            <tr>
                                                                <th
                                                                    class="px-3 py-2 text-[9px] font-bold text-gray-400 uppercase border-b border-gray-100 dark:border-gray-700">
                                                                    Nama Barang</th>
                                                                <th
                                                                    class="px-3 py-2 text-[9px] font-bold text-gray-400 uppercase text-right border-b border-gray-100 dark:border-gray-700">
                                                                    Jumlah</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                                            @foreach ($order->details as $det)
                                                                @php
                                                                    $isOver =
                                                                        $det->jumlah_input >
                                                                        ($det->rapBahan->jumlah_standar ?? 0);

                                                                    $isRap = $det->rapBahan ? true : false;
                                                                @endphp
                                                                <tr
                                                                    class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-colors">
                                                                    <td class="px-3 py-2.5">
                                                                        <p
                                                                            class="text-[11px] font-bold text-gray-700 dark:text-gray-200 leading-tight">
                                                                            {{ $det->nama_barang ?? '-' }}
                                                                        </p>
                                                                        @if ($det->alasan_permintaan_tidak_sesuai_rap)
                                                                            <p
                                                                                class="text-[9px] text-red-500 italic mt-0.5 flex items-center gap-1">
                                                                                <span
                                                                                    class="w-1 h-1 rounded-full bg-red-400"></span>
                                                                                Ket:
                                                                                {{ $det->alasan_permintaan_tidak_sesuai_rap }}
                                                                            </p>
                                                                        @endif
                                                                    </td>
                                                                    <td class="px-3 py-2.5 text-right vertical-top">
                                                                        <p
                                                                            class="text-[11px] font-black {{ $isOver ? 'text-red-600' : 'text-gray-800 dark:text-white' }}">
                                                                            {{ str_replace('.', ',', (float) $det->jumlah_input) }}
                                                                            <span
                                                                                class="text-[9px] font-medium text-gray-400 ms-0.5">{{ $det->satuan ?? '-' }}</span>
                                                                        </p>
                                                                        @if ($isOver)
                                                                            <span
                                                                                class="text-[7px] font-black text-red-500 uppercase bg-red-50 px-1 rounded-[4px] border border-red-100">Melebihi
                                                                                RAP</span>
                                                                        @endif
                                                                        @if (!$isRap)
                                                                            <span
                                                                                class="text-[7px] font-black text-red-500 uppercase bg-red-50 px-1 rounded-[4px] border border-red-100">
                                                                                Diluar RAP</span>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            {{-- Info Footer Kecil --}}
                                            @if ($order->details->count() > 5)
                                                <p
                                                    class="text-[8px] text-gray-400 italic text-center uppercase tracking-tighter">
                                                    Scroll ke bawah untuk melihat item lainnya
                                                </p>
                                            @endif
                                        </div>
                                        {{-- Catatan & Aksi --}}
                                        <div class="space-y-4">
                                            <div>
                                                <h5
                                                    class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2">
                                                    Catatan Permintaan</h5>
                                                <div
                                                    class="p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700 shadow-sm min-h-[60px]">
                                                    <p
                                                        class="text-xs text-gray-600 dark:text-gray-400 italic leading-relaxed">
                                                        "{{ $order->catatan ?? 'Tidak ada catatan permintaan.' }}"
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="pt-2">
                                                <button
                                                    class="w-full py-2 text-[9px] font-black bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 rounded-lg uppercase border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-red-500 transition-all duration-300 flex items-center justify-center gap-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2.5" d="M16 15L12 19L8 15M12 19V5M4 4h16" />
                                                    </svg>
                                                    Ajukan Return (Jika Selesai)
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                @endforeach
            </table>
        </div>
    @else
        {{-- Empty State --}}
        <div
            class="py-16 flex flex-col items-center justify-center border-2 border-dashed border-gray-100 dark:border-gray-800 rounded-3xl bg-gray-50/30">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gray-300 mb-4" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <h5 class="text-xs font-bold text-gray-600 dark:text-gray-300">Belum Ada Riwayat Order</h5>
            <p class="text-[10px] text-gray-400 mt-1 text-center">Data order bahan bangunan belum tersedia untuk QC
                ini.
            </p>
        </div>
    @endif
</div>
