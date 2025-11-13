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

        <!-- -->
        <div class="bg-white border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-5 mb-6">
            <!-- Header Info Pemesanan -->
            <div class="border-b pb-4">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
                    <!-- 📄 Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-green-600" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12h6m-6 4h6m2 4H7a2 2 0 01-2-2V6a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z" />
                    </svg>
                    Rincian Tagihan Pemesanan
                </h2>
                <p class="text-gray-500 text-sm mt-1">
                    Berikut adalah daftar cicilan dan status pembayaran untuk pemesanan ini.
                </p>
            </div>

            <!-- 🧾 Card List -->
            <div class="mt-6 grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @forelse ($rincianTagihan as $tagihan)
                    <div
                        class="border rounded-xl p-5 shadow-sm hover:shadow-md transition relative overflow-hidden
                @if ($tagihan->status_bayar === 'lunas') border-green-300 bg-green-50/50
                @elseif($tagihan->status_bayar === 'pending') border-yellow-300 bg-yellow-50/50
                @else border-red-300 bg-red-50/50 @endif">

                        <!-- Badge Status -->
                        <span
                            class="absolute top-3 right-3 text-xs font-semibold px-2 py-1 rounded-full flex items-center gap-1
                    @if ($tagihan->status_bayar === 'lunas') text-green-700 bg-green-100
                    @elseif($tagihan->status_bayar === 'pending') text-yellow-700 bg-yellow-100
                    @else text-red-700 bg-red-100 @endif">
                            @if ($tagihan->status_bayar === 'lunas')
                                <!-- Check Icon -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                Lunas
                            @elseif($tagihan->status_bayar === 'pending')
                                <!-- Clock Icon -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Pending
                            @else
                                <!-- Warning Icon -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v2m0 4h.01M12 3.75l8.5 14.75H3.5L12 3.75z" />
                                </svg>
                                Telat
                            @endif
                        </span>

                        <!-- Konten Card -->
                        <div class="space-y-2">
                            <h3 class="text-lg font-semibold text-gray-800">
                                Pembayaran ke-{{ $tagihan->pembayaran_ke }}
                            </h3>
                            <p class="text-sm text-gray-500">
                                Jatuh Tempo:
                                <span class="font-medium text-gray-700">
                                    {{ \Carbon\Carbon::parse($tagihan->tanggal_jatuh_tempo)->translatedFormat('d F Y') }}
                                </span>
                            </p>

                            <p class="text-sm text-gray-500">
                                Nominal:
                                <span class="font-semibold text-gray-800">
                                    Rp{{ number_format($tagihan->nominal, 0, ',', '.') }}
                                </span>
                            </p>

                            @if ($tagihan->status_bayar === 'lunas' && $tagihan->tanggal_pembayaran)
                                <p class="text-xs text-gray-500 italic">
                                    Dibayar pada:
                                    {{ \Carbon\Carbon::parse($tagihan->tanggal_pembayaran)->translatedFormat('d M Y') }}
                                </p>
                            @endif
                        </div>

                        <!-- Aksi -->
                        {{-- <div class="mt-4 flex justify-end">
                            @if ($tagihan->status_bayar === 'lunas')
                                <button disabled
                                    class="inline-flex items-center gap-1 px-3 py-1 text-sm font-medium text-gray-500 bg-gray-100 rounded cursor-not-allowed">
                                    <!-- Check -->
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Lunas
                                </button>
                            @else
                                <button
                                    class="inline-flex items-center gap-1 px-3 py-1 text-sm text-white bg-blue-600 rounded hover:bg-blue-700 transition">
                                    <!-- Dollar Icon -->
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 8c-2 0-3 1-3 3s1 3 3 3 3 1 3 3-1 3-3 3m0-14V4m0 16v-2" />
                                    </svg>
                                    Tandai Lunas
                                </button>
                            @endif
                        </div> --}}
                    </div>
                @empty
                    <div class="col-span-full text-center text-gray-500 italic py-10">
                        Belum ada rincian tagihan untuk pemesanan ini.
                    </div>
                @endforelse
            </div>

            <!-- Footer Total -->
            @php
                $total = $rincianTagihan->sum('nominal');
            @endphp
            <div class="flex justify-end mt-8">
                <div class="bg-gray-50 border border-gray-200 rounded-lg px-6 py-4 text-right shadow-sm">
                    <div class="text-gray-600 text-sm">Total Tagihan</div>
                    <div class="text-2xl font-bold text-gray-800">
                        Rp{{ number_format($total, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>




    </div>
@endsection
