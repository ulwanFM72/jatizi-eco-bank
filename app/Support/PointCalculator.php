<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Aturan poin murni (tanpa dependensi framework) sehingga mudah diuji.
 *
 * Setiap akumulasi $gramPerUnit gram menghasilkan $pointsPerUnit poin.
 * Sisa berat di bawah $gramPerUnit tidak hilang: ia dibawa ke setoran berikutnya
 * dan digabung sebelum poin tambahan dihitung.
 */
final class PointCalculator
{
    public function __construct(
        private readonly int $gramPerUnit = 1000,
        private readonly int $pointsPerUnit = 5,
    ) {
        if ($gramPerUnit < 1) {
            throw new InvalidArgumentException('gramPerUnit harus >= 1.');
        }
        if ($pointsPerUnit < 0) {
            throw new InvalidArgumentException('pointsPerUnit tidak boleh negatif.');
        }
    }

    /**
     * @param int $sisaGram  sisa berat yang dibawa dari setoran sebelumnya
     * @param int $beratGram berat setoran baru (gram)
     * @return array{poin:int, sisa_gram:int}  poin yang didapat dari setoran ini dan sisa berat baru
     */
    public function apply(int $sisaGram, int $beratGram): array
    {
        if ($sisaGram < 0 || $beratGram < 0) {
            throw new InvalidArgumentException('Berat tidak boleh negatif.');
        }

        $gabungan = $sisaGram + $beratGram;

        return [
            'poin' => intdiv($gabungan, $this->gramPerUnit) * $this->pointsPerUnit,
            'sisa_gram' => $gabungan % $this->gramPerUnit,
        ];
    }
}
