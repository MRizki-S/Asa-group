@extends('layouts.app')

@section('pageActive', 'SettingPPJB')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        {{-- Breadcrumb --}}
        <div x-data="{ pageName: 'SettingPPJB' }">
            @include('partials.breadcrumb')
        </div>

        {{-- === Keterlambatan PPJB === --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
            <div class="px-5 py-4 sm:px-6 sm:py-5">

                {{-- Header + Tombol Ajukan Baru --}}
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Keterlambatan PPJB</h3>

                    <div class="flex items-center gap-2">
                        {{-- Tombol Riwayat Keterlambatan --}}
                        {{-- <a href="{{ route('settingPPJB.mutu.history') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-800 text-sm rounded-md hover:bg-gray-300
                         dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">
                            Riwayat Keterlambatan PPJB
                        </a> --}}


                        {{-- Tombol Ajukan Baru (hanya jika tidak ada pending) --}}
                        @hasrole(['Project Manager', 'Super Admin '])
                            @if (!$keterlambatanPending)
                                <button data-modal-target="modal-create" data-modal-toggle="modal-create"
                                    class="px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700">
                                    Ajukan Keterlambatan Baru
                                </button>
                            @endif
                        @endrole
                    </div>
                </div>

                {{-- Jika tidak ada Keterlambatan sama sekali --}}
                @if (!$keterlambatanActive && !$keterlambatanPending)
                    <div class="mb-4">
                        <div
                            class="rounded-xl border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/20 p-4 text-center">
                            <p class="text-sm text-gray-600 dark:text-gray-300">Belum ada Keterlambatan PPJB aktif saat ini.
                            </p>
                        </div>
                    </div>
                @endif

                {{-- Keterlambatan Aktif --}}
                @if ($keterlambatanActive)
                    <div class="mb-4">
                        <div
                            class="rounded-xl border border-green-200 bg-green-50 dark:border-green-700 dark:bg-green-900/20 p-4 flex flex-col">

                            {{-- Header --}}
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-semibold text-green-800 dark:text-green-300 text-lg">
                                    Keterlambatan PPJB (Aktif)
                                </h4>
                                <span class="px-2 py-0.5 text-xs rounded bg-green-100 text-green-700">ACC</span>
                            </div>

                            {{-- List Items --}}
                            <div class="flex flex-wrap gap-4 mb-4">
                                <!-- Persentase Denda -->
                                <div class="flex-1 min-w-[150px]">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                        Persentase Denda
                                    </label>
                                    <input type="text" readonly
                                        value="{{ rtrim(rtrim(number_format($keterlambatanActive->persentase_denda, 2, '.', ''), '0'), '.') }} %"
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
                                    <input type="text" readonly
                                        value="{{ $keterlambatanActive->pengaju->username ?? '-' }}"
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
                                    <input type="text" readonly
                                        value="{{ $keterlambatanActive->approver->username ?? '-' }}"
                                        class="bg-gray-50 border text-gray-900 text-sm rounded-lg w-full p-2.5
                              focus:ring-blue-500 focus:border-blue-500
                              dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white
                              dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                </div>
                            </div>

                            {{-- Info + Tombol Nonaktifkan --}}
                            <div class="flex justify-between items-center mt-2">
                                <p class="text-sm text-gray-500">
                                    Disetujui oleh <strong>{{ $keterlambatanActive->approver->username ?? '-' }}</strong>
                                    pada {{ $keterlambatanActive->updated_at?->translatedFormat('d M Y') ?? '-' }}
                                </p>
                                <div class="mt-4 flex justify-end">
                                    <form action="{{ route('settingPPJB.keterlambatan.nonAktif', $keterlambatanActive) }}"
                                        method="POST" class="delete-form">
                                        @csrf
                                        @method('PATCH')
                                        <button type="button"
                                            class="nonAktifkanKeterlambatan px-3 py-1 text-sm bg-red-600 text-white rounded-md hover:bg-red-700">
                                            Nonaktifkan
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Keterlambatan Pending --}}
                @if ($keterlambatanPending)
                    <div class="mb-4">
                        <div
                            class="rounded-xl border border-yellow-200 bg-yellow-50 dark:border-yellow-700 dark:bg-yellow-900/20 p-4 flex flex-col">

                            {{-- Header --}}
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-semibold text-yellow-800 dark:text-yellow-300 text-lg">
                                    Keterlambatan PPJB - Diajukan (Pending)
                                </h4>
                                <span class="px-2 py-0.5 text-xs rounded bg-yellow-100 text-yellow-700">Pending</span>
                            </div>

                            {{-- List Items --}}
                            <div class="flex flex-wrap gap-4 mb-4">
                                <!-- Persentase Denda -->
                                <div class="flex-1 min-w-[150px]">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                        Persentase Denda
                                    </label>
                                    <input type="text" readonly
                                        value="{{ rtrim(rtrim(number_format($keterlambatanPending->persentase_denda, 2, '.', ''), '0'), '.') }} %"
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
                                    <input type="text" readonly value="Pending"
                                        class="bg-yellow-50 border border-yellow-300 text-yellow-700 text-sm font-semibold rounded-lg w-full p-2.5
                           dark:bg-yellow-900 dark:border-yellow-700 dark:text-yellow-100">
                                </div>

                                <!-- Diajukan Oleh -->
                                <div class="flex-1 min-w-[150px]">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                        Diajukan Oleh
                                    </label>
                                    <input type="text" readonly
                                        value="{{ $keterlambatanPending->pengaju->username ?? '-' }}"
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
                                    <input type="text" readonly
                                        value="{{ $keterlambatanPending->approver->username ?? '-' }}"
                                        class="bg-gray-50 border text-gray-900 text-sm rounded-lg w-full p-2.5
                              focus:ring-blue-500 focus:border-blue-500
                              dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white
                              dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                </div>
                            </div>
                            {{-- Info + Tombol Batalkan --}}
                            <div class="flex justify-between items-center">
                                <p class="text-sm text-gray-500 mt-2">
                                    Diajukan oleh <strong>{{ $keterlambatanPending->pengaju->username ?? '-' }}</strong>
                                </p>
                                <div class="flex gap-2 sm:justify-end justify-start">
                                    @hasrole(['Manager Keuangan', 'Super Admin'])
                                        {{-- Tombol Tolak --}}
                                        <form action="{{ route('settingPPJB.keterlambatan.reject', $keterlambatanPending) }}" method="POST"
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
                                        <form action="{{ route('settingPPJB.keterlambatan.approve', $keterlambatanPending) }}"method="POST"
                                            class="approve-form">
                                            @csrf
                                            @method('PATCH')
                                            <button type="button"
                                                class="accPengajuan flex items-center gap-1 px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-sm rounded-md transition-all">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M5 13l4 4L19 7" />
                                                </svg>
                                                ACC
                                            </button>
                                        </form>
                                    @endrole

                                    @hasrole(['Project Manager', 'Super Admin '])
                                    <form
                                        action="{{ route('settingPPJB.keterlambatan.cancelPengajuanPromo', $keterlambatanPending) }}"
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
        </div>

    </div>



    {{-- modal membuat pengajuan Keterlambatan baru --}}
    <div id="modal-create" tabindex="-1" aria-hidden="true"
        class="hidden fixed inset-0 z-50 flex items-center justify-center w-full h-full bg-black/40">

        <div class="relative w-full max-w-md p-4">
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700 flex flex-col max-h-[90vh]">

                <!-- Header -->
                <div class="flex items-center justify-between p-4 border-b dark:border-gray-600">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Ajukan Keterlambatan PPJB Baru
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
                    <form id="mutuForm" action="{{ route('settingPPJB.keterlambatan.updatePengajuan') }}"
                        method="POST" class="space-y-4">
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
                                <label class="text-sm text-gray-700 dark:text-gray-300 mb-1" for="persentase_denda">
                                    Persentase Denda Keterlambatan (%)
                                </label>
                                <input type="number" name="persentase_denda" required
                                    placeholder="Masukan persentase denda keterlambatan"
                                    class="bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                                dark:bg-gray-600 dark:text-white dark:placeholder-gray-400
                                focus:ring-primary-600 focus:border-primary-600" />
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



    <script>
        // {{-- sweatalert 2 for batalkan pengajuan Keterlambatan data --}}
        document.addEventListener('click', function(e) {
            if (e.target.closest('.cancelPengajuan')) {
                const btn = e.target.closest('.cancelPengajuan');
                const form = btn.closest('.delete-form');

                Swal.fire({
                    title: 'Yakin membatalkan pengajuan Keterlambatan ini?',
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

        // {{-- sweatalert 2 for nonaktifkan Keterlambatan data --}}
        document.addEventListener('click', function(e) {
            if (e.target.closest('.nonAktifkanKeterlambatan')) {
                const btn = e.target.closest('.nonAktifkanKeterlambatan');
                const form = btn.closest('.delete-form');

                Swal.fire({
                    title: 'Yakin menonaktifkan Keterlambatan?',
                    text: 'Menonaktifkan Keterlambatan akan berpengaruh pada proses PPJB dan semua data terkait batch ini. Pastikan Anda ingin melanjutkan.',
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


        // 🛑 SweetAlert untuk Tolak Pengajuan (Keterlambatan ini)
        document.addEventListener('click', function(e) {
            if (e.target.closest('.tolakPengajuan')) {
                const btn = e.target.closest('.tolakPengajuan');
                const form = btn.closest('.delete-form');

                Swal.fire({
                    title: 'Tolak Pengajuan Keterlambatan ini?',
                    text: 'Apakah Anda yakin ingin menolak pengajuan Keterlambatan ini ini? Tindakan ini tidak dapat dibatalkan.',
                    icon: 'warning',
                    showCancelButton: true,
                    cancelButtonColor: '#6b7280',
                    confirmButtonColor: '#dc2626',
                    cancelButtonText: 'Batal',
                    confirmButtonText: 'Ya, Tolak!',
                    reverseButtons: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            }
        });

        // ✅ SweetAlert untuk ACC Pengajuan Keterlambatan ini
        document.addEventListener('click', function(e) {
            if (e.target.closest('.accPengajuan')) {
                const btn = e.target.closest('.accPengajuan');
                const form = btn.closest('.approve-form');

                Swal.fire({
                    title: 'Setujui Pengajuan Keterlambatan ini ini?',
                    text: 'Hanya satu pengajuan Keterlambatan ini yang bisa aktif. Jika disetujui, Keterlambatan ini aktif sebelumnya akan dinonaktifkan dan digantikan dengan pengajuan ini.',
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
