{{-- Header Info Unit --}}
<div class="mb-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03] shadow-sm">
    <div class="flex flex-col xl:flex-row justify-between gap-6">
        {{-- Sisi Kiri: Identitas & Status Utama --}}
        <div class="flex-1 space-y-4">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Unit {{ $data->unit->nama_unit }}
                    </h2>
                    <span
                        class="px-3 py-1 text-[10px] font-bold uppercase rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                        Tahap {{ $data->tahap->nama_tahap }}
                    </span>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 flex items-center gap-2">
                    <i class="fa-solid fa-location-dot"></i> {{ $data->perumahaan->nama_perumahaan ?? '-' }}
                    <span class="text-gray-300 dark:text-gray-600">|</span>
                    <i class="fa-solid fa-user-gear"></i> {{ $data->pengawas->nama_lengkap ?? '-' }}
                </p>
            </div>

            {{-- Status Badges Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-7">
                {{-- Status Pembangunan --}}
                <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Status
                        Pembangunan
                    </p>
                    <div class="flex items-center gap-2">
                        {{-- Class dan Text sekarang dinamis berdasarkan variabel unitStatus --}}
                        <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold uppercase transition-all duration-300"
                            :class="{
                                'bg-blue-100 text-blue-600': unitStatus === 'proses',
                                'bg-green-100 text-green-600': unitStatus === 'selesai',
                                'bg-amber-100 text-amber-600': unitStatus === 'selesai dengan catatan'
                            }"
                            x-text="unitStatus">
                        </span>

                        {{-- Tombol Lihat Catatan muncul otomatis jika status berubah ke 'selesai dengan catatan' --}}
                        <template x-if="unitStatus === 'selesai dengan catatan'">
                            <button @click="/* Buka Modal */" class="text-indigo-600 hover:text-indigo-700 transition">
                                <i class="fa-solid fa-circle-info animate-pulse"></i>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 relative"
                    x-data="{ openST: false }">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Serah Terima</p>

                    <div class="relative">
                        <button @click="openST = !openST"
                            class="flex items-center gap-2 px-2.5 py-1 rounded-lg text-xs font-bold uppercase transition-all duration-300 border border-transparent hover:border-gray-300 shadow-sm"
                            :class="{
                                'bg-gray-100 text-gray-500': statusST === 'pending',
                                'bg-blue-100 text-blue-600': statusST === 'siap_serah_terima',
                                'bg-emerald-100 text-emerald-600': statusST === 'siap_lpa'
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
                                        :class="statusST === opt ? 'bg-gray-50 text-blue-600' : 'text-gray-600'"
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
        <div class="w-full xl:w-80 flex flex-col justify-between gap-6">
            <div class="p-4 rounded-2xl border transition-all duration-500"
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

            {{-- Tombol Laporan --}}
            <div class="flex gap-2">
                <a href="#"
                    class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 text-xs font-bold rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition shadow-sm">
                    <i class="fa-solid fa-file-invoice text-blue-500"></i>
                    Laporan Termin
                </a>
            </div>
        </div>

    </div>
</div>
