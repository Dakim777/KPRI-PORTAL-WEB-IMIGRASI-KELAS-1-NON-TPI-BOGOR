<?php

namespace App\Exports;

use App\Models\ProdukKantin;
use App\Models\DetailKantin;
use App\Models\DetailPembelian;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LaporanPenjualanExport implements WithMultipleSheets
{
    use Exportable;

    protected ?string $tanggalDari;
    protected ?string $tanggalSampai;

    public function __construct(?string $tanggalDari = null, ?string $tanggalSampai = null)
    {
        $this->tanggalDari   = $tanggalDari;
        $this->tanggalSampai = $tanggalSampai;
    }

    public function sheets(): array
    {
        // Calculate all data here so we don't duplicate queries
        $laporanData = $this->calculateData();

        return [
            new LaporanPenjualanDetailSheet($laporanData),
            new LaporanPenjualanGlobalSheet($laporanData),
        ];
    }

    private function calculateData(): array
    {
        $produks = ProdukKantin::withTrashed()->orderBy('nama_produk')->get();
        $laporanData = [];

        foreach ($produks as $produk) {
            $masukData = DetailPembelian::where('produk_kantin_id', $produk->id)
                ->whereHas('pembelianKantin', function ($q) {
                    if ($this->tanggalDari) $q->whereDate('created_at', '>=', $this->tanggalDari);
                    if ($this->tanggalSampai) $q->whereDate('created_at', '<=', $this->tanggalSampai);
                })->get();
            $masuk = $masukData->sum('qty');
            $pembelianBarang = $masukData->sum(function($item) { return $item->qty * $item->harga_satuan; });

            $keluarData = DetailKantin::where('produk_kantin_id', $produk->id)
                ->whereHas('transaksi', function ($q) {
                    if ($this->tanggalDari) $q->whereDate('created_at', '>=', $this->tanggalDari);
                    if ($this->tanggalSampai) $q->whereDate('created_at', '<=', $this->tanggalSampai);
                })->orderBy('created_at')->get();
            $keluar = $keluarData->sum('qty');

            $hppGroups = [];
            foreach ($keluarData as $item) {
                if (!isset($hppGroups[$item->harga_modal])) {
                    $hppGroups[$item->harga_modal] = 0;
                }
                $hppGroups[$item->harga_modal] += $item->qty;
            }

            if (empty($hppGroups)) {
                foreach ($masukData as $item) {
                    if (!isset($hppGroups[$item->harga_satuan])) {
                        $hppGroups[$item->harga_satuan] = 0;
                    }
                }
            }

            if (empty($hppGroups)) {
                $hppGroups[$produk->harga_beli ?? 0] = 0;
            }

            $uniqueHpps = array_keys($hppGroups);
            
            $hppLama = $uniqueHpps[0] ?? 0;
            $qtyHppLama = $hppGroups[$hppLama] ?? 0;
            
            $hppBaru = 0;
            $qtyHppBaru = 0;

            if (count($uniqueHpps) > 1) {
                $hppBaru = $uniqueHpps[1];
                $qtyHppBaru = $hppGroups[$hppBaru] ?? 0;
                
                for ($i = 2; $i < count($uniqueHpps); $i++) {
                    $qtyHppBaru += $hppGroups[$uniqueHpps[$i]];
                    $hppBaru = $uniqueHpps[$i]; 
                }
            }

            $stokAkhir = DetailPembelian::where('produk_kantin_id', $produk->id)->sum('sisa_qty');

            if ($this->tanggalSampai) {
                $masukAfter = DetailPembelian::where('produk_kantin_id', $produk->id)
                    ->whereHas('pembelianKantin', function ($q) {
                        $q->whereDate('created_at', '>', $this->tanggalSampai);
                    })->sum('qty');
                
                $keluarAfter = DetailKantin::where('produk_kantin_id', $produk->id)
                    ->whereHas('transaksi', function ($q) {
                        $q->whereDate('created_at', '>', $this->tanggalSampai);
                    })->sum('qty');

                $stokAkhir = $stokAkhir - $masukAfter + $keluarAfter;
            }

            $stokAwal = $stokAkhir - $masuk + $keluar;

            if ($masuk == 0 && $keluar == 0 && $stokAwal == 0 && $stokAkhir == 0) {
                continue;
            }

            $hargaJual = $produk->harga ?? 0;

            $dateStr = '';
            if ($this->tanggalDari && $this->tanggalSampai) {
                $dateStr = date('j-M-Y', strtotime($this->tanggalDari)) . ' s/d ' . date('j-M-Y', strtotime($this->tanggalSampai));
            } elseif ($this->tanggalDari) {
                $dateStr = 'Mulai ' . date('j-M-Y', strtotime($this->tanggalDari));
            } elseif ($this->tanggalSampai) {
                $dateStr = 'Sampai ' . date('j-M-Y', strtotime($this->tanggalSampai));
            } else {
                $dateStr = 'Semua Waktu';
            }

            $laporanData[] = [
                'nama_produk' => $produk->nama_produk,
                'tanggal' => $dateStr,
                'stok_awal' => $stokAwal,
                'masuk' => $masuk,
                'keluar' => $keluar,
                'stok_akhir' => $stokAkhir,
                'hpp_lama' => $hppLama,
                'qty_hpp_lama' => $qtyHppLama,
                'hpp_baru' => $hppBaru,
                'qty_hpp_baru' => $qtyHppBaru,
                'harga_jual' => $hargaJual,
                'pembelian_barang' => $pembelianBarang,
            ];
        }

        return $laporanData;
    }
}
