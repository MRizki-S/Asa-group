@extends('layouts.app')

@section('pageActive', 'ManagePemesanan')

@section('content')
    <div class="max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- ✅ Breadcrumb -->
        <div x-data="{ pageName: 'ManagePemesanan' }">
            @include('partials.breadcrumb')
        </div>

        <!-- ⚠️ Alert Error Validasi -->
        @if ($errors->any())
            <div class="flex p-4 mb-4 text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
                <svg class="shrink-0 inline w-4 h-4 me-3 mt-[2px]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                    fill="currentColor" viewBox="0 0 20 20">
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

        <!-- 🟡 Info -->
        <div
            class="bg-yellow-50 border border-yellow-300 text-yellow-800 rounded-lg p-4 mb-6 shadow-sm flex items-start gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-yellow-600 mt-[2px]" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01M4.93 4.93a10 10 0 1 1 14.14 14.14A10 10 0 0 1 4.93 4.93z" />
            </svg>
            <div>
                <p class="font-medium">Pemesanan Unit KPR ini belum memiliki bank.</p>
                <p class="text-sm text-yellow-700">Silakan pilih bank terlebih dahulu untuk melanjutkan ke kelengkapan
                    berkas KPR.</p>
            </div>
        </div>

        <!-- 📦 Info Pemesanan -->
        <div class="bg-white border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-5 mb-6">
            <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <!-- SVG icon "information" -->
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z" />
                </svg>
                Info Pemesanan
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-2">
                <p><span class="font-medium text-gray-700">Nama User :</span>
                    <span class="text-gray-800">{{ $pemesanan->customer->username ?? '-' }}</span>
                </p>
                <p><span class="font-medium text-gray-700">Nama Sales :</span>
                    <span class="text-gray-800">{{ $pemesanan->sales->username ?? '-' }}</span>
                </p>
                <p><span class="font-medium text-gray-700">Unit :</span>
                    <span class="text-gray-800">
                        {{ $pemesanan->unit->nama_unit ?? '-' }}
                        ({{ $pemesanan->perumahaan->nama_perumahaan ?? '-' }})
                    </span>
                </p>
                <p><span class="font-medium text-gray-700">No HP User:</span>
                    <span class="text-gray-800">{{ $pemesanan->dataDiri->no_hp ?? '-' }}</span>
                </p>
                <p><span class="font-medium text-gray-700">Cara Bayar :</span>
                    <span class="text-gray-800">{{ strtoupper($pemesanan->cara_bayar) ?? '-' }}</span>
                </p>
            </div>
        </div>

        <!-- 🏦 Form Pilih Bank -->
        <form method="POST" action="{{ route('marketing.managePemesanan.kelengkapanBerkasKpr.setBank', $pemesanan->id) }}">
            @csrf
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 max-w-md">
                <label for="bank_id" class="block text-sm font-medium text-gray-700 mb-2">Pilih Bank</label>
                <select name="bank_id" id="bank_id" required
                    class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- Pilih Bank --</option>
                    @foreach ($bankList as $bank)
                        <option value="{{ $bank->id }}">
                            {{ $bank->kode_bank }} - {{ $bank->nama_bank }}
                        </option>
                    @endforeach
                </select>

                <div class="flex justify-end mt-5">
                    <button type="submit"
                        class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-sky-500 text-white rounded-lg shadow hover:scale-[1.02] transition">
                        Simpan & Lanjutkan
                    </button>
                </div>
            </div>
        </form>

    </div>
@endsection
