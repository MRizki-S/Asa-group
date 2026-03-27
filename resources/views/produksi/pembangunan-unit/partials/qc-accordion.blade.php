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
}">

    <h3 class="text-lg font-bold text-gray-700 dark:text-white px-1">Daftar Quality Control</h3>

    @foreach ($data->pembangunanUnitQc as $index => $qc)
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

                <div class="grid grid-cols-12 gap-4 items-center w-full mr-4">
                    <div class="col-span-12 md:col-span-4 flex items-center gap-3">
                        <div
                            class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-lg bg-blue-600 text-white font-bold text-xs shadow-md">
                            {{ $index + 1 }}
                        </div>
                        <h4 class="font-bold text-gray-700 dark:text-gray-200 truncate">{{ $qc->nama_qc }}</h4>
                    </div>

                    <div class="col-span-10 md:col-span-7 flex items-center gap-3">
                        <div class="flex-1 h-2 bg-gray-100 rounded-full dark:bg-gray-700 overflow-hidden">
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
                            class="text-xs font-bold text-blue-600 min-w-[35px] text-right">{{ $qc->persentase }}%</span>
                    </div>

                    <div class="col-span-2 md:col-span-1 flex justify-end text-gray-700">
                        <i class="fa-solid fa-chevron-down transition-transform duration-300"
                            :class="selected === {{ $index }} ? 'rotate-180' : ''"></i>
                    </div>
                </div>
            </div>

            {{-- Konten Accordion --}}
            <div x-show="selected === {{ $index }}" x-collapse x-cloak>
                <div class="border-t border-gray-100 dark:border-gray-800">
                    <div class="flex border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-white/5">
                        <button @click="tab = 'tasks'; $data.updateUrl({{ $index }}, 'tasks')"
                            :class="tab === 'tasks' ? 'border-blue-600 text-blue-600 bg-white dark:bg-transparent' :
                                'border-transparent text-gray-700'"
                            class="flex-1 py-3 text-[10px] font-bold border-b-2 uppercase tracking-widest transition-all">
                            Daftar Tugas
                        </button>
                        <button @click="tab = 'bahan'; $data.updateUrl({{ $index }}, 'bahan')"
                            :class="tab === 'bahan' ? 'border-blue-600 text-blue-600 bg-white dark:bg-transparent' :
                                'border-transparent text-gray-700'"
                            class="flex-1 py-3 text-[10px] font-bold border-b-2 uppercase tracking-widest transition-all">
                            Bahan
                        </button>
                        <button @click="tab = 'upah'; $data.updateUrl({{ $index }}, 'upah')"
                            :class="tab === 'upah' ? 'border-blue-600 text-blue-600 bg-white dark:bg-transparent' :
                                'border-transparent text-gray-700'"
                            class="flex-1 py-3 text-[10px] font-bold border-b-2 uppercase tracking-widest transition-all">
                            Upah
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
</div>
