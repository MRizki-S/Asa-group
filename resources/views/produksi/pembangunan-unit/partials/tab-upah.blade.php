<div x-show="tab === 'upah'" class="space-y-4">
    {{-- Header --}}
    <div class="flex justify-between items-center px-1">
        <div class="flex items-center gap-3">
            <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Daftar Pengajuan Upah</h4>
            <span class="bg-blue-100 text-blue-600 text-[9px] font-bold px-2 py-0.5 rounded-full">
                {{ $qc->pembangunanUnitUpahPengajuan->count() }} Total
            </span>
        </div>

        <div class="flex flex-row gap-2 items-center">
            <a href="{{ route('produksi.pembangunanUnit.laporanUpah', $data->id) }}"
                class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 text-[10px] font-bold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm transition-all uppercase flex items-center gap-2">
                <i class="fa-solid fa-chart-line text-blue-500"></i>
                Lihat Laporan Termin
            </a>
            <button @click="prepareUpah({{ json_encode($qc->pembangunanUnitRapUpah) }}, {{ $qc->id }})"
                class="px-4 py-2 bg-blue-600 text-white text-[10px] font-bold rounded-lg hover:bg-blue-700 shadow-sm transition-all uppercase flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Ajukan Upah
            </button>
        </div>
    </div>

    {{-- Tabel Pengajuan --}}
    @if ($qc->pembangunanUnitUpahPengajuan->count() > 0)
        <div
            class="overflow-hidden border border-gray-100 dark:border-gray-800 rounded-xl shadow-sm bg-white dark:bg-transparent">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="w-10 px-4 py-3"></th>
                        <th class="px-4 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider">Detail
                            Pekerjaan</th>
                        <th class="px-4 py-3 text-[10px] font-bold text-gray-500 uppercase text-right tracking-wider">
                            Nominal</th>
                        <th class="px-4 py-3 text-[10px] font-bold text-gray-500 uppercase text-center tracking-wider">
                            Status</th>
                    </tr>
                </thead>

                {{-- LOOPING MULAI DI SINI --}}
                @foreach ($qc->pembangunanUnitUpahPengajuan as $item)
                    {{-- Setiap pasang baris dibungkus satu tbody agar x-data bisa dishare --}}
                    <tbody x-data="{ open: false }" class="border-t border-gray-100 dark:border-gray-800">
                        {{-- Baris Utama --}}
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
                                    {{ $item->tanggal_diajukan->translatedFormat('d F Y') }}
                                </p>
                                <p class="text-xs font-bold text-gray-700 dark:text-gray-200">
                                    {{ $item->nama_upah }}
                                </p>
                            </td>
                            <td class="px-4 py-4 text-right">
                                <p class="text-xs font-black text-gray-800 dark:text-white font-mono">
                                    Rp {{ number_format($item->nominal_diajukan, 0, ',', '.') }}
                                </p>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded text-[8px] font-black uppercase border {{ $item->status_style }}">
                                    {{ $item->status_label }}
                                </span>
                            </td>
                        </tr>

                        {{-- Baris Detail (Accordion) --}}
                        <tr x-show="open" x-cloak>
                            <td colspan="4" class="p-0 border-none bg-gray-50/50 dark:bg-gray-900/40">
                                <div x-show="open" x-collapse
                                    class="px-10 py-6 border-t border-gray-100 dark:border-gray-800">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                        {{-- Informasi & Catatan --}}
                                        <div class="space-y-4">
                                            <div>
                                                <h5
                                                    class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2">
                                                    Catatan Pengawas</h5>
                                                <div
                                                    class="p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700 shadow-sm">
                                                    <p
                                                        class="text-xs text-gray-600 dark:text-gray-400 italic leading-relaxed">
                                                        "{{ $item->catatan_pengawas ?? 'Tidak ada catatan.' }}"
                                                    </p>
                                                </div>
                                            </div>

                                            @if ($item->alasan_ditolak)
                                                <div
                                                    class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-900/30 rounded-lg">
                                                    <div class="flex items-center gap-2 mb-1">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="w-3 h-3 text-red-600" fill="none"
                                                            viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        <span
                                                            class="text-[9px] font-black text-red-600 uppercase">Alasan
                                                            Penolakan:</span>
                                                    </div>
                                                    <p class="text-xs text-red-700 dark:text-red-400 font-medium">
                                                        {{ $item->alasan_ditolak }}</p>
                                                    <p class="text-[8px] text-red-400 mt-2 font-bold uppercase italic">
                                                        Ditolak pada: {{ $item->ditolak_pada?->format('d/m/Y H:i') }}
                                                    </p>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="space-y-3">
                                            <h5
                                                class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-4">
                                                Log Persetujuan</h5>
                                            <div
                                                class="relative border-l-2 border-gray-200 dark:border-gray-700 ml-2 space-y-5 pb-2">

                                                {{-- 1. Diajukan (Selalu Done) --}}
                                                <div class="relative pl-6">
                                                    <div
                                                        class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-blue-500 border-4 border-white dark:border-gray-900 shadow-sm">
                                                    </div>
                                                    <p
                                                        class="text-[10px] font-black text-gray-700 dark:text-gray-200 uppercase">
                                                        Pengajuan Dikirim</p>
                                                    <p class="text-[9px] text-gray-400">
                                                        {{ $item->tanggal_diajukan->format('d M Y, H:i') }}</p>
                                                </div>

                                                {{-- 2. MGR Produksi --}}
                                                <div class="relative pl-6">
                                                    @php
                                                        $isRejectedProduksi =
                                                            $item->status_pengajuan === 'ditolak_mgr_produksi';
                                                        $isApprovedProduksi = !empty($item->disetujui_mgr_produksi);
                                                        $dotColorProduksi = $isApprovedProduksi
                                                            ? 'bg-emerald-500'
                                                            : ($isRejectedProduksi
                                                                ? 'bg-red-500'
                                                                : 'bg-gray-300 dark:bg-gray-700');
                                                        $textColorProduksi = $isApprovedProduksi
                                                            ? 'text-emerald-600'
                                                            : ($isRejectedProduksi
                                                                ? 'text-red-600'
                                                                : 'text-gray-400 uppercase');
                                                    @endphp
                                                    <div
                                                        class="absolute -left-[9px] top-1 w-4 h-4 rounded-full {{ $dotColorProduksi }} border-4 border-white dark:border-gray-900 shadow-sm">
                                                    </div>
                                                    <p
                                                        class="text-[10px] uppercase font-black {{ $textColorProduksi }}">
                                                        MGR Produksi:
                                                        @if ($isApprovedProduksi)
                                                            Approved
                                                        @elseif($isRejectedProduksi)
                                                            Ditolak
                                                        @else
                                                            Pending
                                                        @endif
                                                    </p>
                                                    <p class="text-[9px] text-gray-400">
                                                        {{ $item->disetujui_mgr_produksi?->format('d M Y, H:i') ?? ($isRejectedProduksi ? 'Ditolak pada ' . $item->ditolak_pada?->format('d M Y, H:i') : '-') }}
                                                    </p>
                                                </div>

                                                {{-- 3. MGR Dukungan --}}
                                                <div class="relative pl-6">
                                                    @php
                                                        $isRejectedDukungan =
                                                            $item->status_pengajuan === 'ditolak_mgr_dukungan';
                                                        $isApprovedDukungan = !empty($item->disetujui_mgr_dukungan);
                                                        $dotColorDukungan = $isApprovedDukungan
                                                            ? 'bg-emerald-500'
                                                            : ($isRejectedDukungan
                                                                ? 'bg-red-500'
                                                                : 'bg-gray-300 dark:bg-gray-700');
                                                        $textColorDukungan = $isApprovedDukungan
                                                            ? 'text-emerald-600'
                                                            : ($isRejectedDukungan
                                                                ? 'text-red-600'
                                                                : 'text-gray-400 uppercase');
                                                    @endphp
                                                    <div
                                                        class="absolute -left-[9px] top-1 w-4 h-4 rounded-full {{ $dotColorDukungan }} border-4 border-white dark:border-gray-900 shadow-sm">
                                                    </div>
                                                    <p
                                                        class="text-[10px] uppercase font-black {{ $textColorDukungan }}">
                                                        MGR Dukungan:
                                                        @if ($isApprovedDukungan)
                                                            Approved
                                                        @elseif($isRejectedDukungan)
                                                            Ditolak
                                                        @else
                                                            Pending
                                                        @endif
                                                    </p>
                                                    <p class="text-[9px] text-gray-400">
                                                        {{ $item->disetujui_mgr_dukungan?->format('d M Y, H:i') ?? ($isRejectedDukungan ? 'Ditolak pada ' . $item->ditolak_pada?->format('d M Y, H:i') : '-') }}
                                                    </p>
                                                </div>

                                                {{-- 4. Akuntan --}}
                                                <div class="relative pl-6">
                                                    @php
                                                        $isRejectedAkuntan =
                                                            $item->status_pengajuan === 'ditolak_akuntan';
                                                        $isApprovedAkuntan = !empty($item->disetujui_akuntan);
                                                        $dotColorAkuntan = $isApprovedAkuntan
                                                            ? 'bg-emerald-500'
                                                            : ($isRejectedAkuntan
                                                                ? 'bg-red-500'
                                                                : 'bg-gray-300 dark:bg-gray-700');
                                                        $textColorAkuntan = $isApprovedAkuntan
                                                            ? 'text-emerald-600'
                                                            : ($isRejectedAkuntan
                                                                ? 'text-red-600'
                                                                : 'text-gray-400 uppercase');
                                                    @endphp
                                                    <div
                                                        class="absolute -left-[9px] top-1 w-4 h-4 rounded-full {{ $dotColorAkuntan }} border-4 border-white dark:border-gray-900 shadow-sm">
                                                    </div>
                                                    <p
                                                        class="text-[10px] uppercase font-black {{ $textColorAkuntan }}">
                                                        Akuntan (Final):
                                                        @if ($isApprovedAkuntan)
                                                            Cair
                                                        @elseif($isRejectedAkuntan)
                                                            Ditolak
                                                        @else
                                                            Pending
                                                        @endif
                                                    </p>
                                                    <p class="text-[9px] text-gray-400">
                                                        {{ $item->disetujui_akuntan?->format('d M Y, H:i') ?? ($isRejectedAkuntan ? 'Ditolak pada ' . $item->ditolak_pada?->format('d M Y, H:i') : '-') }}
                                                    </p>
                                                </div>

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
        <div
            class="py-16 flex flex-col items-center justify-center border-2 border-dashed border-gray-100 dark:border-gray-800 rounded-3xl bg-gray-50/30">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gray-300 mb-4" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h5 class="text-xs font-bold text-gray-600 dark:text-gray-300">Belum Ada Pengajuan Upah</h5>
            <p class="text-[10px] text-gray-400 mt-1">Klik tombol "Ajukan Upah" untuk memulai.</p>
        </div>
    @endif
</div>
