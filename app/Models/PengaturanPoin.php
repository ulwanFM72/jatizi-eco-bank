<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanPoin extends Model
{
    protected $table = 'pengaturan_poin';

    protected $fillable = ['gram_per_unit', 'poin_per_unit'];

    protected function casts(): array
    {
        return [
            'gram_per_unit' => 'integer',
            'poin_per_unit' => 'integer',
        ];
    }

    /** Aturan yang berlaku saat ini (baris tunggal). Dibuat dengan default 1000 gram = 5 poin bila belum ada. */
    public static function current(): self
    {
        return static::query()->first()
            ?? static::create(['gram_per_unit' => 1000, 'poin_per_unit' => 5]);
    }
}
