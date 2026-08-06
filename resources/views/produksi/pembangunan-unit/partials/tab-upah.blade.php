<div x-show="tab === 'upah'" class="space-y-4">
    @php
        // --- Perhitungan Akumulasi Pengajuan Upah disetujui (ACC Akuntan / selesai) & RAP QC ---
        $allApprovedUpah = \App\Models\PembangunanUnitUpahPengajuan::where('pembangunan_unit_qc_id', $qc->id)
            ->where('status_pengajuan', 'disetujui_akuntan')
            ->get();

        $summaryRapUpahItems = collect();
        if ($qc->pembangunanUnitRapUpah) {
            foreach ($qc->pembangunanUnitRapUpah as $rapUpah) {
                $approvedNominal = $allApprovedUpah->where('pembangunan_unit_rap_upah_id', $rapUpah->id)->sum('nominal_diajukan');
                $targetNominal = (float) $rapUpah->nominal_standar;

                $statusUpahSummary = 'belum_terpenuhi';
                if (abs($approvedNominal - $targetNominal) <= 1 && $approvedNominal > 0) {
                    $statusUpahSummary = 'terpenuhi';
                } elseif ($approvedNominal > ($targetNominal + 1)) {
                    $statusUpahSummary = 'melebihi_rap';
                }

                $summaryRapUpahItems->push([
                    'is_rap' => true,
                    'rap_id' => $rapUpah->id,
                    'nama_upah' => $rapUpah->nama_upah,
                    'nominal_rap' => $targetNominal,
                    'nominal_approved' => $approvedNominal,
                    'status_summary' => $statusUpahSummary,
                ]);
            }
        }

        // Grouping per rap_upah_id
        $summaryRapUpahItems = collect();
        if ($qc->pembangunanUnitRapUpah) {
            foreach ($qc->pembangunanUnitRapUpah as $rapUpah) {
                $approvedNominal = $allApprovedUpah->where('pembangunan_unit_rap_upah_id', $rapUpah->id)->sum('nominal_diajukan');
                $targetNominal = (float) $rapUpah->nominal_standar;

                $statusUpahSummary = 'belum_terpenuhi';
                if (abs($approvedNominal - $targetNominal) <= 1 && $approvedNominal > 0) {
                    $statusUpahSummary = 'sesuai_rap';
                } elseif ($approvedNominal > ($targetNominal + 1)) {
                    $statusUpahSummary = 'melebihi_rap';
                }

                $summaryRapUpahItems->push([
                    'is_rap' => true,
                    'rap_id' => $rapUpah->id,
                    'nama_upah' => $rapUpah->nama_upah,
                    'nominal_rap' => $targetNominal,
                    'nominal_approved' => $approvedNominal,
                    'status_summary' => $statusUpahSummary,
                ]);
            }
        }
    @endphp

    {{-- Accordion Akumulasi Upah & RAP --}}
    <div x-data="{ openUpahSummary: false }" class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800/40 overflow-hidden shadow-sm">
        <button type="button" @click="openUpahSummary = !openUpahSummary"
            class="w-full px-4 py-3 bg-gray-50/80 dark:bg-gray-800/60 flex items-center justify-between hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors">
            <div class="flex items-center gap-2 min-w-0">
                <i class="fa-solid fa-calculator text-blue-600 text-xs shrink-0"></i>
                <h4 class="text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider truncate">
                    Akumulasi Upah & RAP
                </h4>
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
                <span class="text-[10px] font-semibold text-gray-500 bg-white dark:bg-gray-700 px-2 py-0.5 rounded border border-gray-200 dark:border-gray-600 whitespace-nowrap">
                    {{ $summaryRapUpahItems->count() }} Pekerjaan
                </span>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400 transition-transform duration-300"
                    :class="openUpahSummary ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </button>

        <div x-show="openUpahSummary" x-collapse x-cloak class="p-4 border-t border-gray-100 dark:border-gray-700/80 space-y-4">
            {{-- Tabel Upah Kuota RAP QC --}}
            <div>
                <div class="overflow-x-auto rounded-lg border border-gray-100 dark:border-gray-700">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-gray-50 dark:bg-gray-800 text-[10px] font-bold text-gray-400 uppercase">
                            <tr>
                                <th class="p-2.5">Pekerjaan</th>
                                <th class="p-2.5 text-right">RAP</th>
                                <th class="p-2.5 text-right">Disetujui</th>
                                <th class="p-2.5 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse ($summaryRapUpahItems as $sUpah)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5">
                                    <td class="p-2.5 font-medium text-gray-800 dark:text-gray-200">{{ $sUpah['nama_upah'] }}</td>
                                    <td class="p-2.5 text-right font-mono whitespace-nowrap">Rp {{ number_format($sUpah['nominal_rap'], 0, ',', '.') }}</td>
                                    <td class="p-2.5 text-right font-mono font-semibold whitespace-nowrap {{ $sUpah['status_summary'] === 'melebihi_rap' ? 'text-red-600' : ($sUpah['status_summary'] === 'sesuai_rap' ? 'text-emerald-600' : 'text-blue-600') }}">
                                        Rp {{ number_format($sUpah['nominal_approved'], 0, ',', '.') }}
                                    </td>
                                    <td class="p-2.5 text-center">
                                        @if ($sUpah['status_summary'] === 'melebihi_rap')
                                            <span class="px-1.5 py-0.5 text-[9px] font-bold bg-red-100 text-red-700 rounded border border-red-200">Melebihi</span>
                                        @elseif ($sUpah['status_summary'] === 'sesuai_rap')
                                            <span class="px-1.5 py-0.5 text-[9px] font-bold bg-emerald-100 text-emerald-700 rounded border border-emerald-200">Sesuai</span>
                                        @else
                                            <span class="px-1.5 py-0.5 text-[9px] font-bold bg-blue-50 text-blue-700 rounded border border-blue-200">Belum</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="p-3 text-center text-gray-400 italic text-[11px]">Tidak ada data RAP upah</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3 px-1 pt-2">
        <div class="flex items-center gap-3">
            <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Daftar Pengajuan Upah</h4>
            <span class="bg-blue-100 text-blue-600 text-[9px] font-bold px-2 py-0.5 rounded-full">
                {{ $qc->pembangunanUnitUpahPengajuan->count() }} Total
            </span>
        </div>

        <div class="flex flex-wrap gap-2 sm:items-center">
            {{-- 
            <a href="{{ route('produksi.pembangunanUnit.laporanUpah', ['id' => $data->id, 'qcId' => $qc->master_qc_urutan_id]) }}"
                class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 text-[10px] font-bold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm transition-all uppercase flex items-center gap-2">
                <i class="fa-solid fa-chart-line text-blue-500"></i>
                Lihat Laporan
            </a>
            --}}
            @if (!in_array($data->status_pembangunan, ['selesai', 'selesai dengan catatan']))
                <button @click="prepareUpah({{ json_encode($qc->pembangunanUnitRapUpah) }}, {{ $qc->id }})"
                    class="px-4 py-2 bg-blue-600 text-white text-[10px] font-bold rounded-lg hover:bg-blue-700 shadow-sm transition-all uppercase flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Ajukan Upah
                </button>
            @endif
        </div>
    </div>

    {{-- Tabel Pengajuan --}}
    @if ($qc->pembangunanUnitUpahPengajuan->count() > 0)
        <div
            class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm bg-white dark:bg-gray-800/40">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="w-8 px-3 py-3"></th>
                        <th class="px-4 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider">No. Pengajuan</th>
                        <th class="px-4 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider">Pekerjaan Upah</th>
                        <th class="px-4 py-3 text-[10px] font-bold text-gray-500 uppercase text-right tracking-wider">Nominal</th>
                        <th class="px-4 py-3 text-[10px] font-bold text-gray-500 uppercase text-center tracking-wider">Status</th>
                    </tr>
                </thead>

                {{-- LOOPING MULAI DI SINI --}}
                @foreach ($qc->pembangunanUnitUpahPengajuan as $item)
                    {{-- Setiap pasang baris dibungkus satu tbody agar x-data bisa dishare --}}
                    <tbody x-data="{ open: false }" class="border-t border-gray-100 dark:border-gray-800">
                        {{-- Baris Utama --}}
                        <tr @click="open = !open"
                            class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors cursor-pointer">
                            <td class="px-3 py-3 text-center">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="w-3 h-3 text-gray-400 transition-transform duration-300 mx-auto"
                                    :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black text-blue-700 bg-blue-50 dark:bg-blue-900/40 dark:text-blue-300 font-mono border border-blue-200/60 dark:border-blue-800/50">
                                    {{ $item->nomor_pengajuan ?? ('UBT-' . \Carbon\Carbon::parse($item->tanggal_diajukan)->format('ymd') . '-' . str_pad($item->id, 4, '0', STR_PAD_LEFT)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <p class="text-xs font-bold text-gray-800 dark:text-gray-100">
                                    {{ $item->nama_upah }}
                                </p>
                                <p class="text-[9px] text-gray-400 font-medium tracking-wide mt-0.5 flex items-center gap-1">
                                    {{ $item->tanggal_diajukan->translatedFormat('d M Y, H:i') }}
                                </p>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <p class="text-xs font-black text-gray-900 dark:text-white font-mono">
                                    Rp {{ number_format($item->nominal_diajukan, 0, ',', '.') }}
                                </p>
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-[9px] font-black uppercase border {{ $item->status_style }}">
                                    {{ $item->status_label }}
                                </span>
                            </td>
                        </tr>

                        {{-- Baris Detail (Accordion) --}}
                        <tr x-show="open" x-cloak>
                            <td colspan="5" class="p-0 border-none bg-gray-50/50 dark:bg-gray-900/40">
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

                                    @if(is_null($item->disetujui_mgr_produksi) && is_null($item->disetujui_mgr_dukungan) && is_null($item->disetujui_akuntan) && is_null($item->ditolak_pada))
                                    <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-800 flex justify-end">
                                        <button type="button"
                                            @click="openCancelUpahModal = true; cancelUpahActionUrl = '{{ route('produksi.pembangunanUnit.upahDestroy', $item->id) }}'"
                                            class="px-4 py-2 text-[10px] font-black bg-red-50 hover:bg-red-100 dark:bg-red-950/20 text-red-600 rounded-xl uppercase border border-red-200 dark:border-red-800/50 transition-all duration-300 flex items-center justify-center gap-2 shadow-sm">
                                            <i class="fa-solid fa-trash-can"></i>
                                            Batalkan Pengajuan Upah
                                        </button>
                                    </div>
                                    @endif
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
