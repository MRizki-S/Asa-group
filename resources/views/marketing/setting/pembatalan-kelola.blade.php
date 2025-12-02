@extends('layouts.app')

@section('pageActive', 'SettingPPJB')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        {{-- Breadcrumb --}}
        <div x-data="{ pageName: 'SettingPPJB' }">
            @include('partials.breadcrumb')
        </div>

        {{-- === Pembatalan PPJB === --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
            <div class="px-5 py-4 sm:px-6 sm:py-5">

                {{-- Header + Tombol Ajukan Baru --}}
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90 flex items-center gap-2">
                            Pembatalan PPJB
                            <span
                                class="inline-flex items-center gap-1 text-sm font-semibold text-green-700 bg-green-100 dark:text-green-300 dark:bg-green-900/40 px-2 py-0.5 rounded-full shadow-sm animate-pulse">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <circle cx="10" cy="10" r="10" />
                                </svg>
                                Aktif
                            </span>
                        </h3>
                    </div>

                    @hasrole(['Project Manager', 'Super Admin '])
                        <div class="flex items-center gap-2">
                            {{-- Tombol Ajukan Baru (hanya jika tidak ada pending) --}}
                            @if (!$pembatalanPending)
                                <button data-modal-target="modal-create" data-modal-toggle="modal-create"
                                    class="px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700">
                                    Ajukan Pembatalan Baru
                                </button>
                            @endif
                        </div>
                    @endrole
                </div>

                {{-- Jika tidak ada Pembatalan sama sekali --}}
                @if (!$pembatalanActive && !$pembatalanPending)
                    <div class="mb-4">
                        <div
                            class="rounded-xl border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/20 p-4 text-center">
                            <p class="text-sm text-gray-600 dark:text-gray-300">Belum ada Pembatalan PPJB aktif saat ini.
                            </p>
                        </div>
                    </div>
                @endif

                {{-- Pembatalan Aktif --}}
                @if ($pembatalanActive)
                    <div class="mt-5 space-y-4">

                        {{-- KPR Section --}}
                        <div
                            class="rounded-xl border border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-900/20 p-4 shadow-sm transition hover:shadow-md">
                            <div class="flex items-center justify-between mb-2">
                                <h5
                                    class="text-blue-800 dark:text-blue-300 font-semibold text-base flex items-center gap-2">
                                    Pembatalan KPR
                                </h5>
                                <span
                                    class="text-sm bg-blue-100 text-blue-800 dark:bg-blue-800/40 dark:text-blue-200 px-2 py-0.5 rounded-md font-medium">
                                    Dinamis
                                </span>
                            </div>

                            <p class="text-sm text-gray-800 dark:text-gray-200 leading-relaxed">
                                PIHAK KEDUA dikenakan potongan sebesar
                                <span
                                    class="font-semibold text-blue-700 dark:text-blue-300 border-b-2 border-dashed border-blue-400 pb-0.5">
                                    Rp{{ number_format($pembatalanActive->nominal_potongan_kpr ?? 0, 0, ',', '.') }},
                                </span>
                                dari Uang Muka atas objek dari perjanjian pemesanan ini apabila ditolak oleh Bank atau
                                adanya wanprestasi dari PIHAK KEDUA.
                            </p>

                            <p class="text-sm text-gray-800 dark:text-gray-200 leading-relaxed mt-2">
                                PIHAK KEDUA juga dikenakan denda sebesar
                                <span
                                    class="font-semibold text-blue-700 dark:text-blue-300 border-b-2 border-dashed border-blue-400 pb-0.5">
                                    {{ rtrim(rtrim(number_format($pembatalanActive->persentase_potongan ?? 0, 2, '.', ''), '0'), '.') }}%
                                </span>
                                dari Harga Jadi apabila adanya wanprestasi dari PIHAK KEDUA saat Surat Keputusan Bank (SP3)
                                telah turun atau dalam proses pembangunan.
                            </p>
                        </div>

                        {{-- Cash Section --}}
                        <div
                            class="rounded-xl border border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/20 p-4 shadow-sm transition hover:shadow-md">
                            <div class="flex items-center justify-between mb-2">
                                <h5
                                    class="text-green-800 dark:text-green-300 font-semibold text-base flex items-center gap-2">
                                    Pembatalan Cash
                                </h5>
                                <span
                                    class="text-sm bg-green-100 text-green-800 dark:bg-green-800/40 dark:text-green-200 px-2 py-0.5 rounded-md font-medium">
                                    Dinamis
                                </span>
                            </div>

                            <p class="text-sm text-gray-800 dark:text-gray-200 leading-relaxed">
                                PIHAK KEDUA dikenakan potongan sebesar
                                <span
                                    class="font-semibold text-green-700 dark:text-green-300 border-b-2 border-dashed border-green-400 pb-0.5">
                                    Rp{{ number_format($pembatalanActive->nominal_potongan_cash ?? 0, 0, ',', '.') }},
                                </span>
                                atas objek dari perjanjian pemesanan ini apabila adanya wanprestasi dari PIHAK KEDUA setelah
                                penandatanganan PPJB
                                atau sebelum dilakukan pembangunan.
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        @if ($pembatalanPending)
            {{-- === Pembatalan PPJB === --}}
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
                <div class="px-5 py-4 sm:px-6 sm:py-5">

                    {{-- Header + Tombol Ajukan Baru --}}
                    <div class="flex items-center justify-between mb-4">
                        {{-- Kiri: Judul + Status --}}
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90 flex items-center gap-2">
                            Pembatalan PPJB
                            <span
                                class="inline-flex items-center gap-1 text-sm font-semibold text-yellow-700 bg-yellow-100 dark:text-yellow-300 dark:bg-yellow-900/40 px-2 py-0.5 rounded-full shadow-sm animate-pulse">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <circle cx="10" cy="10" r="10" />
                                </svg>
                                Pending
                            </span>
                        </h3>

                        {{-- Kanan: Tombol Aksi --}}
                        <div class="flex items-center gap-2">
                            @hasrole(['Manager Keuangan', 'Super Admin'])
                                {{-- Tombol Tolak Pembatalan --}}
                                <form action="{{ route('settingPPJB.pembatalan.reject', $pembatalanPending) }}" method="POST"
                                    class="delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        class="tolakPembatalan flex items-center gap-1 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-sm rounded-md transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Tolak
                                    </button>
                                </form>

                                {{-- Tombol ACC Pembatalan --}}
                                <form action="{{ route('settingPPJB.pembatalan.approve', $pembatalanPending) }}" method="POST"
                                    class="approve-form">
                                    @csrf
                                    @method('PATCH')
                                    <button type="button"
                                        class="accPembatalan flex items-center gap-1 px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-sm rounded-md transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                        ACC
                                    </button>
                                </form>
                            @endhasrole

                            @hasrole(['Project Manager', 'Super Admin'])
                                {{-- Tombol Batalkan Pengajuan --}}
                                <form action="{{ route('settingPPJB.pembatalan.cancelPengajuanPromo', $pembatalanPending) }}"
                                    method="POST" class="delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        class="cancelPengajuan flex items-center gap-1 px-3 py-1.5 text-sm text-white bg-red-600 rounded-lg hover:bg-red-700 transition-all duration-150 shadow-sm hover:shadow-md">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.8" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Batalkan Pengajuan
                                    </button>
                                </form>
                            @endhasrole
                        </div>
                    </div>



                    <div class="mt-5 space-y-4">

                        {{-- KPR Section --}}
                        <div
                            class="rounded-xl border border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-900/20 p-4 shadow-sm transition hover:shadow-md">
                            <div class="flex items-center justify-between mb-2">
                                <h5
                                    class="text-blue-800 dark:text-blue-300 font-semibold text-base flex items-center gap-2">
                                    Pembatalan KPR
                                </h5>
                                <span
                                    class="text-sm bg-blue-100 text-blue-800 dark:bg-blue-800/40 dark:text-blue-200 px-2 py-0.5 rounded-md font-medium">
                                    Dinamis
                                </span>
                            </div>

                            <p class="text-sm text-gray-800 dark:text-gray-200 leading-relaxed">
                                PIHAK KEDUA dikenakan potongan sebesar
                                <span
                                    class="font-semibold text-blue-700 dark:text-blue-300 border-b-2 border-dashed border-blue-400 pb-0.5">
                                    Rp{{ number_format($pembatalanPending->nominal_potongan_kpr ?? 0, 0, ',', '.') }},
                                </span>
                                dari Uang Muka atas objek dari perjanjian pemesanan ini apabila ditolak oleh Bank atau
                                adanya wanprestasi dari PIHAK KEDUA.
                            </p>

                            <p class="text-sm text-gray-800 dark:text-gray-200 leading-relaxed mt-2">
                                PIHAK KEDUA juga dikenakan denda sebesar
                                <span
                                    class="font-semibold text-blue-700 dark:text-blue-300 border-b-2 border-dashed border-blue-400 pb-0.5">
                                    {{ rtrim(rtrim(number_format($pembatalanPending->persentase_potongan ?? 0, 2, '.', ''), '0'), '.') }}%
                                </span>
                                dari Harga Jadi apabila adanya wanprestasi dari PIHAK KEDUA saat Surat Keputusan Bank
                                (SP3)
                                telah turun atau dalam proses pembangunan.
                            </p>
                        </div>

                        {{-- Cash Section --}}
                        <div
                            class="rounded-xl border border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/20 p-4 shadow-sm transition hover:shadow-md">
                            <div class="flex items-center justify-between mb-2">
                                <h5
                                    class="text-green-800 dark:text-green-300 font-semibold text-base flex items-center gap-2">
                                    Pembatalan Cash
                                </h5>
                                <span
                                    class="text-sm bg-green-100 text-green-800 dark:bg-green-800/40 dark:text-green-200 px-2 py-0.5 rounded-md font-medium">
                                    Dinamis
                                </span>
                            </div>

                            <p class="text-sm text-gray-800 dark:text-gray-200 leading-relaxed">
                                PIHAK KEDUA dikenakan potongan sebesar
                                <span
                                    class="font-semibold text-green-700 dark:text-green-300 border-b-2 border-dashed border-green-400 pb-0.5">
                                    Rp{{ number_format($pembatalanPending->nominal_potongan_cash ?? 0, 0, ',', '.') }},
                                </span>
                                atas objek dari perjanjian pemesanan ini apabila adanya wanprestasi dari PIHAK KEDUA
                                setelah
                                penandatanganan PPJB
                                atau sebelum dilakukan pembangunan.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>



    {{-- modal membuat pengajuan Pembatalan baru --}}
    <div id="modal-create" tabindex="-1" aria-hidden="true"
        class="hidden fixed inset-0 z-99999 flex items-center justify-center w-full h-full bg-black/40">

        <div class="relative w-full max-w-xl p-4">
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700 flex flex-col max-h-[90vh]">

                <!-- Header -->
                <div class="flex items-center justify-between p-4 border-b dark:border-gray-600">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Ajukan Pembatalan PPJB Baru
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
                    <form id="mutuForm" action="{{ route('settingPPJB.pembatalan.updatePengajuan') }}" method="POST"
                        class="space-y-4">
                        @csrf

                        {{-- Hidden Perumahaan --}}
                        @php
                            $perumahaanId = auth()->user()->hasGlobalAccess()
                                ? session('current_perumahaan_id')
                                : Auth::user()->perumahaan_id;
                        @endphp
                        <input type="hidden" name="perumahaan_id" value="{{ $perumahaanId }}">

                        <div x-data="{
                            nominalPotonganKpr: '',
                            nominalPotonganCash: '',
                            formatNumber(value) {
                                let number = value.replace(/\D/g, '');
                                return number.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                            },
                            updateNominalPotonganKpr(e) {
                                this.nominalPotonganKpr = this.formatNumber(e.target.value);
                            },
                            updateNominalPotonganCash(e) {
                                this.nominalPotonganCash = this.formatNumber(e.target.value);
                            },
                            get nominalPotonganKprNumber() {
                                return this.nominalPotonganKpr.replace(/\./g, '');
                            },
                            get nominalPotonganCashNumber() {
                                return this.nominalPotonganCash.replace(/\./g, '');
                            },
                        }" id="mutu-list" class="space-y-5">

                            {{-- Persentase Potongan --}}
                            <div class="flex flex-col">
                                <label class="text-sm text-gray-700 dark:text-gray-300 mb-1" for="persentase_potongan">
                                    Persentase Potongan Pembatalan (%)
                                </label>
                                <input type="number" name="persentase_potongan" required
                                    placeholder="Masukkan persentase potongan pembatalan"
                                    class="bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
            dark:bg-gray-600 dark:text-white dark:placeholder-gray-400
            focus:ring-primary-600 focus:border-primary-600" />
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 italic">
                                    Contoh: Denda sebesar <span class="font-semibold">25%</span> dari harga jadi apabila
                                    terjadi wanprestasi saat SP3 telah turun atau proses pembangunan berlangsung.
                                </p>
                            </div>

                            {{-- ======== Section: Pembatalan KPR ======== --}}
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                <h4
                                    class="flex items-center gap-2 text-sm font-semibold text-primary-700 dark:text-primary-400 mb-3 uppercase tracking-wide">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 110 20 10 10 0 010-20z" />
                                    </svg>
                                    Pembatalan KPR
                                </h4>

                                <div class="flex flex-col">
                                    <label class="text-sm text-gray-700 dark:text-gray-300 mb-1"
                                        for="nominal_potongan_kpr">
                                        Nominal Potongan KPR (Rp)
                                    </label>
                                    <input type="text" x-model="nominalPotonganKpr" @input="updateNominalPotonganKpr"
                                        placeholder="Masukkan nominal potongan untuk pembatalan KPR"
                                        class="bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                dark:bg-gray-600 dark:text-white dark:placeholder-gray-400
                focus:ring-primary-600 focus:border-primary-600" />
                                    <input type="hidden" name="nominal_potongan_kpr" :value="nominalPotonganKprNumber">

                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 italic leading-relaxed">
                                        Berdasarkan ketentuan Pasal 5 ayat (2):<br>
                                        PIHAK KEDUA dikenakan potongan sebesar
                                        <span class="font-semibold">Rp 2.000.000</span> dari Uang Muka atas objek dari
                                        perjanjian pemesanan ini apabila ditolak oleh Bank atau adanya wanprestasi dari
                                        PIHAK KEDUA.
                                    </p>
                                </div>
                            </div>

                            {{-- ======== Section: Pembatalan Cash ======== --}}
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                <h4
                                    class="flex items-center gap-2 text-sm font-semibold text-amber-600 dark:text-amber-400 mb-3 uppercase tracking-wide">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Pembatalan Cash
                                </h4>

                                <div class="flex flex-col">
                                    <label class="text-sm text-gray-700 dark:text-gray-300 mb-1"
                                        for="nominal_potongan_cash">
                                        Nominal Potongan Cash (Rp)
                                    </label>
                                    <input type="text" x-model="nominalPotonganCash"
                                        @input="updateNominalPotonganCash"
                                        placeholder="Masukkan nominal potongan untuk pembatalan Cash"
                                        class="bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                dark:bg-gray-600 dark:text-white dark:placeholder-gray-400
                focus:ring-primary-600 focus:border-primary-600" />
                                    <input type="hidden" name="nominal_potongan_cash" :value="nominalPotonganCashNumber">

                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 italic leading-relaxed">
                                        Berdasarkan ketentuan Pasal 4 ayat (2):<br>
                                        PIHAK KEDUA dikenakan potongan sebesar
                                        <span class="font-semibold">Rp 10.000.000</span> atas objek dari perjanjian
                                        pemesanan
                                        ini apabila adanya wanprestasi dari PIHAK KEDUA setelah penandatanganan PPJB
                                        atau sebelum dilakukan pembangunan.
                                    </p>
                                </div>
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



    {{-- sweatalert 2 for batalkan pengajuan Pembatalan data --}}
    <script>
        document.addEventListener('click', function(e) {
            if (e.target.closest('.cancelPengajuan')) {
                const btn = e.target.closest('.cancelPengajuan');
                const form = btn.closest('.delete-form');

                Swal.fire({
                    title: 'Yakin membatalkan pengajuan Pembatalan ini?',
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

        // {{-- sweatalert 2 for nonaktifkan Pembatalan data --}}
        document.addEventListener('click', function(e) {
            if (e.target.closest('.nonAktifkanPembatalan')) {
                const btn = e.target.closest('.nonAktifkanPembatalan');
                const form = btn.closest('.delete-form');

                Swal.fire({
                    title: 'Yakin menonaktifkan Pembatalan?',
                    text: 'Menonaktifkan Pembatalan akan berpengaruh pada proses PPJB dan semua data terkait batch ini. Pastikan Anda ingin melanjutkan.',
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

        // 🛑 SweetAlert untuk Tolak Pengajuan (Pembatalan)
        document.addEventListener('click', function(e) {
            if (e.target.closest('.tolakPembatalan')) {
                const btn = e.target.closest('.tolakPembatalan');
                const form = btn.closest('.delete-form');

                Swal.fire({
                    title: 'Tolak Pengajuan Pembatalan?',
                    text: 'Apakah Anda yakin ingin menolak pengajuan pembatalan ini? Tindakan ini tidak dapat dibatalkan.',
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

        // ✅ SweetAlert untuk ACC Pengajuan Pembatalan
        document.addEventListener('click', function(e) {
            if (e.target.closest('.accPembatalan')) {
                const btn = e.target.closest('.accPembatalan');
                const form = btn.closest('.approve-form');

                Swal.fire({
                    title: 'Setujui Pengajuan Pembatalan?',
                    text: 'Hanya satu pengajuan pembatalan yang bisa aktif. Jika disetujui, pembatalan aktif sebelumnya akan dinonaktifkan dan digantikan dengan pengajuan ini.',
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
