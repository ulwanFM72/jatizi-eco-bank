<?php

namespace Tests\Unit;

use App\Support\PointCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PointCalculatorTest extends TestCase
{
    public function test_seribu_gram_menjadi_lima_poin(): void
    {
        $hasil = (new PointCalculator())->apply(0, 1000);

        $this->assertSame(5, $hasil['poin']);
        $this->assertSame(0, $hasil['sisa_gram']);
    }

    public function test_di_bawah_seribu_gram_tidak_dapat_poin_tetapi_sisa_disimpan(): void
    {
        $hasil = (new PointCalculator())->apply(0, 800);

        $this->assertSame(0, $hasil['poin']);
        $this->assertSame(800, $hasil['sisa_gram']);
    }

    public function test_sisa_digabung_dengan_setoran_berikutnya(): void
    {
        $hitung = new PointCalculator();

        $a = $hitung->apply(0, 800);
        $b = $hitung->apply($a['sisa_gram'], 400);

        $this->assertSame(5, $b['poin']);
        $this->assertSame(200, $b['sisa_gram']);
    }

    public function test_setoran_besar_memberi_kelipatan_poin(): void
    {
        $hasil = (new PointCalculator())->apply(300, 3900); // 4200 gram

        $this->assertSame(20, $hasil['poin']);
        $this->assertSame(200, $hasil['sisa_gram']);
    }

    public function test_aturan_dapat_diubah(): void
    {
        $hasil = (new PointCalculator(500, 3))->apply(0, 1200);

        $this->assertSame(6, $hasil['poin']);
        $this->assertSame(200, $hasil['sisa_gram']);
    }

    public function test_total_poin_tidak_bergantung_cara_setoran_dipecah(): void
    {
        $hitung = new PointCalculator();

        $sisa = 0;
        $total = 0;
        foreach ([250, 250, 250, 250, 600, 400] as $berat) {
            $r = $hitung->apply($sisa, $berat);
            $sisa = $r['sisa_gram'];
            $total += $r['poin'];
        }

        $sekaligus = $hitung->apply(0, 2000);

        $this->assertSame($sekaligus['poin'], $total);
        $this->assertSame($sekaligus['sisa_gram'], $sisa);
    }

    public function test_berat_negatif_ditolak(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new PointCalculator())->apply(0, -1);
    }

    public function test_aturan_tidak_valid_ditolak(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PointCalculator(0, 5);
    }
}
