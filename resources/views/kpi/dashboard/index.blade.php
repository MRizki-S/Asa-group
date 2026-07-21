@extends('layouts.app')

@section('pageActive', 'Dashboard-KPI')

@section('content')
<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-data="{ openModal: false, modalContent: { name: '', tasks: [] } }">

    <div x-data="{ pageName: 'Master KPI' }">
        @include('partials.breadcrumb')
    </div>

    {{-- Filter Section --}}
    <div class="mt-4 mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-200 dark:bg-white/[0.03] dark:border-gray-800">
        <form action="{{ route('kpi.dashboard.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-end w-full">

            <div class="w-full md:w-1/4">
                <label for="devisi" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Devisi</label>
                <select name="devisi" id="devisi" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                    <option value="">Semua Devisi</option>
                    @foreach($devisis as $devisi)
                        <option value="{{ $devisi->id }}" {{ request('devisi') == $devisi->id ? 'selected' : '' }}>
                            {{ $devisi->nama_devisi }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="w-full md:w-1/4">
                <label for="jabatan" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Jabatan</label>
                <select name="jabatan" id="jabatan" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                    <option value="">Semua Jabatan</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ request('jabatan') == $role->id ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="w-full md:w-1/5">
                <label for="tahun" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Tahun Penilaian</label>
                <select name="tahun" id="tahun" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                    @foreach($pilihanTahun as $t)
                        <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>
                            {{ $t }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2 w-full md:w-auto">
                <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2 focus:outline-none">
                    Terapkan
                </button>
                <a href="{{ route('kpi.dashboard.index') }}" class="text-gray-900 bg-white border border-gray-300 hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2 focus:outline-none text-center dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700">
                    Reset
                </a>

                <a href="{{ route('kpi.dashboard.export', ['tahun' => request('tahun', $tahun), 'jabatan' => request('jabatan'), 'devisi' => request('devisi')]) }}"
                   class="text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2 focus:outline-none flex items-center gap-2">
                    <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 16 18">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 1v11m0 0 4-4m-4 4L4 8m11 4v3a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-3"/>
                    </svg>
                    Export
                </a>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto custom-scrollbar bg-white rounded-lg shadow-sm border border-gray-200 dark:bg-white/[0.03] dark:border-gray-800">
        <table class="w-full text-xs text-left text-gray-700 dark:text-gray-300" style="min-width: 950px;">
            <thead class="uppercase bg-gray-200 border-b border-gray-300 dark:bg-gray-800 dark:border-gray-700">
                <tr>
                    <th colspan="18" class="px-2 py-2 text-center text-sm font-bold text-gray-800 border-b border-gray-300 bg-gray-300 dark:bg-gray-700 dark:text-white dark:border-gray-600">
                        Dashboard KPI {{ $tahun }}
                    </th>
                </tr>
                <tr class="text-center font-bold align-middle dark:border-gray-700">
                    <th class="px-2 py-2 border-r border-gray-300 text-left dark:border-gray-700">Nama Karyawan</th>
                    <th class="px-2 py-2 border-r border-gray-300 text-left dark:border-gray-700">Jabatan</th>
                    <th class="px-1 py-2 border-r border-gray-300 dark:border-gray-700">Jan</th>
                    <th class="px-1 py-2 border-r border-gray-300 dark:border-gray-700">Feb</th>
                    <th class="px-1 py-2 border-r border-gray-300 dark:border-gray-700">Mar</th>
                    <th class="px-1 py-2 border-r border-gray-300 bg-yellow-300 text-black dark:bg-yellow-600/40 dark:text-yellow-200 dark:border-gray-700">AVG Q1</th>
                    <th class="px-1 py-2 border-r border-gray-300 dark:border-gray-700">Apr</th>
                    <th class="px-1 py-2 border-r border-gray-300 dark:border-gray-700">Mei</th>
                    <th class="px-1 py-2 border-r border-gray-300 dark:border-gray-700">Jun</th>
                    <th class="px-1 py-2 border-r border-gray-300 bg-[#00b050] text-white dark:bg-green-600/40 dark:text-green-200 dark:border-gray-700">AVG Q2</th>
                    <th class="px-1 py-2 border-r border-gray-300 dark:border-gray-700">Jul</th>
                    <th class="px-1 py-2 border-r border-gray-300 dark:border-gray-700">Agu</th>
                    <th class="px-1 py-2 border-r border-gray-300 dark:border-gray-700">Sep</th>
                    <th class="px-1 py-2 border-r border-gray-300 bg-[#5b9bd5] text-white dark:bg-blue-600/40 dark:text-blue-200 dark:border-gray-700">AVG Q3</th>
                    <th class="px-1 py-2 border-r border-gray-300 dark:border-gray-700">Okt</th>
                    <th class="px-1 py-2 border-r border-gray-300 dark:border-gray-700">Nov</th>
                    <th class="px-1 py-2 border-r border-gray-300 dark:border-gray-700">Des</th>
                    <th class="px-1 py-2 bg-red-600 text-white dark:bg-red-600/40 dark:text-red-200">AVG Q4</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dashboardData as $devisiName => $users)
                    {{-- Subheader Group Devisi --}}
                    <tr class="bg-gray-100 font-bold border-b border-gray-200 dark:bg-gray-800/50 dark:border-gray-700">
                        <td colspan="18" class="px-3 py-2 text-left bg-gray-100 text-gray-800 text-xs font-bold uppercase tracking-wider border-r border-gray-300 dark:bg-gray-800/50 dark:text-white dark:border-gray-700">
                            🏢 {{ $devisiName }}
                        </td>
                    </tr>
                    @foreach($users as $user)
                        <tr class="hover:bg-gray-50 border-b border-gray-200 dark:hover:bg-white/[0.02] dark:border-gray-800">
                            <td class="px-2 py-1.5 border-r border-gray-300 font-medium text-gray-900 dark:text-white dark:border-gray-700" title="{{ $user['nama'] }}">{{ $user['nama'] }}</td>
                            <td class="px-2 py-1.5 border-r border-gray-300 text-gray-900 dark:text-gray-300 dark:border-gray-700" title="{{ $user['jabatan'] }}">{{ $user['jabatan'] }}</td>

                            <td class="px-1 py-1.5 text-center border-r border-gray-300 text-gray-900 dark:text-gray-300 dark:border-gray-700">{{ $user['bulan']['januari'] ?? '-' }}</td>
                            <td class="px-1 py-1.5 text-center border-r border-gray-300 text-gray-900 dark:text-gray-300 dark:border-gray-700">{{ $user['bulan']['februari'] ?? '-' }}</td>
                            <td class="px-1 py-1.5 text-center border-r border-gray-300 text-gray-900 dark:text-gray-300 dark:border-gray-700">{{ $user['bulan']['maret'] ?? '-' }}</td>
                            <td class="px-1 py-1.5 text-center border-r border-gray-300 bg-yellow-100 font-bold text-yellow-900 dark:bg-yellow-950/20 dark:text-yellow-400 dark:border-gray-700">
                                {{ $user['q1'] ? rtrim(rtrim(number_format($user['q1'], 2, ',', '.'), '0'), ',') : '-' }}
                            </td>

                            <td class="px-1 py-1.5 text-center border-r border-gray-300 text-gray-900 dark:text-gray-300 dark:border-gray-700">{{ $user['bulan']['april'] ?? '-' }}</td>
                            <td class="px-1 py-1.5 text-center border-r border-gray-300 text-gray-900 dark:text-gray-300 dark:border-gray-700">{{ $user['bulan']['mei'] ?? '-' }}</td>
                            <td class="px-1 py-1.5 text-center border-r border-gray-300 text-gray-900 dark:text-gray-300 dark:border-gray-700">{{ $user['bulan']['juni'] ?? '-' }}</td>
                            <td class="px-1 py-1.5 text-center border-r border-gray-300 bg-green-100 font-bold text-green-900 dark:bg-green-950/20 dark:text-green-400 dark:border-gray-700">
                                {{ $user['q2'] ? rtrim(rtrim(number_format($user['q2'], 2, ',', '.'), '0'), ',') : '-' }}
                            </td>

                            <td class="px-1 py-1.5 text-center border-r border-gray-300 text-gray-900 dark:text-gray-300 dark:border-gray-700">{{ $user['bulan']['juli'] ?? '-' }}</td>
                            <td class="px-1 py-1.5 text-center border-r border-gray-300 text-gray-900 dark:text-gray-300 dark:border-gray-700">{{ $user['bulan']['agustus'] ?? '-' }}</td>
                            <td class="px-1 py-1.5 text-center border-r border-gray-300 text-gray-900 dark:text-gray-300 dark:border-gray-700">{{ $user['bulan']['september'] ?? '-' }}</td>
                            <td class="px-1 py-1.5 text-center border-r border-gray-300 bg-blue-100 font-bold text-blue-900 dark:bg-blue-950/20 dark:text-blue-400 dark:border-gray-700">
                                {{ $user['q3'] ? rtrim(rtrim(number_format($user['q3'], 2, ',', '.'), '0'), ',') : '-' }}
                            </td>

                            <td class="px-1 py-1.5 text-center border-r border-gray-300 text-gray-900 dark:text-gray-300 dark:border-gray-700">{{ $user['bulan']['oktober'] ?? '-' }}</td>
                            <td class="px-1 py-1.5 text-center border-r border-gray-300 text-gray-900 dark:text-gray-300 dark:border-gray-700">{{ $user['bulan']['november'] ?? '-' }}</td>
                            <td class="px-1 py-1.5 text-center border-r border-gray-300 text-gray-900 dark:text-gray-300 dark:border-gray-700">{{ $user['bulan']['desember'] ?? '-' }}</td>
                            <td class="px-1 py-1.5 text-center bg-red-100 font-bold text-red-900 dark:bg-red-950/20 dark:text-red-400">
                                {{ $user['q4'] ? rtrim(rtrim(number_format($user['q4'], 2, ',', '.'), '0'), ',') : '-' }}
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="18" class="px-4 py-8 text-center text-sm text-gray-500 italic bg-gray-50 dark:bg-gray-800/20 dark:text-gray-400">
                            Data KPI tidak ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
