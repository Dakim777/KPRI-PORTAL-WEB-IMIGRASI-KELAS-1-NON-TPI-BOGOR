<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPembelian extends Model
{
    use HasFactory;

    protected $fillable = [
        'pembelian_kantin_id',
        'produk_kantin_id',
        'qty',
        'sisa_qty',
        'harga_satuan',
        'subtotal',
    ];

    public function pembelianKantin()
    {
        return $this->belongsTo(PembelianKantin::class);
    }

    public function produkKantin()
    {
        return $this->belongsTo(ProdukKantin::class);
    }
}
