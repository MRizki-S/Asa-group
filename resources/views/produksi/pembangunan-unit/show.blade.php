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

        // Fungsi untuk update URL tanpa refresh halaman
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
            this.catatanGlobal = '';
            this.itemsToOrder = bahanArray.map(b => ({
                pembangunan_unit_rap_bahan_id: b.id,
                barang_id: b.barang_id,
                satuan_id: b.satuan_id,
                nama_barang: b.nama_barang,
                satuan: b.satuan,
                jumlah_standar: Number(b.jumlah_standar),
                jumlah_input: Number(b.jumlah_standar),
                checked: true,
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
                    items: selectedItems
                });
                location.reload(); // Setelah reload, URL parameter akan menjaga tab tetap terbuka
            } catch (error) {
                console.error(error.response.data);
                alert('Gagal mengirim order.');
            } finally {
                this.loadingRequest = false;
            }
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
