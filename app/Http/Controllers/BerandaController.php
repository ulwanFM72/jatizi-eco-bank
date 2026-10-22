<?php

namespace App\Http\Controllers;

use App\Services\StatistikService;
use Inertia\Inertia;

class BerandaController extends Controller
{
    public function __invoke(StatistikService $statistik)
    {
        return Inertia::render('Beranda', [
            'ringkasan' => $statistik->ringkasan(),
            'kategori' => $statistik->perKategori(),
            'teratas' => array_slice($statistik->peringkatKelas(), 0, 3),
        ]);
    }
}
