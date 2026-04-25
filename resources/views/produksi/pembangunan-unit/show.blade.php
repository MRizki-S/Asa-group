@extends('layouts.app')

@section('pageActive', 'pembangunanUnit')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6 text-gray-700" x-data="{
        openRequest: false,
        loadingRequest: false,
        selectedQcId: null,
        itemsToOrder: [],
        catatanGlobal: '',
        openUpahModal: false,
        loadingUpah: false,
        itemsToPay: [],
        catatanUpah: '',
        unitStatus: '{{ $data->status_pembangunan ?? 'proses' }}',
        statusST: '{{ $data->status_serah_terima ?? 'pending' }}',
        filterType: 'stock',
        itemsAdditional: [],
        allBarang: {{ $allBarang->toJson() }},
        showAdditional: false,
        openReturnModal: false,
        returnItems: [],
        returnOrderId: null,

        prepareReturn(orderId, items) {
            this.returnOrderId = orderId;
            this.returnItems = items.map(i => ({
                ...i,
                retur: i.retur ? parseFloat(i.retur) : 0,
                keterangan: ''
            }));
            this.openReturnModal = true;
        },

        formatRupiah(val) {
            if (!val) return '';
            return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        },

        parseNumber(val) {
            return val.replace(/\./g, '').replace(/[^0-9]/g, '');
        },


        updateUrl(qcIndex, tabName) {
            const url = new URL(window.location);
            if (qcIndex !== null) {
                url.searchParams.set('qc', qcIndex);
            } else {
                url.searchParams.delete('qc');
                url.searchParams.delete('tab');
            }
            if (tabName) url.searchParams.set('tab', tabName);
            window.history.replaceState({}, '', url);
        },

        async updateStatusST(newVal) {
            try {
                const res = await axios.post('{{ route('produksi.pembangunanUnit.updateSerahTerima', $data->id) }}', {
                    status_serah_terima: newVal
                });

                if (res.data.success) {
                    this.statusST = newVal;

                    if (res.data.unit_status) {
                        this.unitStatus = res.data.unit_status;
                    }
                }
            } catch (e) {
                console.error(e);
                alert('Gagal memperbarui status.');
            }
        },

        prepareUpah(upahArray, qcId) {
            this.selectedQcId = qcId;
            this.catatanUpah = '';
            this.itemsToPay = upahArray.map(u => ({
                pembangunan_unit_rap_upah_id: u.id,
                nama_upah: u.nama_upah,
                nominal_standar: Number(u.nominal_standar),
                nominal_pengajuan: Number(u.nominal_standar),
                checked: false
            }));
            this.openUpahModal = true;
        },

        async submitUpah() {
            const selected = this.itemsToPay.filter(i => i.checked);
            if (selected.length === 0) return alert('Pilih minimal satu upah.');
            if (selected.some(i => i.nominal_pengajuan <= 0 || !i.nominal_pengajuan)) {
                alert('Nominal pengajuan harus diisi untuk item yang dipilih!');
                return;
            }
            this.loadingUpah = true;
            try {
                await axios.post('{{ route('produksi.pembangunanUnit.upahStore') }}', {
                    pembangunan_unit_id: '{{ $data->id }}',
                    pembangunan_unit_qc_id: this.selectedQcId,
                    {{-- catatan: this.catatanUpah, --}}
                    items: selected
                });
                location.reload(); // Setelah reload, URL parameter akan menjaga tab tetap terbuka
            } catch (error) {
                alert('Gagal mengirim pengajuan upah.');
            } finally {
                this.loadingUpah = false;
            }
        },
        prepareOrder(bahanArray, qcId) {
            this.selectedQcId = qcId;
            this.filterType = 'stock';
            this.catatanGlobal = '';

            this.itemsToOrder = bahanArray.map(b => {
                // Ambil faktor dari database (Sekarang Pcs=1, Dus=16)
                const fRap = parseFloat(b.faktor_konversi) || 1;
                const qRap = parseFloat(b.jumlah_standar) || 0;

                // JANGKAR: Total dalam satuan terkecil (Base Unit)
                // 5 Dus * 16 = 80 Pcs
                const baseTotal = qRap * fRap;

                return {
                    pembangunan_unit_rap_bahan_id: b.id,
                    barang_id: b.barang_id,
                    is_stock: b.is_stock,
                    nama_barang: b.nama_barang,

                    // Simpan angka 80 sebagai patokan tetap
                    base_total_anchor: baseTotal,

                    satuan_id: b.satuan_id,
                    satuan: b.satuan,
                    jumlah_input: qRap,
                    jumlah_standar: qRap,
                    faktor_konversi: fRap,
                    checked: false,
                    alasan: ''
                };
            });
            this.openRequest = true;
        },

        changeSatuanOrder(item, newSatuanId) {
            // Cari detail satuan di master barang
            const detailBarang = this.allBarang.find(db => db.id == item.barang_id);
            const s = detailBarang?.available_satuan.find(opt => opt.id == newSatuanId);

            if (s) {
                const faktorBaru = parseFloat(s.faktor) || 1;

                // RUMUS: Total Base / Faktor Baru
                // Pcs: 80 / 1 = 80
                // Dus: 80 / 16 = 5
                const hasilHitung = item.base_total_anchor / faktorBaru;

                // Update State secara reaktif
                item.jumlah_input = hasilHitung;
                item.satuan_id = newSatuanId;
                item.satuan = s.nama;
                item.faktor_konversi = faktorBaru;

                // Update label RAP agar user tahu batas maksimal dalam satuan baru
                item.jumlah_standar = item.base_total_anchor / faktorBaru;
            }
        },

        async submitRequest() {
            const selectedFromRap = this.itemsToOrder.filter(i => i.checked);

            const selectedFromAdditional = this.itemsAdditional.filter(i => i.barang_id != 0);

            const finalItems = [...selectedFromRap, ...selectedFromAdditional];

            if (finalItems.length === 0) return alert('Pilih atau tambah minimal satu barang.');

            this.loadingRequest = true;
            try {
                await axios.post('{{ route('produksi.pembangunanUnit.orderStore') }}', {
                    pembangunan_unit_id: '{{ $data->id }}',
                    pembangunan_unit_qc_id: this.selectedQcId,
                    catatan: this.catatanGlobal,
                    items: finalItems,
                    jenis_order: this.filterType,
                });
                location.reload();
            } catch (error) {
                console.error(error.response?.data);
                alert('Gagal mengirim order: ' + (error.response?.data?.message || 'Terjadi kesalahan'));
            } finally {
                this.loadingRequest = true;
            }
        },

        addAdditionalItem() {
            this.itemsAdditional.push({
                pembangunan_unit_rap_bahan_id: null,
                barang_id: 0,
                nama_barang: '',
                jumlah_input: 1,
                satuan_id: 0,
                satuan: '',
                is_stock: this.filterType === 'stock',
                checked: true,
                alasan: 'Barang tambahan di luar RAP',
                faktor_konversi: 1,
                jumlah_standar: 0
            });
        },

        removeAdditionalItem(index) {
            this.itemsAdditional.splice(index, 1);
        },

        initSelect2(index) {
            this.$nextTick(() => {
                const el = $(`#barang-select-${index}`);
                // Ambil container modal terdekat
                const modalContainer = el.closest('.relative.bg-white');

                el.select2({
                    placeholder: '-- Pilih Barang --',
                    dropdownParent: modalContainer, // WAJIB: agar dropdown ada di dalam DOM modal
                    width: '100%'
                });

                el.on('change', (e) => {
                    this.itemsAdditional[index].barang_id = e.target.value;
                    this.updateBarangDetail(index);
                });
            });
        },

        updateBarangDetail(index) {
            const item = this.itemsAdditional[index];
            const selected = this.allBarang.find(b => b.id == item.barang_id);

            if (selected) {
                item.nama_barang = selected.nama_barang;
                item.is_stock = selected.is_stock;

                // Ambil satuan yang is_default = true (dari controller kita panggil 'is_default')
                // Berdasarkan mapping controller Anda, pastikan data is_default dikirim
                const defSatuan = selected.available_satuan.find(s => s.is_default) || selected.available_satuan[0];

                if (defSatuan) {
                    item.satuan_id = defSatuan.id;
                    item.satuan = defSatuan.nama;
                    item.faktor_konversi = defSatuan.faktor;
                }
            }
        },

        getAvailableSatuan(barangId) {
            if (!barangId || barangId == 0) return [];
            const barang = this.allBarang.find(b => b.id == barangId);
            return barang ? barang.available_satuan : []; // contains id, nama, faktor
        },


        getFilteredBarang(currentIndex) {
            const selectedIds = this.itemsAdditional
                .filter((item, idx) => idx !== currentIndex && item.barang_id != 0)
                .map(item => item.barang_id.toString());

            // Tambahkan juga barang yang ada di itemsToOrder agar tidak dobel
            const rapIds = this.itemsToOrder.map(i => i.barang_id.toString());
            const allBlockedIds = [...selectedIds, ...rapIds];

            return this.allBarang.filter(b =>
                (this.filterType === 'stock' ? b.is_stock : !b.is_stock) &&
                !allBlockedIds.includes(b.id.toString())
            );
        }
    }">

        <div x-data="{ pageName: 'Detail Pembangunan' }">
            @include('partials.breadcrumb')
        </div>

        {{-- 1. Header Info --}}
        @include('produksi.pembangunan-unit.partials.header-info')

        {{-- 2. Kontainer Daftar QC --}}
        @include('produksi.pembangunan-unit.partials.qc-accordion')

        {{-- 3. Modals --}}
        @include('produksi.pembangunan-unit.partials.modal-order')
        @include('produksi.pembangunan-unit.partials.modal-upah')
        @include('produksi.pembangunan-unit.partials.modal-order-return')
    </div>
@endsection
