@extends('layouts.app')

@section('pageActive', 'PemesananUnit')

@section('content')

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">
    <style>
        .flatpickr-calendar {
            z-index: 999999 !important;
        }
    </style>

    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb -->
        <div x-data="{ pageName: 'PemesananUnit' }">
            @include('partials.breadcrumb')
        </div>

        <!-- Alert Error Validasi -->
        @if ($errors->any())
            <div class="flex p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400"
                role="alert">
                <svg class="shrink-0 inline w-4 h-4 me-3 mt-[2px]" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
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

        <!-- Informasi Promo & Ketentuan -->
        <div x-data="{ openInfo: false }" class="mt-6 mb-6 rounded-xl border border-gray-200 dark:border-gray-700
               bg-white dark:bg-gray-800/50 shadow-sm text-gray-800 dark:text-gray-100 overflow-hidden">

            <!-- Header -->
            <button @click="openInfo = !openInfo" class="w-full flex justify-between items-center px-5 py-3 bg-gray-50 dark:bg-gray-800/70
                   hover:bg-gray-100 dark:hover:bg-gray-700 transition font-semibold text-sm">
                <span>📄 Informasi Terkait PPJB</span>
                <svg :class="{ 'rotate-180': openInfo }" xmlns="http://www.w3.org/2000/svg"
                    class="w-5 h-5 transform transition-transform duration-200" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div x-show="openInfo" x-transition x-cloak class="px-5 py-5">

                <!-- Ketentuan Keterlambatan & Pembatalan -->
                <div class="space-y-2 text-sm leading-snug">
                    @if ($keterlambatan)
                        <div class="flex items-start gap-2">
                            <span class="text-gray-500 text-lg">⚠️</span>
                            <p>
                                <span class="font-semibold">Keterlambatan:</span>
                                Pembayaran yang melewati tanggal jatuh tempo akan dikenakan denda sebesar
                                <span class="font-bold text-red-600 bg-red-50 dark:bg-red-900/30 px-1 rounded">
                                    {{ rtrim(rtrim(number_format($keterlambatan->persentase_denda, 2, ',', '.'), '0'), ',') }}%
                                </span>
                                per bulan dari nilai angsuran.
                            </p>
                        </div>
                    @endif

                    @if ($pembatalan)
                        <div class="flex items-start gap-2">
                            <span class="text-gray-500 text-lg">❗</span>
                            <p>
                                <span class="font-semibold">Pembatalan:</span>
                                Jika pemesanan dibatalkan, akan dikenakan potongan sebesar
                                <span class="font-bold text-yellow-600 bg-yellow-50 dark:bg-yellow-900/30 px-1 rounded">
                                    {{ rtrim(rtrim(number_format($pembatalan->persentase_potongan, 2, ',', '.'), '0'), ',') }}%
                                </span>
                                dari total dana yang telah masuk.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <form action="{{ route('marketing.pemesananUnit.store') }}" method="POST" x-data="bookingForm()" @submit="handleSubmit($event)">
            @csrf

            {{-- Sub 1: Akun User & Booking Unit --}}
            @include('marketing.pemesanan-unit.partials.akunUser-Booking')

            {{-- Sub 1.5: Pilih Fee Agen (Hanya SPV Pembiayaan KPR) --}}
            @if ($isSPVKPR)
                <div x-show="hasSelected && selectedCustomer.booking.source === 'agent'" x-transition.opacity
                    class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
                    <div class="px-5 py-4 sm:px-6 sm:py-5">
                        <h3
                            class="text-base font-medium text-gray-800 dark:text-white/90 mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">
                            Fee Agent Referral
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pilih Skema Fee
                                    Agen</label>
                                <select name="master_agent_fee_id"
                                    class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                                    <option value="">-- Pilih Skema Fee --</option>
                                    @foreach ($feeAgens as $fee)
                                        <option value="{{ $fee->id }}">
                                            {{ $fee->judul_fee }} - (Rp {{ number_format($fee->nominal, 0, ',', '.') }})
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gray-500 italic">* Pilih skema fee yang akan diberikan kepada agen
                                    untuk pemesanan ini.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Data Diri Users --}}
            @include('marketing.pemesanan-unit.partials.data-diri')

            <div>
                {{-- Sistem Pembayaran --}}
                @include('marketing.pemesanan-unit.partials.sistem-pembayaran')

                {{-- Cara Pembayaran --}}
                @include('marketing.pemesanan-unit.partials.cara-pembayaraan')
            </div>

            <!-- Pesan kosong sebelum memilih akun user - bookign maka cara bayar tidak akan tampil -->
            <div x-show="!hasSelected" x-transition.opacity
                class="rounded-2xl border border-dashed border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/30 py-10 text-center text-gray-600 dark:text-gray-300 mb-6">
                <svg class="w-8 h-8 mx-auto mb-3 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p>Silakan pilih akun customer terlebih dahulu untuk menampilkan cara pembayaran.</p>
            </div>

            <!-- Tombol Aksi -->
            @can('marketing.pemesanan-unit.create')
                <div class="flex justify-end gap-2">
                    {{-- <button type="button" onclick="history.back()" class="px-8 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300
                        dark:text-white dark:bg-gray-700 dark:hover:bg-gray-600">
                        Kembali
                    </button> --}}
                    <button type="submit" class="px-8 py-2.5 text-sm font-medium text-white rounded-lg bg-blue-600 hover:bg-blue-700
                        focus:outline-none focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800">
                        Simpan
                    </button>
                </div>
            @endcan
        </form>
    </div>

    {{-- js --}}
    <script>
        document.addEventListener('alpine:init', () => {

            Alpine.data('bookingForm', () => ({
                // === DATA CUSTOMER ===
                customers: @json($customersData),
                selectedCustomerId: '',
                selectedCustomer: null,
                hasSelected: false,

                // CARA BAYAR
                caraBayar: '',
                caraBayarCash: [],
                caraBayarKpr: [],

                // ===================================
                // 💵 CASH STATE
                // ===================================
                cashHargaRumah: '',
                cashHargaRumahNumber: 0,
                cashLuasKelebihan: '',
                cashNominalKelebihan: '',
                cashNominalKelebihanNumber: 0,
                cashTambahanList: [],

                get cashTotalTambahanNumber() {
                    return this.cashTambahanList.reduce((acc, curr) => acc + (parseInt(curr.nominal) || 0), 0);
                },
                get cashTotalTambahan() {
                    return this.formatNumber(this.cashTotalTambahanNumber);
                },
                get cashHargaJadiNumber() {
                    return this.cashHargaRumahNumber + this.cashNominalKelebihanNumber + this.cashTotalTambahanNumber;
                },
                get cashHargaJadi() {
                    return this.formatNumber(this.cashHargaJadiNumber);
                },

                // Cicilan Cash
                selectedCash: null,
                cashJumlahCicilan: '',
                cashMinimalDp: '',
                cashAngsuranList: [],

                // ===================================
                // 🏦 KPR STATE
                // ===================================
                kprHargaTotal: '',
                kprHargaTotalNumber: 0,
                kprSbumPemerintah: 4000000,
                kprDpRumahInduk: '',
                kprDpRumahIndukNumber: 0,

                get kprNilaiKprNumber() {
                    const val = this.kprHargaTotalNumber - this.kprSbumPemerintah - this.kprDpRumahIndukNumber;
                    return val > 0 ? val : 0;
                },
                get kprNilaiKpr() {
                    return 'Rp ' + this.formatNumber(this.kprNilaiKprNumber);
                },

                // Cicilan DP KPR
                selectedKpr: null,
                jumlahCicilan: '',
                minimalDp: '',
                angsuranList: [],

                // Biaya Administrasi KPR
                adaBiayaAdmin: false,
                biayaAdminList: [],
                get totalBiayaAdminNumber() {
                    if (!this.adaBiayaAdmin) return 0;
                    return this.biayaAdminList.reduce((acc, curr) => acc + (parseInt(curr.nominal) || 0), 0);
                },
                get totalBiayaAdmin() {
                    return this.formatNumber(this.totalBiayaAdminNumber);
                },

                // Cicilan Biaya Administrasi
                adminJumlahCicilan: 1,
                adminAngsuranList: [],

                // ===================================
                // 🎁 BONUS
                // ===================================
                bonusCashList: [{ nama_bonus: '' }],
                bonusCashOptions: @js(
                    $bonusCash?->items->map(fn($i) => [
                        'nama_bonus' => $i->nama_bonus,
                        'nominal' => $i->nominal_bonus,
                    ]) ?? []
                ),

                bonusKprList: [{ nama_bonus: '' }],
                bonusKprOptions: @js(
                    $bonusKpr?->items->map(fn($i) => [
                        'nama_bonus' => $i->nama_bonus,
                        'nominal' => $i->nominal_bonus,
                    ]) ?? []
                ),

                // ===================================
                // 📱 MOBILE-FIRST FASILITAS KPR
                // ===================================
                activePromoTab: 'dp',
                promoMatrix: {
                    @if ($promoKpr && $promoKpr->items->count() > 0)
                        @foreach ($promoKpr->items as $item)
                            @php
                                $n = strtolower(trim($item->nama_promo));
                                
                                // 1. Masuk DP (Biru)
                                $isDp = ($n === 'admin') || str_contains($n, 'listrik') || str_contains($n, 'pbg') || str_contains($n, 'bphtb') || str_contains($n, 'bpthb');
                                
                                // 2. Masuk Biaya Admin (Kuning)
                                $isAdmin = str_contains($n, 'akte') || str_contains($n, 'akta') || str_contains($n, 'ajb') || str_contains($n, 'balik nama') || str_contains($n, 'realisasi') || str_contains($n, 'asuransi') || str_contains($n, 'pdam');
                                
                                // 3. Status Awal (Bisa DP, Admin, atau Netral/none)
                                $statusAwal = 'none';
                                if ($isDp) {
                                    $statusAwal = 'dp';
                                } elseif ($isAdmin) {
                                    $statusAwal = 'admin';
                                }
                            @endphp
                            '{{ addslashes($item->nama_promo) }}': '{{ $statusAwal }}',
                        @endforeach
                    @endif
                },
                countPromo(target) {
                    return Object.values(this.promoMatrix).filter(v => v === target).length;
                },
                getPromoChipClass(name) {
                    const status = this.promoMatrix[name];
                    if (status === 'dp') {
                        if (this.activePromoTab === 'dp') {
                            return 'bg-blue-100/90 text-blue-800 border-blue-300 dark:bg-blue-900/40 dark:text-blue-200 dark:border-blue-700 font-semibold ring-1 ring-blue-300 shadow-xs';
                        }
                        return 'bg-blue-50/70 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800 font-medium';
                    }
                    if (status === 'admin') {
                        if (this.activePromoTab === 'admin') {
                            return 'bg-amber-100/90 text-amber-900 border-amber-300 dark:bg-amber-900/40 dark:text-amber-200 dark:border-amber-700 font-semibold ring-1 ring-amber-300 shadow-xs';
                        }
                        return 'bg-amber-50/70 text-amber-800 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800 font-medium';
                    }
                    return 'bg-white text-gray-400 border-gray-200 hover:border-gray-300 hover:text-gray-600 dark:bg-gray-800 dark:text-gray-500 dark:border-gray-700';
                },
                togglePromoChip(name) {
                    const current = this.promoMatrix[name];
                    const target = this.activePromoTab;
                    if (target === 'admin' && !this.adaBiayaAdmin) return;

                    if (current === target) {
                        this.promoMatrix[name] = 'none';
                    } else {
                        this.promoMatrix[name] = target;
                    }
                },
                setAllTabPromo(action) {
                    const target = this.activePromoTab;
                    if (target === 'admin' && !this.adaBiayaAdmin) return;

                    Object.keys(this.promoMatrix).forEach(k => {
                        if (action === 'select_all') {
                            this.promoMatrix[k] = target;
                        } else if (action === 'reset') {
                            if (this.promoMatrix[k] === target) {
                                this.promoMatrix[k] = 'none';
                            }
                        }
                    });
                },

                isLoading: false,

                // === LIFECYCLE ===
                init() {
                    this.initSelect2();
                    this.$watch('caraBayar', (val) => this.onCaraBayarChange(val));
                    this.$watch('adaBiayaAdmin', (val) => {
                        if (val && this.biayaAdminList.length === 0) {
                            this.addBiayaAdmin();
                            this.generateAngsuranAdmin(this.adminJumlahCicilan);
                        }
                        if (!val && this.activePromoTab === 'admin') {
                            this.activePromoTab = 'dp';
                        }
                    });
                },

                // === METHODS CUSTOMER ===
                setCustomer(id) {
                    this.selectedCustomerId = id;
                    this.selectedCustomer = this.customers.find(c => c.id == id) || null;
                    this.hasSelected = !!this.selectedCustomer;

                    if (this.hasSelected && this.selectedCustomer.booking) {
                        const finalVal = parseInt((this.selectedCustomer.booking.harga_final || 0).toString().split('.')[0]) || 0;
                        const kelebihanVal = parseInt((this.selectedCustomer.booking.nominal_kelebihan || 0).toString().split('.')[0]) || 0;

                        // set data KPR
                        this.kprHargaTotalNumber = finalVal;
                        this.kprHargaTotal = this.formatNumber(finalVal);
                        this.kprDpRumahIndukNumber = 0;
                        this.kprDpRumahInduk = '';

                        // set data Cash
                        this.cashHargaRumahNumber = finalVal;
                        this.cashHargaRumah = this.formatNumber(finalVal);
                        this.cashLuasKelebihan = this.selectedCustomer.booking.luas_kelebihan ?? '';
                        this.cashNominalKelebihanNumber = kelebihanVal;
                        this.cashNominalKelebihan = kelebihanVal > 0 ? this.formatNumber(kelebihanVal) : '';

                        if (this.selectedCustomer.booking.perumahaan_id) {
                            this.fetchSettingPpjb(this.selectedCustomer.booking.perumahaan_id);
                        }
                    } else {
                        this._resetAngsuranState();
                    }
                },

                async fetchSettingPpjb(perumahaanId) {
                    this.isLoading = true;
                    this._resetAngsuranState({ keepCaraBayar: true });

                    try {
                        const res = await fetch(`api/setting-cara-bayar/${perumahaanId}`);
                        const data = await res.json();

                        this.caraBayarCash = Array.isArray(data?.data?.cash) ? data.data.cash : [];
                        this.caraBayarKpr = Array.isArray(data?.data?.kpr) ? data.data.kpr : [];
                        this.selectedKpr = null;
                        this.selectedCash = null;
                    } catch (e) {
                        console.error('fetchSettingPpjb error', e);
                    } finally {
                        this.isLoading = false;
                    }
                },

                onCaraBayarChange(val) {
                    this._resetAngsuranState({ keepCaraBayar: true });
                },

                // === METHODS CASH ===
                updateCashHargaRumah(e) {
                    let raw = e.target.value.replace(/\D/g, '');
                    this.cashHargaRumahNumber = parseInt(raw || 0);
                    this.cashHargaRumah = this.formatNumber(raw);
                    if (this.selectedCash) {
                        this.generateAngsuranCash(this.cashJumlahCicilan);
                    }
                },
                updateCashNominalKelebihan(e) {
                    let raw = e.target.value.replace(/\D/g, '');
                    this.cashNominalKelebihanNumber = parseInt(raw || 0);
                    this.cashNominalKelebihan = this.formatNumber(raw);
                    if (this.selectedCash) {
                        this.generateAngsuranCash(this.cashJumlahCicilan);
                    }
                },
                addCashTambahan() {
                    this.cashTambahanList.push({ keterangan: '', nominal: 0, nominalFormatted: '' });
                },
                removeCashTambahan(idx) {
                    this.cashTambahanList.splice(idx, 1);
                    if (this.selectedCash) {
                        this.generateAngsuranCash(this.cashJumlahCicilan);
                    }
                },
                formatNominalCashTambahan(idx) {
                    let v = (this.cashTambahanList[idx]?.nominalFormatted || '').toString().replace(/\D/g, '');
                    this.cashTambahanList[idx].nominal = parseInt(v || 0);
                    this.cashTambahanList[idx].nominalFormatted = this.formatNumber(v);
                    if (this.selectedCash) {
                        this.generateAngsuranCash(this.cashJumlahCicilan);
                    }
                },
                pilihCash(cashItem) {
                    this.selectedCash = cashItem;
                    const jumlah = parseInt(cashItem.jumlah_cicilan) || 1;
                    this.cashJumlahCicilan = jumlah;
                    this.cashMinimalDp = parseInt(cashItem.minimal_dp) || 0;
                    this.generateAngsuranCash(jumlah);
                },
                generateAngsuranCash(jumlah) {
                    const today = new Date();
                    this.cashAngsuranList = [];
                    const total = this.cashHargaJadiNumber;
                    const minDp = this.cashMinimalDp || 0;

                    let sisa = total;
                    let firstNominal = minDp > 0 ? minDp : (jumlah > 0 ? Math.floor(total / jumlah) : 0);
                    if (firstNominal > total) firstNominal = total;
                    sisa -= firstNominal;

                    const sisaPerTermin = (jumlah > 1) ? Math.floor(sisa / (jumlah - 1)) : 0;
                    let sisaBerjalan = sisa;

                    for (let i = 0; i < jumlah; i++) {
                        const tanggal = new Date(today);
                        tanggal.setMonth(today.getMonth() + i);
                        const formatted = tanggal.toISOString().split('T')[0];
                        const isLast = i === jumlah - 1;

                        let nom = 0;
                        if (i === 0) {
                            nom = (jumlah === 1) ? total : firstNominal;
                        } else if (isLast) {
                            nom = sisaBerjalan;
                        } else {
                            nom = sisaPerTermin;
                            sisaBerjalan -= nom;
                        }

                        const ket = jumlah === 1 
                            ? 'Pelunasan Cash Keras' 
                            : (isLast ? `Pembayaran ke - ${i + 1} / Pelunasan` : `Pembayaran ke - ${i + 1}`);

                        this.cashAngsuranList.push({
                            pembayaran_ke: i + 1,
                            tanggal: formatted,
                            nominal: nom > 0 ? nom : '',
                            nominalFormatted: nom > 0 ? this.formatNumber(nom) : '',
                            keterangan: ket,
                        });
                    }
                },
                formatNominalCashAngsuran(idx) {
                    let v = (this.cashAngsuranList[idx]?.nominalFormatted || '').toString().replace(/\D/g, '');
                    this.cashAngsuranList[idx].nominal = parseInt(v || 0);
                    this.cashAngsuranList[idx].nominalFormatted = this.formatNumber(v);
                },

                // === VALIDASI CICILAN CASH ===
                get totalCicilanCashNumber() {
                    return this.cashAngsuranList.reduce((acc, curr) => acc + (parseInt(curr.nominal) || 0), 0);
                },
                get isCashCicilanOver() {
                    return this.cashHargaJadiNumber > 0 && this.totalCicilanCashNumber > this.cashHargaJadiNumber;
                },
                isCashRowOver(idx) {
                    if (!this.cashHargaJadiNumber || this.cashHargaJadiNumber <= 0) return false;
                    let sum = 0;
                    for (let i = 0; i <= idx; i++) {
                        sum += (parseInt(this.cashAngsuranList[i]?.nominal) || 0);
                    }
                    return sum > this.cashHargaJadiNumber;
                },

                // === METHODS KPR ===
                updateKprHargaTotal(e) {
                    let raw = e.target.value.replace(/\D/g, '');
                    this.kprHargaTotalNumber = parseInt(raw || 0);
                    this.kprHargaTotal = this.formatNumber(raw);
                },
                updateKprDpRumahInduk(e) {
                    let raw = e.target.value.replace(/\D/g, '');
                    this.kprDpRumahIndukNumber = parseInt(raw || 0);
                    this.kprDpRumahInduk = this.formatNumber(raw);
                    if (this.selectedKpr) {
                        this.generateAngsuran(parseInt(this.selectedKpr.jumlah_cicilan) || 1);
                    }
                },
                pilihKpr(kprItem) {
                    this.selectedKpr = kprItem;
                    const jumlah = parseInt(kprItem.jumlah_cicilan) || 1;
                    this.jumlahCicilan = jumlah;
                    this.minimalDp = parseInt(kprItem.minimal_dp) || 0;
                    this.generateAngsuran(jumlah);
                },
                generateAngsuran(jumlah) {
                    const today = new Date();
                    this.angsuranList = [];
                    const total = this.kprDpRumahIndukNumber;
                    const perTermin = jumlah > 0 ? Math.floor(total / jumlah) : 0;
                    let sisa = total;

                    for (let i = 0; i < jumlah; i++) {
                        const tanggal = new Date(today);
                        tanggal.setMonth(today.getMonth() + i);
                        const formatted = tanggal.toISOString().split('T')[0];
                        const isLast = i === jumlah - 1;
                        const nom = isLast ? sisa : perTermin;
                        sisa -= nom;

                        const ket = jumlah === 1 
                            ? 'Pelunasan' 
                            : (isLast ? `Pembayaran ke - ${i + 1} / Pelunasan` : `Pembayaran ke - ${i + 1}`);

                        this.angsuranList.push({
                            pembayaran_ke: i + 1,
                            tanggal: formatted,
                            nominal: nom > 0 ? nom : '',
                            nominalFormatted: nom > 0 ? this.formatNumber(nom) : '',
                            keterangan: ket,
                        });
                    }
                },
                formatNominal(idx) {
                    let v = (this.angsuranList[idx]?.nominalFormatted || '').toString().replace(/\D/g, '');
                    this.angsuranList[idx].nominal = parseInt(v || 0);
                    this.angsuranList[idx].nominalFormatted = this.formatNumber(v);
                },

                // === VALIDASI CICILAN DP KPR ===
                get totalCicilanKprDpNumber() {
                    return this.angsuranList.reduce((acc, curr) => acc + (parseInt(curr.nominal) || 0), 0);
                },
                get isKprDpOver() {
                    return this.kprDpRumahIndukNumber > 0 && this.totalCicilanKprDpNumber > this.kprDpRumahIndukNumber;
                },
                isKprDpRowOver(idx) {
                    if (!this.kprDpRumahIndukNumber || this.kprDpRumahIndukNumber <= 0) return false;
                    let sum = 0;
                    for (let i = 0; i <= idx; i++) {
                        sum += (parseInt(this.angsuranList[i]?.nominal) || 0);
                    }
                    return sum > this.kprDpRumahIndukNumber;
                },

                // Biaya Admin Row
                addBiayaAdmin() {
                    this.biayaAdminList.push({ keterangan: '', nominal: 0, nominalFormatted: '' });
                },
                removeBiayaAdmin(idx) {
                    this.biayaAdminList.splice(idx, 1);
                    this.generateAngsuranAdmin(this.adminJumlahCicilan);
                },
                formatNominalAdmin(idx) {
                    let v = (this.biayaAdminList[idx]?.nominalFormatted || '').toString().replace(/\D/g, '');
                    this.biayaAdminList[idx].nominal = parseInt(v || 0);
                    this.biayaAdminList[idx].nominalFormatted = this.formatNumber(v);
                    this.generateAngsuranAdmin(this.adminJumlahCicilan);
                },
                generateAngsuranAdmin(jumlah) {
                    jumlah = parseInt(jumlah) || 1;
                    this.adminJumlahCicilan = jumlah;
                    this.adminAngsuranList = [];
                    const today = new Date();
                    const total = this.totalBiayaAdminNumber;
                    const perTermin = jumlah > 0 ? Math.floor(total / jumlah) : 0;
                    let sisa = total;

                    for (let i = 0; i < jumlah; i++) {
                        const tanggal = new Date(today);
                        tanggal.setMonth(today.getMonth() + i);
                        const formatted = tanggal.toISOString().split('T')[0];
                        const isLast = i === jumlah - 1;
                        const nom = isLast ? sisa : perTermin;
                        sisa -= nom;

                        const ket = jumlah === 1 
                            ? 'Pelunasan' 
                            : (isLast ? `Pembayaran ke - ${i + 1} / Pelunasan` : `Pembayaran ke - ${i + 1}`);

                        this.adminAngsuranList.push({
                            pembayaran_ke: i + 1,
                            tanggal: formatted,
                            nominal: nom > 0 ? nom : 0,
                            nominalFormatted: nom > 0 ? this.formatNumber(nom) : '',
                            keterangan: ket,
                        });
                    }
                },
                formatNominalAdminAngsuran(idx) {
                    let v = (this.adminAngsuranList[idx]?.nominalFormatted || '').toString().replace(/\D/g, '');
                    this.adminAngsuranList[idx].nominal = parseInt(v || 0);
                    this.adminAngsuranList[idx].nominalFormatted = this.formatNumber(v);
                },

                // === VALIDASI CICILAN BIAYA ADMINISTRASI / TAMBAHAN ===
                get totalCicilanAdminNumber() {
                    return this.adminAngsuranList.reduce((acc, curr) => acc + (parseInt(curr.nominal) || 0), 0);
                },
                get isAdminCicilanOver() {
                    return this.totalBiayaAdminNumber > 0 && this.totalCicilanAdminNumber > this.totalBiayaAdminNumber;
                },
                isAdminRowOver(idx) {
                    if (!this.totalBiayaAdminNumber || this.totalBiayaAdminNumber <= 0) return false;
                    let sum = 0;
                    for (let i = 0; i <= idx; i++) {
                        sum += (parseInt(this.adminAngsuranList[i]?.nominal) || 0);
                    }
                    return sum > this.totalBiayaAdminNumber;
                },

                // Form Submit Validation Guard
                handleSubmit(e) {
                    if (this.caraBayar === 'cash') {
                        if (this.isCashCicilanOver) {
                            e.preventDefault();
                            Swal.fire({
                                icon: 'error',
                                title: 'Nominal Cicilan Melebihi Total!',
                                text: `Total cicilan Cash (Rp ${this.formatNumber(this.totalCicilanCashNumber)}) melebihi Harga Jadi Cash (Rp ${this.formatNumber(this.cashHargaJadiNumber)}). Harap periksa kembali baris yang berwarna merah.`,
                            });
                            return false;
                        }
                    } else if (this.caraBayar === 'kpr') {
                        if (this.isKprDpOver) {
                            e.preventDefault();
                            Swal.fire({
                                icon: 'error',
                                title: 'Nominal Cicilan DP Melebihi DP Rumah!',
                                text: `Total cicilan DP (Rp ${this.formatNumber(this.totalCicilanKprDpNumber)}) melebihi DP Rumah (Rp ${this.formatNumber(this.kprDpRumahIndukNumber)}). Harap periksa kembali baris yang berwarna merah.`,
                            });
                            return false;
                        }
                        if (this.adaBiayaAdmin && this.isAdminCicilanOver) {
                            e.preventDefault();
                            Swal.fire({
                                icon: 'error',
                                title: 'Nominal Cicilan Melebihi Biaya Administrasi / Tambahan!',
                                text: `Total cicilan biaya administrasi / tambahan (Rp ${this.formatNumber(this.totalCicilanAdminNumber)}) melebihi Total Biaya Administrasi / Tambahan (Rp ${this.formatNumber(this.totalBiayaAdminNumber)}). Harap periksa kembali baris yang berwarna merah.`,
                            });
                            return false;
                        }
                    }
                },

                // Format Number
                formatNumber(value) {
                    if (value === null || value === undefined || value === '') return '';
                    value = String(value).replace(/\D/g, '');
                    return value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                },

                _resetAngsuranState(opts = {}) {
                    this.angsuranList = [];
                    this.cashAngsuranList = [];
                    this.adminAngsuranList = [];
                    this.biayaAdminList = [];
                    this.adaBiayaAdmin = false;
                    this.cashTambahanList = [];
                    this.jumlahCicilan = '';
                    this.cashJumlahCicilan = '';
                    this.minimalDp = '';
                    this.cashMinimalDp = '';
                    this.selectedCash = null;
                    this.selectedKpr = null;
                    if (!opts.keepCaraBayar) this.caraBayar = '';
                },

                // Bonus helpers
                availableBonusCash(index) {
                    const selected = this.bonusCashList.map(b => b.nama_bonus).filter(Boolean);
                    return this.bonusCashOptions.filter(opt =>
                        !selected.includes(opt.nama_bonus) ||
                        opt.nama_bonus === this.bonusCashList[index].nama_bonus
                    );
                },
                getNominalCash(nama) {
                    const found = this.bonusCashOptions.find(o => o.nama_bonus === nama);
                    return found ? found.nominal : '';
                },
                addBonusCash() {
                    const remaining = this.bonusCashOptions.filter(opt =>
                        !this.bonusCashList.some(b => b.nama_bonus === opt.nama_bonus)
                    );
                    if (remaining.length) this.bonusCashList.push({ nama_bonus: '' });
                },

                availableBonusKpr(index) {
                    const selected = this.bonusKprList.map(b => b.nama_bonus).filter(Boolean);
                    return this.bonusKprOptions.filter(opt =>
                        !selected.includes(opt.nama_bonus) ||
                        opt.nama_bonus === this.bonusKprList[index].nama_bonus
                    );
                },
                getNominalKpr(nama) {
                    const found = this.bonusKprOptions.find(o => o.nama_bonus === nama);
                    return found ? found.nominal : '';
                },
                addBonusKpr() {
                    const remaining = this.bonusKprOptions.filter(opt =>
                        !this.bonusKprList.some(b => b.nama_bonus === opt.nama_bonus)
                    );
                    if (remaining.length) this.bonusKprList.push({ nama_bonus: '' });
                },

                // === SELECT2 ===
                initSelect2() {
                    const self = this;
                    const select = $('#selectUser');
                    if (!select.length) return;

                    select.select2({
                        theme: 'bootstrap4',
                        width: '100%',
                        placeholder: "Cari dan pilih akun customer...",
                        allowClear: true
                    });

                    select.on('change', function() {
                        self.setCustomer(this.value);
                    });

                    const pre = select.val();
                    if (pre) this.setCustomer(pre);
                },
            }));
            // wilayahForm.js (bisa inline di dalam <script> atau file terpisah)
            Alpine.data('wilayahForm', () => ({
                provinsi_code: '',
                provinsi_nama: '',
                kota_code: '',
                kota_nama: '',
                kecamatan_code: '',
                kecamatan_nama: '',
                    desa_code: '',
                desa_nama: '',
                listProvinsi: [],
                listKota: [],
                    listKecamatan: [],
                listDesa: [],
                // state loading
                isLoadingProvinsi: false,
                isLoadingKota: false,
                    isLoadingKecamatan: false,
                isLoadingDesa: false,
                // ========================================================
                async loadProvinsi() {
                        this.isLoadingProvinsi = true;
                    this.listProvinsi = [];
                    try {
                        const res = await fetch('/api/wilayah/provinsi');
                            const json = await res.json();
                        this.listProvinsi = json.data || [];
                        this.provinsi_code = '';
                        this.kota_code = '';
                        this.kecamatan_code = '';
                        this.desa_code = '';
                        this.listKota = [];
                            this.listKecamatan = [];
                        this.listDesa = [];
                        this.initSelect2('#provinsi_code', this.listProvinsi, 'provinsi');
                    } catch (err) {
                        console.error('Gagal memuat provinsi:', err);
                    } finally {
                        this.isLoadingProvinsi = false;
                        }
                },
                // ========================================================
                    async loadKota() {
                    if (!this.provinsi_code) return;
                        this.isLoadingKota = true;
                    this.listKota = [];
                    try {
                        const res = await fetch(`/api/wilayah/kota/${this.provinsi_code}`);
                            const json = await res.json();
                        this.listKota = json.data || [];
                        this.kota_code = '';
                        this.kecamatan_code = '';
                        this.desa_code = '';
                            this.listKecamatan = [];
                        this.listDesa = [];
                        this.initSelect2('#kota_code', this.listKota, 'kota');
                    } catch (err) {
                        console.error('Gagal memuat kota:', err);
                    } finally {
                        this.isLoadingKota = false;
                        }
                },
                // ========================================================
                    async loadKecamatan() {
                    if (!this.kota_code) return;
                        this.isLoadingKecamatan = true;
                    this.listKecamatan = [];
                    try {
                        const res = await fetch(`/api/wilayah/kecamatan/${this.kota_code}`);
                            const json = await res.json();
                        this.listKecamatan = json.data || [];
                        this.kecamatan_code = '';
                            this.desa_code = '';
                        this.listDesa = [];
                        this.initSelect2('#kecamatan_code', this.listKecamatan, 'kecamatan');
                    } catch (err) {
                        console.error('Gagal memuat kecamatan:', err);
                    } finally {
                        this.isLoadingKecamatan = false;
                        }
                },
                async loadDesa() {
                    if (!this.kecamatan_code) return;
                        this.isLoadingDesa = true;
                    this.listDesa = [];
                    try {
                        const res = await fetch(`/api/wilayah/desa/${this.kecamatan_code}`);
                            const json = await res.json();
                        this.listDesa = json.data || [];
                        this.desa_code = '';
                        this.initSelect2('#desa_code', this.listDesa, 'desa');
                    } catch (err) {
                        console.error('Gagal memuat desa:', err);
                    } finally {
                        this.isLoadingDesa = false;
                        }
                },
                initSelect2(selector, dataList, modelKey) {
                    if ($(selector).hasClass('select2-hidden-accessible')) {
                            $(selector).off('change').select2('destroy');
                    }
                    $(selector).empty().append('<option value="">Pilih</option>');
                    (dataList || []).forEach(item => {
                            $(selector).append(new Option(item.name, item.code));
                    });
                    $(selector).select2({
                        theme: 'bootstrap4',
                        placeholder: 'Pilih lokasi...',
                        allowClear: true,
                            width: '100%',
                    });
                    $(selector).on('change', (e) => {
                            const code = e.target.value;
                        const selected = dataList.find(i => i.code === code);
                            this[`${modelKey}_code`] = code || '';
                        this[`${modelKey}_nama`] = selected ? selected.name : '';
                        if (modelKey === 'provinsi') this.loadKota();
                        if (modelKey === 'kota') this.loadKecamatan();
                        if (modelKey === 'kecamatan') this.loadDesa();
                    });
                    },
            }));
            });    </script>
@endsection
