<?php

namespace Tests\Feature;

use App\Models\JenisSampah;
use App\Models\Kelas;
use App\Models\PengaturanPoin;
use App\Models\Setoran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SetoranTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $petugas;
    private Kelas $kelas;
    private JenisSampah $jenis;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        PengaturanPoin::create(['gram_per_unit' => 1000, 'poin_per_unit' => 5]);
        $this->admin = User::factory()->create(['role' => 'admin', 'username' => 'admin']);
        $this->petugas = User::factory()->create(['role' => 'petugas', 'username' => 'petugas']);
        $this->kelas = Kelas::create(['nama' => 'X PPLG 1', 'tipe' => 'kelas']);
        $this->jenis = JenisSampah::create(['nama' => 'Plastik', 'kategori' => 'anorganik', 'aktif' => true]);
    }

    private function setor(float|string $berat, string $satuan = 'kg', ?string $kunci = null)
    {
        return $this->actingAs($this->petugas)->post('/admin/setoran', [
            'nama_penyetor' => 'Budi Santoso',
            'kelas_id' => $this->kelas->id,
            'jenis_sampah_id' => $this->jenis->id,
            'berat' => $berat,
            'satuan' => $satuan,
            'tanggal' => today()->toDateString(),
            'idempotency_key' => $kunci ?? (string) Str::uuid(),
        ]);
    }

    public function test_sisa_berat_disimpan_dan_digabung_ke_setoran_berikutnya(): void
    {
        $this->setor('0.8')->assertSessionHasNoErrors();
        $this->kelas->refresh();
        $this->assertSame(0, $this->kelas->total_poin);
        $this->assertSame(800, $this->kelas->sisa_gram);

        $this->setor('400', 'gram')->assertSessionHasNoErrors();
        $this->kelas->refresh();
        $this->assertSame(5, $this->kelas->total_poin);
        $this->assertSame(200, $this->kelas->sisa_gram);
        $this->assertSame(1200, $this->kelas->total_gram);
    }

    public function test_kirim_ulang_dengan_kunci_sama_tidak_menghitung_poin_dua_kali(): void
    {
        $kunci = (string) Str::uuid();

        $this->setor('1', 'kg', $kunci);
        $this->setor('1', 'kg', $kunci);

        $this->assertSame(1, Setoran::count());
        $this->assertSame(5, $this->kelas->refresh()->total_poin);
    }

    public function test_koreksi_menghitung_ulang_tanpa_menggandakan_poin(): void
    {
        $this->setor('0.8');
        $this->setor('400', 'gram');
        $pertama = Setoran::orderBy('id')->first();

        $this->actingAs($this->admin)->put("/admin/setoran/{$pertama->id}", [
            'nama_penyetor' => 'Budi Santoso',
            'kelas_id' => $this->kelas->id,
            'jenis_sampah_id' => $this->jenis->id,
            'berat' => '1000',
            'satuan' => 'gram',
            'tanggal' => today()->toDateString(),
        ])->assertSessionHasNoErrors();

        $this->kelas->refresh();
        // 1000 g -> 5 poin, lalu 400 g -> 0 poin dengan sisa 400. Total tetap 5, bukan 10.
        $this->assertSame(5, $this->kelas->total_poin);
        $this->assertSame(400, $this->kelas->sisa_gram);
        $this->assertSame('dikoreksi', $pertama->refresh()->status);
    }

    public function test_hapus_setoran_menghitung_ulang(): void
    {
        $this->setor('1', 'kg');
        $setoran = Setoran::first();

        $this->actingAs($this->admin)->delete("/admin/setoran/{$setoran->id}");

        $this->kelas->refresh();
        $this->assertSame(0, $this->kelas->total_poin);
        $this->assertSame(0, $this->kelas->sisa_gram);
        $this->assertSame(0, Setoran::count());
    }

    public function test_poin_tidak_bisa_dimanipulasi_dari_browser(): void
    {
        $this->actingAs($this->petugas)->post('/admin/setoran', [
            'nama_penyetor' => 'Budi',
            'kelas_id' => $this->kelas->id,
            'jenis_sampah_id' => $this->jenis->id,
            'berat' => '0.5',
            'satuan' => 'kg',
            'tanggal' => today()->toDateString(),
            'poin' => 9999, // diabaikan
            'petugas_id' => $this->admin->id, // diabaikan
        ]);

        $this->assertSame(0, Setoran::first()->poin);
        $this->assertSame($this->petugas->id, Setoran::first()->petugas_id);
    }

    public function test_validasi_berat_kelas_dan_jenis(): void
    {
        $this->actingAs($this->petugas)->post('/admin/setoran', [
            'nama_penyetor' => 'Budi',
            'kelas_id' => 9999,
            'jenis_sampah_id' => 9999,
            'berat' => '-5',
            'satuan' => 'kg',
            'tanggal' => today()->toDateString(),
        ])->assertSessionHasErrors(['kelas_id', 'jenis_sampah_id', 'berat']);

        $this->assertSame(0, Setoran::count());
    }

    public function test_petugas_tidak_boleh_mengoreksi_atau_menghapus(): void
    {
        $this->setor('1');
        $setoran = Setoran::first();

        $this->actingAs($this->petugas)->delete("/admin/setoran/{$setoran->id}")->assertForbidden();
    }

    public function test_siswa_tidak_boleh_masuk_halaman_admin_dan_tamu_diarahkan_ke_login(): void
    {
        $siswa = User::factory()->create(['role' => 'siswa', 'username' => 'siswa1']);

        $this->actingAs($siswa)->get('/admin')->assertForbidden();

        auth()->logout();
        $this->get('/admin')->assertRedirect('/masuk');
    }

    public function test_halaman_publik_dapat_dibuka(): void
    {
        $this->get('/')->assertOk();
        $this->get('/peringkat')->assertOk();
        $this->get('/riwayat')->assertOk();
    }
}
