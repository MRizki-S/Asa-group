@extends('layouts.app')

@section('pageActive', 'PengajuanPembatalan')

@section('content')
    <!-- ===== Main Content Start ===== -->
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: 'PengajuanPembatalan' }">
            @include('partials.breadcrumb')
        </div>
        <!-- Breadcrumb End -->

        {{-- Alert Error Validasi --}}
        @if ($errors->any())
            <div class="flex p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400"
                role="alert">
                <svg class="shrink-0 inline w-4 h-4 me-3 mt-[2px]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                    fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                </svg>
                <span class="sr-only">Danger</span>
                <div>
                    <span class="font-medium">Terjadi kesalahan validasi:</span>
                    <ul class="mt-1.5 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif


        <div class="space-y-5 sm:space-y-6">
            {{-- 🏘️ Kalau global, tampilkan perumahaan secara terpisah --}}
            @if ($isGlobal)
                @if ($pengajuanPembatalan->isEmpty())
                    {{-- 💡 Jika global tapi tidak ada data sama sekali --}}
                    <div
                        class="rounded-2xl border border-gray-200 px-5 py-4 sm:px-6 sm:py-5 bg-white text-center text-gray-500
            dark:border-gray-800 dark:bg-white/[0.03]">
                        Tidak ada data pengajuan pembatalan unit.
                    </div>
                @endif

                @foreach ($pengajuanPembatalan as $perumahaanId => $items)
                    @php
                        $namaPerumahaan =
                            $items->first()?->pemesananUnit?->perumahaan?->nama_perumahaan ?? 'Tidak Diketahui';
                    @endphp

                    <div
                        class="rounded-2xl border border-gray-200 px-5 py-4 sm:px-6 sm:py-5 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-5">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                                Pengajuan Pembatalan Unit - {{ $namaPerumahaan }}
                            </h3>
                        </div>

                        <table id="table-pengajuanPembatalan"
                            class="min-w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-3">Nama Customer</th>
                                    <th class="px-4 py-3">Unit Dipesan</th>
                                    <th class="px-4 py-3">Sales</th>
                                    <th class="px-4 py-3">Tanggal Pengajuan</th>
                                    <th class="px-4 py-3 text-center">Status Project Manager</th>
                                    <th class="px-4 py-3 text-center">Status Manager Keuangan</th>
                                    <th class="px-4 py-3 text-center">Status Pengajuan Akhir</th>
                                    <th class="px-4 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($items as $item)
                                    <tr
                                        class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                        <td class="px-4 py-2 font-medium text-gray-900 dark:text-white">
                                            {{ Str::upper($item->pemesananUnit?->customer?->username ?? '-') }}
                                        </td>
                                        <td class="px-4 py-2">
                                            {{ $item->pemesananUnit?->unit?->nama_unit ?? '-' }}
                                        </td>
                                        <td class="px-4 py-2">
                                            {{ Str::upper($item->pemesananUnit?->sales?->username ?? '-') }}
                                        </td>
                                        <td class="px-4 py-2">
                                            {{ \Carbon\Carbon::parse($item->tanggal_pengajuan)->format('d M Y') }}
                                        </td>

                                        @php
                                            // Fungsi kecil buat tentuin warna status
                                            function statusColor($status)
                                            {
                                                $status = strtolower($status ?? 'pending');
                                                return match ($status) {
                                                    'acc', 'disetujui', 'setuju' => [
                                                        'bg-green-100',
                                                        'border-green-500',
                                                        'text-green-700',
                                                    ],
                                                    'tolak', 'ditolak', 'reject' => [
                                                        'bg-red-100',
                                                        'border-red-500',
                                                        'text-red-700',
                                                    ],
                                                    default => [
                                                        'bg-yellow-100',
                                                        'border-yellow-500',
                                                        'text-yellow-700',
                                                    ], // pending
                                                };
                                            }

                                            // Ambil semua status
                                            $statusPemasaran = strtolower($item->status_manager_pemasaran ?? 'pending');
                                            $statusKeuangan = strtolower($item->status_mgr_keuangan ?? 'pending');
                                            $statusAkhir = strtolower($item->status_pengajuan ?? 'pending');

                                            // Deklarasi warna (reuse function)
                                            [$bgPemasaran, $borderPemasaran, $textPemasaran] = statusColor(
                                                $statusPemasaran,
                                            );
                                            [$bgKeuangan, $borderKeuangan, $textKeuangan] = statusColor(
                                                $statusKeuangan,
                                            );
                                            [$bgAkhir, $borderAkhir, $textAkhir] = statusColor($statusAkhir);
                                        @endphp

                                        <td class="px-4 py-2 text-center">
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full border {{ $bgPemasaran }} {{ $borderPemasaran }} {{ $textPemasaran }}">
                                                {{ $statusPemasaran }}
                                            </span>
                                        </td>

                                        <td class="px-4 py-2 text-center">
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full border {{ $bgKeuangan }} {{ $borderKeuangan }} {{ $textKeuangan }}">
                                                {{ $statusKeuangan }}
                                            </span>
                                        </td>

                                        <td class="px-4 py-2 text-center">
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full border {{ $bgAkhir }} {{ $borderAkhir }} {{ $textAkhir }}">
                                                {{ $statusAkhir }}
                                            </span>
                                        </td>


                                        {{-- Aksi --}}
                                        <td class="px-4 py-2 text-center">
                                            <a href="{{ route('marketing.pengajuan-pembatalan.show', $item->id) }}"
                                                class="inline-flex items-center gap-1
                                    text-xs font-medium text-blue-700 bg-blue-100 hover:bg-blue-200
                                    dark:bg-blue-800 dark:text-blue-100 dark:hover:bg-blue-700
                                    px-2.5 py-1.5 rounded-md transition-colors duration-200
                                    focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1
                                    active:scale-95">
                                                Detail
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-4 py-3 text-center text-gray-500">
                                            Tidak ada pengajuan pembatalan pemesanan unit.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endforeach
            @else
                {{-- Jika bukan global --}}
                <div
                    class="rounded-2xl border border-gray-200 px-5 py-4 sm:px-6 sm:py-5 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                    <h3 class="mb-4 text-base font-medium text-gray-800 dark:text-white/90">
                        Pengajuan Pembatalan Unit - {{ $namaPerumahaan }}
                    </h3>

                    <table id="table-pengajuanPembatalan"
                        class="min-w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3">Nama Customer</th>
                                <th class="px-4 py-3">Unit Dipesan</th>
                                <th class="px-4 py-3">Sales</th>
                                <th class="px-4 py-3 text-center">Tanggal Pengajuan</th>
                                <th class="px-4 py-3 text-center">Status Project Manager</th>
                                <th class="px-4 py-3 text-center">Status Manager Keuangan</th>
                                <th class="px-4 py-3 text-center">Status Pengajuan Akhir</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pengajuanPembatalan as $item)
                                <tr
                                    class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                    <td class="px-4 py-2 font-medium text-gray-900 dark:text-white">
                                        {{ Str::upper($item->pemesananUnit?->customer?->username ?? '-') }}
                                    </td>
                                    <td class="px-4 py-2">
                                        {{ $item->pemesananUnit?->unit?->nama_unit ?? '-' }}
                                    </td>
                                    <td class="px-4 py-2">
                                        {{ Str::upper($item->pemesananUnit?->sales?->username ?? '-') }}
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        {{ \Carbon\Carbon::parse($item->tanggal_pengajuan)->format('d M Y') }}
                                    </td>
                                    @php
                                        // Fungsi kecil buat tentuin warna status
                                        function statusColor($status)
                                        {
                                            $status = strtolower($status ?? 'pending');
                                            return match ($status) {
                                                'acc', 'disetujui', 'setuju' => [
                                                    'bg-green-100',
                                                    'border-green-500',
                                                    'text-green-700',
                                                ],
                                                'tolak', 'ditolak', 'reject' => [
                                                    'bg-red-100',
                                                    'border-red-500',
                                                    'text-red-700',
                                                ],
                                                default => [
                                                    'bg-yellow-100',
                                                    'border-yellow-500',
                                                    'text-yellow-700',
                                                ], // pending
                                            };
                                        }

                                        // Ambil semua status
                                        $statusPemasaran = strtolower($item->status_mgr_pemasaran ?? 'pending');
                                        $statusKeuangan = strtolower($item->status_mgr_keuangan ?? 'pending');
                                        $statusAkhir = strtolower($item->status_pengajuan ?? 'pending');

                                        // Deklarasi warna (reuse function)
                                        [$bgPemasaran, $borderPemasaran, $textPemasaran] = statusColor(
                                            $statusPemasaran,
                                        );
                                        [$bgKeuangan, $borderKeuangan, $textKeuangan] = statusColor($statusKeuangan);
                                        [$bgAkhir, $borderAkhir, $textAkhir] = statusColor($statusAkhir);
                                    @endphp

                                    <td class="px-4 py-2 text-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full border {{ $bgPemasaran }} {{ $borderPemasaran }} {{ $textPemasaran }}">
                                            {{ $statusPemasaran }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-2 text-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full border {{ $bgKeuangan }} {{ $borderKeuangan }} {{ $textKeuangan }}">
                                            {{ $statusKeuangan }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-2 text-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full border {{ $bgAkhir }} {{ $borderAkhir }} {{ $textAkhir }}">
                                            {{ $statusAkhir }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-2 text-center">
                                        <a href="{{ route('marketing.pengajuan-pembatalan.show', $item->id) }}"
                                            class="inline-flex items-center gap-1
                                text-xs font-medium text-blue-700 bg-blue-100 hover:bg-blue-200
                                dark:bg-blue-800 dark:text-blue-100 dark:hover:bg-blue-700
                                px-2.5 py-1.5 rounded-md transition-colors duration-200
                                focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1
                                active:scale-95">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-3 text-center text-gray-500">
                                        Tidak ada pengajuan pembatalan pemesanan unit.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif

        </div>
    </div>
    <!-- ===== Main Content End ===== -->

    {{-- sweatalert 2 for delete data --}}
    <script>
        if (document.getElementById("table-pengajuanPembatalan") && typeof simpleDatatables.DataTable !== 'undefined') {
            const dataTable = new simpleDatatables.DataTable("#table-pengajuanPembatalan", {
                searchable: true,
                sortable: true,
            });
        }
    </script>
@endsection
