<table>
    {{-- 1. HEADER INFORMASI KARYAWAN --}}
    <tr>
        <td style="font-weight: bold; width: 100px;">NAMA</td>
        <td colspan="4">: {{ $kpi->user->nama_lengkap }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold;">JABATAN</td>
        <td colspan="4">: {{ $kpi->user->getRoleNames()->implode(', ') }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold;">DIVISI</td>
        <td colspan="4">: {{ $kpi->user->divisi->nama ?? '-' }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold;">BULAN</td>
        <td colspan="4">: {{ date('F Y', mktime(0, 0, 0, $kpi->bulan, 1, $kpi->tahun)) }}</td>
    </tr>

    {{-- Spacer --}}
    <tr>
        <td></td>
    </tr>

    {{-- 2. TABEL RINGKASAN KOMPONEN KPI --}}
    <thead>
        <tr>
            <th
                style="border: 1px solid black; background-color: #f3f3f3; font-weight: bold; text-align: center; width: 50px;">
                NO
            </th>
            <th
                style="border: 1px solid black; background-color: #f3f3f3; font-weight: bold; text-align: center; width: 300px;">
                KOMPONEN KPI
            </th>
            <th
                style="border: 1px solid black; background-color: #f3f3f3; font-weight: bold; text-align: center; width: 100px;">
                BOBOT (%)
            </th>
            <th
                style="border: 1px solid black; background-color: #f3f3f3; font-weight: bold; text-align: center; width: 100px;">
                SKOR
            </th>
            <th
                style="border: 1px solid black; background-color: #f3f3f3; font-weight: bold; text-align: center; width: 120px;">
                NILAI AKHIR
            </th>
        </tr>
    </thead>
    <tbody>
        @php $totalAkhir = 0; @endphp
        @foreach ($kpi->details as $index => $detail)
            @php
                $nilai = $detail->nilai_akhir;
                $totalAkhir += $nilai;
            @endphp
            <tr>
                <td style="border: 1px solid black; text-align: center;">{{ $index + 1 }}</td>
                <td style="border: 1px solid black;">{{ $detail->nama_komponen }}</td>
                <td style="border: 1px solid black; text-align: center;">{{ $detail->bobot }}</td>
                <td style="border: 1px solid black; text-align: center;">{{ $detail->skor }}</td>
                <td style="border: 1px solid black; text-align: center;">{{ number_format($nilai, 2) }}</td>
            </tr>
        @endforeach

        {{-- ROW TOTAL --}}
        <tr>
            <td colspan="2"
                style="border: 1px solid black; font-weight: bold; text-align: right; background-color: #f3f3f3;">
                TOTAL NILAI KPI
            </td>
            <td style="border: 1px solid black; font-weight: bold; text-align: center; background-color: #f3f3f3;">
                100
            </td>
            <td style="border: 1px solid black; background-color: #f3f3f3;"></td>
            <td style="border: 1px solid black; font-weight: bold; text-align: center; background-color: #d9eaf7;">
                {{ number_format($totalAkhir, 2) }}
            </td>
        </tr>
    </tbody>
</table>
