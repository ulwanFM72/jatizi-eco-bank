<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisSampah;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class JenisSampahController extends Controller
{
    public function index()
    {
        $jenis = JenisSampah::query()
            ->withCount('setoran')
            ->orderBy('kategori')->orderBy('nama')
            ->get(['id', 'nama', 'kategori', 'keterangan', 'aktif']);

        return Inertia::render('Admin/JenisSampah/Index', ['jenis' => $jenis]);
    }

    public function store(Request $request)
    {
        JenisSampah::create($request->validate([
            'nama' => ['required', 'string', 'max:80', 'unique:jenis_sampah,nama'],
            'kategori' => ['required', Rule::in(JenisSampah::KATEGORI)],
            'keterangan' => ['nullable', 'string', 'max:255'],
            'aktif' => ['boolean'],
        ]));

        return back()->with('success', 'Jenis sampah ditambahkan.');
    }

    public function update(Request $request, JenisSampah $jenisSampah)
    {
        $jenisSampah->update($request->validate([
            'nama' => ['required', 'string', 'max:80', Rule::unique('jenis_sampah', 'nama')->ignore($jenisSampah->id)],
            'kategori' => ['required', Rule::in(JenisSampah::KATEGORI)],
            'keterangan' => ['nullable', 'string', 'max:255'],
            'aktif' => ['boolean'],
        ]));

        return back()->with('success', 'Jenis sampah diperbarui.');
    }

    public function destroy(JenisSampah $jenisSampah)
    {
        if ($jenisSampah->setoran()->withTrashed()->exists()) {
            return back()->with('error', 'Jenis sampah sudah dipakai pada setoran. Nonaktifkan saja agar tidak muncul di form.');
        }

        $jenisSampah->delete();

        return back()->with('success', 'Jenis sampah dihapus.');
    }
}
