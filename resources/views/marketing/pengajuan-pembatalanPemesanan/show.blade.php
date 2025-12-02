@extends('layouts.app')

@section('pageActive', 'PengajuanPembatalan')

@section('content')

    {{-- <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css"> --}}
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb -->
        <div x-data="{ pageName: 'PengajuanPembatalan' }">
            @include('partials.breadcrumb')
        </div>

        <!-- Alert Error Validasi -->
        @if ($errors->any())
            <div class="flex p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400"
                role="alert">
                <svg class="shrink-0 inline w-4 h-4 me-3 mt-[2px]" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                    viewBox="0 0 20 20">
                    <path
                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                </svg>
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


        <!-- 📦 Info Pemesanan -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <!-- SVG icon "information" -->
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z" />
                </svg>
                Info Pemesanan
            </h2>

            @php
                $pemesanan = $pengajuanPembatalan->pemesananUnit;
                $caraBayar = strtoupper($pemesanan->cara_bayar ?? '-');
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Kolom Kiri -->
                <table class="w-full text-gray-800">
                    <tbody>
                        <tr>
                            <td class="font-medium text-gray-700 w-40">Nama User</td>
                            <td class="w-4 text-center">:</td>
                            <td>{{ $pemesanan->customer->username ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="font-medium text-gray-700">Nama Sales</td>
                            <td class="text-center">:</td>
                            <td>{{ $pemesanan->sales->username ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="font-medium text-gray-700">Unit</td>
                            <td class="text-center">:</td>
                            <td>
                                {{ $pemesanan->unit->nama_unit ?? '-' }}
                                ({{ $pemesanan->perumahaan->nama_perumahaan ?? '-' }})
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Kolom Kanan -->
                <table class="w-full text-gray-800">
                    <tbody>
                        <tr>
                            <td class="font-medium text-gray-700 w-40">No HP</td>
                            <td class="w-4 text-center">:</td>
                            <td>{{ $pemesanan->dataDiri->no_hp ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="font-medium text-gray-700">Cara Bayar</td>
                            <td class="text-center">:</td>
                            <td>{{ $caraBayar }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- pengajuan pembatalan detail --}}
        <div x-data="{ showImage: false, imageUrl: '' }"
            class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
            <div class="px-5 py-4 sm:px-6 sm:py-5">
                <!-- Header -->
                <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                        Pengajuan Pembatalan Pemesanan
                    </h3>
                </div>

                <!-- 📝 Alasan Pembatalan -->
                <div class="mb-5">
                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Alasan Pembatalan
                    </label>
                    <input type="text" readonly value="{{ $pengajuanPembatalan->alasan_pembatalan ?? '-' }}"
                        class="form-control w-full bg-gray-50 border border-gray-300 text-gray-900 text-base rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-700 dark:text-white dark:border-gray-600">
                </div>

                <!-- 📋 Alasan Detail Pembatalan -->
                <div class="mb-6">
                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Alasan Detail Pembatalan
                    </label>
                    <textarea readonly rows="3"
                        class="form-control w-full bg-gray-50 border border-gray-300 text-gray-900 text-base rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-700 dark:text-white dark:border-gray-600">{{ $pengajuanPembatalan->alasan_detail ?? '-' }}</textarea>
                </div>

                <!-- 🖼️ Bukti Pembatalan -->
                <div class="mt-6">
                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Bukti Pendukung Pembatalan
                    </label>

                    @if (!empty($pengajuanPembatalan->bukti_pembatalan))
                        <a href="{{ asset('storage/bukti_pembatalan/' . $pengajuanPembatalan->bukti_pembatalan) }}" download
                            class="bg-blue-500 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-600 transition text-sm">
                            Unduh: {{ $pengajuanPembatalan->bukti_pembatalan }}
                        </a>
                    @else
                        <p class="text-sm text-gray-500 mb-1">Tidak ada bukti yang diunggah.</p>
                    @endif

                    <!-- Catatan tambahan -->
                    <div
                        class="mt-3 flex items-center gap-2 bg-gray-50 border-l-4 border-gray-400 text-gray-700 p-3 rounded-md text-sm">
                        <!-- Icon info -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0 text-gray-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z" />
                        </svg>
                        <span>
                            Jika bukti pendukung tidak bisa diunduh, silakan hubungi tim IT untuk bantuan.
                        </span>
                    </div>
                </div>


            </div>
        </div>

        {{-- 🔹 hasil keputusan Project Manager --}}
        <div x-data="{ open: false }"
            class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6 overflow-hidden">
            <!-- Header Accordion -->
            <button @click="open = !open"
                class="w-full flex justify-between items-center px-5 py-4 sm:px-6 sm:py-5 text-left transition hover:bg-gray-50 dark:hover:bg-gray-800/50">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z" />
                    </svg>
                    Keputusan Project Manager
                </h3>


                <svg x-show="!open" xmlns="http://www.w3.org/2000/svg"
                    class="w-5 h-5 text-gray-500 transition-transform duration-300" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
                <svg x-show="open" xmlns="http://www.w3.org/2000/svg"
                    class="w-5 h-5 text-gray-500 transition-transform duration-300 rotate-180" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <!-- Isi Accordion -->
            <div x-show="open" x-collapse x-transition
                class="px-5 py-4 sm:px-6 sm:py-5 border-t border-gray-100 dark:border-gray-800">
                @php
                    $status = $pengajuanPembatalan->status_mgr_pemasaran ?? 'pending';
                    $statusStyle =
                        [
                            'acc' => 'bg-green-100 text-green-800 border-green-300',
                            'tolak' => 'bg-red-100 text-red-800 border-red-300',
                            'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                        ][$status] ?? 'bg-gray-100 text-gray-800 border-gray-300';

                    $statusIcon =
                        [
                            'acc' => 'check-circle',
                            'tolak' => 'x-circle',
                            'pending' => 'clock',
                        ][$status] ?? 'minus-circle';
                @endphp

                <!-- 🏷️ Status Card -->
                <div class="flex items-center gap-3 mb-5 border {{ $statusStyle }} rounded-lg p-3">
                    @if ($statusIcon === 'check-circle')
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-green-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m6 2a10 10 0 11-20 0 10 10 0 0120 0z" />
                        </svg>
                    @elseif ($statusIcon === 'x-circle')
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    @elseif ($statusIcon === 'clock')
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-yellow-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 8v4h4m-4-4v8m0 0a9 9 0 110-18 9 9 0 010 18z" />
                        </svg>
                    @endif

                    <span class="font-medium text-sm">
                        Status Keputusan:
                        <span class="font-semibold capitalize">{{ $status }}</span>
                    </span>
                </div>

                <!-- ✏️ Catatan Keputusan -->
                <div class="mb-2">
                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Catatan Keputusan
                    </label>
                    <textarea readonly rows="2"
                        class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-base rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-700 dark:text-white dark:border-gray-600">{{ $pengajuanPembatalan->catatan_mgr_pemasaran ?? '-' }}</textarea>
                </div>

                <!-- 📅 Waktu Keputusan -->
                @if ($pengajuanPembatalan->tanggal_respon_pemasaran ?? false)
                    <p class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Diperbarui pada:
                        {{ \Carbon\Carbon::parse($pengajuanPembatalan->tanggal_respon_pemasaran)->format('d M Y, H:i') }}
                    </p>
                @endif
            </div>
        </div>
        {{-- 🔹 Tombol Aksi Project Manager --}}
        <div x-data="{ openModal: false, actionType: '' }">
            {{-- Tombol aksi hanya untuk Project Manager dan status masih pending --}}
            @role('Project Manager')
                @if ($pengajuanPembatalan->status_mgr_pemasaran === 'pending')
                    <div class="flex justify-end gap-3 mt-6">
                        <button @click="actionType = 'tolak'; openModal = true" type="button"
                            class="px-4 py-2 text-sm font-medium text-gray-800 bg-gray-300 rounded-lg shadow-md
                        hover:bg-gray-400 hover:shadow-lg transition duration-200 ease-in-out">
                            Tolak
                        </button>

                        <button @click="actionType = 'acc'; openModal = true" type="button"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-md
                        hover:bg-blue-700 hover:shadow-lg transition duration-200 ease-in-out">
                            ACC / Approve
                        </button>
                    </div>
                @endif
            @endrole

            <!-- Modal -->
            <div x-show="openModal" x-transition
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
                style="display: none">
                <div @click.away="openModal = false" class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 relative">
                    <!-- Header -->
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z" />
                        </svg>
                        Keputusan Project Manager
                    </h2>

                    <form method="POST"
                        action="{{ route('marketing.pengajuan-pembatalan.keputusan-pemasaran', $pengajuanPembatalan->id) }}">
                        @csrf
                        @method('PATCH')

                        <input type="hidden" name="status_mgr_pemasaran" :value="actionType">

                        <!-- Select Status -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Status Keputusan
                            </label>
                            <select x-model="actionType" name="status_mgr_pemasaran" :disabled="true"
                                class="w-full border border-gray-300 rounded-lg p-2.5 text-gray-800 bg-gray-100 cursor-not-allowed focus:ring-0 focus:border-gray-300">
                                <option value="acc">ACC / Approve</option>
                                <option value="tolak">Tolak</option>
                            </select>
                        </div>

                        <!-- Catatan -->
                        <div class="mb-5">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Catatan Keputusan
                            </label>
                            <textarea name="catatan_mgr_pemasaran" rows="3" placeholder="Tuliskan catatan alasan keputusan..."
                                class="w-full border border-gray-300 rounded-lg p-2.5 text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required></textarea>
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="flex justify-end gap-3">
                            <button type="button" @click="openModal = false"
                                class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-200 rounded-lg hover:bg-gray-300 transition">
                                Batal
                            </button>

                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{--   Hasil Keputusan Manager Keuangan --}}
        @if ($pengajuanPembatalan->status_mgr_pemasaran !== 'pending')
            <div x-data="{ open: false }"
                class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6 overflow-hidden">
                <!-- Header Accordion -->
                <button @click="open = !open"
                    class="w-full flex justify-between items-center px-5 py-4 sm:px-6 sm:py-5 text-left transition hover:bg-gray-50 dark:hover:bg-gray-800/50">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-green-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z" />
                        </svg>
                        Keputusan Manager Keuangan
                    </h3>

                    <svg x-show="!open" xmlns="http://www.w3.org/2000/svg"
                        class="w-5 h-5 text-gray-500 transition-transform duration-300" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                    <svg x-show="open" xmlns="http://www.w3.org/2000/svg"
                        class="w-5 h-5 text-gray-500 transition-transform duration-300 rotate-180" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Isi Accordion -->
                <div x-show="open" x-collapse x-transition
                    class="px-5 py-4 sm:px-6 sm:py-5 border-t border-gray-100 dark:border-gray-800">
                    @php
                        $status = $pengajuanPembatalan->status_mgr_keuangan ?? 'pending';
                        $statusStyle =
                            [
                                'acc' => 'bg-green-100 text-green-800 border-green-300',
                                'tolak' => 'bg-red-100 text-red-800 border-red-300',
                                'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                            ][$status] ?? 'bg-gray-100 text-gray-800 border-gray-300';

                        $statusIcon =
                            [
                                'acc' => 'check-circle',
                                'tolak' => 'x-circle',
                                'pending' => 'clock',
                            ][$status] ?? 'minus-circle';
                    @endphp

                    <!-- 🏷️ Status Card -->
                    <div class="flex items-center gap-3 mb-5 border {{ $statusStyle }} rounded-lg p-3">
                        @if ($statusIcon === 'check-circle')
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-green-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12l2 2 4-4m6 2a10 10 0 11-20 0 10 10 0 0120 0z" />
                            </svg>
                        @elseif ($statusIcon === 'x-circle')
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        @elseif ($statusIcon === 'clock')
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-yellow-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 8v4h4m-4-4v8m0 0a9 9 0 110-18 9 9 0 010 18z" />
                            </svg>
                        @endif

                        <span class="font-medium text-sm">
                            Status Keputusan:
                            <span class="font-semibold capitalize">{{ $status }}</span>
                        </span>
                    </div>

                    <!-- ✏️ Catatan Keputusan -->
                    <div class="mb-2">
                        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Catatan Keputusan
                        </label>
                        <textarea readonly rows="2"
                            class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-base rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-700 dark:text-white dark:border-gray-600">{{ $pengajuanPembatalan->catatan_mgr_keuangan ?? '-' }}</textarea>
                    </div>

                    <!-- 📅 Waktu Keputusan -->
                    @if ($pengajuanPembatalan->tanggal_respon_keuangan ?? false)
                        <p class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Diperbarui pada:
                            {{ \Carbon\Carbon::parse($pengajuanPembatalan->tanggal_respon_keuangan)->format('d M Y, H:i') }}
                        </p>
                    @endif
                </div>
            </div>


            {{-- 🔹 Tombol Aksi Manager Keuangan --}}
            <div x-data="{ openModal: false, actionType: '' }">
                @role('Manager Keuangan')
                    @if ($pengajuanPembatalan->status_mgr_pemasaran !== 'pending' && $pengajuanPembatalan->status_mgr_keuangan === 'pending')
                        <div class="flex justify-end gap-3 mt-6">
                            <button @click="actionType = 'tolak'; openModal = true" type="button"
                                class="px-4 py-2 text-sm font-medium text-gray-800 bg-gray-300 rounded-lg shadow-md
                    hover:bg-gray-400 hover:shadow-lg transition duration-200 ease-in-out">
                                Tolak
                            </button>

                            <button @click="actionType = 'acc'; openModal = true" type="button"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-md
                        hover:bg-blue-700 hover:shadow-lg transition duration-200 ease-in-out">
                                ACC / Approve
                            </button>
                        </div>
                    @endif
                @endrole

                <!-- Modal -->
                <div x-show="openModal" x-transition
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
                    style="display: none">
                    <div @click.away="openModal = false"
                        class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 relative">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-green-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z" />
                            </svg>
                            Keputusan Manager Keuangan
                        </h2>

                        <form method="POST"
                            action="{{ route('marketing.pengajuan-pembatalan.keputusan-keuangan', $pengajuanPembatalan->id) }}">
                            @csrf
                            @method('PATCH')

                            <input type="hidden" name="status_mgr_keuangan" :value="actionType">

                            <!-- Status -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status Keputusan</label>
                                <select x-model="actionType" name="status_mgr_keuangan" :disabled="true"
                                    class="w-full border border-gray-300 rounded-lg p-2.5 text-gray-800 bg-gray-100 cursor-not-allowed focus:ring-0 focus:border-gray-300">
                                    <option value="acc">ACC / Approve</option>
                                    <option value="tolak">Tolak</option>
                                </select>
                            </div>

                            <!-- Catatan -->
                            <div class="mb-5">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Keputusan</label>
                                <textarea name="catatan_mgr_keuangan" rows="3" placeholder="Tuliskan catatan alasan keputusan..."
                                    class="w-full border border-gray-300 rounded-lg p-2.5 text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    required></textarea>
                            </div>

                            <!-- 🔘 Checkbox Pengecualian Potongan -->
                            <div class="mb-5">
                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="pengecualian_potongan" value="1"
                                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="font-medium">Pengecualian Potongan</span>
                                </label>
                                <p class="text-xs text-gray-500 mt-1">
                                    Centang opsi ini jika Anda ingin mengecualikan potongan keuangan dari pemesanan ini.
                                </p>
                            </div>

                            <!-- Tombol Aksi -->
                            <div class="flex justify-end gap-3">
                                <button type="button" @click="openModal = false"
                                    class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-200 rounded-lg hover:bg-gray-300 transition">
                                    Batal
                                </button>

                                <button type="submit"
                                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                                    Simpan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif


    </div>
@endsection
