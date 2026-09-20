@extends('layouts.app')

@section('pageActive', 'orderBarangKawasan')

@section('content')
<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">
    <div x-data="{ pageName: 'Order Barang Kawasan' }">
        @include('partials.breadcrumb')
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-xl bg-green-50 p-4 text-sm text-green-800 border border-green-200 dark:bg-green-900/20 dark:border-green-800 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-xl bg-red-50 p-4 text-sm text-red-800 border border-red-200 dark:bg-red-900/20 dark:border-red-800 dark:text-red-300">
            {{ session('error') }}
        </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-gray-100 dark:border-gray-800 pb-4">
            <div>
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">Daftar Order Barang Kawasan</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Riwayat dan daftar pengajuan order barang kawasan oleh pengawas proyek</p>
            </div>
            <div>
                <a href="{{ route('produksi.pembangunanKawasan.orderCreate') }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Buat Order Barang Baru
                </a>
            </div>
        </div>

        {{-- Filter Status --}}
        <div class="mb-4 flex flex-wrap items-center gap-2">
            @php
                $currStatus = request('status', '');
            @endphp
            <a href="{{ route('produksi.pembangunanKawasan.orderIndex') }}"
                class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $currStatus === '' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300' }}">
                Semua Status
            </a>
            <a href="{{ route('produksi.pembangunanKawasan.orderIndex', ['status' => 'diproses']) }}"
                class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $currStatus === 'diproses' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300' }}">
                Menunggu
            </a>
            <a href="{{ route('produksi.pembangunanKawasan.orderIndex', ['status' => 'menunggu_spv']) }}"
                class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $currStatus === 'menunggu_spv' ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300' }}">
                Menunggu SPV
            </a>
            <a href="{{ route('produksi.pembangunanKawasan.orderIndex', ['status' => 'selesai']) }}"
                class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $currStatus === 'selesai' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300' }}">
                Selesai (Disetujui)
            </a>
            <a href="{{ route('produksi.pembangunanKawasan.orderIndex', ['status' => 'ditolak']) }}"
                class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $currStatus === 'ditolak' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300' }}">
                Ditolak
            </a>
        </div>

        {{-- Tabel Daftar Order --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                <thead class="bg-gray-50 text-xs uppercase text-gray-600 dark:bg-gray-800 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3">No. Order / NBK</th>
                        <th class="px-4 py-3">Tanggal Diajukan</th>
                        <th class="px-4 py-3">Perumahan / Kawasan</th>
                        <th class="px-4 py-3">Periode</th>
                        <th class="px-4 py-3 text-center">Jumlah Item</th>
                        <th class="px-4 py-3 text-center">Jenis</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($orders as $order)
                        @php
                            $pk = $order->kawasan;
                            $perumahan = $pk?->perumahan?->nama_perumahaan ?? '-';
                            $kawasanNama = $pk?->nama ?? '-';
                            $statusMap = [
                                'diproses' => ['label' => 'Menunggu', 'class' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300'],
                                'menunggu_spv' => ['label' => 'Menunggu SPV', 'class' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300'],
                                'selesai' => ['label' => 'Selesai', 'class' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300'],
                                'ditolak' => ['label' => 'Ditolak', 'class' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300'],
                            ];
                            $statusInfo = $statusMap[$order->status_order] ?? ['label' => $order->status_order, 'class' => 'bg-gray-100 text-gray-800'];
                            $periodeObj = $order->periode;
                            $periodeLabel = $periodeObj ? (($periodeObj->tanggal_mulai ? \Carbon\Carbon::parse($periodeObj->tanggal_mulai)->format('d/m/Y') : '-') . ' s/d ' . ($periodeObj->tanggal_selesai ? \Carbon\Carbon::parse($periodeObj->tanggal_selesai)->format('d/m/Y') : 'Sekarang')) : '-';
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                            <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">
                                <div>{{ $order->nomor_order }}</div>
                                @if($order->nomor_nbk)
                                    <div class="text-[11px] text-green-600 dark:text-green-400 font-mono font-normal">NBK: {{ $order->nomor_nbk }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs">
                                {{ $order->tanggal_diajukan ? $order->tanggal_diajukan->format('d/m/Y H:i') : '-' }} WIB
                            </td>
                            <td class="px-4 py-3">
                                @if ($order->pembangunan_kawasan_id)
                                    <a href="{{ route('produksi.pembangunanKawasan.show', $order->pembangunan_kawasan_id) }}" class="group block">
                                        <div class="font-medium text-gray-900 group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400 group-hover:underline">{{ $perumahan }}</div>
                                        <div class="text-xs text-gray-500">Kawasan: {{ $kawasanNama }}</div>
                                    </a>
                                @else
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $perumahan }}</div>
                                    <div class="text-xs text-gray-500">Kawasan: {{ $kawasanNama }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs">
                                @if ($order->pembangunan_kawasan_id)
                                    <a href="{{ route('produksi.pembangunanKawasan.show', $order->pembangunan_kawasan_id) }}?tab=barang"
                                        class="inline-flex items-center gap-1 font-semibold text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:underline"
                                        title="Buka detail kawasan pada Tab Barang">
                                        <span>{{ $periodeLabel }}</span>
                                        <svg class="w-3 h-3 opacity-60 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                @else
                                    <span>{{ $periodeLabel }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center font-bold">
                                {{ $order->details->count() }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $order->jenis_order === 'direct' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ $order->jenis_order }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold whitespace-nowrap {{ $statusInfo['class'] }}">
                                    {{ $statusInfo['label'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    @if ($order->status_order === 'selesai')
                                        <a href="{{ route('gudang.permintaanBarang.pembangunanKawasan.notaPdf', $order->id) }}"
                                            target="_blank"
                                            class="inline-flex items-center gap-1 rounded-lg bg-emerald-50 px-2.5 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-300">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            NBK
                                        </a>
                                    @endif
                                    @if ($order->status_order === 'diproses')
                                        <form method="POST" action="{{ route('produksi.pembangunanKawasan.orderDestroy', $order->id) }}"
                                            onsubmit="return confirm('Apakah Anda yakin ingin membatalkan order ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center gap-1 rounded-lg bg-red-50 px-2.5 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-100 dark:bg-red-950/40 dark:text-red-300">
                                                Batal
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500">
                                Belum ada data order barang kawasan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection
