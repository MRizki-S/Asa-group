@extends('layouts.app')

@section('pageActive', 'pembangunanUnit')

@section('content')
<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6 text-gray-700">
    <div x-data="{ pageName: 'Detail Pembangunan' }">
        @include('partials.breadcrumb')
    </div>

    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-1">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Unit {{ $data->unit->nama_unit }}</h2>
                    <span class="px-3 py-1 text-[10px] font-bold uppercase rounded-full bg-blue-100 text-blue-600">
                        Tahap {{ $data->tahap->nama_tahap }}
                    </span>
                </div>
                <p class="text-sm text-gray-500">{{ $data->perumahaan->nama_perumahaan ?? 'Lembah Hijau Residence' }} • Pengawas: {{ $data->pengawas->nama_lengkap ?? '-' }}</p>
            </div>

            <div class="w-full md:w-72">
                <div class="flex justify-between items-end mb-2">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Progres</span>
                    <span id="total-progress-text" class="text-2xl font-black text-blue-600">{{ $data->total_progres }}%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-3 dark:bg-gray-700 overflow-hidden">
                    <div id="total-progress-bar" class="bg-blue-600 h-3 rounded-full transition-all duration-1000 shadow-[0_0_12px_rgba(37,99,235,0.4)]"
                         style="width: {{ $data->total_progres }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-4" x-data="{ selected: null }">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white px-1">Daftar Quality Control</h3>

        @foreach($data->pembangunanUnitQc as $index => $qc)
        <div class="rounded-2xl border border-gray-200 bg-white overflow-hidden dark:border-gray-800 dark:bg-white/[0.03]"
             x-data="{ tab: 'tasks' }">

            <div class="flex items-center justify-between p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                 @click="selected !== {{ $index }} ? selected = {{ $index }} : selected = null">

                <div class="grid grid-cols-12 gap-4 items-center w-full mr-4">
                    <div class="col-span-12 md:col-span-4 flex items-center gap-3">
                        <div class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-lg bg-blue-600 text-white font-bold text-xs shadow-md">
                            {{ $index + 1 }}
                        </div>
                        <h4 class="font-bold text-gray-800 dark:text-gray-200 truncate text-gray-700">{{ $qc->nama_qc }}</h4>
                    </div>

                    <div class="col-span-10 md:col-span-7 flex items-center gap-3">
                        <div class="flex-1 h-2 bg-gray-100 rounded-full dark:bg-gray-700 overflow-hidden">
                            <div id="bar-qc-{{ $qc->id }}" class="h-full bg-blue-600 rounded-full transition-all duration-500"
                                 style="width: {{ $qc->persentase }}%"></div>
                        </div>
                        <span id="text-qc-{{ $qc->id }}" class="text-xs font-bold text-blue-600 min-w-[35px] text-right">{{ $qc->persentase }}%</span>
                    </div>

                    <div class="col-span-2 md:col-span-1 flex justify-end text-gray-700">
                        <i class="fa-solid fa-chevron-down transition-transform duration-300"
                           :class="selected === {{ $index }} ? 'rotate-180' : ''"></i>
                    </div>
                </div>
            </div>

            <div x-show="selected === {{ $index }}" x-collapse x-cloak>
                <div class="border-t border-gray-100 dark:border-gray-800">

                    <div class="flex border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-white/5">
                        <button @click="tab = 'tasks'" :class="tab === 'tasks' ? 'border-blue-600 text-blue-600 bg-white dark:bg-transparent' : 'border-transparent text-gray-500'"
                                class="flex-1 py-3 text-[10px] font-bold border-b-2 uppercase tracking-widest transition-all">
                            <i class="fa-solid fa-list-check mr-1"></i> Daftar Tugas
                        </button>
                        <button @click="tab = 'bahan'" :class="tab === 'bahan' ? 'border-blue-600 text-blue-600 bg-white dark:bg-transparent' : 'border-transparent text-gray-500'"
                                class="flex-1 py-3 text-[10px] font-bold border-b-2 uppercase tracking-widest transition-all">
                            <i class="fa-solid fa-box mr-1"></i> Bahan
                        </button>
                        <button @click="tab = 'upah'" :class="tab === 'upah' ? 'border-blue-600 text-blue-600 bg-white dark:bg-transparent' : 'border-transparent text-gray-500'"
                                class="flex-1 py-3 text-[10px] font-bold border-b-2 uppercase tracking-widest transition-all">
                            <i class="fa-solid fa-money-bill-wave mr-1"></i> Upah
                        </button>
                    </div>

                    <div class="p-5">
                        <div x-show="tab === 'tasks'" class="space-y-3">
                            @foreach($qc->pembangunanUnitQcTask as $task)
                            <div class="flex flex-col md:flex-row md:items-center justify-between p-4 rounded-xl border border-gray-100 bg-white dark:bg-white/5 dark:border-gray-800 transition-all hover:border-blue-200 shadow-sm"
                                 x-data="{
                                    status: '{{ $task->keterangan_selesai }}',
                                    loading: false,
                                    async saveTask(val) {
                                        this.loading = true;
                                        try {
                                            const res = await axios.post('{{ route('produksi.pembangunanUnit.updateTask', $task->id) }}', { keterangan_selesai: val });
                                            this.status = val;
                                            document.getElementById('bar-qc-{{ $qc->id }}').style.width = res.data.new_qc_percentage + '%';
                                            document.getElementById('text-qc-{{ $qc->id }}').innerText = res.data.new_qc_percentage + '%';
                                            document.getElementById('total-progress-bar').style.width = res.data.new_total_percentage + '%';
                                            document.getElementById('total-progress-text').innerText = res.data.new_total_percentage + '%';
                                        } catch (e) { alert('Gagal menyimpan perubahan.'); }
                                        this.loading = false;
                                    }
                                 }">
                                <div class="flex items-center gap-3">
                                    <div class="w-1.5 h-8 rounded-full flex-shrink-0" :class="{'bg-gray-300': status === 'belum sesuai', 'bg-green-500': status === 'sesuai', 'bg-yellow-500': status === 'sesuai dengan catatan'}"></div>
                                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ $task->tugas }}</p>
                                </div>
                                <div class="flex items-center gap-3 mt-3 md:mt-0">
                                    <template x-if="loading"><i class="fa-solid fa-circle-notch animate-spin text-blue-600 text-xs"></i></template>
                                    <select x-model="status" @change="saveTask($event.target.value)" class="text-xs font-bold rounded-lg border-gray-200 bg-gray-50 p-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-gray-700">
                                        <option value="belum sesuai">Belum Sesuai</option>
                                        <option value="sesuai">Sesuai</option>
                                        <option value="sesuai dengan catatan">Sesuai Dengan Catatan</option>
                                    </select>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div x-show="tab === 'bahan'" class="space-y-3">
                            @forelse($qc->pembangunanUnitRapBahan as $bahan)
                            <div class="flex justify-between items-center p-4 bg-gray-50 dark:bg-white/5 rounded-xl border border-gray-100 dark:border-gray-800">
                                <div>
                                    <p class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ $bahan->nama_barang }}</p>
                                    <p class="text-xs text-gray-500">Qty: {{ $bahan->jumlah_standar }} {{ $bahan->satuan }}</p>
                                </div>
                                <span class="px-3 py-1 text-[10px] font-bold rounded bg-green-100 text-green-700 uppercase">Tersedia</span>
                            </div>
                            @empty
                            <p class="text-center text-xs text-gray-400 py-4 italic">Belum ada data bahan.</p>
                            @endforelse
                        </div>

                        <div x-show="tab === 'upah'" class="space-y-3">
                            @forelse($qc->pembangunanUnitRapUpah as $upah)
                            <div class="flex justify-between items-center p-4 bg-gray-50 dark:bg-white/5 rounded-xl border border-dashed border-gray-300 dark:border-gray-700">
                                <div>
                                    <p class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ $upah->nama_upah }}</p>
                                    <p class="text-xs text-blue-600 font-mono font-bold">Rp {{ number_format($upah->nominal_standar, 0, ',', '.') }}</p>
                                </div>
                                <i class="fa-solid fa-circle-check text-green-500"></i>
                            </div>
                            @empty
                            <p class="text-center text-xs text-gray-400 py-4 italic">Belum ada data upah.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
