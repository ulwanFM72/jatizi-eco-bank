<?php

namespace App\Http\Controllers;

use App\Models\Setoran;
use App\Models\User;
use App\Services\StatistikService;
use App\Support\SetoranPresenter;
use Illuminate\Http\Request;
use Inertia\Inertia;

/** Halaman pribadi siswa / guru / tenaga administrasi: riwayat, akumulasi, nilai setoran, peringkat kepedulian. */
class AkunController extends Controller
{
    public function show(Request $request, StatistikService $statistik)
    {
        $user = $request->user()->load('kelas:id,nama,tipe');

        if ($user->isStaff()) {
            return redirect()->route('admin.dashboard');
        }

        $aktif = User::query()
            ->whereIn('role', User::PENYETOR_ROLES)
            ->where('total_gram', '>', 0);

        $peringkat = null;
        if ($user->total_gram > 0) {
            $peringkat = (clone $aktif)->where(function ($q) use ($user) {
                $q->where('total_poin', '>', $user->total_poin)
                    ->orWhere(fn ($q) => $q->where('total_poin', $user->total_poin)->where('total_gram', '>', $user->total_gram));
            })->count() + 1;
        }

        $peringkatKelas = null;
        if ($user->kelas && $user->kelas->tipe === 'kelas') {
            $peringkatKelas = collect($statistik->peringkatKelas())->firstWhere('id', $user->kelas_id)['rank'] ?? null;
        }

        $terbaru = Setoran::query()
            ->with(['kelas:id,nama', 'jenis:id,nama,kategori', 'petugas:id,name'])
            ->where('penyetor_id', $user->id)
            ->orderByDesc('tanggal')->orderByDesc('id')
            ->limit(5)->get()
            ->map(fn (Setoran $s) => SetoranPresenter::baris($s, 'pribadi'));

        return Inertia::render('Akun', [
            'profil' => [
                'name' => $user->name,
                'username' => $user->username,
                'role' => $user->role,
                'nomor_induk' => $user->nomor_induk,
                'kelas' => $user->kelas?->nama,
            ],
            'ringkas' => [
                'total_gram' => $user->total_gram,
                'sisa_gram' => $user->sisa_gram,
                'total_poin' => $user->total_poin,
                'peringkat' => $peringkat,
                'dari_penyetor' => $aktif->count(),
                'peringkat_kelas' => $peringkatKelas,
            ],
            'terbaru' => $terbaru,
        ]);
    }
}
