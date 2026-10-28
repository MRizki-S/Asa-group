@extends('layouts.app')

@section('pageActive', 'PengajuanPembatalan')

@section('content')
    @php
        if (!function_exists('statusColorRiwayat')) {
            function statusColorRiwayat($status)
            {
                $status = strtolower($status ?? 'pending');
                return match ($status) {
                    'acc', 'disetujui', 'setuju' => [
                        'bg-green-100 dark:bg-green-900/40',
                        'border-green-500',
                        'text-green-700 dark:text-green-300',
                    ],
                    'tolak', 'ditolak', 'reject' => [
                        'bg-red-100 dark:bg-red-900/40',
                        'border-red-500',
                        'text-red-700 dark:text-red-300',
                    ],
                    default => [
                        'bg-yellow-100 dark:bg-yellow-900/40',
                        'border-yellow-500',
                        'text-yellow-700 dark:text-yellow-300',
                    ],
                };
            }
        }
    @endphp

    <!-- ===== Main Content Start ===== -->
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: 'RiwayatPembatalan' }">
            @include('partials.breadcrumb')
        </div>
        <!-- Breadcrumb End -->

        <!-- Action Bar Start -->
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3 bg-white dark:bg-white/[0.03] p-4 rounded-2xl border border-gray-200 dark:border-gray-800">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-gray-800 dark:text-white">Riwayat Pembatalan Unit</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Daftar arsip seluruh pengajuan pembatalan pemesanan unit yang telah diproses</p>
                </div>
            </div>
            <div>
                <a href="{{ route('marketing.pengajuan-pembatalan.listPengajuan') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 rounded-xl transition shadow-sm active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Kembali ke Pengajuan</span>
                </a>
            </div>
        </div>
        <!-- Action Bar End -->

        {{-- 🔍 Filter Bulan & Tahun --}}
        <form method="GET" action="{{ route('marketing.pengajuan-pembatalan.riwayat') }}"
            class="mb-6 flex flex-wrap items-end gap-3 bg-white dark:bg-white/[0.03] p-4 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm">

            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Bulan</label>
                <select name="bulan"
                    class="rounded-xl border border-gray-300 bg-gray-50 dark:bg-gray-800 text-gray-800 text-sm focus:ring-emerald-500 focus:border-emerald-500 dark:border-gray-700 dark:text-gray-300 min-w-[160px] p-2.5">
                    <option value="">Semua Bulan</option>
                    @php
                        $namaBulan = [
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                        ];
                    @endphp
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ (string)$bulan === (string)$m ? 'selected' : '' }}>
                            {{ $namaBulan[$m] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Tahun</label>
                <select name="tahun"
                    class="rounded-xl border border-gray-300 bg-gray-50 dark:bg-gray-800 text-gray-800 text-sm focus:ring-emerald-500 focus:border-emerald-500 dark:border-gray-700 dark:text-gray-300 min-w-[130px] p-2.5">
                    @php $currentYear = (int)date('Y'); @endphp
                    @foreach(range($currentYear - 4, $currentYear + 2) as $y)
                        <option value="{{ $y }}" {{ (int)$tahun === $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition focus:ring-4 focus:ring-emerald-300 active:scale-95 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    <span>Filter</span>
                </button>

                @if($bulan !== '' || (int)$tahun !== (int)date('Y'))
                    <a href="{{ route('marketing.pengajuan-pembatalan.riwayat') }}"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 transition">
                        Reset
                    </a>
                @endif
            </div>

            <div class="ml-auto text-xs text-gray-500 dark:text-gray-400 self-center">
                Periode:
                <span class="font-semibold text-gray-700 dark:text-gray-200">
                    {{ $bulan ? ($namaBulan[(int)$bulan] ?? '') . ' ' . $tahun : 'Sepanjang Tahun ' . $tahun }}
                </span>
            </div>
        </form>

        <div class="space-y-5 sm:space-y-6">
            {{-- 🏘️ Kalau global, tampilkan perumahaan secara terpisah --}}
            @if ($isGlobal)
                @if ($riwayatPembatalan->isEmpty())
                    <div class="rounded-2xl border border-gray-200 px-5 py-8 sm:px-6 bg-white text-center text-gray-500 dark:border-gray-800 dark:bg-white/[0.03]">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="font-medium">Belum ada riwayat pembatalan unit.</p>
                    </div>
                @endif

                @foreach ($riwayatPembatalan as $perumahaanId => $items)
                    @php
                        $namaPerum = $items->first()?->pemesananUnit?->perumahaan?->nama_perumahaan ?? 'Tidak Diketahui';
                    @endphp

                    <div class="rounded-2xl border border-gray-200 px-5 py-4 sm:px-6 sm:py-5 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-5">
                        <div class="mb-4 flex items-center justify-between border-b pb-3 dark:border-gray-700">
                            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                                Riwayat Pembatalan Unit - {{ $namaPerum }}
                            </h3>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                Total: {{ $items->count() }} Data
                            </span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="table-riwayat-pembatalan min-w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead class="text-xs uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th class="px-4 py-3">Nama Customer</th>
                                        <th class="px-4 py-3">Unit Dipesan</th>
                                        <th class="px-4 py-3">Sales / Agent</th>
                                        <th class="px-4 py-3 text-center">Tanggal Dibatalkan</th>
                                        <th class="px-4 py-3 text-center">Status PM</th>
                                        <th class="px-4 py-3 text-center">Status Keuangan</th>
                                        <th class="px-4 py-3 text-center">Status Akhir</th>
                                        @can('marketing.pengajuan-pembatalan.read')
                                            <th class="px-4 py-3 text-center">Aksi</th>
                                        @endcan
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($items as $item)
                                        @php
                                            $custName = $item->pemesananUnit?->dataDiri?->nama_pribadi
                                                ?? (!empty(trim($item->pemesananUnit?->customer?->nama_lengkap ?? '')) ? trim($item->pemesananUnit?->customer?->nama_lengkap) : null)
                                                ?? $item->pemesananUnit?->customer?->username
                                                ?? '-';

                                            $isAgent = ($item->pemesananUnit?->source === 'agent' && $item->pemesananUnit?->agent);
                                            $salesOrAgent = $isAgent
                                                ? ($item->pemesananUnit->agent->nama_agent ?? '-')
                                                : (!empty(trim($item->pemesananUnit?->sales?->nama_lengkap ?? ''))
                                                    ? trim($item->pemesananUnit?->sales?->nama_lengkap)
                                                    : ($item->pemesananUnit?->sales?->username ?? '-'));

                                            $statusPM = strtolower($item->status_mgr_pemasaran ?? 'pending');
                                            $statusKeuangan = strtolower($item->status_mgr_keuangan ?? 'pending');
                                            $statusAkhir = strtolower($item->status_pengajuan ?? 'pending');

                                            [$bgPM, $borderPM, $textPM] = statusColorRiwayat($statusPM);
                                            [$bgKeu, $borderKeu, $textKeu] = statusColorRiwayat($statusKeuangan);
                                            [$bgAkhir, $borderAkhir, $textAkhir] = statusColorRiwayat($statusAkhir);
                                        @endphp
                                        <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                                {{ Str::upper($custName) }}
                                            </td>
                                            <td class="px-4 py-3 font-semibold text-gray-800 dark:text-gray-200">
                                                {{ $item->pemesananUnit?->unit?->nama_unit ?? '-' }}
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="font-medium text-gray-800 dark:text-gray-200">
                                                        {{ Str::upper(Str::words($salesOrAgent, 2, '')) }}
                                                    </span>
                                                    @if ($isAgent)
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                                            Agent
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                                {{ \Carbon\Carbon::parse($item->tanggal_pengajuan)->format('d M Y') }}
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-full border {{ $bgPM }} {{ $borderPM }} {{ $textPM }}">
                                                    {{ strtoupper($statusPM) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-full border {{ $bgKeu }} {{ $borderKeu }} {{ $textKeu }}">
                                                    {{ strtoupper($statusKeuangan) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-full border {{ $bgAkhir }} {{ $borderAkhir }} {{ $textAkhir }}">
                                                    {{ strtoupper($statusAkhir) }}
                                                </span>
                                            </td>
                                            @can('marketing.pengajuan-pembatalan.read')
                                                <td class="px-4 py-3 text-center">
                                                    <a href="{{ route('marketing.pengajuan-pembatalan.show', $item->id) }}"
                                                        class="inline-flex items-center gap-1 text-xs font-medium text-blue-700 bg-blue-100 hover:bg-blue-200 dark:bg-blue-800 dark:text-blue-100 dark:hover:bg-blue-700 px-3 py-1.5 rounded-lg transition shadow-sm active:scale-95">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        </svg>
                                                        Detail
                                                    </a>
                                                </td>
                                            @endcan
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="px-4 py-4 text-center text-gray-500">
                                                Tidak ada riwayat pembatalan pemesanan unit.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach

            @else
                {{-- 🏠 Tampilan Non-Global (Single Perumahan) --}}
                <div class="rounded-2xl border border-gray-200 px-5 py-4 sm:px-6 sm:py-5 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="mb-4 flex items-center justify-between border-b pb-3 dark:border-gray-700">
                        <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                            Riwayat Pembatalan Unit - {{ $namaPerumahaan }}
                        </h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                            Total: {{ $riwayatPembatalan->count() }} Data
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="table-riwayat-pembatalan min-w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-3">Nama Customer</th>
                                    <th class="px-4 py-3">Unit Dipesan</th>
                                    <th class="px-4 py-3">Sales / Agent</th>
                                    <th class="px-4 py-3 text-center">Tanggal Dibatalkan</th>
                                    <th class="px-4 py-3 text-center">Status PM</th>
                                    <th class="px-4 py-3 text-center">Status Keuangan</th>
                                    <th class="px-4 py-3 text-center">Status Akhir</th>
                                    @can('marketing.pengajuan-pembatalan.read')
                                        <th class="px-4 py-3 text-center">Aksi</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($riwayatPembatalan as $item)
                                    @php
                                        $custName = $item->pemesananUnit?->dataDiri?->nama_pribadi
                                            ?? (!empty(trim($item->pemesananUnit?->customer?->nama_lengkap ?? '')) ? trim($item->pemesananUnit?->customer?->nama_lengkap) : null)
                                            ?? $item->pemesananUnit?->customer?->username
                                            ?? '-';

                                        $isAgent = ($item->pemesananUnit?->source === 'agent' && $item->pemesananUnit?->agent);
                                        $salesOrAgent = $isAgent
                                            ? ($item->pemesananUnit->agent->nama_agent ?? '-')
                                            : (!empty(trim($item->pemesananUnit?->sales?->nama_lengkap ?? ''))
                                                ? trim($item->pemesananUnit?->sales?->nama_lengkap)
                                                : ($item->pemesananUnit?->sales?->username ?? '-'));

                                        $statusPM = strtolower($item->status_mgr_pemasaran ?? 'pending');
                                        $statusKeuangan = strtolower($item->status_mgr_keuangan ?? 'pending');
                                        $statusAkhir = strtolower($item->status_pengajuan ?? 'pending');

                                        [$bgPM, $borderPM, $textPM] = statusColorRiwayat($statusPM);
                                        [$bgKeu, $borderKeu, $textKeu] = statusColorRiwayat($statusKeuangan);
                                        [$bgAkhir, $borderAkhir, $textAkhir] = statusColorRiwayat($statusAkhir);
                                    @endphp
                                    <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                            {{ Str::upper($custName) }}
                                        </td>
                                        <td class="px-4 py-3 font-semibold text-gray-800 dark:text-gray-200">
                                            {{ $item->pemesananUnit?->unit?->nama_unit ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-medium text-gray-800 dark:text-gray-200">
                                                    {{ Str::upper(Str::words($salesOrAgent, 2, '')) }}
                                                </span>
                                                @if ($isAgent)
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                                        Agent
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($item->tanggal_pengajuan)->format('d M Y') }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-full border {{ $bgPM }} {{ $borderPM }} {{ $textPM }}">
                                                {{ strtoupper($statusPM) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-full border {{ $bgKeu }} {{ $borderKeu }} {{ $textKeu }}">
                                                {{ strtoupper($statusKeuangan) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-full border {{ $bgAkhir }} {{ $borderAkhir }} {{ $textAkhir }}">
                                                {{ strtoupper($statusAkhir) }}
                                            </span>
                                        </td>
                                        @can('marketing.pengajuan-pembatalan.read')
                                            <td class="px-4 py-3 text-center">
                                                <a href="{{ route('marketing.pengajuan-pembatalan.show', $item->id) }}"
                                                    class="inline-flex items-center gap-1 text-xs font-medium text-blue-700 bg-blue-100 hover:bg-blue-200 dark:bg-blue-800 dark:text-blue-100 dark:hover:bg-blue-700 px-3 py-1.5 rounded-lg transition shadow-sm active:scale-95">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    Detail
                                                </a>
                                            </td>
                                        @endcan
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-4 py-4 text-center text-gray-500">
                                            Tidak ada riwayat pembatalan pemesanan unit.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <!-- ===== Main Content End ===== -->

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof simpleDatatables !== 'undefined' && typeof simpleDatatables.DataTable !== 'undefined') {
                document.querySelectorAll('.table-riwayat-pembatalan').forEach(function(table) {
                    new simpleDatatables.DataTable(table, {
                        searchable: true,
                        sortable: true,
                    });
                });
            }
        });
    </script>
@endsection
