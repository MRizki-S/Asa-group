@extends('layouts.app')

@section('pageActive', 'ManagePemesanan')

@section('content')
    <div class="max-w-[--breakpoint-2xl] p-4 md:p-6">
        <!-- ✅ Breadcrumb -->
        <div x-data="{ pageName: 'ManagePemesanan' }">
            @include('partials.breadcrumb')
        </div>

        <!-- ⚠️ Alert Validasi -->
        @if ($errors->any())
            <div class="flex p-4 mb-4 text-red-800 rounded-lg bg-red-50" role="alert">
                <svg class="shrink-0 w-4 h-4 me-3 mt-[2px]" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                    viewBox="0 0 20 20">
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
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 mb-6">
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
                <p><span class="font-medium text-gray-700">Nama User :</span> {{ $pemesanan->customer->username ?? '-' }}
                </p>
                <p><span class="font-medium text-gray-700">Nama Sales :</span> {{ $pemesanan->sales->username ?? '-' }}</p>
                <p><span class="font-medium text-gray-700">Unit :</span> {{ $pemesanan->unit->nama_unit ?? '-' }}
                    ({{ $pemesanan->perumahaan->nama_perumahaan ?? '-' }})</p>
                <p><span class="font-medium text-gray-700">No HP User:</span> {{ $pemesanan->dataDiri->no_hp ?? '-' }}</p>
                <p><span class="font-medium text-gray-700">Cara Bayar :</span> KPR</p>
                <p><span class="font-medium text-gray-700">Bank :</span> {{ $pemesanan->kpr->bank->nama_bank ?? '-' }}
                    ({{ $pemesanan->kpr->bank->kode_bank ?? '-' }})</p>
            </div>
        </div>
        <form method="POST" action="{{ route('marketing.kelengkapanBerkasKpr.updateKpr', $pemesanan->id) }}">
            @csrf
            @method('PUT')

            @php
                // cek apakah user boleh update berkas
                $bolehUpdate = auth()->user()->can('marketing.kelola-pemesanan.update-berkas');
            @endphp

            {{-- Form Ganti Bank & Status KPR --}}
            <div
                class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 mb-6 grid grid-cols-1 sm:grid-cols-2 gap-4">

                <!-- Bank -->
                <div>
                    <label for="bank_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Bank
                    </label>
                    <select id="bank_id" name="bank_id"
                        class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2"
                        {{ $bolehUpdate ? '' : 'disabled' }}>
                        @foreach ($bankList as $bank)
                            <option value="{{ $bank->id }}"
                                {{ $pemesanan->kpr->bank_id == $bank->id ? 'selected' : '' }}>
                                {{ $bank->nama_bank }} ({{ $bank->kode_bank }})
                            </option>
                        @endforeach
                    </select>

                    @if ($bolehUpdate)
                        <div class="mt-2 text-sm text-gray-700 bg-blue-50 border border-blue-100 rounded-lg p-2">
                            💬 Jika ingin mengganti bank, pilih bank baru, lalu klik
                            <span class="font-semibold text-blue-700">Simpan Perubahan</span>.
                        </div>
                    @endif
                </div>

                <!-- Status KPR -->
                <div>
                    <label for="status_kpr" class="block text-sm font-medium text-gray-700 mb-1">
                        Status KPR
                    </label>
                    <select id="status_kpr" name="status_kpr"
                        class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2"
                        {{ $bolehUpdate ? '' : 'disabled' }}>
                        @foreach ($statusList as $value => $label)
                            <option value="{{ $value }}"
                                {{ $pemesanan->kpr->status_kpr == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>

                    @if ($bolehUpdate)
                        <div
                            class="mt-2 flex items-start gap-2 rounded-lg bg-blue-50 border border-blue-200 p-2 text-sm text-blue-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mt-0.5 flex-shrink-0 text-blue-600"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 110 20 10 10 0 010-20z" />
                            </svg>
                            <p>
                                Jika <span class="font-semibold">status KPR</span> diubah menjadi
                                <span class="font-semibold text-blue-600">"ACC"</span>, sistem akan
                                <span class="font-semibold">mengirimkan notifikasi WhatsApp</span> secara otomatis ke
                                customer.
                            </p>
                        </div>
                    @endif
                </div>

            </div>




            <!-- 🧾 Form Checklist Dokumen -->
            @forelse ($dokumenList as $kategori => $dokumens)
                <div x-data="{ open: true }"
                    class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden mb-6">
                    <!-- Accordion Header -->
                    <button type="button" @click="open = !open"
                        class="w-full flex justify-between items-center bg-gray-100 px-4 py-3 border-b hover:bg-gray-200 transition">
                        <h3 class="font-semibold text-gray-800 uppercase tracking-wide">
                            {{ str_replace('_', ' ', $kategori) }}
                        </h3>
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="w-5 h-5 text-gray-600 transform transition-transform duration-200"
                            :class="{ 'rotate-90': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>

                    <!-- Accordion Body -->
                    <div x-show="open" x-collapse.duration.200ms>
                        <table class="w-full border-collapse">
                            <thead class="bg-gray-50 text-gray-700 border-b">
                                <tr>
                                    <th class="py-3 px-4 text-center w-12">No</th>
                                    <th class="py-3 px-4 text-left">Nama Dokumen</th>
                                    <th class="py-3 px-4 text-center w-40">Tanggal Diubah</th>
                                    <th class="py-3 px-4 text-center w-40">Update By</th>
                                    <th class="py-3 px-4 text-center w-20">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($dokumens as $index => $dok)
                                    <tr class="hover:bg-blue-50 transition">
                                        <td class="py-3 px-4 text-center text-gray-700">{{ $index + 1 }}</td>
                                        <td class="py-3 px-4 text-gray-800">{{ $dok->masterDokumen->nama_dokumen ?? '-' }}
                                        </td>
                                        <td class="py-3 px-4 text-center text-gray-600">
                                            {{ $dok->tanggal_update ? $dok->tanggal_update->format('d M Y') : '-' }}
                                        </td>
                                        <td class="py-3 px-4 text-center text-gray-600">
                                            {{ $dok->updatedBy->username ?? '-' }}
                                        </td>

                                        <td class="py-3 px-4 text-center">
                                            <input type="checkbox" name="dokumen[{{ $dok->id }}]" value="1"
                                                {{ $dok->status ? 'checked' : '' }} {{ $bolehUpdate ? '' : 'disabled' }}
                                                class="w-5 h-5 accent-blue-600 rounded focus:ring-blue-500
                                            {{ $bolehUpdate ? '' : 'cursor-not-allowed opacity-60' }}">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="text-center text-gray-500 italic">Tidak ada daftar dokumen KPR untuk bank ini.</div>
            @endforelse

            <!-- 🔘 Tombol Aksi -->
            <div class="mt-6 flex justify-end">
                <a href="{{ route('marketing.managePemesanan.index') }}"
                    class="inline-flex items-center gap-1 px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                    <i class="ri-arrow-go-back-line"></i> Kembali
                </a>
                @if ($bolehUpdate)
                    <button type="submit"
                        class="ml-2 inline-flex items-center gap-1 px-6 py-2.5 bg-gradient-to-r from-blue-600 to-sky-500 text-white rounded-lg shadow hover:shadow-md hover:scale-[1.02] transition">
                        <i class="ri-save-3-line"></i> Simpan Perubahan
                    </button>
                @endif
            </div>
        </form>
    </div>
@endsection
