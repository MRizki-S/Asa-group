@extends('layouts.app')

@section('pageActive', 'ListAdendum')

@section('content')
    <!-- ===== Main Content Start ===== -->
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: 'ListAdendum' }">
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
            {{-- Kalau global, tampilkan perumahaan secara terpisah --}}
            @if ($isGlobal)

                @if ($listAdendum->isEmpty())
                    <div class="rounded-2xl border border-gray-200 px-5 py-4 bg-white text-center text-gray-500">
                        Tidak ada data List Adendum Pemesanan Unit.
                    </div>
                @else

                    @foreach ($listAdendum as $perumahaanId => $items)

                        @php
                            $first = $items->first();
                            $perum = $first->pemesananUnit->perumahaan ?? null;
                            $namaPerumahaan = $perum->nama_perumahaan ?? 'Tidak Diketahui';
                        @endphp

                        <div class="rounded-2xl border border-gray-200 px-5 py-4 bg-white mb-5">
                            <h3 class="text-base font-medium mb-4">
                                List Adendum Pemesanan Unit - {{ $namaPerumahaan }}
                            </h3>

                            <table class="min-w-full text-sm text-left text-gray-500">
                                <thead class="text-xs uppercase bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-3">Nama User</th>
                                        <th class="px-4 py-3">Sales</th>
                                        <th class="px-4 py-3 text-center">Unit Dipesan</th>
                                        <th class="px-4 py-3">Jenis Adendum</th>
                                        <th class="px-4 py-3 text-center">Tanggal Pengajuan</th>
                                        <th class="px-4 py-3 text-center">Status</th>
                                        <th class="px-4 py-3 text-center">Aksi</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($items as $item)

                                        @php
                                            $status = strtolower($item->status);

                                            $color = [
                                                'acc' => 'green',
                                                'setuju' => 'green',
                                                'disetujui' => 'green',
                                                'tolak' => 'red',
                                                'ditolak' => 'red',
                                                'reject' => 'red',
                                                'tidak_disetujui' => 'red',
                                            ][$status] ?? 'yellow';

                                            $bg = "bg-{$color}-100";
                                            $border = "border-{$color}-500";
                                            $text = "text-{$color}-700";
                                        @endphp

                                        <tr class="border-b hover:bg-gray-50">

                                            <td class="px-4 py-2 font-medium text-gray-900">
                                                {{ Str::upper($item->pemesananUnit->customer->nama_lengkap ?? '-') }}
                                            </td>

                                            <td class="px-4 py-2">
                                                {{ Str::upper($item->pemesananUnit->sales->nama_lengkap ?? '-') }}
                                            </td>

                                            <td class="px-4 py-2 text-center">
                                                {{ $item->pemesananUnit->unit->nama_unit ?? '-' }}
                                            </td>

                                            <td class="px-4 py-2">
                                                {{ Str::title(str_replace('_', ' ', $item->jenis)) }}
                                            </td>

                                            <td class="px-4 py-2 text-center">
                                                {{ $item->tanggal_adendum?->format('d M Y') ?? '-' }}
                                            </td>

                                            <td class="px-4 py-2 text-center">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full border {{ $bg }} {{ $border }} {{ $text }}">
                                                    {{ ucfirst($item->status) }}
                                                </span>
                                            </td>

                                            <td class="px-4 py-2 text-center">
                                                <a href="{{ route('marketing.adendum.detail', $item->id) }}"
                                                    class="inline-flex items-center gap-1 text-xs font-medium bg-blue-100 text-blue-700 px-2.5 py-1.5 rounded-md">
                                                    Detail
                                                </a>
                                            </td>

                                        </tr>

                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    @endforeach
                @endif

            @else
                {{-- Jika bukan global --}}
                <div
                    class="rounded-2xl border border-gray-200 px-5 py-4 sm:px-6 sm:py-5 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                    <h3 class="mb-4 text-base font-medium text-gray-800 dark:text-white/90">
                        List Adendum - {{ $namaPerumahaan }}
                    </h3>

                    <table id="table-listAdendum" class="min-w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3">Nama User</th>
                                <th class="px-4 py-3">Sales</th>
                                <th class="px-4 py-3 text-center">Unit Dipesan</th>
                                <th class="px-4 py-3">Jenis Adendum</th>
                                <th class="px-4 py-3 text-center">Tanggal Pengajuan</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($listAdendum as $item)
                                <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition">

                                    {{-- Nama User (Pengaju) --}}
                                    <td class="px-4 py-2 font-medium text-gray-900 dark:text-white">
                                        {{ Str::upper($item->pemesananUnit->customer?->nama_lengkap ?? '-') }}
                                    </td>

                                    {{-- Sales --}}
                                    <td class="px-4 py-2">
                                        {{ Str::upper($item->pemesananUnit->sales?->nama_lengkap ?? '-') }}
                                    </td>

                                    {{-- Unit Dipesan --}}
                                    <td class="px-4 py-2 text-center">
                                        {{ $item->pemesananUnit?->unit?->nama_unit ?? '-' }}
                                    </td>

                                    {{-- Jenis Adendum --}}
                                    <td class="px-4 py-2">
                                        {{ Str::title(str_replace('_', ' ', $item->jenis)) }}
                                    </td>

                                    {{-- Tanggal Pengajuan --}}
                                    <td class="px-4 py-2 text-center">
                                        {{ $item->tanggal_adendum?->format('d M Y') ?? '-' }}
                                    </td>
                                    @php
                                        if (!function_exists('statusColorAdendum')) {
                                            function statusColorAdendum($status)
                                            {
                                                $status = strtolower(trim($status ?? 'pending'));

                                                $map = [
                                                    'acc' => 'green',
                                                    'setuju' => 'green',
                                                    'disetujui' => 'green',

                                                    'tolak' => 'red',
                                                    'ditolak' => 'red',
                                                    'reject' => 'red',
                                                    'tidak_disetujui' => 'red',

                                                    'pending' => 'yellow',
                                                ];

                                                $color = $map[$status] ?? 'yellow';

                                                return [
                                                    "bg-{$color}-100",
                                                    "border-{$color}-500",
                                                    "text-{$color}-700",
                                                ];
                                            }
                                        }

                                        [$bg, $border, $text] = statusColorAdendum($item->status);
                                    @endphp
                                    {{-- Status --}}
                                    <td class="px-4 py-2 text-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full border {{ $bg }} {{ $border }} {{ $text }}">
                                            {{ ucfirst($item->status ?? 'Pending') }}
                                        </span>
                                    </td>


                                    {{-- Aksi --}}
                                    <td class="px-4 py-2 text-center">
                                        <a href="{{ route('marketing.adendum.detail', $item->id) }}"
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
                                    <td colspan="7" class="px-4 py-3 text-center text-gray-500">
                                        Tidak ada adendum.
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
        if (document.getElementById("table-listAdendum") && typeof simpleDatatables.DataTable !== 'undefined') {
            const dataTable = new simpleDatatables.DataTable("#table-listAdendum", {
                searchable: true,
                sortable: true,
            });
        }
    </script>
@endsection