<?php

namespace App\Support;

use App\Models\Setoran;

/**
 * Menentukan data setoran yang boleh dilihat tiap jenis pengunjung:
 *  - publik : tamu (nama disamarkan, tanpa petugas dan catatan)
 *  - pribadi: siswa / guru / tendik melihat setoran miliknya sendiri
 *  - staff  : admin dan petugas melihat data lengkap
 */
final class SetoranPresenter
{
    public static function baris(Setoran $s, string $mode): array
    {
        $baris = [
            'id' => $s->id,
            'tanggal' => $s->tanggal?->toDateString(),
            'kelas' => $s->kelas?->nama,
            'jenis' => $s->jenis?->nama,
            'kategori' => $s->jenis?->kategori,
            'berat_gram' => $s->berat_gram,
            'poin' => $mode === 'pribadi' ? (int) ($s->poin_pribadi ?? 0) : $s->poin,
            'penyetor' => $mode === 'publik' ? self::samarkan($s->nama_penyetor) : $s->nama_penyetor,
            'status' => $s->status,
        ];

        if ($mode === 'pribadi') {
            $baris['petugas'] = $s->petugas?->name;
            $baris['catatan'] = $s->catatan;
        }

        if ($mode === 'staff') {
            $baris += [
                'kelas_id' => $s->kelas_id,
                'jenis_sampah_id' => $s->jenis_sampah_id,
                'penyetor_id' => $s->penyetor_id,
                'petugas' => $s->petugas?->name,
                'catatan' => $s->catatan,
                'dicatat_pada' => $s->created_at?->toIso8601String(),
                'dikoreksi_pada' => $s->dikoreksi_pada?->toIso8601String(),
            ];
        }

        return $baris;
    }

    /** "Ahmad Fauzi Rahman" menjadi "Ahmad F." */
    public static function samarkan(?string $nama): string
    {
        $bagian = preg_split('/\s+/', trim((string) $nama), -1, PREG_SPLIT_NO_EMPTY);
        if (! $bagian) {
            return 'Anonim';
        }

        $inisial = isset($bagian[1]) ? ' '.mb_strtoupper(mb_substr($bagian[1], 0, 1)).'.' : '';

        return $bagian[0].$inisial;
    }
}
