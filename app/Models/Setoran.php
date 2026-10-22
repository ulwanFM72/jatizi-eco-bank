<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setoran extends Model
{
    use SoftDeletes;

    protected $table = 'setoran';

    /** `poin` dan `poin_pribadi` sengaja tidak fillable: hanya SetoranService (backend) yang menghitungnya. */
    protected $fillable = [
        'idempotency_key', 'kelas_id', 'penyetor_id', 'nama_penyetor', 'jenis_sampah_id',
        'berat_gram', 'tanggal', 'petugas_id', 'aturan_gram', 'aturan_poin',
        'status', 'catatan', 'dikoreksi_pada',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'berat_gram' => 'integer',
            'aturan_gram' => 'integer',
            'aturan_poin' => 'integer',
            'poin' => 'integer',
            'poin_pribadi' => 'integer',
            'dikoreksi_pada' => 'datetime',
        ];
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function jenis(): BelongsTo
    {
        return $this->belongsTo(JenisSampah::class, 'jenis_sampah_id');
    }

    public function penyetor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penyetor_id');
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    /**
     * Filter umum: dari, sampai (tanggal), kelas_id, q (nama penyetor, hanya bila $cariNama).
     * Kolom dikualifikasi dengan nama tabel agar aman dipakai bersama join.
     */
    public function scopeFilter(Builder $query, array $f, bool $cariNama = false): Builder
    {
        return $query
            ->when($f['dari'] ?? null, fn (Builder $q, $v) => $q->where('setoran.tanggal', '>=', $v))
            ->when($f['sampai'] ?? null, fn (Builder $q, $v) => $q->where('setoran.tanggal', '<=', $v))
            ->when($f['kelas_id'] ?? null, fn (Builder $q, $v) => $q->where('setoran.kelas_id', $v))
            ->when(
                $cariNama && ! empty($f['q']),
                fn (Builder $q) => $q->where('setoran.nama_penyetor', 'like', '%'.addcslashes((string) $f['q'], '%_\\').'%')
            );
    }
}
