<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setoran;
use App\Services\StatistikService;
use App\Support\SetoranPresenter;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke(StatistikService $statistik)
    {
        $terbaru = Setoran::query()
            ->with(['kelas:id,nama', 'jenis:id,nama,kategori', 'petugas:id,name'])
            ->orderByDesc('tanggal')->orderByDesc('id')
            ->limit(8)->get()
            ->map(fn (Setoran $s) => SetoranPresenter::baris($s, 'staff'));

        return Inertia::render('Admin/Dashboard', [
            'ringkasan' => $statistik->ringkasan(),
            'hariIni' => Setoran::whereDate('tanggal', today())->count(),
            'kategori' => $statistik->perKategori(),
            'peringkat' => array_slice($statistik->peringkatKelas(), 0, 5),
            'terbaru' => $terbaru,
        ]);
    }
}
