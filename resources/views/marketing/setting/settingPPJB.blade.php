@extends('layouts.app')

@section('pageActive', 'SettingPPJB')

@section('content')
    <!-- ===== Main Content Start ===== -->
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: 'SettingPPJB' }">
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


        {{-- Promo --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
            <div class="px-5 py-4 sm:px-6 sm:py-5">
                <h3
                    class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 border-b-2 border-gray-100 dark:border-gray-800">
                    Promo PPJB
                </h3>

                <!-- Dua Card Promo -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    {{-- ================= Promo Cash ================= --}}
                    <div
                        class="flex flex-col relative rounded-xl border border-gray-200 dark:border-gray-700
                       bg-gray-50 dark:bg-white/5 p-4">

                        <!-- Judul + Badge -->
                        <div
                            class="flex items-center justify-between mb-3 border-b border-gray-100 dark:border-gray-700 pb-2">
                            <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                                Promo Cash
                            </h4>
                            @if ($promoCash && $promoCash->status_aktif)
                                <span
                                    class="px-3 py-1 text-xs font-semibold rounded-full
                     bg-green-100 text-green-700 dark:bg-green-500 dark:text-white">
                                    Aktif
                                </span>
                            @else
                                <span
                                    class="px-3 py-1 text-xs font-semibold rounded-full
                     bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                    Tidak ada promo aktif
                                </span>
                            @endif
                        </div>

                        <!-- List Promo -->
                        {{-- Promo Cash --}}
                        <ul class="space-y-2 mb-4">
                            @if ($promoCash && $promoCash->items->count() > 0)
                                @foreach ($promoCash->items as $item)
                                    <li class="flex items-center">
                                        <span class="text-gray-800 dark:text-gray-200">{{ $item->nama_promo }}</span>
                                        <div
                                            class="flex-grow mx-2 border-b border-dashed border-gray-300 dark:border-gray-600">
                                        </div>
                                    </li>
                                @endforeach
                            @else
                                <li class="text-gray-400 italic">Belum ada promo aktif</li>
                            @endif
                        </ul>

                        <!-- Tombol -->
                        <a href="{{ route('settingPPJB.promoCash.edit') }}"
                            class="mt-auto self-end px-4 py-2 text-sm font-medium rounded-lg bg-blue-500 text-white
                          hover:bg-blue-600 transition-colors">
                            Kelola Promo
                        </a>
                    </div>

                    {{-- ================= Promo KPR ================= --}}
                    <div
                        class="flex flex-col relative rounded-xl border border-gray-200 dark:border-gray-700
                       bg-gray-50 dark:bg-white/5 p-4">

                        <!-- Judul + Badge -->
                        <div
                            class="flex items-center justify-between mb-3 border-b border-gray-100 dark:border-gray-700 pb-2">
                            <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                                Promo KPR
                            </h4>
                            @if ($promoKpr && $promoKpr->status_aktif)
                                <span
                                    class="px-3 py-1 text-xs font-semibold rounded-full
                                 bg-green-100 text-green-700 dark:bg-green-500 dark:text-white">
                                    Aktif
                                </span>
                            @else
                                <span
                                    class="px-3 py-1 text-xs font-semibold rounded-full
                                    bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                    Tidak ada promo aktif
                                </span>
                            @endif
                        </div>

                        {{-- Promo KPR --}}
                        <ul class="space-y-2 mb-4">
                            @if ($promoKpr && $promoKpr->items->count() > 0)
                                @foreach ($promoKpr->items as $item)
                                    <li class="flex items-center">
                                        <span class="text-gray-800 dark:text-gray-200">{{ $item->nama_promo }}</span>
                                        <div
                                            class="flex-grow mx-2 border-b border-dashed border-gray-300 dark:border-gray-600">
                                        </div>
                                    </li>
                                @endforeach
                            @else
                                <li class="text-gray-400 italic">Belum ada promo aktif</li>
                            @endif
                        </ul>

                        <!-- Tombol -->
                        <a href="{{ route('settingPPJB.promoKpr.edit') }}"
                            class="mt-auto self-end px-4 py-2 text-sm font-medium rounded-lg bg-blue-500 text-white
                          hover:bg-blue-600 transition-colors">
                            Kelola Promo
                        </a>
                    </div>

                </div>
            </div>
        </div>
        {{-- end Promo --}}



        {{-- Mutu --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
            <div class="px-5 py-4 sm:px-6 sm:py-5">
                <h3
                    class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 border-b-2 border-gray-100 dark:border-gray-800">
                    Mutu PPJB
                </h3>

                <!-- Card Mutu -->
                <div
                    class="flex flex-col relative rounded-xl border border-blue-200 dark:border-blue-700
            bg-blue-50 dark:bg-blue-900/10 p-5 shadow-sm">

                    <!-- Judul + Badge Aktif -->
                    <div class="flex items-center justify-between mb-4 border-b border-blue-100 dark:border-blue-800 pb-2">
                        <h4 class="text-lg font-semibold text-blue-800 dark:text-blue-200">
                            Mutu
                        </h4>
                        @if ($mutu && $mutu->status_aktif)
                            <span
                                class="px-2.5 py-0.5 text-xs font-semibold rounded-full
                         bg-green-100 text-green-700 dark:bg-green-500 dark:text-white">
                                Aktif
                            </span>
                        @else
                            <span
                                class="px-2.5 py-0.5 text-xs font-semibold rounded-full
                         bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                Tidak ada Mutu aktif
                            </span>
                        @endif
                    </div>

                    <!-- Detail item -->
                    <ul class="space-y-3 mb-4">
                        @if ($mutu && $mutu->items->count())
                            @foreach ($mutu->items as $item)
                                <li class="flex items-center">
                                    <span class="text-gray-800 dark:text-gray-200">{{ $item->nama_mutu }}</span>
                                    <div class="flex-grow mx-2 border-b border-dashed border-gray-300 dark:border-gray-600">
                                    </div>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Rp
                                        {{ number_format($item->nominal_mutu, 0, ',', '.') }}</span>
                                </li>
                            @endforeach
                        @else
                            <li class="text-gray-500 dark:text-gray-400 italic">Belum ada mutu aktif.</li>
                        @endif
                    </ul>


                    <a href="{{ route('settingPPJB.mutu.edit') }}"
                        class="mt-auto self-end px-4 py-2 text-sm font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                        Kelola Mutu
                    </a>
                </div>


            </div>
        </div>
        {{-- end Mutu --}}


        {{-- Terkait Cara Bayar --}}
        <div x-data="{ tab: '{{ session('tab', 'KPR') }}' }"
            class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6 shadow-sm overflow-hidden">

            {{-- ==== Header Tab ==== --}}
            <div class="flex justify-center gap-3 p-3 bg-gray-50 dark:bg-gray-800/60">
                <button @click="tab = 'KPR'"
                    class="flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-medium transition-all duration-200"
                    :class="tab === 'KPR'
                        ?
                        'bg-blue-600 text-white shadow-md scale-[1.03]' :
                        'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 hover:scale-[1.02]'">
                    🏠 <span>KPR</span>
                </button>

                <button @click="tab = 'CASH'"
                    class="flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-medium transition-all duration-200"
                    :class="tab === 'CASH'
                        ?
                        'bg-blue-600 text-white shadow-md scale-[1.03]' :
                        'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 hover:scale-[1.02]'">
                    💰 <span>Cash</span>
                </button>
            </div>

            {{-- ==== TAB KPR ==== --}}
            <div x-show="tab === 'KPR'" x-transition class="px-5 py-4 sm:px-6 sm:py-5">
                <h3
                    class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 border-b-2 border-gray-100 dark:border-gray-800">
                    Terkait Cara Bayar - KPR
                </h3>

                @if ($caraBayarKpr)
                    <div class="flex flex-col relative rounded-xl border border-gray-200 dark:border-gray-700 p-4 mb-4">
                        <div class="flex flex-wrap gap-4 mb-4">
                            <div class="flex-1 min-w-[150px]">
                                <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Nama
                                    Cicilan</label>
                                <input type="text" readonly value="{{ $caraBayarKpr->nama_cara_bayar ?? '-' }}"
                                    class="bg-gray-50 border text-gray-900 text-sm rounded-lg w-full p-2.5
                            dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>

                            <div class="flex-1 min-w-[150px]">
                                <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Jumlah
                                    Cicilan</label>
                                <input type="text" readonly value="{{ $caraBayarKpr->jumlah_cicilan ?? '-' }} X"
                                    class="bg-gray-50 border text-gray-900 text-sm rounded-lg w-full p-2.5
                            dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>

                            <div class="flex-1 min-w-[150px]">
                                <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Minimal
                                    DP</label>
                                <input type="text" readonly
                                    value="Rp {{ number_format($caraBayarKpr->minimal_dp ?? 0, 0, ',', '.') }}"
                                    class="bg-gray-50 border text-gray-900 text-sm rounded-lg w-full p-2.5
                            dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>

                            <div class="flex-1 min-w-[150px]">
                                <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Status</label>
                                <input type="text" readonly value="Aktif"
                                    class="bg-green-50 border border-green-300 text-green-700 text-sm font-semibold rounded-lg w-full p-2.5">
                            </div>

                            <div class="flex-1 min-w-[150px]">
                                <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Diajukan
                                    Oleh</label>
                                <input type="text" readonly value="{{ $caraBayarKpr->pengaju->username ?? '-' }}"
                                    class="bg-gray-50 border text-gray-900 text-sm rounded-lg w-full p-2.5
                            dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>

                            <div class="flex-1 min-w-[150px]">
                                <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Disetujui
                                    Oleh</label>
                                <input type="text" readonly value="{{ $caraBayarKpr->approver->username ?? '-' }}"
                                    class="bg-gray-50 border text-gray-900 text-sm rounded-lg w-full p-2.5
                            dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                        </div>

                        <a href="{{ route('settingPPJB.caraBayar.edit') }}"
                            class="mt-auto self-end px-4 py-2 text-sm font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                            Kelola Cara Bayar
                        </a>
                    </div>
                @else
                    <div
                        class="flex flex-col relative rounded-xl border border-gray-200 dark:border-gray-700
                bg-gray-50 dark:bg-gray-800/30 p-5 shadow-sm text-center text-gray-500 dark:text-gray-400 italic">
                        Belum ada cara bayar KPR aktif.
                        <a href="{{ route('settingPPJB.caraBayar.edit') }}"
                            class="mt-3 inline-block px-4 py-2 text-sm font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                            Kelola Cara Bayar
                        </a>
                    </div>
                @endif
            </div>

            {{-- ==== TAB CASH ==== --}}
            <div x-show="tab === 'CASH'" x-transition class="px-5 py-4 sm:px-6 sm:py-5">
                <h3
                    class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 border-b-2 border-gray-100 dark:border-gray-800">
                    Terkait Cara Bayar - Cash
                </h3>

                @if ($caraBayarCash->count() > 0)
                    <div class="grid sm:grid-cols-2 gap-5">
                        @foreach ($caraBayarCash as $cash)
                            <div
                                class="flex flex-col rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
                                <div class="flex flex-wrap gap-4 mb-4">
                                    <div class="flex-1 min-w-[150px]">
                                        <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Nama
                                            Cicilan</label>
                                        <input type="text" readonly
                                            value="{{ $caraBayarKpr->nama_cara_bayar ?? '-' }}"
                                            class="bg-gray-50 border text-gray-900 text-sm rounded-lg w-full p-2.5
                            dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    </div>
                                    <div class="flex-1 min-w-[150px]">
                                        <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Jumlah
                                            Cicilan</label>
                                        <input type="text" readonly value="{{ $cash->jumlah_cicilan ?? '-' }} X"
                                            class="bg-gray-50 border text-gray-900 text-sm rounded-lg w-full p-2.5
                                    dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    </div>

                                    <div class="flex-1 min-w-[150px]">
                                        <label class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Minimal
                                            DP</label>
                                        <input type="text" readonly
                                            value="Rp {{ number_format($cash->minimal_dp ?? 0, 0, ',', '.') }}"
                                            class="bg-gray-50 border text-gray-900 text-sm rounded-lg w-full p-2.5
                                    dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    </div>

                                    <div class="flex-1 min-w-[150px]">
                                        <label
                                            class="block mb-1 text-sm font-medium text-gray-900 dark:text-white">Status</label>
                                        <input type="text" readonly value="Aktif"
                                            class="bg-green-50 border border-green-300 text-green-700 text-sm font-semibold rounded-lg w-full p-2.5">
                                    </div>
                                </div>

                                <div class="flex justify-between items-center mt-auto">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        Diajukan: <span
                                            class="font-medium text-gray-800 dark:text-white">{{ $cash->pengaju->username ?? '-' }}</span><br>
                                        Disetujui: <span
                                            class="font-medium text-gray-800 dark:text-white">{{ $cash->approver->username ?? '-' }}</span>
                                    </div>
                                    <a href="{{ route('settingPPJB.caraBayar.edit') }}"
                                        class="px-3 py-2 text-sm font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                                        Kelola
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div
                        class="flex flex-col relative rounded-xl border border-gray-200 dark:border-gray-700
                bg-gray-50 dark:bg-gray-800/30 p-5 shadow-sm text-center text-gray-500 dark:text-gray-400 italic">
                        Belum ada cara bayar Cash aktif.
                        <a href="{{ route('settingPPJB.caraBayar.edit') }}"
                            class="mt-3 inline-block px-4 py-2 text-sm font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                            Kelola Cara Bayar
                        </a>
                    </div>
                @endif
            </div>
        </div>
        {{-- end Terkait Cara Bayar --}}


        {{-- Keterlambatan PPJB --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
            <div class="px-5 py-4 sm:px-6 sm:py-5">
                <h3
                    class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 border-b-2 border-gray-100 dark:border-gray-800">
                    Keterlambatan PPJB
                </h3>
                @if ($keterlambatanPPJB)
                    <!-- Grid 2 kolom -->
                    <div class="flex flex-wrap gap-4 mb-4">
                        <!-- Persentase Denda -->
                        <div class="flex-1 min-w-[150px]">
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Persentase Denda
                            </label>
                            <input type="text" readonly
                                value="{{ rtrim(rtrim(number_format($keterlambatanPPJB->persentase_denda, 2, '.', ''), '0'), '.') }} %"
                                class="bg-gray-50 border text-gray-900 text-sm rounded-lg w-full p-2.5
                              focus:ring-blue-500 focus:border-blue-500
                              dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white
                              dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        </div>

                        <!-- Status -->
                        <div class="flex-1 min-w-[150px]">
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Status
                            </label>
                            <input type="text" readonly value="Aktif"
                                class="bg-green-50 border border-green-300 text-green-700 text-sm font-semibold rounded-lg w-full p-2.5
                           dark:bg-green-900 dark:border-green-700 dark:text-green-100">
                        </div>

                        <!-- Diajukan Oleh -->
                        <div class="flex-1 min-w-[150px]">
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Diajukan Oleh
                            </label>
                            <input type="text" readonly value="{{ $keterlambatanPPJB->pengaju->username ?? '-' }}"
                                class="bg-gray-50 border text-gray-900 text-sm rounded-lg w-full p-2.5
                              focus:ring-blue-500 focus:border-blue-500
                              dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white
                              dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        </div>

                        <!-- Disetujui Oleh -->
                        <div class="flex-1 min-w-[150px]">
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Disetujui Oleh
                            </label>
                            <input type="text" readonly value="{{ $keterlambatanPPJB->approver->username ?? '-' }}"
                                class="bg-gray-50 border text-gray-900 text-sm rounded-lg w-full p-2.5
                              focus:ring-blue-500 focus:border-blue-500
                              dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white
                              dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="flex justify-end">
                        <a href="{{ route('settingPPJB.keterlambatan.edit') }}"
                            class="px-4 py-2 text-sm font-medium rounded-lg bg-blue-500 text-white
                        hover:bg-blue-600 transition-colors">
                            Kelola Keterlambatan
                        </a>
                    </div>
                @else
                    <!-- Kalau Tidak Ada Cara Bayar -->
                    <div
                        class="flex flex-col relative rounded-xl border border-gray-200 dark:border-gray-700
                bg-gray-50 dark:bg-gray-800/30 p-5 shadow-sm text-center text-gray-500 dark:text-gray-400 italic">
                        Belum ada Keterlambatan aktif.
                        {{-- Tombol Kelola Cara Bayar --}}
                        <a href="{{ route('settingPPJB.keterlambatan.edit') }}"
                            class="mt-auto self-end px-4 py-2 text-sm font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                            Kelola Keterlambatan
                        </a>

                    </div>
                @endif
            </div>
        </div>
        {{-- end Keterlambatan PPJB --}}

        {{-- Pembatalan PPJB --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
            <div class="px-5 py-4 sm:px-6 sm:py-5">
                <h3
                    class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 border-b-2 border-gray-100 dark:border-gray-800">
                    Pembatalan PPJB
                </h3>

                @if ($pembatalanPPJB)
                    <!-- Grid 2 kolom -->
                    <div class="flex flex-wrap gap-4 mb-4">
                        <!-- Persentase Denda -->
                        <div class="flex-1 min-w-[150px]">
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Persentase Pembatalan
                            </label>
                            <input type="text" readonly
                                value="{{ rtrim(rtrim(number_format($pembatalanPPJB->persentase_potongan, 2, '.', ''), '0'), '.') }} %"
                                class="bg-gray-50 border text-gray-900 text-sm rounded-lg w-full p-2.5
                              focus:ring-blue-500 focus:border-blue-500
                              dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white
                              dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        </div>

                        <!-- Status -->
                        <div class="flex-1 min-w-[150px]">
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Status
                            </label>
                            <input type="text" readonly value="Aktif"
                                class="bg-green-50 border border-green-300 text-green-700 text-sm font-semibold rounded-lg w-full p-2.5
                           dark:bg-green-900 dark:border-green-700 dark:text-green-100">
                        </div>

                        <!-- Diajukan Oleh -->
                        <div class="flex-1 min-w-[150px]">
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Diajukan Oleh
                            </label>
                            <input type="text" readonly value="{{ $pembatalanPPJB->pengaju->username ?? '-' }}"
                                class="bg-gray-50 border text-gray-900 text-sm rounded-lg w-full p-2.5
                              focus:ring-blue-500 focus:border-blue-500
                              dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white
                              dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        </div>

                        <!-- Disetujui Oleh -->
                        <div class="flex-1 min-w-[150px]">
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Disetujui Oleh
                            </label>
                            <input type="text" readonly value="{{ $pembatalanPPJB->approver->username ?? '-' }}"
                                class="bg-gray-50 border text-gray-900 text-sm rounded-lg w-full p-2.5
                              focus:ring-blue-500 focus:border-blue-500
                              dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white
                              dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="flex justify-end">
                        <a href="{{ route('settingPPJB.pembatalan.edit') }}"
                            class="px-4 py-2 text-sm font-medium rounded-lg bg-blue-500 text-white
                      hover:bg-blue-600 transition-colors">
                            Kelola Pembatalan
                        </a>
                    </div>
                @else
                    <!-- Kalau Tidak Ada Cara Bayar -->
                    <div
                        class="flex flex-col relative rounded-xl border border-gray-200 dark:border-gray-700
                bg-gray-50 dark:bg-gray-800/30 p-5 shadow-sm text-center text-gray-500 dark:text-gray-400 italic">
                        Belum ada Potongan Pembatalan aktif.
                        {{-- Tombol Kelola Cara Bayar --}}
                        <a href="{{ route('settingPPJB.pembatalan.edit') }}"
                            class="mt-auto self-end px-4 py-2 text-sm font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                            Kelola Pembatalan
                        </a>

                    </div>
                @endif
            </div>
        </div>
        {{-- end Pembatalan PPJB --}}



    </div>
    <!-- ===== Main Content End ===== -->
@endsection
