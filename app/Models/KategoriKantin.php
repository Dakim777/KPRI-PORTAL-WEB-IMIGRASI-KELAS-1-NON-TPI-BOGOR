<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriKantin extends Model
{
    protected $fillable = ['nama_kategori', 'deskripsi'];

    public function produks()
    {
        return $this->hasMany(ProdukKantin::class, 'kategori_id');
    }
}
