<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class LaporanPenjualanDetailSheet implements FromView, ShouldAutoSize, WithStyles, WithTitle
{
    protected array $laporanData;

    public function __construct(array $laporanData)
    {
        $this->laporanData = $laporanData;
    }

    public function view(): View
    {
        return view('kantin.excel.laporan_penjualan', [
            'laporanData' => $this->laporanData
        ]);
    }

    public function title(): string
    {
        return 'Laporan Detail';
    }

    public function styles(Worksheet $sheet)
    {
        $styles = [];
        $currentRow = 1;

        foreach ($this->laporanData as $data) {
            // Header Row (Product Name)
            $styles[$currentRow] = [
                'font' => ['bold' => true, 'color' => ['argb' => 'FF000000']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFFFFF00'], // Yellow
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ];
            $sheet->mergeCells("A{$currentRow}:V{$currentRow}");

            // Subheader Row (Column Names)
            $styles[$currentRow + 1] = [
                'font' => ['bold' => true, 'color' => ['argb' => 'FF000000']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFFFFF00'], // Yellow
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ];

            // Data Row
            $styles[$currentRow + 2] = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ];

            $currentRow += 4; // Move to next product block
        }

        return $styles;
    }
}
