@extends('layouts.app')

@section('pageActive', 'User-KPI')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-data="kpiCalculator()">
        <div x-data="{ pageName: '{{ $isReadOnly ? 'Detail Nilai KPI' : ($isManager && $kpiUser->isMenungguReview() ? 'Review Nilai KPI' : 'Input Nilai KPI') }}: {{ $kpiUser->karyawan->nama }}' }">
            @include('partials.breadcrumb')
        </div>

        {{-- Alert Error --}}
        @if ($errors->any())
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan',
                        html: `<ul class="text-left list-disc list-inside text-sm">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>`,
                        showConfirmButton: true
                    });
                });
            </script>
        @endif

        {{-- Banner Permintaan Revisi dari Manajer --}}
        @if ($kpiUser->status == 'revisi')
            <div class="mb-6 p-4 sm:p-5 rounded-2xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900/50 flex items-start gap-3 shadow-sm">
                <div class="p-2 rounded-xl bg-red-100 dark:bg-red-900/60 text-red-600 dark:text-red-300 shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h4 class="text-sm font-bold text-red-800 dark:text-red-300">Penilaian Memerlukan Revisi dari Manajer</h4>
                    <p class="text-xs text-red-700 dark:text-red-400 mt-1 leading-relaxed">
                        <span class="font-bold">Catatan Revisi:</span> "{{ $kpiUser->catatan_revisi }}"
                    </p>
                    <p class="text-[11px] text-red-600/80 dark:text-red-400/80 mt-1.5 italic">
                        Silakan perbaiki data penilaian di bawah ini lalu klik tombol <strong>Posting ke Manager</strong> setelah selesai.
                    </p>
                </div>
            </div>
        @endif

        {{-- Banner Status Menunggu Review (Read-only bagi Admin) --}}
        @if ($kpiUser->status == 'menunggu_review' && $isReadOnly)
            <div class="mb-6 p-4 rounded-2xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 flex items-center gap-3">
                <div class="p-2 rounded-xl bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300 shrink-0">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <p class="text-sm text-blue-700 dark:text-blue-300 font-medium">
                    Penilaian ini sedang dalam proses review oleh Manajer Strategi & Kepatuhan dan berstatus read-only (tidak dapat diedit).
                </p>
            </div>
        @endif

        {{-- Banner Mode Review Manajer --}}
        @if ($kpiUser->status == 'menunggu_review' && $isManager && !$isReadOnly)
            <div class="mb-6 p-4 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-900/50 flex items-start gap-3 shadow-sm">
                <div class="p-2 rounded-xl bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-300 shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm text-indigo-700 dark:text-indigo-300 font-medium">
                        <span class="font-bold">Mode Review Manajer:</span> Anda dapat mengoreksi target & capaian task, atau melakukan penyesuaian skor secara manual (0, 70, 100) per komponen sebelum finalisasi.
                    </p>
                    @if (!empty($kpiUser->catatan_revisi))
                        <div class="mt-2 text-xs text-indigo-900 dark:text-indigo-200 bg-white/70 dark:bg-gray-800/70 p-2.5 rounded-xl border border-indigo-200 dark:border-indigo-800">
                            <span class="font-bold">Catatan Permintaan Revisi Sebelumnya:</span> "{{ $kpiUser->catatan_revisi }}"
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Row Header (Karyawan, Periode, Total Nilai, Status) --}}
        <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Karyawan</p>
                <p class="text-sm font-bold text-gray-800 dark:text-white">{{ $kpiUser->karyawan->nama }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $kpiUser->karyawan->role?->name ?? '-' }}</p>
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
                    x-text="results._total ?? {{ round((float) $kpiUser->total_nilai) }}">
                </p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Status Penilaian</p>
                <div>
                    @if ($kpiUser->status == 'draft')
                        <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 uppercase tracking-wider">
                            Draft
                        </span>
                    @elseif ($kpiUser->status == 'menunggu_review')
                        <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 uppercase tracking-wider">
                            Menunggu Review
                        </span>
                    @elseif ($kpiUser->status == 'revisi')
                        <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300 uppercase tracking-wider">
                            Revisi
                        </span>
                    @elseif ($kpiUser->status == 'final')
                        <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300 uppercase tracking-wider">
                            Final
                        </span>
                    @endif
                </div>
                @if ($kpiUser->isFinal() && $kpiUser->finalized_by)
                    <p class="text-[10px] text-gray-400 mt-1">
                        Oleh: {{ $kpiUser->finalizedBy?->nama_lengkap ?? 'Manajer' }} ({{ $kpiUser->finalized_at?->format('d/m/Y H:i') }})
                    </p>
                @endif
            </div>
        </div>

        <form id="formKpiUser" action="{{ route('kpi.user.update', $kpiUser->id) }}" method="POST">
            @csrf @method('PUT')
            <input type="hidden" name="action_type" id="form_action_type" :value="actionType">
            <input type="hidden" name="catatan_revisi" id="form_catatan_revisi" :value="catatanRevisiText">

            <div class="space-y-6">
                @foreach ($kpiUser->details as $komponen)
                    @php
                        $tipeCek = ['KEPATUHAN', 'AKKUMULASI_NILAI'];
                        $isUnder90 = $komponen->kepatuhan_percent < 90 &&
                            in_array($komponen->komponen->tipe_perhitungan, $tipeCek) &&
                            !$komponen->nilai_tetap;
                        $isReviewKhusus = (bool) $komponen->is_review_khusus && !$komponen->nilai_tetap;
                    @endphp

                    <div class="rounded-2xl border {{ $isReviewKhusus ? 'border-amber-400 shadow-sm' : ($isUnder90 ? 'border-red-300 shadow-sm' : 'border-gray-200') }} bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden shadow-sm">
                        <div class="bg-gray-50 dark:bg-gray-900/50 px-4 sm:px-6 py-4 border-b border-gray-200 dark:border-gray-800 flex flex-col sm:flex-row justify-between sm:items-center gap-3">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-bold text-gray-800 dark:text-white">{{ $komponen->nama_komponen }}</h3>
                                    @if ($komponen->nilai_tetap)
                                        <span class="flex items-center gap-1 px-2 py-0.5 rounded-full bg-green-100 dark:bg-green-900/30 text-[10px] font-bold text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                            SKOR OVERRIDE MANAJER
                                        </span>
                                    @elseif ($isReviewKhusus)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 border border-amber-300">
                                            REVIEW KHUSUS
                                        </span>
                                    @elseif ($isUnder90)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300 border border-red-300">
                                            &lt; 90%
                                        </span>
                                    @endif
                                </div>
                                <p class="text-[10px] text-gray-500 font-medium italic mt-0.5">
                                    Bobot: {{ $komponen->bobot }}% | Tipe: {{ $komponen->komponen->tipe_perhitungan }}
                                </p>
                            </div>

                            <div class="flex items-center gap-4 sm:gap-6">
                                <div class="text-right">
                                    <span class="text-[10px] text-gray-400 uppercase font-bold block">Kepatuhan</span>
                                    <span class="text-base font-black text-blue-600"
                                        x-text="(results[{{ $komponen->id }}]?.persen ?? {{ number_format($komponen->kepatuhan_percent, 1) }}) + '%'">
                                    </span>
                                </div>

                                {{-- Override Skor: Hanya tampil untuk Manajer saat status Menunggu Review --}}
                                @if ($isManager && $kpiUser->isMenungguReview() && !$isReadOnly)
                                    <div class="text-right border-l pl-4 sm:pl-6 border-gray-200 dark:border-gray-700">
                                        <span class="text-[10px] text-gray-400 uppercase font-bold block">Override Skor</span>
                                        <select name="skor_custom[{{ $komponen->id }}]"
                                            x-model="skorCustom[{{ $komponen->id }}]"
                                            class="mt-0.5 rounded-lg py-1 px-2 border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs font-bold text-gray-700 dark:text-white focus:ring-indigo-500">
                                            <option value="auto">Otomatis</option>
                                            <option value="0">0 (Nol)</option>
                                            <option value="70">70 (Cukup)</option>
                                            <option value="100">100 (Penuh)</option>
                                        </select>
                                    </div>
                                @else
                                    <div class="text-right border-l pl-4 sm:pl-6 border-gray-200 dark:border-gray-700">
                                        <span class="text-[10px] text-gray-400 uppercase font-bold block">Skor</span>
                                        <span class="text-base font-black text-green-600"
                                            x-text="results[{{ $komponen->id }}]?.skor ?? {{ round((float) $komponen->skor) }}">
                                        </span>
                                    </div>
                                @endif

                                <div class="text-right border-l pl-4 sm:pl-6 border-gray-200 dark:border-gray-700">
                                    <span class="text-[10px] text-orange-500 uppercase font-bold block">Nilai</span>
                                    <span class="text-base font-black text-orange-600"
                                        x-text="results[{{ $komponen->id }}]?.nilaiAkhir ?? {{ round((float) $komponen->nilai_akhir) }}">
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Bagian Review Khusus (Checkbox untuk Admin saat bukan mode read-only) --}}
                        @if (!$isReadOnly && !$isManager && !$komponen->nilai_tetap)
                            <div class="px-4 sm:px-6 py-3 bg-amber-50/40 dark:bg-amber-950/20 border-b border-amber-100 dark:border-amber-900/30">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                    <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                                        <input type="checkbox"
                                            name="is_review_khusus[{{ $komponen->id }}]"
                                            value="1"
                                            x-model="reviewKhusus[{{ $komponen->id }}]"
                                            class="rounded border-amber-300 text-amber-600 focus:ring-amber-500 w-4 h-4">
                                        <span class="text-xs font-bold text-amber-900 dark:text-amber-300">Ajukan Review Khusus untuk Komponen Ini</span>
                                    </label>
                                    <span class="text-[11px] text-amber-700/70 dark:text-amber-400/70 italic" x-show="reviewKhusus[{{ $komponen->id }}]">
                                        Nilai & task akan ditinjau langsung oleh Manajer Strategi & Kepatuhan
                                    </span>
                                </div>
                                <div x-show="reviewKhusus[{{ $komponen->id }}]" class="mt-2.5">
                                    <label class="block text-[10px] font-bold uppercase text-amber-800 dark:text-amber-300 mb-1">
                                        Alasan Pengajuan Review Khusus:
                                    </label>
                                    <textarea name="alasan_review_khusus[{{ $komponen->id }}]"
                                        rows="2"
                                        class="w-full rounded-xl border-amber-200 dark:border-amber-800/80 bg-white dark:bg-gray-900 text-gray-700 dark:text-white text-xs focus:border-amber-500 focus:ring-amber-500 transition-all placeholder:text-gray-400"
                                        placeholder="Tuliskan pertimbangan atau alasan mengapa komponen ini memerlukan review khusus manajer...">{{ $komponen->alasan_review_khusus }}</textarea>
                                </div>
                            </div>
                        @elseif ($komponen->is_review_khusus && $komponen->alasan_review_khusus)
                            <div class="px-4 sm:px-6 py-3 bg-amber-50/60 dark:bg-amber-950/30 border-b border-amber-100 dark:border-amber-900/30 text-xs text-amber-900 dark:text-amber-200">
                                <span class="font-bold text-[10px] uppercase text-amber-700 dark:text-amber-400 block mb-0.5">Alasan Pengajuan Review Khusus (Admin):</span>
                                <p class="italic leading-relaxed">{{ $komponen->alasan_review_khusus }}</p>
                            </div>
                        @endif

                        {{-- Tabel Task --}}
                        <div class="max-w-full overflow-x-auto custom-scrollbar">
                            <table class="w-full border-collapse" style="min-width: 850px;">
                                @php
                                    $tipePerhitungan = $komponen->komponen->tipe_perhitungan;
                                    $mode = $modeMapping[$tipePerhitungan] ?? 'range';
                                @endphp
                                <thead class="bg-gray-50/50 dark:bg-white/[0.01] border-b border-gray-200 dark:border-gray-800">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase w-12 shrink-0">No</th>
                                        <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase min-w-[320px]">Jenis Data / Task</th>
                                        @if ($mode != 'select')
                                            <th class="px-6 py-3 text-center text-[10px] font-bold text-gray-400 uppercase whitespace-nowrap {{ $komponen->komponen->tipe_perhitungan == 'DEVIASI_BUDGET' ? 'w-60' : 'w-40' }}">
                                                {{ $komponen->komponen->label_total }}
                                            </th>
                                        @endif
                                        <th class="px-6 py-3 text-center text-[10px] font-bold text-gray-400 uppercase whitespace-nowrap {{ $komponen->komponen->tipe_perhitungan == 'DEVIASI_BUDGET' ? 'w-60' : ($mode == 'select' ? 'w-80' : 'w-40') }}">
                                            {{ $komponen->komponen->label_tercapai }}
                                        </th>
                                        @if ($mode != 'select')
                                            <th class="px-6 py-3 text-center text-[10px] font-bold text-gray-400 uppercase whitespace-nowrap w-40">
                                                {{ $komponen->komponen->label_tidak_tercapai }}
                                            </th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @foreach ($komponen->tasks as $index => $task)
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition-colors">
                                            <td class="px-4 py-4 text-xs text-gray-400">{{ $index + 1 }}</td>
                                            <td class="px-6 py-4 text-sm font-medium text-gray-700 dark:text-gray-300 min-w-[320px] leading-relaxed">
                                                {{ $task->nama_task }}
                                            </td>

                                            @if ($mode != 'select')
                                                <td class="px-6 py-3 text-center">
                                                    @if ($isReadOnly)
                                                        <span class="text-sm font-bold text-gray-700 dark:text-white">{{ number_format($task->target, 0, ',', '.') }}</span>
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
                                                    @if ($isReadOnly)
                                                        @php $selectedOpt = $indicators->where('tipe_perhitungan', $tipePerhitungan)->where('nilai', $task->nilai)->first(); @endphp
                                                        <span class="text-sm font-bold text-gray-700 dark:text-white">
                                                            {{ $selectedOpt->option ?? '-' }} ({{ (float) $task->nilai }})
                                                        </span>
                                                    @else
                                                        <select
                                                            name="task[{{ $komponen->id }}][{{ $task->id }}][nilai]"
                                                            x-model="values[{{ $task->id }}].nilai"
                                                            class="w-full text-center bg-gray-50 dark:bg-gray-800 border-none rounded-lg text-sm font-bold text-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 py-1.5 transition">
                                                            <option value="0">-- Pilih --</option>
                                                            @foreach ($indicators->where('tipe_perhitungan', $tipePerhitungan) as $opt)
                                                                <option value="{{ $opt->nilai }}"
                                                                    {{ old("task.{$komponen->id}.{$task->id}.nilai", $task->nilai) == $opt->nilai ? 'selected' : '' }}>
                                                                    ({{ (float) $opt->nilai }}) {{ $opt->option }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <input type="hidden"
                                                            name="task[{{ $komponen->id }}][{{ $task->id }}][target]"
                                                            value="100">
                                                    @endif
                                                @else
                                                    @if ($isReadOnly)
                                                        <span class="text-sm font-bold text-gray-700 dark:text-white">{{ number_format($task->tercapai, 0, ',', '.') }}</span>
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
                                                <td class="px-6 py-4 text-center text-sm font-black text-gray-500 dark:text-gray-400">
                                                    <span x-text="calculateDiff({{ $task->id }})"></span>
                                                </td>
                                            @endif
                                        </tr>

                                        @if ($mode != 'select')
                                            <tr x-show="isDifferent({{ $task->id }})" class="bg-red-50/50 dark:bg-red-900/10">
                                                <td colspan="2"></td>
                                                <td colspan="3" class="px-6 py-3">
                                                    <label class="block text-[10px] font-bold text-red-500 uppercase mb-1">
                                                        Alasan Tidak Tercapai:
                                                    </label>
                                                    @if ($isReadOnly)
                                                        <p class="text-sm text-gray-700 dark:text-white italic">
                                                            {{ $task->alasan_tidak_tercapai ?? '-' }}
                                                        </p>
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

                        {{-- Catatan Tambahan --}}
                        <div class="p-5 bg-gray-50/30 dark:bg-white/[0.01] border-t border-gray-100 dark:border-gray-800">
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-2">
                                Catatan / Evaluasi Tambahan
                            </label>
                            @if ($isReadOnly)
                                <p class="text-sm text-gray-700 dark:text-white italic">
                                    {{ $komponen->catatan_tambahan ?? '-' }}
                                </p>
                            @else
                                <textarea name="catatan[{{ $komponen->id }}]" rows="2"
                                    class="w-full rounded-xl border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-700 dark:text-white text-sm focus:border-blue-500 focus:ring-blue-500 transition-all"
                                    placeholder="Tulis alasan atau evaluasi di sini...">{{ $komponen->catatan_tambahan }}</textarea>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Footer Aksi --}}
            <div class="mt-6 flex flex-col w-full md:flex-row justify-between items-center gap-4 bg-white dark:bg-white/[0.03] p-6 rounded-2xl border border-gray-200 dark:border-gray-800 mb-10 shadow-sm">
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        Status saat ini: <strong class="font-bold uppercase text-gray-800 dark:text-white">{{ $kpiUser->status == 'menunggu_review' ? 'Menunggu Review' : ($kpiUser->status == 'revisi' ? 'Revisi' : ($kpiUser->status == 'final' ? 'Final' : 'Draft')) }}</strong>
                    </span>
                </div>

                <div class="flex w-full flex-col-reverse md:flex-row justify-end items-center gap-3">
                    {{-- Tombol Kembali: Abu-abu --}}
                    <a href="{{ route('kpi.user.index') }}"
                        class="px-6 py-2.5 w-full md:w-fit text-sm text-center font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition shadow-sm">
                        Kembali
                    </a>

                    {{-- Mode FINAL (Read Only) --}}
                    @if ($kpiUser->isFinal())
                        @can('kpi.kpi-user.export')
                            <a href="{{ route('kpi.user.exportExcel', $kpiUser->id) }}"
                                class="px-5 py-2.5 w-full md:w-fit text-sm text-center font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition shadow-sm">
                                Export Excel
                            </a>
                        @endcan

                    {{-- Mode REVIEW MANAJER --}}
                    @elseif ($isManager && $kpiUser->isMenungguReview())
                        {{-- Kembalikan (Minta Revisi): Merah --}}
                        @can('kpi.kpi-user.minta-revisi')
                            <button type="button" @click="openModalRevisi()"
                                :disabled="submitting"
                                class="px-5 py-2.5 w-full md:w-fit text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition shadow-sm">
                                Kembalikan (Minta Revisi)
                            </button>
                        @endcan

                        {{-- Simpan Draft Review: Kuning --}}
                        <button type="button" @click="handleAction('simpan_draft_review')"
                            :disabled="submitting"
                            class="px-5 py-2.5 w-full md:w-fit text-sm font-medium text-white bg-yellow-500 hover:bg-yellow-600 rounded-lg transition shadow-sm">
                            <span x-text="submitting && actionType === 'simpan_draft_review' ? 'Menyimpan...' : 'Simpan Draft Review'"></span>
                        </button>

                        {{-- Setujui & Finalisasi: Hijau --}}
                        @can('kpi.kpi-user.finalisasi')
                            <button type="button" @click="handleFinalisasi()"
                                :disabled="submitting"
                                class="px-6 py-2.5 w-full md:w-fit text-sm font-bold text-white bg-green-600 hover:bg-green-700 rounded-lg transition shadow-md">
                                <span x-text="submitting && actionType === 'finalisasi' ? 'Memproses...' : 'Setujui & Finalisasi'"></span>
                            </button>
                        @endcan

                    {{-- Mode INPUT / EDIT ADMIN --}}
                    @elseif (!$isReadOnly)
                        {{-- Simpan Draft: Kuning --}}
                        @can('kpi.kpi-user.update-simpan-nilai')
                            <button type="button" @click="handleAction('save_draft')"
                                :disabled="submitting"
                                class="px-5 py-2.5 w-full md:w-fit text-sm font-medium text-white bg-yellow-500 hover:bg-yellow-600 rounded-lg transition shadow-sm">
                                <span x-text="submitting && actionType === 'save_draft' ? 'Menyimpan...' : 'Simpan Draft'"></span>
                            </button>
                        @endcan

                        {{-- Posting ke Manager: Biru --}}
                        @can('kpi.kpi-user.minta-riview')
                            <button type="button" @click="handlePostingManager()"
                                :disabled="submitting"
                                class="px-6 py-2.5 w-full md:w-fit text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition shadow-md">
                                <span x-text="submitting && actionType === 'posting_manager' ? 'Memproses...' : 'Posting ke Manager'"></span>
                            </button>
                        @endcan
                    @endif
                </div>
            </div>
        </form>

        {{-- Modal Minta Revisi (Khusus Manager) --}}
        <div x-show="showModalRevisi"
            x-cloak
            class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/50 p-4">
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl w-full max-w-lg overflow-hidden"
                @click.away="showModalRevisi = false">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                    <h3 class="text-base font-bold text-gray-800 dark:text-white">
                        Kembalikan Penilaian KPI ke Admin
                    </h3>
                    <button type="button" @click="showModalRevisi = false"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="p-6">
                    <label class="block text-xs font-bold uppercase text-gray-600 dark:text-gray-400 mb-2">
                        Catatan Revisi <span class="text-red-500">*</span>
                    </label>
                    <textarea x-model="modalCatatan"
                        rows="4"
                        class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-white text-sm p-3 focus:ring-2 focus:ring-red-500 outline-none"
                        placeholder="Tuliskan catatan perbaikan atau alasan penilaian dikembalikan ke Admin..."></textarea>
                    <p class="text-[11px] text-gray-500 mt-1 italic">
                        Catatan ini akan ditampilkan ke Admin saat memperbaiki data penilaian KPI.
                    </p>
                </div>
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-800 flex justify-end gap-3">
                    <button type="button" @click="showModalRevisi = false"
                        class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Batal
                    </button>
                    <button type="button" @click="submitMintaRevisi()"
                        class="px-5 py-2 text-sm font-bold text-white bg-red-600 hover:bg-red-700 rounded-lg transition shadow-sm">
                        Kirim Permintaan Revisi
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function kpiCalculator() {
            return {
                submitting: false,
                actionType: 'save_draft',
                showModalRevisi: false,
                modalCatatan: '',
                catatanRevisiText: '',

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

                skorCustom: {
                    @foreach ($kpiUser->details as $komponen)
                        {{ $komponen->id }}: "{{ ($isManager && $kpiUser->isMenungguReview() && $komponen->nilai_tetap) ? (int) $komponen->skor : 'auto' }}",
                    @endforeach
                },

                reviewKhusus: {
                    @foreach ($kpiUser->details as $komponen)
                        {{ $komponen->id }}: {{ $komponen->is_review_khusus ? 'true' : 'false' }},
                    @endforeach
                },

                komponenData: [
                    @foreach ($kpiUser->details as $komponen)
                        {
                            id: {{ $komponen->id }},
                            bobot: {{ $komponen->bobot }},
                            tipe: "{{ $komponen->komponen->tipe_perhitungan }}",
                            taskIds: [{{ $komponen->tasks->pluck('id')->join(', ') }}],
                            taskCount: {{ $komponen->tasks->count() }},
                        },
                    @endforeach
                ],

                indicators: @json($indicators),

                get results() {
                    let out = {};
                    let totalNilaiAkhir = 0;

                    for (const k of this.komponenData) {
                        const customVal = this.skorCustom[k.id];
                        const isOverridden = customVal !== 'auto' && customVal !== '' && customVal !== undefined;

                        let totalTarget = 0,
                            totalTercapai = 0,
                            totalNilaiSelect = 0;

                        for (const tid of k.taskIds) {
                            const v = this.values[tid];
                            if (!v) continue;
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

                        if (isOverridden) {
                            skor = parseFloat(customVal) || 0;
                            persen = skor;
                            if (k.tipe === 'PROPORSIONAL' || k.tipe === 'SELISIH_STOK') {
                                persen = totalTarget > 0 ? (totalTercapai / totalTarget) * 100 : 0;
                            }
                        } else if (k.tipe === 'KONDISI_LANGSUNG') {
                            skor = totalNilaiSelect;
                            persen = skor;
                        } else if (k.tipe === 'AKKUMULASI_NILAI') {
                            persen = k.taskCount > 0 ? (totalNilaiSelect / (k.taskCount * 100)) * 100 : 0;
                            skor = this.lookupSkor('KEPATUHAN', persen);
                        } else if (k.tipe === 'SELISIH_STOK') {
                            const selisih = Math.abs(totalTarget - totalTercapai);
                            persen = totalTarget > 0 ? (selisih / totalTarget) * 100 : 0;
                            skor = this.lookupSkor(k.tipe, persen);
                        } else if (k.tipe === 'PROPORSIONAL') {
                            persen = totalTarget > 0 ? (totalTercapai / totalTarget) * 100 : 0;
                            skor = Math.min(100, persen);
                        } else {
                            persen = totalTarget > 0 ? (totalTercapai / totalTarget) * 100 : 0;
                            skor = this.lookupSkor(k.tipe, persen);
                        }

                        const nilaiAkhir = (k.bobot / 100) * skor;
                        totalNilaiAkhir += nilaiAkhir;

                        out[k.id] = {
                            persen: Math.round(persen * 10) / 10,
                            skor: Math.round(skor),
                            nilaiAkhir: Math.round(nilaiAkhir),
                        };
                    }

                    out._total = Math.round(totalNilaiAkhir);
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

                handleAction(action) {
                    this.actionType = action;
                    this.submitting = true;
                    document.getElementById('form_action_type').value = action;
                    document.getElementById('formKpiUser').submit();
                },

                handlePostingManager() {
                    Swal.fire({
                        title: 'Posting ke Manager?',
                        text: 'Penilaian KPI akan diajukan ke Manajer Strategi & Kepatuhan untuk direview dan difinalisasi.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#2563eb',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Ya, Posting!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.handleAction('posting_manager');
                        }
                    });
                },

                handleFinalisasi() {
                    Swal.fire({
                        title: 'Setujui & Finalisasi?',
                        text: 'Penilaian KPI ini akan dikunci secara permanen dan tidak dapat diubah lagi.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#16a34a',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Ya, Finalisasi!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.handleAction('finalisasi');
                        }
                    });
                },

                openModalRevisi() {
                    this.modalCatatan = '';
                    this.showModalRevisi = true;
                },

                submitMintaRevisi() {
                    if (!this.modalCatatan || this.modalCatatan.trim() === '') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Catatan Revisi Wajib Diisi',
                            text: 'Harap masukkan alasan atau catatan perbaikan untuk Admin.'
                        });
                        return;
                    }
                    this.actionType = 'minta_revisi';
                    this.catatanRevisiText = this.modalCatatan.trim();
                    this.showModalRevisi = false;
                    this.submitting = true;

                    document.getElementById('form_action_type').value = 'minta_revisi';
                    document.getElementById('form_catatan_revisi').value = this.catatanRevisiText;
                    document.getElementById('formKpiUser').submit();
                }
            };
        }
    </script>
@endsection
