@extends('layouts.app')

@section('pageActive', $pageActive)

@section('content')
<!-- ===== Main Content Start ===== -->
<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

    <!-- Breadcrumb Start -->
    <div x-data="{ pageName: '{{ $pageActive }}' }">
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

    <div class="space-y-5 sm:space-y-6">
        <div
            class="rounded-2xl border border-gray-200 px-5 py-4 sm:px-6 sm:py-5 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                    {{ $pageTitle }}
                </h3>

                <a href="{{ $isAbm ? route('gudang.pengajuanUpahHarianTukang.create') : route('gudang.pengajuanUpahHarianTukang.createMangoon') }}"
                    class="inline-block px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                    @if($isAbm)
                    + Buat Pengajuan ABM
                    @else
                    + Buat Pengajuan Mangoon
                    @endif
                </a>
            </div>

            <table id="table-pengajuanUpah">
                <thead>
                    <tr>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                            Nomor Pengajuan
                        </th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                            Periode
                        </th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                            Jumlah Tukang
                        </th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                            Total Upah
                        </th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                            Status
                        </th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                            Dibuat Oleh
                        </th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                            Terakhir Diubah
                        </th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pengajuans as $item)
                    <tr>
                        {{-- Nomor Pengajuan --}}
                        <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            {{ $item->nomor_upah_harian }}
                        </td>

                        {{-- Periode --}}
                        <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            @php
                                $mulai = $item->tanggal_mulai;
                                $selesai = $item->tanggal_selesai;
                                if ($mulai && $selesai) {
                                    if ($mulai->format('Y-m') === $selesai->format('Y-m')) {
                                        $periode = $mulai->translatedFormat('d') . ' - ' . $selesai->translatedFormat('d F Y');
                                    } elseif ($mulai->format('Y') === $selesai->format('Y')) {
                                        $periode = $mulai->translatedFormat('d F') . ' - ' . $selesai->translatedFormat('d F Y');
                                    } else {
                                        $periode = $mulai->translatedFormat('d F Y') . ' - ' . $selesai->translatedFormat('d F Y');
                                    }
                                } else {
                                    $periode = '-';
                                }
                            @endphp
                            {{ $periode }}
                        </td>

                        {{-- Jumlah Tukang --}}
                        <td class="font-medium text-center text-gray-900 whitespace-nowrap dark:text-white">
                            {{ $item->details->pluck('tukang_id')->unique()->count() }} Orang
                        </td>

                        {{-- Total Upah --}}
                        <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            Rp {{ number_format($item->details->sum('nominal_harian_final'), 0, ',', '.') }}
                        </td>

                        {{-- Status --}}
                        <td class="p-2 whitespace-nowrap align-middle text-center">
                            @if($item->status === 'disetujui')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                Disetujui
                            </span>
                            @elseif($item->status === 'diajukan')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                Diajukan
                            </span>
                            @elseif($item->status === 'ditolak')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                                Ditolak
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                Draft
                            </span>
                            @endif
                        </td>

                        {{-- Dibuat Oleh --}}
                        <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            {{ $item->createdBy->username ?? $item->createdBy->name ?? '-' }}
                        </td>

                        {{-- Terakhir Diubah --}}
                        <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            {{ $item->updated_at ? $item->updated_at->translatedFormat('d F Y H:i') : '-' }}
                        </td>

                        {{-- Aksi --}}
                        <td class="px-6 py-4">
                            <div class="flex justify-center gap-2">
                                @if($item->status === 'draft')
                                <a href="{{ $isAbm
                                        ? route('gudang.pengajuanUpahHarianTukang.edit', $item->id)
                                        : route('gudang.pengajuanUpahHarianTukang.editMangoon', $item->id) }}"
                                    class="inline-flex items-center gap-1 text-xs font-medium text-orange-700 bg-orange-100 hover:bg-orange-200 dark:bg-orange-800 dark:text-orange-100 dark:hover:bg-orange-700 px-2.5 py-1.5 rounded-md transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-1 active:scale-95">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                                    </svg>
                                    Edit
                                </a>
                                <form id="deleteForm-{{ $item->id }}"
                                      action="{{ $isAbm
                                        ? route('gudang.pengajuanUpahHarianTukang.destroy', $item->id)
                                        : route('gudang.pengajuanUpahHarianTukang.destroyMangoon', $item->id) }}"
                                      method="POST"
                                      class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        onclick="confirmDeleteDraft('{{ $item->id }}', '{{ $item->nomor_upah_harian }}')"
                                        class="inline-flex items-center gap-1 text-xs font-medium text-red-700 bg-red-100 hover:bg-red-200 dark:bg-red-800 dark:text-red-100 dark:hover:bg-red-700 px-2.5 py-1.5 rounded-md transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-1 active:scale-95">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Hapus
                                    </button>
                                </form>
                                @else
                                <a href="{{ $isAbm
                                        ? route('gudang.pengajuanUpahHarianTukang.detail', $item->id)
                                        : route('gudang.pengajuanUpahHarianTukang.detailMangoon', $item->id) }}"
                                    class="inline-flex items-center gap-1 text-xs font-medium text-blue-700 bg-blue-100 hover:bg-blue-200 dark:bg-blue-800 dark:text-blue-100 dark:hover:bg-blue-700 px-2.5 py-1.5 rounded-md transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1 active:scale-95">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.573-3.007-9.964-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    Detail
                                </a>
                                @endif
                            </div>
                        </td>

                    </tr>
                     @empty
                     @endforelse
                </tbody>
            </table>

        </div>
    </div>

</div>
<!-- ===== Main Content End ===== -->

<script>
    if (document.getElementById("table-pengajuanUpah") && typeof simpleDatatables.DataTable !== 'undefined') {
        const dataTable = new simpleDatatables.DataTable("#table-pengajuanUpah", {
            searchable: true,
            sortable: true,
            perPageSelect: [10, 25, 50, 100]
        });
    }

    function confirmDeleteDraft(id, nomor) {
        Swal.fire({
            title: 'Hapus Draft Pengajuan?',
            html: `Anda akan menghapus draft pengajuan <br><strong>${nomor}</strong>.<br><span class="text-sm text-gray-500">Tindakan ini tidak dapat dibatalkan.</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('deleteForm-' + id).submit();
            }
        });
    }
</script>
@endsection
