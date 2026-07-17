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
use PhpOffice\PhpSpreadsheet\Style\Border;

class LaporanPenjualanGlobalSheet implements FromView, ShouldAutoSize, WithStyles, WithTitle
{
    protected array $laporanData;

    public function __construct(array $laporanData)
    {
        $this->laporanData = $laporanData;
    }

    public function view(): View
    {
        return view('kantin.excel.laporan_penjualan_global', [
            'laporanData' => $this->laporanData
        ]);
    }

    public function title(): string
    {
        return 'General - Global';
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = count($this->laporanData) + 5; // 1 title, 1 period, 1 blank, 1 header, data..., 1 total row

        $styles = [
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
            2 => [
                'font' => ['bold' => true, 'size' => 11],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
            4 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE2EFDA'], // Light green
                ],
            ],
            $lastRow => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFFFFF00'], // Yellow
                ],
            ],
        ];

        // Apply borders to the table (row 4 to $lastRow)
        $sheet->getStyle("A4:H{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);
        
        $sheet->mergeCells("A1:H1");
        $sheet->mergeCells("A2:H2");

        return $styles;
    }
}
