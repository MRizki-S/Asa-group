@extends('layouts.app')

@section('pageActive', 'PemesananUnit')

@section('content')

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">
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
        <div x-data="{ openInfo: false, openCash: false, openKpr: false }"
            class="mt-6 mb-6 rounded-xl border border-gray-200 dark:border-gray-700
           bg-white dark:bg-gray-800/50 shadow-sm text-gray-800 dark:text-gray-100 overflow-hidden">

            <!-- Header -->
            <button @click="openInfo = !openInfo"
                class="w-full flex justify-between items-center px-5 py-3 bg-gray-50 dark:bg-gray-800/70
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
                <div class="space-y-2 text-sm leading-snug mb-4">
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

                <!-- Promo Cash -->
                @if ($promoCash)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                        <button @click="openCash = !openCash"
                            class="w-full flex justify-between items-center px-4 py-2.5
                           bg-gray-50 dark:bg-gray-700/40 text-sm font-medium
                           hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                            <span><b>Promo — Pembayaran Cash</b></span>
                            <svg :class="{ 'rotate-180': openCash }" xmlns="http://www.w3.org/2000/svg"
                                class="w-4 h-4 transform transition-transform duration-200" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="openCash" x-transition x-cloak
                            class="p-4 bg-white dark:bg-gray-800 text-sm leading-snug">
                            <ul class="list-disc ml-5 space-y-1">
                                @foreach ($promoCash->items as $item)
                                    <li>{{ $item->nama_promo }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <!-- Promo KPR -->
                @if ($promoKpr)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden mt-3">
                        <button @click="openKpr = !openKpr"
                            class="w-full flex justify-between items-center px-4 py-2.5
                           bg-gray-50 dark:bg-gray-700/40 text-sm font-medium
                           hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                            <span><b>Promo — Pembayaran KPR</b></span>
                            <svg :class="{ 'rotate-180': openKpr }" xmlns="http://www.w3.org/2000/svg"
                                class="w-4 h-4 transform transition-transform duration-200" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="openKpr" x-transition x-cloak
                            class="p-4 bg-white dark:bg-gray-800 text-sm leading-snug">
                            <ul class="list-disc ml-5 space-y-1">
                                @foreach ($promoKpr->items as $item)
                                    <li>{{ $item->nama_promo }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <form action="{{ route('marketing.pemesananUnit.store') }}" method="POST" x-data="bookingForm()">
            @csrf

            {{-- Sub 1: Akun User & Booking Unit  --}}
            @include('marketing.pemesanan-unit.partials.akunUser-Booking')

            {{-- Data Diri Users --}}
            @include('marketing.pemesanan-unit.partials.data-diri')


            {{-- Opsional -> Pindah Unit --}}
            {{-- <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6">
                <div class="px-5 py-4 sm:px-6 sm:py-5">
                    <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                            Pindah Unit
                        </h3>
                        <div
                            class="flex items-center gap-2 bg-yellow-50 text-yellow-800 text-sm px-3 py-1.5 rounded-md border border-yellow-300 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-yellow-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-.01-10a9 9 0 100 18 9 9 0 000-18z" />
                            </svg>
                            <span>Kosongkan pilihan di bawah ini jika unit tetap sama dengan booking unit</span>
                        </div>
                    </div>

                    <div x-data="{
                        tahap: [],
                        unit: [],
                        async fetchTahap(perumahaanSlug) {
                            if (!perumahaanSlug) {
                                this.tahap = [];
                                this.unit = [];
                                return
                            }
                            const res = await fetch(`/etalase/perumahaan/${perumahaanSlug}/tahap-json`);
                            if (res.ok) this.tahap = await res.json();
                        },
                        async fetchUnit(tahapId) {
                            if (!tahapId) { this.unit = []; return }
                            const res = await fetch(`/etalase/tahap/${tahapId}/unit-json`);
                            if (res.ok) this.unit = await res.json();
                        }
                    }" class="grid grid-cols-1 md:grid-cols-3 gap-4">

                        <!-- Select Perumahaan -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Perumahaan (Tujuan)
                            </label>
                            <select name="perumahaan_id_pindah"
                                @change="
                        fetchTahap($event.target.options[$event.target.selectedIndex].getAttribute('data-slug'));
                        unit = [];
                    "
                                class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                           dark:bg-gray-700 dark:text-white
                           @error('perumahaan_id_pindah') border-red-500 @else border-gray-300 @enderror">
                                <option value="">Pilih Perumahaan</option>
                                @foreach ($allPerumahaan as $p)
                                    <option value="{{ $p->id }}" data-slug="{{ $p->slug }}"
                                        {{ old('perumahaan_id_pindah') == $p->id ? 'selected' : '' }}>
                                        {{ $p->nama_perumahaan }}
                                    </option>
                                @endforeach
                            </select>
                            @error('perumahaan_id_pindah')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Select Tahap -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Tahap (Tujuan)
                            </label>
                            <select name="tahap_id_pindah" @change="fetchUnit($event.target.value)"
                                class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                           dark:bg-gray-700 dark:text-white
                           @error('tahap_id_pindah') border-red-500 @else border-gray-300 @enderror">
                                <option value="">Pilih Tahap</option>
                                <template x-for="t in tahap" :key="t.id">
                                    <option :value="t.id" x-text="t.nama_tahap"></option>
                                </template>
                            </select>
                            @error('tahap_id_pindah')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Select Unit -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Unit (Tujuan)
                            </label>
                            <select name="unit_id_pindah"
                                class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                           dark:bg-gray-700 dark:text-white
                           @error('unit_id_pindah') border-red-500 @else border-gray-300 @enderror">
                                <option value="">Pilih Unit</option>
                                <template x-for="u in unit" :key="u.id">
                                    <option :value="u.id" x-text="u.nama_unit"></option>
                                </template>
                            </select>
                            @error('unit_id_pindah')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>
                </div>
            </div> --}}

            {{-- Sistem Pembayaran --}}
            @include('marketing.pemesanan-unit.partials.sistem-pembayaran')

            {{-- Cara Pembayaran --}}
            @include('marketing.pemesanan-unit.partials.cara-pembayaraan')

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
            <div class="flex justify-end gap-2">
                {{-- <button type="button" onclick="history.back()"
                    class="px-8 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300
                dark:text-white dark:bg-gray-700 dark:hover:bg-gray-600">
                    Kembali
                </button> --}}
                <button type="subemit"
                    class="px-8 py-2.5 text-sm font-medium text-white rounded-lg bg-blue-600 hover:bg-blue-700
                focus:outline-none focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800">
                    Simpan
                </button>
            </div>
        </form>
    </div>

    {{-- js  --}}
    <script>
        document.addEventListener('alpine:init', () => {

            Alpine.data('bookingForm', () => ({
                customers: @json($customersData),
                selectedCustomerId: '',
                selectedCustomer: null,
                jumlahCicilan: '',
                minimalDp: '',
                angsuranList: [],
                isLoading: false,
                hasSelected: false,

                setCustomer(id) {
                    this.selectedCustomerId = id;
                    this.selectedCustomer = this.customers.find(c => c.id == id) || null;
                    this.hasSelected = !!this.selectedCustomer;

                    if (this.selectedCustomer && this.selectedCustomer.booking.perumahaan_id) {
                        this.fetchSettingPpjb(this.selectedCustomer.booking.perumahaan_id);
                    } else {
                        this.jumlahCicilan = '';
                        this.minimalDp = '';
                        this.angsuranList = [];
                    }
                },

                async fetchSettingPpjb(perumahaanId) {
                    this.isLoading = true;
                    this.jumlahCicilan = '';
                    this.minimalDp = '';
                    this.angsuranList = [];

                    try {
                        const res = await fetch(`api/setting-cara-bayar/${perumahaanId}`);
                        const data = await res.json();

                        if (data) {
                            const jumlah = parseInt(data.data.jumlah_cicilan) || 0;
                            this.jumlahCicilan = jumlah + ' x';
                            this.minimalDp = parseInt(data.data.minimal_dp) || 0;
                            this.generateAngsuran(jumlah);
                        } else {
                            this.jumlahCicilan = 'Tidak ada data';
                        }
                    } catch (error) {
                        console.error(error);
                        this.jumlahCicilan = 'Gagal memuat data';
                    } finally {
                        // biar loadingnya keliatan sebentar
                        setTimeout(() => this.isLoading = false, 500);
                    }
                },

                generateAngsuran(jumlah) {
                    const today = new Date();
                    this.angsuranList = [];

                    for (let i = 0; i < jumlah; i++) {
                        const tanggal = new Date(today);
                        tanggal.setMonth(today.getMonth() + i);
                        const formatted = tanggal.toISOString().split('T')[0];

                        this.angsuranList.push({
                            tanggal: formatted,
                            nominal: i === 0 ? this.minimalDp : '',
                            nominalFormatted: i === 0 ? this.formatNumber(this.minimalDp) : '',
                        });
                    }
                },

                formatNominal(index) {
                    let value = this.angsuranList[index].nominalFormatted.replace(/\D/g, '');
                    this.angsuranList[index].nominal = parseInt(value || 0);
                    this.angsuranList[index].nominalFormatted = this.formatNumber(value);
                },

                formatNumber(value) {
                    if (!value) return '';
                    return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                },

                initSelect2() {
                    const self = this;
                    const select = $('#selectUser');

                    select.select2({
                        theme: 'bootstrap4',
                        width: '100%',
                        placeholder: "Cari dan pilih akun customer...",
                        allowClear: true
                    });

                    select.on('change', function() {
                        self.setCustomer(this.value);
                    });
                }
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

        });
    </script>
@endsection
