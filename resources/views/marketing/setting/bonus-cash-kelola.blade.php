@extends('layouts.app')

@section('pageActive', 'SettingPPJB')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        {{-- Breadcrumb --}}
        <div x-data="{ pageName: 'SettingPPJB' }">
            @include('partials.breadcrumb')
        </div>

        {{-- === Bonus Cash PPJB === --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
            <div class="px-5 py-4 sm:px-6 sm:py-5">

                {{-- Header + Tombol Ajukan Baru --}}
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Bonus Cash PPJB</h3>

                    <div class="flex items-center gap-2">
                        {{-- Tombol Riwayat Bonus Cash --}}
                        <a href="{{ route('settingPPJB.bonusCash.history') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-800 text-sm rounded-md hover:bg-gray-300
                         dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">
                            Riwayat Bonus Cash PPJB
                        </a>


                        {{-- Tombol Ajukan Baru (hanya jika tidak ada pending) --}}
                        @hasrole(['Manager Pemasaran', 'Super Admin '])
                            @if (!$bonusCashPending)
                                <button data-modal-target="modal-create" data-modal-toggle="modal-create"
                                    class="px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700">
                                    Ajukan Bonus Cash PPJB Baru
                                </button>
                            @endif
                        @endrole
                    </div>
                </div>

                {{-- Jika tidak ada batch sama sekali --}}
                @if (!$bonusCashActive && !$bonusCashPending)
                    <div class="mb-4">
                        <div
                            class="rounded-xl border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/20 p-4 text-center">
                            <p class="text-sm text-gray-600 dark:text-gray-300">Belum ada Bonus Cash PPJB aktif saat ini.
                            </p>
                        </div>
                    </div>
                @endif

                {{-- Batch Aktif --}}
                @if ($bonusCashActive)
                    <div class="mb-4">
                        <div
                            class="rounded-xl border border-green-200 bg-green-50 dark:border-green-700 dark:bg-green-900/20 p-4 flex flex-col">

                            {{-- Header --}}
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-semibold text-green-800 dark:text-green-300 text-lg">Bonus Cash PPJB (Aktif)
                                </h4>
                                <span class="px-2 py-0.5 text-xs rounded bg-green-100 text-green-700">ACC</span>
                            </div>

                            {{-- List Items --}}
                            <ul class="space-y-3 mb-4">
                                @if ($bonusCashActive->items->count())
                                    @foreach ($bonusCashActive->items as $item)
                                        <li class="flex items-center">
                                            <span class="text-gray-800 dark:text-gray-200">{{ $item->nama_bonus }}</span>
                                        </li>
                                    @endforeach
                                @else
                                    <li class="text-gray-500 dark:text-gray-400 italic">Belum ada mutu aktif.</li>
                                @endif
                            </ul>

                            {{-- Info + Tombol Nonaktifkan --}}
                            <div class="flex justify-between items-center mt-2">
                                <p class="text-sm text-gray-500">
                                    Disetujui oleh <strong>{{ $bonusCashActive->penyetuju->username ?? '-' }}</strong>
                                    pada
                                    {{ $bonusCashActive->tanggal_acc ? $bonusCashActive->tanggal_acc->translatedFormat('d M Y') : '-' }}
                                </p>
                                <div class="mt-4 flex justify-end">
                                    <form action="{{ route('settingPPJB.bonusCash.nonAktif', $bonusCashActive) }}"
                                        method="POST" class="delete-form">
                                        @csrf
                                        @method('PATCH')
                                        <button type="button"
                                            class="nonAktifkanBonusCash Cash px-3 py-1 text-sm bg-red-600 text-white rounded-md hover:bg-red-700">
                                            Nonaktifkan
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Batch Pending --}}
                @if ($bonusCashPending)
                    <div class="mb-4">
                        <div
                            class="rounded-xl border border-yellow-200 bg-yellow-50 dark:border-yellow-700 dark:bg-yellow-900/20 p-4 flex flex-col">

                            {{-- Header --}}
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-semibold text-yellow-800 dark:text-yellow-300 text-lg">Bonus Cash PPJB -
                                    Diajukan
                                    (Pending)</h4>
                                <span class="px-2 py-0.5 text-xs rounded bg-yellow-100 text-yellow-700">Pending</span>
                            </div>

                            {{-- List Items --}}
                            <ul class="list-decimal list-inside space-y-1 text-gray-700 dark:text-gray-300 text-sm">
                                @foreach ($bonusCashPending->items as $item)
                                    <li>{{ $item->nama_bonus }}
                                    </li>
                                @endforeach
                            </ul>

                            {{-- Info + Tombol Batalkan --}}
                            <div class="flex justify-between items-center">
                                <p class="text-sm text-gray-500 mt-2">
                                    Diajukan oleh <strong>{{ $bonusCashPending->pengaju->username ?? '-' }}</strong>
                                    pada {{ $bonusCashPending->tanggal_pengajuan->format('d M Y') }}
                                </p>
                                <div class="flex gap-2 sm:justify-end justify-start">
                                    @hasrole(['Manager Keuangan', 'Super Admin'])
                                        {{-- Tombol Tolak --}}
                                        <form action="{{ route('settingPPJB.bonusCash.reject', $bonusCashPending) }}"
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
                                            action="{{ route('settingPPJB.bonusCash.approve', $bonusCashPending) }}"method="POST"
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

                                    @hasrole(['Manager Pemasaran', 'Super Admin '])
                                        <form action="{{ route('settingPPJB.bonusCash.cancel', $bonusCashPending) }}"
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




    {{-- Modal Ajukan Bonus Cash PPJB Baru --}}
    <div id="modal-create" tabindex="-1" aria-hidden="true"
        class="hidden fixed inset-0 z-50 flex items-center justify-center w-full h-full bg-black/40">

        <div class="relative w-full max-w-xl p-4">
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700 flex flex-col max-h-[90vh]">

                {{-- Header --}}
                <div class="flex items-center justify-between p-4 border-b dark:border-gray-600">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Ajukan Bonus Cash PPJB Baru
                    </h3>
                    <button type="button"
                        class="text-gray-400 hover:text-gray-900 hover:bg-gray-200 rounded-lg w-8 h-8 flex items-center justify-center dark:hover:bg-gray-600 dark:hover:text-white"
                        data-modal-toggle="modal-create">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M1 1l6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                        </svg>
                        <span class="sr-only">Close</span>
                    </button>
                </div>

                {{-- Body --}}
                <div class="overflow-y-auto px-4 py-6 space-y-4 flex-1">
                    <form id="bonusCashForm" action="{{ route('settingPPJB.bonusCash.pengajuanUpdate') }}" method="POST"
                        class="space-y-4">
                        @csrf

                        {{-- Hidden perumahaan --}}
                        <input type="hidden" name="perumahaan_id"
                            value="{{ auth()->user()->hasGlobalAccess() ? session('current_perumahaan_id') : Auth::user()->perumahaan_id }}">

                        {{-- Dynamic input list --}}
                        <div id="bonusCash-list" class="space-y-3">
                            <div class="flex items-center gap-2 bonusCash-item">
                                {{-- Nama Bonus Cash --}}
                                <div class="flex-1 flex flex-col">
                                    <label class="text-sm text-gray-700 dark:text-gray-300 mb-1">
                                        Nama Bonus Cash
                                    </label>
                                    <input type="text" name="nama_bonus[]" placeholder="Masukkan nama bonus"
                                        class="bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                                    dark:bg-gray-600 dark:text-white dark:placeholder-gray-400
                                    focus:ring-blue-600 focus:border-blue-600" />
                                </div>

                                {{-- Tombol hapus --}}
                                <button type="button"
                                    class="remove-btn text-red-600 hover:text-red-800 text-xl font-bold mt-6">&times;</button>
                            </div>
                        </div>

                        {{-- Tombol tambah --}}
                        <div>
                            <button type="button" id="addBonusCash"
                                class="px-3 py-1 text-sm rounded-md bg-green-600 text-white hover:bg-green-700">
                                + Tambah Bonus Cash
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Footer --}}
                <div class="flex justify-end gap-3 p-4 border-t dark:border-gray-600">
                    <button type="button" data-modal-toggle="modal-create"
                        class="px-4 py-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-100">
                        Batal
                    </button>
                    <button type="submit" form="bonusCashForm"
                        class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
                        Simpan
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- Script Dinamis --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const list = document.getElementById("bonusCash-list");
            const addRow = document.getElementById("addBonusCash");

            // Tambah row baru
            addRow.addEventListener("click", () => {
                const div = document.createElement("div");
                div.classList.add("flex", "items-center", "gap-2", "bonusCash-item");
                div.innerHTML = `
                <div class="flex-1 flex flex-col">
                    <label class="text-sm text-gray-700 dark:text-gray-300 mb-1">
                        Nama Bonus Cash
                    </label>
                    <input type="text" name="nama_bonus[]" placeholder="Masukkan nama bonus"
                        class="bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                        dark:bg-gray-600 dark:text-white dark:placeholder-gray-400
                        focus:ring-blue-600 focus:border-blue-600" />
                </div>
                <button type="button" class="remove-btn text-red-600 hover:text-red-800 text-xl font-bold mt-6">&times;</button>
            `;
                list.appendChild(div);
            });

            // Hapus row
            list.addEventListener("click", (e) => {
                if (e.target.classList.contains("remove-btn")) {
                    e.target.closest(".bonusCash-item").remove();
                }
            });
        });



        // {{-- sweatalert 2 for batalkan pengajuan Bonus Cash data --}}
        document.addEventListener('click', function(e) {
            if (e.target.closest('.cancelPengajuan')) {
                const btn = e.target.closest('.cancelPengajuan');
                const form = btn.closest('.delete-form');

                Swal.fire({
                    title: 'Yakin membatalkan pengajuan Bonus Cash ini?',
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


        // {{-- sweatalert 2 for nonaktifkan Bonus Cash data --}}
        document.addEventListener('click', function(e) {
            if (e.target.closest('.nonAktifkanBonusCash')) {
                const btn = e.target.closest('.nonAktifkanBonusCash');
                const form = btn.closest('.delete-form');

                Swal.fire({
                    title: 'Yakin menonaktifkan Bonus Cash?',
                    text: 'Menonaktifkan Bonus Cash akan berpengaruh pada proses PPJB dan semua data terkait batch ini. Pastikan Anda ingin melanjutkan.',
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

        // 🛑 SweetAlert untuk Tolak Pengajuan (Bonus Cash)
        document.addEventListener('click', function(e) {
            if (e.target.closest('.tolakPengajuan')) {
                const btn = e.target.closest('.tolakPengajuan');
                const form = btn.closest('.delete-form');

                Swal.fire({
                    title: 'Tolak Pengajuan Bonus Cash?',
                    text: 'Apakah Anda yakin ingin menolak pengajuan Bonus Cash ini? Tindakan ini tidak dapat dibatalkan.',
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

        // ✅ SweetAlert untuk ACC Pengajuan Bonus Cash
        document.addEventListener('click', function(e) {
            if (e.target.closest('.accPengajuan')) {
                const btn = e.target.closest('.accPengajuan');
                const form = btn.closest('.approve-form');

                Swal.fire({
                    title: 'Setujui Pengajuan Bonus Cash ini?',
                    text: 'Hanya satu pengajuan Bonus Cash yang bisa aktif. Jika disetujui, Bonus Cash aktif sebelumnya akan dinonaktifkan dan digantikan dengan pengajuan ini.',
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
