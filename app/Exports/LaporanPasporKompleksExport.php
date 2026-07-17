<?php

namespace App\Exports;

use App\Models\TransaksiPaspor;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

class LaporanPasporKompleksExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $dari;
    protected $sampai;

    public function __construct($dari = null, $sampai = null)
    {
        $this->dari = $dari;
        $this->sampai = $sampai;
    }

    public function view(): View
    {
        $query = TransaksiPaspor::query()->with('kasir');

        if ($this->dari && $this->sampai) {
            $query->tanggalRange($this->dari, $this->sampai);
        }

        $paspors = $query->orderBy('tanggal_transaksi', 'desc')->get();

        return view('paspor.exports.kompleks', [
            'paspors' => $paspors
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $range = 'A1:' . $highestColumn . $highestRow;

        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        // Beri border lebih tebal (medium) untuk outline luar tabel agar lebih profesional
        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
