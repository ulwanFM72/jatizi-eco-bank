<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\PengaturanPoin;
use App\Models\Setoran;
use App\Models\User;
use App\Support\PointCalculator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Satu-satunya tempat poin dan sisa berat dihitung (di backend, bukan di browser).
 *
 * Prinsip konsistensi:
 *  1. Setiap perubahan (catat / koreksi / hapus) berjalan dalam satu transaksi database.
 *  2. Baris kelas dan penyetor yang terdampak dikunci (SELECT ... FOR UPDATE) lebih dulu,
 *     sehingga dua petugas yang menyimpan bersamaan diproses berurutan.
 *  3. Setelah data berubah, buku besar (poin per setoran, total_gram, sisa_gram, total_poin)
 *     dihitung ulang dengan memutar ulang seluruh setoran aktif berurutan menurut id.
 *     Hasilnya selalu deterministik, jadi mengoreksi atau menghapus setoran
 *     tidak pernah membuat poin terhitung dua kali.
 *  4. Kunci idempotensi mencegah klik ganda / kirim ulang form mencatat setoran dua kali.
 */
class SetoranService
{
    /**
     * @param array{penyetor_id:?int,nama_penyetor:?string,kelas_id:?int,jenis_sampah_id:int,berat_gram:int,tanggal:string,catatan:?string,idempotency_key:?string} $data
     */
    public function catat(array $data, User $petugas): Setoran
    {
        $data = $this->lengkapiPenyetor($data);

        return DB::transaction(function () use ($data, $petugas) {
            // Penguncian harus menjadi pernyataan pertama dalam transaksi.
            $this->kunci([$data['kelas_id']], [$data['penyetor_id']]);

            if (! empty($data['idempotency_key'])) {
                $ada = Setoran::withTrashed()->where('idempotency_key', $data['idempotency_key'])->first();
                if ($ada) {
                    return $ada; // kirim ulang: kembalikan yang sudah tersimpan
                }
            }

            $aturan = PengaturanPoin::current();

            $setoran = Setoran::create([
                'idempotency_key' => $data['idempotency_key'] ?? null,
                'kelas_id' => $data['kelas_id'],
                'penyetor_id' => $data['penyetor_id'],
                'nama_penyetor' => $data['nama_penyetor'],
                'jenis_sampah_id' => $data['jenis_sampah_id'],
                'berat_gram' => $data['berat_gram'],
                'tanggal' => $data['tanggal'],
                'petugas_id' => $petugas->id, // petugas penerima selalu dari akun yang login
                'aturan_gram' => $aturan->gram_per_unit,
                'aturan_poin' => $aturan->poin_per_unit,
                'status' => 'valid',
                'catatan' => $data['catatan'] ?? null,
            ]);

            $this->hitungUlang([$setoran->kelas_id], [$setoran->penyetor_id]);

            return $setoran->refresh();
        });
    }

    /** Koreksi setoran (khusus admin). Poin dihitung ulang, bukan ditambah/dikurangi manual. */
    public function koreksi(Setoran $setoran, array $data): Setoran
    {
        $data = $this->lengkapiPenyetor($data);

        return DB::transaction(function () use ($setoran, $data) {
            $this->kunci(
                [$setoran->kelas_id, $data['kelas_id']],
                [$setoran->penyetor_id, $data['penyetor_id']],
            );

            $s = Setoran::whereKey($setoran->id)->lockForUpdate()->firstOrFail();
            $kelasLama = $s->kelas_id;
            $userLama = $s->penyetor_id;
            $this->kunci([$kelasLama], [$userLama]); // aman bila ada perubahan bersamaan

            $s->update([
                'kelas_id' => $data['kelas_id'],
                'penyetor_id' => $data['penyetor_id'],
                'nama_penyetor' => $data['nama_penyetor'],
                'jenis_sampah_id' => $data['jenis_sampah_id'],
                'berat_gram' => $data['berat_gram'],
                'tanggal' => $data['tanggal'],
                'catatan' => $data['catatan'] ?? null,
                'status' => 'dikoreksi',
                'dikoreksi_pada' => now(),
            ]);

            if ($data['penyetor_id'] === null) {
                $s->forceFill(['poin_pribadi' => null])->save();
            }

            $this->hitungUlang([$kelasLama, $s->kelas_id], [$userLama, $s->penyetor_id]);

            return $s->refresh();
        });
    }

