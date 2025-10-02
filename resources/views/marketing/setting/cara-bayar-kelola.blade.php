@extends('layouts.app')

@section('pageActive', 'SettingPPJB')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        {{-- Breadcrumb --}}
        <div x-data="{ pageName: 'SettingPPJB' }">
            @include('partials.breadcrumb')
        </div>

        {{-- === Cara Bayar PPJB === --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
            <div class="px-5 py-4 sm:px-6 sm:py-5">

                {{-- Header + Tombol Ajukan Baru --}}
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Cara Bayar PPJB</h3>

                    <div class="flex items-center gap-2">
                        {{-- Tombol Riwayat Cara Bayar --}}
                        {{-- <a href="{{ route('settingPPJB.mutu.history') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-800 text-sm rounded-md hover:bg-gray-300
                         dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">
                            Riwayat Cara Bayar PPJB
                        </a> --}}


                        {{-- Tombol Ajukan Baru (hanya jika tidak ada pending) --}}
                        @if (!$caraBayarPending)
                            <button data-modal-target="modal-create" data-modal-toggle="modal-create"
                                class="px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700">
                                Ajukan Cara Bayar Baru
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Jika tidak ada Cara Bayar sama sekali --}}
                @if (!$caraBayarActive && !$caraBayarPending)
                    <div class="mb-4">
                        <div
                            class="rounded-xl border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/20 p-4 text-center">
                            <p class="text-sm text-gray-600 dark:text-gray-300">Belum ada Cara Bayar PPJB aktif saat ini.
                            </p>
                        </div>
                    </div>
                @endif

                {{-- Cara Bayar Aktif --}}
                @if ($caraBayarActive)
                    <div class="mb-4">
                        <div
                            class="rounded-xl border border-green-200 bg-green-50 dark:border-green-700 dark:bg-green-900/20 p-4 flex flex-col">

                            {{-- Header --}}
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-semibold text-green-800 dark:text-green-300 text-lg">
                                    Cara Bayar PPJB (Aktif)
                                </h4>
                                <span class="px-2 py-0.5 text-xs rounded bg-green-100 text-green-700">ACC</span>
                            </div>

                            {{-- List Items --}}
                            <div class="flex flex-wrap gap-4 mb-4">
                                <!-- Jumlah Cicilan -->
                                <div class="flex-1 min-w-[150px]">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                        Jumlah Cicilan
                                    </label>
                                    <input type="text" readonly value="{{ $caraBayarActive->jumlah_cicilan }} x"
                                        class="bg-gray-50 border text-gray-900 text-sm rounded-lg w-full p-2.5 dark:bg-gray-700 dark:text-white">
                                </div>

                                <!-- Minimal DP -->
                                <div class="flex-1 min-w-[150px]">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                        Minimal DP
                                    </label>
                                    <input type="text" readonly
                                        value="Rp {{ number_format($caraBayarActive->minimal_dp, 0, ',', '.') }}"
                                        class="bg-gray-50 border text-gray-900 text-sm rounded-lg w-full p-2.5 dark:bg-gray-700 dark:text-white">
                                </div>

                                <!-- Status -->
                                <div class="flex-1 min-w-[150px]">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                        Status
                                    </label>
                                    <input type="text" readonly value="Aktif"
                                        class="bg-green-50 border border-green-300 text-green-700 text-sm font-semibold rounded-lg w-full p-2.5">
                                </div>

                                <!-- Diajukan Oleh -->
                                <div class="flex-1 min-w-[150px]">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                        Diajukan Oleh
                                    </label>
                                    <input type="text" readonly value="{{ $caraBayarActive->pengaju->username ?? '-' }}"
                                        class="bg-gray-50 border text-gray-900 text-sm rounded-lg w-full p-2.5 dark:bg-gray-700 dark:text-white">
                                </div>

                                <!-- Disetujui Oleh -->
                                <div class="flex-1 min-w-[150px]">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                        Disetujui Oleh
                                    </label>
                                    <input type="text" readonly value="{{ $caraBayarActive->approver->username ?? '-' }}"
                                        class="bg-gray-50 border text-gray-900 text-sm rounded-lg w-full p-2.5 dark:bg-gray-700 dark:text-white">
                                </div>
                            </div>

                            {{-- Info + Tombol Nonaktifkan --}}
                            <div class="flex justify-between items-center mt-2">
                                <p class="text-sm text-gray-500">
                                    Disetujui oleh <strong>{{ $caraBayarActive->approver->username ?? '-' }}</strong>
                                    pada {{ $caraBayarActive->updated_at?->translatedFormat('d M Y') ?? '-' }}
                                </p>
                                <div class="mt-4 flex justify-end">
                                    <form action="{{ route('settingPPJB.caraBayar.nonAktif', $caraBayarActive) }}"
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

                {{-- Cara Bayar Pending --}}
                @if ($caraBayarPending)
                    <div class="mb-4">
                        <div
                            class="rounded-xl border border-yellow-200 bg-yellow-50 dark:border-yellow-700 dark:bg-yellow-900/20 p-4 flex flex-col">

                            {{-- Header --}}
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-semibold text-yellow-800 dark:text-yellow-300 text-lg">
                                    Cara Bayar PPJB - Diajukan (Pending)
                                </h4>
                                <span class="px-2 py-0.5 text-xs rounded bg-yellow-100 text-yellow-700">Pending</span>
                            </div>

                            {{-- List Items --}}
                            <div class="flex flex-wrap gap-4 mb-4">
                                <!-- Jumlah Cicilan -->
                                <div class="flex-1 min-w-[150px]">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                        Jumlah Cicilan
                                    </label>
                                    <input type="text" readonly value="{{ $caraBayarPending->jumlah_cicilan }} x"
                                        class="bg-gray-50 border text-gray-900 text-sm rounded-lg w-full p-2.5 dark:bg-gray-700 dark:text-white">
                                </div>

                                <!-- Minimal DP -->
                                <div class="flex-1 min-w-[150px]">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                        Minimal DP
                                    </label>
                                    <input type="text" readonly
                                        value="Rp {{ number_format($caraBayarPending->minimal_dp, 0, ',', '.') }}"
                                        class="bg-gray-50 border text-gray-900 text-sm rounded-lg w-full p-2.5 dark:bg-gray-700 dark:text-white">
                                </div>

                                <!-- Status Pengajuan -->
                                <div class="flex-1 min-w-[150px]">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                        Status Pengajuan
                                    </label>
                                    <input type="text" readonly
                                        value="{{ ucfirst($caraBayarPending->status_pengajuan) }}"
                                        class="bg-yellow-50 border border-yellow-300 text-yellow-700 text-sm font-semibold rounded-lg w-full p-2.5">
                                </div>

                                <!-- Diajukan Oleh -->
                                <div class="flex-1 min-w-[150px]">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                        Diajukan Oleh
                                    </label>
                                    <input type="text" readonly
                                        value="{{ $caraBayarPending->pengaju->username ?? '-' }}"
                                        class="bg-gray-50 border text-gray-900 text-sm rounded-lg w-full p-2.5 dark:bg-gray-700 dark:text-white">
                                </div>

                                <!-- Disetujui Oleh -->
                                <div class="flex-1 min-w-[150px]">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                        Disetujui Oleh
                                    </label>
                                    <input type="text" readonly
                                        value="{{ $caraBayarPending->approver->username ?? '-' }}"
                                        class="bg-gray-50 border text-gray-900 text-sm rounded-lg w-full p-2.5 dark:bg-gray-700 dark:text-white">
                                </div>
                            </div>

                            {{-- Info + Tombol Batalkan --}}
                            <div class="flex justify-between items-center">
                                <p class="text-sm text-gray-500 mt-2">
                                    Diajukan oleh <strong>{{ $caraBayarPending->pengaju->username ?? '-' }}</strong>
                                </p>
                                <div class="mt-4 flex justify-end">
                                    <form
                                        action="{{ route('settingPPJB.caraBayar.cancelPengajuanPromo', $caraBayarPending) }}"
                                        method="POST" class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                            class="cancelPengajuan px-3 py-1 text-sm text-white bg-red-600 rounded hover:bg-red-700">
                                            Batalkan Pengajuan
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif


            </div>
        </div>

    </div>



    {{-- modal membuat pengajuan cara bayar baru --}}
    <div id="modal-create" tabindex="-1" aria-hidden="true"
        class="hidden fixed inset-0 z-50 flex items-center justify-center w-full h-full bg-black/40">

        <div class="relative w-full max-w-md p-4">
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700 flex flex-col max-h-[90vh]">

                <!-- Header -->
                <div class="flex items-center justify-between p-4 border-b dark:border-gray-600">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Ajukan Cara Bayar PPJB Baru
                    </h3>
                    <button type="button"
                        class="text-gray-400 hover:text-gray-900 hover:bg-gray-200 rounded-lg w-8 h-8 flex items-center justify-center dark:hover:bg-gray-600 dark:hover:text-white"
                        data-modal-toggle="modal-create">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                        </svg>
                        <span class="sr-only">Close</span>
                    </button>
                </div>

                <!-- Body (scrollable) -->
                <div class="overflow-y-auto px-4 py-6 space-y-4 flex-1">
                    <form id="mutuForm" action="{{ route('settingPPJB.caraBayar.updatePengajuan') }}" method="POST"
                        class="space-y-4">
                        @csrf

                        {{-- Hidden Perumahaan --}}
                        @php
                            $perumahaanId = auth()->user()->hasGlobalAccess()
                                ? session('current_perumahaan_id')
                                : Auth::user()->perumahaan_id;
                        @endphp
                        <input type="hidden" name="perumahaan_id" value="{{ $perumahaanId }}">


                        <div id="mutu-list" class="space-y-3">
                            {{-- Jumlah Cicila --}}
                            <div class="flex-1 flex flex-col">
                                <label class="text-sm text-gray-700 dark:text-gray-300 mb-1" for="jumlah_cicilan">
                                    Jumlah Cicilan (X)
                                </label>
                                <input type="number" required name="jumlah_cicilan" placeholder="Contoh: 3 x"
                                    class="bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                                dark:bg-gray-600 dark:text-white dark:placeholder-gray-400
                                focus:ring-primary-600 focus:border-primary-600" />
                            </div>

                            {{-- Nominal Cara Bayar --}}
                            <div class="flex flex-col" x-data="rupiahInput('')">
                                <label class="text-sm text-gray-700 dark:text-gray-300 mb-1" for="minimal_dp">
                                    Minimal Dp (Rp)
                                </label>

                                <!-- Input tampilan -->
                                <input type="text" placeholder="Minimal Dp" x-model="display"
                                    @input="onInput($event); $refs.hidden.value = value"
                                    class="bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                                    dark:bg-gray-600 dark:text-white dark:placeholder-gray-400
                                    focus:ring-primary-600 focus:border-primary-600" />

                                <!-- Hidden input dikirim ke server -->
                                <input type="hidden" x-ref="hidden" name="minimal_dp" required :value="value">
                            </div>

                        </div>
                    </form>
                </div>

                <!-- Footer -->
                <div class="flex justify-end gap-3 p-4 border-t dark:border-gray-600">
                    <button type="button" data-modal-toggle="modal-create"
                        class="px-4 py-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-100">
                        Batal
                    </button>
                    <button type="submit" form="mutuForm"
                        class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
                        Simpan
                    </button>
                </div>

            </div>
        </div>
    </div>



    {{-- sweatalert 2 for batalkan pengajuan Cara Bayar data --}}
    <script>
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
    </script>


@endsection
