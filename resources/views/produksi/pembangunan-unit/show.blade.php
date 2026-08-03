@extends('layouts.app')

@section('pageActive', 'pembangunanUnit')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6 text-gray-700" 
        @update-unit-status.window="unitStatus = $event.detail"
        @update-total-progress.window="totalProgress = $event.detail"
        x-data="{
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
        totalProgress: {{ $data->total_progres }},
        statusST: '{{ $data->status_serah_terima ?? 'pending' }}',
        filterType: 'stock',
        itemsAdditional: [],
        allBarang: {{ $allBarang->toJson() }},
        openCancelOrderModal: false,
        cancelOrderActionUrl: '',
        openCancelUpahModal: false,
        cancelUpahActionUrl: '',
        showAdditional: false,
        openReturnModal: false,
        returnLoading: false,
        returnSubmitting: false,
        returnQcId: null,
        returnQcName: '',
        returnPembangunanUnitId: '{{ $data->id }}',
        returnSummary: [],
        returnItems: [],
        returnTanggal: new Date().toISOString().slice(0, 10),
        returnCatatan: '',

        async prepareReturn(qcId, qcName) {
            this.returnQcId = qcId;
            this.returnQcName = qcName;
            this.returnItems = [];
            this.returnTanggal = new Date().toISOString().slice(0, 10);
            this.returnCatatan = '';
            this.returnSummary = [];
            this.returnLoading = true;
            this.openReturnModal = true;
            try {
                const res = await axios.get(`/produksi/pembangunan-unit/return-barang/${qcId}/summary`);
                this.returnSummary = res.data.items || [];
                this.returnQcName = res.data.qc?.nama || qcName;
            } catch (e) {
                alert('Gagal memuat data barang QC.');
                this.openReturnModal = false;
            } finally {
                this.returnLoading = false;
            }
        },

        getAvailableReturnBarang(currentIndex) {
            const usedIds = this.returnItems
                .filter((_, i) => i !== currentIndex && _.barang_id)
                .map(i => i.barang_id);
            return this.returnSummary.filter(s => !usedIds.includes(s.barang_id));
        },

        getBarangSatuanOptions(barangId) {
            if (!barangId) return [];
            const barang = this.allBarang.find(b => b.id == barangId);
            return barang ? barang.available_satuan : [];
        },

        formatQty(val) {
            if (!val || isNaN(val)) return 0;
            return Math.round(val * 1000) / 1000;
        },

        onReturnBarangChange(index, barangId) {
            const item = this.returnItems[index];
            const summary = this.returnSummary.find(s => s.barang_id == barangId);
            if (!summary) return;

            item.barang_id = parseInt(barangId);
            item.nama_barang = summary.nama_barang;
            item.total_diterima_base = summary.total_diterima_base;
            item.sudah_retur_base = summary.sudah_retur_base;
            item.sisa_retur_base = summary.sisa_retur_base;
            item.base_satuan_nama = summary.base_satuan_nama;
            item.satuan_options = summary.satuan_options || [];

            item.satuan_id = summary.base_satuan_id;
            item.satuan_selected_nama = summary.base_satuan_nama;
            item.faktor = 1;

            this.recalculateReturnItemDisplay(index);
            item.jumlah_input = 0;
        },

        onReturnSatuanChange(index) {
            const item = this.returnItems[index];
            if (!item.satuan_options) return;
            const sat = item.satuan_options.find(s => s.satuan_id == item.satuan_id);
            if (!sat) return;

            item.faktor = sat.konversi_ke_base || 1;
            item.satuan_selected_nama = sat.nama_satuan;

            this.recalculateReturnItemDisplay(index);
            item.jumlah_input = 0;
        },

        recalculateReturnItemDisplay(index) {
            const item = this.returnItems[index];
            const faktor = item.faktor > 0 ? item.faktor : 1;

            item.total_diterima_display = this.formatQty(item.total_diterima_base / faktor);
            item.sudah_retur_display = this.formatQty(item.sudah_retur_base / faktor);
            item.sisa_retur_display = this.formatQty(item.sisa_retur_base / faktor);
            item.max_jumlah_input = item.sisa_retur_display;
        },

        addReturnItem() {
            this.returnItems.push({
                barang_id: null,
                nama_barang: '',
                satuan_id: null,
                satuan_selected_nama: '',
                satuan_options: [],
                faktor: 1,
                jumlah_input: 0,
                max_jumlah_input: 0,
                total_diterima_base: 0,
                sudah_retur_base: 0,
                sisa_retur_base: 0,
                total_diterima_display: 0,
                sudah_retur_display: 0,
                sisa_retur_display: 0,
                base_satuan_nama: '',
                keterangan: '',
            });
            const index = this.returnItems.length - 1;
            this.initReturnSelect2(index);
        },

        removeReturnItem(index) {
            this.returnItems.splice(index, 1);
            // Destroy select2 is handled when DOM node is removed
        },

        initReturnSelect2(index) {
            this.$nextTick(() => {
                const el = $(`#return-barang-select-${index}`);
                if(el.length === 0) return;
                const modalContainer = el.closest('.fixed');

                el.select2({
                    placeholder: '-- Pilih Barang --',
                    dropdownParent: modalContainer,
                    width: '100%'
                });

                el.on('change', (e) => {
                    // Update Alpine data manually because Select2 hides original select change event
                    this.onReturnBarangChange(index, e.target.value);
                });
            });
        },

        async submitReturn() {
            if (!this.returnTanggal) {
                alert('Harap isi tanggal return.');
                return;
            }

            const validItems = this.returnItems.filter(i => i.barang_id && i.jumlah_input > 0);
            if (validItems.length === 0) {
                alert('Tambahkan minimal satu barang dengan jumlah return yang valid.');
                return;
            }

            const overLimit = validItems.some(i => i.jumlah_input > i.max_jumlah_input && i.max_jumlah_input > 0);
            if (overLimit) {
                alert('Salah satu item melebihi sisa yang dapat dikembalikan.');
                return;
            }

            this.returnSubmitting = true;
            try {
                await axios.post('{{ route('produksi.pembangunanUnit.returnStore') }}', {
                    pembangunan_unit_id: this.returnPembangunanUnitId,
                    pembangunan_unit_qc_id: this.returnQcId,
                    tanggal_return: this.returnTanggal,
                    catatan: this.returnCatatan,
                    items: validItems.map(i => ({
                        barang_id: i.barang_id,
                        nama_barang: i.nama_barang,
                        satuan_id: i.satuan_id,
                        satuan: i.satuan_selected_nama,
                        jumlah_input: i.jumlah_input,
                        keterangan: i.keterangan,
                    })),
                });
                location.reload();
            } catch (error) {
                const msg = error.response?.data?.message || 'Terjadi kesalahan.';
                alert('Gagal mengajukan return: ' + msg);
            } finally {
                this.returnSubmitting = false;
            }
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

        async promptCreateServis() {
            const result = await Swal.fire({
                title: 'Mulai Servis?',
                text: 'Apakah Anda yakin ingin memulai sesi Servis untuk unit ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2563EB',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Ya, Mulai Servis',
                cancelButtonText: 'Batal'
            });

            if (result.isConfirmed) {
                const form = document.getElementById('form-create-servis');
                if (form) form.submit();
            }
        },

        goToServis(servisIndex) {
            window.dispatchEvent(new CustomEvent('open-qc', { detail: servisIndex }));
            this.updateUrl(servisIndex, 'bahan');
            this.$nextTick(() => {
                const el = document.getElementById('qc-card-' + servisIndex);
                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        },

        async updateStatusST(newVal) {
            if (this.statusST === newVal) return;

            const label = newVal.replace(/_/g, ' ');
            const result = await Swal.fire({
                title: 'Konfirmasi Serah Terima',
                text: `Apakah Anda yakin ingin mengubah status serah terima menjadi '${label}'?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2563EB',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Ya, Ubah Status',
                cancelButtonText: 'Batal'
            });

            if (!result.isConfirmed) return;

            try {
                const res = await axios.post('{{ route('produksi.pembangunanUnit.updateSerahTerima', $data->id) }}', {
                    status_serah_terima: newVal
                });

                if (res.data.success) {
                    this.statusST = newVal;

                    if (res.data.unit_status) {
                        this.unitStatus = res.data.unit_status;
                    }
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Status serah terima berhasil diperbarui!',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            } catch (e) {
                console.error(e);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Gagal memperbarui status serah terima'
                });
            }
        },

        async updateUnitStatus(newVal) {
            if (this.unitStatus === newVal) return;

            // Jika mau selesai tapi progress belum 100%, tampilkan error
            if (newVal === 'selesai' && this.totalProgress < 100) {
                Swal.fire({
                    icon: 'error',
                    title: 'Tidak Dapat Diselesaikan',
                    text: `Progress pembangunan baru ${this.totalProgress}%. Harus 100% untuk bisa diselesaikan.`,
                    confirmButtonColor: '#d33'
                });
                return;
            }

            const label = newVal.charAt(0).toUpperCase() + newVal.slice(1);
            const result = await Swal.fire({
                title: 'Konfirmasi Status',
                text: `Apakah Anda yakin ingin mengubah status menjadi '${label}'?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: newVal === 'selesai' ? '#059669' : '#2563EB',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Ya, Ubah Status',
                cancelButtonText: 'Batal'
            });

            if (!result.isConfirmed) return;

            try {
                const res = await axios.patch('{{ route('produksi.pembangunanUnit.update', $data->id) }}', {
                    status_pembangunan: newVal,
                    _method: 'PATCH'
                });

                this.unitStatus = newVal;
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: `Status berhasil diubah menjadi ${label}!`,
                    timer: 1500,
                    showConfirmButton: false
                });
            } catch (e) {
                console.error(e);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: e.response?.data?.message || 'Gagal memperbarui status pembangunan'
                });
            }
        },


        prepareUpah(upahArray, qcId) {
            this.selectedQcId = qcId;
            this.itemsToPay = upahArray.map(u => {
                const nominalStandar = Number(u.nominal_standar) || 0;
                const totalOrderedUpah = Number(u.total_ordered_upah) || 0;
                const remaining = Math.max(0, nominalStandar - totalOrderedUpah);
                return {
                    pembangunan_unit_rap_upah_id: u.id,
                    nama_upah: u.nama_upah,
                    nominal_standar: nominalStandar,
                    total_ordered_upah: totalOrderedUpah,
                    nominal_pengajuan: remaining,
                    catatan_pengawas: '',
                    checked: false
                };
            });
            this.openUpahModal = true;
        },

        async submitUpah() {
            const selected = this.itemsToPay.filter(i => i.checked);
            if (selected.length === 0) return alert('Pilih minimal satu upah.');
            if (selected.some(i => i.nominal_pengajuan <= 0 || !i.nominal_pengajuan)) {
                alert('Nominal pengajuan harus diisi untuk item yang dipilih!');
                return;
            }
            if (selected.some(i => {
                const total = parseFloat(i.nominal_pengajuan || 0) + parseFloat(i.total_ordered_upah || 0);
                const limit = parseFloat(i.nominal_standar || 0);
                return total > limit + 0.01 && (!i.catatan_pengawas || !i.catatan_pengawas.trim());
            })) {
                alert('Terdapat item upah yang melebihi RAP. Harap isi catatan pengawas sebagai alasan!');
                return;
            }
            this.loadingUpah = true;
            try {
                await axios.post('{{ route('produksi.pembangunanUnit.upahStore') }}', {
                    pembangunan_unit_id: '{{ $data->id }}',
                    pembangunan_unit_qc_id: this.selectedQcId,
                    items: selected
                });
                location.reload(); // Setelah reload, URL parameter akan menjaga tab tetap terbuka
            } catch (error) {
                alert(error.response?.data?.message || 'Gagal mengirim pengajuan upah.');
            } finally {
                this.loadingUpah = false;
            }
        },
        prepareOrder(bahanArray, qcId) {
            this.selectedQcId = qcId;
            this.filterType = 'stock';
            this.catatanGlobal = '';
            this.showAdditional = false;
            this.itemsAdditional = [];

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
                    total_ordered_base: parseFloat(b.total_ordered_base) || 0,

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
                this.loadingRequest = false;
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
        },

        prepareServisOrder(qcId) {
            this.selectedQcId = qcId;
            this.filterType = 'stock';
            this.catatanGlobal = '';
            this.itemsToOrder = [];
            this.itemsAdditional = [];
            this.showAdditional = true;
            this.addAdditionalItem();
            this.openRequest = true;
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

        <!-- Modal Konfirmasi Batal Order -->
        <template x-teleport="body">
            <div x-show="openCancelOrderModal"
                class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/50 dark:bg-black/80"
                x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100">
                <div @click.away="openCancelOrderModal = false" class="relative w-full max-w-md p-4">
                    <div class="relative bg-white rounded-xl shadow-xl dark:bg-gray-800 overflow-hidden border border-gray-100 dark:border-gray-700">
                        <div class="flex items-center justify-between p-4 border-b dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Batalkan Order Barang</h3>
                            <button type="button" @click="openCancelOrderModal = false" class="text-gray-400 hover:text-gray-900 dark:hover:text-white">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 14 14">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                                </svg>
                            </button>
                        </div>
                    </p>
                    <form :action="cancelOrderActionUrl" method="POST" class="flex justify-center gap-3">
                        @csrf
                        @method('DELETE')
                        <button type="button" @click="openCancelOrderModal = false"
                            class="px-4 py-2 text-xs font-semibold text-gray-600 bg-gray-100 dark:bg-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-xs font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700">
                            Ya, Batalkan
                        </button>
                    </form>
                </div>
            </div>
        </template>

        <!-- Modal Konfirmasi Batal Upah -->
        <template x-teleport="body">
            <div x-show="openCancelUpahModal"
                class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/50 dark:bg-black/80"
                x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100">
                <div @click.away="openCancelUpahModal = false" class="relative w-full max-w-md p-4">
                    <div class="relative bg-white rounded-xl shadow-xl dark:bg-gray-800 overflow-hidden border border-gray-100 dark:border-gray-700">
                        <div class="flex items-center justify-between p-4 border-b dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Batalkan Pengajuan Upah</h3>
                            <button type="button" @click="openCancelUpahModal = false" class="text-gray-400 hover:text-gray-900 dark:hover:text-white">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 14 14">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                                </svg>
                            </button>
                        </div>

                        <form :action="cancelUpahActionUrl" method="POST" class="p-5 space-y-4">
                            @csrf
                            @method('DELETE')
                            
                            <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                                Apakah Anda yakin ingin membatalkan pengajuan upah ini? Tindakan ini tidak dapat dibatalkan.
                            </p>

                            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-600">
                                <button type="button" @click="openCancelUpahModal = false"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                    Kembali
                                </button>
                                <button type="submit"
                                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:ring-red-300 shadow-sm transition">
                                    Ya, Batalkan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </template>
    </div>
@endsection
