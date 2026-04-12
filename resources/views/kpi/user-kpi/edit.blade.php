@extends('layouts.app')

@section('pageActive', 'User-KPI')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-data="kpiCalculator()">
        <div x-data="{ pageName: '{{ $kpiUser->status == 'final' ? 'Detail Nilai KPI' : 'Input Nilai KPI' }}: {{ $kpiUser->user->nama_lengkap }}' }">
            @include('partials.breadcrumb')
        </div>

        {{-- Row Header (Karyawan, Periode, dll) --}}
        <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Karyawan</p>
                <p class="text-sm font-bold text-gray-800 dark:text-white">{{ $kpiUser->user->nama_lengkap }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Periode</p>
                <p class="text-sm font-bold text-gray-800 dark:text-white">
                    {{ date('F', mktime(0, 0, 0, $kpiUser->bulan, 1)) }} {{ $kpiUser->tahun }}
                </p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Total Nilai Akhir</p>
                <p class="text-sm font-bold text-gray-800 dark:text-white">{{ (float) $kpiUser->total_nilai }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Status Penilaian</p>
                <span
                    class="inline-block text-[10px] font-bold px-2 py-0.5 rounded {{ $kpiUser->status == 'final' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }} uppercase tracking-wider">
                    {{ $kpiUser->status }}
                </span>
            </div>
        </div>

        <form action="{{ route('kpi.user.update', $kpiUser->id) }}" method="POST">
            @csrf @method('PUT')

            <div class="space-y-6">
                @foreach ($kpiUser->details as $komponen)
                    <div
                        class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden shadow-sm">
                        <div
                            class="bg-gray-50 dark:bg-gray-900/50 px-6 py-4 border-b border-gray-200 dark:border-gray-800 flex justify-between items-center">
                            <div>
                                <h3 class="font-bold text-gray-800 dark:text-white">{{ $komponen->nama_komponen }}</h3>
                                <p class="text-[10px] text-gray-500 font-medium italic">Bobot: {{ $komponen->bobot }}% |
                                    Tipe: {{ $komponen->komponen->tipe_perhitungan }}</p>
                            </div>
                            <div class="flex items-center gap-6">
                                <div class="text-right">
                                    <span class="text-[10px] text-gray-400 uppercase font-bold block">Kepatuhan</span>
                                    <span
                                        class="text-base font-black text-blue-600">{{ number_format($komponen->kepatuhan_percent, 1) }}%</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] text-gray-400 uppercase font-bold block">Skor</span>
                                    <span class="text-base font-black text-green-600">{{ (float) $komponen->skor }}</span>
                                </div>
                                <div class="text-right border-l pl-6 border-gray-200 dark:border-gray-700">
                                    <span class="text-[10px] text-orange-500 uppercase font-bold block">Nilai</span>
                                    <span
                                        class="text-base font-black text-orange-600">{{ (float) $komponen->nilai_akhir }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                @php
                                    $tipePerhitungan = $komponen->komponen->tipe_perhitungan;
                                    $mode = $modeMapping[$tipePerhitungan] ?? 'range';
                                @endphp
                                <thead
                                    class="bg-gray-50/50 dark:bg-white/[0.01] border-b border-gray-200 dark:border-gray-800">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase w-16">
                                            No</th>
                                        <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase">Jenis
                                            Data / Task</th>
                                        @if ($mode != 'select')
                                            <th
                                                class="px-6 py-3 text-center text-[10px] font-bold text-gray-400 uppercase w-40">
                                                {{ $komponen->komponen->label_total }}</th>
                                        @endif
                                        <th
                                            class="px-6 py-3 text-center text-[10px] font-bold text-gray-400 uppercase w-40">
                                            {{ $komponen->komponen->label_tercapai }}</th>
                                        @if ($mode != 'select')
                                            <th
                                                class="px-6 py-3 text-center text-[10px] font-bold text-gray-400 uppercase w-40">
                                                {{ $komponen->komponen->label_tidak_tercapai }}</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @foreach ($komponen->tasks as $index => $task)
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition-colors">
                                            <td class="px-6 py-4 text-xs text-gray-400">{{ $index + 1 }}</td>
                                            <td class="px-6 py-4 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                {{ $task->nama_task }}</td>

                                            @if ($mode != 'select')
                                                <td class="px-6 py-3 text-center">
                                                    @if ($kpiUser->status == 'final')
                                                        <span
                                                            class="text-sm font-bold text-gray-700 dark:text-white">{{ number_format($task->target, 0, ',', '.') }}</span>
                                                    @else
                                                        <input type="text"
                                                            name="task[{{ $komponen->id }}][{{ $task->id }}][target]"
                                                            x-model="values[{{ $task->id }}].target"
                                                            @input="values[{{ $task->id }}].target = formatDisplay($event.target.value)"
                                                            class="w-full text-center bg-gray-50 dark:bg-gray-800 border-none rounded-lg text-sm font-bold text-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 py-1.5 transition">
                                                    @endif
                                                </td>
                                            @endif

                                            <td class="px-6 py-3 text-center">
                                                @if ($mode === 'select')
                                                    @if ($kpiUser->status == 'final')
                                                        @php $selectedOpt = $indicators->where('tipe_perhitungan', $tipePerhitungan)->where('nilai', $task->nilai)->first(); @endphp
                                                        <span
                                                            class="text-sm font-bold text-gray-700 dark:text-white">{{ $selectedOpt->option ?? '-' }}
                                                            ({{ (float) $task->nilai }})
                                                        </span>
                                                    @else
                                                        <select
                                                            name="task[{{ $komponen->id }}][{{ $task->id }}][nilai]"
                                                            class="w-full text-center bg-gray-50 dark:bg-gray-800 border-none rounded-lg text-sm font-bold text-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 py-1.5 transition">
                                                            <option value="0">-- Pilih --</option>
                                                            @foreach ($indicators->where('tipe_perhitungan', $tipePerhitungan) as $opt)
                                                                <option value="{{ $opt->nilai }}"
                                                                    {{ old("task.{$komponen->id}.{$task->id}.nilai", $task->nilai) == $opt->nilai ? 'selected' : '' }}>
                                                                    ({{ (float) $opt->nilai }})
                                                                    {{ $opt->option }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <input type="hidden"
                                                            name="task[{{ $komponen->id }}][{{ $task->id }}][target]"
                                                            value="100">
                                                    @endif
                                                @else
                                                    @if ($kpiUser->status == 'final')
                                                        <span
                                                            class="text-sm font-bold text-gray-700 dark:text-white">{{ number_format($task->tercapai, 0, ',', '.') }}</span>
                                                    @else
                                                        <input type="text"
                                                            name="task[{{ $komponen->id }}][{{ $task->id }}][tercapai]"
                                                            x-model="values[{{ $task->id }}].tercapai"
                                                            @input="values[{{ $task->id }}].tercapai = formatDisplay($event.target.value)"
                                                            class="w-full text-center bg-gray-50 dark:bg-gray-800 border-none rounded-lg text-sm font-bold text-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 py-1.5 transition">
                                                    @endif
                                                @endif
                                            </td>

                                            @if ($mode != 'select')
                                                <td
                                                    class="px-6 py-4 text-center text-sm font-black text-gray-500 dark:text-gray-400">
                                                    <span x-text="calculateDiff({{ $task->id }})"></span>
                                                </td>
                                            @endif
                                        </tr>

                                        @if ($mode != 'select')
                                            <tr x-show="isDifferent({{ $task->id }})"
                                                class="bg-red-50/50 dark:bg-red-900/10">
                                                <td colspan="2"></td>
                                                <td colspan="3" class="px-6 py-3">
                                                    <label
                                                        class="block text-[10px] font-bold text-red-500 uppercase mb-1">Alasan
                                                        Tidak Tercapai:</label>
                                                    @if ($kpiUser->status == 'final')
                                                        <p class="text-sm text-gray-700 dark:text-white italic">
                                                            {{ $task->alasan_tidak_tercapai ?? '-' }}</p>
                                                    @else
                                                        <textarea name="task[{{ $komponen->id }}][{{ $task->id }}][alasan_tidak_tercapai]"
                                                            class="w-full rounded-lg border-red-200 dark:border-red-900/30 bg-white dark:bg-gray-900 text-sm text-gray-700 dark:text-white focus:ring-red-500"
                                                            rows="1" placeholder="Tulis alasan disini...">{{ $task->alasan_tidak_tercapai }}</textarea>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="p-5 bg-gray-50/30 dark:bg-white/[0.01] border-t border-gray-100 dark:border-gray-800">
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-2">Penyebab Tidak Tercapai
                                / Catatan Tambahan</label>
                            @if ($kpiUser->status == 'final')
                                <p class="text-sm text-gray-700 dark:text-white italic">
                                    {{ $komponen->catatan_tambahan ?? '-' }}</p>
                            @else
                                <textarea name="catatan[{{ $komponen->id }}]" rows="2"
                                    class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-700 dark:text-white text-sm focus:border-blue-500 focus:ring-blue-500 transition-all"
                                    placeholder="Tulis alasan atau evaluasi di sini...">{{ $komponen->catatan_tambahan }}</textarea>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div
                class="mt-8 flex flex-col w-full md:flex-row justify-between items-center gap-4 bg-white dark:bg-white/[0.03] p-6 rounded-2xl border border-gray-200 dark:border-gray-800 mb-10">
                <div class="flex flex-col w-full md:flex-row justify-start items-center gap-3">
                    @if ($kpiUser->status != 'final')
                        <label class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Simpan
                            sebagai:</label>
                        <select name="status"
                            class="rounded-lg py-2 border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-800 text-gray-700 dark:text-white text-sm font-bold focus:ring-blue-500">
                            <option value="draft" {{ $kpiUser->status == 'draft' ? 'selected' : '' }}>DRAFT</option>
                            <option value="final" {{ $kpiUser->status == 'final' ? 'selected' : '' }}
                                x-bind:disabled="hasZeroKepatuhan()">
                                FINAL
                            </option>
                        </select>
                        <template x-if="hasZeroKepatuhan()">
                            <p class="text-[10px] text-red-500 font-bold italic">* Skor Kepatuhan 0 ditemukan. Status FINAL
                                terkunci.</p>
                        </template>
                    @else
                        <p class="text-sm font-bold text-green-600 uppercase italic">Penilaian ini telah difinalisasi.</p>
                    @endif
                </div>
                <div class="flex w-full flex-col-reverse md:flex-row justify-end gap-3">
                    <a href="{{ route('kpi.user.index') }}"
                        class="px-6 py-2 w-full md:w-fit text-sm text-center font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition">Kembali</a>
                    @if ($kpiUser->status != 'final')
                        <a href="{{ route('kpi.request.review', $kpiUser->id) }}" x-show="hasZeroKepatuhan()"
                            class="px-6 py-2 w-full md:w-fit text-sm text-center font-medium text-white bg-orange-500 rounded-lg hover:bg-orange-600 transition">
                            Mita Review
                        </a>
                        <button type="submit"
                            class="px-6 py-2 w-full md:w-fit text-sm text-center font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Simpan
                            Nilai</button>
                    @else
                        <a href="{{ route('kpi.user.exportExcel', $kpiUser->id) }}"
                            class="px-3 py-2 w-full md:w-fit text-sm text-center font-medium text-white bg-green-600 rounded-md hover:bg-green-700 transition">
                            Export Excel
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <script>
        function kpiCalculator() {
            return {
                values: {
                    @foreach ($kpiUser->details as $komponen)
                        @foreach ($komponen->tasks as $task)
                            {{ $task->id }}: {
                                target: "{{ number_format($task->target, 0, ',', '.') }}",
                                tercapai: "{{ number_format($task->tercapai, 0, ',', '.') }}",
                                nilai: "{{ $task->nilai ?? 0 }}",
                            },
                        @endforeach
                    @endforeach
                },
                komponenScores: [
                    @foreach ($kpiUser->details as $komponen)
                        {
                            tipe: "{{ $komponen->komponen->tipe_perhitungan }}",
                            skor: {{ $komponen->skor ?? 0 }}
                        },
                    @endforeach
                ],
                formatDisplay(value) {
                    if (!value && value !== 0) return '';
                    let str = value.toString().replace(/\D/g, '');
                    return str.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                },
                calculateDiff(id) {
                    let target = parseInt(this.values[id].target.replace(/\./g, '')) || 0;
                    let tercapai = parseInt(this.values[id].tercapai.replace(/\./g, '')) || 0;
                    return this.formatDisplay(target - tercapai);
                },
                isDifferent(id) {
                    let target = parseInt(this.values[id].target.replace(/\./g, '')) || 0;
                    let tercapai = parseInt(this.values[id].tercapai.replace(/\./g, '')) || 0;
                    return target !== tercapai;
                },
                hasZeroKepatuhan() {
                    return this.komponenScores.some(k => k.tipe === 'KEPATUHAN' && k.skor <= 0);
                },
            }
        }
    </script>
@endsection
