<?php

namespace App\Exports;

use App\Models\PemesananUnit;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomClosingUnitExport implements FromArray, WithStyles, WithCustomStartCell
{
    protected $tahun;
    protected $bulan;
    protected $perumahaanId;
    protected $namaPerumahaan;
    protected $columns;
    protected $isAgent;

    /**
     * Bulan yang akan ditampilkan.
     */
    protected $months = [];

    /**
     * Judul laporan.
     */
    protected $title = '';

    /**
     * Informasi posisi bulan.
     */
    protected $monthRanges = [];

    /**
     * Cell status KPR.
     */
    protected $statusCells = [];

    /**
     * Kolom terakhir.
     */
    protected $lastColumn = 'A';

    /**
     * Baris header tabel (diset saat array() berjalan).
     */
    protected $headerRow = 5;

    /**
     * Nama bulan.
     */
    protected $namaBulan = [
        1  => 'Januari',
        2  => 'Februari',
        3  => 'Maret',
        4  => 'April',
        5  => 'Mei',
        6  => 'Juni',
        7  => 'Juli',
        8  => 'Agustus',
        9  => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    /**
     * Kolom yang tersedia untuk dipilih.
     *
     * nama_unit WAJIB.
     */
    protected $availableColumns = [
        'nama_unit' => 'Nama Unit',

        'nama_user' => 'Nama Customer',

        'nama_agent_sales' => 'Sales / Agent',

        'tanggal_closing' => 'Tanggal Closing',

        'cara_bayar' => 'Cara Bayar',

        'status_kpr' => 'Status KPR',

        'tgl_masuk_berkas' => 'Tgl. Masuk Berkas',

        'tgl_acc' => 'Tgl. ACC KPR',

        'tgl_realisasi' => 'Tgl. Realisasi',

        'no_hp' => 'No. HP / WA',

        'no_ktp' => 'No. KTP / NIK',

        'pekerjaan' => 'Pekerjaan',

        'alamat' => 'Alamat Detail',
    ];

    public function __construct(
        $tahun,
        $bulan,
        $perumahaanId,
        $namaPerumahaan,
        array $columns,
        $isAgent = false
    ) {
        $this->tahun = $tahun;
        $this->bulan = $bulan;
        $this->perumahaanId = $perumahaanId;
        $this->namaPerumahaan = $namaPerumahaan;
        $this->isAgent = $isAgent;

        /**
         * Normalisasi pilihan kolom.
         *
         * nama_unit akan otomatis ditambahkan
         * jika tidak dipilih user.
         */
        $this->columns = $this->normalizeColumns($columns);

        /**
         * Tentukan bulan.
         */
        if (
            $this->bulan !== ''
            && $this->bulan !== null
            && $this->bulan !== 'all'
        ) {
            $this->months = [
                (int) $this->bulan
            ];
        } else {
            $this->months = range(1, 12);
        }

        /**
         * Judul laporan.
         */
        $this->title = 'Data Blok Deal ' . (
            $this->namaPerumahaan ?: 'Semua Perumahan'
        );

        /**
         * A = No
         * B dst = kolom pilihan
         */
        $totalColumns = count($this->columns) + 1;

        $this->lastColumn = Coordinate::stringFromColumnIndex(
            $totalColumns
        );
    }

    /**
     * Data dimulai dari A5.
     *
     * Row 1 = Judul
     * Row 2 = Perumahan
     * Row 3 = Tanggal export
     * Row 4 = spacer
     * Row 5 = header tabel
     * Row 6 dst = data
     */
    public function startCell(): string
    {
        return 'A5';
    }

    /**
     * =============================================================
     * ARRAY
     * =============================================================
     *
     * Struktur:
     *
     * Row 5
     * Header
     *
     * Row 6
     * BULAN: JANUARI
     *
     * Row 7 dst
     * Data Januari
     *
     * spacer
     *
     * BULAN: FEBRUARI
     *
     * Data Februari
     */
    public function array(): array
    {
        $this->monthRanges = [];
        $this->statusCells = [];

        $itemsByMonth = $this->itemsByMonth();

        $rows = [];

        /**
         * Karena startCell = A5,
         * maka:
         *
         * index 0 array = Excel row 5
         */
        $excelRow = 5;

        /**
         * =========================================================
         * HEADER TABEL - HANYA SEKALI
         * =========================================================
         */
        $rows[] = $this->headings();

        $this->headerRow = $excelRow;

        $excelRow++;

        /**
         * =========================================================
         * LOOP BULAN
         * =========================================================
         */
        foreach ($this->months as $month) {

            $monthItems = $itemsByMonth[$month] ?? collect();

            /**
             * Jika pilih ALL dan bulan tidak memiliki data,
             * jangan tampilkan bulan tersebut.
             */
            if (
                $this->bulan === 'all'
                && $monthItems->isEmpty()
            ) {
                continue;
            }

            /**
             * =====================================================
             * BARIS BULAN (2 Baris di-merge)
             * =====================================================
             */
            $monthRowStart = $excelRow;

            $monthRowData = array_fill(
                0,
                count($this->columns) + 1,
                ''
            );

            $monthRowData[0] =
                'BULAN: ' .
                strtoupper(
                    $this->namaBulan[$month]
                );

            $rows[] = $monthRowData;
            $excelRow++;

            // Baris kedua untuk merge vertikal
            $rows[] = array_fill(
                0,
                count($this->columns) + 1,
                ''
            );
            $excelRow++;

            $monthRowEnd = $excelRow - 1;

            /**
             * =====================================================
             * DATA BULAN
             * =====================================================
             */
            $dataStartRow = $excelRow;

            /**
             * Nomor selalu reset setiap bulan.
             */
            $number = 1;

            if ($monthItems->isEmpty()) {

                $emptyRow = array_fill(
                    0,
                    count($this->columns) + 1,
                    ''
                );

                $emptyRow[0] = 'Tidak ada data';

                $rows[] = $emptyRow;

                $excelRow++;

            } else {

                foreach ($monthItems as $item) {

                    $currentExcelRow = $excelRow;

                    /**
                     * Data.
                     */
                    $rows[] = $this->mapRow(
                        $item,
                        $number
                    );

                    /**
                     * =================================================
                     * STATUS KPR
                     * =================================================
                     */
                    if (
                        in_array(
                            'status_kpr',
                            $this->columns,
                            true
                        )
                    ) {

                        $status = strtolower(
                            trim(
                                (string) (
                                    $item->cara_bayar === 'kpr'
                                        ? (
                                            $item->kpr->status_kpr
                                            ?? '-'
                                        )
                                        : '-'
                                )
                            )
                        );

                        if (
                            in_array(
                                $status,
                                [
                                    'proses',
                                    'acc',
                                    'realisasi',
                                ],
                                true
                            )
                        ) {

                            $statusColumnIndex =
                                array_search(
                                    'status_kpr',
                                    $this->columns,
                                    true
                                ) + 2;

                            $statusColumn =
                                Coordinate::stringFromColumnIndex(
                                    $statusColumnIndex
                                );

                            $this->statusCells[] = [
                                'cell' =>
                                    $statusColumn .
                                    $currentExcelRow,

                                'status' => $status,
                            ];
                        }
                    }

                    $excelRow++;
                    $number++;
                }
            }

            /**
             * Simpan posisi bulan.
             */
            $this->monthRanges[] = [
                'month' => $month,

                'monthRowStart' => $monthRowStart,

                'monthRowEnd' => $monthRowEnd,

                'dataStartRow' => $dataStartRow,

                'dataEndRow' => $excelRow - 1,
            ];

        }

        return $rows;
    }

    /**
     * =============================================================
     * STYLES
     * =============================================================
     */
    public function styles(Worksheet $sheet)
    {
        /**
         * =========================================================
         * HEADER LAPORAN
         * =========================================================
         */

        /**
         * ROW 1
         */
        $sheet->mergeCells(
            "A1:{$this->lastColumn}1"
        );

        $sheet->setCellValue(
            'A1',
            $this->title
        );

        $sheet->getStyle(
            "A1:{$this->lastColumn}1"
        )->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
                'color' => [
                    'rgb' => '1F4E78',
                ],
            ],

            'alignment' => [
                'horizontal' =>
                    Alignment::HORIZONTAL_CENTER,

                'vertical' =>
                    Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(23);

        /**
         * =========================================================
         * ROW 2
         * =========================================================
         */
        $sheet->mergeCells(
            "A2:{$this->lastColumn}2"
        );

        $sheet->setCellValue(
            'A2',
            'Perumahan: ' .
            (
                $this->namaPerumahaan
                ?: 'Semua Perumahan'
            )
        );

        $sheet->getStyle(
            "A2:{$this->lastColumn}2"
        )->applyFromArray([
            'font' => [
                'size' => 10,
                'color' => [
                    'rgb' => '666666',
                ],
            ],

            'alignment' => [
                'horizontal' =>
                    Alignment::HORIZONTAL_CENTER,

                'vertical' =>
                    Alignment::VERTICAL_CENTER,
            ],
        ]);

        /**
         * =========================================================
         * ROW 3
         * =========================================================
         */
        $sheet->mergeCells(
            "A3:{$this->lastColumn}3"
        );

        $sheet->setCellValue(
            'A3',
            'Tanggal Export: ' .
            now()->format('d-m-Y H:i') .
            ' WIB'
        );

        $sheet->getStyle(
            "A3:{$this->lastColumn}3"
        )->applyFromArray([
            'font' => [
                'size' => 9,
                'color' => [
                    'rgb' => '666666',
                ],
            ],

            'alignment' => [
                'horizontal' =>
                    Alignment::HORIZONTAL_CENTER,

                'vertical' =>
                    Alignment::VERTICAL_CENTER,
            ],
        ]);

        /**
         * =========================================================
         * ROW 4
         * =========================================================
         */
        $sheet->getRowDimension(4)->setRowHeight(8);

        /**
         * =========================================================
         * HEADER TABEL - HANYA SEKALI
         * =========================================================
         */
        $sheet->getStyle(
            "A{$this->headerRow}:{$this->lastColumn}{$this->headerRow}"
        )->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 10,
                'color' => [
                    'rgb' => 'FFFFFF',
                ],
            ],

            'fill' => [
                'fillType' =>
                    Fill::FILL_SOLID,

                'startColor' => [
                    'rgb' => '1F4E78',
                ],
            ],

            'alignment' => [
                'horizontal' =>
                    Alignment::HORIZONTAL_CENTER,

                'vertical' =>
                    Alignment::VERTICAL_CENTER,

                'wrapText' => true,
            ],

            'borders' => [
                'allBorders' => [
                    'borderStyle' =>
                        Border::BORDER_THIN,

                    'color' => [
                        'rgb' => '000000',
                    ],
                ],
            ],
        ]);

        $sheet->getRowDimension(
            $this->headerRow
        )->setRowHeight(30);

        /**
         * =========================================================
         * STYLE SETIAP BULAN
         * =========================================================
         */
        foreach ($this->monthRanges as $range) {

            $monthRowStart =
                $range['monthRowStart'] ?? ($range['monthRow'] ?? 6);

            $monthRowEnd =
                $range['monthRowEnd'] ?? $monthRowStart;

            $dataStartRow =
                $range['dataStartRow'];

            $dataEndRow =
                $range['dataEndRow'];

            /**
             * =====================================================
             * BARIS BULAN (Merge vertikal 2 row & horizontal ke kanan)
             * =====================================================
             */
            $sheet->mergeCells(
                "A{$monthRowStart}:{$this->lastColumn}{$monthRowEnd}"
            );

            $sheet->getStyle(
                "A{$monthRowStart}:{$this->lastColumn}{$monthRowEnd}"
            )->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 11,
                    'color' => [
                        'rgb' => '000000',
                    ],
                ],

                'alignment' => [
                    'horizontal' =>
                        Alignment::HORIZONTAL_LEFT,

                    'vertical' =>
                        Alignment::VERTICAL_CENTER,
                ],
            ]);

            $sheet->getRowDimension(
                $monthRowStart
            )->setRowHeight(18);

            $sheet->getRowDimension(
                $monthRowEnd
            )->setRowHeight(18);

            /**
             * =====================================================
             * DATA
             * =====================================================
             */
            if ($dataEndRow >= $dataStartRow) {

                $sheet->getStyle(
                    "A{$dataStartRow}:{$this->lastColumn}{$dataEndRow}"
                )->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' =>
                                Border::BORDER_THIN,

                            'color' => [
                                'rgb' => '000000',
                            ],
                        ],
                    ],

                    'alignment' => [
                        'vertical' =>
                            Alignment::VERTICAL_CENTER,

                        'wrapText' => true,
                    ],
                ]);

                /**
                 * Alignment khusus kolom No (A) & Nama Unit pada baris data
                 */
                $sheet->getStyle(
                    "A{$dataStartRow}:A{$dataEndRow}"
                )->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    )
                    ->setVertical(
                        Alignment::VERTICAL_CENTER
                    );

                if (
                    in_array(
                        'nama_unit',
                        $this->columns,
                        true
                    )
                ) {
                    $namaUnitIndex =
                        array_search(
                            'nama_unit',
                            $this->columns,
                            true
                        ) + 2;

                    $namaUnitColumn =
                        Coordinate::stringFromColumnIndex(
                            $namaUnitIndex
                        );

                    $sheet->getStyle(
                        "{$namaUnitColumn}{$dataStartRow}:{$namaUnitColumn}{$dataEndRow}"
                    )->getAlignment()
                        ->setHorizontal(
                            Alignment::HORIZONTAL_CENTER
                        )
                        ->setVertical(
                            Alignment::VERTICAL_CENTER
                        );
                }

                /**
                 * Tinggi row otomatis menyesuaikan isi (wrapText).
                 *
                 * -1 = auto height di PhpSpreadsheet.
                 */
                for (
                    $row = $dataStartRow;
                    $row <= $dataEndRow;
                    $row++
                ) {
                    $sheet->getRowDimension(
                        $row
                    )->setRowHeight(-1);
                }
            }
        }

        /**
         * =========================================================
         * STATUS KPR
         * =========================================================
         */
        foreach ($this->statusCells as $statusCell) {

            $status =
                $statusCell['status'];

            $style = match ($status) {

                /**
                 * PROSES = Kuning
                 */
                'proses' => [
                    'fill' => [
                        'fillType' =>
                            Fill::FILL_SOLID,

                        'startColor' => [
                            'rgb' => 'FFFF00',
                        ],
                    ],

                    'font' => [
                        'bold' => true,

                        'color' => [
                            'rgb' => '000000',
                        ],
                    ],
                ],

                /**
                 * ACC = Hijau
                 */
                'acc' => [
                    'fill' => [
                        'fillType' =>
                            Fill::FILL_SOLID,

                        'startColor' => [
                            'rgb' => '00B050',
                        ],
                    ],

                    'font' => [
                        'bold' => true,

                        'color' => [
                            'rgb' => '000000',
                        ],
                    ],
                ],

                /**
                 * REALISASI = Biru
                 */
                'realisasi' => [
                    'fill' => [
                        'fillType' =>
                            Fill::FILL_SOLID,

                        'startColor' => [
                            'rgb' => '00B0F0',
                        ],
                    ],

                    'font' => [
                        'bold' => true,

                        'color' => [
                            'rgb' => '000000',
                        ],
                    ],
                ],

                default => [],
            };

            if (!empty($style)) {

                $sheet->getStyle(
                    $statusCell['cell']
                )->applyFromArray($style);

                $sheet->getStyle(
                    $statusCell['cell']
                )->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    )
                    ->setVertical(
                        Alignment::VERTICAL_CENTER
                    );
            }
        }

        /**
         * =========================================================
         * COLUMN WIDTH
         * =========================================================
         */
        $this->setColumnWidths($sheet);

        /**
         * =========================================================
         * FREEZE HEADER
         * =========================================================
         *
         * Header berada di row 5.
         * Data pertama dimulai setelah row 5.
         */
        $sheet->freezePane('A6');

        /**
         * =========================================================
         * PAGE SETUP
         * =========================================================
         */
        $sheet->getPageSetup()
            ->setOrientation(
                \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE
            )
            ->setPaperSize(
                \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4
            )
            ->setFitToWidth(1)
            ->setFitToHeight(0);

        $sheet->getPageMargins()
            ->setTop(0.4)
            ->setRight(0.3)
            ->setBottom(0.4)
            ->setLeft(0.3);

        return [];
    }

    /**
     * =============================================================
     * NORMALIZE COLUMN
     * =============================================================
     */
    protected function normalizeColumns(array $columns): array
    {
        /**
         * Buang value kosong.
         */
        $columns = array_values(
            array_filter(
                $columns,
                fn ($column) =>
                    is_string($column)
                    && trim($column) !== ''
            )
        );

        /**
         * Hapus duplicate.
         */
        $columns = array_values(
            array_unique($columns)
        );

        /**
         * Hanya gunakan column yang tersedia.
         * (tgl_masuk_berkas / tgl_acc / tgl_realisasi dikecualikan
         *  karena akan di-inject otomatis — tidak boleh dipilih manual)
         */
        $columns = array_values(
            array_filter(
                $columns,
                fn ($column) =>
                    array_key_exists(
                        $column,
                        $this->availableColumns
                    )
                    && !in_array(
                        $column,
                        ['tgl_masuk_berkas', 'tgl_acc', 'tgl_realisasi'],
                        true
                    )
            )
        );

        /**
         * =========================================================
         * NAMA UNIT WAJIB
         * =========================================================
         */
        if (
            !in_array(
                'nama_unit',
                $columns,
                true
            )
        ) {
            array_unshift(
                $columns,
                'nama_unit'
            );
        }

        /**
         * =========================================================
         * AUTO-INJECT TANGGAL KPR
         * =========================================================
         *
         * Jika status_kpr dipilih, sisipkan otomatis
         * tgl_masuk_berkas, tgl_acc, tgl_realisasi
         * tepat setelah status_kpr.
         */
        $statusKprIndex = array_search(
            'status_kpr',
            $columns,
            true
        );

        if ($statusKprIndex !== false) {
            array_splice(
                $columns,
                $statusKprIndex + 1,
                0,
                ['tgl_masuk_berkas', 'tgl_acc', 'tgl_realisasi']
            );
        }

        return $columns;
    }

    /**
     * =============================================================
     * HEADINGS
     * =============================================================
     */
    protected function headings(): array
    {
        return array_merge(
            ['No'],
            array_map(
                fn ($column) =>
                    $this->availableColumns[$column],
                $this->columns
            )
        );
    }

    /**
     * =============================================================
     * QUERY
     * =============================================================
     */
    protected function itemsByMonth()
    {
        $user = Auth::user();

        $query = PemesananUnit::with([
            'unit',
            'customer',
            'sales',
            'agent',
            'dataDiri',
            'kpr.bank',
        ])
            ->whereIn(
                'cara_bayar',
                ['kpr', 'cash']
            )
            ->where(
                'status_pengajuan',
                'acc'
            )
            ->where(
                'perumahaan_id',
                $this->perumahaanId
            )
            ->whereDoesntHave(
                'pengajuanPembatalan',
                function ($q) {
                    $q->where(
                        'status_pengajuan',
                        '!=',
                        'ditolak'
                    );
                }
            );

        /**
         * =========================================================
         * SOURCE
         * =========================================================
         */
        if ($this->isAgent === true || $this->isAgent === 'agent') {

            $query->where(
                'source',
                'agent'
            );

        } elseif ($this->isAgent === false || $this->isAgent === 'internal') {

            $query->where(
                function ($q) {
                    $q->where(
                        'source',
                        'internal'
                    )->orWhereNull(
                        'source'
                    );
                }
            );
        }

        /**
         * =========================================================
         * TAHUN
         * =========================================================
         */
        if ($this->tahun) {

            $query->whereYear(
                'tanggal_pemesanan',
                $this->tahun
            );
        }

        /**
         * =========================================================
         * BULAN
         * =========================================================
         */
        if (
            $this->bulan !== ''
            && $this->bulan !== null
            && $this->bulan !== 'all'
        ) {

            $query->whereMonth(
                'tanggal_pemesanan',
                (int) $this->bulan
            );
        }

        /**
         * =========================================================
         * MARKETING
         * =========================================================
         */
        if (
            ($this->isAgent === false || $this->isAgent === 'internal')
            && $user
            && $user->hasAnyRole(['Marketing', 'Marketing (ADL)', 'Marketing (LHR)'])
        ) {

            $query->where(
                'sales_id',
                $user->id
            );
        }

        /**
         * =========================================================
         * ORDER
         * =========================================================
         */
        $items = $query
            ->orderBy(
                'tanggal_pemesanan',
                'asc'
            )
            ->orderBy(
                'cara_bayar',
                'desc'
            )
            ->orderBy(
                'id',
                'asc'
            )
            ->get();

        /**
         * =========================================================
         * GROUP PER BULAN
         * =========================================================
         */
        $grouped = [];

        foreach ($this->months as $month) {

            $grouped[$month] = collect();
        }

        foreach (
            $items->groupBy(
                function ($item) {

                    return $item->tanggal_pemesanan
                        ? (
                            (int)
                            $item->tanggal_pemesanan
                                ->format('n')
                        )
                        : null;
                }
            ) as $month => $monthItems
        ) {

            if ($month !== null) {

                $grouped[(int) $month] =
                    $monthItems;
            }
        }

        return $grouped;
    }

    /**
     * =============================================================
     * MAP ROW
     * =============================================================
     */
    protected function mapRow(
        PemesananUnit $item,
        int $number
    ): array {

        $row = [
            $number
        ];

        foreach ($this->columns as $column) {

            $row[] = match ($column) {

                /**
                 * Nama Unit
                 */
                'nama_unit' =>
                    $item->unit->nama_unit
                    ?? '-',

                /**
                 * Customer
                 */
                'nama_user' =>
                    $item->customer->nama_lengkap
                    ?? $item->customer->username
                    ?? '-',

                /**
                 * Sales / Agent
                 */
                'nama_agent_sales' =>
                    ($item->source === 'agent' || ($this->isAgent === true && !$item->sales))

                        ? (
                            $item->agent->nama_agent
                            ?? '-'
                        )

                        : (
                            $item->sales->nama_lengkap
                            ?? $item->sales->username
                            ?? '-'
                        ),

                /**
                 * Tanggal Closing
                 */
                'tanggal_closing' =>
                    $item->tanggal_pemesanan
                        ? $item->tanggal_pemesanan
                            ->format('d-m-Y')
                        : '-',

                /**
                 * Cara Bayar
                 */
                'cara_bayar' =>
                    strtoupper(
                        (string) (
                            $item->cara_bayar
                            ?? '-'
                        )
                    ),

                /**
                 * Status KPR
                 */
                'status_kpr' =>
                    $item->cara_bayar === 'kpr'

                        ? strtoupper(
                            (string) (
                                $item->kpr->status_kpr
                                ?? '-'
                            )
                        )

                        : '-',

                /**
                 * Tanggal Masuk Berkas (KPR saja)
                 */
                'tgl_masuk_berkas' =>
                    $item->cara_bayar === 'kpr'
                        ? (
                            $item->kpr?->tanggal_masuk_berkas
                                ? $item->kpr->tanggal_masuk_berkas->format('d-m-Y')
                                : '-'
                        )
                        : '-',

                /**
                 * Tanggal ACC (KPR saja)
                 */
                'tgl_acc' =>
                    $item->cara_bayar === 'kpr'
                        ? (
                            $item->kpr?->tanggal_acc
                                ? $item->kpr->tanggal_acc->format('d-m-Y')
                                : '-'
                        )
                        : '-',

                /**
                 * Tanggal Realisasi (KPR saja)
                 */
                'tgl_realisasi' =>
                    $item->cara_bayar === 'kpr'
                        ? (
                            $item->kpr?->tanggal_realisasi
                                ? $item->kpr->tanggal_realisasi->format('d-m-Y')
                                : '-'
                        )
                        : '-',

                /**
                 * No HP — prefix apostrof agar Excel simpan sebagai teks
                 */
                'no_hp' =>
                    '\'' . (
                        $item->dataDiri->no_hp
                        ?? $item->customer->no_hp
                        ?? '-'
                    ),

                /**
                 * KTP — prefix apostrof agar Excel simpan sebagai teks
                 */
                'no_ktp' =>
                    '\'' . (
                        $item->dataDiri->no_ktp
                        ?? '-'
                    ),

                /**
                 * Pekerjaan
                 */
                'pekerjaan' =>
                    $item->dataDiri->pekerjaan
                    ?? '-',

                /**
                 * Alamat
                 */
                'alamat' =>
                    $this->formatAlamat($item),

                default => '-',
            };
        }

        return $row;
    }

    /**
     * =============================================================
     * FORMAT ALAMAT
     * =============================================================
     */
    protected function formatAlamat(
        PemesananUnit $item
    ): string {

        if (!$item->dataDiri) {
            return '-';
        }

        $parts = array_filter([
            $item->dataDiri->alamat_detail,
            $item->dataDiri->desa_nama,
            $item->dataDiri->kecamatan_nama,
            $item->dataDiri->kota_nama,
            $item->dataDiri->provinsi_nama,
        ]);

        return !empty($parts)
            ? implode(', ', $parts)
            : '-';
    }

    /**
     * =============================================================
     * COLUMN WIDTH
     * =============================================================
     */
    protected function setColumnWidths(
        Worksheet $sheet
    ): void {

        /**
         * No.
         */
        $sheet
            ->getColumnDimension('A')
            ->setWidth(6);

        /**
         * Column dinamis.
         */
        foreach (
            $this->columns as $index => $column
        ) {

            /**
             * A = No
             * B = column index 0
             */
            $letter =
                Coordinate::stringFromColumnIndex(
                    $index + 2
                );

            $width = match ($column) {

                'nama_unit' => 14,

                'nama_user' => 28,

                'nama_agent_sales' => 32,

                'tanggal_closing' => 16,

                'cara_bayar' => 13,

                'status_kpr' => 14,

                'no_hp' => 18,

                'no_ktp' => 20,

                'pekerjaan' => 26,

                'alamat' => 52,

                'tgl_masuk_berkas' => 17,

                'tgl_acc' => 15,

                'tgl_realisasi' => 17,

                default => 18,
            };

            $sheet
                ->getColumnDimension($letter)
                ->setWidth($width);
        }
    }
}
