<div x-show="tab === 'bahan'" class="space-y-4">
    <div class="flex justify-between items-center px-1">
        <h4 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em]">Daftar Bahan RAP</h4>
        @if ($qc->pembangunanUnitRapBahan->count() > 0)
            <button @click="prepareOrder({{ json_encode($qc->pembangunanUnitRapBahan) }}, {{ $qc->id }})"
                class="px-4 py-2 bg-blue-600 text-white text-[10px] font-bold rounded-lg hover:bg-blue-700 shadow-sm transition-all uppercase">
                Order Barang
            </button>
        @endif
    </div>

    <div
        class="overflow-hidden border border-gray-100 dark:border-gray-800 rounded-xl bg-white dark:bg-white/5 shadow-sm">
        <div
            class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-gray-100 dark:divide-gray-800">
            @php $chunksRap = $qc->pembangunanUnitRapBahan->chunk(ceil($qc->pembangunanUnitRapBahan->count() / 2)); @endphp
            @foreach ($chunksRap as $index => $chunk)
                <div class="w-full">
                    <table class="w-full text-left border-collapse">
                        <thead
                            class="bg-gray-50 dark:bg-gray-800/50 {{ $index > 0 ? 'hidden md:table-header-group' : '' }}">
                            <tr>
                                <th class="px-4 py-2 text-[10px] font-bold text-gray-500 uppercase">Nama Barang</th>
                                <th class="px-4 py-2 text-[10px] font-bold text-gray-500 uppercase text-right">RAP
                                </th>
                                <th class="px-4 py-2 text-[10px] font-bold text-gray-500 uppercase text-right">Real
                                </th>
                                <th class="px-4 py-2 text-[10px] font-bold text-gray-500 uppercase text-right">
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800/50">
                            @foreach ($chunk as $bahan)
                                <tr class="hover:bg-gray-50/50 text-xs">
                                    <td class="px-4 py-2.5 font-bold text-gray-700 dark:text-gray-200">
                                        {{ $bahan->nama_barang }}</td>
                                    <td class="px-4 py-2.5 text-right font-mono text-gray-600 dark:text-gray-400">
                                        {{ str_replace('.', ',', (float) $bahan->jumlah_standar) }} <span
                                            class="text-[10px]">{{ $bahan->satuan }}</span>
                                    </td>
                                    <td class="px-4 py-2.5 text-right font-mono text-gray-600 dark:text-gray-400">
                                        0 <span class="text-[10px]">{{ $bahan->satuan }}</span>
                                    </td>
                                    <td class="px-4 py-2.5 text-right font-mono text-gray-600 dark:text-gray-400">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
    </div>

    @php
        $orders = \App\Models\PembangunanUnitBarangOrder::with('details.barang')
            ->where('pembangunan_unit_qc_id', $qc->id)
            ->latest()
            ->get();
    @endphp

    @if ($orders->count() > 0)
        <div class="mt-10 space-y-5">
            <div class="flex items-center gap-3 px-1">
                <h4 class="text-xs font-bold text-gray-700 uppercase tracking-widest">Riwayat Permintaan</h4>
                <div class="h-[1px] flex-1 bg-gray-100 dark:bg-gray-800"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($orders as $order)
                    <div
                        class="group bg-white dark:bg-white/[0.02] border border-gray-200 dark:border-gray-800 rounded-2xl p-4 shadow-sm hover:shadow-md transition-all duration-300">

                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-xl bg-gray-50 dark:bg-gray-800 flex items-center justify-center text-gray-400 group-hover:bg-blue-50 group-hover:text-blue-600 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                    </svg>
                                </div>
                                <div>
                                    <p
                                        class="text-xs font-black text-gray-800 dark:text-white uppercase tracking-tight">
                                        REQ-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</p>
                                    <p class="text-[10px] text-gray-500 font-medium">
                                        {{ \Carbon\Carbon::parse($order->tanggal_diajukan)->translatedFormat('d M Y') }}
                                        • {{ \Carbon\Carbon::parse($order->tanggal_diajukan)->format('H:i') }}</p>
                                </div>
                            </div>
                            @php
                                $statusMap = [
                                    'menunggu' => 'bg-amber-50 text-amber-600 border-amber-100',
                                    'diproses' => 'bg-blue-50 text-blue-600 border-blue-100',
                                    'selesai' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                    'ditolak' => 'bg-red-50 text-red-600 border-red-100',
                                ];
                                $currentStyle =
                                    $statusMap[$order->status_order] ?? 'bg-gray-50 text-gray-500 border-gray-100';
                            @endphp
                            <span
                                class="px-2.5 py-1 text-[9px] font-black rounded-full uppercase border {{ $statusMap[$order->status_order] ?? '' }}">
                                {{ $order->status_order }}
                            </span>
                        </div>

                        <div
                            class="bg-gray-50/50 dark:bg-gray-900/40 rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden mb-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 divide-x divide-gray-100 dark:divide-gray-800">
                                @php
                                    $detailChunks = $order->details->chunk(ceil($order->details->count() / 2));
                                @endphp

                                @foreach ($detailChunks as $chunk)
                                    <table class="w-full">
                                        <tbody class="divide-y divide-gray-100/50 dark:divide-gray-800/50">
                                            @foreach ($chunk as $det)
                                                @php $isOver = (float)$det->jumlah_input > (float)($det->jumlah_base ?? 0); @endphp
                                                <tr class="text-[10px]">
                                                    <td class="px-3 py-2">
                                                        <p class="font-bold text-gray-700 dark:text-gray-300 leading-tight truncate max-w-[80px]"
                                                            title="{{ $det->nama_barang }}">{{ $det->nama_barang }}
                                                        </p>
                                                        @if ($det->alasan_permintaan_tidak_sesuai_rap)
                                                            <p class="text-[8px] text-red-500 italic mt-0.5">Note:
                                                                {{ $det->alasan_permintaan_tidak_sesuai_rap }}</p>
                                                        @endif
                                                    </td>
                                                    <td class="px-3 py-2 text-right">
                                                        <span
                                                            class="font-black {{ $isOver ? 'text-red-600' : 'text-gray-800 dark:text-white' }}">
                                                            {{ str_replace('.', ',', (float) $det->jumlah_input) }}
                                                            <span
                                                                class="text-[8px] font-medium text-gray-400">{{ $det->satuan }}</span>
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-2 text-right">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endforeach
                            </div>
                        </div>

                        @if ($order->catatan)
                            <div class="mb-4 px-3 py-2 bg-blue-50/30 border-s-2 border-blue-400 rounded-r-lg">
                                <p class="text-[10px] text-gray-600 italic leading-relaxed">"{{ $order->catatan }}"</p>
                            </div>
                        @endif

                        <div class="flex gap-2">
                            <button
                                class="flex-1 py-2 text-[9px] font-black bg-white dark:bg-gray-800 text-gray-500 rounded-lg uppercase border border-gray-200 dark:border-gray-700 hover:text-red-500 transition-all flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path d="M16 15L12 19L8 15M12 19V5" />
                                    <path d="M4 4h16" />
                                </svg>
                                Return
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
