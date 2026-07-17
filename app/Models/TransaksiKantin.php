<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class TransaksiKantin extends Model
{
    use LogsActivity, SoftDeletes;
    protected $fillable = [
        'kasir_id',
        'total_harga',
        'jumlah_bayar',
        'kembalian',
        'metode_bayar',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function kasir()
    {
        return $this->belongsTo(User::class, 'kasir_id');
    }

    public function details()
    {
        return $this->hasMany(DetailKantin::class, 'transaksi_kantin_id');
    }
}
