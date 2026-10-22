<?php

namespace Database\Seeders;

use App\Models\JenisSampah;
use App\Models\Kelas;
use App\Models\PengaturanPoin;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Aturan poin bawaan: setiap akumulasi 1.000 gram = 5 poin.
        if (! PengaturanPoin::query()->exists()) {
            PengaturanPoin::create(['gram_per_unit' => 1000, 'poin_per_unit' => 5]);
        }

        // Tiga kategori sampah.
        foreach (
            [
                ['Sampah Organik', 'organik', 'Sisa makanan, daun, dan bahan yang mudah terurai'],
                ['Sampah An-organik', 'anorganik', 'Plastik, kertas, logam, kaca'],
                ['Sampah B3', 'b3', 'Bahan berbahaya dan beracun: baterai, lampu, elektronik'],
            ] as [$nama, $kategori, $keterangan]
        ) {
            JenisSampah::firstOrCreate(['nama' => $nama], ['kategori' => $kategori, 'keterangan' => $keterangan, 'aktif' => true]);
        }

        // Kelompok penampung setoran guru dan tenaga administrasi (tidak ikut peringkat kelas).
        Kelas::firstOrCreate(['nama' => 'Guru'], ['tipe' => 'guru']);
        Kelas::firstOrCreate(['nama' => 'Tenaga Administrasi'], ['tipe' => 'tendik']);

        // Akun admin awal. UBAH kata sandi ini setelah login pertama.
        User::firstOrCreate(['username' => 'admin'], [
            'name' => 'Administrator',
            'email' => 'admin@jatizi.local',
            'password' => env('SEED_ADMIN_PASSWORD', 'Jatizi@2026'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Data contoh hanya untuk lingkungan lokal.
        if (app()->environment('local')) {
            $this->dataContoh();
        }
    }

    private function dataContoh(): void
    {
        $kelas = collect(['X PPLG 1', 'X PPLG 2', 'XI PPLG 1', 'XII PPLG 1'])
            ->map(fn($nama) => Kelas::firstOrCreate(['nama' => $nama], ['tipe' => 'kelas']));

        User::firstOrCreate(['username' => 'petugas'], [
            'name' => 'Petugas Bank Sampah',
            'email' => 'petugas@jatizi.local',
            'password' => 'Petugas@2026',
            'role' => 'petugas',
            'is_active' => true,
        ]);

        User::firstOrCreate(['username' => 'siswa1'], [
            'name' => 'Ahmad Fauzi',
            'email' => '1siswa@jatizi.local',
            'password' => 'Siswa@2026',
            'role' => 'siswa',
            'nomor_induk' => '2025001',
            'kelas_id' => $kelas->first()->id,
            'is_active' => true,
        ]);

        User::firstOrCreate(['username' => 'guru1'], [
            'name' => 'Bu Siti Aminah',
            'email' => 'guru1@jatizi.local',
            'password' => 'Guru@2026',
            'role' => 'guru',
            'nomor_induk' => '198001012005012001',
            'kelas_id' => Kelas::where('tipe', 'guru')->value('id'),
            'is_active' => true,
        ]);
    }
}
