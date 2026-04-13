@extends('layouts.app')

@section('pageActive', 'User-KPI')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-data="kpiCalculator()">
        <div x-data="{ pageName: 'Detail Penilaian KPI: {{ $kpiUser->user->nama_lengkap }}' }">
            @include('partials.breadcrumb')
        </div>

        {{-- Info Cards --}}
        <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
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
            <div class="rounded-2xl border border-gray-200 bg-blue-600 p-5 text-white shadow-lg shadow-blue-600/20">
                <p class="text-[10px] uppercase font-bold opacity-80 mb-1">Total Nilai Akhir</p>
                <p class="text-2xl font-bold" x-text="grandTotalSkor.toFixed(2)"></p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Status Penilaian</p>
                <span
                    class="inline-block text-[10px] font-bold px-2 py-0.5 rounded {{ $kpiUser->status == 'final' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }} uppercase tracking-wider">
                    {{ $kpiUser->status }}
                </span>
            </div>
        </div>

        <div class="space-y-6">
            @foreach ($kpiUser->details as $komponen)
                <div
                    class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden shadow-sm">
                    {{-- Card Header --}}
                    <div
                        class="bg-gray-50 dark:bg-gray-900/50 px-6 py-4 border-b border-gray-200 dark:border-gray-800 flex justify-between items-center text-gray-700">
                        <div>
                            <h3 class="font-bold text-gray-800 dark:text-white">{{ $komponen->nama_komponen }}</h3>
                            <p class="text-[10px] text-gray-500 font-medium italic">Bobot: {{ $komponen->bobot }}%</p>
                        </div>
                        <div class="flex items-center gap-6">
                            <div class="text-right">
                                <span class="text-[10px] text-gray-400 uppercase font-bold block">Kepatuhan</span>
                                <span class="text-base font-black text-blue-600"
                                    x-text="calculateKepatuhan({{ $komponen->id }}) + '%'"></span>
                            </div>
                            <div class="text-right border-l pl-6 border-gray-200 dark:border-gray-700">
                                <span class="text-[10px] text-gray-400 uppercase font-bold block">Skor</span>
                                <span class="text-base font-black text-green-600"
                                    x-text="getSkor({{ $komponen->id }})"></span>
                            </div>
                            <div class="text-right border-l pl-6 border-gray-200 dark:border-gray-700">
                                <span class="text-[10px] text-orange-500 uppercase font-bold block">Nilai</span>
                                <span class="text-base font-black text-orange-600"
                                    x-text="getWeightedNilai({{ $komponen->id }}, {{ $komponen->bobot }}).toFixed(2)"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Table Task --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead
                                class="bg-gray-50/50 dark:bg-white/[0.01] border-b border-gray-200 dark:border-gray-800 text-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase w-16">No
                                    </th>
                                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase">Jenis Data
                                        / Task</th>
                                    <th class="px-6 py-3 text-center text-[10px] font-bold text-gray-400 uppercase w-32">
                                        Target</th>
                                    <th class="px-6 py-3 text-center text-[10px] font-bold text-gray-400 uppercase w-32">
                                        Tercapai</th>
                                    <th class="px-6 py-3 text-center text-[10px] font-bold text-gray-400 uppercase w-32">
                                        Pencapaian</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700">
                                @foreach ($komponen->tasks as $index => $task)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition-colors">
                                        <td class="px-6 py-4 text-xs text-gray-400">{{ $index + 1 }}</td>
                                        <td class="px-6 py-4 text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {{ $task->nama_task }}
                                        </td>
                                        <td class="px-6 py-4 text-center text-sm font-bold text-gray-800 dark:text-white">
                                            {{ number_format($task->target, 0) }}
                                        </td>
                                        <td class="px-6 py-4 text-center text-sm font-bold text-gray-800 dark:text-white">
                                            {{ number_format($task->tercapai, 0) }}
                                        </td>
                                        <td
                                            class="px-6 py-4 text-center text-sm font-black text-gray-500 dark:text-gray-400">
                                            <span x-text="calculateRowPercent({{ $task->id }}) + '%'"></span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Footer Card / Catatan --}}
                    @if ($komponen->catatan_tambahan)
                        <div class="p-5 bg-gray-50/30 dark:bg-white/[0.01] border-t border-gray-100 dark:border-gray-800">
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-2 text-gray-700">Penyebab
                                Tidak Tercapai / Catatan Tambahan</label>
                            <div
                                class="p-4 rounded-xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-700 text-sm text-gray-700 dark:text-gray-300 italic">
                                "{{ $komponen->catatan_tambahan }}"
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-8 flex justify-end gap-3 mb-10">
            <a href="{{ route('kpi.user.exportExcel', $kpiUser->id) }}"
                class="px-3 py-1.5 text-xs font-medium text-white bg-green-600 rounded-md hover:bg-green-700 transition">
                Export Excel
            </a>
            <a href="{{ route('kpi.user.index') }}"
                class="px-8 py-3 text-sm font-bold text-gray-500 bg-gray-100 rounded-xl hover:bg-gray-200 transition uppercase tracking-widest">
                Kembali
            </a>
            @if ($kpiUser->status == 'draft')
                <a href="{{ route('kpi.user.edit', $kpiUser->id) }}"
                    class="px-8 py-3 text-sm font-bold text-white bg-blue-600 rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-600/20 transition uppercase tracking-widest">
                    Edit Nilai
                </a>
            @endif
        </div>
    </div>

    <script>
        function kpiCalculator() {
            return {
                komponenData: {
                    @foreach ($kpiUser->details as $komponen)
                        "{{ $komponen->id }}": {
                            bobot: {{ $komponen->bobot ?? 0 }}
                        },
                    @endforeach
                },
                values: {
                    @foreach ($kpiUser->details as $komponen)
                        @foreach ($komponen->tasks as $task)
                            "{{ $task->id }}": {
                                target: {{ $task->target ?? 0 }},
                                tercapai: {{ $task->tercapai ?? 0 }},
                                komponen_id: "{{ $komponen->id }}"
                            },
                        @endforeach
                    @endforeach
                },
                calculateRowPercent(id) {
                    let t = parseFloat(this.values[id].target) || 0;
                    let a = parseFloat(this.values[id].tercapai) || 0;
                    if (t <= 0) return 0;
                    return Math.min(Math.round((a / t) * 100), 100);
                },
                calculateKepatuhan(komponenId) {
                    let totalT = 0;
                    let totalA = 0;
                    Object.values(this.values).forEach(v => {
                        if (v.komponen_id == komponenId) {
                            totalT += (parseFloat(v.target) || 0);
                            totalA += (parseFloat(v.tercapai) || 0);
                        }
                    });
                    if (totalT <= 0) return 0;
                    let hasil = (totalA / totalT) * 100;
                    return hasil.toFixed(1);
                },
                getSkor(komponenId) {
                    let percent = this.calculateKepatuhan(komponenId);
                    if (percent >= 100) return 100;
                    if (percent >= 95) return 85;
                    if (percent >= 90) return 70;
                    return 0;
                },
                getWeightedNilai(komponenId, bobot) {
                    let skor = this.getSkor(komponenId);
                    return (parseFloat(bobot) / 100) * skor;
                },
                get grandTotalSkor() {
                    let totalNilai = 0;
                    Object.keys(this.komponenData).forEach(id => {
                        let bobot = this.komponenData[id].bobot;
                        totalNilai += this.getWeightedNilai(id, bobot);
                    });
                    return totalNilai;
                }
            }
        }
    </script>
@endsection
