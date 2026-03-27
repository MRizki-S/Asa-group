<div x-show="tab === 'bahan'" class="space-y-4">
    <div class="flex justify-end items-center px-1">
        {{-- Tombol hanya muncul jika ada data di pembangunanUnitRapBahan --}}
        @if ($qc->pembangunanUnitRapBahan->count() > 0)
            <button @click="prepareOrder({{ json_encode($qc->pembangunanUnitRapBahan) }}, {{ $qc->id }})"
                class="px-4 py-2 bg-blue-600 text-white text-[10px] font-bold rounded-lg hover:bg-blue-700 shadow-sm transition-all uppercase">
                Order Barang
            </button>
        @endif
    </div>

    {{-- Riwayat Permintaan  --}}
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

                        {{-- Header Card --}}
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-xl bg-gray-50 dark:bg-gray-800 flex items-center justify-center text-gray-400 group-hover:bg-blue-50 group-hover:text-blue-500 transition-colors">
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
                                        • {{ \Carbon\Carbon::parse($order->tanggal_diajukan)->format('H:i') }}
                                    </p>
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
                                class="px-2.5 py-1 text-[9px] font-black rounded-full uppercase border {{ $currentStyle }}">
                                {{ $order->status_order }}
                            </span>
                        </div>

                        {{-- List Items --}}
                        <div
                            class="bg-gray-50/50 dark:bg-gray-900/40 rounded-xl border border-gray-100 dark:border-gray-800 p-3 mb-3">
                            <table class="w-full">
                                <tbody class="divide-y divide-gray-100/50 dark:divide-gray-800/50">
                                    @foreach ($order->details as $det)
                                        @php
                                            $isOver = $det->jumlah_input > ($det->jumlah_base ?? 0);
                                        @endphp
                                        <tr>
                                            <td class="py-2">
                                                <p class="text-[11px] text-gray-600 dark:text-gray-400 leading-tight">
                                                    {{ $det->nama_barang ?? '-' }}</p>
                                                @if ($det->alasan_permintaan_tidak_sesuai_rap)
                                                    <p
                                                        class="text-[9px] text-red-500 italic mt-0.5 flex items-center gap-1">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-2.5 h-2.5"
                                                            viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd"
                                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                                                clip-rule="evenodd" />
                                                        </svg>
                                                        Note: {{ $det->alasan_permintaan_tidak_sesuai_rap }}
                                                    </p>
                                                @endif
                                            </td>
                                            <td class="py-2 text-right">
                                                <div class="flex flex-col items-end">
                                                    <span
                                                        class="text-[11px] font-black {{ $isOver ? 'text-red-600' : 'text-gray-800 dark:text-white' }}">
                                                        {{ str_replace('.', ',', (float) $det->jumlah_input) }}
                                                        <span
                                                            class="text-[9px] font-medium text-gray-400 ms-1">{{ $det->satuan ?? '-' }}</span>
                                                    </span>
                                                    @if ($isOver)
                                                        <span
                                                            class="text-[8px] font-bold text-red-500 uppercase tracking-tighter bg-red-50 px-1 rounded">Over
                                                            RAP</span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Global Note --}}
                        @if ($order->catatan)
                            <div class="mb-4 px-3 py-2 bg-blue-50/30 border-s-2 border-blue-400 rounded-e-lg">
                                <p class="text-[9px] font-bold text-blue-600 uppercase mb-0.5">Catatan Permintaan:</p>
                                <p class="text-[10px] text-gray-600 dark:text-gray-400 leading-relaxed">
                                    {{ $order->catatan }}</p>
                            </div>
                        @endif

                        {{-- Actions --}}
                        <div class="flex gap-2">
                            <button
                                class="flex-1 py-2 text-[9px] font-black bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 rounded-lg uppercase border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-red-500 transition-all duration-300 flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16 15L12 19L8 15M12 19V5" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4h16" />
                                </svg>
                                Return
                            </button>
                            {{-- <button
                                class="w-10 py-2 text-[9px] flex items-center justify-center bg-gray-50 dark:bg-gray-800 text-gray-400 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-blue-50 hover:text-blue-500 transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button> --}}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
