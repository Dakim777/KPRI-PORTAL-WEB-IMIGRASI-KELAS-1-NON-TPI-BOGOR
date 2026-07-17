<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class TransaksiPaspor extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'kasir_id',
        'id_transaksi',
        'no_struk',
        'nama_penerima',
        'jumlah_paspor',
        'nama_paspor_list',
        'alamat_tujuan_pengiriman',
        'no_resi',
        'total_pembayaran_pemohon',
        'biaya_kurir',
        'biaya_modal_packaging',
        'harga_materai',
        'harga_surat_kuasa',
        'tanggal_transaksi',
        'tanggal_pengiriman_paspor',
        'tanggal_paspor_diterima',
        'status_pengiriman',
        'no_hp',
        'metode_pembayaran',
        'handling_fee',
        'estimasi_tanggal_selesai',
    ];

    protected $casts = [
        'nama_paspor_list'          => 'encrypted:array',
        'alamat_tujuan_pengiriman'  => 'encrypted',
        'no_hp'                     => 'encrypted',
        'tanggal_transaksi'         => 'date',
        'tanggal_pengiriman_paspor' => 'date',
        'tanggal_paspor_diterima'   => 'date',
        'estimasi_tanggal_selesai'  => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    /**
     * Status badge color map for views.
     */
    public const STATUS_COLORS = [
        'transaksi' => 'bg-gray-100 text-gray-800',
        'diproses'  => 'bg-yellow-100 text-yellow-800',
        'selesai'   => 'bg-green-100 text-green-800',
        'batal'     => 'bg-red-100 text-red-800',
    ];

    public const STATUS_ICONS = [
        'transaksi' => '📝',
        'diproses'  => '🕐',
        'selesai'   => '✅',
        'batal'     => '❌',
    ];

    public static function statusList(): array
    {
        return ['transaksi', 'diproses', 'selesai', 'batal'];
    }

    public function statusColorClass(): string
    {
        return self::STATUS_COLORS[$this->status_pengiriman] ?? 'bg-gray-100 text-gray-700';
    }

    public function statusIcon(): string
    {
        return self::STATUS_ICONS[$this->status_pengiriman] ?? '❓';
    }

    /**
     * Auto-calculate prices before saving.
     */
    protected static function booted(): void
    {
        static::saving(function (self $model) {
            $model->harga_materai = (int) $model->jumlah_paspor * 12000;
            $model->harga_surat_kuasa = (int) $model->jumlah_paspor * 1000;
        });
    }

    // ─── Relationships ─────────────────────────────────────────────────────

    public function kasir()
    {
        return $this->belongsTo(User::class, 'kasir_id');
    }

    // ─── Scopes ────────────────────────────────────────────────────────────

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('nama_penerima', 'like', "%{$term}%")
              ->orWhere('id_transaksi', 'like', "%{$term}%")
              ->orWhere('no_resi', 'like', "%{$term}%");
        });
    }

    public function scopeTanggal(Builder $query, ?string $tanggal): Builder
    {
        return $tanggal ? $query->whereDate('tanggal_transaksi', $tanggal) : $query;
    }

    public function scopeTanggalRange(Builder $query, ?string $dari, ?string $sampai): Builder
    {
        if ($dari)   $query->whereDate('tanggal_transaksi', '>=', $dari);
        if ($sampai) $query->whereDate('tanggal_transaksi', '<=', $sampai);
        return $query;
    }

    public function scopeBulanTahun(Builder $query, ?string $bulan, ?string $tahun): Builder
    {
        if ($bulan) $query->whereMonth('tanggal_transaksi', $bulan);
        if ($tahun) $query->whereYear('tanggal_transaksi', $tahun);
        return $query;
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $status ? $query->where('status_pengiriman', $status) : $query;
    }
}
