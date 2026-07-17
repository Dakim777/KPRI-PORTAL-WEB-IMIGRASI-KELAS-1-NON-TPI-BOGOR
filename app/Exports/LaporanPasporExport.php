<?php

namespace App\Exports;

use App\Models\TransaksiPaspor;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class LaporanPasporExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    ShouldAutoSize
{
    protected ?string $dari;
    protected ?string $sampai;

    public function __construct(?string $dari = null, ?string $sampai = null)
    {
        $this->dari   = $dari;
        $this->sampai = $sampai;
    }

    public function query(): Builder
    {
        $query = TransaksiPaspor::query()
            ->with('kasir')
            ->orderBy('tanggal_kirim', 'asc');

        if ($this->dari) {
            $query->whereDate('tanggal_kirim', '>=', $this->dari);
        }
        if ($this->sampai) {
            $query->whereDate('tanggal_kirim', '<=', $this->sampai);
        }

        return $query;
    }

    public function title(): string
    {
        return 'Laporan Pengiriman Paspor';
    }

    public function headings(): array
    {
        return [
            'No.',
            'Tanggal Kirim',
            'Nama Pemohon',
            'No. Resi',
            'No. Paspor',
            'Tujuan',
            'Status',
            'Nama Petugas',
            'Kasir Input',
            'Biaya Ongkir (Rp)',
            'Biaya Tambahan (Rp)',
            'Total Biaya (Rp)',
        ];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $row->tanggal_kirim instanceof \Carbon\Carbon
                ? $row->tanggal_kirim->format('d/m/Y')
                : $row->tanggal_kirim,
            $row->nama_pemohon,
            $row->no_resi,
            $row->no_paspor ?? '-',   // auto-decrypted by Eloquent cast
            $row->tujuan ?? '-',       // auto-decrypted by Eloquent cast
            $row->status_pengiriman,
            $row->nama_petugas,
            $row->kasir->name ?? '-',
            $row->biaya_ongkir,
            $row->biaya_tambahan,
            $row->total_biaya,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold'  => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                    'size'  => 11,
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF0D1F3C'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }
}
