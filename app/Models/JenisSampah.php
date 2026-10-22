<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisSampah extends Model
{
    protected $table = 'jenis_sampah';

    public const KATEGORI = ['organik', 'anorganik', 'b3'];

    protected $fillable = ['nama', 'kategori', 'keterangan', 'aktif'];

    protected function casts(): array
    {
        return ['aktif' => 'boolean'];
    }

    public function setoran(): HasMany
    {
        return $this->hasMany(Setoran::class, 'jenis_sampah_id');
    }
}
