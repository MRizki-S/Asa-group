@extends('layouts.app')

@section('pageActive', $pageActive ?? 'ManagePemesanan')

@section('content')

    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb -->
        <div x-data="{ pageName: '{{ $pageActive ?? 'ManagePemesanan' }}' }">
            @include('partials.breadcrumb')
        </div>

        <div class="mt-4">
            {{-- 🧾 Akun User & Booking Unit --}}
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6 p-6">

                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-6 border-b pb-1">
                    Akun User & Booking Unit
                </h3>

                {{-- Akun user dan tanggal pemesanan --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                    {{-- Akun Customer --}}
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Nama Customer (Akun Closing)
                        </label>
                        <input type="text" readonly
                            value="{{ $pengajuan->customer->nama_lengkap ?? $pengajuan->customer->username ?? '-' }} ({{ $pengajuan->customer->username ?? '-' }}) — {{ $pengajuan->customer->no_hp ?? '-' }}"
                            class="w-full bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700">
                    </div>

                    {{-- Tanggal Pemesanan --}}
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Tanggal Pemesanan
                        </label>
                        <input type="text" readonly
                            value="{{ $pengajuan->tanggal_pemesanan ? \Carbon\Carbon::parse($pengajuan->tanggal_pemesanan)->format('d M Y') : '-' }}"
                            class="w-full bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700">
                    </div>
                </div>

                {{-- Blok perumahaan, tahap, unit --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                    {{-- Perumahaan --}}
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Perumahaan</label>
                        <input type="text" readonly value="{{ $pengajuan->perumahaan->nama_perumahaan ?? '-' }}"
                            class="w-full bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700">
                    </div>

                    {{-- Tahap --}}
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Tahap</label>
                        <input type="text" readonly value="{{ $pengajuan->tahap->nama_tahap ?? '-' }}"
                            class="w-full bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700">
                    </div>

                    {{-- Unit --}}
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Unit</label>
                        <input type="text" readonly value="{{ $pengajuan->unit->nama_unit ?? '-' }}"
                            class="w-full bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700">
                    </div>
                </div>
            </div>

            {{-- 🤝 Agent Referral (Jika ada) --}}
            @if ($pengajuan->source === 'agent' || $pengajuan->agent_id)
                <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-6 border-b pb-1">
                        Informasi Agen Referral
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Nama Agen
                            </label>
                            <input type="text" readonly value="{{ $pengajuan->agent->nama_agent ?? '-' }}"
                                class="w-full bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700">
                        </div>

                        @if ($pengajuan->feeAgent && $pengajuan->feeAgent->isNotEmpty())
                            @foreach ($pengajuan->feeAgent as $fee)
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Fee Agen
                                    </label>
                                    <input type="text" readonly
                                        value="{{ $fee->masterAgentFee->judul_fee ?? 'Fee' }} — (Rp {{ number_format($fee->nominal_snapshot, 0, ',', '.') }})"
                                        class="w-full bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700">
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            @endif

            {{-- 🧍‍♂️ Data Diri User --}}
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
                <div class="px-5 py-4 sm:px-6 sm:py-5">
                    <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">
                        <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                            Data Diri User (Pembeli)
                        </h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        {{-- Nama User --}}
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Nama Lengkap
                            </label>
                            <input type="text" readonly value="{{ $pengajuan->dataDiri->nama_pribadi ?? ($pengajuan->customer->nama_lengkap ?? '-') }}"
                                class="form-control w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-700 dark:text-white dark:border-gray-600">
                        </div>

                        {{-- Nomor HP --}}
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Nomor HP
                            </label>
                            <input type="text" readonly value="{{ $pengajuan->dataDiri->no_hp ?? ($pengajuan->customer->no_hp ?? '-') }}"
                                class="form-control w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-700 dark:text-white dark:border-gray-600">
                        </div>

                        <!-- Nomor KTP -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                No KTP
                            </label>
                            <input type="text" readonly value="{{ $pengajuan->dataDiri->no_ktp ?? '-' }}"
                                class="form-control w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-700 dark:text-white dark:border-gray-600">
                        </div>

                        <!-- Pekerjaan -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Pekerjaan
                            </label>
                            <input type="text" readonly value="{{ $pengajuan->dataDiri->pekerjaan ?? '-' }}"
                                class="form-control w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-700 dark:text-white dark:border-gray-600">
                        </div>

                        {{-- Provinsi --}}
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Provinsi
                            </label>
                            <input type="text" readonly value="{{ $pengajuan->dataDiri->provinsi_nama ?? '-' }}"
                                class="form-control w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-700 dark:text-white dark:border-gray-600">
                        </div>

                        {{-- Kota --}}
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Kota / Kabupaten
                            </label>
                            <input type="text" readonly value="{{ $pengajuan->dataDiri->kota_nama ?? '-' }}"
                                class="form-control w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-700 dark:text-white dark:border-gray-600">
                        </div>

                        {{-- Kecamatan --}}
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Kecamatan
                            </label>
                            <input type="text" readonly value="{{ $pengajuan->dataDiri->kecamatan_nama ?? '-' }}"
                                class="form-control w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-700 dark:text-white dark:border-gray-600">
                        </div>

                        {{-- Desa --}}
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Desa / Kelurahan
                            </label>
                            <input type="text" readonly value="{{ $pengajuan->dataDiri->desa_nama ?? '-' }}"
                                class="form-control w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-700 dark:text-white dark:border-gray-600">
                        </div>

                        {{-- RT --}}
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">RT</label>
                            <input type="text" readonly value="{{ $pengajuan->dataDiri->rt ?? '-' }}"
                                class="form-control w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-700 dark:text-white dark:border-gray-600">
                        </div>

                        {{-- RW --}}
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">RW</label>
                            <input type="text" readonly value="{{ $pengajuan->dataDiri->rw ?? '-' }}"
                                class="form-control w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-700 dark:text-white dark:border-gray-600">
                        </div>

                        {{-- Jalan / Dusun --}}
                        <div class="md:col-span-2">
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Jalan / Dusun
                            </label>
                            <textarea readonly rows="2"
                                class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-700 dark:text-white dark:border-gray-600">{{ $pengajuan->dataDiri->alamat_detail ?? '-' }}</textarea>
                        </div>

                    </div>
                </div>
            </div>

            {{-- sistem pembayaran --}}
            <div x-data="{
                caraBayar: '{{ $pengajuan->cara_bayar }}',
            }"
                class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">

                <!-- 🔘 Pilihan Cara Bayar -->
                <div class="px-5 py-4 sm:px-6 sm:py-5">
                    <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                            Sistem Pembayaran
                        </h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Select Cara Bayar -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Cara Bayar
                            </label>
                            <input type="text" readonly value="{{ strtoupper($pengajuan->cara_bayar) }}"
                                class="w-full bg-gray-100 border text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed
                        dark:bg-gray-700 dark:text-gray-300 border-gray-300">
                        </div>
                    </div>
                </div>

                <!-- 💵 FORM CASH -->
                @if ($pengajuan->cara_bayar === 'cash')
                    <div class="px-5 py-4 sm:px-6 sm:py-5 border-t border-gray-100 dark:border-gray-800">

                        <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">
                            <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Rincian Pembayaran CASH</h3>
                            <span
                                class="inline-flex items-center px-3 py-1 text-sm font-semibold text-yellow-800 bg-yellow-100 rounded-full border border-yellow-300 dark:bg-yellow-900/30 dark:text-yellow-300">
                                CASH
                            </span>
                        </div>

                        <div class="space-y-4">
                            <!-- Harga Rumah -->
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Harga Rumah
                                </label>
                                <input type="text" readonly
                                    value="Rp {{ number_format($pengajuan->cash->harga_rumah ?? 0, 0, ',', '.') }}"
                                    class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed
                            dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600">
                            </div>

                            <!-- Kelebihan Tanah -->
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                    Kelebihan Tanah
                                </label>
                                <div class="grid grid-cols-2 gap-4">
                                    <input type="text" readonly
                                        class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed
                                dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600"
                                        value="{{ $pengajuan->cash->luas_kelebihan ?? '-' }} m²">

                                    <input type="text" readonly
                                        class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed
                                dark:bg-gray-700 dark:text-gray-400 dark:border-gray-600"
                                        value="Rp {{ number_format($pengajuan->cash->nominal_kelebihan ?? 0, 0, ',', '.') }}">
                                </div>
                            </div>

                            <!-- Harga Jadi -->
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                    Harga Jadi
                                </label>
                                <input type="text" readonly
                                    value="Rp {{ number_format(($pengajuan->cash->harga_rumah ?? 0) + ($pengajuan->cash->nominal_kelebihan ?? 0), 0, ',', '.') }}"
                                    class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm font-semibold rounded-lg p-2.5 cursor-not-allowed
                            dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600">
                            </div>
                        </div>
                    </div>
                @endif

                <!-- 🏦 FORM KPR -->
                @if ($pengajuan->cara_bayar === 'kpr')
                    <div class="px-5 py-4 sm:px-6 sm:py-5 border-t border-gray-100 dark:border-gray-800">
                        <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">
                            <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Rincian Pembayaran KPR</h3>
                            <span
                                class="inline-flex items-center px-3 py-1 text-sm font-semibold text-blue-800 bg-blue-100 rounded-full border border-blue-300 dark:bg-blue-900/30 dark:text-blue-300">
                                KPR
                            </span>
                        </div>

                        <div class="space-y-5">
                            <!-- DP Rumah Induk -->
                            <div>
                                <label class="block mt-4 mb-1 text-sm font-medium text-gray-900 dark:text-white">
                                    DP Rumah Induk
                                </label>
                                <input type="text" readonly
                                    value="Rp {{ number_format($pengajuan->kpr->dp_rumah_induk ?? 0, 0, ',', '.') }}"
                                    class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed
                            dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600">
                            </div>

                            <!-- Kelebihan Tanah -->
                            <div class="grid grid-cols-2 gap-4 items-end mt-4">
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                        Luas Kelebihan Tanah (m²)
                                    </label>
                                    <input type="text" readonly
                                        class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed
                                dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600"
                                        value="{{ $pengajuan->kpr->luas_kelebihan ?? '-' }} m²">
                                </div>
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                        Nominal Kelebihan (Rp)
                                    </label>
                                    <input type="text" readonly
                                        value="Rp {{ number_format($pengajuan->kpr->nominal_kelebihan ?? 0, 0, ',', '.') }}"
                                        class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed
                                dark:bg-gray-700 dark:text-gray-400 dark:border-gray-600">
                                </div>
                            </div>

                            <!-- Total DP -->
                            <div>
                                <label class="block mb-1 text-sm font-semibold text-gray-900 dark:text-white">
                                    Total DP
                                </label>
                                <input type="text" readonly
                                    value="Rp {{ number_format(($pengajuan->kpr->dp_rumah_induk ?? 0) + ($pengajuan->kpr->nominal_kelebihan ?? 0), 0, ',', '.') }}"
                                    class="w-full bg-green-50 border border-green-300 text-green-700 text-sm font-semibold rounded-lg p-2.5 cursor-not-allowed
                            dark:bg-green-900/30 dark:border-green-700">
                            </div>

                            <!-- DP Dibayarkan Pembeli -->
                            <div>
                                <label class="block mb-1 text-sm font-semibold text-gray-900 dark:text-white">
                                    DP Dibayarkan Pembeli
                                </label>
                                @php
                                    $sbum = 4000000;
                                    $totDp = ($pengajuan->kpr->dp_rumah_induk ?? 0) + ($pengajuan->kpr->nominal_kelebihan ?? 0);
                                    $dpBeli = max(0, $totDp - $sbum);
                                @endphp
                                <input type="text" readonly
                                    value="Rp {{ number_format($dpBeli, 0, ',', '.') }}"
                                    class="w-full bg-gray-100 border border-gray-300 text-gray-600 text-sm rounded-lg p-2.5 cursor-not-allowed
                            dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700">
                            </div>

                            <!-- Harga Total & Nilai KPR -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <div>
                                    <label class="block mb-1 text-sm font-semibold text-gray-900 dark:text-white">
                                        Harga Total Rumah
                                    </label>
                                    <input type="text" readonly
                                        value="Rp {{ number_format($pengajuan->kpr->harga_total ?? 0, 0, ',', '.') }}"
                                        class="w-full bg-indigo-50 border border-indigo-300 text-indigo-700 text-sm font-semibold rounded-lg p-2.5 cursor-not-allowed
                                dark:bg-indigo-900/30 dark:border-indigo-700">
                                </div>
                                <div>
                                    <label class="block mb-1 text-sm font-semibold text-gray-900 dark:text-white">
                                        Nilai KPR
                                    </label>
                                    @php
                                        $hargaTotalKpr = $pengajuan->kpr->harga_total ?? 0;
                                        $nilaiKpr = max(0, $hargaTotalKpr - $totDp);
                                    @endphp
                                    <input type="text" readonly
                                        value="Rp {{ number_format($nilaiKpr, 0, ',', '.') }}"
                                        class="w-full bg-blue-50 border border-blue-300 text-blue-700 text-sm font-semibold rounded-lg p-2.5 cursor-not-allowed
                                dark:bg-blue-900/30 dark:border-blue-700">
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- 🏦 Status & Informasi Berkas KPR (Hanya jika cara bayar KPR) --}}
            @if ($pengajuan->cara_bayar === 'kpr')
                <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
                    <div class="px-5 py-4 sm:px-6 sm:py-5">
                        <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">
                            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90 flex items-center gap-2">
                                <span>Status & Progres Berkas KPR</span>
                            </h3>
                            <span class="inline-flex items-center px-3 py-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded-full border border-blue-300 dark:bg-blue-900/30 dark:text-blue-300">
                                Bank & Progres KPR
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                            {{-- Bank KPR --}}
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Bank KPR
                                </label>
                                <input type="text" readonly value="{{ $pengajuan->kpr->bank->nama_bank ?? '-' }}"
                                    class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-700 dark:text-white dark:border-gray-600 font-medium">
                            </div>

                            {{-- Status KPR --}}
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Status KPR
                                </label>
                                @php
                                    $statusKpr = strtolower($pengajuan->kpr->status_kpr ?? '');
                                    $statusBadgeClass = match($statusKpr) {
                                        'acc' => 'bg-emerald-50 text-emerald-700 border-emerald-300 dark:bg-emerald-950/30 dark:text-emerald-400 dark:border-emerald-700',
                                        'realisasi' => 'bg-blue-50 text-blue-700 border-blue-300 dark:bg-blue-950/30 dark:text-blue-400 dark:border-blue-700',
                                        'tolak', 'ditolak' => 'bg-red-50 text-red-700 border-red-300 dark:bg-red-950/30 dark:text-red-400 dark:border-red-700',
                                        'proses', 'masuk berkas' => 'bg-amber-50 text-amber-700 border-amber-300 dark:bg-amber-950/30 dark:text-amber-400 dark:border-amber-700',
                                        default => 'bg-gray-50 text-gray-700 border-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600',
                                    };
                                @endphp
                                <input type="text" readonly value="{{ $pengajuan->kpr->status_kpr ? strtoupper($pengajuan->kpr->status_kpr) : '-' }}"
                                    class="w-full {{ $statusBadgeClass }} border text-sm font-semibold rounded-lg p-2.5 cursor-not-allowed uppercase">
                            </div>

                            {{-- Tanggal Masuk Berkas --}}
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Tanggal Masuk Berkas
                                </label>
                                <input type="text" readonly
                                    value="{{ $pengajuan->kpr->tanggal_masuk_berkas ? \Carbon\Carbon::parse($pengajuan->kpr->tanggal_masuk_berkas)->format('d M Y') : '-' }}"
                                    class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-700 dark:text-white dark:border-gray-600">
                            </div>

                            {{-- Tanggal ACC KPR --}}
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Tanggal ACC KPR
                                </label>
                                <input type="text" readonly
                                    value="{{ $pengajuan->kpr->tanggal_acc ? \Carbon\Carbon::parse($pengajuan->kpr->tanggal_acc)->format('d M Y') : '-' }}"
                                    class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-700 dark:text-white dark:border-gray-600">
                            </div>

                            {{-- Tanggal Realisasi --}}
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Tanggal Realisasi
                                </label>
                                <input type="text" readonly
                                    value="{{ $pengajuan->kpr->tanggal_realisasi ? \Carbon\Carbon::parse($pengajuan->kpr->tanggal_realisasi)->format('d M Y') : '-' }}"
                                    class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 cursor-not-allowed dark:bg-gray-700 dark:text-white dark:border-gray-600">
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- 💸 Bonus Cash (muncul kalau cash dipilih) -->
            @if ($pengajuan->cara_bayar === 'cash' && $pengajuan->bonusCash->isNotEmpty())
                <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
                    <div class="px-5 py-4 sm:px-6 sm:py-5 space-y-3 border-t border-gray-100 dark:border-gray-800">
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-2">Bonus Cash</h3>

                        @foreach ($pengajuan->bonusCash as $bonus)
                            <div class="flex gap-2 items-center">
                                <input type="text" readonly value="{{ $bonus->nama_bonus ?? '-' }}"
                                    class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5
                    dark:bg-gray-800 dark:text-white dark:border-gray-600 cursor-not-allowed">
                            </div>
                        @endforeach
                    </div>
                </div>
            @elseif($pengajuan->cara_bayar === 'kpr' && $pengajuan->bonusKpr->isNotEmpty())
                <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
                    <div class="px-5 py-4 sm:px-6 sm:py-5 space-y-3 border-t border-gray-100 dark:border-gray-800">
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90 mb-2">Bonus KPR</h3>

                        @foreach ($pengajuan->bonusKpr as $bonus)
                            <div class="flex gap-2 items-center">
                                <input type="text" readonly value="{{ $bonus->nama_bonus ?? '-' }}"
                                    class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5
                    dark:bg-gray-800 dark:text-white dark:border-gray-600 cursor-not-allowed">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- pemesanan unit cicilan --}}
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
                <div class="px-5 py-4 sm:px-6 sm:py-5">
                    <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                            Cara Pembayaran
                        </h3>
                    </div>

                    <!-- Bagian Isi -->
                    <div class="space-y-5">
                        <!-- Berapa Kali Angsur -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Berapa Kali Angsur
                            </label>
                            <input type="text" readonly value="{{ $pengajuan->caraBayar->jumlah_cicilan ?? '-' }}"
                                class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5
                    dark:bg-gray-800 dark:text-white dark:border-gray-600 cursor-not-allowed">
                        </div>

                        <!-- Minimal DP -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Minimal DP
                            </label>
                            <input type="text" readonly
                                value="{{ isset($pengajuan->caraBayar->minimal_dp) ? 'Rp ' . number_format($pengajuan->caraBayar->minimal_dp, 0, ',', '.') : '-' }}"
                                class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5
                    dark:bg-gray-800 dark:text-white dark:border-gray-600 cursor-not-allowed">
                        </div>

                        <!-- Daftar Angsuran -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Angsuran
                            </label>

                            @if ($pengajuan->cicilan && $pengajuan->cicilan->count() > 0)
                                @foreach ($pengajuan->cicilan as $index => $cicilan)
                                    <div
                                        class="flex flex-col md:flex-row md:items-center gap-3 pb-4 mb-2 border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800/40 rounded-lg p-3 transition-all">

                                        <!-- Pembayaran Ke -->
                                        <div class="w-full md:w-1/4">
                                            <input type="text" readonly
                                                value="{{ 'Pembayaran ke - ' . $cicilan->pembayaran_ke }}"
                                                class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5
                                    dark:bg-gray-800 dark:text-white dark:border-gray-600 cursor-not-allowed">
                                        </div>

                                        <!-- Tanggal Jatuh Tempo -->
                                        <div class="w-full md:w-1/3">
                                            <input type="text" readonly
                                                value="{{ $cicilan->tanggal_jatuh_tempo ? \Carbon\Carbon::parse($cicilan->tanggal_jatuh_tempo)->format('d M Y') : '-' }}"
                                                class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5
                                    dark:bg-gray-800 dark:text-white dark:border-gray-600 cursor-not-allowed">
                                        </div>

                                        <!-- Nominal -->
                                        <div class="w-full md:w-1/3">
                                            <input type="text" readonly
                                                value="Rp {{ number_format($cicilan->nominal, 0, ',', '.') }}"
                                                class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5
                                    dark:bg-gray-800 dark:text-white dark:border-gray-600 cursor-not-allowed">
                                        </div>

                                    </div>
                                @endforeach
                            @else
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    Tidak ada data cicilan.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tombol Kembali Bawah -->
            <div class="flex justify-end mt-6 mb-8">
                <a href="{{ $backUrl ?? route('marketing.managePemesanan.index') }}"
                    class="inline-flex items-center gap-1.5 px-6 py-2.5 text-sm font-semibold text-gray-700 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 rounded-lg shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </a>
            </div>

        </div>
    </div>

@endsection
