@extends('layouts.app')

@section('pageActive', 'PengajuanPembangunan')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">
<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

    @include('partials.breadcrumb', ['breadcrumbs' => [
        ['label' => 'Permintaan Dibangun', 'url' => route('produksi.pengajuanPembangunanUnit.index')],
        ['label' => 'Edit Pembangunan', 'url' => '']
    ]])

    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({ icon: 'success', title: 'Berhasil', text: '{{ session('success') }}', showConfirmButton: false, timer: 2000 });
            });
        </script>
    @endif
    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({ icon: 'error', title: 'Gagal', text: '{{ session('error') }}', showConfirmButton: true });
            });
        </script>
    @endif

    {{-- Header Info Card --}}
    @php
        $statusVal = $pengajuan->status_pengajuan ?? ($pembangunan->status_pengajuan ?? 'dibangun');
    @endphp
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow-sm mb-6 p-5 md:p-6 mt-6">
        <div class="flex flex-col gap-4">
            {{-- Header Top: Nama Unit & Badge Status --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 dark:border-gray-800 pb-4">
                <div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <h2 class="text-xl md:text-2xl font-bold text-gray-800 dark:text-white leading-tight">
                            Unit {{ $pembangunan->unit->nama_unit ?? 'Tidak Diketahui' }}
                        </h2>
                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                            {{ $statusVal === 'selesai' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60' :
                               ($statusVal === 'dibangun' ? 'bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400 border border-blue-200 dark:border-blue-800/60' :
                               'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200 dark:border-amber-800/60') }}">
                            {{ $statusVal === 'dibangun' ? 'Proses Pembangunan' : ucfirst($statusVal) }}
                        </span>
                    </div>
                    <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400 mt-2 flex flex-wrap items-center gap-x-3 gap-y-1">
                        <span><i class="fa-solid fa-location-dot me-1 text-red-500"></i>{{ $pembangunan->perumahaan->nama_perumahaan ?? '-' }}</span>
                        <span class="text-gray-300 dark:text-gray-600">|</span>
                        <span><i class="fa-solid fa-layer-group me-1 text-blue-500"></i><span class="font-semibold text-gray-600 dark:text-gray-300">Tahap:</span> {{ $pembangunan->tahap->nama_tahap ?? '-' }}</span>
                    </p>
                </div>
            </div>

            {{-- Header Cards Grid: Info QC, SPV, Pengawas & Jadwal --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-1">
                {{-- QC Container --}}
                <div class="p-3.5 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700/60">
                    <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">QC Container</p>
                    <p class="text-xs font-bold text-blue-600 dark:text-blue-400 truncate">{{ $pembangunan->qcContainer->nama_container ?? '-' }}</p>
                </div>

                {{-- SPV --}}
                <div class="p-3.5 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700/60">
                    <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">SPV Drafting &amp; Estimasi</p>
                    <p class="text-xs font-bold text-gray-800 dark:text-gray-200 truncate">{{ $pembangunan->spv->nama_lengkap ?? '-' }}</p>
                </div>

                {{-- Pengawas Unit --}}
                <div class="p-3.5 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700/60">
                    <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Pengawas Unit</p>
                    <p class="text-xs font-bold text-gray-800 dark:text-gray-200 truncate">{{ $pembangunan->pengawas->nama_lengkap ?? '-' }}</p>
                </div>

                {{-- Jadwal Pembangunan --}}
                <div class="p-3.5 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700/60">
                    <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Jadwal Pembangunan</p>
                    <p class="text-xs font-bold text-gray-800 dark:text-gray-200">
                        {{ $pembangunan->tanggal_mulai ? \Carbon\Carbon::parse($pembangunan->tanggal_mulai)->format('d M Y') : '-' }}
                        <span class="text-gray-400 font-normal">s/d</span>
                        {{ $pembangunan->tanggal_selesai ? \Carbon\Carbon::parse($pembangunan->tanggal_selesai)->format('d M Y') : '-' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Form Edit --}}
    <form action="{{ route('produksi.pengajuanPembangunanUnit.update', $pembangunan->id) }}" method="POST" x-data="{ submitting: false }" @submit="if(submitting) { $event.preventDefault(); return; }; submitting = true">
        @csrf
        @method('PUT')

        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow-sm mb-6 p-5">
            <h3 class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100 dark:border-gray-800">
                Lokasi &amp; Pengawas
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-white">Perumahan</label>
                    <input type="text" readonly value="{{ $pembangunan->perumahaan->nama_perumahaan ?? 'N/A' }}"
                        class="w-full bg-gray-100 border border-gray-300 text-gray-500 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400 cursor-not-allowed outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-white">Tahap</label>
                    <input type="text" readonly value="{{ $pembangunan->tahap->nama_tahap ?? 'N/A' }}"
                        class="w-full bg-gray-100 border border-gray-300 text-gray-500 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400 cursor-not-allowed outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-white">Unit</label>
                    <input type="text" readonly value="{{ $pembangunan->unit->nama_unit ?? 'N/A' }}"
                        class="w-full bg-gray-100 border border-gray-300 text-gray-500 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400 cursor-not-allowed outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-white">SPV Drafting, Teknis &amp; Estimasi</label>
                    <input type="text" readonly value="{{ $pembangunan->spv->nama_lengkap ?? '-' }}"
                        class="w-full bg-gray-100 border border-gray-300 text-gray-500 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400 cursor-not-allowed outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-white">Pengawas Unit <span class="text-red-500">*</span></label>
                    <select name="pengawas_id" required id="selectPengawas"
                        class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Pilih Pengawas</option>
                        @foreach ($allPengawas as $user)
                            <option value="{{ $user->id }}" {{ $pembangunan->pengawas_id == $user->id ? 'selected' : '' }}>
                                {{ $user->nama_lengkap }}
                            </option>
                        @endforeach
                    </select>
                    <script>
                        $(document).ready(function() {
                            $('#selectPengawas').select2({
                                placeholder: "-- Pilih Pengawas --",
                                theme: 'bootstrap4',
                                allowClear: true,
                                width: '100%'
                            });
                        });
                    </script>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-white">Subcon <span class="text-red-500">*</span></label>
                    <input type="text" name="subcon" required value="{{ old('subcon', $pembangunan->subcon) }}" placeholder="Masukkan subcon..."
                        class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow-sm mb-6 p-5">
            <h3 class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100 dark:border-gray-800">
                Detail Pembangunan &amp; QC
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6" x-data="{ simpanMulai: '{{ \Carbon\Carbon::parse($pembangunan->tanggal_mulai)->format('Y-m-d') }}', simpanSelesai: '{{ \Carbon\Carbon::parse($pembangunan->tanggal_selesai)->format('Y-m-d') }}', endPickerEdit: null }">
                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-white">Master QC Container</label>
                    <input type="text" readonly value="{{ $pembangunan->qcContainer->nama_container ?? '-' }}"
                        class="w-full bg-gray-100 border border-gray-300 text-gray-500 text-sm rounded-lg p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400 cursor-not-allowed outline-none">
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tanggal Mulai <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none z-10">
                            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" /></svg>
                        </div>
                        <input type="text" x-init="flatpickr($el, {
                            dateFormat: 'd-m-Y',
                            defaultDate: '{{ \Carbon\Carbon::parse($pembangunan->tanggal_mulai)->format('d-m-Y') }}',
                            onChange: (selectedDates, dateStr, instance) => {
                                simpanMulai = instance.formatDate(selectedDates[0], 'Y-m-d');
                                if (endPickerEdit) {
                                    endPickerEdit.set('minDate', selectedDates[0]);
                                }
                            }
                        })"
                            class="w-full pl-10 pr-3 py-2.5 text-gray-700 rounded-lg border border-gray-300 bg-gray-50 text-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white outline-none"
                            placeholder="Pilih Tanggal Mulai">
                        <input type="hidden" name="tanggal_mulai" x-model="simpanMulai">
                    </div>
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Estimasi Selesai <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none z-10">
                            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" /></svg>
                        </div>
                        <input type="text" x-init="endPickerEdit = flatpickr($el, {
                            dateFormat: 'd-m-Y',
                            defaultDate: '{{ \Carbon\Carbon::parse($pembangunan->tanggal_selesai)->format('d-m-Y') }}',
                            minDate: '{{ \Carbon\Carbon::parse($pembangunan->tanggal_mulai)->format('d-m-Y') }}',
                            onChange: (selectedDates, dateStr, instance) => { simpanSelesai = instance.formatDate(selectedDates[0], 'Y-m-d'); }
                        })"
                            class="w-full pl-10 pr-3 py-2.5 text-gray-700 rounded-lg border border-gray-300 bg-gray-50 text-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white outline-none"
                            placeholder="Pilih Tanggal Selesai">
                        <input type="hidden" name="tanggal_selesai" x-model="simpanSelesai">
                    </div>
                </div>
            </div>
        </div>

        {{-- Tombol Aksi --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('produksi.pengajuanPembangunanUnit.index') }}"
                class="inline-flex items-center justify-center px-5 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-sm font-bold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition shadow-sm">
                Batal
            </a>
            <button type="submit" :disabled="submitting" :class="submitting ? 'opacity-50 cursor-not-allowed' : ''"
                class="inline-flex items-center justify-center px-5 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-lg hover:bg-blue-700 transition shadow-sm">
                <span x-text="submitting ? 'Memproses...' : 'Simpan Perubahan'"></span>
            </button>
        </div>
    </form>
</div>
@endsection
