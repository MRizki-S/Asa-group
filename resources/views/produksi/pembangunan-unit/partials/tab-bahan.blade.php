<div x-show="tab === 'bahan'" class="space-y-4">

    @php
        $orders = \App\Models\PembangunanUnitBarangOrder::with(['details.barang.baseUnit', 'details.rapBahan', 'user', 'accBy'])
            ->where('pembangunan_unit_qc_id', $qc->id)
            ->latest()
            ->get();

        $hasSelesai = $orders->where('status_order', 'selesai')->count() > 0;
        $qcNama = 'Ke - ' . $qc->qc_urutan_ke . ' (' . ($qc->nama_qc ?? $qc->masterQc->nama_qc ?? 'QC') . ')';

        // --- Perhitungan Akumulasi Order Selesai & RAP QC ---
        // 1. Ambil seluruh order detail yang selesai untuk QC ini
        $allApprovedDetails = \App\Models\PembangunanUnitBarangOrderDetail::whereHas('order', function ($q) use ($qc) {
            $q->where('pembangunan_unit_qc_id', $qc->id)
              ->where('status_order', 'selesai');
        })->with(['barang.baseUnit', 'rapBahan'])->get();

        // Ambil kuantitas yang sudah di-retur (status selesai) untuk QC ini
        $allReturnedDetails = \App\Models\PembangunanUnitBarangReturnDetail::whereHas('barangReturn', function ($q) use ($qc) {
            $q->where('pembangunan_unit_qc_id', $qc->id)
              ->where('status', 'selesai');
        })->get();

        // Grouping per rap_bahan_id dan per barang_id (luar RAP)
        $summaryRapItems = collect();
        if ($qc->pembangunanUnitRapBahan) {
            foreach ($qc->pembangunanUnitRapBahan as $rap) {
                $rawApprovedQtyBase = (float) $allApprovedDetails->where('rap_bahan_id', $rap->id)->sum('jumlah_base');
                $returnedQtyBase = (float) $allReturnedDetails->where('barang_id', $rap->barang_id)->sum('jumlah_base');
                $approvedQtyBase = max(0, $rawApprovedQtyBase - $returnedQtyBase);

                $targetQtyBase = (float)$rap->jumlah_standar * (float)($rap->faktor_konversi ?? 1);
                $faktorRap = (float)($rap->faktor_konversi ?? 1);
                if ($faktorRap <= 0) $faktorRap = 1.0;
                
                $approvedQtyRapSatuan = $approvedQtyBase / $faktorRap;
                $targetQtyRapSatuan = (float)$rap->jumlah_standar;

                $statusSummary = 'belum_terpenuhi';
                if (abs($approvedQtyRapSatuan - $targetQtyRapSatuan) < 0.0001) {
                    $statusSummary = 'terpenuhi';
                } elseif ($approvedQtyRapSatuan > $targetQtyRapSatuan) {
                    $statusSummary = 'melebihi_rap';
                }

                $summaryRapItems->push([
                    'is_rap' => true,
                    'rap_id' => $rap->id,
                    'nama_barang' => $rap->nama_barang,
                    'satuan' => $rap->satuan,
                    'jumlah_rap' => $targetQtyRapSatuan,
                    'jumlah_ordered_satuan' => $approvedQtyRapSatuan,
                    'jumlah_ordered_base' => $approvedQtyBase,
                    'jumlah_rap_base' => $targetQtyBase,
                    'status_summary' => $statusSummary,
                    'sisa_base' => max(0, $targetQtyBase - $approvedQtyBase),
                ]);
            }
        }

        // Barang Luar RAP yang disetujui (ACC selesai)
        $outsideRapApprovedDetails = $allApprovedDetails->whereNull('rap_bahan_id')->groupBy('barang_id');
        $summaryLuarRapItems = collect();
        foreach ($outsideRapApprovedDetails as $bId => $detGroup) {
            $firstDet = $detGroup->first();
            $totalBaseLuar = $detGroup->sum('jumlah_base');
            $summaryLuarRapItems->push([
                'is_rap' => false,
                'nama_barang' => $firstDet->nama_barang,
                'satuan' => $firstDet->satuan,
                'jumlah_ordered_base' => $totalBaseLuar,
                'status_summary' => 'luar_rap',
            ]);
        }
    @endphp

    {{-- Accordion Akumulasi Order & RAP --}}
    @can('produksi.properti.pembangunan-unit.akumulasi-barang')
    <div x-data="{ openSummary: false }" class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800/40 overflow-hidden shadow-sm">
        <button type="button" @click="openSummary = !openSummary"
            class="w-full px-4 py-3 bg-gray-50/80 dark:bg-gray-800/60 flex items-center justify-between hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors">
            <div class="flex items-center gap-2 min-w-0">
                <i class="fa-solid fa-calculator text-blue-600 text-xs shrink-0"></i>
                <h4 class="text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider truncate">
                    {{ $qc->is_servis ? 'Akumulasi Servis' : 'Akumulasi Barang & RAP' }}
                </h4>
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
                <span class="text-[10px] font-semibold text-gray-500 bg-white dark:bg-gray-700 px-2 py-0.5 rounded border border-gray-200 dark:border-gray-600 whitespace-nowrap">
                    @if ($qc->is_servis)
                        {{ $summaryLuarRapItems->count() }} Item
                    @else
                        {{ $summaryRapItems->count() }} RAP | {{ $summaryLuarRapItems->count() }} Luar
                    @endif
                </span>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400 transition-transform duration-300"
                    :class="openSummary ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </button>

        <div x-show="openSummary" x-collapse x-cloak class="p-4 border-t border-gray-100 dark:border-gray-700/80 space-y-4">
            {{-- Tabel Barang Sesuai RAP (Hanya untuk QC reguler) --}}
            @if (!$qc->is_servis)
                <div>
                    <h5 class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">1. Daftar Barang Kuota RAP QC</h5>
                    <div class="overflow-x-auto rounded-lg border border-gray-100 dark:border-gray-700">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead class="bg-gray-50 dark:bg-gray-800 text-[10px] font-bold text-gray-400 uppercase">
                                <tr>
                                    <th class="p-2.5">Nama Barang</th>
                                    <th class="p-2.5 text-center">Jenis</th>
                                    <th class="p-2.5 text-center">RAP</th>
                                    <th class="p-2.5 text-center">Terorder</th>
                                    <th class="p-2.5 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse ($summaryRapItems as $sRap)
                                    @php
                                        // Ambil jenis_order dari order selesai yang memesan rap item ini
                                        $relatedJenis = $allApprovedDetails->where('rap_bahan_id', $sRap['rap_id'])->pluck('order.jenis_order')->unique()->filter();
                                        $formatDec = function($val) {
                                            $num = round((float)$val, 3);
                                            return rtrim(rtrim(number_format($num, 3, ',', '.'), '0'), ',');
                                        };
                                    @endphp
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5">
                                        <td class="p-2.5 font-medium text-gray-800 dark:text-gray-200">{{ $sRap['nama_barang'] }}</td>
                                        <td class="p-2.5 text-center">
                                            @forelse($relatedJenis as $j)
                                                <span class="inline-block px-1.5 py-0.5 text-[8px] font-black uppercase rounded border {{ $j === 'stock' ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-amber-50 text-amber-600 border-amber-100' }} mr-0.5">
                                                    {{ $j }}
                                                </span>
                                            @empty
                                                <span class="text-gray-400 italic text-[10px]">-</span>
                                            @endforelse
                                        </td>
                                        <td class="p-2.5 text-center whitespace-nowrap">{{ $formatDec($sRap['jumlah_rap']) }} {{ $sRap['satuan'] }}</td>
                                        <td class="p-2.5 text-center font-semibold whitespace-nowrap {{ $sRap['status_summary'] === 'melebihi_rap' ? 'text-red-600' : ($sRap['status_summary'] === 'terpenuhi' ? 'text-emerald-600' : 'text-blue-600') }}">
                                            {{ $formatDec($sRap['jumlah_ordered_satuan']) }} {{ $sRap['satuan'] }}
                                        </td>
                                        <td class="p-2.5 text-center">
                                            @if ($sRap['status_summary'] === 'melebihi_rap')
                                                <span class="px-1.5 py-0.5 text-[9px] font-bold bg-red-100 text-red-700 rounded border border-red-200">Melebihi</span>
                                            @elseif ($sRap['status_summary'] === 'terpenuhi')
                                                <span class="px-1.5 py-0.5 text-[9px] font-bold bg-emerald-100 text-emerald-700 rounded border border-emerald-200">Terpenuhi</span>
                                            @else
                                                <span class="px-1.5 py-0.5 text-[9px] font-bold bg-blue-50 text-blue-700 rounded border border-blue-200">Belum</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="p-3 text-center text-gray-400 italic text-[11px]">Tidak ada data barang RAP</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Tabel Barang Luar RAP --}}
            <div>
                <h5 class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">2. Daftar Barang Di Luar RAP (Akumulasi Order Selesai)</h5>
                <div class="overflow-x-auto rounded-lg border border-gray-100 dark:border-gray-700">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-gray-50 dark:bg-gray-800 text-[10px] font-bold text-gray-400 uppercase">
                            <tr>
                                <th class="p-2.5">Nama Barang</th>
                                <th class="p-2.5 text-center">Jenis</th>
                                <th class="p-2.5 text-center">Terorder</th>
                                <th class="p-2.5 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse ($summaryLuarRapItems as $sLuar)
                                @php
                                    $relatedLuarJenis = $allApprovedDetails->whereNull('rap_bahan_id')->where('nama_barang', $sLuar['nama_barang'])->pluck('order.jenis_order')->unique()->filter();
                                    $formatDec = function($val) {
                                        $num = round((float)$val, 3);
                                        return rtrim(rtrim(number_format($num, 3, ',', '.'), '0'), ',');
                                    };
                                @endphp
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5">
                                    <td class="p-2.5 font-medium text-gray-800 dark:text-gray-200">{{ $sLuar['nama_barang'] }}</td>
                                    <td class="p-2.5 text-center">
                                        @forelse($relatedLuarJenis as $j)
                                            <span class="inline-block px-1.5 py-0.5 text-[8px] font-black uppercase rounded border {{ $j === 'stock' ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-amber-50 text-amber-600 border-amber-100' }} mr-0.5">
                                                {{ $j }}
                                            </span>
                                        @empty
                                            <span class="text-gray-400 italic text-[10px]">-</span>
                                        @endforelse
                                    </td>
                                    <td class="p-2.5 text-center font-semibold text-amber-600 whitespace-nowrap">{{ $formatDec($sLuar['jumlah_ordered_base']) }} {{ $sLuar['satuan'] }}</td>
                                    <td class="p-2.5 text-center">
                                        <span class="px-1.5 py-0.5 text-[9px] font-bold bg-amber-100 text-amber-700 rounded border border-amber-200">Diluar</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="p-3 text-center text-gray-400 italic text-[11px]">Belum ada order barang di luar RAP yang disetujui (ACC Selesai)</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endcan

    {{-- Header Riwayat Order --}}
    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3 px-1 pt-2">
        <div class="flex items-center gap-3">
            <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Riwayat Order Barang</h4>
            <span class="bg-blue-100 text-blue-600 text-[9px] font-bold px-2 py-0.5 rounded-full">
                {{ $orders->count() }} Total
            </span>
        </div>
    </div>

    {{-- Tabel Order Barang --}}
    @if ($orders->count() > 0)
        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm bg-white dark:bg-gray-800/40">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="w-10 px-4 py-3"></th>
                        <th class="px-4 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider">No Order</th>
                        <th class="px-4 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider text-center">Item</th>
                        <th class="w-40 px-4 py-3 text-[10px] font-bold text-gray-500 uppercase text-center tracking-wider">Status</th>
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
                                    {{ \Carbon\Carbon::parse($order->tanggal_diajukan)->translatedFormat('d M Y, H:i') }}
                                </p>
                                <div class="flex items-center gap-2">
                                    <p class="text-xs font-bold text-gray-700 dark:text-gray-200">
                                        {{ $order->nomor_order ?? 'REQ-' . str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                                    </p>
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
                                    $style = $statusMap[$order->status_order] ?? 'bg-gray-50 text-gray-500 border-gray-100';
                                @endphp
                                <span class="inline-flex items-center px-2 py-1 rounded text-[8px] font-black uppercase border {{ $style }}">
                                    {{ str_replace('_', ' ', $order->status_order) }}
                                </span>
                            </td>
                        </tr>

                        {{-- Accordion Detail --}}
                        <tr x-show="open" x-cloak>
                            <td colspan="4" class="p-0 border-none bg-gray-50/50 dark:bg-gray-900/40">
                                <div x-show="open" x-collapse class="px-6 py-4 border-t border-gray-100 dark:border-gray-800">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        {{-- Daftar Barang --}}
                                        <div class="space-y-3">
                                            <h5 class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">
                                                Detail Item Barang
                                            </h5>
                                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                                                <div class="max-h-[350px] overflow-auto custom-scrollbar">
                                                    <table class="w-full text-left border-collapse">
                                                        <thead class="bg-gray-50/80 dark:bg-gray-700/50 sticky top-0 z-10 backdrop-blur-sm">
                                                            <tr>
                                                                <th class="px-3 py-2 text-[10px] font-semibold text-gray-500 uppercase border-b border-gray-100 dark:border-gray-700">Nama Barang</th>
                                                                <th class="px-3 py-2 text-[10px] font-semibold text-gray-500 uppercase text-center border-b border-gray-100 dark:border-gray-700">Jumlah</th>
                                                                <th class="px-3 py-2 text-[10px] font-semibold text-gray-500 uppercase text-center border-b border-gray-100 dark:border-gray-700">Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                                            @foreach ($order->details as $det)
                                                                @php
                                                                    $isRap = (bool) $det->rapBahan;
                                                                    $isOverOrder = false;

                                                                    if ($isRap) {
                                                                        $standarRap = (float) ($det->rapBahan->jumlah_standar ?? 0);
                                                                        $faktorRap = (float) ($det->rapBahan->faktor_konversi ?? 1);
                                                                        $baseRapTarget = $standarRap * $faktorRap;

                                                                        // Akumulasi order terdahulu + order ini (yang tidak ditolak)
                                                                        $prevAccumulatedBase = (float) \App\Models\PembangunanUnitBarangOrderDetail::where('rap_bahan_id', $det->rap_bahan_id)
                                                                            ->whereHas('order', function ($q) use ($order) {
                                                                                $q->where('status_order', '!=', 'ditolak')
                                                                                  ->where('created_at', '<=', $order->created_at);
                                                                            })
                                                                            ->sum('jumlah_base');

                                                                        $isOverOrder = ($prevAccumulatedBase - $baseRapTarget) > 0.001;
                                                                    }
                                                                @endphp

                                                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-colors text-xs">
                                                                    <td class="px-3 py-2.5 align-top">
                                                                        <p class="text-[11px] font-normal text-gray-800 dark:text-gray-100 leading-snug">
                                                                            {{ $det->nama_barang ?? '-' }}
                                                                        </p>
                                                                        @if ($det->alasan_permintaan_tidak_sesuai_rap)
                                                                            <p class="text-[9px] text-red-500 italic mt-0.5 font-normal">
                                                                                Ket: {{ $det->alasan_permintaan_tidak_sesuai_rap }}
                                                                            </p>
                                                                        @endif
                                                                    </td>
                                                                    <td class="px-3 py-2.5 text-center align-top whitespace-nowrap">
                                                                        <p class="text-[11px] font-normal text-gray-900 dark:text-white">
                                                                            {{ (float) $det->jumlah_input }}
                                                                            <span class="text-[9px] text-gray-500 dark:text-gray-400">{{ $det->satuan }}</span>
                                                                        </p>
                                                                    </td>
                                                                    <td class="px-3 py-2.5 text-center align-top whitespace-nowrap">
                                                                        @if ($isRap && !$isOverOrder)
                                                                            <span class="text-[8px] font-medium text-emerald-700 uppercase bg-emerald-50 dark:bg-emerald-950/40 px-1.5 py-0.5 rounded border border-emerald-200 dark:border-emerald-800">Sesuai RAP</span>
                                                                        @elseif ($isRap && $isOverOrder)
                                                                            <span class="text-[8px] font-medium text-red-700 uppercase bg-red-50 dark:bg-red-950/40 px-1.5 py-0.5 rounded border border-red-200 dark:border-red-800">Melebihi RAP</span>
                                                                        @elseif (!$isRap)
                                                                            <span class="text-[8px] font-medium text-amber-700 uppercase bg-amber-50 dark:bg-amber-950/40 px-1.5 py-0.5 rounded border border-amber-200 dark:border-amber-800">Diluar RAP</span>
                                                                        @endif
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
                                                        "{{ $order->catatan ?? 'Tidak ada catatan permintaan.' }}"
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="p-3 bg-gray-50/80 dark:bg-gray-800/40 rounded-xl border border-gray-100 dark:border-gray-700/60 space-y-2 text-xs">
                                                <div class="flex justify-between items-center text-[10px]">
                                                    <span class="text-gray-400 font-medium">Diajukan Oleh:</span>
                                                    <span class="font-bold text-gray-700 dark:text-gray-200">
                                                        {{ $order->user->nama_lengkap ?? $order->user->name ?? $order->user->username ?? 'Pengawas' }}
                                                    </span>
                                                </div>
                                                @if (in_array($order->status_order, ['selesai', 'ditolak']))
                                                    <div class="flex justify-between items-start text-[10px] border-t border-gray-100 dark:border-gray-700 pt-1.5">
                                                        <span class="text-gray-400 font-medium shrink-0">Dikonfirmasi Oleh:</span>
                                                        <div class="text-right">
                                                            <span class="font-bold text-gray-700 dark:text-gray-200 block">
                                                                {{ $order->accBy->nama_lengkap ?? $order->accBy->name ?? $order->accBy->username ?? 'Petugas Gudang' }}
                                                            </span>
                                                            @if ($order->tanggal_selesai)
                                                                <span class="text-[9px] text-gray-400 font-normal block mt-0.5">({{ \Carbon\Carbon::parse($order->tanggal_selesai)->translatedFormat('d M Y, H:i') }})</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
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
                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <h5 class="text-xs font-bold text-gray-600 dark:text-gray-300">Belum Ada Riwayat Order</h5>
        </div>
    @endif

    {{-- Tabel Riwayat Retur Barang --}}
    @php
        $returns = \App\Models\PembangunanUnitBarangReturn::with(['details.barang', 'createdBy', 'accBy'])
            ->where('pembangunan_unit_qc_id', $qc->id)
            ->latest()
            ->get();
    @endphp

    <div class="pt-6 border-t border-gray-100 dark:border-gray-800 space-y-4">
        {{-- Header Section --}}
        <div class="flex items-center gap-3 px-1">
            <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Riwayat Retur Barang</h4>
            <span class="bg-red-100 text-red-600 text-[9px] font-bold px-2 py-0.5 rounded-full">
                {{ $returns->count() }} Total
            </span>
        </div>

        @if ($returns->count() > 0)
            <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm bg-white dark:bg-gray-800/40">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="w-10 px-4 py-3"></th>
                            <th class="px-4 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider">No Retur</th>
                            <th class="px-4 py-3 text-[10px] font-bold text-gray-500 uppercase tracking-wider text-center">Item</th>
                            <th class="w-40 px-4 py-3 text-[10px] font-bold text-gray-500 uppercase text-center tracking-wider">Status</th>
                        </tr>
                    </thead>

                    @foreach ($returns as $ret)
                        <tbody x-data="{ open: false }" class="border-t border-gray-100 dark:border-gray-800">
                            <tr @click="open = !open" class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors cursor-pointer">
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
                                        {{ \Carbon\Carbon::parse($ret->tanggal_return)->translatedFormat('d M Y, H:i') }}
                                    </p>
                                    <p class="text-xs font-bold text-gray-700 dark:text-gray-200">
                                        {{ $ret->nomor_return }}
                                    </p>
                                </td>
                                <td class="px-4 py-4 text-xs text-center">
                                    <span class="text-[10px] text-gray-400 font-medium">
                                        {{ $ret->details->count() }} Item
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    @php
                                        $retStatusMap = [
                                            'diproses' => 'bg-blue-50 text-blue-600 border-blue-100',
                                            'selesai' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                            'ditolak' => 'bg-red-50 text-red-600 border-red-100',
                                            'draft' => 'bg-gray-50 text-gray-600 border-gray-100',
                                        ];
                                        $retStyle = $retStatusMap[$ret->status] ?? 'bg-gray-50 text-gray-500 border-gray-100';
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-1 rounded text-[8px] font-black uppercase border {{ $retStyle }}">
                                        {{ $ret->status }}
                                    </span>
                                </td>
                            </tr>

                            {{-- Accordion Detail Retur --}}
                            <tr x-show="open" x-cloak>
                                <td colspan="4" class="p-0 border-none bg-gray-50/50 dark:bg-gray-900/40">
                                    <div x-show="open" x-collapse class="px-10 py-6 border-t border-gray-100 dark:border-gray-800">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                            {{-- Daftar Item Barang Retur --}}
                                            <div class="space-y-4">
                                                <h5 class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2">
                                                    Detail Item Barang Retur
                                                </h5>
                                                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                                                    <div class="max-h-[350px] overflow-auto custom-scrollbar">
                                                        <table class="w-full text-left border-collapse">
                                                            <thead class="bg-gray-50/80 dark:bg-gray-700/50 sticky top-0 z-10 backdrop-blur-sm">
                                                                <tr>
                                                                    <th class="px-3 py-2 text-[9px] font-bold text-gray-400 uppercase border-b border-gray-100 dark:border-gray-700">Nama Barang</th>
                                                                    <th class="px-3 py-2 text-[9px] font-bold text-gray-400 uppercase text-right border-b border-gray-100 dark:border-gray-700">Jumlah Retur</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                                                @foreach ($ret->details as $rdet)
                                                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-colors">
                                                                        <td class="px-3 py-3">
                                                                            <p class="text-[11px] font-bold text-gray-700 dark:text-gray-200 leading-tight">
                                                                                {{ $rdet->nama_barang ?? '-' }}
                                                                            </p>
                                                                            @if ($rdet->keterangan)
                                                                                <p class="text-[9px] text-gray-400 italic mt-0.5">
                                                                                    Ket: {{ $rdet->keterangan }}
                                                                                </p>
                                                                            @endif
                                                                            @if ($ret->status === 'selesai')
                                                                                <div class="flex items-center gap-1.5 mt-1.5">
                                                                                    <span class="text-[8px] font-bold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-100">
                                                                                        Layak: {{ (float)$rdet->jumlah_layak_base }}
                                                                                    </span>
                                                                                    @if ($rdet->jumlah_rusak_base > 0)
                                                                                        <span class="text-[8px] font-bold text-red-600 bg-red-50 px-1.5 py-0.5 rounded border border-red-100">
                                                                                            Rusak: {{ (float)$rdet->jumlah_rusak_base }}
                                                                                        </span>
                                                                                    @endif
                                                                                </div>
                                                                            @endif
                                                                        </td>
                                                                        <td class="px-3 py-3 text-right align-top">
                                                                            <p class="text-[11px] font-black text-gray-800 dark:text-white">
                                                                                {{ (float)$rdet->jumlah_input }}
                                                                                <span class="text-[9px] font-medium text-gray-400">{{ $rdet->satuan }}</span>
                                                                            </p>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Catatan & Metadata --}}
                                            <div class="space-y-4">
                                                <div>
                                                    <h5 class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2">
                                                        Catatan Retur
                                                    </h5>
                                                    <div class="p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700 shadow-sm min-h-[60px]">
                                                        <p class="text-xs text-gray-600 dark:text-gray-400 italic leading-relaxed">
                                                            "{{ $ret->catatan ?? 'Tidak ada catatan retur.' }}"
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="p-3 bg-gray-50/80 dark:bg-gray-800/40 rounded-xl border border-gray-100 dark:border-gray-700/60 space-y-2 text-xs">
                                                    <div class="flex justify-between items-center text-[10px]">
                                                        <span class="text-gray-400 font-medium">Diajukan Oleh:</span>
                                                        <span class="font-bold text-gray-700 dark:text-gray-200">
                                                            {{ $ret->createdBy->nama_lengkap ?? $ret->createdBy->name ?? '-' }}
                                                        </span>
                                                    </div>
                                                    @if (in_array($ret->status, ['selesai', 'ditolak']) && $ret->accBy)
                                                        <div class="flex justify-between items-start text-[10px] border-t border-gray-100 dark:border-gray-700 pt-1.5">
                                                            <span class="text-gray-400 font-medium shrink-0">Dikonfirmasi Oleh:</span>
                                                            <div class="text-right">
                                                                <span class="font-bold text-gray-700 dark:text-gray-200 block">
                                                                    {{ $ret->accBy->nama_lengkap ?? $ret->accBy->name ?? '-' }}
                                                                </span>
                                                                @if ($ret->acc_at)
                                                                    <span class="text-[9px] text-gray-400 font-normal block mt-0.5">({{ \Carbon\Carbon::parse($ret->acc_at)->translatedFormat('d M Y, H:i') }})</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>

                                                @if ($ret->status === 'ditolak' && $ret->alasan_tolak)
                                                    <div class="p-3 bg-red-50 dark:bg-red-950/20 rounded-xl border border-red-200 dark:border-red-800/50">
                                                        <p class="text-[9px] font-black text-red-500 uppercase tracking-wider mb-1">Alasan Penolakan Gudang:</p>
                                                        <p class="text-xs text-red-700 dark:text-red-300 italic">"{{ $ret->alasan_tolak }}"</p>
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
            <div class="py-10 flex flex-col items-center justify-center border border-dashed border-gray-100 dark:border-gray-800 rounded-2xl bg-gray-50/20">
                <i class="fa-solid fa-rotate-left text-gray-300 text-xl mb-2"></i>
                <p class="text-xs font-bold text-gray-400">Belum Ada Riwayat Retur Barang</p>
            </div>
        @endif
    </div>
</div>
