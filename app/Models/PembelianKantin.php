<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembelianKantin extends Model
{
    use HasFactory;

    protected $fillable = [
        'kasir_id',
        'total_pembelian',
    ];

    public function kasir()
    {
        return $this->belongsTo(User::class, 'kasir_id');
    }

    public function detailPembelians()
    {
        return $this->hasMany(DetailPembelian::class);
    }
}
