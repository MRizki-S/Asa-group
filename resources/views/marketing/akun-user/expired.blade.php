@extends('layouts.app')

@section('pageActive', 'AkunUser')

@section('content')
    <!-- ===== Main Content Start ===== -->
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: 'AkunUser' }">
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
            <div
                class="rounded-2xl border border-gray-200 px-5 py-4 sm:px-6 sm:py-5 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        User Booking Expired
                    </h3>

                    <a href="{{ route('marketing.akunUser.index') }}"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Kembali
                    </a>
                </div>



                <table id="table-akunUser">
                    <thead>
                        <tr>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                                <span class="flex items-center">
                                    Username
                                    <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                    </svg>
                                </span>
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                                No Hp
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                                <span class="flex items-center">
                                    Sales / Agen Terkait
                                    <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                    </svg>
                                </span>
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                                <span class="flex items-center">
                                    Perumahaan
                                    <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                    </svg>
                                </span>
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 ">
                                Tahap
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 ">
                                Keep Unit
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 ">
                                <span class="flex items-center">
                                    Tanggal Booking
                                    <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                    </svg>
                                </span>
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 ">
                                <span class="flex items-center">
                                    Tanggal Expired Booking
                                    <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                    </svg>
                                </span>
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                                Status
                            </th>

                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($akunUser as $item)
                            <tr>
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $item->username }}
                                </td>
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $item->no_hp }}
                                </td>
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    @if($item->booking?->source === 'agent')
                                        <div class="flex flex-col">
                                            <span class="text-sm">{{ $item->booking?->agent?->nama_agent ?? '-' }}</span>
                                            <span class="text-[10px] text-gray-500 italic">Agen Referral</span>
                                        </div>
                                    @else
                                        <div class="flex flex-col">
                                            <span class="text-sm">{{ $item->booking?->sales?->username ?? '-' }}</span>
                                            <span class="text-[10px] text-gray-500 italic">Sales Marketing</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white text-center">
                                    @php
                                        $perum = $item->booking?->unit?->perumahaan?->nama_perumahaan;
                                        $badgeClass = 'bg-gray-400'; // default
                                        if ($perum === 'Asa Dreamland') {
                                            $badgeClass = 'bg-green-500';
                                        } elseif ($perum === 'Lembah Hijau Residence') {
                                            $badgeClass = 'bg-blue-500';
                                        }
                                    @endphp

                                    @if ($perum)
                                        <span
                                            class="px-2 py-1 text-xs font-semibold text-white rounded {{ $badgeClass }}">
                                            {{ $perum }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>

                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $item->booking?->unit?->tahap?->nama_tahap ?? '-' }}
                                </td>
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $item->booking?->unit?->blok?->nama_blok ?? '' }}
                                    ({{ $item->booking?->unit?->nama_unit ?? '-' }})
                                </td>
                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $item->booking->tanggal_booking->format('d M Y') }}
                                </td>
                                 <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $item->booking->tanggal_expired->format('d M Y') }}
                                </td>

                                <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    @php
                                        $status = $item->booking->status;
                                        $badgeStatus = 'bg-gray-100 text-gray-800';
                                        if($status === 'active') $badgeStatus = 'bg-green-100 text-green-800';
                                        if($status === 'forwarded') $badgeStatus = 'bg-blue-100 text-blue-800';
                                        if($status === 'expired') $badgeStatus = 'bg-red-100 text-red-800';
                                    @endphp
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeStatus }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>

    </div>
    <!-- ===== Main Content End ===== -->
     
    <script>

        if (document.getElementById("table-akunUser") && typeof simpleDatatables.DataTable !== 'undefined') {
            const dataTable = new simpleDatatables.DataTable("#table-akunUser", {
                searchable: true,
                sortable: true,
            });
        }
    </script>
@endsection
