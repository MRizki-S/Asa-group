<div x-show="tab === 'upah'" class="space-y-4">
    {{-- Header & Tombol Trigger Modal --}}
    <div class="flex justify-between items-center px-1">
        <div class="flex items-center gap-3">
            <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Daftar Pengajuan Upah</h4>
            {{-- Badge jumlah pengajuan (Opsional) --}}
            <span class="bg-gray-100 text-gray-500 text-[9px] font-bold px-2 py-0.5 rounded-full">
                {{ \App\Models\PembangunanUnitUpahPengajuan::where('pembangunan_unit_qc_id', $qc->id)->count() }}
            </span>
        </div>

        {{-- Button ini akan memicu modal yang berisi list Master RAP Upah untuk dipilih --}}
        <button @click="prepareUpah({{ json_encode($qc->pembangunanUnitRapUpah) }}, {{ $qc->id }})"
            class="px-4 py-2 bg-blue-600 text-white text-[10px] font-bold rounded-lg hover:bg-blue-700 shadow-sm transition-all uppercase flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Ajukan Upah
        </button>
    </div>

    {{-- Daftar Pengajuan --}}
    @php
        $pengajuans = \App\Models\PembangunanUnitUpahPengajuan::where('pembangunan_unit_qc_id', $qc->id)
            ->latest()
            ->get();
    @endphp

    @if ($pengajuans->count() > 0)
        <div class="overflow-hidden border border-gray-100 dark:border-gray-800 rounded-xl shadow-sm">
            <table class="w-full text-left border-collapse bg-white dark:bg-white/5">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider">Tanggal &
                            Detail Pekerjaan</th>
                        <th class="px-4 py-3 text-[10px] font-bold text-gray-500 uppercase text-right tracking-wider">
                            Nominal</th>
                        <th class="px-4 py-3 text-[10px] font-bold text-gray-500 uppercase text-center tracking-wider">
                            Status Approval</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($pengajuans as $item)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-colors">
                            <td class="px-4 py-3">
                                <p class="text-[10px] text-gray-400 font-medium mb-0.5 uppercase">
                                    {{ \Carbon\Carbon::parse($item->tanggal_diajukan)->translatedFormat('d M Y') }}
                                </p>
                                <p class="text-xs font-bold text-gray-700 dark:text-gray-200">
                                    {{ $item->nama_upah }}
                                </p>

                                @if ($item->catatan_pengawas)
                                    <div
                                        class="mt-1.5 flex items-start gap-1.5 p-1.5 bg-gray-50 rounded border border-gray-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-2.5 h-2.5 text-gray-400 mt-0.5"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                        </svg>
                                        <p class="text-[9px] italic text-gray-500 leading-relaxed">
                                            {{ $item->catatan_pengawas }}
                                        </p>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right vertical-top">
                                <p class="text-xs font-black text-gray-800 dark:text-white font-mono">
                                    Rp {{ number_format($item->nominal_diajukan, 0, ',', '.') }}
                                </p>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $statusStyles = [
                                        'diajukan' => 'bg-amber-50 text-amber-600 border-amber-100',
                                        'disetujui_mgr_produksi' => 'bg-blue-50 text-blue-600 border-blue-100',
                                        'disetujui_mgr_dukungan' => 'bg-purple-50 text-purple-600 border-purple-100',
                                        'disetujui_akuntan' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                        'ditolak_mgr_produksi' => 'bg-red-50 text-red-600 border-red-100',
                                        'ditolak_mgr_dukungan' => 'bg-red-50 text-red-600 border-red-100',
                                        'ditolak_akuntan' => 'bg-red-50 text-red-600 border-red-100',
                                    ];
                                    $style =
                                        $statusStyles[$item->status_pengajuan] ??
                                        'bg-gray-50 text-gray-600 border-gray-100';
                                @endphp
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-[8px] font-black uppercase border {{ $style }}">
                                    {{ str_replace('_', ' ', $item->status_pengajuan) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        {{-- Empty State --}}
        <div class="py-12 flex flex-col items-center justify-center border-2 border-dashed border-gray-100 rounded-2xl">
            <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-300" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <p class="text-xs text-gray-400 font-medium italic">Belum ada pengajuan upah yang dibuat.</p>
        </div>
    @endif
</div>
