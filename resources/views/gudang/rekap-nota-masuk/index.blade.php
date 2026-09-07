@extends('layouts.app')

@section('pageActive', 'RekapNotaMasuk')

@section('content')
<!-- ===== Main Content Start ===== -->
<div class="w-full max-w-full p-2 md:p-4" x-init="sidebarToggle = true" x-data="{
    showModal: false,
    selectedBarang: '',
    selectedDate: '',
    selectedDateRaw: '',
    selectedTransactions: [],
    openTxModal(barangName, dayNum, dayTransactions, rawDate) {
        if (!dayTransactions || dayTransactions.length === 0) return;
        this.selectedBarang = barangName;
        this.selectedDate = dayNum + ' {{ \Carbon\Carbon::create()->month((int)$bulan)->translatedFormat('F') }} {{ $tahun }}';
        this.selectedDateRaw = rawDate;
        this.selectedTransactions = dayTransactions;
        this.showModal = true;
    }
}">

    <!-- Breadcrumb Start -->
    <div x-data="{ pageName: 'Rekap Barang Masuk & Keluar Per Hari' }">
        @include('partials.breadcrumb')
    </div>
    <!-- Breadcrumb End -->

    <div class="space-y-5 sm:space-y-6">
        <div class="rounded-2xl border border-gray-200 px-3 py-3 sm:px-6 sm:py-5 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            
            {{-- Header Title & Filter --}}
            <div class="mb-5 flex flex-col gap-4 md:flex-row md:items-center md:justify-between border-b border-gray-100 dark:border-gray-800 pb-4">
                <div>
                    <h3 class="text-base sm:text-lg font-bold text-gray-800 dark:text-white flex flex-wrap items-center gap-2">
                        <span>📅 Rekap Mutasi Barang Masuk & Keluar</span>
                        <span class="px-2.5 py-0.5 text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300 rounded-full">
                            {{ count($rekapMatrix) }} Jenis Barang
                        </span>
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Akumulasi pergerakan stok per tanggal pada bulan <strong>{{ \Carbon\Carbon::create()->month((int)$bulan)->translatedFormat('F') }} {{ $tahun }}</strong> (Lengkap dengan Stok Awal Cutoff & Stok Akhir Realtime Gudang).
                    </p>
                </div>

                {{-- Form Filter --}}
                <form method="GET" action="{{ route('gudang.rekapNotaMasuk.index') }}" id="filter-rekap-form"
                    class="flex flex-wrap items-center gap-2 sm:gap-3 bg-gray-50 dark:bg-gray-800/50 p-2.5 sm:p-3 rounded-xl border border-gray-200 dark:border-gray-800">
                    
                    <div class="flex items-center gap-1.5">
                        <label class="text-[11px] sm:text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Gudang / UBS</label>
                        <select name="ubs_id" class="rounded-lg border-gray-300 bg-white text-gray-800 text-xs py-1.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300 min-w-[130px] sm:min-w-[160px]">
                            <option value="all" {{ (string)$selectedUbsId === 'all' ? 'selected' : '' }}>Semua Gudang</option>
                            @foreach($allUbs as $u)
                                <option value="{{ $u->id }}" {{ (string)$selectedUbsId === (string)$u->id ? 'selected' : '' }}>
                                    {{ $u->nama_ubs }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <label class="text-[11px] sm:text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Bulan</label>
                        <select name="bulan" class="rounded-lg border-gray-300 bg-white text-gray-800 text-xs py-1.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300 min-w-[110px] sm:min-w-[130px]">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ (int)$bulan === $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <label class="text-[11px] sm:text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Tahun</label>
                        <select name="tahun" class="rounded-lg border-gray-300 bg-white text-gray-800 text-xs py-1.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300 min-w-[80px] sm:min-w-[90px]">
                            @php $currentYear = now()->year; @endphp
                            @foreach(range($currentYear - 3, $currentYear + 1) as $y)
                                <option value="{{ $y }}" {{ (int)$tahun === $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit"
                        class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-700 transition focus:ring-4 focus:ring-blue-300 active:scale-95 shadow-sm">
                        Tampilkan
                    </button>

                    <a href="{{ route('gudang.rekapNotaMasuk.index') }}"
                        class="inline-flex items-center gap-1 rounded-lg bg-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-300 transition dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                        Reset
                    </a>
                </form>
            </div>

            {{-- Live Search Input & Checklist & Legend --}}
            <div class="mb-3 flex flex-col xl:flex-row xl:items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-3 w-full xl:w-auto">
                    <div class="relative w-full sm:w-80">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" id="search-rekap-input" onkeyup="filterRekapTable()" placeholder="Cari kode / nama barang..."
                            class="w-full pl-9 pr-3 py-2 text-xs bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                    </div>

                    <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-semibold text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800/50 px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 shadow-2xs">
                        <input type="checkbox" name="hanya_transaksi" id="filter-hanya-transaksi" value="1" form="filter-rekap-form" {{ (request('hanya_transaksi') || !empty($hanyaTransaksi)) ? 'checked' : '' }}
                            onchange="filterRekapTable(); document.getElementById('filter-rekap-form').submit();"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-700">
                        <span>Tampilkan Barang Ber-Transaksi Saja</span>
                    </label>
                </div>

                {{-- Legend Indicator --}}
                <div class="flex flex-wrap items-center gap-2.5 sm:gap-4 text-[11px] sm:text-xs font-semibold">
                    <div class="flex items-center gap-1.5">
                        <span class="inline-block w-2.5 h-2.5 sm:w-3 sm:h-3 rounded bg-slate-700"></span>
                        <span class="text-slate-700 dark:text-slate-300 font-bold">Stok Awal</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="inline-block w-2.5 h-2.5 sm:w-3 sm:h-3 rounded bg-blue-600"></span>
                        <span class="text-blue-700 dark:text-blue-400 font-bold">Biru: Masuk (+)</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="inline-block w-2.5 h-2.5 sm:w-3 sm:h-3 rounded bg-red-500"></span>
                        <span class="text-red-700 dark:text-red-400 font-bold">Merah: Keluar (-)</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="inline-block w-2.5 h-2.5 sm:w-3 sm:h-3 rounded bg-emerald-600"></span>
                        <span class="text-emerald-700 dark:text-emerald-400 font-bold">Hijau: Stok Gudang</span>
                    </div>
                </div>
            </div>

            {{-- Mobile Swipe Hint --}}
            <div class="text-[11px] font-semibold text-blue-700 dark:text-blue-300 bg-blue-50/80 dark:bg-blue-900/40 px-3 py-1.5 rounded-lg border border-blue-200 dark:border-blue-800/60 flex items-center gap-1.5 sm:hidden mb-2">
                <span>👉 <strong>Petunjuk Mobile:</strong> Geser tabel ke kanan / kiri untuk melihat pergerakan tanggal 1 s/d {{ $daysInMonth }}.</span>
            </div>

            {{-- Tabel Matrix Scrollable dengan Touch-Pan Smooth Horizontal Scroll & Sticky Left Columns --}}
            <div class="overflow-x-auto touch-pan-x custom-scrollbar rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm max-h-[680px] overflow-y-auto" style="-webkit-overflow-scrolling: touch;">
                <table class="w-full text-xs text-left text-gray-700 dark:text-gray-300 border-collapse min-w-[1100px]">
                    <thead class="bg-gray-200 dark:bg-gray-800 text-gray-800 dark:text-gray-200 uppercase font-bold sticky top-0 z-30">
                        <tr>
                            <th class="px-2.5 sm:px-3 py-2.5 border-r border-b border-gray-300 dark:border-gray-700 sticky left-0 z-40 bg-gray-200 dark:bg-gray-800 min-w-[90px] sm:min-w-[110px] shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">
                                Kode Barang
                            </th>
                            <th class="px-2.5 sm:px-3 py-2.5 border-r border-b border-gray-300 dark:border-gray-700 sticky left-[90px] sm:left-[110px] z-40 bg-gray-200 dark:bg-gray-800 min-w-[140px] sm:min-w-[200px] shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">
                                Nama Barang
                            </th>
                            <th class="px-1.5 sm:px-2 py-2.5 border-r border-b border-gray-300 dark:border-gray-700 text-center min-w-[60px] sm:min-w-[65px]">
                                Satuan
                            </th>
                            <th class="px-2 sm:px-2.5 py-2.5 border-r border-b border-gray-300 dark:border-gray-700 bg-slate-800 text-white text-center font-extrabold min-w-[80px] sm:min-w-[90px]">
                                Stok Awal
                            </th>
                            @for($d = 1; $d <= $daysInMonth; $d++)
                                <th class="px-1 py-2.5 border-r border-b border-gray-300 dark:border-gray-700 text-center min-w-[42px] sm:min-w-[44px] {{ $d == now()->day && (int)$bulan == now()->month && (int)$tahun == now()->year ? 'bg-blue-600 text-white font-extrabold' : '' }}">
                                    {{ $d }}
                                </th>
                            @endfor
                            <th class="px-2 sm:px-2.5 py-2.5 border-r border-b border-gray-300 dark:border-gray-700 bg-blue-700 text-white text-center font-extrabold min-w-[80px] sm:min-w-[90px]">
                                Tot. Masuk
                            </th>
                            <th class="px-2 sm:px-2.5 py-2.5 border-r border-b border-gray-300 dark:border-gray-700 bg-red-600 text-white text-center font-extrabold min-w-[80px] sm:min-w-[90px]">
                                Tot. Keluar
                            </th>
                            <th class="px-2 sm:px-2.5 py-2.5 border-b border-gray-300 dark:border-gray-700 bg-emerald-600 text-white text-center font-extrabold min-w-[95px] sm:min-w-[105px]">
                                Stok Gudang
                            </th>
                        </tr>
                    </thead>
                    <tbody id="table-rekap-body">
                        @forelse($rekapMatrix as $item)
                            @php
                                $hasMonthTx = ($item['total_masuk'] > 0 || $item['total_keluar'] > 0);
                            @endphp
                            <tr data-has-tx="{{ $hasMonthTx ? 'true' : 'false' }}" class="group hover:bg-gray-100/70 dark:hover:bg-gray-800/60 border-b border-gray-200 dark:border-gray-800 transition-colors">
                                {{-- Sticky Kode Barang --}}
                                <td class="px-2.5 sm:px-3 py-2 font-mono font-bold text-gray-900 border-r border-gray-300 dark:text-white dark:border-gray-800 sticky left-0 z-20 bg-white dark:bg-gray-900 group-hover:bg-gray-100 dark:group-hover:bg-gray-800 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)] text-[11px] sm:text-xs">
                                    {{ $item['kode_barang'] }}
                                </td>
                                {{-- Sticky Nama Barang --}}
                                <td class="px-2.5 sm:px-3 py-2 font-medium text-gray-900 border-r border-gray-300 dark:text-white dark:border-gray-800 sticky left-[90px] sm:left-[110px] z-20 bg-white dark:bg-gray-900 group-hover:bg-gray-100 dark:group-hover:bg-gray-800 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)] truncate max-w-[140px] sm:max-w-[240px] text-[11px] sm:text-xs" title="{{ $item['nama_barang'] }}">
                                    {{ $item['nama_barang'] }}
                                </td>
                                <td class="px-1.5 sm:px-2 py-2 text-center border-r border-gray-300 dark:border-gray-800">
                                    <span class="px-1 py-0.5 bg-gray-100 dark:bg-gray-800 rounded text-[10px] sm:text-[11px] font-semibold text-gray-600 dark:text-gray-400">{{ $item['nama_satuan'] }}</span>
                                </td>

                                {{-- Stok Awal (Cutoff Awal Bulan) --}}
                                <td class="px-2 sm:px-2.5 py-2 text-center bg-slate-50 dark:bg-slate-900/40 font-bold text-slate-800 dark:text-slate-200 border-r border-gray-300 dark:border-gray-800 text-[11px] sm:text-xs">
                                    {{ rtrim(rtrim(number_format($item['stok_awal'], 2, ',', '.'), '0'), ',') }}
                                </td>
                                
                                {{-- Loop Days 1..N --}}
                                @for($d = 1; $d <= $daysInMonth; $d++)
                                    @php
                                        $mVal = $item['masuk'][$d];
                                        $kVal = $item['keluar'][$d];
                                        $txList = $item['transactions'][$d] ?? [];
                                        $rawDate = sprintf('%04d-%02d-%02d', $tahun, $bulan, $d);
                                        $hasActivity = ($mVal > 0 || $kVal > 0);
                                    @endphp
                                    <td class="px-0.5 sm:px-1 py-1.5 text-center border-r border-gray-300 dark:border-gray-800 align-middle {{ $hasActivity ? 'cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-700 transition' : 'text-gray-300 dark:text-gray-700' }}"
                                        @if($hasActivity)
                                            @click="openTxModal('{{ addslashes($item['nama_barang']) }}', '{{ $d }}', {{ json_encode($txList) }}, '{{ $rawDate }}')"
                                            title="Klik untuk rincian {{ count($txList) }} transaksi pada tgl {{ $d }}"
                                        @endif>

                                        @if(!$hasActivity)
                                            <span class="text-gray-300 dark:text-gray-700 text-[11px] sm:text-xs">-</span>
                                        @else
                                            <div class="flex flex-col gap-0.5 items-center justify-center">
                                                {{-- Barang Masuk (Atas - Biru) --}}
                                                @if($mVal > 0)
                                                    <span class="inline-block px-1 py-0.5 w-full bg-blue-100 dark:bg-blue-900/60 text-blue-800 dark:text-blue-200 font-extrabold text-[9px] sm:text-[10px] rounded leading-tight shadow-2xs">
                                                        +{{ rtrim(rtrim(number_format($mVal, 2, ',', '.'), '0'), ',') }}
                                                    </span>
                                                @endif

                                                {{-- Barang Keluar (Bawah - Merah) --}}
                                                @if($kVal > 0)
                                                    <span class="inline-block px-1 py-0.5 w-full bg-red-100 dark:bg-red-900/60 text-red-800 dark:text-red-200 font-extrabold text-[9px] sm:text-[10px] rounded leading-tight shadow-2xs">
                                                        -{{ rtrim(rtrim(number_format($kVal, 2, ',', '.'), '0'), ',') }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                @endfor

                                {{-- Total Masuk --}}
                                <td class="px-2 sm:px-2.5 py-2 text-center bg-blue-50 dark:bg-blue-950/30 font-extrabold text-blue-700 dark:text-blue-400 border-r border-gray-300 dark:border-gray-800 text-[11px] sm:text-xs">
                                    {{ $item['total_masuk'] > 0 ? '+' . rtrim(rtrim(number_format($item['total_masuk'], 2, ',', '.'), '0'), ',') : '-' }}
                                </td>

                                {{-- Total Keluar --}}
                                <td class="px-2 sm:px-2.5 py-2 text-center bg-red-50 dark:bg-red-950/30 font-extrabold text-red-700 dark:text-red-400 border-r border-gray-300 dark:border-gray-800 text-[11px] sm:text-xs">
                                    {{ $item['total_keluar'] > 0 ? '-' . rtrim(rtrim(number_format($item['total_keluar'], 2, ',', '.'), '0'), ',') : '-' }}
                                </td>

                                {{-- Stok Akhir (Stok Realtime Gudang) --}}
                                <td class="px-2 sm:px-2.5 py-2 text-center bg-emerald-50 dark:bg-emerald-950/30 font-extrabold text-emerald-700 dark:text-emerald-400 text-[11px] sm:text-xs">
                                    {{ rtrim(rtrim(number_format($item['stok_akhir'], 2, ',', '.'), '0'), ',') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $daysInMonth + 6 }}" class="px-4 py-8 text-center text-sm text-gray-500 italic bg-gray-50 dark:bg-gray-800/20 dark:text-gray-400">
                                    Tidak ada data pergerakan barang pada bulan {{ \Carbon\Carbon::create()->month((int)$bulan)->translatedFormat('F') }} {{ $tahun }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    {{-- Modal Detail Rincian Transaksi Masuk / Keluar --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4" x-transition>
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl max-w-xl w-full p-4 sm:p-5 border border-gray-200 dark:border-gray-800" @click.outside="showModal = false">
            <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-800 pb-3 mb-4">
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white" x-text="selectedBarang"></h3>
                    <p class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Rincian Mutasi Barang Tanggal <span class="font-bold text-blue-600 dark:text-blue-400" x-text="selectedDate"></span>
                    </p>
                </div>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-lg">
                    ✕
                </button>
            </div>

            <div class="space-y-4">
                <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800 max-h-[320px]">
                    <table class="w-full text-xs text-left text-gray-700 dark:text-gray-300">
                        <thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold uppercase sticky top-0">
                            <tr>
                                <th class="px-2.5 py-2 border-b border-gray-200 dark:border-gray-700">Tipe Mutasi</th>
                                <th class="px-2.5 py-2 border-b border-gray-200 dark:border-gray-700">No. Dokumen / Jenis</th>
                                <th class="px-2.5 py-2 border-b border-gray-200 dark:border-gray-700">Lokasi</th>
                                <th class="px-2.5 py-2 border-b border-gray-200 dark:border-gray-700 text-right">Jumlah</th>
                                <th class="px-2.5 py-2 border-b border-gray-200 dark:border-gray-700 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(t, i) in selectedTransactions" :key="i">
                                <tr class="hover:bg-gray-50 border-b border-gray-100 dark:hover:bg-gray-800/40 dark:border-gray-800">
                                    <td class="px-2.5 py-2">
                                        <span class="px-1.5 py-0.5 rounded font-extrabold text-[9px] sm:text-[10px]"
                                            :class="t.tipe === 'masuk' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-200' : 'bg-red-100 text-red-800 dark:bg-red-900/60 dark:text-red-200'"
                                            x-text="t.tipe === 'masuk' ? 'MASUK (+)' : 'KELUAR (-)'">
                                        </span>
                                    </td>
                                    <td class="px-2.5 py-2">
                                        <div class="font-mono font-bold text-gray-900 dark:text-white text-[11px] sm:text-xs" x-text="t.doc_number"></div>
                                        <div class="text-[10px] sm:text-[11px] text-gray-500 dark:text-gray-400" x-text="t.jenis_label"></div>
                                    </td>
                                    <td class="px-2.5 py-2 text-gray-800 dark:text-gray-200 text-[11px] sm:text-xs" x-text="t.lokasi"></td>
                                    <td class="px-2.5 py-2 text-right font-extrabold text-[11px] sm:text-xs"
                                        :class="t.tipe === 'masuk' ? 'text-blue-700 dark:text-blue-400' : 'text-red-700 dark:text-red-400'"
                                        x-text="(t.tipe === 'masuk' ? '+' : '-') + t.qty.toLocaleString('id-ID') + ' ' + t.satuan">
                                    </td>
                                    <td class="px-2.5 py-2 text-center">
                                        <a :href="t.url" target="_blank" class="inline-flex items-center gap-1 px-2 py-1 bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 rounded font-bold text-[10px] sm:text-[11px] transition">
                                            Lihat ↗
                                        </a>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-between gap-2 pt-2 border-t border-gray-100 dark:border-gray-800">
                    <a href="{{ route('gudang.auditLog.index') }}" 
                       class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-semibold flex items-center gap-1">
                        <span>🔍 Buka Audit Log Stok Lengkap</span>
                    </a>

                    <button @click="showModal = false" class="w-full sm:w-auto px-4 py-1.5 text-xs font-semibold text-gray-700 bg-gray-200 dark:bg-gray-800 dark:text-gray-300 rounded-lg hover:bg-gray-300 transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
<!-- ===== Main Content End ===== -->

<script>
    function filterRekapTable() {
        let input = document.getElementById('search-rekap-input');
        let checkbox = document.getElementById('filter-hanya-transaksi');
        let filter = input ? input.value.toLowerCase() : '';
        let hanyaTx = checkbox ? checkbox.checked : false;
        let rows = document.querySelectorAll('#table-rekap-body tr');
        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            let hasTx = row.getAttribute('data-has-tx') === 'true';
            let matchesSearch = text.includes(filter);
            let matchesTx = !hanyaTx || hasTx;
            row.style.display = (matchesSearch && matchesTx) ? '' : 'none';
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        filterRekapTable();
    });
</script>
@endsection