    /** Hapus setoran (soft delete) lalu hitung ulang buku besar yang terdampak. */
    public function hapus(Setoran $setoran): void
    {
        DB::transaction(function () use ($setoran) {
            $this->kunci([$setoran->kelas_id], [$setoran->penyetor_id]);

            $s = Setoran::whereKey($setoran->id)->lockForUpdate()->firstOrFail();
            $kelasId = $s->kelas_id;
            $userId = $s->penyetor_id;
            $s->delete();

            $this->hitungUlang([$kelasId], [$userId]);
        });
    }

    /**
     * Bila setoran dikaitkan ke akun penyetor, nama dan kelas diambil dari akun itu
     * (klien tidak bisa memalsukannya). Bila tidak, nama dan kelas wajib diisi manual.
     */
    private function lengkapiPenyetor(array $data): array
    {
        $data['penyetor_id'] = $data['penyetor_id'] ?? null;

        if ($data['penyetor_id']) {
            $user = User::findOrFail($data['penyetor_id']);

            if (! in_array($user->role, User::PENYETOR_ROLES, true) || ! $user->is_active) {
                throw ValidationException::withMessages([
                    'penyetor_id' => 'Akun penyetor harus berperan siswa, guru, atau tenaga administrasi dan masih aktif.',
                ]);
            }

            $data['nama_penyetor'] = $user->name;
            if ($user->kelas_id) {
                $data['kelas_id'] = $user->kelas_id;
            }
        }

        if (empty($data['kelas_id'])) {
            throw ValidationException::withMessages(['kelas_id' => 'Kelas wajib dipilih.']);
        }
        if (empty($data['nama_penyetor'])) {
            throw ValidationException::withMessages(['nama_penyetor' => 'Nama penyetor wajib diisi.']);
        }

        return $data;
    }

    /** Kunci baris kelas lalu user dengan urutan id naik agar tidak terjadi deadlock. */
    private function kunci(array $kelasIds, array $userIds): void
    {
        $kelasIds = collect($kelasIds)->filter()->unique()->sort()->values();
        if ($kelasIds->isNotEmpty()) {
            Kelas::whereIn('id', $kelasIds)->orderBy('id')->lockForUpdate()->get();
        }

        $userIds = collect($userIds)->filter()->unique()->sort()->values();
        if ($userIds->isNotEmpty()) {
            User::whereIn('id', $userIds)->orderBy('id')->lockForUpdate()->get();
        }
    }

    /** Hitung ulang buku besar kelas dan penyetor yang terdampak. */
    private function hitungUlang(array $kelasIds, array $userIds): void
    {
        foreach (collect($kelasIds)->filter()->unique() as $id) {
            $kelas = Kelas::findOrFail($id);
            $this->putarUlang(Setoran::query()->where('kelas_id', $id), $kelas, 'poin');
        }

        foreach (collect($userIds)->filter()->unique() as $id) {
            $user = User::find($id);
            if ($user) {
                $this->putarUlang(Setoran::query()->where('penyetor_id', $id), $user, 'poin_pribadi');
            }
        }
    }

    /**
     * Putar ulang semua setoran aktif satu pemilik buku besar (kelas atau user), berurutan menurut id.
     * Tiap setoran memakai snapshot aturan poin miliknya sendiri.
     */
    private function putarUlang(Builder $query, Model $pemilik, string $kolomPoin): void
    {
        $sisa = 0;
        $totalGram = 0;
        $totalPoin = 0;

        foreach ($query->orderBy('id')->get() as $s) {
            $hasil = (new PointCalculator($s->aturan_gram, $s->aturan_poin))->apply($sisa, $s->berat_gram);

            $sisa = $hasil['sisa_gram'];
            $totalGram += $s->berat_gram;
            $totalPoin += $hasil['poin'];

            if ($s->{$kolomPoin} !== $hasil['poin']) {
                $s->forceFill([$kolomPoin => $hasil['poin']])->saveQuietly();
            }
        }

        $pemilik->forceFill([
            'total_gram' => $totalGram,
            'sisa_gram' => $sisa,
            'total_poin' => $totalPoin,
        ])->save();
    }
}
