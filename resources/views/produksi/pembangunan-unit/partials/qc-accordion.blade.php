<div class="space-y-4" x-data="{
    selected: new URLSearchParams(window.location.search).get('qc') !== null ? parseInt(new URLSearchParams(window.location.search).get('qc')) : null,
    init() {
        // Auto scroll ke elemen yang terbuka saat load
        if (this.selected !== null) {
            this.$nextTick(() => {
                const el = document.getElementById('qc-card-' + this.selected);
                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        }
    }
}" @open-qc.window="selected = $event.detail">

    <h3 class="text-lg font-bold text-gray-700 dark:text-white px-1">Daftar Quality Control</h3>

    @php
        $qcNumber = 1;
        $regularQcs = $data->pembangunanUnitQc->where('is_servis', false);
        $servisQc = $data->pembangunanUnitQc->where('is_servis', true)->first();
    @endphp

    {{-- Section 1: QC Regular --}}
    @foreach ($regularQcs as $index => $qc)
        <div id="qc-card-{{ $index }}"
            class="rounded-2xl border border-gray-200 bg-white overflow-hidden dark:border-gray-800 dark:bg-white/[0.03]"
            x-data="{
                tab: (new URLSearchParams(window.location.search).get('qc') == {{ $index }} && new URLSearchParams(window.location.search).get('tab')) || 'tasks'
            }">

            {{-- Header QC Klik --}}
            <div class="flex items-center justify-between p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-white/5 transition-all text-gray-700"
                @click="
                    selected !== {{ $index }} ? selected = {{ $index }} : selected = null;
                    $data.updateUrl(selected, tab);
                ">

                <div class="flex flex-col gap-2 w-full mr-3">
                    {{-- Baris 1: Nomor + Nama + Chevron --}}
                    <div class="flex items-center gap-3">
                        <div
                            class="flex-shrink-0 flex items-center justify-center w-7 h-7 rounded-lg bg-blue-600 text-white font-bold text-xs shadow-md">
                            {{ $qcNumber++ }}
                        </div>
                        <h4 class="flex-1 font-bold text-gray-700 dark:text-gray-200 truncate text-sm">{{ $qc->nama_qc }}</h4>
                        <i class="fa-solid fa-chevron-down transition-transform duration-300 text-gray-400 flex-shrink-0"
                            :class="selected === {{ $index }} ? 'rotate-180' : ''"></i>
                    </div>

                    {{-- Baris 2: Progress Bar --}}
                    <div class="flex items-center gap-2">
                        <div class="flex-1 h-1.5 bg-gray-100 rounded-full dark:bg-gray-700 overflow-hidden">
                            @php
                                $initialBarColor = 'bg-blue-600';
                                $qcTasks = $qc->pembangunanUnitQcTask;
                                if ($qcTasks->where('selesai', 0)->count() === 0 && $qcTasks->count() > 0) {
                                    $initialBarColor =
                                        $qcTasks->where('keterangan_selesai', 'sesuai dengan catatan')->count() > 0
                                            ? 'bg-yellow-500'
                                            : 'bg-green-500';
                                }
                            @endphp
                            <div id="bar-qc-{{ $qc->id }}"
                                class="h-full {{ $initialBarColor }} rounded-full transition-all duration-500"
                                style="width: {{ $qc->persentase }}%"></div>
                        </div>
                        <span id="text-qc-{{ $qc->id }}"
                            class="text-xs font-bold text-blue-600 min-w-[30px] text-right">{{ $qc->persentase }}%</span>
                    </div>
                </div>
            </div>

            {{-- Konten Accordion --}}
            <div x-show="selected === {{ $index }}" x-collapse x-cloak>
                <div class="border-t border-gray-100 dark:border-gray-800">
                    <div class="flex border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-white/5 overflow-x-auto">
                        <button @click="tab = 'tasks'; $data.updateUrl({{ $index }}, 'tasks')"
                            :class="tab === 'tasks' ? 'border-blue-600 text-blue-600 bg-white dark:bg-transparent' :
                                'border-transparent text-gray-500 dark:text-gray-400'"
                            class="flex-1 min-w-[80px] py-3 px-2 text-[10px] font-bold border-b-2 uppercase tracking-wider transition-all whitespace-nowrap">
                            <i class="fa-solid fa-list-check me-1"></i> Tugas
                        </button>
                        <button @click="tab = 'bahan'; $data.updateUrl({{ $index }}, 'bahan')"
                            :class="tab === 'bahan' ? 'border-blue-600 text-blue-600 bg-white dark:bg-transparent' :
                                'border-transparent text-gray-500 dark:text-gray-400'"
                            class="flex-1 min-w-[80px] py-3 px-2 text-[10px] font-bold border-b-2 uppercase tracking-wider transition-all whitespace-nowrap">
                            <i class="fa-solid fa-box me-1"></i> Barang
                        </button>
                        <button @click="tab = 'upah'; $data.updateUrl({{ $index }}, 'upah')"
                            :class="tab === 'upah' ? 'border-blue-600 text-blue-600 bg-white dark:bg-transparent' :
                                'border-transparent text-gray-500 dark:text-gray-400'"
                            class="flex-1 min-w-[80px] py-3 px-2 text-[10px] font-bold border-b-2 uppercase tracking-wider transition-all whitespace-nowrap">
                            <i class="fa-solid fa-money-bill-wave me-1"></i> Upah
                        </button>
                    </div>

                    <div class="p-5">
                        @include('produksi.pembangunan-unit.partials.tab-task')
                        @include('produksi.pembangunan-unit.partials.tab-bahan')
                        @include('produksi.pembangunan-unit.partials.tab-upah')
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    {{-- Section 2: Servis Pasca Serah Terima --}}
    @if($servisQc)
        @php
            $servisIndex = $data->pembangunanUnitQc->search(fn($q) => $q->id === $servisQc->id);
        @endphp
        <div class="pt-6 border-t border-gray-200 dark:border-gray-800">
            <h3 class="text-lg font-bold text-gray-700 dark:text-white px-1 mb-3">Servis</h3>
            
            <div id="qc-card-{{ $servisIndex }}"
                class="rounded-2xl border border-gray-200 bg-white overflow-hidden dark:border-gray-800 dark:bg-white/[0.03]"
                x-data="{
                    tab: (new URLSearchParams(window.location.search).get('qc') == {{ $servisIndex }} && new URLSearchParams(window.location.search).get('tab')) || 'bahan'
                }">

                {{-- Header QC Klik --}}
                <div class="flex items-center justify-between p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-white/5 transition-all text-gray-700"
                    @click="
                        selected !== {{ $servisIndex }} ? selected = {{ $servisIndex }} : selected = null;
                        $data.updateUrl(selected, tab);
                    ">

                    <div class="flex items-center gap-3 w-full mr-3">
                        <div class="flex-shrink-0 flex items-center justify-center w-7 h-7 rounded-lg bg-blue-600 text-white font-bold text-xs shadow-md">
                            S    
                        </div>
                        <h4 class="flex-1 font-bold text-gray-700 dark:text-gray-200 truncate text-sm">Servis</h4>
                        <i class="fa-solid fa-chevron-down transition-transform duration-300 text-gray-400 flex-shrink-0"
                            :class="selected === {{ $servisIndex }} ? 'rotate-180' : ''"></i>
                    </div>
                </div>

                {{-- Konten Accordion --}}
                <div x-show="selected === {{ $servisIndex }}" x-collapse x-cloak>
                    <div class="border-t border-gray-100 dark:border-gray-800">
                        <div class="flex border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-white/5 overflow-x-auto">
                            <button @click="tab = 'bahan'; $data.updateUrl({{ $servisIndex }}, 'bahan')"
                                class="flex-1 min-w-[80px] py-3 px-2 text-[10px] font-bold border-b-2 border-blue-600 text-blue-600 bg-white dark:bg-transparent uppercase tracking-wider transition-all whitespace-nowrap">
                                <i class="fa-solid fa-box me-1"></i> Barang
                            </button>
                        </div>

                        @php $qc = $servisQc; @endphp
                        <div class="p-5">
                            @include('produksi.pembangunan-unit.partials.tab-bahan')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
