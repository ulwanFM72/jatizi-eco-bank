<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Pencarian akun penyetor (siswa / guru / tendik) untuk form setoran. */
class PenyetorController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $like = addcslashes($q, '%_\\');

        $hasil = User::query()
            ->with('kelas:id,nama')
            ->whereIn('role', User::PENYETOR_ROLES)
            ->where('is_active', true)
            ->where(function ($w) use ($like) {
                $w->where('name', 'like', "%{$like}%")->orWhere('nomor_induk', 'like', "{$like}%");
            })
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'role' => $u->role,
                'nomor_induk' => $u->nomor_induk,
                'kelas_id' => $u->kelas_id,
                'kelas' => $u->kelas?->nama,
            ]);

        return response()->json($hasil);
    }
}
