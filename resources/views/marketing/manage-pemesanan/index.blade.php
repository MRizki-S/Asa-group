@extends('layouts.app')

@section('pageActive', 'ManagePemesanan')

@section('content')
    <!-- ===== Main Content Start ===== -->
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: 'ManagePemesanan' }">
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

        <div class="space-y-6">
            {{-- ==================== KPR SECTION ==================== --}}
            <div
                class="rounded-2xl border border-gray-200 bg-white px-6 py-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90 flex items-center gap-2">
                        <span class="px-2.5 py-1 font-medium bg-indigo-100 text-indigo-700 rounded-full">
                            Manage Pemesanan - KPR
                        </span>
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table id="table-managePemesananKpr" class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                <th class="px-4 py-3 w-[200px]">Nama User</th>
                                <th class="px-4 py-3">Unit</th>
                                <th class="px-4 py-3">Nama Sales</th>
                                @can('marketing.kelola-pemesanan.print-ppjb')
                                    <th class="px-4 py-3 text-center">PPJB</th>
                                @endcan

                                <th class="px-4 py-3 text-center">Kelengkapan Berkas</th>
                                <th class="px-4 py-3">Bank</th>
                                <th class="px-4 py-3 text-center">Progress Bangunan</th>
                                <th class="px-4 py-3 text-center">Status KPR</th>
                                <th class="px-4 py-3">Status Unit Pemesanan</th>
                                @can('marketing.kelola-pemesanan.update-berkas')
                                    <th class="px-4 py-3 text-center">Update Data KPR</th>
                                @endcan

                                {{-- @can('marketing.kelola-pemesanan.pengajuan-adendum')
                                    <th class="px-4 py-3 text-center">Adendum</th>
                                @endcan --}}

                                @can('marketing.kelola-pemesanan.tagihan.read')
                                    <th class="px-4 py-3 text-center">Rincian Tagihan</th>
                                @endcan

                               @can('marketing.kelola-pemesanan.pengajuan-pembatalan')
                                    <th class="px-4 py-3 text-center">Pengajuan Pembatalan</th>
                                @endcan
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($pemesananKpr as $item)
                                <tr class="border-b hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                    <td class="px-4 py-2 font-medium text-gray-800 dark:text-white truncate max-w-[200px]">
                                        {{ $item->customer->username ?? '-' }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">{{ $item->unit->nama_unit ?? '-' }}</td>
                                    <td class="px-4 py-2">{{ $item->sales->username ?? '-' }}</td>

                                    @can('marketing.kelola-pemesanan.print-ppjb')
                                        <td class="px-4 py-2 text-center">
                                            <a href="{{ route('ppjbKPR.export.word', $item->id) }}"
                                                class="inline-flex items-center px-3 py-1 text-white bg-blue-600 rounded hover:bg-blue-700 transition">
                                                PPJB
                                            </a>
                                        </td>
                                    @endcan

                                    <td class="px-4 py-2 text-center">
                                        <span class="text-gray-600">{{ $item->kelengkapan_berkas }}</span>
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        @if (!empty($item->kpr->bank->kode_bank))
                                            <span
                                                class="inline-block px-2 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded">
                                                {{ $item->kpr->bank->kode_bank }}
                                            </span>
                                        @else
                                            <span
                                                class="inline-block px-2 py-1 text-xs font-medium text-gray-500 bg-gray-100 rounded">
                                                -
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-2 text-center">{{ $item->progress_bangunan ?? '' }}</td>
                                    <td class="px-4 py-2 text-center">
                                        <span
                                            class="px-2 py-1 rounded font-medium
                                        {{ $item->kpr->status_kpr === 'acc'
                                            ? 'bg-green-100 text-green-700'
                                            : ($item->kpr->status_kpr === 'proses'
                                                ? 'bg-yellow-100 text-yellow-700'
                                                : 'bg-gray-100 text-gray-700') }}">
                                            {{ ucfirst($item->kpr->status_kpr ?? '-') }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        @php
                                            $status = $item->status_pemesanan ?? '-';
                                            $classes = match ($status) {
                                                'proses' => 'bg-yellow-100 text-yellow-800 px-2 py-1 rounded',
                                                'LPA' => 'bg-blue-100 text-blue-800 px-2 py-1 rounded',
                                                'serah_terima' => 'bg-green-100 text-green-800 px-2 py-1 rounded',
                                                default => 'bg-gray-100 text-gray-600 px-2 py-1 rounded',
                                            };
                                        @endphp
                                        <span class="{{ $classes }}">{{ ucfirst($status) }}</span>
                                    </td>

                                    {{-- lihat berkas kpr dan update (khusus staff kpr) --}}
                                    @can('marketing.kelola-pemesanan.update-berkas')
                                        <td class="px-4 py-2 text-center">
                                            <a href="{{ route('marketing.kelengkapanBerkasKpr.editKpr', $item->id) }}"
                                                class="inline-flex items-center gap-1 px-3 py-1 text-white bg-indigo-600 rounded hover:bg-indigo-700 transition whitespace-nowrap">
                                                <i class="ri-edit-line"></i>
                                                <span>Update Data</span>
                                            </a>
                                        </td>
                                    @endcan


                                    {{-- 🟡 Pengajuan Adendum --}}
                                    {{-- @can('marketing.kelola-pemesanan.pengajuan-adendum')
                                        <td class="px-4 py-2 text-center">
                                            <button
                                                class="inline-flex items-center gap-1 px-3 py-1 text-white bg-orange-500 rounded hover:bg-orange-600 transition">
                                                <i class="ri-repeat-line"></i>Adendum
                                            </button>
                                        </td>
                                    @endcan --}}


                                    {{-- 🟢 Rincian Tagihan --}}
                                    @can('marketing.kelola-pemesanan.tagihan.read')
                                        <td class="px-4 py-2 text-center">
                                            <a href="{{ route('marketing.rincianTagihan', $item->id) }}"
                                                class="inline-flex items-center gap-1 px-3 py-1 text-white bg-green-600 rounded hover:bg-green-700 transition">
                                                <i class="ri-file-list-3-line"></i> Lihat
                                            </a>
                                        </td>
                                    @endcan



                                    @can(abilities: 'marketing.kelola-pemesanan.pengajuan-pembatalan')
                                        {{-- 🔴 Pengajuan Pembatalan --}}
                                        <td class="px-4 py-2 text-center">
                                            <button data-modal-target="modal-pembatalan" data-modal-toggle="modal-pembatalan"
                                                data-id="{{ $item->id }}"
                                                data-nama-unit="{{ $item->unit->nama_unit ?? '-' }}"
                                                data-nama-user="{{ $item->customer->username ?? '-' }}"
                                                data-cara-bayar="{{ ucfirst($item->cara_bayar) }}"
                                                data-no-hp="{{ $item->customer->no_hp ?? '-' }}"
                                                class="inline-flex items-center gap-1 px-3 py-1 text-white bg-red-600 rounded hover:bg-red-700 transition">
                                                <i class="ri-close-circle-line"></i> Pembatalan
                                            </button>
                                        </td>
                                    @endcan

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>



            {{-- ==================== CASH SECTION ==================== --}}
            <div
                class="rounded-2xl border border-gray-200 bg-white px-6 py-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90 flex items-center gap-2">
                        <span class="px-2.5 py-1 font-medium bg-emerald-100 text-emerald-700 rounded-full">
                            Manage Pemesanan - Cash
                        </span>
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table id="table-managePemesananCash" class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                <th class="px-4 py-3 w-[200px]">Nama User</th>
                                <th class="px-4 py-3">Unit</th>
                                <th class="px-4 py-3">Nama Sales</th>

                                @can('marketing.kelola-pemesanan.print-ppjb')
                                    <th class="px-4 py-3 text-center">PPJB</th>
                                @endcan

                                <th class="px-4 py-3 text-center">Kelengkapan Berkas</th>
                                <th class="px-4 py-3 text-center">Progress Bangunan</th>
                                <th class="px-4 py-3">Status Unit Pemesanan</th>

                                @can('marketing.kelola-pemesanan.update-berkas')
                                    <th class="px-4 py-3 text-center">Update Data Cash</th>
                                @endcan

                                {{-- @can('marketing.kelola-pemesanan.pengajuan-adendum')
                                    <th class="px-4 py-3 text-center">Adendum</th>
                                @endcan --}}

                                @can('marketing.kelola-pemesanan.tagihan.read')
                                    <th class="px-4 py-3 text-center">Rincian Tagihan</th>
                                @endcan

                               @can('marketing.kelola-pemesanan.pengajuan-pembatalan')
                                    <th class="px-4 py-3 text-center">Pengajuan Pembatalan</th>
                                @endcan
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($pemesananCash as $item)
                                <tr class="border-b hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                    <td class="px-4 py-2 font-medium text-gray-800 dark:text-white truncate max-w-[200px]">
                                        {{ $item->customer->username ?? '-' }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">{{ $item->unit->nama_unit ?? '-' }}</td>
                                    <td class="px-4 py-2">{{ $item->sales->username ?? '-' }}</td>

                                    @can('marketing.kelola-pemesanan.print-ppjb')
                                        <td class="px-4 py-2 text-center">
                                            <a href="{{ route('ppjbCASH.export.word', $item->id) }}"
                                                class="inline-flex items-center px-3 py-1 text-white bg-blue-600 rounded hover:bg-blue-700 transition">
                                                PPJB
                                            </a>
                                        </td>
                                    @endcan

                                    <td class="px-4 py-2 text-center">
                                        <span class=text-gray-600">{{ $item->kelengkapan_berkas ?? 0 }}</span>
                                    </td>
                                    <td class="px-4 py-2 text-center">{{ $item->progress_bangunan ?? '-' }}</td>
                                    <td class="px-4 py-2 text-center">
                                        @php
                                            $status = $item->status_pemesanan ?? '-';
                                            $classes = match ($status) {
                                                'proses' => 'bg-yellow-100 text-yellow-800 px-2 py-1 rounded',
                                                'LPA' => 'bg-blue-100 text-blue-800 px-2 py-1 rounded',
                                                'serah_terima' => 'bg-green-100 text-green-800 px-2 py-1 rounded',
                                                default => 'bg-gray-100 text-gray-600 px-2 py-1 rounded',
                                            };
                                        @endphp
                                        <span class="{{ $classes }}">{{ ucfirst($status) }}</span>
                                    </td>


                                    {{-- 🔵 Update Data Cash --}}
                                    @can('marketing.kelola-pemesanan.update-berkas')
                                        <td class="px-4 py-2 text-center">
                                            <a href="{{ route('marketing.kelengkapanBerkasCash.editCash', $item->id) }}"
                                                class="inline-flex items-center gap-1 px-3 py-1 text-white bg-indigo-600 rounded hover:bg-indigo-700 transition">
                                                <i class="ri-edit-line"></i> Update Data
                                            </a>
                                        </td>
                                    @endcan

                                    {{-- 🟡 Adendum --}}
                                    {{-- @can('marketing.kelola-pemesanan.pengajuan-adendum')
                                        <td class="px-4 py-2 text-center">
                                            <a href="{{ route('marketing.pindahUnit.createPengajuan', $item->id) }}"
                                                class="inline-flex items-center gap-1 px-3 py-1 text-white bg-orange-500 rounded hover:bg-orange-600 transition">
                                                <i class="ri-repeat-line"></i> Adendum
                                            </a>
                                        </td>
                                    @endcan --}}




                                    {{-- 🟢 Rincian Tagihan --}}
                                    @can('marketing.kelola-pemesanan.tagihan.read')
                                        <td class="px-4 py-2 text-center">
                                            <a href="{{ route('marketing.rincianTagihan', $item->id) }}"
                                                class="inline-flex items-center gap-1 px-3 py-1 text-white bg-green-600 rounded hover:bg-green-700 transition">
                                                <i class="ri-file-list-3-line"></i> Lihat
                                            </a>
                                        </td>
                                    @endcan

                                    @can(abilities: 'marketing.kelola-pemesanan.pengajuan-pembatalan')
                                        {{-- 🔴 Pengajuan Pembatalan --}}
                                        <td class="px-4 py-2 text-center">
                                            <button data-modal-target="modal-pembatalan" data-modal-toggle="modal-pembatalan"
                                                data-id="{{ $item->id }}"
                                                data-nama-unit="{{ $item->unit->nama_unit ?? '-' }}"
                                                data-nama-user="{{ $item->customer->username ?? '-' }}"
                                                data-cara-bayar="{{ ucfirst($item->cara_bayar) }}"
                                                data-no-hp="{{ $item->customer->no_hp ?? '-' }}"
                                                class="inline-flex items-center gap-1 px-3 py-1 text-white bg-red-600 rounded hover:bg-red-700 transition">
                                                <i class="ri-close-circle-line"></i> Pembatalan
                                            </button>
                                        </td>
                                    @endcan

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>
    <!-- ===== Main Content End ===== -->


    @include('marketing.manage-pemesanan.modal.modal-pengajuan-pembatalanPemesanan')

    {{-- sweatalert 2 for delete data --}}
    <script>
        if (document.getElementById("table-managePemesananKpr") && typeof simpleDatatables.DataTable !== 'undefined') {
            const dataTable = new simpleDatatables.DataTable("#table-managePemesananKpr", {
                searchable: true,
                sortable: true,

            });
        }

        if (document.getElementById("table-managePemesananCash") && typeof simpleDatatables.DataTable !== 'undefined') {
            const dataTable = new simpleDatatables.DataTable("#table-managePemesananCash", {
                searchable: true,
                sortable: true,
            });
        }
    </script>
@endsection
