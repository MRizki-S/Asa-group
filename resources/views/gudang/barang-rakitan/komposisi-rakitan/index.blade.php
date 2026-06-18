@extends('layouts.app')

@section('pageActive', 'KomposisiRakitan')

@section('content')
<!-- ===== Main Content Start ===== -->
<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

    <!-- Breadcrumb Start -->
    <div x-data="{ pageName: 'KomposisiRakitan' }">
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
                    Komposisi Rakitan
                </h3>

                <a href="{{ route('gudang.komposisiRakitan.create') }}"
                    class="inline-block px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                    + Tambah Komposisi
                </a>
            </div>



            <table id="table-komposisiRakitan">
                <thead>
                    <tr>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                            <span class="flex items-center">
                                Barang Hasil
                                <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                    width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                </svg>
                            </span>
                        </th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                            <span class="flex items-center">
                                Hasil Produksi
                                <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                    width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                </svg>
                            </span>
                        </th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                            Jumlah Komponen Bahan
                        </th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                            Status
                        </th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                            Keterangan
                        </th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                            Cretaed By
                        </th>
                        <th class="bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-400 text-center">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($barangRakitans as $item)
                    <tr>
                        {{-- Barang Hasil --}}
                        <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            <div>{{ $item->barangHasil->nama_barang ?? '-' }}</div>
                            <div class="text-xs font-normal text-gray-500 dark:text-gray-400">
                                {{ $item->barangHasil->kode_barang ?? '-' }}
                            </div>
                        </td>

                        {{-- Hasil Produksi --}}
                        <td class="font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            {{ rtrim(rtrim(number_format((float) $item->qty_hasil, 3, ',', '.'), '0'), ',') }}
                            {{ $item->satuanHasil->nama ?? '-' }}
                        </td>

                        {{-- Komponen Bahan --}}
                        <td class="font-medium text-gray-900 dark:text-white">
                            <div class="space-y-1">
                                @forelse ($item->details as $detail)
                                <div class="text-sm">
                                    <span>{{ $detail->barangBahan->nama_barang ?? '-' }}</span>
                                    <span class="text-gray-500 dark:text-gray-400">
                                        ({{ rtrim(rtrim(number_format((float) $detail->qty, 3, ',', '.'), '0'), ',') }}
                                        {{ $detail->satuan->nama ?? '-' }})
                                    </span>
                                </div>
                                @empty
                                <span class="text-gray-500 dark:text-gray-400">Belum ada bahan</span>
                                @endforelse
                            </div>
                        </td>

                        {{-- Status --}}
                        <td class="p-2 whitespace-nowrap align-middle">
                            @if($item->status === 'active')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                Active
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                Inactive
                            </span>
                            @endif
                        </td>

                        {{-- Keterangan --}}
                        <td class="font-medium text-center text-gray-900 whitespace-nowrap dark:text-white">
                            {{ $item->keterangan ?: '-' }}
                        </td>

                        {{-- Dibuat Oleh --}}
                        <td class="font-medium text-center text-gray-900 whitespace-nowrap dark:text-white">
                            {{ $item->creator->username ?? '-' }}
                        </td>

                        {{-- Aksi --}}
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-2 justify-center">
                                <a href="{{ route('gudang.komposisiRakitan.edit', $item) }}"
                                    class="inline-flex items-center gap-1 text-xs font-medium text-yellow-700 bg-yellow-100 hover:bg-yellow-200 dark:bg-yellow-800 dark:text-yellow-100 dark:hover:bg-yellow-700 px-2.5 py-1.5 rounded-md transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-1 active:scale-95">
                                    Edit
                                </a>

                                <form action="{{ route('gudang.komposisiRakitan.destroy', $item) }}"
                                    method="POST" class="delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        class="delete-btn px-3 py-1.5 text-xs text-white bg-red-600 rounded-md hover:bg-red-700">
                                        Delete
                                    </button>
                                </form>
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

{{-- sweatalert 2 for delete data --}}
<script>
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-btn')) {
            const btn = e.target.closest('.delete-btn');
            const form = btn.closest('.delete-form');

            Swal.fire({
                title: 'Yakin hapus data ini?',
                text: "Data yang sudah dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    });

    if (document.getElementById("table-komposisiRakitan") && typeof simpleDatatables.DataTable !== 'undefined') {
        const dataTable = new simpleDatatables.DataTable("#table-komposisiRakitan", {
            searchable: true,
            sortable: true,
            perPageSelect: [10, 25, 50, 100]
        });
    }
</script>
@endsection
