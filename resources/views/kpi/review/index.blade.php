@extends('layouts.app')

@section('pageActive', 'Review-KPI')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <div x-data="{ pageName: 'Review Materialitas (Skor 0)' }">
            @include('partials.breadcrumb')
        </div>

        {{-- Alert Success --}}
        @if (session('success'))
            <div class="flex p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400"
                role="alert">
                <svg class="flex-shrink-0 inline w-4 h-4 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                </svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        <div class="space-y-5 sm:space-y-6">
            <div
                class="rounded-2xl border border-gray-200 px-5 py-4 sm:px-6 sm:py-5 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

                {{-- Header --}}
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Daftar Review Skor 0</h3>
                        <p class="text-xs text-gray-500 mt-1 italic">* Manajer dapat mengubah skor 0 menjadi 70 berdasarkan
                            pertimbangan materialitas.</p>
                    </div>
                </div>

                {{-- Loop Kartu Karyawan --}}
                <div class="space-y-6">
                    @forelse ($reviews as $kpi)
                        <div
                            class="rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden bg-gray-50/30 dark:bg-transparent shadow-sm">
                            <form action="{{ route('kpi.review.update', $kpi->id) }}" method="POST">
                                @csrf @method('PUT')

                                {{-- Sub-Header per Karyawan --}}
                                <div
                                    class="bg-gray-50 dark:bg-gray-800/50 px-5 py-3 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                                    <div class="flex items-center gap-3">
                                        <div>
                                            <h4 class="text-sm font-bold text-gray-800 dark:text-white">
                                                {{ $kpi->user->nama_lengkap }}</h4>
                                            <p class="text-[10px] text-gray-400 uppercase tracking-wider">
                                                {{ date('F Y', mktime(0, 0, 0, $kpi->bulan, 1, $kpi->tahun)) }}</p>
                                        </div>
                                    </div>
                                    <button type="submit"
                                        class="px-4 py-1.5 text-xs font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition shadow-sm uppercase tracking-tighter">
                                        Simpan Perubahan
                                    </button>
                                </div>

                                {{-- Tabel Detail --}}
                                <div class="overflow-x-auto">
                                    <table class="min-w-full">
                                        <thead>
                                            <tr class="text-left border-b border-gray-200 dark:border-gray-800">
                                                <th
                                                    class="py-2 px-5 font-bold text-[10px] text-gray-400 dark:text-gray-500 uppercase tracking-widest">
                                                    Komponen KPI</th>
                                                <th
                                                    class="py-2 px-5 font-bold text-[10px] text-gray-400 dark:text-gray-500 uppercase tracking-widest">
                                                    Alasan Tidak Tercapai</th>
                                                <th
                                                    class="py-2 px-5 font-bold text-[10px] text-gray-400 dark:text-gray-500 uppercase tracking-widest text-center w-32">
                                                    Opsi Skor</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                            @foreach ($kpi->details->where('skor', 0) as $komponen)
                                                <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.01]">
                                                    <td class="py-4 px-5 align-top">
                                                        <div class="text-sm font-bold text-red-600 dark:text-red-400">
                                                            {{ $komponen->nama_komponen }}</div>
                                                        <div class="text-[10px] text-gray-400 mt-0.5 italic">Bobot:
                                                            {{ $komponen->bobot }}%</div>
                                                    </td>
                                                    <td class="py-4 px-5 align-top">
                                                        <div class="space-y-1.5">
                                                            @foreach ($komponen->tasks as $task)
                                                                @if ($task->alasan_tidak_tercapai)
                                                                    <div
                                                                        class="p-2 rounded bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                                                                        <p
                                                                            class="text-[9px] font-black text-blue-500 dark:text-blue-400 uppercase tracking-tighter">
                                                                            {{ $task->nama_task }}</p>
                                                                        <p
                                                                            class="text-xs text-gray-600 dark:text-gray-400 italic">
                                                                            "{{ $task->alasan_tidak_tercapai }}"</p>
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    </td>
                                                    <td class="py-4 px-5 align-top">
                                                        <select name="skor_custom[{{ $komponen->id }}]"
                                                            class="w-full appearance-none bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-sm font-bold rounded-lg py-1.5 px-3 text-gray-700 dark:text-white outline-none focus:ring-1 focus:ring-blue-500 text-center">
                                                            <option value="0" selected>0</option>
                                                            <option value="70">70</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </form>
                        </div>
                    @empty
                        <div class="py-12 text-center">
                            <div class="mb-3 flex justify-center">
                                <svg class="w-12 h-12 text-gray-200 dark:text-gray-700" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-400 italic text-sm">Tidak ada penilaian dengan skor 0 yang memerlukan
                                review.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
