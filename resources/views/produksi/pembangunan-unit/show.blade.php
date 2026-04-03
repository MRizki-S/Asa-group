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
            this.itemsToOrder = bahanArray.map(b => ({
                pembangunan_unit_rap_bahan_id: b.id,
                barang_id: b.barang_id,
                satuan_id: b.satuan_id,
                nama_barang: b.nama_barang,
                satuan: b.satuan,
                faktor_konversi: Number(b.faktor_konversi),
                jumlah_input: Number(b.jumlah_standar),
                jumlah_standar: Number(b.jumlah_standar),
                is_stock: b.is_stock,
                checked: false,
                alasan: ''
            }));
            this.openRequest = true;
        },
    
        async submitRequest() {
            const selectedItems = this.itemsToOrder.filter(i => i.checked);
            if (selectedItems.length === 0) return alert('Pilih minimal satu barang.');
            this.loadingRequest = true;
            try {
                await axios.post('{{ route('produksi.pembangunanUnit.orderStore') }}', {
                    pembangunan_unit_id: '{{ $data->id }}',
                    pembangunan_unit_qc_id: this.selectedQcId,
                    catatan: this.catatanGlobal,
                    {{-- items: selectedItems --}}
                    items: this.itemsToOrder.filter(i => i.checked),
                    jenis_order: this.filterType,
                });
                location.reload();
            } catch (error) {
                console.error(error.response.data);
                alert('Gagal mengirim order.');
            } finally {
                this.loadingRequest = false;
            }
        },
    
        addAdditionalItem() {
            this.itemsAdditional.push({
                barang_id: 0,
                nama_barang: '',
                jumlah_input: 1,
                satuan_id: 0,
                satuan: '',
                is_stock: this.filterType === 'stock',
                checked: true,
                pembangunan_unit_rap_bahan_id: null,
                alasan: 'Barang tambahan di luar RAP',
                faktor_konversi: 1
            });
        },
    
        removeAdditionalItem(index) {
            this.itemsAdditional.splice(index, 1);
        },
    
        updateBarangDetail(index) {
            const item = this.itemsAdditional[index];
            // Cari data barang dari master list
            const selected = this.allBarang.find(b => b.id == item.barang_id);
    
            if (selected) {
                item.nama_barang = selected.nama_barang;
    
                // RESET satuan ke pilihan pertama dari barang baru
                if (selected.available_satuan && selected.available_satuan.length > 0) {
                    const firstSatuan = selected.available_satuan[0];
                    item.satuan_id = firstSatuan.id;
                    item.satuan = firstSatuan.nama;
                    item.faktor_konversi = firstSatuan.faktor;
                } else {
                    item.satuan_id = 0;
                    item.satuan = '';
                    item.faktor_konversi = 1;
                }
            }
        },
    
        getAvailableSatuan(barangId) {
            if (!barangId || barangId == 0) return [];
            const barang = this.allBarang.find(b => b.id == barangId);
            return barang ? barang.available_satuan : [];
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
    </div>
@endsection
