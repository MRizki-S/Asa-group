<table>
    {{-- Header Informasi --}}
    <tr>
        <td style="font-weight: bold;">NAMA</td>
        <td colspan="3">: {{ $kpi->user->nama_lengkap }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold;">JABATAN</td>
        <td colspan="3">: {{ $kpi->user->getRoleNames()->implode(', ') }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold;">DIVISI</td>
        <td colspan="3">: -</td> {{-- Sesuaikan field divisi jika ada --}}
    </tr>
    <tr>
        <td style="font-weight: bold;">BULAN</td>
        <td colspan="3">: {{ date('F Y', mktime(0, 0, 0, $kpi->bulan, 1, $kpi->tahun)) }}</td>
    </tr>

    <tr>
        <td></td>
    </tr> {{-- Spacer --}}

    {{-- Tabel Legend Skor --}}
    <tr>
        <th style="border: 1px solid black; background-color: #f3f3f3; font-weight: bold; text-align: center;">KEPATUHAN
        </th>
        <th style="border: 1px solid black; background-color: #f3f3f3; font-weight: bold; text-align: center;">SKOR</th>
    </tr>
    <tr>
        <td style="border: 1px solid black; text-align: center;">100%</td>
        <td style="border: 1px solid black; text-align: center;">100</td>
    </tr>
    <tr>
        <td style="border: 1px solid black; text-align: center;">95-99%</td>
        <td style="border: 1px solid black; text-align: center;">85</td>
    </tr>
    <tr>
        <td style="border: 1px solid black; text-align: center;">90-94%</td>
        <td style="border: 1px solid black; text-align: center;">70</td>
    </tr>
    <tr>
        <td style="border: 1px solid black; text-align: center;">
            < 90%</td>
        <td style="border: 1px solid black; text-align: center;">0</td>
    </tr>

    <tr>
        <td></td>
    </tr> {{-- Spacer --}}

    {{-- Ringkasan Komponen KPI --}}
    <thead>
        <tr>
            <th style="border: 1px solid black; background-color: #f3f3f3; font-weight: bold;">NO</th>
            <th style="border: 1px solid black; background-color: #f3f3f3; font-weight: bold;">KOMPONEN KPI</th>
            <th style="border: 1px solid black; background-color: #f3f3f3; font-weight: bold; text-align: center;">BOBOT
            </th>
            <th style="border: 1px solid black; background-color: #f3f3f3; font-weight: bold; text-align: center;">SKOR
            </th>
            <th style="border: 1px solid black; background-color: #f3f3f3; font-weight: bold; text-align: center;">NILAI
            </th>
        </tr>
    </thead>
    <tbody>
        @php $totalAkhir = 0; @endphp
        @foreach ($kpi->details as $index => $detail)
            @php
                $skor = $detail->skor;
                $nilai = ($detail->bobot / 100) * $skor;
                $totalAkhir += $nilai;
            @endphp
            <tr>
                <td style="border: 1px solid black; text-align: center;">{{ $index + 1 }}</td>
                <td style="border: 1px solid black;">{{ $detail->nama_komponen }}</td>
                <td style="border: 1px solid black; text-align: center;">{{ $detail->bobot }}</td>
                <td style="border: 1px solid black; text-align: center;">{{ $skor }}</td>
                <td style="border: 1px solid black; text-align: center;">{{ number_format($nilai, 2) }}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="4" style="border: 1px solid black; font-weight: bold; text-align: right;">TOTAL NILAI</td>
            <td style="border: 1px solid black; font-weight: bold; text-align: center; background-color: #d9eaf7;">
                {{ number_format($totalAkhir, 2) }}</td>
        </tr>
    </tbody>

    <tr>
        <td></td>
    </tr> {{-- Spacer --}}

    {{-- Detail per Komponen --}}
    @foreach ($kpi->details as $detail)
        <tr>
            <td colspan="6" style="font-weight: bold; background-color: #e2e2e2;">{{ $detail->nama_komponen }}</td>
        </tr>
        <tr>
            <th style="border: 1px solid black; font-weight: bold; text-align: center;">NO</th>
            <th style="border: 1px solid black; font-weight: bold; text-align: center;">JENIS DATA</th>
            <th style="border: 1px solid black; font-weight: bold; text-align: center;">TOTAL</th>
            <th style="border: 1px solid black; font-weight: bold; text-align: center;">TERCAPAI</th>
            <th style="border: 1px solid black; font-weight: bold; text-align: center;">TIDAK TERCAPAI</th>
            <th style="border: 1px solid black; font-weight: bold; text-align: center;">KEPATUHAN</th>
            <th style="border: 1px solid black; font-weight: bold; text-align: center;">PENYEBAB TIDAK TERCAPAI</th>
        </tr>
        @foreach ($detail->tasks as $tIdx => $task)
            <tr>
                <td style="border: 1px solid black; text-align: center;">{{ $tIdx + 1 }}</td>
                <td style="border: 1px solid black;">{{ $task->nama_task }}</td>
                <td style="border: 1px solid black; text-align: center;">{{ (int) $task->target }}</td>
                <td style="border: 1px solid black; text-align: center;">{{ (int) $task->tercapai }}</td>
                <td style="border: 1px solid black; text-align: center;">
                    {{ (int) $task->target - (int) $task->tercapai }}</td>
                @if ($loop->first)
                    <td rowspan="{{ $detail->tasks->count() + 1 }}"
                        style="border: 1px solid black; text-align: center; vertical-align: middle; font-weight: bold;">
                        {{ number_format($detail->kepatuhan_percent, 1) }}%
                    </td>
                    <td rowspan="{{ $detail->tasks->count() + 1 }}"
                        style="border: 1px solid black; vertical-align: top;">
                        {{ $detail->catatan_tambahan ?? '-' }}
                    </td>
                @endif
            </tr>
        @endforeach
        <tr>
            <td colspan="2" style="border: 1px solid black; font-weight: bold; text-align: right;">TOTAL</td>
            <td style="border: 1px solid black; font-weight: bold; text-align: center;">
                {{ (int) $detail->total_target }}</td>
            <td style="border: 1px solid black; font-weight: bold; text-align: center;">
                {{ (int) $detail->total_tercapai }}</td>
            <td style="border: 1px solid black; font-weight: bold; text-align: center;">
                {{ (int) $detail->total_target - (int) $detail->total_tercapai }}</td>
        </tr>
        <tr>
            <td></td>
        </tr> {{-- Spacer antar komponen --}}
    @endforeach
</table>
