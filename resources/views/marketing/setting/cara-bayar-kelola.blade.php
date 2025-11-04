@extends('layouts.app')

@section('pageActive', 'SettingPPJB')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        {{-- Breadcrumb --}}
        <div x-data="{ pageName: 'SettingPPJB' }">
            @include('partials.breadcrumb')
        </div>

        {{-- === Cara Bayar PPJB === --}}
        <div x-data="{ tab: '{{ session('tab', 'KPR') }}', openModal: false, }"
            class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900/50 overflow-hidden">
            <!-- ===== Tab Header ===== -->
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

            {{-- === KPR TAB === --}}
            <div x-show="tab === 'KPR'" x-transition class="px-5 py-4 sm:px-6 sm:py-5">
                {{-- Header + Tombol Ajukan Baru --}}
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Cara Bayar PPJB - KPR</h3>

                    @hasrole(['Manager Pemasaran', 'Super Admin '])
                        <div class="flex items-center gap-2">
                            @if (!$caraBayarPendingKpr)
                                <button @click="openModal = true"
                                    class="px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700">
                                    Ajukan Cara Bayar Baru
                                </button>
                            @endif
                        </div>
                    @endrole
                </div>

                {{-- Tidak ada data --}}
                @if (!$caraBayarActiveKpr && !$caraBayarPendingKpr)
                    <div class="mb-4">
                        <div
                            class="rounded-xl border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/20 p-4 text-center">
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                Belum ada Cara Bayar KPR aktif saat ini.
                            </p>
                        </div>
                    </div>
                @endif

                {{-- Aktif --}}
                @if ($caraBayarActiveKpr)
                    <div class="mb-4">
                        <div
                            class="rounded-xl border border-green-200 bg-green-50 dark:border-green-700 dark:bg-green-900/20 p-4 flex flex-col">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-semibold text-green-800 dark:text-green-300 text-lg">
                                    {{ $caraBayarActiveKpr->nama_cara_bayar ?? 'Cara Bayar KPR (Aktif)' }}
                                </h4>
                                <span class="px-2 py-0.5 text-xs rounded bg-green-100 text-green-700">ACC</span>
                            </div>

                            <div class="flex flex-wrap gap-4 mb-4">
                                {{-- Nama Cicilan --}}
                                <div class="flex-1 min-w-[150px]">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama
                                        Cicilan</label>
                                    <input type="text" readonly value="{{ $caraBayarActiveKpr->nama_cara_bayar ?? '-' }}"
                                        class="bg-gray-50 border text-gray-900 text-sm rounded-lg w-full p-2.5 dark:bg-gray-700 dark:text-white">
                                </div>

                                {{-- Jumlah Cicilan --}}
                                <div class="flex-1 min-w-[150px]">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Jumlah
                                        Cicilan</label>
                                    <input type="text" readonly value="{{ $caraBayarActiveKpr->jumlah_cicilan }} x"
                                        class="bg-gray-50 border text-gray-900 text-sm rounded-lg w-full p-2.5 dark:bg-gray-700 dark:text-white">
                                </div>

                                {{-- Minimal DP --}}
                                <div class="flex-1 min-w-[150px]">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Minimal
                                        DP</label>
                                    <input type="text" readonly
                                        value="Rp {{ number_format($caraBayarActiveKpr->minimal_dp, 0, ',', '.') }}"
                                        class="bg-gray-50 border text-gray-900 text-sm rounded-lg w-full p-2.5 dark:bg-gray-700 dark:text-white">
                                </div>

                                {{-- Status --}}
                                <div class="flex-1 min-w-[150px]">
                                    <label
                                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Status</label>
                                    <input type="text" readonly value="Aktif"
                                        class="bg-green-50 border border-green-300 text-green-700 text-sm font-semibold rounded-lg w-full p-2.5">
                                </div>
                            </div>

                            <div class="flex justify-between items-center mt-2">
                                <p class="text-sm text-gray-500">
                                    Disetujui oleh <strong>{{ $caraBayarActiveKpr->approver->username ?? '-' }}</strong>
                                    pada {{ $caraBayarActiveKpr->updated_at?->translatedFormat('d M Y') ?? '-' }}
                                </p>
                                <div class="mt-4 flex justify-end">
                                    <form action="{{ route('settingPPJB.caraBayar.nonAktif', $caraBayarActiveKpr) }}"
                                        method="POST" class="delete-form">
                                        @csrf
                                        @method('PATCH')
                                        <button type="button"
                                            class="nonAktifkanCaraBayar px-3 py-1 text-sm bg-red-600 text-white rounded-md hover:bg-red-700">
                                            Nonaktifkan
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Pending --}}
                @if ($caraBayarPendingKpr)
                    <div class="mb-4">
                        <div
                            class="rounded-xl border border-yellow-200 bg-yellow-50 dark:border-yellow-700 dark:bg-yellow-900/20 p-4 flex flex-col shadow-sm transition hover:shadow-md">

                            <div class="flex items-center justify-between mb-2">
                                <h4
                                    class="font-semibold text-yellow-800 dark:text-yellow-300 text-lg flex items-center gap-2">
                                    <span>📄
                                        {{ $caraBayarPendingKpr->nama_cara_bayar ?? 'Cara Bayar KPR - Diajukan (Pending)' }}</span>
                                </h4>
                                <span
                                    class="px-2 py-0.5 text-xs rounded bg-yellow-100 text-yellow-700 font-semibold">Pending</span>
                            </div>

                            <div class="flex flex-wrap gap-4 mb-4">
                                {{-- Nama Cicilan --}}
                                <div class="flex-1 min-w-[150px]">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama
                                        Cicilan</label>
                                    <input type="text" readonly
                                        value="{{ $caraBayarPendingKpr->nama_cara_bayar ?? '-' }}"
                                        class="bg-gray-50 border text-gray-900 text-sm rounded-lg w-full p-2.5 dark:bg-gray-700 dark:text-white">
                                </div>

                                {{-- Jumlah Cicilan --}}
                                <div class="flex-1 min-w-[150px]">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Jumlah
                                        Cicilan</label>
                                    <input type="text" readonly value="{{ $caraBayarPendingKpr->jumlah_cicilan }} x"
                                        class="bg-gray-50 border text-gray-900 text-sm rounded-lg w-full p-2.5 dark:bg-gray-700 dark:text-white">
                                </div>

                                {{-- Minimal DP --}}
                                <div class="flex-1 min-w-[150px]">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Minimal
                                        DP</label>
                                    <input type="text" readonly
                                        value="Rp {{ number_format($caraBayarPendingKpr->minimal_dp, 0, ',', '.') }}"
                                        class="bg-gray-50 border text-gray-900 text-sm rounded-lg w-full p-2.5 dark:bg-gray-700 dark:text-white">
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mt-2">
                                <p class="text-sm text-gray-500">
                                    Diajukan oleh <strong>{{ $caraBayarPendingKpr->pengaju->username ?? '-' }}</strong>
                                    pada {{ $caraBayarPendingKpr->created_at?->translatedFormat('d M Y') ?? '-' }}
                                </p>

                                {{-- Tombol Aksi --}}
                                <div class="flex gap-2 sm:justify-end justify-start">
                                    @hasrole(['Manager Keuangan', 'Super Admin'])
                                        {{-- Tombol Tolak --}}
                                        <form action="{{ route('settingPPJB.caraBayar.reject', $caraBayarPendingKpr) }}"
                                            method="POST" class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                class="tolakPengajuan flex items-center gap-1 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-sm rounded-md transition-all">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                                Tolak
                                            </button>
                                        </form>

                                        {{-- Tombol ACC --}}
                                        <form
                                            action="{{ route('settingPPJB.caraBayar.approve', $caraBayarPendingKpr) }}"method="POST"
                                            class="approve-form">
                                            @csrf
                                            @method('PATCH')
                                            <button type="button"
                                                class="accPengajuanKPR flex items-center gap-1 px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-sm rounded-md transition-all">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M5 13l4 4L19 7" />
                                                </svg>
                                                ACC
                                            </button>
                                        </form>
                                    @endrole

                                    @hasrole(['Manager Pemasaran', 'Super Admin '])
                                        {{-- Tombol untuk pengaju --}}
                                        <form
                                            action="{{ route('settingPPJB.caraBayar.cancelPengajuanPromo', $caraBayarPendingKpr) }}"
                                            method="POST" class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                class="cancelPengajuan px-3 py-1 text-sm text-white bg-red-600 rounded hover:bg-red-700">
                                                Batalkan Pengajuan
                                            </button>
                                        </form>
                                    @endrole
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

            </div>


            {{-- === CASH TAB === --}}
            <div x-show="tab === 'CASH'" x-transition class="px-5 py-4 sm:px-6 sm:py-5">
                {{-- Header + Tombol Ajukan Baru --}}
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Cara Bayar PPJB - Cash</h3>
                      @hasrole(['Manager Pemasaran', 'Super Admin '])
                    <button @click="openModal = true"
                        class="px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700">
                        Ajukan Cara Bayar Baru
                    </button>
                    @endrole
                </div>

                {{-- Tidak ada data --}}
                @if ($caraBayarActiveCash->isEmpty() && $caraBayarPendingCash->isEmpty())
                    <div
                        class="rounded-xl border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/20 p-4 text-center">
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Belum ada Cara Bayar Cash aktif maupun pending saat ini.
                        </p>
                    </div>
                @endif

                {{-- Aktif --}}
                @foreach ($caraBayarActiveCash as $item)
                    <div class="mb-4">
                        <div
                            class="rounded-xl border border-green-200 bg-green-50 dark:border-green-700 dark:bg-green-900/20 p-4 flex flex-col">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-semibold text-green-800 dark:text-green-300 text-lg">
                                    {{ $item->nama_cara_bayar ?? 'Cara Bayar Cash (Aktif)' }}
                                </h4>
                                <span class="px-2 py-0.5 text-xs rounded bg-green-100 text-green-700">ACC</span>
                            </div>

                            <div class="flex flex-wrap gap-4 mb-4">
                                <div class="flex-1 min-w-[150px]">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama
                                        Cicilan</label>
                                    <input type="text" readonly value="{{ $item->nama_cara_bayar ?? '-' }}"
                                        class="bg-gray-50 border text-gray-900 text-sm rounded-lg w-full p-2.5 dark:bg-gray-700 dark:text-white">
                                </div>

                                <div class="flex-1 min-w-[150px]">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Jumlah
                                        Cicilan</label>
                                    <input type="text" readonly value="{{ $item->jumlah_cicilan }} x"
                                        class="bg-gray-50 border text-gray-900 text-sm rounded-lg w-full p-2.5 dark:bg-gray-700 dark:text-white">
                                </div>

                                <div class="flex-1 min-w-[150px]">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Minimal
                                        DP</label>
                                    <input type="text" readonly
                                        value="Rp {{ number_format($item->minimal_dp, 0, ',', '.') }}"
                                        class="bg-gray-50 border text-gray-900 text-sm rounded-lg w-full p-2.5 dark:bg-gray-700 dark:text-white">
                                </div>

                                <div class="flex-1 min-w-[150px]">
                                    <label
                                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Status</label>
                                    <input type="text" readonly value="Aktif"
                                        class="bg-green-50 border border-green-300 text-green-700 text-sm font-semibold rounded-lg w-full p-2.5">
                                </div>
                            </div>

                            <div class="flex justify-between items-center mt-2">
                                <p class="text-sm text-gray-500">
                                    Disetujui oleh <strong>{{ $item->approver->username ?? '-' }}</strong>
                                    pada {{ $item->updated_at?->translatedFormat('d M Y') ?? '-' }}
                                </p>
                                <div class="mt-4 flex justify-end">
                                    <form action="{{ route('settingPPJB.caraBayar.nonAktif', $item) }}" method="POST"
                                        class="delete-form">
                                        @csrf
                                        @method('PATCH')
                                        <button type="button"
                                            class="nonAktifkanCaraBayar px-3 py-1 text-sm bg-red-600 text-white rounded-md hover:bg-red-700">
                                            Nonaktifkan
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Pending --}}
                @foreach ($caraBayarPendingCash as $item)
                    <div class="mb-4">
                        <div
                            class="rounded-xl border border-yellow-200 bg-yellow-50 dark:border-yellow-700 dark:bg-yellow-900/20 p-4 flex flex-col shadow-sm transition hover:shadow-md">

                            <div class="flex items-center justify-between mb-2">
                                <h4
                                    class="font-semibold text-yellow-800 dark:text-yellow-300 text-lg flex items-center gap-2">
                                    <span>📄 {{ $item->nama_cara_bayar ?? 'Cara Bayar Cash - Diajukan (Pending)' }}</span>
                                </h4>
                                <span
                                    class="px-2 py-0.5 text-xs rounded bg-yellow-100 text-yellow-700 font-semibold">Pending</span>
                            </div>

                            <div class="flex flex-wrap gap-4 mb-4">
                                {{-- Nama Cicilan --}}
                                <div class="flex-1 min-w-[150px]">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama
                                        Cicilan</label>
                                    <input type="text" readonly value="{{ $item->nama_cara_bayar ?? '-' }}"
                                        class="bg-gray-50 border text-gray-900 text-sm rounded-lg w-full p-2.5 dark:bg-gray-700 dark:text-white">
                                </div>

                                {{-- Jumlah Cicilan --}}
                                <div class="flex-1 min-w-[150px]">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Jumlah
                                        Cicilan</label>
                                    <input type="text" readonly value="{{ $item->jumlah_cicilan }} x"
                                        class="bg-gray-50 border text-gray-900 text-sm rounded-lg w-full p-2.5 dark:bg-gray-700 dark:text-white">
                                </div>

                                {{-- Minimal DP --}}
                                <div class="flex-1 min-w-[150px]">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Minimal
                                        DP</label>
                                    <input type="text" readonly
                                        value="Rp {{ number_format($item->minimal_dp, 0, ',', '.') }}"
                                        class="bg-gray-50 border text-gray-900 text-sm rounded-lg w-full p-2.5 dark:bg-gray-700 dark:text-white">
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mt-2">
                                <p class="text-sm text-gray-500">
                                    Diajukan oleh <strong>{{ $item->pengaju->username ?? '-' }}</strong>
                                    pada {{ $item->created_at?->translatedFormat('d M Y') ?? '-' }}
                                </p>

                                {{-- Tombol Aksi --}}
                                <div class="flex gap-2 sm:justify-end justify-start">
                                    @hasrole(['Manager Keuangan', 'Super Admin'])
                                        {{-- Tombol Tolak --}}
                                        <form action="{{ route('settingPPJB.caraBayar.reject', $item) }}" method="POST"
                                            class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                class="tolakPengajuan flex items-center gap-1 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-sm rounded-md transition-all">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                                Tolak
                                            </button>
                                        </form>

                                        {{-- Tombol ACC --}}
                                        <form action="{{ route('settingPPJB.caraBayar.approve', $item) }}"method="POST"
                                            class="approve-form">
                                            @csrf
                                            @method('PATCH')
                                            <button type="button"
                                                class="accPengajuanCash flex items-center gap-1 px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-sm rounded-md transition-all">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M5 13l4 4L19 7" />
                                                </svg>
                                                ACC
                                            </button>
                                        </form>
                                    @endrole

                                    @hasrole(['Manager Pemasaran', 'Super Admin '])
                                        {{-- Tombol untuk cancel pengajuan  --}}
                                        <form action="{{ route('settingPPJB.caraBayar.cancelPengajuanPromo', $item) }}"
                                            method="POST" class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                class="cancelPengajuan px-3 py-1 text-sm text-white bg-red-600 rounded hover:bg-red-700">
                                                Batalkan Pengajuan
                                            </button>
                                        </form>
                                    @endrole
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>



            <!-- Modal -->
            <template x-if="openModal">
                <div @click.self="openModal = false"
                    class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
                    <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md max-w-md w-full overflow-hidden">
                        <!-- Header -->
                        <div class="flex items-center justify-between p-4 border-b dark:border-gray-600">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Ajukan Cara Bayar <span x-text="tab"></span>
                            </h3>
                            <button @click="openModal = false"
                                class="text-gray-400 hover:text-gray-900 hover:bg-gray-200 rounded-lg w-8 h-8 flex items-center justify-center dark:hover:bg-gray-600 dark:hover:text-white">
                                ✕
                            </button>
                        </div>

                        <!-- Body -->
                        <form action="{{ route('settingPPJB.caraBayar.updatePengajuan') }}" method="POST"
                            class="p-4 space-y-4">
                            @csrf
                            <input type="hidden" name="perumahaan_id"
                                value="{{ auth()->user()->hasGlobalAccess() ? session('current_perumahaan_id') : Auth::user()->perumahaan_id }}">

                            <!-- Jenis Pembayaran -->
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 block">Jenis
                                    Pembayaran</label>
                                <input type="text" name="jenis_pembayaran" x-model="tab" readonly
                                    class="bg-gray-100 border text-gray-900 text-sm rounded-lg p-2.5 w-full
                            dark:bg-gray-600 dark:text-white dark:placeholder-gray-400">
                            </div>

                            <!-- Nama Cicilan -->
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 block">Nama
                                    Cicilan</label>
                                <input type="text" name="nama_cara_bayar" required placeholder="Masukkan nama cicilan"
                                    class="bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5 w-full
                            dark:bg-gray-600 dark:text-white dark:placeholder-gray-400">
                            </div>

                            <!-- Jumlah Cicilan -->
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 block">Jumlah
                                    Cicilan (X)</label>
                                <input type="number" name="jumlah_cicilan" required placeholder="Contoh: 3"
                                    class="bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5 w-full
                            dark:bg-gray-600 dark:text-white dark:placeholder-gray-400">
                            </div>

                            <!-- Minimal DP -->
                            <div class="flex flex-col" x-data="rupiahInput('')">
                                <label class="text-sm text-gray-700 dark:text-gray-300 mb-1">Minimal DP (Rp)</label>
                                <input type="text" placeholder="Minimal DP" x-model="display"
                                    @input="onInput($event); $refs.hidden.value = value"
                                    class="bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                            dark:bg-gray-600 dark:text-white dark:placeholder-gray-400" />
                                <input type="hidden" x-ref="hidden" name="minimal_dp" required :value="value">
                            </div>

                            <!-- Footer -->
                            <div class="flex justify-end gap-3 border-t pt-3 dark:border-gray-600">
                                <button type="button" @click="openModal = false"
                                    class="px-4 py-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-100">
                                    Batal
                                </button>
                                <button type="submit"
                                    class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
                                    Simpan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </template>

        </div>


    </div>



    {{-- sweatalert 2 for batalkan pengajuan Cara Bayar data --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('tab', '{{ session('tab', 'KPR') }}');
        });

        // sweatalert cancel pengajuan
        document.addEventListener('click', function(e) {
            if (e.target.closest('.cancelPengajuan')) {
                const btn = e.target.closest('.cancelPengajuan');
                const form = btn.closest('.delete-form');

                Swal.fire({
                    title: 'Yakin membatalkan pengajuan Cara Bayar ini?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            }
        });

        // {{-- sweatalert 2 for nonaktifkan Cara Bayar data --}}
        document.addEventListener('click', function(e) {
            if (e.target.closest('.nonAktifkanCaraBayar')) {
                const btn = e.target.closest('.nonAktifkanCaraBayar');
                const form = btn.closest('.delete-form');

                Swal.fire({
                    title: 'Yakin menonaktifkan Cara Bayar?',
                    text: 'Menonaktifkan Cara Bayar akan berpengaruh pada proses PPJB dan semua data terkait batch ini. Pastikan Anda ingin melanjutkan.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, nonaktifkan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            }
        });

        // SweetAlert untuk tolak pengajuan Cara Bayar
        document.addEventListener('click', function(e) {
            if (e.target.closest('.tolakPengajuan')) {
                const btn = e.target.closest('.tolakPengajuan');
                const form = btn.closest('.delete-form');

                Swal.fire({
                    title: 'Tolak pengajuan Cara Bayar ini?',
                    text: 'Pengajuan akan dibatalkan dan data ini akan dihapus.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, tolak!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            }
        });

        // SweetAlert untuk ACC Pengajuan KPR
        document.addEventListener('click', function(e) {
            if (e.target.closest('.accPengajuanKPR')) {
                const btn = e.target.closest('.accPengajuanKPR');
                const form = btn.closest('.approve-form');

                Swal.fire({
                    title: 'ACC Pengajuan Cara Bayar KPR?',
                    text: 'Hanya satu cara bayar KPR yang bisa aktif. Jika disetujui, cara bayar KPR yang aktif sebelumnya akan dinonaktifkan dan diganti dengan pengajuan ini.',
                    icon: 'warning',
                    showCancelButton: true,
                    cancelButtonColor: '#6b7280',
                    confirmButtonColor: '#16a34a',
                    cancelButtonText: 'Batal',
                    confirmButtonText: 'Ya, ACC!',
                    reverseButtons: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            }
        });

        // SweetAlert untuk ACC Pengajuan Cash
        document.addEventListener('click', function(e) {
            if (e.target.closest('.accPengajuanCash')) {
                const btn = e.target.closest('.accPengajuanCash');
                const form = btn.closest('.approve-form');

                Swal.fire({
                    title: 'Setujui Pengajuan Cash?',
                    text: 'Cara bayar ini akan diaktifkan dan disetujui.',
                    icon: 'question',
                    showCancelButton: true,
                    cancelButtonColor: '#6b7280',
                    confirmButtonColor: '#16a34a',
                    cancelButtonText: 'Batal',
                    confirmButtonText: 'Ya, Setujui!',
                    reverseButtons: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            }
        });
    </script>
@endsection
