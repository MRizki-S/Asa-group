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

                        <!-- Aksi Checklist / Tandai Lunas -->
                        <div class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                            @if ($tagihan->status_bayar === 'lunas')
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-green-700 bg-green-100/80 px-2.5 py-1 rounded-md">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    Terverifikasi Lunas
                                </span>

                                <form id="form-batal-{{ $tagihan->id }}" action="{{ route('marketing.rincianTagihan.updateStatus', $tagihan->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status_bayar" value="pending">
                                    <button type="button" onclick="konfirmasiBatalLunas('{{ $tagihan->id }}', '{{ $tagihan->pembayaran_ke }}')"
                                        class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-red-600 transition"
                                        title="Batalkan status lunas jika salah checklist">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        Batal Lunas
                                    </button>
                                </form>
                            @else
                                <form id="form-lunas-{{ $tagihan->id }}" action="{{ route('marketing.rincianTagihan.updateStatus', $tagihan->id) }}" method="POST" class="w-full flex justify-end">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status_bayar" value="lunas">
                                    <input type="hidden" name="tanggal_pembayaran" id="form-lunas-tgl-{{ $tagihan->id }}" value="{{ date('Y-m-d') }}">
                                    <button type="button" onclick="konfirmasiLunas('{{ $tagihan->id }}', '{{ $tagihan->pembayaran_ke }}', 'Rp{{ number_format($tagihan->nominal, 0, ',', '.') }}')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 active:scale-95 transition shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Checklist Sudah Bayar
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center text-gray-500 italic py-10">
                        Belum ada rincian tagihan untuk pemesanan ini.
                    </div>
                @endforelse
            </div>

            <!-- Footer Ringkasan Pembayaran -->
            @php
                $totalTagihan = $rincianTagihan->sum('nominal');
                $totalLunas = $rincianTagihan->where('status_bayar', 'lunas')->sum('nominal');
                $totalSisa = max(0, $totalTagihan - $totalLunas);
            @endphp
            <div class="mt-8 grid sm:grid-cols-3 gap-4">
                <div class="bg-blue-50/70 border border-blue-200 rounded-xl p-4 shadow-sm">
                    <div class="text-blue-700 text-xs font-semibold uppercase tracking-wider">Total Tagihan Cicilan</div>
                    <div class="text-xl font-bold text-blue-900 mt-1">
                        Rp{{ number_format($totalTagihan, 0, ',', '.') }}
                    </div>
                </div>
                <div class="bg-green-50/70 border border-green-200 rounded-xl p-4 shadow-sm">
                    <div class="text-green-700 text-xs font-semibold uppercase tracking-wider">Sudah Dibayar (Lunas)</div>
                    <div class="text-xl font-bold text-green-900 mt-1">
                        Rp{{ number_format($totalLunas, 0, ',', '.') }}
                    </div>
                </div>
                <div class="bg-amber-50/70 border border-amber-200 rounded-xl p-4 shadow-sm">
                    <div class="text-amber-700 text-xs font-semibold uppercase tracking-wider">Sisa Tagihan Belum Lunas</div>
                    <div class="text-xl font-bold text-amber-900 mt-1">
                        Rp{{ number_format($totalSisa, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function konfirmasiLunas(id, pembayaranKe, nominal) {
        Swal.fire({
            title: 'Konfirmasi Pembayaran Lunas',
            html: `
                <div class="text-sm text-gray-600 text-left mt-2">
                    <p class="mb-3">Tandai cicilan <b>ke-${pembayaranKe}</b> sebesar <span class="text-green-600 font-bold">${nominal}</span> sebagai <b>LUNAS</b>?</p>
                    <label class="block font-medium text-gray-700 mb-1">Tanggal Pembayaran:</label>
                    <input type="date" id="swal-tgl-bayar" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 border rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#16a34a',
            cancelButtonColor: '#9ca3af',
            confirmButtonText: 'Ya, Checklist Lunas',
            cancelButtonText: 'Batal',
            preConfirm: () => {
                const tgl = document.getElementById('swal-tgl-bayar').value;
                if (!tgl) {
                    Swal.showValidationMessage('Tanggal pembayaran tidak boleh kosong!');
                }
                return tgl;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('form-lunas-tgl-' + id).value = result.value;
                document.getElementById('form-lunas-' + id).submit();
            }
        });
    }

    function konfirmasiBatalLunas(id, pembayaranKe) {
        Swal.fire({
            title: 'Batalkan Status Lunas?',
            text: `Status cicilan ke-${pembayaranKe} akan dikembalikan menjadi Pending.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#9ca3af',
            confirmButtonText: 'Ya, Kembalikan ke Pending',
            cancelButtonText: 'Tutup'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('form-batal-' + id).submit();
            }
        });
    }
</script>
@endpush
