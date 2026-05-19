@extends('layouts.app')

@section('pageActive', 'TargetPenjualan')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-data="targetMarketing()">
        <!-- Breadcrumb Start -->
        <div x-data="{ pageName: 'Target Penjualan' }">
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
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
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
            <!-- Left Side: Form Configuration -->
            <div class="lg:col-span-4 space-y-6">
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    <h3
                        class="mb-6 text-base font-bold text-gray-800 dark:text-white/90 border-b border-gray-100 pb-3 dark:border-gray-800">
                        Set Target Penjualan
                    </h3>

                    <form @submit.prevent="submitTarget" class="space-y-5">
                        <!-- Tahun Selection -->
                        <div>
                            <label class="mb-2 block text-xs font-bold text-gray-500 uppercase">
                                Tahun
                            </label>
                            <select x-model="year" @change="loadTargetData"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 p-2.5 text-sm font-medium focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800">

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
                                    <button type="button" @click="quarter = q; updateMonths(); loadTargetData()"
                                        :class="quarter === q ? 'bg-blue-600 text-white' : 'bg-gray-50 text-gray-500 border-transparent'"
                                        class="rounded-lg py-2 text-xs font-bold transition-all border" x-text="'Q' + q">
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Main Target Input -->
                        <div>
                            <label class="mb-2 block text-xs font-bold text-gray-500 uppercase">Total Target (Unit)</label>
                            <div class="relative">
                                <input type="number" x-model="quarterTarget" @input="autoDistribute()"
                                    @cannot('target-marketing.target-penjualan.update') readonly @endcannot
                                    class="block w-full rounded-lg border border-gray-200 bg-gray-50 p-3 text-lg font-bold text-gray-800 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                    placeholder="0">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <span class="text-[10px] font-bold text-gray-400">UNIT</span>
                                </div>
                            </div>
                        </div>

                        @can('target-marketing.target-penjualan.update')
                        <button type="submit" :disabled="loading"
                            class="w-full rounded-lg bg-blue-600 py-3 text-sm font-bold text-white hover:bg-blue-700 transition-colors disabled:opacity-50">
                            <span x-show="!loading">Simpan Target</span>
                            <span x-show="loading">Menyimpan...</span>
                        </button>
                        @endcan
                    </form>
                </div>
            </div>

            <!-- Right Side: Monthly Breakdown -->
            <div class="lg:col-span-8">
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="mb-6 flex items-center justify-between">
                        <h3 class="text-base font-bold text-gray-800 dark:text-white/90">
                            Breakdown Bulanan <span class="text-blue-600" x-text="'Q' + quarter"></span>
                        </h3>
                        <div class="flex flex-col items-end gap-1">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-gray-400">Total:</span>
                                <span class="text-lg font-black text-gray-800 dark:text-white"
                                    x-text="totalDistributed + ' Unit'"></span>
                            </div>
                            <template x-if="lastUpdate">
                                <div class="flex items-center gap-1.5 text-[10px] font-medium text-gray-400 italic">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Diperbarui oleh <span class="font-bold text-gray-500"
                                            x-text="lastUpdate.user"></span> pada <span
                                            x-text="lastUpdate.date"></span></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <template x-for="(month, index) in months" :key="index">
                            <div
                                class="flex items-center gap-4 rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/30">
                                <div class="w-24 shrink-0">
                                    <span class="text-sm font-bold text-gray-700 dark:text-gray-300"
                                        x-text="month.name"></span>
                                </div>
                                <div class="relative flex-1">
                                    <input type="number" x-model.number="month.target" @input="updateFromMonthly()"
                                        @cannot('target-marketing.target-penjualan.update') readonly @endcannot
                                        class="w-full rounded-lg border border-gray-200 bg-white p-2.5 text-sm font-bold text-blue-600 focus:ring-2 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900"
                                        placeholder="0">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <span class="text-[10px] font-bold text-gray-400 uppercase">Unit</span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function targetMarketing() {
            return {
                year: new Date().getFullYear(),
                quarter: 1,
                quarterTarget: 0,
                months: [],
                loading: false,
                existingTargets: @json($targets),
                perumahaanId: {{ $perumahaan->id }},
                lastUpdate: null,

                init() {
                    this.updateMonths();
                    this.loadTargetData();
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

                updateMonths() {
                    const quarterData = {
                        1: [
                            { name: 'Januari', bulan: 1, target: 0 },
                            { name: 'Februari', bulan: 2, target: 0 },
                            { name: 'Maret', bulan: 3, target: 0 }
                        ],
                        2: [
                            { name: 'April', bulan: 4, target: 0 },
                            { name: 'Mei', bulan: 5, target: 0 },
                            { name: 'Juni', bulan: 6, target: 0 }
                        ],
                        3: [
                            { name: 'Juli', bulan: 7, target: 0 },
                            { name: 'Agustus', bulan: 8, target: 0 },
                            { name: 'September', bulan: 9, target: 0 }
                        ],
                        4: [
                            { name: 'Oktober', bulan: 10, target: 0 },
                            { name: 'November', bulan: 11, target: 0 },
                            { name: 'Desember', bulan: 12, target: 0 }
                        ]
                    };
                    this.months = JSON.parse(JSON.stringify(quarterData[this.quarter]));
                },

                loadTargetData() {
                    const qName = 'Q' + this.quarter;
                    const match = this.existingTargets.find(t => t.tahun == this.year && t.quarter == qName);

                    if (match) {
                        this.quarterTarget = match.target_penjualan_quarter;
                        this.months.forEach(m => {
                            const monthData = match.bulanan.find(b => b.bulan == m.bulan);
                            m.target = monthData ? monthData.target_penjualan_bulan : 0;
                        });
                        this.lastUpdate = {
                            user: match.updater ? match.updater.name : 'Unknown',
                            date: this.formatDate(match.updated_at)
                        };
                    } else {
                        this.quarterTarget = 0;
                        this.months.forEach(m => m.target = 0);
                        this.lastUpdate = null;
                    }
                },

                autoDistribute() {
                    if (this.quarterTarget < 0) this.quarterTarget = 0;
                    const base = Math.floor(this.quarterTarget / 3);
                    const remainder = this.quarterTarget % 3;
                    this.months[0].target = base;
                    this.months[1].target = base;
                    this.months[2].target = base + remainder;
                },

                updateFromMonthly() {
                    this.quarterTarget = this.totalDistributed;
                },

                get totalDistributed() {
                    return this.months.reduce((sum, m) => sum + (parseInt(m.target) || 0), 0);
                },

                async submitTarget() {
                    this.loading = true;
                    try {
                        const response = await fetch("{{ route('marketing.target-penjualan.store') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                perumahaan_id: this.perumahaanId,
                                tahun: this.year,
                                quarter: this.quarter,
                                target_penjualan_quarter: this.quarterTarget,
                                monthly_targets: this.months
                            })
                        });

                        const result = await response.json();

                        if (result.success) {
                            // Update existing targets list
                            const index = this.existingTargets.findIndex(t => t.id === result.data.id);
                            if (index !== -1) {
                                this.existingTargets[index] = result.data;
                            } else {
                                this.existingTargets.push(result.data);
                            }

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