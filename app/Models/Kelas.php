<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    protected $table = 'kelas';

    public const TIPE = ['kelas', 'guru', 'tendik'];

    /** Kolom buku besar sengaja tidak fillable: hanya SetoranService yang boleh mengubahnya. */
    protected $fillable = ['nama', 'tipe'];

    protected function casts(): array
    {
        return [
            'total_gram' => 'integer',
            'sisa_gram' => 'integer',
            'total_poin' => 'integer',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function setoran(): HasMany
    {
        return $this->hasMany(Setoran::class);
    }
}
