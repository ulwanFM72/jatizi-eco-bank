<?php

use App\Http\Controllers\AkunController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\JenisSampahController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\PengaturanPoinController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Admin\PenyetorController;
use App\Http\Controllers\Admin\SetoranController;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\PeringkatController;
use App\Http\Controllers\RiwayatController;
use Illuminate\Support\Facades\Route;

// ---- Halaman publik ----
Route::get('/', BerandaController::class)->name('beranda');
Route::get('/peringkat', PeringkatController::class)->name('peringkat');
Route::get('/riwayat', RiwayatController::class)->name('riwayat'); // isi menyesuaikan hak akses pengguna

// ---- Autentikasi ----
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [LoginController::class, 'create'])->name('login');
    Route::post('/masuk', [LoginController::class, 'store'])->middleware('throttle:6,1');
});
Route::post('/keluar', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

// ---- Halaman pribadi siswa / guru / tenaga administrasi ----
Route::get('/akun', [AkunController::class, 'show'])->middleware('auth')->name('akun');

// ---- Halaman admin & petugas ----
Route::prefix('admin')->middleware(['auth', 'role:admin,petugas'])->name('admin.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    // Admin dan petugas: lihat riwayat + input setoran baru
    Route::get('setoran', [SetoranController::class, 'index'])->name('setoran.index');
    Route::post('setoran', [SetoranController::class, 'store'])->name('setoran.store');
    Route::get('penyetor/cari', PenyetorController::class)->name('penyetor.cari');

    // Khusus admin: koreksi/hapus setoran, data master, pengguna, aturan poin, laporan
    Route::middleware('role:admin')->group(function () {
        Route::put('setoran/{setoran}', [SetoranController::class, 'update'])->name('setoran.update');
        Route::delete('setoran/{setoran}', [SetoranController::class, 'destroy'])->name('setoran.destroy');

        Route::resource('kelas', KelasController::class)->only(['index', 'store', 'update', 'destroy'])->parameters(['kelas' => 'kelas']);
        Route::resource('jenis-sampah', JenisSampahController::class)->only(['index', 'store', 'update', 'destroy'])->parameters(['jenis-sampah' => 'jenisSampah']);
        Route::resource('pengguna', PenggunaController::class)->only(['index', 'store', 'update', 'destroy'])->parameters(['pengguna' => 'pengguna']);

        Route::get('pengaturan-poin', [PengaturanPoinController::class, 'edit'])->name('pengaturan-poin.edit');
        Route::put('pengaturan-poin', [PengaturanPoinController::class, 'update'])->name('pengaturan-poin.update');

        Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('laporan/pdf', [LaporanController::class, 'pdf'])->name('laporan.pdf');
    });
});
