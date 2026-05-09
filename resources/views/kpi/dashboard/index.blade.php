@extends('layouts.app')

@section('pageActive', 'Dashboard-KPI')

@section('content')
<div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-data="{ openModal: false, modalContent: { name: '', tasks: [] } }">

    <div x-data="{ pageName: 'Master KPI' }">
        @include('partials.breadcrumb')
    </div>

    {{-- Filter Section --}}
    <div class="mt-4 mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
        <form action="{{ route('kpi.dashboard.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-end w-full">

            <div class="w-full md:w-1/3">
                <label for="jabatan" class="block mb-1 text-sm font-medium text-gray-700">Jabatan</label>
                <select name="jabatan" id="jabatan" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2">
                    <option value="">Semua Jabatan</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ request('jabatan') == $role->id ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="w-full md:w-1/4">
                <label for="tahun" class="block mb-1 text-sm font-medium text-gray-700">Tahun Penilaian</label>
                <select name="tahun" id="tahun" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2">
                    @foreach($pilihanTahun as $t)
                        <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>
                            {{ $t }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2 w-full md:w-auto">
                <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2 focus:outline-none">
                    Filter
                </button>
                <a href="{{ route('kpi.dashboard.index') }}" class="text-gray-900 bg-white border border-gray-300 hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2 focus:outline-none text-center">
                    Reset
                </a>

                <a href="{{ route('kpi.dashboard.export', ['tahun' => request('tahun', $tahun), 'jabatan' => request('jabatan')]) }}"
                   class="text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2 focus:outline-none flex items-center gap-2">
                    <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 16 18">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 1v11m0 0 4-4m-4 4L4 8m11 4v3a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-3"/>
                    </svg>
                    Export Excel
                </a>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow-sm border border-gray-200">
        <table class="w-full text-xs text-left text-gray-700">
            <thead class="uppercase bg-gray-200 border-b border-gray-300">
                <tr>
                    <th colspan="18" class="px-2 py-2 text-center text-sm font-bold text-gray-800 border-b border-gray-300 bg-gray-300">
                        Dashboard KPI {{ $tahun }}
                    </th>
                </tr>
                <tr class="text-center font-bold align-middle">
                    <th class="px-2 py-2 border-r border-gray-300 text-left ">Nama Karyawan</th>
                    <th class="px-2 py-2 border-r border-gray-300 text-left ">Jabatan</th>
                    <th class="px-1 py-2 border-r border-gray-300">Jan</th>
                    <th class="px-1 py-2 border-r border-gray-300">Feb</th>
                    <th class="px-1 py-2 border-r border-gray-300">Mar</th>
                    <th class="px-1 py-2 border-r border-gray-300 bg-yellow-300 text-black">AVG Q1</th>
                    <th class="px-1 py-2 border-r border-gray-300">Apr</th>
                    <th class="px-1 py-2 border-r border-gray-300">Mei</th>
                    <th class="px-1 py-2 border-r border-gray-300">Jun</th>
                    <th class="px-1 py-2 border-r border-gray-300 bg-[#00b050] text-white">AVG Q2</th>
                    <th class="px-1 py-2 border-r border-gray-300">Jul</th>
                    <th class="px-1 py-2 border-r border-gray-300">Agu</th>
                    <th class="px-1 py-2 border-r border-gray-300">Sep</th>
                    <th class="px-1 py-2 border-r border-gray-300 bg-[#5b9bd5] text-white">AVG Q3</th>
                    <th class="px-1 py-2 border-r border-gray-300">Okt</th>
                    <th class="px-1 py-2 border-r border-gray-300">Nov</th>
                    <th class="px-1 py-2 border-r border-gray-300">Des</th>
                    <th class="px-1 py-2 bg-red-600 text-white">AVG Q4</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dashboardData as $jabatan => $users)
                    @foreach($users as $user)
                        <tr class="hover:bg-gray-50 border-b border-gray-200">
                            <td class="px-2 py-1.5 border-r border-gray-300 font-medium text-gray-900" title="{{ $user['nama'] }}">{{ $user['nama'] }}</td>
                            <td class="px-2 py-1.5 border-r border-gray-300" title="{{ $user['jabatan'] }}">{{ $user['jabatan'] }}</td>

                            <td class="px-1 py-1.5 text-center border-r border-gray-300">{{ $user['bulan']['januari'] ?? '-' }}</td>
                            <td class="px-1 py-1.5 text-center border-r border-gray-300">{{ $user['bulan']['februari'] ?? '-' }}</td>
                            <td class="px-1 py-1.5 text-center border-r border-gray-300">{{ $user['bulan']['maret'] ?? '-' }}</td>
                            <td class="px-1 py-1.5 text-center border-r border-gray-300 bg-yellow-100 font-bold text-yellow-900">
                                {{ $user['q1'] ? rtrim(rtrim(number_format($user['q1'], 2, ',', '.'), '0'), ',') : '-' }}
                            </td>

                            <td class="px-1 py-1.5 text-center border-r border-gray-300">{{ $user['bulan']['april'] ?? '-' }}</td>
                            <td class="px-1 py-1.5 text-center border-r border-gray-300">{{ $user['bulan']['mei'] ?? '-' }}</td>
                            <td class="px-1 py-1.5 text-center border-r border-gray-300">{{ $user['bulan']['juni'] ?? '-' }}</td>
                            <td class="px-1 py-1.5 text-center border-r border-gray-300 bg-green-100 font-bold text-green-900">
                                {{ $user['q2'] ? rtrim(rtrim(number_format($user['q2'], 2, ',', '.'), '0'), ',') : '-' }}
                            </td>

                            <td class="px-1 py-1.5 text-center border-r border-gray-300">{{ $user['bulan']['juli'] ?? '-' }}</td>
                            <td class="px-1 py-1.5 text-center border-r border-gray-300">{{ $user['bulan']['agustus'] ?? '-' }}</td>
                            <td class="px-1 py-1.5 text-center border-r border-gray-300">{{ $user['bulan']['september'] ?? '-' }}</td>
                            <td class="px-1 py-1.5 text-center border-r border-gray-300 bg-blue-100 font-bold text-blue-900">
                                {{ $user['q3'] ? rtrim(rtrim(number_format($user['q3'], 2, ',', '.'), '0'), ',') : '-' }}
                            </td>

                            <td class="px-1 py-1.5 text-center border-r border-gray-300">{{ $user['bulan']['oktober'] ?? '-' }}</td>
                            <td class="px-1 py-1.5 text-center border-r border-gray-300">{{ $user['bulan']['november'] ?? '-' }}</td>
                            <td class="px-1 py-1.5 text-center border-r border-gray-300">{{ $user['bulan']['desember'] ?? '-' }}</td>
                            <td class="px-1 py-1.5 text-center bg-red-100 font-bold text-red-900">
                                {{ $user['q4'] ? rtrim(rtrim(number_format($user['q4'], 2, ',', '.'), '0'), ',') : '-' }}
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="18" class="px-4 py-8 text-center text-sm text-gray-500 italic bg-gray-50">
                            Data KPI tidak ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
