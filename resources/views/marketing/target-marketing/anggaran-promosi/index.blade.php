@extends('layouts.app')

@section('pageActive', 'AnggaranPromosi')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-data="anggaranPromosi()">
        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: 'Anggaran Promosi' }">
            @include('partials.breadcrumb')
        </div>
        <!-- Breadcrumb End -->

        <!-- UBS Info Header -->
        <div
            class="mb-6 flex items-center justify-between rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03] shadow-sm">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-600 text-white">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xs font-bold uppercase tracking-widest text-gray-400">UBS Terpilih</h2>
                    <h1 class="text-lg font-bold text-gray-800 dark:text-white/90">
                        {{ $perumahaan->nama_perumahaan ?? 'Pilih Perumahaan' }}
                    </h1>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            <!-- Configuration Form -->
            <div class="lg:col-span-12">
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    <h3
                        class="mb-6 text-base font-bold text-gray-800 dark:text-white/90 border-b border-gray-100 pb-3 dark:border-gray-800">
                        Set Anggaran Promosi
                    </h3>

                    <form @submit.prevent="submitAnggaran" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Tahun Selection -->
                            <div>
                                <label class="mb-2 block text-xs font-bold text-gray-500 uppercase">
                                    Tahun
                                </label>
                                <select x-model="year" @change="loadData"
                                    class="w-full rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm font-medium focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800">
                                    <template x-for="y in Array.from({ length: 6 }, (_, i) => new Date().getFullYear() + i)"
                                        :key="y">
                                        <option :value="y" x-text="y"></option>
                                    </template>
                                </select>
                            </div>

                            <!-- Quarter Selection -->
                            <div>
                                <label class="mb-2 block text-xs font-bold text-gray-500 uppercase">Kuartal</label>
                                <div class="grid grid-cols-4 gap-2">
                                    <template x-for="q in [1,2,3,4]">
                                        <button type="button" @click="quarter = q; loadData()"
                                            :class="quarter == q ? 'bg-blue-600 text-white' :
                                                'bg-gray-50 text-gray-500 border-transparent'"
                                            class="rounded-lg py-3 text-sm font-bold transition-all border"
                                            x-text="'Q' + q">
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Target Anggaran -->
                            <div x-data="rupiahInput(targetAnggaran.toString())" x-init="$watch('targetAnggaran', v => { display = formatRupiah(v.toString()); value = v.toString(); })">
                                <label class="mb-2 block text-xs font-bold text-gray-500 uppercase">Target Anggaran</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-sm font-bold text-gray-400">Rp</span>
                                    </div>
                                    <input type="text" x-model="display" @input="onInput($event); targetAnggaran = value"
                                        class="block w-full rounded-lg border border-gray-200 bg-gray-50 p-3 pl-10 text-lg font-bold text-gray-800 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                        placeholder="0">
                                </div>
                            </div>

                            <!-- Realisasi Anggaran -->
                            <div x-data="rupiahInput(realisasiAnggaran.toString())" x-init="$watch('realisasiAnggaran', v => { display = formatRupiah(v.toString()); value = v.toString(); })">
                                <label class="mb-2 block text-xs font-bold text-gray-500 uppercase">Realisasi Anggaran</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-sm font-bold text-gray-400">Rp</span>
                                    </div>
                                    <input type="text" x-model="display" @input="onInput($event); realisasiAnggaran = value"
                                        :class="parseInt(realisasiAnggaran) > parseInt(targetAnggaran) ? 'bg-red-100 border-red-400 dark:bg-red-900/20 dark:border-red-800' : 'bg-gray-50 border-gray-200 dark:bg-gray-800 dark:border-gray-700'"
                                        class="block w-full rounded-lg border p-3 pl-10 text-lg font-bold text-gray-800 focus:border-blue-500 focus:ring-blue-500 dark:text-white"
                                        placeholder="0">
                                </div>
                            </div>
                        </div>

                        <!-- Catatan -->
                        <div>
                            <label class="mb-2 block text-xs font-bold text-gray-500 uppercase">Catatan</label>
                            <textarea x-model="catatan" rows="4"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm font-medium focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                placeholder="Masukkan catatan atau keterangan anggaran..."></textarea>
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-800">
                            <div>
                                <template x-if="lastUpdate">
                                    <div class="flex items-center gap-1.5 text-xs font-medium text-gray-400 italic">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>Diperbarui oleh <span class="font-bold text-gray-500"
                                                x-text="lastUpdate.user"></span> pada <span
                                                x-text="lastUpdate.date"></span></span>
                                    </div>
                                </template>
                            </div>
                            <button type="submit" :disabled="loading"
                                class="min-w-[200px] rounded-lg bg-blue-600 py-3 px-6 text-sm font-bold text-white hover:bg-blue-700 transition-colors disabled:opacity-50 flex items-center justify-center gap-2">
                                <span x-show="!loading">Simpan Anggaran</span>
                                <span x-show="loading">Menyimpan...</span>
                                <template x-if="loading">
                                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </template>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function anggaranPromosi() {
            return {
                year: new Date().getFullYear(),
                quarter: 1,
                targetAnggaran: 0,
                realisasiAnggaran: 0,
                catatan: '',
                loading: false,
                existingData: @json($anggarans),
                perumahaanId: {{ $perumahaan->id }},
                lastUpdate: null,

                init() {
                    this.loadData();
                },

                formatDate(dateString) {
                    if (!dateString) return '';
                    const date = new Date(dateString);
                    return date.toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                },

                loadData() {
                    const qName = 'Q' + this.quarter;
                    const match = this.existingData.find(t => t.tahun == this.year && t.quarter == qName);

                    if (match) {
                        this.targetAnggaran = Math.floor(match.target_anggaran);
                        this.realisasiAnggaran = Math.floor(match.realisasi_anggaran);
                        this.catatan = match.catatan || '';
                        this.lastUpdate = {
                            user: match.updater ? match.updater.name : 'Unknown',
                            date: this.formatDate(match.updated_at)
                        };
                    } else {
                        this.targetAnggaran = 0;
                        this.realisasiAnggaran = 0;
                        this.catatan = '';
                        this.lastUpdate = null;
                    }
                },

                async submitAnggaran() {
                    this.loading = true;
                    try {
                        const response = await fetch("{{ route('marketing.anggaran-promosi.store') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                perumahaan_id: this.perumahaanId,
                                tahun: this.year,
                                quarter: this.quarter,
                                target_anggaran: this.targetAnggaran,
                                realisasi_anggaran: this.realisasiAnggaran,
                                catatan: this.catatan
                            })
                        });

                        const result = await response.json();

                        if (result.success) {
                            // Update existing data list
                            const index = this.existingData.findIndex(t => t.id === result.data.id);
                            if (index !== -1) {
                                this.existingData[index] = result.data;
                            } else {
                                this.existingData.push(result.data);
                            }

                            this.loadData();

                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: result.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            throw new Error(result.message);
                        }
                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: error.message || 'Terjadi kesalahan sistem.'
                        });
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
@endsection