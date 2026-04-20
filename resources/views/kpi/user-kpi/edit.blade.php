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
                <p class="text-sm font-bold text-gray-800 dark:text-white"
                    x-text="results._total ?? {{ (float) $kpiUser->total_nilai }}">
                </p>
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
                            @if ($komponen->nilai_tetap)
                                <span
                                    class="flex items-center gap-1 px-2 py-1 rounded-full bg-green-100 dark:bg-green-900/30 text-[10px] font-bold text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    DINILAI MANAGER
                                </span>
                            @endif
                            <div class="flex gap-7">
                                <div class="text-right">
                                    <span class="text-[10px] text-gray-400 uppercase font-bold block">Kepatuhan</span>
                                    <span class="text-base font-black text-blue-600"
                                        x-text="(results[{{ $komponen->id }}]?.persen ?? {{ number_format($komponen->kepatuhan_percent, 1) }}) + '%'">
                                    </span>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] text-gray-400 uppercase font-bold block">Skor</span>
                                    <span class="text-base font-black text-green-600"
                                        x-text="results[{{ $komponen->id }}]?.skor ?? {{ (float) $komponen->skor }}">
                                    </span>
                                </div>
                                <div class="text-right border-l pl-6 border-gray-200 dark:border-gray-700">
                                    <span class="text-[10px] text-orange-500 uppercase font-bold block">Nilai</span>
                                    <span class="text-base font-black text-orange-600"
                                        x-text="results[{{ $komponen->id }}]?.nilaiAkhir ?? {{ (float) $komponen->nilai_akhir }}">
                                    </span>
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
                                                class="px-6 py-3 text-center text-[10px] font-bold text-gray-400 uppercase {{ $komponen->komponen->tipe_perhitungan == 'DEVIASI_BUDGET' ? 'w-60' : 'w-40' }}">
                                                {{ $komponen->komponen->label_total }}</th>
                                        @endif
                                        <th
                                            class="px-6 py-3 text-center text-[10px] font-bold text-gray-400 uppercase
    {{ $komponen->komponen->tipe_perhitungan == 'DEVIASI_BUDGET' ? 'w-60' : ($mode == 'select' ? 'w-80' : 'w-40') }}">
                                            {{ $komponen->komponen->label_tercapai }}
                                        </th>
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
                                                            @if ($komponen->nilai_tetap) readonly @endif
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
                                                            x-model="values[{{ $task->id }}].nilai"
                                                            @if ($komponen->nilai_tetap) disabled @endif
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
                                                            @if ($komponen->nilai_tetap) readonly @endif
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

            @if ($prosesReview)
                <div class="bg-blue-50 mt-4 border-l-4 border-blue-400 p-4 mb-4" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700 font-medium">
                                Sedang dalam proses review manajer. Harap menunggu hingga proses ini selesai untuk melakukan
                                finalisasi.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <div
                class="mt-4 flex flex-col w-full md:flex-row justify-between items-center gap-4 bg-white dark:bg-white/[0.03] p-6 rounded-2xl border border-gray-200 dark:border-gray-800 mb-10">
                <div class="flex flex-col w-full md:flex-row justify-start items-center gap-3">
                    @if ($kpiUser->status != 'final')
                        @can('kpi.kpi-user.update-simpan-nilai')
                            <label class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Simpan
                                sebagai:</label>
                            <select name="status"
                                class="rounded-lg py-2 border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-800 text-gray-700 dark:text-white text-sm font-bold focus:ring-blue-500">
                                <option value="draft" {{ $kpiUser->status == 'draft' ? 'selected' : '' }}>DRAFT</option>
                                <option value="final" {{ $kpiUser->status == 'final' ? 'selected' : '' }}
                                    x-bind:disabled="hasProblem()">
                                    FINAL
                                </option>
                            </select>
                        @endcan
                    @else
                        <p class="text-sm font-bold text-green-600 uppercase italic">Penilaian ini telah difinalisasi.</p>
                    @endif
                </div>
                <div class="flex w-full flex-col-reverse md:flex-row justify-end gap-3">
                    <a href="{{ route('kpi.user.index') }}"
                        class="px-6 py-2 w-full md:w-fit text-sm text-center font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition">Kembali</a>
                    @if ($kpiUser->status != 'final')
                        @can('kpi.kpi-user.minta-riview')
                            <a href="{{ route('kpi.request.review', $kpiUser->id) }}" x-show="hasProblem()"
                                @if (!$bolehRequest) onclick="return false;" @endif
                                class="px-6 py-2 w-full md:w-fit text-sm text-center font-medium rounded-lg transition
                {{ $bolehRequest
                    ? 'text-white bg-orange-500 hover:bg-orange-600 cursor-pointer'
                    : 'text-gray-400 bg-gray-100 dark:bg-gray-800 dark:text-gray-600 cursor-not-allowed border border-gray-200 dark:border-gray-700' }}">
                                Minta Review
                            </a>
                        @endcan
                        @can('kpi.kpi-user.update-simpan-nilai')
                            <button type="submit"
                                class="px-6 py-2 w-full md:w-fit text-sm text-center font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                                Simpan Nilai
                            </button>
                        @endcan
                    @else
                        @can('kpi.kpi-user.export')
                            <a href="{{ route('kpi.user.exportExcel', $kpiUser->id) }}"
                                class="px-3 py-2 w-full md:w-fit text-sm text-center font-medium text-white bg-green-600 rounded-md hover:bg-green-700 transition">
                                Export Excel
                            </a>
                        @endcan
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

                // Data komponen lengkap untuk kalkulasi
                komponenData: [
                    @foreach ($kpiUser->details as $komponen)
                        {
                            id: {{ $komponen->id }},
                            bobot: {{ $komponen->bobot }},
                            tipe: "{{ $komponen->komponen->tipe_perhitungan }}",
                            taskIds: [{{ $komponen->tasks->pluck('id')->join(', ') }}],
                            taskCount: {{ $komponen->tasks->count() }},
                            isFixed: {{ $komponen->nilai_tetap ? 'true' : 'false' }},
                            fixedData: {
                                persen: {{ $komponen->kepatuhan_percent ?? 0 }},
                                skor: {{ $komponen->skor ?? 0 }},
                                nilaiAkhir: {{ $komponen->nilai_akhir ?? 0 }}
                            }
                        },
                    @endforeach
                ],

                // Data indikator/lookup untuk skor
                indicators: @json($indicators),

                get results() {
                    let out = {};
                    let totalNilaiAkhir = 0;

                    for (const k of this.komponenData) {

                        if (k.isFixed) {
                            out[k.id] = {
                                persen: k.fixedData.persen,
                                skor: k.fixedData.skor,
                                nilaiAkhir: k.fixedData.nilaiAkhir
                            };
                            totalNilaiAkhir += k.fixedData.nilaiAkhir;
                            continue;
                        }

                        let totalTarget = 0,
                            totalTercapai = 0,
                            totalNilaiSelect = 0;

                        for (const tid of k.taskIds) {
                            const v = this.values[tid];
                            const nilaiSelect = parseFloat(v.nilai) || 0;

                            if (k.tipe === 'KONDISI_LANGSUNG' || k.tipe === 'AKKUMULASI_NILAI') {
                                totalNilaiSelect += nilaiSelect;
                            } else {
                                totalTarget += this.parseNum(v.target);
                                totalTercapai += this.parseNum(v.tercapai);
                            }
                        }

                        let skor = 0,
                            persen = 0;

                        if (k.tipe === 'KONDISI_LANGSUNG') {
                            // Skor langsung dari nilai select (biasanya 1 task)
                            skor = totalNilaiSelect;
                            persen = skor;

                        } else if (k.tipe === 'AKKUMULASI_NILAI') {
                            // Persen = rata-rata nilai select, lalu lookup ke KEPATUHAN
                            persen = k.taskCount > 0 ? (totalNilaiSelect / (k.taskCount * 100)) * 100 : 0;
                            skor = this.lookupSkor('KEPATUHAN', persen);

                        } else if (k.tipe === 'SELISIH_STOK') {
                            const selisih = Math.abs(totalTarget - totalTercapai);
                            persen = totalTarget > 0 ? (selisih / totalTarget) * 100 : 0;
                            skor = this.lookupSkor(k.tipe, persen);

                        } else {
                            // KEPATUHAN, DEVIASI_BUDGET, range default
                            persen = totalTarget > 0 ? (totalTercapai / totalTarget) * 100 : 0;
                            skor = this.lookupSkor(k.tipe, persen);
                        }

                        const nilaiAkhir = (k.bobot / 100) * skor;
                        totalNilaiAkhir += nilaiAkhir;

                        out[k.id] = {
                            persen: Math.round(persen * 10) / 10,
                            skor: Math.round(skor * 100) / 100,
                            nilaiAkhir: Math.round(nilaiAkhir * 100) / 100,
                        };
                    }

                    out._total = Math.round(totalNilaiAkhir * 100) / 100;
                    return out;
                },

                lookupSkor(tipe, nilai) {
                    const nilaiRounded = Math.round(nilai * 10) / 10;
                    const list = this.indicators.filter(i => i.tipe_perhitungan === tipe);

                    const rule = list.find(i => {
                        const bb = i.batas_bawah !== null ? parseFloat(i.batas_bawah) : -999999;
                        const ba = i.batas_atas !== null ? parseFloat(i.batas_atas) : 999999;
                        return nilaiRounded >= bb && nilaiRounded <= ba;
                    });

                    return rule ? parseFloat(rule.skor) : 0;
                },

                parseNum(val) {
                    if (!val && val !== 0) return 0;
                    return parseFloat(val.toString().replace(/\./g, '').replace(',', '.')) || 0;
                },

                formatDisplay(value) {
                    if (!value && value !== 0) return '';
                    let str = value.toString().replace(/\D/g, '');
                    return str.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                },

                calculateDiff(id) {
                    let target = this.parseNum(this.values[id].target);
                    let tercapai = this.parseNum(this.values[id].tercapai);
                    return this.formatDisplay(target - tercapai);
                },

                isDifferent(id) {
                    let target = this.parseNum(this.values[id].target);
                    let tercapai = this.parseNum(this.values[id].tercapai);
                    return target !== tercapai;
                },

                hasProblem() {
                    return this.komponenData.some(k => {
                        if (k.tipe !== 'KEPATUHAN' && k.tipe !== 'AKKUMULASI_NILAI') return false;
                        if (k.isFixed) return false;
                        const hasil = this.results[k.id];
                        return hasil && hasil.persen !== null && hasil.persen < 90;
                    });
                },
            }
        }
    </script>
@endsection
