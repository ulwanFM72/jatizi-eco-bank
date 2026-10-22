<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLES = ['admin', 'petugas', 'siswa', 'guru', 'tendik'];
    public const STAFF_ROLES = ['admin', 'petugas'];
    public const PENYETOR_ROLES = ['siswa', 'guru', 'tendik'];

    /** Kolom buku besar (total_gram, sisa_gram, total_poin) sengaja tidak boleh diisi massal. */
    protected $fillable = [
        'name', 'username', 'email', 'password', 'role', 'nomor_induk', 'kelas_id', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'kelas_id' => 'integer',
            'total_gram' => 'integer',
            'sisa_gram' => 'integer',
            'total_poin' => 'integer',
        ];
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function setoranSebagaiPenyetor(): HasMany
    {
        return $this->hasMany(Setoran::class, 'penyetor_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStaff(): bool
    {
        return in_array($this->role, self::STAFF_ROLES, true);
    }
}
