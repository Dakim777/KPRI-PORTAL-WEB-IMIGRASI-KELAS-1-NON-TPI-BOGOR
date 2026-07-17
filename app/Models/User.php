<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isKasirKantin(): bool
    {
        return $this->role === 'kasir_kantin';
    }

    public function isKasirPaspor(): bool
    {
        return $this->role === 'kasir_paspor';
    }

    public function transaksiKantins()
    {
        return $this->hasMany(TransaksiKantin::class, 'kasir_id');
    }

    public function transaksiPaspors()
    {
        return $this->hasMany(TransaksiPaspor::class, 'kasir_id');
    }
}
