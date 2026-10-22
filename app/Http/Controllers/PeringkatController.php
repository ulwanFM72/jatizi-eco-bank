<?php

namespace App\Http\Controllers;

use App\Services\StatistikService;
use Inertia\Inertia;

class PeringkatController extends Controller
{
    public function __invoke(StatistikService $statistik)
    {
        return Inertia::render('Peringkat', [
            'peringkat' => $statistik->peringkatKelas(),
        ]);
    }
}
