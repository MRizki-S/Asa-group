@extends('layouts.app')

@section('pageActive', 'Review-KPI')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">
        <div x-data="{ pageName: 'Review Materialitas KPI: {{ $kpiUser->user->nama_lengkap }}' }">
            @include('partials.breadcrumb')
        </div>

        {{-- Row Header (Read Only) --}}
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
                <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Total Nilai Saat Ini</p>
                <p class="text-sm font-bold text-gray-800 dark:text-white">
                    {{ (float) $kpiUser->total_nilai }}
                </p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Status</p>
                <span
                    class="inline-block text-[10px] font-bold px-2 py-0.5 rounded bg-yellow-100 text-yellow-700 uppercase tracking-wider">
                    {{ $kpiUser->status }}
                </span>
            </div>
        </div>

        <form action="{{ route('kpi.review.update', $kpiUser->id) }}" method="POST">
            @csrf @method('PUT')

            <div class="space-y-6">
                @foreach ($kpiUser->details as $komponen)
                    @php
                        $tipeCek = ['KEPATUHAN', 'AKKUMULASI_NILAI'];
                        $isBermasalah =
                            $komponen->kepatuhan_percent < 90 &&
                            in_array($komponen->komponen->tipe_perhitungan, $tipeCek) &&
                            !$komponen->nilai_tetap;
                    @endphp

                    <div
                        class="rounded-2xl border {{ $isBermasalah ? 'border-red-500 shadow-md shadow-red-50/50' : 'border-gray-200 shadow-sm' }} bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">

                        <div
                            class="bg-gray-50 dark:bg-gray-900/50 px-6 py-4 border-b border-gray-200 dark:border-gray-800 flex justify-between items-center">
                            <div>
                                <h3 class="font-bold text-gray-800 dark:text-white">{{ $komponen->nama_komponen }}</h3>
                                <p class="text-[10px] text-gray-500 font-medium italic">
                                    Bobot: {{ $komponen->bobot }}% | Tipe: {{ $komponen->komponen->tipe_perhitungan }}
                                </p>
                            </div>
                            <div class="flex gap-6">
                                <div class="text-right">
                                    <span class="text-[10px] text-gray-400 uppercase font-bold block">Kepatuhan</span>
                                    <span
                                        class="text-base font-black {{ $isBermasalah ? 'text-red-600' : 'text-blue-600' }}">
                                        {{ number_format($komponen->kepatuhan_percent, 1) }}%
                                    </span>
                                </div>
                                <div class="text-right border-l pl-6 border-gray-200 dark:border-gray-700">
                                    <span class="text-[10px] text-gray-400 uppercase font-bold block">Skor</span>
                                    @if ($isBermasalah)
                                        @can('kpi.kpi-riview.simpan-hasil-riview')
                                            <select name="skor_custom[{{ $komponen->id }}]"
                                                class="mt-1 block w-24 rounded-lg border-red-300 text-sm font-black text-red-600 focus:ring-red-500 focus:border-red-500 py-1">
                                                <option value="0" {{ $komponen->skor == 0 ? 'selected' : '' }}>0</option>
                                                <option value="70" {{ $komponen->skor == 70 ? 'selected' : '' }}>70
                                                </option>
                                            </select>
                                        @endcan
                                    @else
                                        <span
                                            class="text-base font-black text-green-600">{{ (float) $komponen->skor }}</span>
                                    @endif
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
                                        <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase">Task
                                        </th>
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
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01]">
                                            <td class="px-6 py-4 text-xs text-gray-400">{{ $index + 1 }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                                {{ $task->nama_task }}</td>
                                            @if ($mode != 'select')
                                                <td
                                                    class="px-6 py-4 text-center text-sm font-bold text-gray-800 dark:text-white">
                                                    {{ number_format($task->target, 0, ',', '.') }}
                                                </td>
                                            @endif
                                            <td
                                                class="px-6 py-4 text-center text-sm font-bold text-gray-800 dark:text-white">
                                                {{ number_format($mode == 'select' ? $task->nilai : $task->tercapai, 0, ',', '.') }}
                                            </td>
                                            @if ($mode != 'select')
                                                <td
                                                    class="px-6 py-4 text-center text-sm font-bold text-gray-800 dark:text-white">
                                                    {{ number_format(abs($task->target - $task->tercapai), 0, ',', '.') }}
                                                </td>
                                            @endif
                                        </tr>
                                        @if ($task->alasan_tidak_tercapai)
                                            <tr class="bg-red-50/30">
                                                <td colspan="4" class="px-6 py-2">
                                                    <p class="text-[10px] text-red-500 italic"><span
                                                            class="font-bold">Alasan:</span>
                                                        {{ $task->alasan_tidak_tercapai }}</p>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Catatan (Read Only) --}}
                        <div class="p-5 bg-gray-50/30 dark:bg-white/[0.01] border-t border-gray-100 dark:border-gray-800">
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Catatan Tambahan</label>
                            <p class="text-sm text-gray-700 dark:text-white italic">
                                {{ $komponen->catatan_tambahan ?: '-' }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Footer Form --}}
            <div
                class="mt-8 flex flex-col w-full md:flex-row justify-between items-center gap-4 bg-white dark:bg-white/[0.03] p-6 rounded-2xl border border-gray-200 dark:border-gray-800 mb-10">
                <div class="flex flex-col w-full md:flex-row justify-start items-center gap-3">
                    @can('kpi.kpi-riview.simpan-hasil-riview')
                        <label class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase">Simpan sebagai:</label>
                        <select name="status" class="rounded-lg py-2 border-gray-300 text-sm font-bold focus:ring-blue-500">
                            <option value="draft" {{ $kpiUser->status == 'draft' ? 'selected' : '' }}>DRAFT</option>
                            <option value="final" {{ $kpiUser->status == 'final' ? 'selected' : '' }}>FINAL</option>
                        </select>
                    @endcan
                </div>
                <div class="flex w-full flex-col-reverse md:flex-row justify-end gap-3">
                    <a href="{{ route('kpi.review.index') }}"
                        class="px-6 py-2 w-full md:w-fit text-sm text-center font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                        Kembali
                    </a>
                    @can('kpi.kpi-riview.simpan-hasil-riview')
                        <button type="submit"
                            class="px-6 py-2 w-full md:w-fit text-sm text-center font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-md">
                            Simpan Hasil Review
                        </button>
                    @endcan
                </div>
            </div>
        </form>
    </div>
@endsection
