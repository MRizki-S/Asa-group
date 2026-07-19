{{-- Header Info Unit --}}
<div class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 md:p-6 dark:border-gray-800 dark:bg-white/[0.03] shadow-sm">
    <div class="flex flex-col lg:flex-row justify-between gap-6 lg:items-stretch">

        {{-- Sisi Kiri: Identitas & Status Utama --}}
        <div class="flex-1 flex flex-col justify-between gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-1.5">
                    <h2 class="text-xl md:text-2xl font-bold text-gray-800 dark:text-white">Unit {{ $data->unit->nama_unit }}</h2>
                    <span class="px-2.5 py-0.5 text-[10px] font-bold uppercase rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                        Tahap {{ $data->tahap->nama_tahap }}
                    </span>
                </div>
                <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400 flex flex-wrap items-center gap-x-3 gap-y-1 mt-1.5">
                    <span><i class="fa-solid fa-location-dot me-1"></i>{{ $data->perumahaan->nama_perumahaan ?? '-' }}</span>
                    <span class="text-gray-300 dark:text-gray-600">|</span>
                    <span><i class="fa-solid fa-user-shield me-1"></i><span class="font-semibold text-gray-600 dark:text-gray-300">SPV:</span> {{ $data->spv->nama_lengkap ?? '-' }}</span>
                    <span class="text-gray-300 dark:text-gray-600">|</span>
                    <span><i class="fa-solid fa-user-gear me-1"></i><span class="font-semibold text-gray-600 dark:text-gray-300">Pengawas:</span> {{ $data->pengawas->nama_lengkap ?? '-' }}</span>
                </p>
            </div>

            {{-- Status Badges Grid --}}
            <div class="grid grid-cols-2 gap-3 mt-2 lg:mt-6 max-w-md">
                {{-- Status Pembangunan --}}
                <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700/60">
                    <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Status</p>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-bold uppercase transition-all duration-300"
                            :class="{
                                'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400': unitStatus === 'proses',
                                'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400': unitStatus === 'selesai',
                                'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400': unitStatus === 'selesai dengan catatan'
                            }"
                            x-text="unitStatus">
                        </span>
                        <template x-if="unitStatus === 'selesai dengan catatan'">
                            <button @click="/* Buka Modal */" class="text-indigo-600 hover:text-indigo-700 transition">
                                <i class="fa-solid fa-circle-info animate-pulse"></i>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Serah Terima --}}
                <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700/60 relative"
                    x-data="{ openST: false }">
                    <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Serah Terima</p>
                    <div class="relative">
                        <button @click="openST = !openST"
                            class="flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-[10px] font-bold uppercase transition-all duration-300 border border-transparent hover:border-gray-300 dark:hover:border-gray-600 shadow-sm"
                            :class="{
                                'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-300': statusST === 'pending',
                                'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400': statusST === 'siap_serah_terima',
                                'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400': statusST === 'siap_lpa'
                            }">
                            <span x-text="statusST.replace(/_/g, ' ')"></span>
                            <i class="fa-solid fa-chevron-down text-[8px] transition-transform"
                                :class="openST ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="openST" @click.away="openST = false" x-transition x-cloak
                            class="absolute left-0 mt-2 w-48 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl z-50 overflow-hidden">
                            <div class="p-1 space-y-1">
                                <template x-for="opt in ['pending', 'siap_serah_terima', 'siap_lpa']">
                                    <button @click="updateStatusST(opt); openST = false"
                                        class="w-full text-left px-3 py-2 text-[10px] font-bold uppercase rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                                        :class="statusST === opt ? 'bg-gray-50 text-blue-600 dark:bg-gray-750 dark:text-blue-400' : 'text-gray-600 dark:text-gray-300'"
                                        x-text="opt.replace(/_/g, ' ')">
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sisi Kanan: Progres & Akses Laporan --}}
        <div class="w-full lg:w-96 shrink-0 flex flex-col justify-between gap-4">
            {{-- Progress Box --}}
            <div class="p-4 rounded-2xl border transition-all duration-500 flex-1 flex flex-col justify-center"
                :class="{
                    'bg-blue-50/50 border-blue-100 dark:bg-blue-900/10 dark:border-blue-900/30': unitStatus === 'proses',
                    'bg-green-50/50 border-green-100 dark:bg-green-900/10 dark:border-green-900/30': unitStatus === 'selesai',
                    'bg-yellow-50/50 border-yellow-100 dark:bg-yellow-900/10 dark:border-yellow-900/30': unitStatus === 'selesai dengan catatan'
                }">
                <div class="flex justify-between items-end mb-2">
                    <span class="text-[10px] font-black uppercase tracking-widest transition-colors duration-500"
                        :class="{
                            'text-blue-600 dark:text-blue-400': unitStatus === 'proses',
                            'text-green-600 dark:text-green-400': unitStatus === 'selesai',
                            'text-yellow-600 dark:text-yellow-400': unitStatus === 'selesai dengan catatan'
                        }">Penyelesaian</span>
                    <span id="total-progress-text" class="text-2xl font-black transition-colors duration-500"
                        :class="{
                            'text-blue-600 dark:text-blue-400': unitStatus === 'proses',
                            'text-green-600 dark:text-green-400': unitStatus === 'selesai',
                            'text-yellow-600 dark:text-yellow-400': unitStatus === 'selesai dengan catatan'
                        }">
                        {{ $data->total_progres }}%
                    </span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                    <div id="total-progress-bar" class="h-full rounded-full transition-all duration-1000 shadow-sm"
                        :class="{
                            'bg-blue-600': unitStatus === 'proses',
                            'bg-green-500': unitStatus === 'selesai',
                            'bg-yellow-500': unitStatus === 'selesai dengan catatan'
                        }"
                        style="width: {{ $data->total_progres }}%"></div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex gap-2">
                {{-- Laporan Dropdown --}}
                <div class="relative flex-1" x-data="{ openReportDropdown: false }">
                    <button @click="openReportDropdown = !openReportDropdown"
                        class="w-full inline-flex items-center justify-between gap-2 px-4 py-2.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 text-xs font-bold rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition shadow-sm">
                        <span>Laporan</span>
                        <i class="fa-solid fa-chevron-down transition-transform" :class="openReportDropdown ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="openReportDropdown" @click.away="openReportDropdown = false" x-transition x-cloak
                        class="absolute left-0 mt-2 w-48 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl z-50 overflow-hidden text-left">
                        <div class="p-1 space-y-1">
                            <a href="{{ route('produksi.pembangunanUnit.laporanBahan', ['id' => $data->id]) }}"
                                class="block px-3 py-2 text-xs font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                                <i class="fa-solid fa-cubes mr-1.5 text-blue-500"></i> Laporan Bahan
                            </a>
                            <a href="{{ route('produksi.pembangunanUnit.laporanUpah', ['id' => $data->id]) }}"
                                class="block px-3 py-2 text-xs font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                                <i class="fa-solid fa-money-bill-wave mr-1.5 text-green-500"></i> Laporan Upah
                            </a>
                            <a href="{{ route('produksi.pembangunanUnit.laporanTermin.export', $data->id) }}"
                                class="block px-3 py-2 text-xs font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                                <i class="fa-solid fa-file-invoice-dollar mr-2 text-purple-500"></i> Laporan Termin
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Selesaikan --}}
                @php
                    $isCompleted = in_array($data->status_pembangunan, ['selesai', 'selesai dengan catatan']);
                    $isProgressNotComplete = $data->total_progres < 100;
                    $isDisabled = $isCompleted || $isProgressNotComplete;
                @endphp
                <form action="{{ route('produksi.pembangunanUnit.update', $data->id) }}" method="POST" class="flex-1"
                    x-data="{ submitting: false, confirming: false }"
                    @submit.prevent="
                        if (confirming || submitting) return;
                        confirming = true;
                        Swal.fire({
                            title: 'Konfirmasi',
                            text: 'Apakah Anda yakin ingin menyelesaikan pembangunan unit ini?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#059669',
                            confirmButtonText: 'Ya, Selesaikan',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            confirming = false;
                            if (result.isConfirmed) {
                                submitting = true;
                                $el.submit();
                            }
                        })
                    ">
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                        :disabled="submitting || ['selesai', 'selesai dengan catatan'].includes(unitStatus) || totalProgress < 100"
                        @if ($isDisabled) disabled @endif
                        class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs font-bold rounded-xl shadow-sm transition-all duration-300
                        {{ $isDisabled ? 'bg-gray-300 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed' : 'bg-green-600 text-white hover:bg-green-700 active:scale-95 cursor-pointer' }}"
                        :class="(submitting || ['selesai', 'selesai dengan catatan'].includes(unitStatus) || totalProgress < 100)
                            ? 'bg-gray-300 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed'
                            : 'bg-green-600 text-white hover:bg-green-700 active:scale-95 cursor-pointer'">
                        <i class="fa-solid fa-circle-check"></i>
                        <span x-text="submitting ? 'Memproses...' : 'Selesaikan'"></span>
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
