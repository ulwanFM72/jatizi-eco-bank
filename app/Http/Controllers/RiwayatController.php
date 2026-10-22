<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Setoran;
use App\Support\SetoranPresenter;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Riwayat setoran sesuai hak akses:
 *  - tamu            : data ringkas, nama penyetor disamarkan
 *  - siswa/guru/tendik: hanya setoran miliknya sendiri
 *  - admin/petugas   : semua setoran, data lengkap
 */
class RiwayatController extends Controller
{
    public function __invoke(Request $request)
    {
        $filters = $request->validate([
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date'],
            'kelas_id' => ['nullable', 'integer'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $user = $request->user();
        $mode = ! $user ? 'publik' : ($user->isStaff() ? 'staff' : 'pribadi');

        $query = Setoran::query()
            ->with(['kelas:id,nama', 'jenis:id,nama,kategori', 'petugas:id,name'])
            ->filter($filters, cariNama: $mode === 'staff')
            ->orderByDesc('setoran.tanggal')
            ->orderByDesc('setoran.id');

        if ($mode === 'pribadi') {
            $query->where('setoran.penyetor_id', $user->id);
        }

        $setoran = $query->paginate(15)->withQueryString()
            ->through(fn (Setoran $s) => SetoranPresenter::baris($s, $mode));

        return Inertia::render('Riwayat', [
            'setoran' => $setoran,
            'filters' => [
                'dari' => $filters['dari'] ?? '',
                'sampai' => $filters['sampai'] ?? '',
                'kelas_id' => $filters['kelas_id'] ?? '',
                'q' => $filters['q'] ?? '',
            ],
            'kelasOptions' => $mode === 'pribadi' ? [] : Kelas::orderBy('nama')->get(['id', 'nama']),
            'mode' => $mode,
        ]);
    }
}
