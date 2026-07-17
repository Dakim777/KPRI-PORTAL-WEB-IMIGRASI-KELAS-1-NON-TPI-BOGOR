<?php

namespace App\Exports;

use App\Models\TransaksiPaspor;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PasporExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    ShouldAutoSize
{
    protected Builder $query;

    public function __construct(Builder $query)
    {
        // Accept the already-filtered query builder from the controller.
        $this->query = $query;
    }

    /**
     * The query used to fetch rows for the export.
     */
    public function query(): Builder
    {
        return $this->query->with('kasir')->orderBy('tanggal_kirim', 'asc');
    }

    /**
     * Sheet name.
     */
    public function title(): string
    {
        return 'Data Pengiriman Paspor';
    }

    /**
     * Column header row.
     */
    public function headings(): array
    {
        return [
            'No.',
            'Nama Pemohon',
            'No. Paspor',
            'No. Resi',
            'Tujuan',
            'Tanggal Kirim',
            'Biaya Ongkir (Rp)',
            'Biaya Tambahan (Rp)',
            'Total Biaya (Rp)',
            'Nama Petugas',
            'Status Pengiriman',
            'Dibuat Pada',
        ];
    }

    /**
     * Map each TransaksiPaspor row to the column order above.
     */
    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $row->nama_pemohon,
            $row->no_paspor ?? '-',
            $row->no_resi,
            $row->tujuan,
            $row->tanggal_kirim ? $row->tanggal_kirim->format('d/m/Y') : '-',
            $row->biaya_ongkir,
            $row->biaya_tambahan,
            $row->total_biaya,
            $row->nama_petugas,
            $row->status_pengiriman,
            $row->created_at->format('d/m/Y H:i'),
        ];
    }

    /**
     * Style the header row (bold + background color).
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            // Row 1 = headings
            1 => [
                'font' => [
                    'bold'  => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                    'size'  => 11,
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1E3A5F'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}
