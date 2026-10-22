<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengaturanPoin;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PengaturanPoinController extends Controller
{
    public function edit()
    {
        return Inertia::render('Admin/PengaturanPoin', [
            'aturan' => PengaturanPoin::current()->only(['gram_per_unit', 'poin_per_unit']),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'gram_per_unit' => ['required', 'integer', 'min:1', 'max:100000'],
            'poin_per_unit' => ['required', 'integer', 'min:0', 'max:1000'],
        ]);

        PengaturanPoin::current()->update($data);

        return back()->with('success', 'Aturan poin disimpan. Berlaku untuk setoran yang dicatat mulai sekarang; setoran lama tidak berubah.');
    }
}
