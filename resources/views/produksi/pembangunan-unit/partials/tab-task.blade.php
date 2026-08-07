<div x-show="tab === 'tasks'" class="space-y-3">
    @foreach ($qc->pembangunanUnitQcTask as $task)
        <div class="flex flex-col md:flex-row md:items-center justify-between p-4 rounded-xl border border-gray-100 bg-white dark:bg-white/5 dark:border-gray-800 transition-all hover:border-blue-200 shadow-sm"
            x-data="{
                status: '{{ $task->keterangan_selesai }}',
                loading: false,
                openModalNote: false,
                async saveTask(val) {
                    this.loading = true;
                    try {
                        const res = await axios.post('{{ route('produksi.pembangunanUnit.updateTask', $task->id) }}', { keterangan_selesai: val });
                        this.status = val;
                        const barElement = document.getElementById('bar-qc-{{ $qc->id }}');
                        if (barElement) {
                            barElement.style.width = res.data.new_qc_percentage + '%';
                            barElement.classList.remove('bg-blue-600', 'bg-green-500', 'bg-yellow-500');
                            barElement.classList.add(res.data.qc_bar_color);
                        }
            
                        document.getElementById('text-qc-{{ $qc->id }}').innerText = res.data.new_qc_percentage + '%';
                        document.getElementById('total-progress-bar').style.width = res.data.new_total_percentage + '%';
                        document.getElementById('total-progress-text').innerText = res.data.new_total_percentage + '%';
                        $dispatch('update-unit-status', res.data.unit_status);
                        $dispatch('update-total-progress', parseFloat(res.data.new_total_percentage));
            
                    } catch (e) { alert('Gagal menyimpan perubahan.'); }
                    this.loading = false;
                }
            }">
            <div class="flex items-center gap-3">
                <div class="w-1.5 h-8 rounded-full flex-shrink-0"
                    :class="{ 'bg-gray-300': status === 'belum sesuai', 'bg-green-500': status === 'sesuai', 'bg-yellow-500': status === 'sesuai dengan catatan' }">
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ $task->tugas }}</p>
                    {{-- Preview Catatan jika ada --}}
                    @if ($task->catatan)
                        <p class="text-[10px] text-blue-600 dark:text-blue-400 font-medium italic">Note:
                            {{ $task->catatan }}</p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-2 mt-3 md:mt-0">
                <template x-if="loading"><i
                        class="fa-solid fa-circle-notch animate-spin text-blue-600 text-xs"></i></template>

                {{-- Tombol Catatan --}}
                <button type="button" @click="openModalNote = true"
                    class="px-3 py-1 text-sm rounded-md border transition-colors {{ $task->catatan ? 'border-blue-200 bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:border-blue-800 dark:text-blue-400' : 'border-gray-200 text-gray-400 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-white/10' }}">
                    Catatan
                </button>

                <select x-model="status" @change="saveTask($event.target.value)"
                    :disabled="['selesai', 'selesai dengan catatan'].includes($data.unitStatus) || {{ auth()->user()->cannot('produksi.properti.pembangunan-unit.edit-task') ? 'true' : 'false' }}"
                    @if (in_array($data->status_pembangunan, ['selesai', 'selesai dengan catatan']) || auth()->user()->cannot('produksi.properti.pembangunan-unit.edit-task')) disabled @endif
                    class="text-xs font-bold rounded-lg border-gray-200 bg-gray-50 p-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 text-gray-700 dark:text-white">
                    <option value="belum sesuai">Belum Sesuai</option>
                    <option value="sesuai">Sesuai</option>
                    <option value="sesuai dengan catatan">Sesuai Dengan Catatan</option>
                </select>
            </div>

            {{-- MODAL (Pakai Form asli biar return back() kerja) --}}
            <template x-teleport="body">
                <div x-show="openModalNote"
                    class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60"
                    x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" x-cloak>

                    <div class="bg-white dark:bg-gray-900 rounded-2xl max-w-md w-full shadow-2xl border border-gray-100 dark:border-gray-800 overflow-hidden"
                        @click.away="openModalNote = false">

                        <form action="{{ route('produksi.pembangunanUnit.updateTaskNote', $task->id) }}" method="POST" x-data="{ submitting: false }" @submit="if(submitting) { $event.preventDefault(); return; }; submitting = true">
                            @csrf
                            <div class="p-6">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="font-bold text-gray-800 dark:text-white">Catatan QC Task</h3>
                                    <button type="button" @click="openModalNote = false"
                                        class="text-gray-400 hover:text-gray-600 dark:hover:text-white transition-colors">
                                        <i class="fa-solid fa-xmark text-lg"></i>
                                    </button>
                                </div>

                                <div
                                    class="p-3 bg-blue-50/50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-800/30 rounded-xl mb-4">
                                    <p class="text-xs font-bold text-blue-700 dark:text-blue-400">
                                        {{ $task->nama_qc }}</p>
                                    <p class="text-[10px] text-gray-500 dark:text-gray-400">
                                        {{ $data->unit->nama_unit ?? 'Unit' }}</p>
                                </div>

                                <label
                                    class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Pesan
                                    Catatan</label>
                                <textarea name="catatan_qc" rows="4"
                                    x-show="!['selesai', 'selesai dengan catatan'].includes($data.unitStatus) && {{ auth()->user()->can('produksi.properti.pembangunan-unit.edit-task') ? 'true' : 'false' }}"
                                    @if (in_array($data->status_pembangunan, ['selesai', 'selesai dengan catatan']) || auth()->user()->cannot('produksi.properti.pembangunan-unit.edit-task')) disabled @endif
                                    class="w-full text-xs rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-black/20 text-gray-800 dark:text-gray-200 focus:ring-blue-500 focus:border-blue-500 p-3 outline-none"
                                    placeholder="Tuliskan catatan perbaikan atau kendala di sini...">{!! old('catatan_qc', $task->catatan_qc) !!}</textarea>

                                <div x-show="['selesai', 'selesai dengan catatan'].includes($data.unitStatus) || {{ auth()->user()->cannot('produksi.properti.pembangunan-unit.edit-task') ? 'true' : 'false' }}"
                                    class="p-3 bg-gray-50 dark:bg-white/5 rounded-xl border border-gray-100 dark:border-gray-800 text-xs text-gray-700 dark:text-gray-300 min-h-[80px]">
                                    {!! old('catatan_qc', $task->catatan_qc) ?: '<span class="text-gray-400 italic">Tidak ada catatan</span>' !!}
                                </div>
                            </div>

                            <div
                                class="px-6 py-4 bg-gray-50 dark:bg-white/5 border-t border-gray-100 dark:border-gray-800 flex justify-end gap-3">
                                <button type="button" @click="openModalNote = false"
                                    class="px-4 py-2 text-xs font-bold text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-white transition-colors">
                                    {{ auth()->user()->can('produksi.properti.pembangunan-unit.edit-task') ? 'BATAL' : 'TUTUP' }}
                                </button>
                                @can('produksi.properti.pembangunan-unit.edit-task')
                                <button type="submit"
                                    :disabled="submitting"
                                    :class="submitting ? 'opacity-50 cursor-not-allowed' : ''"
                                    x-show="!['selesai', 'selesai dengan catatan'].includes($data.unitStatus)"
                                    @if (in_array($data->status_pembangunan, ['selesai', 'selesai dengan catatan'])) style="display:none;" @endif
                                    class="px-5 py-2 text-xs font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-lg shadow-blue-600/20 transition-all">
                                    <span x-text="submitting ? 'MEMPROSES...' : 'SIMPAN CATATAN'"></span>
                                </button>
                                @endcan
                            </div>
                        </form>
                    </div>
                </div>
            </template>
        </div>
    @endforeach
</div>
