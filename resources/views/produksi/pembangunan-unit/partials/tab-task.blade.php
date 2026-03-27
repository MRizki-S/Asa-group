<div x-show="tab === 'tasks'" class="space-y-3">
    @foreach ($qc->pembangunanUnitQcTask as $task)
        <div class="flex flex-col md:flex-row md:items-center justify-between p-4 rounded-xl border border-gray-100 bg-white dark:bg-white/5 dark:border-gray-800 transition-all hover:border-blue-200 shadow-sm"
            x-data="{
                status: '{{ $task->keterangan_selesai }}',
                loading: false,
                async saveTask(val) {
                    this.loading = true;
                    try {
                        const res = await axios.post('{{ route('produksi.pembangunanUnit.updateTask', $task->id) }}', { keterangan_selesai: val });
                        this.status = val;
                        const barElement = document.getElementById('bar-qc-{{ $qc->id }}');
                        barElement.style.width = res.data.new_qc_percentage + '%';
                        barElement.classList.remove('bg-blue-600', 'bg-green-500', 'bg-yellow-500');
                        barElement.classList.add(res.data.qc_bar_color);
            
                        document.getElementById('text-qc-{{ $qc->id }}').innerText = res.data.new_qc_percentage + '%';
                        document.getElementById('total-progress-bar').style.width = res.data.new_total_percentage + '%';
                        document.getElementById('total-progress-text').innerText = res.data.new_total_percentage + '%';
                        $data.unitStatus = res.data.unit_status;
            
                    } catch (e) { alert('Gagal menyimpan perubahan.'); }
                    this.loading = false;
                }
            }">
            <div class="flex items-center gap-3">
                <div class="w-1.5 h-8 rounded-full flex-shrink-0"
                    :class="{ 'bg-gray-300': status === 'belum sesuai', 'bg-green-500': status === 'sesuai', 'bg-yellow-500': status === 'sesuai dengan catatan' }">
                </div>
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ $task->tugas }}</p>
            </div>
            <div class="flex items-center gap-3 mt-3 md:mt-0">
                <template x-if="loading"><i
                        class="fa-solid fa-circle-notch animate-spin text-blue-600 text-xs"></i></template>
                <select x-model="status" @change="saveTask($event.target.value)"
                    class="text-xs font-bold rounded-lg border-gray-200 bg-gray-50 p-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 text-gray-700 dark:text-white">
                    <option value="belum sesuai">Belum Sesuai</option>
                    <option value="sesuai">Sesuai</option>
                    <option value="sesuai dengan catatan">Sesuai Dengan Catatan</option>
                </select>
            </div>
        </div>
    @endforeach
</div>
