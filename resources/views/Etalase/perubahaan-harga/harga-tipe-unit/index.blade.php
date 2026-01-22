@extends('layouts.app')

@section('pageActive', 'PerubahanHargaTipeUnit')

@section('content')
    <!-- ===== Main Content Start ===== -->
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-data="typeSearch()">

        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: 'Tipe Unit' }">
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

        <div
            class="mb-4 rounded-lg border border-yellow-300 bg-yellow-50 px-4 py-3
            text-sm text-yellow-800 dark:border-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-200">
            <div class="flex items-start gap-2">
                <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v4m0 4h.01M10.29 3.86l-7.4 12.82A1.5 1.5 0 004.19 19h15.62a1.5 1.5 0 001.3-2.32l-7.4-12.82a1.5 1.5 0 00-2.6 0z" />
                </svg>

                <div>
                    <p class="font-semibold mb-1">Catatan Perubahan Harga</p>
                    <ul class="list-disc pl-5 space-y-1">
                        <li>
                            <strong>Setujui (ACC)</strong> akan mengubah harga <strong>seluruh unit</strong>
                            yang menggunakan tipe ini, dengan ketentuan
                            <strong>status unit masih <span class="font-semibold">READY</span></strong>.
                        </li>
                        <li>
                            <strong>Tolak</strong> akan membatalkan pengajuan perubahan harga
                            dan menghapus data pengajuan.
                        </li>
                    </ul>
                </div>
            </div>
        </div>


        <div class="space-y-5 sm:space-y-6">
            <div
                class="rounded-2xl border border-gray-200 px-5 py-4 sm:px-6 sm:py-5 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        Pengajuan Perubahaan Harga Tipe Unit
                    </h3>
                </div>



                <table id="table-pengajuanHargaTipe">
                    <thead>
                        <tr>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                                <span class="flex items-center">
                                    Tipe Unit
                                    <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                    </svg>
                                </span>
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                                Diajukan Oleh
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                                <span class="flex items-center">
                                    Harga Saat ini(Rp)
                                    <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                    </svg>
                                </span>
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                                <span class="flex items-center">
                                    Harga Diajukan (Rp)
                                    <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                    </svg>
                                </span>
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 ">
                                Status Pengajuan
                            </th>
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 ">
                                <span class="flex items-center">
                                    Tanggal Diajukan
                                    <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                    </svg>
                                </span>
                            </th>
                            @can('etalase.perubahaan-harga.type-unit.action')
                            <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                                Aksi
                            </th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($types as $item)
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">

                                {{-- Tipe Unit --}}
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                    {{ $item->nama_type }}
                                </td>

                                {{-- Diajukan Oleh --}}
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                    {{ $item->diajukanOleh?->username ?? '-' }}
                                </td>

                                {{-- Harga Saat Ini --}}
                                <td class="px-6 py-4 text-gray-900 dark:text-white">
                                    Rp {{ number_format($item->harga_dasar ?? 0, 0, ',', '.') }}
                                </td>

                                {{-- Harga Diajukan --}}
                                <td class="px-6 py-4 text-center text-gray-900 dark:text-white">
                                    Rp {{ number_format($item->harga_diajukan ?? 0, 0, ',', '.') }}
                                </td>

                                {{-- Status Pengajuan --}}
                                <td class="px-6 py-4">
                                    @php
                                        $statusClass = match ($item->status_pengajuan) {
                                            'pending'
                                                => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-200',
                                            'acc'
                                                => 'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-200',
                                            'tolak' => 'bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-200',
                                            default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                        };
                                    @endphp

                                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                                        {{ strtoupper($item->status_pengajuan ?? '-') }}
                                    </span>
                                </td>

                                {{-- Tanggal Diajukan --}}
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                    {{ $item->tanggal_pengajuan ? \Carbon\Carbon::parse($item->tanggal_pengajuan)->format('d M Y') : '-' }}
                                </td>

                                {{-- Aksi --}}
                                @can('etalase.perubahaan-harga.type-unit.action')
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex justify-center gap-2">

                                            {{-- ACC --}}
                                            <form action="{{ route('perubahan-harga.tipe-unit.approvePengajuan', $item->id) }}"
                                                method="POST" class="approve-form">
                                                @csrf

                                                <button type="button"
                                                    class="approve-btn px-3 py-1.5 text-xs font-medium rounded
                                                bg-green-600 text-white hover:bg-green-700
                                                disabled:bg-gray-400 disabled:cursor-not-allowed"
                                                    {{ $item->status_pengajuan !== 'pending' ? 'disabled' : '' }}>
                                                    ACC
                                                </button>
                                            </form>


                                            {{-- tolak pengajuan --}}
                                            <form action="{{ Route('perubahan-harga.tipe-unit.tolakPengajuan', $item->id) }}"
                                                method="POST" class="tolak-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button"
                                                    class="tolak-btn px-3 py-1 text-sm text-white bg-red-600 rounded hover:bg-red-700">
                                                    Tolak
                                                </button>
                                            </form>

                                        </div>
                                    </td>
                                @endcan
                            </tr>
                        @empty
                        @endforelse
                    </tbody>

                </table>

            </div>
        </div>

    </div>

    <script>
        document.addEventListener('click', function(e) {
            // button tolak
            if (e.target.closest('.tolak-btn')) {
                const btn = e.target.closest('.tolak-btn');
                const form = btn.closest('.tolak-form');

                Swal.fire({
                    title: 'Tolak pengajuan perubahan harga?',
                    text: "Pengajuan perubahan harga yang ditolak tidak dapat dikembalikan lagi.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Tolak!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            }

            // button acc
            if (e.target.closest('.approve-btn')) {
                const btn = e.target.closest('.approve-btn');
                const form = btn.closest('.approve-form');

                Swal.fire({
                    title: 'Yakin menyetujui pengajuan perubahan harga?',
                    text: "Pengajuan perubahan harga yang disetujui akan diterapkan secara langsung.",
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    cancelButtonText: 'Batal',
                    confirmButtonText: 'Ya, Setujui!',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            }
        });

        if (document.getElementById("table-pengajuanHargaTipe") && typeof simpleDatatables.DataTable !== 'undefined') {
            const dataTable = new simpleDatatables.DataTable("#table-pengajuanHargaTipe", {
                searchable: true,
                sortable: true,
            });
        }
    </script>
@endsection
