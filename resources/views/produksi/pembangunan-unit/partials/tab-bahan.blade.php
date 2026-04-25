<div x-show="tab === 'bahan'" class="space-y-4">

    {{-- Tabel Order Bahan --}}
    @php
        $orders = \App\Models\PembangunanUnitBarangOrder::with('details.barang')
            ->where('pembangunan_unit_qc_id', $qc->id)
            ->latest()
            ->get();
    @endphp

    {{-- Header --}}
    <div class="flex justify-between items-center px-1">
        <div class="flex items-center gap-3">
            <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Riwayat Order Bahan</h4>
            <span class="bg-blue-100 text-blue-600 text-[9px] font-bold px-2 py-0.5 rounded-full">
                {{ $orders->count() }} Total
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
                                <div class="flex items-center gap-2">
                                    <p class="text-xs font-bold text-gray-700 dark:text-gray-200">
                                        REQ-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</p>
                                    {{-- Badge jika ada retur di salah satu item --}}
                                    @if ($order->details->where('jumlah_return', '>', 0)->count() > 0)
                                        <span
                                            class="bg-orange-100 text-orange-600 text-[8px] font-black px-1.5 py-0.5 rounded uppercase tracking-tighter">Ada
                                            Retur</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-4 text-xs text-center">
                                <div class="flex items-center flex-col gap-1">
                                    <span
                                        class="inline-flex items-center w-fit px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider {{ $order->jenis_order === 'stock' ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-amber-50 text-amber-600 border-amber-100' }}">
                                        {{ $order->jenis_order }}
                                    </span>
                                    <span class="text-[10px] text-gray-400 font-medium">{{ $order->details->count() }}
                                        Item</span>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-center">
                                @php
                                    $statusMap = [
                                        'diproses' => 'bg-blue-50 text-blue-600 border-blue-100',
                                        'selesai' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                        'ditolak' => 'bg-red-50 text-red-600 border-red-100',
                                        'return_pending' => 'bg-orange-50 text-orange-600 border-orange-100',
                                    ];
                                    $style =
                                        $statusMap[$order->status_order] ?? 'bg-gray-50 text-gray-500 border-gray-100';
                                @endphp
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded text-[8px] font-black uppercase border {{ $style }}">
                                    {{ str_replace('_', ' ', $order->status_order) }}
                                </span>
                            </td>
                        </tr>

                        {{-- Accordion Detail --}}
                        <tr x-show="open" x-cloak>
                            <td colspan="4" class="p-0 border-none bg-gray-50/50 dark:bg-gray-900/40">
                                <div x-show="open" x-collapse
                                    class="px-10 py-6 border-t border-gray-100 dark:border-gray-800">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                        {{-- Daftar Barang --}}
                                        <div class="space-y-4">
                                            <h5
                                                class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2">
                                                Detail Item Barang</h5>
                                            <div
                                                class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                                                <div class="max-h-[350px] overflow-y-auto custom-scrollbar">
                                                    <table class="w-full text-left border-collapse">
                                                        <thead
                                                            class="bg-gray-50/80 dark:bg-gray-700/50 sticky top-0 z-10 backdrop-blur-sm">
                                                            <tr>
                                                                <th
                                                                    class="px-3 py-2 text-[9px] font-bold text-gray-400 uppercase border-b border-gray-100 dark:border-gray-700">
                                                                    Nama Barang</th>
                                                                <th
                                                                    class="px-3 py-2 text-[9px] font-bold text-gray-400 uppercase text-right border-b border-gray-100 dark:border-gray-700">
                                                                    Status Jumlah</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                                            @foreach ($order->details as $det)
                                                                @php
                                                                    // 1. Ambil jumlah base dari pengajuan (sudah hasil kali jumlah_input * faktor)
                                                                    $baseOrder = (float) $det->jumlah_base;

                                                                    // 2. Hitung jumlah base dari RAP asli (jumlah_standar * faktor_konversi RAP)
                                                                    $standarRap =
                                                                        (float) ($det->rapBahan->jumlah_standar ?? 0);
                                                                    $faktorRap =
                                                                        (float) ($det->rapBahan->faktor_konversi ?? 1);
                                                                    $baseRap = $standarRap * $faktorRap;

                                                                    // 3. Bandingkan base vs base (misal: 112 Pcs vs 112 Pcs)
                                                                    // Kita beri toleransi sedikit (epsilon) untuk menghindari isu floating point
                                                                    $isOver = $baseOrder - $baseRap > 0.001;

                                                                    $isRap = (bool) $det->rapBahan;
                                                                    $isReturned = $det->jumlah_return > 0;
                                                                @endphp

                                                                <tr
                                                                    class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-colors">
                                                                    <td class="px-3 py-3">
                                                                        <p
                                                                            class="text-[11px] font-bold text-gray-700 dark:text-gray-200 leading-tight">
                                                                            {{ $det->nama_barang ?? '-' }}
                                                                        </p>
                                                                        {{-- Badge Retur & Alasan --}}
                                                                        @if ($isReturned)
                                                                            <div
                                                                                class="mt-2 p-2 bg-orange-50/50 dark:bg-orange-900/10 border border-orange-100 dark:border-orange-900/30 rounded-lg">
                                                                                <p
                                                                                    class="text-[10px] text-orange-700 dark:text-orange-400 font-bold flex items-center gap-1">
                                                                                    <i
                                                                                        class="fa-solid fa-triangle-exclamation"></i>
                                                                                    Retur:
                                                                                    {{ (float) $det->jumlah_return }}
                                                                                    {{ $det->satuan }}
                                                                                </p>
                                                                            </div>
                                                                        @endif
                                                                        @if ($det->alasan_permintaan_tidak_sesuai_rap)
                                                                            <p
                                                                                class="text-[9px] text-red-500 italic mt-1">
                                                                                Ket:
                                                                                {{ $det->alasan_permintaan_tidak_sesuai_rap }}
                                                                            </p>
                                                                        @endif
                                                                    </td>
                                                                    <td class="px-3 py-3 text-right align-top">
                                                                        <div class="flex flex-col items-end">
                                                                            <p
                                                                                class="text-[11px] font-black {{ $isOver ? 'text-red-600' : 'text-gray-800 dark:text-white' }}">
                                                                                {{ (float) $det->jumlah_input }} <span
                                                                                    class="text-[9px] font-medium text-gray-400">{{ $det->satuan }}</span>
                                                                            </p>

                                                                            <div
                                                                                class="flex flex-wrap justify-end gap-1 mt-1">
                                                                                @if ($isOver && $det->rap_bahan_id != null)
                                                                                    <span
                                                                                        class="text-[7px] font-black text-red-500 uppercase bg-red-50 px-1 rounded border border-red-100">Melebihi
                                                                                        RAP</span>
                                                                                @endif
                                                                                @if (!$isRap)
                                                                                    <span
                                                                                        class="text-[7px] font-black text-amber-500 uppercase bg-amber-50 px-1 rounded border border-amber-100">Luar
                                                                                        RAP</span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
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
                                                        "{{ $order->catatan ?? 'Tidak ada catatan permintaan.' }}"</p>
                                                </div>
                                            </div>

                                            @if ($order->status_order == 'selesai' || $order->status_order == 'return_pending')
                                                <div class="pt-2">
                                                    <button type="button"
                                                        @click="prepareReturn({{ $order->id }}, {{ $order->details->map(fn($d) => ['id' => $d->id, 'nama' => $d->nama_barang, 'jumlah' => $d->jumlah_input, 'satuan' => $d->satuan, 'retur' => (float) $d->jumlah_return, 'keterangan' => $d->keterangan_return])->toJson() }})"
                                                        class="w-full py-2.5 text-[10px] font-black bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 rounded-xl uppercase border border-gray-200 dark:border-gray-700 hover:bg-orange-50 hover:text-orange-600 hover:border-orange-200 transition-all duration-300 flex items-center justify-center gap-2 shadow-sm">
                                                        <i class="fa-solid fa-rotate-left"></i>
                                                        {{ $order->status_order == 'return_pending' ? 'Perbarui Data Retur' : 'Ajukan Pengembalian' }}
                                                    </button>
                                                </div>
                                            @endif
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
        <div
            class="py-16 flex flex-col items-center justify-center border-2 border-dashed border-gray-100 dark:border-gray-800 rounded-3xl bg-gray-50/30">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gray-300 mb-4" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <h5 class="text-xs font-bold text-gray-600 dark:text-gray-300">Belum Ada Riwayat Order</h5>
        </div>
    @endif
</div>
