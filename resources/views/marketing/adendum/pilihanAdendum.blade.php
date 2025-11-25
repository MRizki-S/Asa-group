@extends('layouts.app')

@section('pageActive', 'Adendum')

@section('content')
    <!-- ===== Main Content Start ===== -->
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: 'Adendum' }">
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

        <div class="space-y-6">
            <!-- Title -->
            <h2 class="text-xl font-semibold text-center">Pilih Jenis Adendum</h2>

            <!-- Grid Cards -->
            <div class="grid md:grid-cols-2 gap-6">

                <!-- 1. Adendum Cara Bayar -->
                <a href="{{ route('marketing.adendum.caraBayar') }}"
                    class="group flex items-start gap-4 p-5 bg-white border rounded-xl shadow transition
           hover:bg-gray-50 hover:border-gray-300 hover:shadow-lg hover:scale-[1.02]">

                    <!-- Icon -->
                    <svg class="w-10 h-10 text-blue-500 transition-transform group-hover:scale-110" fill="none"
                        stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 12.79A9 9 0 1 1 11.21 3h.02A9 9 0 0 1 21 12.79z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 9l-6 6m0-6l6 6" />
                    </svg>

                    <div>
                        <h3 class="text-lg font-semibold">Adendum Cara Bayar</h3>
                        <p class="text-sm text-gray-600">
                            Digunakan untuk mengubah skema atau jadwal pembayaran sesuai kesepakatan baru.
                        </p>
                    </div>
                </a>

                <!-- 2. Adendum Ganti Unit -->
                <a href="/marketing/adendum/ganti-unit"
                    class="group flex items-start gap-4 p-5 bg-white border rounded-xl shadow transition
           hover:bg-gray-50 hover:border-gray-300 hover:shadow-lg hover:scale-[1.02]">

                    <!-- Icon (lebih besar & warna biru) -->
                    <svg class="w-15 h-15 text-yellow-500 transition-transform group-hover:scale-110" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 4H4m0 0v4m0-4 5 5m7-5h4m0 0v4m0-4-5 5M8 20H4m0 0v-4m0 4 5-5m7 5h4m0 0v-4m0 4-5-5" />
                    </svg>

                    <div>
                        <h3 class="text-lg font-semibold">Adendum Ganti Unit</h3>
                        <p class="text-sm text-gray-600">
                            Digunakan untuk mengajukan perpindahan unit dengan catatan harga unit baru sama.
                            Jika harga berbeda dan mempengaruhi cara bayar, maka prosesnya masuk ke Adendum Gabungan.
                        </p>
                    </div>
                </a>


                <!-- 3. Adendum Perubahan Promo -->
                <a href="/marketing/adendum/perubahan-promo"
                    class="group flex items-start gap-4 p-5 bg-white border rounded-xl shadow transition
           hover:bg-gray-50 hover:border-gray-300 hover:shadow-lg hover:scale-[1.02]">

                    <!-- Icon -->
                    <svg class="w-10 h-10 text-gray-800 dark:text-white transition-transform group-hover:scale-110" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M10.83 5a3.001 3.001 0 0 0-5.66 0H4a1 1 0 1 0 0 2h1.17a3.001 3.001 0 0 0 5.66 0H20a1 1 0 1 0 0-2h-9.17ZM4 11h9.17a3.001 3.001 0 0 1 5.66 0H20a1 1 0 1 1 0 2h-1.17a3.001 3.001 0 0 1-5.66 0H4a1 1 0 1 1 0-2Zm1.17 6H4a1 1 0 1 0 0 2h1.17a3.001 3.001 0 0 0 5.66 0H20a1 1 0 1 0 0-2h-9.17a3.001 3.001 0 0 0-5.66 0Z" />
                    </svg>


                    <div>
                        <h3 class="text-lg font-semibold">Adendum Perubahan Promo</h3>
                        <p class="text-sm text-gray-600">
                            Digunakan untuk mengubah promo, diskon, atau benefit yang sudah disepakati sebelumnya.
                        </p>
                    </div>
                </a>

                <!-- 4. Adendum Gabungan -->
                <a href="/marketing/adendum/gabungan"
                    class="group flex items-start gap-4 p-5 bg-white border rounded-xl shadow transition
           hover:bg-gray-50 hover:border-gray-300 hover:shadow-lg hover:scale-[1.02]">

                    <!-- Icon -->
                    <svg class="w-10 h-10 text-orange-500 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" stroke-width="1.5"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                        <circle cx="12" cy="12" r="9" />
                    </svg>

                    <div>
                        <h3 class="text-lg font-semibold">Adendum Gabungan</h3>
                        <p class="text-sm text-gray-600">
                            Digunakan saat perubahan melibatkan lebih dari satu jenis adendum sekaligus.
                        </p>
                    </div>
                </a>

            </div>
        </div>

    </div>
    <!-- ===== Main Content End ===== -->
@endsection
