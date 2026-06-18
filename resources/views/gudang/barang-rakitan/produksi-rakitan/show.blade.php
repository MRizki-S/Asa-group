@extends('layouts.app')

@section('pageActive', 'ProduksiRakitan')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">
        @include('partials.breadcrumb')

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-6 flex items-center justify-between">
                <h3 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                    Detail Produksi Rakitan: {{ $item->nomor_rakitan }}
                </h3>
                <a href="{{ route('gudang.produksiRakitan.index') }}"
                    class="text-sm font-medium text-blue-600 hover:underline">
                    &larr; Kembali ke Daftar
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Nomor Rakitan</label>
                        <p class="text-base text-gray-800 dark:text-white">{{ $item->nomor_rakitan }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Tanggal</label>
                        <p class="text-base text-gray-800 dark:text-white">{{ $item->tanggal_rakitan->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Gudang</label>
                        <p class="text-base text-gray-800 dark:text-white">{{ $item->ubs->nama_ubs ?? 'HUB' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Status</label>
                        <p class="mt-1">
                            @if($item->status === 'posted')
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                    Posted
                                </span>
                            @elseif($item->status === 'cancelled')
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                                    Cancelled
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                    {{ ucfirst($item->status) }}
                                </span>
                            @endif
                        </p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Barang Hasil</label>
                        <p class="text-base text-gray-800 dark:text-white">{{ $item->barangHasil->nama_barang }}
                            ({{ $item->barangHasil->kode_barang }})</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Qty Hasil</label>
                        <p class="text-base text-gray-800 dark:text-white">
                            {{ rtrim(rtrim(number_format($item->qty_hasil, 3, ',', '.'), '0'), ',') }}
                            {{ $item->satuanHasil->nama }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Total Biaya Production</label>
                        <p class="text-base text-gray-800 dark:text-white">Rp
                            {{ rtrim(rtrim(number_format($item->total_biaya, 2, ',', '.'), '0'), ',') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Harga Satuan</label>
                        <p class="text-base text-gray-800 dark:text-white">Rp
                            {{ rtrim(rtrim(number_format($item->harga_satuan, 2, ',', '.'), '0'), ',') }}</p>
                    </div>
                </div>
            </div>

            <div class="mt-8">
                <h4 class="text-lg font-medium text-gray-800 dark:text-white/90 mb-4">Rincian Komponen Bahan</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Barang Bahan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Qty</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Harga Satuan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($item->details as $detail)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">
                                        {{ $detail->barangBahan->nama_barang }} ({{ $detail->barangBahan->kode_barang }})
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">
                                        {{ rtrim(rtrim(number_format($detail->qty_pakai, 3, ',', '.'), '0'), ',') }}
                                        {{ $detail->satuan->nama }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">
                                        Rp
                                        {{ rtrim(rtrim(number_format($detail->qty_pakai > 0 ? $detail->harga_total / $detail->qty_pakai : 0, 2, ',', '.'), '0'), ',') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">
                                        Rp {{ rtrim(rtrim(number_format($detail->harga_total, 2, ',', '.'), '0'), ',') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if($item->keterangan)
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-500">Keterangan</label>
                    <p class="text-base text-gray-800 dark:text-white">{{ $item->keterangan }}</p>
                </div>
            @endif
        </div>
    </div>
@endsection