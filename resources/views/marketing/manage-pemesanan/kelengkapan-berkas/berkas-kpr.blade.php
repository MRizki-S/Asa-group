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
            {{-- Proses & Timeline KPR --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 mb-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Proses & Timeline KPR
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
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
                            <div class="mt-2 text-xs text-gray-700 bg-blue-50 border border-blue-100 rounded-lg p-2">
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
                    </div>
                </div>

                <!-- Divider -->
                <div class="border-t border-gray-100 my-6"></div>

                <!-- Timeline Section -->
                <div class="mb-4">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Timeline Tanggal Proses</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <!-- Tanggal Masuk Berkas -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Tanggal Masuk Berkas
                            </label>
                            <div class="relative" x-data="{
                                tampil: '{{ $pemesanan->kpr->tanggal_masuk_berkas ? $pemesanan->kpr->tanggal_masuk_berkas->format('d-m-Y') : '' }}',
                                simpan: '{{ $pemesanan->kpr->tanggal_masuk_berkas ? $pemesanan->kpr->tanggal_masuk_berkas->format('Y-m-d') : '' }}'
                            }">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-500" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                                    </svg>
                                </div>
                                <input type="text" x-model="tampil" x-init="flatpickr($el, {
                                    dateFormat: 'd-m-Y',
                                    defaultDate: tampil || null,
                                    onChange: (dates, dateStr) => {
                                        tampil = dateStr;
                                        if (dates.length > 0) {
                                            const d = dates[0];
                                            simpan = d.getFullYear() + '-' +
                                                ('0' + (d.getMonth() + 1)).slice(-2) + '-' +
                                                ('0' + d.getDate()).slice(-2);
                                        } else {
                                            simpan = '';
                                        }
                                    }
                                })" placeholder="Pilih tanggal"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 @error('tanggal_masuk_berkas') border-red-500 focus:ring-red-500 @enderror"
                                    {{ $bolehUpdate ? '' : 'disabled' }}>
                                <input type="hidden" name="tanggal_masuk_berkas" x-model="simpan">
                            </div>
                            @error('tanggal_masuk_berkas')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tanggal ACC -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Tanggal ACC
                            </label>
                            <div class="relative" x-data="{
                                tampil: '{{ $pemesanan->kpr->tanggal_acc ? $pemesanan->kpr->tanggal_acc->format('d-m-Y') : '' }}',
                                simpan: '{{ $pemesanan->kpr->tanggal_acc ? $pemesanan->kpr->tanggal_acc->format('Y-m-d') : '' }}'
                            }">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-500" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                                    </svg>
                                </div>
                                <input type="text" x-model="tampil" x-init="flatpickr($el, {
                                    dateFormat: 'd-m-Y',
                                    defaultDate: tampil || null,
                                    onChange: (dates, dateStr) => {
                                        tampil = dateStr;
                                        if (dates.length > 0) {
                                            const d = dates[0];
                                            simpan = d.getFullYear() + '-' +
                                                ('0' + (d.getMonth() + 1)).slice(-2) + '-' +
                                                ('0' + d.getDate()).slice(-2);
                                        } else {
                                            simpan = '';
                                        }
                                    }
                                })" placeholder="Pilih tanggal"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 @error('tanggal_acc') border-red-500 focus:ring-red-500 @enderror"
                                    {{ $bolehUpdate ? '' : 'disabled' }}>
                                <input type="hidden" name="tanggal_acc" x-model="simpan">
                            </div>
                            @error('tanggal_acc')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tanggal Realisasi -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Tanggal Realisasi
                            </label>
                            <div class="relative" x-data="{
                                tampil: '{{ $pemesanan->kpr->tanggal_realisasi ? $pemesanan->kpr->tanggal_realisasi->format('d-m-Y') : '' }}',
                                simpan: '{{ $pemesanan->kpr->tanggal_realisasi ? $pemesanan->kpr->tanggal_realisasi->format('Y-m-d') : '' }}'
                            }">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-500" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                                    </svg>
                                </div>
                                <input type="text" x-model="tampil" x-init="flatpickr($el, {
                                    dateFormat: 'd-m-Y',
                                    defaultDate: tampil || null,
                                    onChange: (dates, dateStr) => {
                                        tampil = dateStr;
                                        if (dates.length > 0) {
                                            const d = dates[0];
                                            simpan = d.getFullYear() + '-' +
                                                ('0' + (d.getMonth() + 1)).slice(-2) + '-' +
                                                ('0' + d.getDate()).slice(-2);
                                        } else {
                                            simpan = '';
                                        }
                                    }
                                })" placeholder="Pilih tanggal"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 @error('tanggal_realisasi') border-red-500 focus:ring-red-500 @enderror"
                                    {{ $bolehUpdate ? '' : 'disabled' }}>
                                <input type="hidden" name="tanggal_realisasi" x-model="simpan">
                            </div>
                            @error('tanggal_realisasi')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Alur visual --}}
                <div class="mt-4 flex flex-wrap items-center gap-2 text-sm text-gray-600 bg-gray-50 border border-gray-100 rounded-lg p-3">
                    <span class="font-medium text-blue-600">Urutan Proses:</span>
                    <span>Masuk Berkas</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span>ACC</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span>Realisasi</span>
                </div>

                <p class="mt-3 text-xs text-gray-500 italic">
                    "Catat tanggal setiap tahapan proses KPR untuk kebutuhan monitoring dan laporan realisasi bulanan."
                </p>
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
