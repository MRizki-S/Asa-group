@extends('layouts.app')

@section('pageActive', 'SettingPPJB')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        {{-- Breadcrumb --}}
        <div x-data="{ pageName: 'SettingPPJB' }">
            @include('partials.breadcrumb')
        </div>

        {{-- === Mutu PPJB === --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
            <div class="px-5 py-4 sm:px-6 sm:py-5">

                {{-- Header + Tombol Ajukan Baru --}}
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Mutu PPJB</h3>

                    <div class="flex items-center gap-2">
                        {{-- Tombol Riwayat Mutu --}}
                        <a href="{{ route('settingPPJB.mutu.history') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-800 text-sm rounded-md hover:bg-gray-300
                         dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">
                            Riwayat Mutu PPJB
                        </a>


                        {{-- Tombol Ajukan Baru (hanya jika tidak ada pending) --}}
                        @if (!$mutuPending)
                            <button data-modal-target="modal-create" data-modal-toggle="modal-create"
                                class="px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700">
                                Ajukan Mutu PPJB Baru
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Jika tidak ada batch sama sekali --}}
                @if (!$mutuActive && !$mutuPending)
                    <div class="mb-4">
                        <div
                            class="rounded-xl border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/20 p-4 text-center">
                            <p class="text-sm text-gray-600 dark:text-gray-300">Belum ada Mutu PPJB aktif saat ini.</p>
                        </div>
                    </div>
                @endif

                {{-- Batch Aktif --}}
                @if ($mutuActive)
                    <div class="mb-4">
                        <div
                            class="rounded-xl border border-green-200 bg-green-50 dark:border-green-700 dark:bg-green-900/20 p-4 flex flex-col">

                            {{-- Header --}}
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-semibold text-green-800 dark:text-green-300 text-lg">Mutu PPJB (Aktif)</h4>
                                <span class="px-2 py-0.5 text-xs rounded bg-green-100 text-green-700">ACC</span>
                            </div>

                            {{-- List Items --}}
                            <ul class="space-y-3 mb-4">
                                @if ($mutuActive->items->count())
                                    @foreach ($mutuActive->items as $item)
                                        <li class="flex items-center">
                                            <span class="text-gray-800 dark:text-gray-200">{{ $item->nama_mutu }}</span>
                                            <div
                                                class="flex-grow mx-2 border-b border-dashed border-gray-300 dark:border-gray-600">
                                            </div>
                                            <span class="text-sm text-gray-500 dark:text-gray-400">Rp
                                                {{ number_format($item->nominal_mutu, 0, ',', '.') }}</span>
                                        </li>
                                    @endforeach
                                @else
                                    <li class="text-gray-500 dark:text-gray-400 italic">Belum ada mutu aktif.</li>
                                @endif
                            </ul>

                            {{-- Info + Tombol Nonaktifkan --}}
                            <div class="flex justify-between items-center mt-2">
                                <p class="text-sm text-gray-500">
                                    Disetujui oleh <strong>{{ $mutuActive->penyetuju->username ?? '-' }}</strong>
                                    pada
                                    {{ $mutuActive->tanggal_acc ? $mutuActive->tanggal_acc->translatedFormat('d M Y') : '-' }}
                                </p>
                                <div class="mt-4 flex justify-end">
                                    <form action="{{ route('settingPPJB.mutu.nonAktif', $mutuActive) }}" method="POST"
                                        class="delete-form">
                                        @csrf
                                        @method('PATCH')
                                        <button type="button"
                                            class="nonAktifkanMutu px-3 py-1 text-sm bg-red-600 text-white rounded-md hover:bg-red-700">
                                            Nonaktifkan
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Batch Pending --}}
                @if ($mutuPending)
                    <div class="mb-4">
                        <div
                            class="rounded-xl border border-yellow-200 bg-yellow-50 dark:border-yellow-700 dark:bg-yellow-900/20 p-4 flex flex-col">

                            {{-- Header --}}
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-semibold text-yellow-800 dark:text-yellow-300 text-lg">Mutu PPJB - Diajukan
                                    (Pending)</h4>
                                <span class="px-2 py-0.5 text-xs rounded bg-yellow-100 text-yellow-700">Pending</span>
                            </div>

                            {{-- List Items --}}
                            <ul class="list-decimal list-inside space-y-1 text-gray-700 dark:text-gray-300 text-sm">
                                @foreach ($mutuPending->items as $item)
                                    <li>{{ $item->nama_mutu }} - Rp {{ number_format($item->nominal_mutu, 0, ',', '.') }}
                                    </li>
                                @endforeach
                            </ul>

                            {{-- Info + Tombol Batalkan --}}
                            <div class="flex justify-between items-center">
                                <p class="text-sm text-gray-500 mt-2">
                                    Diajukan oleh <strong>{{ $mutuPending->pengaju->username ?? '-' }}</strong>
                                    pada {{ $mutuPending->tanggal_pengajuan->format('d M Y') }}
                                </p>
                                <div class="mt-4 flex justify-end">
                                    <form action="{{ route('settingPPJB.mutu.cancel', $mutuPending) }}" method="POST"
                                        class="delete-form">
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




    {{-- modal pengajuan baru mutu --}}
    <div id="modal-create" tabindex="-1" aria-hidden="true"
        class="hidden fixed inset-0 z-50 flex items-center justify-center w-full h-full bg-black/40">

        <div class="relative w-full max-w-xl p-4">
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700 flex flex-col max-h-[90vh]">

                <!-- Header -->
                <div class="flex items-center justify-between p-4 border-b dark:border-gray-600">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Ajukan Mutu PPJB Baru
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
                    <form id="mutuForm" action="{{ route('settingPPJB.mutu.pengajuanUpdate') }}" method="POST"
                        class="space-y-4">
                        @csrf

                        {{-- Hidden Perumahaan --}}
                        @php
                            $perumahaanId = auth()->user()->hasGlobalAccess()
                                ? session('current_perumahaan_id')
                                : Auth::user()->perumahaan_id;
                        @endphp
                        <input type="hidden" name="perumahaan_id" value="{{ $perumahaanId }}">

                        {{-- Dynamic Input List --}}
                        <div id="mutu-list" class="space-y-3">
                            <div class="flex items-center gap-2 mutu-item">
                                {{-- Nama Mutu --}}
                                <div class="flex-1 flex flex-col">
                                    <label class="text-sm text-gray-700 dark:text-gray-300 mb-1" for="nama_mutu">
                                        Nama Mutu
                                    </label>
                                    <input type="text" name="nama_mutu[]" placeholder="Masukan nama mutu"
                                        class="bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                   dark:bg-gray-600 dark:text-white dark:placeholder-gray-400
                   focus:ring-primary-600 focus:border-primary-600" />
                                </div>

                                {{-- Nominal Mutu --}}
                                <div class="flex flex-col w-40">
                                    <label class="text-sm text-gray-700 dark:text-gray-300 mb-1" for="nominal_mutu_display">
                                        Nominal Rp
                                    </label>
                                    <input type="text" name="nominal_mutu_display[]" placeholder="Nominal Rp"
                                        class="bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                   dark:bg-gray-600 dark:text-white dark:placeholder-gray-400
                   focus:ring-primary-600 focus:border-primary-600"
                                        data-hidden-input="nominal_mutu[]" />
                                    {{-- Hidden input untuk dikirim ke server --}}
                                    <input type="hidden" name="nominal_mutu[]" value="">
                                </div>

                                {{-- Tombol hapus --}}
                                <button type="button"
                                    class="remove-btn text-red-600 hover:text-red-800 text-xl font-bold mt-6">&times;</button>
                            </div>

                        </div>

                        {{-- Tombol Tambah --}}
                        <div>
                            <button type="button" id="addMutu"
                                class="px-3 py-1 text-sm rounded-md bg-green-600 text-white hover:bg-green-700">
                                + Tambah Mutu
                            </button>
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

    {{-- Script dinamis + format rupiah --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const list = document.getElementById("mutu-list");
            const addRow = document.getElementById("addMutu");

            // Fungsi format rupiah
            function formatRupiah(num) {
                if (!num) return '';
                return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            function setupRupiahInput(input) {
                const hiddenInputName = input.dataset.hiddenInput;
                const hiddenInput = input.closest('.mutu-item').querySelector(`input[name="${hiddenInputName}"]`);

                input.addEventListener('input', (e) => {
                    // ambil angka murni
                    const value = e.target.value.replace(/\D/g, '');
                    hiddenInput.value = value; // untuk dikirim ke server
                    e.target.value = value ? formatRupiah(value) : '';
                });

                // Inisialisasi kalau ada value
                if (hiddenInput.value) {
                    input.value = formatRupiah(hiddenInput.value);
                }
            }

            // Setup untuk input awal
            document.querySelectorAll('input[data-hidden-input]').forEach(el => setupRupiahInput(el));

            // Tambah row baru
            addRow.addEventListener("click", () => {
                const div = document.createElement("div");
                div.classList.add("flex", "items-center", "gap-2", "mutu-item");
                div.innerHTML = `
            <input type="text" name="nama_mutu[]" placeholder="Masukan nama mutu"
                class="flex-1 bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                       dark:bg-gray-600 dark:text-white dark:placeholder-gray-400
                       focus:ring-primary-600 focus:border-primary-600" />

            <input type="text" name="nominal_mutu_display[]" placeholder="Nominal Rp"
                class="w-40 bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                       dark:bg-gray-600 dark:text-white dark:placeholder-gray-400
                       focus:ring-primary-600 focus:border-primary-600"
                data-hidden-input="nominal_mutu[]" />

            <input type="hidden" name="nominal_mutu[]" value="">

            <button type="button" class="remove-btn text-red-600 hover:text-red-800 text-xl font-bold">&times;</button>
        `;
                list.appendChild(div);

                // setup rupiah input baru
                setupRupiahInput(div.querySelector('input[data-hidden-input]'));
            });

            // Hapus row
            list.addEventListener("click", (e) => {
                if (e.target.classList.contains("remove-btn")) {
                    e.target.closest(".mutu-item").remove();
                }
            });
        });
    </script>

    {{-- sweatalert 2 for batalkan pengajuan Mutu data --}}
    <script>
        document.addEventListener('click', function(e) {
            if (e.target.closest('.cancelPengajuan')) {
                const btn = e.target.closest('.cancelPengajuan');
                const form = btn.closest('.delete-form');

                Swal.fire({
                    title: 'Yakin membatalkan pengajuan Mutu ini?',
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
    </script>

    {{-- sweatalert 2 for nonaktifkan Mutu data --}}
    <script>
        document.addEventListener('click', function(e) {
            if (e.target.closest('.nonAktifkanMutu')) {
                const btn = e.target.closest('.nonAktifkanMutu');
                const form = btn.closest('.delete-form');

                Swal.fire({
                    title: 'Yakin menonaktifkan Mutu?',
                    text: 'Menonaktifkan Mutu akan berpengaruh pada proses PPJB dan semua data terkait batch ini. Pastikan Anda ingin melanjutkan.',
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
