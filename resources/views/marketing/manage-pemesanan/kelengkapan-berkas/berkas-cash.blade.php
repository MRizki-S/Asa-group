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

        <!-- 📦 Info Pemesanan -->
        <div class="bg-white border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-5 mb-6">
            <h2 class="text-base font-semibold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                    width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                    <path fill-rule="evenodd"
                        d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12Zm9.408-5.5a1 1 0 1 0 0 2h.01a1 1 0 1 0 0-2h-.01ZM10 10a1 1 0 1 0 0 2h1v3h-1a1 1 0 1 0 0 2h4a1 1 0 1 0 0-2h-1v-4a1 1 0 0 0-1-1h-2Z"
                        clip-rule="evenodd" />
                </svg>

                Info
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-2">
                <p>
                    <span class="font-medium text-gray-700 dark:text-gray-300">Nama User :</span>
                    <span class="text-gray-800 dark:text-gray-100">{{ $pemesanan->customer->username ?? '-' }}</span>
                </p>
                <p>
                    <span class="font-medium text-gray-700 dark:text-gray-300">Unit:</span>
                    <span class="text-gray-800 dark:text-gray-100">
                        {{ $pemesanan->unit->nama_unit ?? '-' }}
                        ({{ $pemesanan->perumahaan->nama_perumahaan ?? '-' }})
                    </span>
                </p>
                <p>
                    <span class="font-medium text-gray-700 dark:text-gray-300">Nama Sales:</span>
                    <span class="text-gray-800 dark:text-gray-100">{{ $pemesanan->sales->username ?? '-' }}</span>
                </p>
                <p>
                    <span class="font-medium text-gray-700 dark:text-gray-300">No Hp:</span>
                    <span class="text-gray-800 dark:text-gray-100">{{ $pemesanan->dataDiri->no_hp ?? '-' }}</span>
                </p>
            </div>
        </div>

        <!-- 🧾 Form Checklist Dokumen -->
        <form method="POST" action="{{ url('marketing/manage-pemesanan/kelengkapan-berkas-cash/' . $pemesanan->id) }}">
            @csrf
            @method('PUT')

            <div
                class="bg-white border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden max-w-3xl">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-b">
                        <tr>
                            <th class="py-3 px-4 text-center w-12">No</th>
                            <th class="py-3 px-4 text-left">Nama Dokumen</th>
                            <th class="py-3 px-4 text-center w-40">Tanggal DiUbah</th>
                            <th class="py-3 px-4 text-center w-">Update By</th>
                            <th class="py-3 px-4 text-center w-20">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse ($dokumenList as $index => $dokumen)
                            <tr class="hover:bg-blue-50/50 dark:hover:bg-gray-800 transition">
                                <td class="py-3 px-4 text-gray-700 dark:text-gray-300 text-center">
                                    {{ $index + 1 }}
                                </td>
                                <td class="py-3 px-4 text-gray-800 dark:text-gray-100">
                                    {{ $dokumen->nama_dokumen }}
                                </td>
                                <td class="py-3 px-4 text-center text-gray-600 dark:text-gray-300">
                                    @if ($dokumen->status)
                                        {{ $dokumen->tanggal_update ? $dokumen->tanggal_update->format('d M Y') : '-' }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-gray-600 text-center">
                                    {{ $dokumen->updatedBy ? $dokumen->updatedBy->username : '-' }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <input type="checkbox" name="dokumen[{{ $dokumen->nama_dokumen }}]" value="1"
                                        {{ $dokumen->status == 1 ? 'checked' : '' }}
                                        class="w-5 h-5 accent-blue-600 transition-all duration-200 focus:ring-blue-500 rounded">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 px-4 text-center text-gray-500 dark:text-gray-400 italic">
                                    Tidak ada daftar berkas untuk pemesanan ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- 🔘 Tombol Aksi -->
            <div class="mt-6 flex justify-end">
                <div class="flex items-center gap-2">
                    <a href="{{ route('marketing.managePemesanan.index') }}"
                        class="inline-flex items-center gap-1 px-5 py-2.5 font-medium bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                        <i class="ri-arrow-go-back-line"></i> Kembali
                    </a>

                    <button type="submit"
                        class="inline-flex items-center gap-1 px-6 py-2.5 font-medium bg-gradient-to-r from-blue-600 to-sky-500 text-white rounded-lg shadow hover:shadow-md hover:scale-[1.02] transition">
                        <i class="ri-save-3-line"></i> Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>

    </div>
@endsection
