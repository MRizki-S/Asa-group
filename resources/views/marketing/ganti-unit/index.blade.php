@extends('layouts.app')

@section('pageActive', 'GantiUnit')

@section('content')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">

    <style>
        .select2-container--bootstrap4 .select2-selection--single {
            height: 44px !important;
            padding: 8px 12px !important;
            border-radius: 0.5rem !important;
            border: 1px solid #d1d5db !important;
            display: flex !important;
            align-items: center !important;
        }

        .dark .select2-container--bootstrap4 .select2-selection--single {
            background-color: #1f2937 !important;
            border-color: #374151 !important;
            color: #f3f4f6 !important;
        }

        .dark .select2-container--bootstrap4 .select2-selection__rendered {
            color: #f3f4f6 !important;
        }

        .dark .select2-dropdown {
            background-color: #1f2937 !important;
            border-color: #374151 !important;
            color: #f3f4f6 !important;
        }

        .dark .select2-results__option {
            color: #f3f4f6 !important;
        }

        .dark .select2-results__option--highlighted {
            background-color: #3b82f6 !important;
            color: #ffffff !important;
        }

        .select2-container {
            width: 100% !important;
        }
    </style>

    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-data="gantiUnitHandler()">

        <!-- Breadcrumb -->
        <div x-data="{ pageName: 'Ganti Unit (Private)' }">
            @include('partials.breadcrumb')
        </div>

        {{-- Alert Success --}}
        @if (session('success'))
            <div class="flex p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400 border border-green-200 dark:border-green-700 shadow-sm"
                role="alert">
                <svg class="shrink-0 inline w-5 h-5 me-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd"></path>
                </svg>
                <div>
                    <span class="font-semibold">Berhasil!</span> {{ session('success') }}
                </div>
            </div>
        @endif

        {{-- Alert Errors --}}
        @if ($errors->any())
            <div class="flex p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400 border border-red-200 dark:border-red-700 shadow-sm"
                role="alert">
                <svg class="shrink-0 inline w-5 h-5 me-3 mt-[2px]" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd"></path>
                </svg>
                <div>
                    <span class="font-semibold">Terjadi Kesalahan:</span>
                    <ul class="mt-1.5 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- Card Form Ganti Unit -->
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03] mb-8">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 dark:border-gray-800 pb-4 mb-6">
                <div>
                    <div class="flex items-center gap-2.5">
                        <span class="p-2 bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 rounded-xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                        </span>
                        <div>
                            <h3 class="text-lg font-bold text-gray-800 dark:text-white">
                                Form Ganti Unit Pemesanan
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Pindahkan pemesanan unit yang sudah di-ACC ke unit baru yang available. Status unit lama akan otomatis kembali menjadi Available dan unit pengganti menjadi Sold.
                            </p>
                        </div>
                    </div>
                </div>

                @if ($namaPerumahaan)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                        🏠 {{ $namaPerumahaan }}
                    </span>
                @endif
            </div>

            <form id="formGantiUnit" action="{{ route('marketing.gantiUnit.store') }}" method="POST" @submit.prevent="submitForm">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
                    
                    {{-- Box Unit Awal --}}
                    <div class="lg:col-span-5 bg-gray-50 dark:bg-gray-800/40 p-5 rounded-2xl border border-gray-200 dark:border-gray-700 space-y-4">
                        <div class="flex items-center justify-between pb-2 border-b border-gray-200 dark:border-gray-700">
                            <span class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-indigo-500"></span> 1. Pilih Pemesanan Unit Asal
                            </span>
                            <span class="text-xs text-gray-500 font-medium">Status ACC</span>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                Pemesanan Unit (Nama Unit & Customer) <span class="text-red-500">*</span>
                            </label>
                            <select id="select_pemesanan_unit" name="pemesanan_unit_id" class="form-control select2 w-full" required>
                                <option value="">-- Pilih Pemesanan Unit (ACC) --</option>
                                @foreach ($pemesananList as $item)
                                    @php
                                        $namaCustomer = $item->customer->nama_lengkap ?? $item->customer->username ?? '-';
                                        $namaUnit = $item->unit->nama_unit ?? '-';
                                        $blok = $item->unit->blok->nama_blok ?? '';
                                        $tahap = $item->tahap->nama_tahap ?? '';
                                        $sales = $item->sales->nama_lengkap ?? $item->sales->username ?? '-';
                                        $label = "{$namaUnit}" . ($blok ? " (Blok {$blok})" : "") . " — {$namaCustomer}" . ($item->customer->username ? " [{$item->customer->username}]" : "");
                                    @endphp
                                    <option value="{{ $item->id }}"
                                        data-unit-id="{{ $item->unit_id }}"
                                        data-unit-name="{{ $namaUnit }}"
                                        data-blok="{{ $blok }}"
                                        data-tahap="{{ $tahap }}"
                                        data-customer="{{ $namaCustomer }}"
                                        data-customer-username="{{ $item->customer->username ?? '-' }}"
                                        data-no-hp="{{ $item->customer->no_hp ?? '-' }}"
                                        data-cara-bayar="{{ strtoupper($item->cara_bayar) }}"
                                        data-sales="{{ $sales }}"
                                        data-tgl-pesan="{{ $item->tanggal_pemesanan ? \Carbon\Carbon::parse($item->tanggal_pemesanan)->format('d M Y') : '-' }}"
                                        {{ old('pemesanan_unit_id') == $item->id ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Preview Detail Pemesanan Terpilih --}}
                        <div x-show="selectedPemesanan.id" x-transition class="bg-white dark:bg-gray-800 p-3.5 rounded-xl border border-gray-200 dark:border-gray-700 text-xs space-y-2">
                            <div class="flex justify-between items-center text-gray-700 dark:text-gray-300">
                                <span class="text-gray-500">Unit Saat Ini:</span>
                                <span class="font-bold text-gray-900 dark:text-white" x-text="selectedPemesanan.unitName + (selectedPemesanan.blok ? ' (Blok ' + selectedPemesanan.blok + ')' : '')"></span>
                            </div>
                            <div class="flex justify-between items-center text-gray-700 dark:text-gray-300">
                                <span class="text-gray-500">Customer:</span>
                                <span class="font-semibold" x-text="selectedPemesanan.customer + ' (' + selectedPemesanan.username + ')'"></span>
                            </div>
                            <div class="flex justify-between items-center text-gray-700 dark:text-gray-300">
                                <span class="text-gray-500">No HP Customer:</span>
                                <span class="font-mono text-blue-600 dark:text-blue-400" x-text="selectedPemesanan.noHp"></span>
                            </div>
                            <div class="flex justify-between items-center text-gray-700 dark:text-gray-300">
                                <span class="text-gray-500">Cara Bayar:</span>
                                <span class="px-2 py-0.5 rounded font-bold"
                                    :class="selectedPemesanan.caraBayar === 'KPR' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700'"
                                    x-text="selectedPemesanan.caraBayar"></span>
                            </div>
                            <div class="flex justify-between items-center text-gray-700 dark:text-gray-300">
                                <span class="text-gray-500">Sales / Tanggal:</span>
                                <span x-text="selectedPemesanan.sales + ' | ' + selectedPemesanan.tglPesan"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Icon Panah Pergantian --}}
                    <div class="lg:col-span-2 flex flex-col items-center justify-center text-center py-2">
                        <div class="p-3 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full border border-blue-200 dark:border-blue-700 shadow-sm animate-pulse">
                            <svg class="w-6 h-6 rotate-90 lg:rotate-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </div>
                        <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400 mt-2 uppercase tracking-wider">
                            Ganti Menjadi
                        </span>
                    </div>

                    {{-- Box Unit Baru --}}
                    <div class="lg:col-span-5 bg-gray-50 dark:bg-gray-800/40 p-5 rounded-2xl border border-gray-200 dark:border-gray-700 space-y-4">
                        <div class="flex items-center justify-between pb-2 border-b border-gray-200 dark:border-gray-700">
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> 2. Pilih Unit Pengganti
                            </span>
                            <span class="text-xs text-emerald-600 font-medium">Status Available</span>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                Unit Pengganti (Available) <span class="text-red-500">*</span>
                            </label>
                            <select id="select_unit_baru" name="unit_baru_id" class="form-control select2 w-full" required>
                                <option value="">-- Pilih Unit Pengganti (Available) --</option>
                                @foreach ($unitAvailable as $unit)
                                    @php
                                        $blok = $unit->blok->nama_blok ?? '';
                                        $tahap = $unit->tahap->nama_tahap ?? '';
                                        $type = $unit->type->nama_type ?? '';
                                        $harga = $unit->harga_final ?? $unit->harga_jual ?? 0;
                                        $labelUnit = "{$unit->nama_unit}" . ($blok ? " (Blok {$blok})" : "") . ($tahap ? " — {$tahap}" : "") . ($type ? " [Type {$type}]" : "");
                                    @endphp
                                    <option value="{{ $unit->id }}"
                                        data-unit-name="{{ $unit->nama_unit }}"
                                        data-blok="{{ $blok }}"
                                        data-tahap="{{ $tahap }}"
                                        data-type="{{ $type }}"
                                        data-harga="{{ number_format($harga, 0, ',', '.') }}"
                                        data-luas-kelebihan="{{ $unit->luas_kelebihan ?? 0 }}"
                                        {{ old('unit_baru_id') == $unit->id ? 'selected' : '' }}>
                                        {{ $labelUnit }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Preview Detail Unit Baru Terpilih --}}
                        <div x-show="selectedUnitBaru.id" x-transition class="bg-white dark:bg-gray-800 p-3.5 rounded-xl border border-gray-200 dark:border-gray-700 text-xs space-y-2">
                            <div class="flex justify-between items-center text-gray-700 dark:text-gray-300">
                                <span class="text-gray-500">Unit Pengganti:</span>
                                <span class="font-bold text-emerald-600 dark:text-emerald-400" x-text="selectedUnitBaru.unitName + (selectedUnitBaru.blok ? ' (Blok ' + selectedUnitBaru.blok + ')' : '')"></span>
                            </div>
                            <div class="flex justify-between items-center text-gray-700 dark:text-gray-300">
                                <span class="text-gray-500">Tahap:</span>
                                <span class="font-semibold" x-text="selectedUnitBaru.tahap || '-'"></span>
                            </div>
                            <div class="flex justify-between items-center text-gray-700 dark:text-gray-300">
                                <span class="text-gray-500">Type / Luas:</span>
                                <span x-text="(selectedUnitBaru.type || '-') + ' | Kelebihan: ' + selectedUnitBaru.luasKelebihan + ' m²'"></span>
                            </div>
                            <div class="flex justify-between items-center text-gray-700 dark:text-gray-300">
                                <span class="text-gray-500">Estimasi Harga Unit:</span>
                                <span class="font-bold text-gray-900 dark:text-white" x-text="'Rp ' + selectedUnitBaru.harga"></span>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-3 mt-8 pt-5 border-t border-gray-100 dark:border-gray-800">
                    <button type="reset" @click="resetSelection"
                        class="px-5 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 rounded-xl transition">
                        Reset
                    </button>
                    
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 active:scale-95 rounded-xl shadow-md transition focus:ring-4 focus:ring-blue-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Simpan & Ganti Unit
                    </button>
                </div>
            </form>
        </div>

        {{-- Tabel Daftar Pemesanan Unit Aktif --}}
        <div class="rounded-2xl border border-gray-200 bg-white px-6 py-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                        Daftar Pemesanan Unit (ACC)
                    </h3>
                    <p class="text-xs text-gray-500">Daftar pemesanan unit yang aktif saat ini</p>
                </div>
                <span class="px-3 py-1 text-xs font-semibold bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-full">
                    Total: {{ $pemesananList->count() }} Pemesanan
                </span>
            </div>

            <div class="overflow-x-auto">
                <table id="table-daftarPemesanan" class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 text-xs uppercase">
                            <th class="px-4 py-3">Nama Unit</th>
                            <th class="px-4 py-3">Customer</th>
                            <th class="px-4 py-3">Cara Bayar</th>
                            <th class="px-4 py-3">Sales / Agen</th>
                            <th class="px-4 py-3">Tgl Pemesanan</th>
                            <th class="px-4 py-3 text-center">Status Unit</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800 text-sm">
                        @forelse ($pemesananList as $pesan)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                <td class="px-4 py-2.5 font-bold text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ $pesan->unit->nama_unit ?? '-' }}
                                    @if ($pesan->unit && $pesan->unit->blok)
                                        <span class="text-xs font-normal text-gray-500">(Blok {{ $pesan->unit->blok->nama_blok }})</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5">
                                    <div class="font-medium text-gray-800 dark:text-gray-200">
                                        {{ $pesan->customer->nama_lengkap ?? $pesan->customer->username ?? '-' }}
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        {{ $pesan->customer->no_hp ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-4 py-2.5">
                                    <span class="px-2 py-0.5 rounded text-xs font-bold {{ $pesan->cara_bayar === 'kpr' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700' }}">
                                        {{ strtoupper($pesan->cara_bayar) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-xs text-gray-600 dark:text-gray-300">
                                    @if ($pesan->source === 'agent' && $pesan->agent)
                                        <span class="text-blue-600 font-medium">Agen: {{ $pesan->agent->nama_agent }}</span>
                                    @else
                                        {{ $pesan->sales->nama_lengkap ?? $pesan->sales->username ?? '-' }}
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-xs text-gray-500 whitespace-nowrap">
                                    {{ $pesan->tanggal_pemesanan ? \Carbon\Carbon::parse($pesan->tanggal_pemesanan)->format('d M Y') : '-' }}
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <span class="px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                                        {{ ucfirst($pesan->unit->status_unit ?? 'sold') }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <button type="button" @click="pilihPemesananLangsung('{{ $pesan->id }}')"
                                        class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                        </svg>
                                        Pilih Unit Ini
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">
                                    Tidak ada data pemesanan unit yang aktif (ACC).
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Select2 & Simple-DataTables Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        function gantiUnitHandler() {
            return {
                selectedPemesanan: {
                    id: '',
                    unitId: '',
                    unitName: '',
                    blok: '',
                    tahap: '',
                    customer: '',
                    username: '',
                    noHp: '',
                    caraBayar: '',
                    sales: '',
                    tglPesan: ''
                },
                selectedUnitBaru: {
                    id: '',
                    unitName: '',
                    blok: '',
                    tahap: '',
                    type: '',
                    harga: '',
                    luasKelebihan: 0
                },

                init() {
                    this.$nextTick(() => {
                        this.initSelect2();
                    });
                },

                initSelect2() {
                    const self = this;

                    // Select Pemesanan Unit
                    $('#select_pemesanan_unit').select2({
                        theme: 'bootstrap4',
                        placeholder: '-- Cari & Pilih Pemesanan Unit (ACC) --',
                        allowClear: true,
                        width: '100%'
                    }).on('change', function() {
                        const val = $(this).val();
                        if (val) {
                            const opt = $(this).find(':selected');
                            self.selectedPemesanan = {
                                id: val,
                                unitId: opt.data('unit-id'),
                                unitName: opt.data('unit-name') || '-',
                                blok: opt.data('blok') || '',
                                tahap: opt.data('tahap') || '',
                                customer: opt.data('customer') || '-',
                                username: opt.data('customer-username') || '-',
                                noHp: opt.data('no-hp') || '-',
                                caraBayar: opt.data('cara-bayar') || '-',
                                sales: opt.data('sales') || '-',
                                tglPesan: opt.data('tgl-pesan') || '-'
                            };
                        } else {
                            self.selectedPemesanan = { id: '', unitId: '', unitName: '', blok: '', tahap: '', customer: '', username: '', noHp: '', caraBayar: '', sales: '', tglPesan: '' };
                        }
                    });

                    // Select Unit Baru
                    $('#select_unit_baru').select2({
                        theme: 'bootstrap4',
                        placeholder: '-- Cari & Pilih Unit Pengganti (Available) --',
                        allowClear: true,
                        width: '100%'
                    }).on('change', function() {
                        const val = $(this).val();
                        if (val) {
                            const opt = $(this).find(':selected');
                            self.selectedUnitBaru = {
                                id: val,
                                unitName: opt.data('unit-name') || '-',
                                blok: opt.data('blok') || '',
                                tahap: opt.data('tahap') || '',
                                type: opt.data('type') || '',
                                harga: opt.data('harga') || '0',
                                luasKelebihan: opt.data('luas-kelebihan') || 0
                            };
                        } else {
                            self.selectedUnitBaru = { id: '', unitName: '', blok: '', tahap: '', type: '', harga: '', luasKelebihan: 0 };
                        }
                    });
                },

                pilihPemesananLangsung(id) {
                    $('#select_pemesanan_unit').val(id).trigger('change');
                    // Scroll smoothly to form
                    document.getElementById('formGantiUnit').scrollIntoView({ behavior: 'smooth' });
                },

                resetSelection() {
                    $('#select_pemesanan_unit').val('').trigger('change');
                    $('#select_unit_baru').val('').trigger('change');
                },

                submitForm() {
                    const pemesananId = $('#select_pemesanan_unit').val();
                    const unitBaruId = $('#select_unit_baru').val();

                    if (!pemesananId) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Peringatan',
                            text: 'Harap pilih pemesanan unit asal terlebih dahulu!'
                        });
                        return;
                    }

                    if (!unitBaruId) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Peringatan',
                            text: 'Harap pilih unit pengganti terlebih dahulu!'
                        });
                        return;
                    }

                    if (this.selectedPemesanan.unitId == unitBaruId) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Unit Sama',
                            text: 'Unit pengganti tidak boleh sama dengan unit asal yang sedang dipesan!'
                        });
                        return;
                    }

                    const namaLama = this.selectedPemesanan.unitName;
                    const namaBaru = this.selectedUnitBaru.unitName;
                    const customer = this.selectedPemesanan.customer;

                    Swal.fire({
                        title: 'Konfirmasi Ganti Unit',
                        html: `Apakah Anda yakin ingin mengganti unit untuk <b>${customer}</b>?<br><br>
                               <span class="text-red-600 font-semibold">${namaLama}</span> ➔ <span class="text-green-600 font-semibold">${namaBaru}</span><br><br>
                               <small class="text-gray-500">Unit ${namaLama} akan menjadi <b>Available</b> dan Unit ${namaBaru} akan menjadi <b>Sold</b>.</small>`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#2563eb',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Ya, Ganti Unit!',
                        cancelButtonText: 'Batal',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('formGantiUnit').submit();
                        }
                    });
                }
            };
        }

        // Initialize DataTable for list pemesanan
        document.addEventListener("DOMContentLoaded", () => {
            if (document.getElementById("table-daftarPemesanan") && typeof simpleDatatables.DataTable !== 'undefined') {
                new simpleDatatables.DataTable("#table-daftarPemesanan", {
                    searchable: true,
                    sortable: true,
                    perPage: 10,
                });
            }
        });
    </script>
@endsection
