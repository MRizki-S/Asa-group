@extends('layouts.app')

@section('pageActive', 'unitLayout')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb -->
        <div x-data="{ pageName: 'unitLayout' }">
            @include('partials.breadcrumb')
        </div>

        <!-- Card Detail Unit -->
        <div class="rounded-2xl border border-gray-200 shadow-sm bg-white/90 dark:border-gray-700 dark:bg-gray-900 mb-8">
            <div class="px-6 py-6 space-y-8">

                <!-- Master Data -->
                <fieldset class="space-y-1">
                    <legend class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        Master Data
                        <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
                            Informasi utama perumahaan & tahap
                        </span>
                    </legend>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Perumahaan -->
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Perumahaan</label>
                            <input type="text" value="{{ $perumahaan->nama_perumahaan }}" readonly
                                class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-white" />
                        </div>

                        <!-- Tahap -->
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Tahap</label>
                            <input type="text" value="{{ $unit->tahap->nama_tahap ?? '-' }}" readonly
                                class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-white" />
                        </div>
                    </div>
                </fieldset>

                <!-- Blok & Tipe -->
                <fieldset class="space-y-1">
                    <legend class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        Tahap – Blok & Tipe
                        <span class="text-sm font-normal text-gray-500 dark:text-gray-400">Detail blok dan tipe unit</span>
                    </legend>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Blok</label>
                            <input type="text" value="{{ $unit->blok->nama_blok ?? '-' }}" readonly
                                class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-white" />
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Tipe Unit</label>
                            <input type="text" value="{{ $unit->type->nama_type ?? '-' }}" readonly
                                class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-white" />
                        </div>

                        <div class="md:col-span-2">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Nama Unit</label>
                            <input type="text" value="{{ $unit->nama_unit }}" readonly
                                class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-white" />
                        </div>
                    </div>
                </fieldset>

                <!-- Kualifikasi Dasar -->
                <fieldset class="space-y-1">
                    <legend class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        Kualifikasi Dasar
                    </legend>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Kualifikasi Dasar</label>
                            <input type="text"
                                value="{{ ucfirst(str_replace('_', ' ', $unit->kualifikasi_dasar ?? '-')) }}" readonly
                                class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-white" />
                        </div>

                        @if ($unit->kualifikasi_dasar === 'kelebihan_tanah')
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Luas Kelebihan</label>
                                <input type="text" value="{{ $unit->luas_kelebihan ?? '-' }}" readonly
                                    class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-white" />
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Nominal
                                    Kelebihan</label>
                                <input type="text"
                                    value="Rp {{ number_format($unit->nominal_kelebihan ?? 0, 0, ',', '.') }}" readonly
                                    class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-white" />
                            </div>
                        @endif
                    </div>
                </fieldset>

                <!-- Kualifikasi Posisi -->
                <fieldset class="space-y-1">
                    <legend class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        Kualifikasi Posisi
                    </legend>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Kualifikasi Posisi</label>
                            <input type="text" value="{{ $unit->tahapKualifikasi->kualifikasiBlok->nama_kualifikasi_blok ?? '-' }} - Rp{{ number_format($unit->tahapKualifikasi->nominal_tambahan ?? 0, 0, ',', '.') }}"
                                readonly
                                class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:text-white" />
                        </div>
                    </div>
                </fieldset>

                <!-- Status Unit -->
                <fieldset class="space-y-4">
                    <legend class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        Status Unit
                        <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
                            Menampilkan status ketersediaan unit
                        </span>
                    </legend>

                    @php
                        $status = strtolower($unit->status_unit ?? 'ready');

                        // Warna berdasarkan status
                        $statusColor = match ($status) {
                            'sold' => 'bg-gray-500 text-white',
                            'booked' => 'bg-yellow-400 text-gray-900',
                            default => 'bg-green-500 text-white', // ready
                        };

                        // Label cantik
                        $statusLabel = match ($status) {
                            'sold' => 'Sold',
                            'booked' => 'Booked',
                            default => 'Ready   ', // ready
                        };
                    @endphp

                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">
                            Status Unit
                        </label>
                        <span class="inline-block px-4 py-2 text-sm font-semibold rounded-lg {{ $statusColor }}">
                            {{ $statusLabel }}
                        </span>
                    </div>
                </fieldset>


                <!-- Informasi Harga Rumah -->
                <fieldset class="space-y-4">
                    <legend class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        Informasi Harga
                        <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
                            Menampilkan harga rumah berdasarkan hasil perhitungan sistem
                        </span>
                    </legend>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Harga Final -->
                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">
                                Harga Final (Rp)
                            </label>
                            <input type="text" readonly
                                value="Rp {{ number_format($unit->harga_final ?? 0, 0, ',', '.') }}"
                                class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5
                    dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                        </div>

                        <!-- Harga Jual -->
                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">
                                Harga Jual (Rp)
                            </label>
                            <input type="text" readonly
                                value="{{ $unit->harga_jual ? 'Rp ' . number_format($unit->harga_jual, 0, ',', '.') : '-' }}"
                                class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5
                    dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                        </div>
                    </div>
                </fieldset>


            </div>

            <!-- Tombol Kembali -->
            <div class="flex justify-end px-6 pb-6">
                <a href="{{ route('unit.index', $perumahaan->slug) }}"
                    class="px-10 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-300 dark:text-white dark:bg-gray-700 dark:hover:bg-gray-600 dark:focus:ring-gray-600">
                    Kembali
                </a>
            </div>
        </div>
    </div>
@endsection
