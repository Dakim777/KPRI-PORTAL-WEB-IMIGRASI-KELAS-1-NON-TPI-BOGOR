<?php

namespace App\Exports;

use App\Models\DetailPembelian;
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

class LaporanPembelianExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    ShouldAutoSize
{
    protected ?string $tanggalDari;
    protected ?string $tanggalSampai;
    protected int $rowNumber = 0;

    public function __construct(?string $tanggalDari = null, ?string $tanggalSampai = null)
    {
        $this->tanggalDari   = $tanggalDari;
        $this->tanggalSampai = $tanggalSampai;
    }

    public function query(): Builder
    {
        $query = DetailPembelian::query()
            ->with(['pembelianKantin', 'produkKantin'])
            ->whereHas('pembelianKantin', function ($q) {
                if ($this->tanggalDari) {
                    $q->whereDate('created_at', '>=', $this->tanggalDari);
                }
                if ($this->tanggalSampai) {
                    $q->whereDate('created_at', '<=', $this->tanggalSampai);
                }
            })
            ->join('pembelian_kantins', 'detail_pembelians.pembelian_kantin_id', '=', 'pembelian_kantins.id')
            ->orderBy('pembelian_kantins.created_at', 'asc')
            ->select('detail_pembelians.*');

        return $query;
    }

    public function title(): string
    {
        return 'Pembelian';
    }

    public function headings(): array
    {
        // no | nama produk | kode barang | tanggal pembelian | jumlah barang | total harga pembelian | harga satuan |
        return [
            'no',
            'nama produk',
            'kode barang',
            'tanggal pembelian',
            'jumlah barang',
            'total harga pembelian',
            'harga satuan',
        ];
    }

    /**
     * @param DetailPembelian $detail
     */
    public function map($detail): array
    {
        $this->rowNumber++;
        $transaksi = $detail->pembelianKantin;
        $produk = $detail->produkKantin;

        return [
            $this->rowNumber,
            $produk->nama_produk ?? 'Produk Terhapus',
            $produk->barcode ?? '—',
            $transaksi->created_at->format('j/n/Y'),
            $detail->qty,
            $detail->subtotal,
            $detail->harga_satuan,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF15803D'], // Green-700
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }
}
