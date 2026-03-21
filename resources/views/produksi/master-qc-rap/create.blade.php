@extends('layouts.app')

@section('pageActive', 'MasterQC-RAP')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-data="qcForm()">

        <div x-data="{ pageName: 'Master QC & RAP' }">
            @include('partials.breadcrumb')
        </div>

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

        <form action="{{ route('produksi.masterQcRap.store') }}" method="POST">
            @csrf

            {{-- Card Informasi Utama --}}
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800 mb-6 shadow-sm">
                <div class="px-5 py-4 sm:px-6 sm:py-5">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white mb-5 border-b border-gray-100 dark:border-gray-700 pb-3">
                        Informasi Utama QC
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type Unit</label>
                            <select name="type_id" required
                                class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 transition">
                                <option value="" class="dark:bg-gray-800">-- Pilih Type --</option>
                                @foreach ($allType ?? [] as $item)
                                    <option value="{{ $item->id }}" {{ old('type_id') == $item->id ? 'selected' : '' }}>
                                        {{ $item->nama_type }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama QC</label>
                            <input type="text" name="nama_container" required placeholder="Masukkan nama container..."
                                value="{{ old('nama_container') }}"
                                class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 transition" />
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jumlah Langkah QC</label>
                            <div class="flex gap-2">
                                <input type="number" x-model="tempJumlahQc" min="1" max="50" placeholder="0"
                                    class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white text-sm rounded-lg p-2.5 focus:ring-blue-500" />
                                <button type="button" @click="generateQc()"
                                    class="px-5 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors shadow-sm text-sm whitespace-nowrap">
                                    Terapkan
                                </button>
                            </div>
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
                <p class="text-xs text-gray-500 dark:text-gray-400 italic font-medium">
                    * Data pada seluruh tab akan diproses secara kolektif.
                </p>
                <div class="flex gap-3">
                    <button type="button" onclick="window.location.href='{{ route('produksi.masterQcRap.index') }}'"
                        class="px-6 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition shadow-sm">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-10 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-lg">
                        Simpan Master QC & RAP
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        function qcForm() {
            return {
                tab: 'qc',
                tempJumlahQc: null,
                qcGroups: [],
                bahanGroups: [],
                upahGroups: [],
                openAccordions: {},

                generateQc() {
                    const jumlah = parseInt(this.tempJumlahQc);
                    if (isNaN(jumlah) || jumlah < 1) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Input Tidak Valid',
                            text: 'Silakan masukkan jumlah yang valid (minimal 1)',
                            confirmButtonColor: '#2563eb'
                        });
                        return;
                    }

                    if (this.qcGroups.length > 0) {
                        Swal.fire({
                            title: 'Timpa Data QC?',
                            text: "Mengatur ulang langkah QC akan mengosongkan tabel RAP Bahan & Upah yang sudah terisi!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Ya, Atur Ulang',
                            cancelButtonText: 'Batal',
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6'
                        }).then((result) => {
                            if (result.isConfirmed) this.executeGenerate(jumlah);
                        });
                    } else {
                        this.executeGenerate(jumlah);
                    }
                },

                executeGenerate(jumlah) {
                    this.qcGroups = [];
                    this.bahanGroups = [];
                    this.upahGroups = [];
                    this.openAccordions = {};
                    for (let i = 1; i <= jumlah; i++) {
                        this.qcGroups.push({
                            qc_ke: i,
                            nama_qc: `QC-${i}`,
                            tugas: ['']
                        });
                        if(i === 1) this.openAccordions[0] = true;
                    }
                },

                addQc() {
                    const next = this.qcGroups.length + 1;
                    const index = this.qcGroups.length;
                    this.qcGroups.push({ qc_ke: next, nama_qc: `QC-${next}`, tugas: [''] });
                    this.openAccordions[index] = true;
                },

                removeQc(index) {
                    Swal.fire({
                        title: 'Hapus Langkah QC?',
                        text: 'Menghapus langkah ini akan menghapus RAP Bahan & Upah terkait!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Hapus',
                        confirmButtonColor: '#d33'
                    }).then((r) => {
                        if (r.isConfirmed) {
                            this.qcGroups.splice(index, 1);
                            this.qcGroups.forEach((g, i) => g.qc_ke = i + 1);
                            this.bahanGroups = this.bahanGroups.filter(b => b.urutan_idx !== index);
                            this.upahGroups = this.upahGroups.filter(u => u.urutan_idx !== index);
                        }
                    });
                },

                toggleAccordion(index) {
                    this.openAccordions[index] = !this.openAccordions[index];
                },

                addTugas(qcIndex) {
                    this.qcGroups[qcIndex].tugas.push('');
                },

                removeTugas(qcIndex, tugasIndex) {
                    this.qcGroups[qcIndex].tugas.splice(tugasIndex, 1);
                },

                addBahan(indexQC) {
                    this.bahanGroups.push({
                        urutan_idx: indexQC,
                        barang_id: '',
                        jumlah_kebutuhan_standar: 0,
                        satuan_id: ''
                    });
                    this.openAccordions[indexQC] = true;
                },

                removeBahan(index) {
                    this.bahanGroups.splice(index, 1);
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

                removeUpah(index) {
                    this.upahGroups.splice(index, 1);
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
