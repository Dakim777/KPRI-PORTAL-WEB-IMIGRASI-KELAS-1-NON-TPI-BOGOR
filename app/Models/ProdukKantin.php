<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class ProdukKantin extends Model
{
    use LogsActivity, SoftDeletes;
    protected $fillable = [
        'nama_produk',
        'barcode',
        'kategori_id',
        'harga_beli',
        'harga',
        'stok',
    ];

    /** Stok di bawah angka ini dianggap "menipis" */
    const STOK_MENIPIS = 5;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function detailKantins()
    {
        return $this->hasMany(DetailKantin::class, 'produk_kantin_id');
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriKantin::class, 'kategori_id');
    }

    public function isStokHabis(): bool
    {
        return $this->stok === 0;
    }

    public function isStokMenipis(): bool
    {
        return $this->stok > 0 && $this->stok <= self::STOK_MENIPIS;
    }
}
