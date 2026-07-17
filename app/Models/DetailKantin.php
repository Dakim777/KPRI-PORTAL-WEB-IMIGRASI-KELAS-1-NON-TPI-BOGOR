<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailKantin extends Model
{
    protected $fillable = [
        'transaksi_kantin_id',
        'produk_kantin_id',
        'qty',
        'harga_satuan',
        'harga_modal',
        'subtotal',
    ];

    public function transaksi()
    {
        return $this->belongsTo(TransaksiKantin::class, 'transaksi_kantin_id');
    }

    public function produk()
    {
        return $this->belongsTo(ProdukKantin::class, 'produk_kantin_id');
    }
}
