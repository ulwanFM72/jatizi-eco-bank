<?php

namespace App\Services;

use App\Models\JenisSampah;
use App\Models\Kelas;
use App\Models\Setoran;

class StatistikService
{
    /** @return array{jumlah_transaksi:int,total_gram:int,total_poin:int} */
    public function ringkasan(): array
    {
        $row = Setoran::query()
            ->selectRaw('COUNT(*) as jumlah, COALESCE(SUM(berat_gram), 0) as gram, COALESCE(SUM(poin), 0) as poin')
            ->first();

        return [
            'jumlah_transaksi' => (int) $row->jumlah,
            'total_gram' => (int) $row->gram,
            'total_poin' => (int) $row->poin,
        ];
    }

    /** Total per kategori (organik, anorganik, b3); kategori tanpa setoran tetap muncul dengan nilai 0. */
    public function perKategori(): array
    {
        $data = Setoran::query()
            ->join('jenis_sampah', 'jenis_sampah.id', '=', 'setoran.jenis_sampah_id')
            ->groupBy('jenis_sampah.kategori')
            ->selectRaw('jenis_sampah.kategori as kategori, COUNT(*) as jumlah, SUM(setoran.berat_gram) as gram, SUM(setoran.poin) as poin')
            ->get()
            ->keyBy('kategori');

        return collect(JenisSampah::KATEGORI)->map(fn ($k) => [
            'kategori' => $k,
            'jumlah' => (int) ($data[$k]->jumlah ?? 0),
            'gram' => (int) ($data[$k]->gram ?? 0),
            'poin' => (int) ($data[$k]->poin ?? 0),
        ])->all();
    }

    /**
     * Peringkat kelas menurut total poin (lalu total berat). Kelas dengan poin dan berat sama
     * mendapat peringkat yang sama. Hanya tipe "kelas" (guru / tendik tidak ikut peringkat kelas).
     */
    public function peringkatKelas(): array
    {
        $kelas = Kelas::query()
            ->where('tipe', 'kelas')
            ->orderByDesc('total_poin')
            ->orderByDesc('total_gram')
            ->orderBy('nama')
            ->get();

        $hasil = [];
        $rank = 0;
        $prev = null;

        foreach ($kelas->values() as $i => $k) {
            $kunci = $k->total_poin.'-'.$k->total_gram;
            if ($kunci !== $prev) {
                $rank = $i + 1;
                $prev = $kunci;
            }
            $hasil[] = [
                'rank' => $rank,
                'id' => $k->id,
                'nama' => $k->nama,
                'total_poin' => $k->total_poin,
                'total_gram' => $k->total_gram,
                'sisa_gram' => $k->sisa_gram,
            ];
        }

        return $hasil;
    }
}
