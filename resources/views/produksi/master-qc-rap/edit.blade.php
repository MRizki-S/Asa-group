@extends('layouts.app')

@section('pageActive', 'MasterQC-RAP')

@section('content')
<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6"
     x-data="qcEditForm(
        @js($container->urutan->map(fn($u) => [
            'qc_ke' => $u->qc_ke,
            'nama_qc' => $u->nama_qc,
            'tugas' => $u->tugas->pluck('tugas')->toArray()
        ])),
        @js($container->rapBahan->map(fn($b) => [
            'urutan_idx' => $container->urutan->search(fn($u) => $u->id == $b->master_qc_urutan_id),
            'barang_id' => $b->barang_id ?? 1,
            'jumlah_kebutuhan_standar' => $b->jumlah_kebutuhan_standar,
            'satuan' => $b->satuan
        ])),
        @js($container->rapUpah->map(fn($up) => [
            'urutan_idx' => $container->urutan->search(fn($u) => $u->id == $up->master_qc_urutan_id),
            'master_upah_id' => $up->master_upah_id,
            'nominal_standar' => $up->nominal_standar
        ]))
     )">

    <div x-data="{ pageName: 'Edit Master QC & RAP' }">
        @include('partials.breadcrumb')
    </div>

    {{-- Alert Error Validasi --}}
    @if ($errors->any())
        <div class="flex p-4 mb-6 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400 shadow-sm" role="alert">
            <svg class="shrink-0 inline w-4 h-4 me-3 mt-[2px]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
            </svg>
            <div>
                <span class="font-medium">Terjadi kesalahan validasi:</span>
                <ul class="mt-1.5 list-disc list-inside text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ route('produksi.masterQcRap.update', $container->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Card Informasi Utama --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800 mb-6 shadow-sm">
            <div class="px-5 py-4 sm:px-6 sm:py-5">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white mb-5 border-b border-gray-100 dark:border-gray-700 pb-3">
                    Informasi Utama QC
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type Unit</label>
                        <select name="type_id" required
                            class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 transition">
                            @foreach ($allType ?? [] as $item)
                                <option value="{{ $item->id }}" {{ $container->type_id == $item->id ? 'selected' : '' }}>
                                    {{ $item->nama_type }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Container</label>
                        <input type="text" name="nama_container" required value="{{ $container->nama_container }}"
                            class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 transition" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab Dashboard --}}
        <div class="mb-6">
            <div class="flex border-b border-gray-200 dark:border-gray-700">
                <button type="button" @click="tab = 'qc'" :class="tab === 'qc' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'" class="px-6 py-3 border-b-2 font-medium text-sm transition-all focus:outline-none">
                    1. Langkah QC
                </button>
                <button type="button" @click="tab = 'bahan'" :class="tab === 'bahan' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'" class="px-6 py-3 border-b-2 font-medium text-sm transition-all focus:outline-none">
                    2. RAP Bahan
                </button>
                <button type="button" @click="tab = 'upah'" :class="tab === 'upah' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'" class="px-6 py-3 border-b-2 font-medium text-sm transition-all focus:outline-none">
                    3. RAP Upah
                </button>
            </div>

            @include('produksi.master-qc-rap.partials.tab-qc')
            @include('produksi.master-qc-rap.partials.tab-bahan')
            @include('produksi.master-qc-rap.partials.tab-upah')
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
            <button type="button" @click="window.location.reload()"
                class="px-6 py-2.5 text-sm font-medium text-orange-700 bg-orange-50 border border-orange-200 rounded-lg hover:bg-orange-100 transition flex items-center">
                <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Reset (Muat Ulang)
            </button>
            <div class="flex gap-3">
                <button type="button" onclick="window.location.href='{{ route('produksi.masterQcRap.index') }}'"
                    class="px-6 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition shadow-sm font-semibold">
                    Batal
                </button>
                <button type="submit"
                    class="px-10 py-2.5 text-sm font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-lg">
                    Update Master QC & RAP
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    function qcEditForm(initialQc, initialBahan, initialUpah) {
        return {
            tab: 'qc',
            qcGroups: initialQc || [],
            bahanGroups: initialBahan || [],
            upahGroups: initialUpah || [],
            openAccordions: {},

            toggleAccordion(index) {
                this.openAccordions[index] = !this.openAccordions[index];
            },

            addQc() {
                const next = this.qcGroups.length + 1;
                this.qcGroups.push({ qc_ke: next, nama_qc: `QC-${next}`, tugas: [''] });
            },

            removeQc(index) {
                Swal.fire({
                    title: 'Hapus Langkah QC?',
                    text: 'Menghapus langkah ini akan menghapus RAP Bahan & Upah terkait yang sudah dipilih!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then((r) => {
                    if (r.isConfirmed) {
                        this.qcGroups.splice(index, 1);
                        // Re-order qc_ke
                        this.qcGroups.forEach((g, i) => g.qc_ke = i + 1);
                        // Filter out materials and wages that belong to the deleted index
                        this.bahanGroups = this.bahanGroups.filter(b => b.urutan_idx !== index);
                        this.upahGroups = this.upahGroups.filter(u => u.urutan_idx !== index);

                        // Adjust remaining urutan_idx to keep sync
                        this.bahanGroups.forEach(b => { if(b.urutan_idx > index) b.urutan_idx--; });
                        this.upahGroups.forEach(u => { if(u.urutan_idx > index) u.urutan_idx--; });
                    }
                });
            },

            addTugas(idx) {
                this.qcGroups[idx].tugas.push('');
            },

            removeTugas(idx, tIdx) {
                this.qcGroups[idx].tugas.splice(tIdx, 1);
            },

            addBahan(indexQC) {
                this.bahanGroups.push({
                    urutan_idx: indexQC,
                    barang_id: '',
                    jumlah_kebutuhan_standar: 0,
                    satuan: ''
                });
                this.openAccordions[indexQC] = true;
            },

            removeBahan(idx) {
                this.bahanGroups.splice(idx, 1);
            },

            formatRupiah(val) {
                if (!val) return '';
                return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            },

            parseNumber(val) {
                return val.replace(/\./g, '').replace(/[^0-9]/g, '');
            },

            addUpah(indexQC) {
                this.upahGroups.push({
                    urutan_idx: indexQC,
                    master_upah_id: '',
                    nominal_standar: 0
                });
                this.openAccordions[indexQC] = true;
            },

            removeUpah(idx) {
                this.upahGroups.splice(idx, 1);
            }
        }
    }
</script>

<style>
    [x-cloak] { display: none !important; }
    select option { background-color: white; color: black; }
    .dark select option { background-color: #1f2937; color: white; }
</style>
@endsection
