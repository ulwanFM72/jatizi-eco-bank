<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class KelasController extends Controller
{
    public function index()
    {
        $kelas = Kelas::query()
            ->withCount(['setoran', 'users'])
            ->orderBy('tipe')->orderBy('nama')
            ->get()
            ->map(fn (Kelas $k) => [
                'id' => $k->id,
                'nama' => $k->nama,
                'tipe' => $k->tipe,
                'total_gram' => $k->total_gram,
                'total_poin' => $k->total_poin,
                'setoran_count' => $k->setoran_count,
                'users_count' => $k->users_count,
            ]);

        return Inertia::render('Admin/Kelas/Index', ['kelas' => $kelas]);
    }

    public function store(Request $request)
    {
        Kelas::create($request->validate([
            'nama' => ['required', 'string', 'max:50', 'unique:kelas,nama'],
            'tipe' => ['required', Rule::in(Kelas::TIPE)],
        ]));

        return back()->with('success', 'Kelas ditambahkan.');
    }

    public function update(Request $request, Kelas $kelas)
    {
        $kelas->update($request->validate([
            'nama' => ['required', 'string', 'max:50', Rule::unique('kelas', 'nama')->ignore($kelas->id)],
            'tipe' => ['required', Rule::in(Kelas::TIPE)],
        ]));

        return back()->with('success', 'Kelas diperbarui.');
    }

    public function destroy(Kelas $kelas)
    {
        if ($kelas->setoran()->withTrashed()->exists() || $kelas->users()->exists()) {
            return back()->with('error', 'Kelas tidak dapat dihapus karena sudah memiliki setoran atau anggota.');
        }

        $kelas->delete();

        return back()->with('success', 'Kelas dihapus.');
    }
}
