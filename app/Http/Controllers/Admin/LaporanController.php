<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Setoran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LaporanController extends Controller
{
    private const MAKS_BARIS_PDF = 2000;

    public function index(Request $request)
    {
        $f = $this->filter($request);

        return Inertia::render('Admin/Laporan', [
            'filters' => $f,
            'kelasOptions' => Kelas::orderBy('tipe')->orderBy('nama')->get(['id', 'nama']),
        ] + $this->data($f));
    }

    public function pdf(Request $request)
    {
        $f = $this->filter($request);
        $data = $this->data($f);

        $detail = Setoran::query()
            ->with(['kelas:id,nama', 'jenis:id,nama,kategori', 'petugas:id,name'])
            ->filter($f, cariNama: false)
            ->orderBy('setoran.tanggal')->orderBy('setoran.id')
            ->limit(self::MAKS_BARIS_PDF)
            ->get();

        $pdf = Pdf::loadView('reports.setoran', $data + [
            'filters' => $f,
            'namaKelas' => $f['kelas_id'] ? Kelas::whereKey($f['kelas_id'])->value('nama') : 'Semua kelas',
            'detail' => $detail,
            'dipotong' => $data['ringkasan']['jumlah_transaksi'] > self::MAKS_BARIS_PDF,
            'dicetakOleh' => $request->user()->name,
            'maksBaris' => self::MAKS_BARIS_PDF,
        ])->setPaper('a4', 'portrait');

        $nama = "laporan-setoran-{$f['dari']}_sd_{$f['sampai']}.pdf";

        // ?unduh=1 mengunduh file; tanpa itu PDF dibuka di tab baru (siap dicetak).
        return $request->boolean('unduh') ? $pdf->download($nama) : $pdf->stream($nama);
    }

    /** @return array{dari:string,sampai:string,kelas_id:?int} */
    private function filter(Request $request): array
    {
        $v = $request->validate([
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
            'kelas_id' => ['nullable', 'integer', 'exists:kelas,id'],
        ]);

        return [
            'dari' => $v['dari'] ?? now()->startOfMonth()->toDateString(),
            'sampai' => $v['sampai'] ?? now()->toDateString(),
            'kelas_id' => isset($v['kelas_id']) ? (int) $v['kelas_id'] : null,
        ];
    }

    private function data(array $f): array
    {
        $r = Setoran::query()->filter($f)
            ->selectRaw('COUNT(*) as jumlah, COALESCE(SUM(setoran.berat_gram), 0) as gram, COALESCE(SUM(setoran.poin), 0) as poin')
            ->first();

        $perKelas = Setoran::query()->filter($f)
            ->join('kelas', 'kelas.id', '=', 'setoran.kelas_id')
            ->groupBy('kelas.id', 'kelas.nama', 'kelas.sisa_gram')
            ->selectRaw('kelas.id as id, kelas.nama as nama, kelas.sisa_gram as sisa_gram, COUNT(*) as jumlah, SUM(setoran.berat_gram) as gram, SUM(setoran.poin) as poin')
            ->orderByDesc('poin')->orderByDesc('gram')->orderBy('kelas.nama')
            ->get()
            ->map(fn ($k) => [
                'id' => $k->id,
                'nama' => $k->nama,
                'jumlah' => (int) $k->jumlah,
                'gram' => (int) $k->gram,
                'poin' => (int) $k->poin,
                'sisa_gram' => (int) $k->sisa_gram,
            ])->all();

        $perKategori = Setoran::query()->filter($f)
            ->join('jenis_sampah', 'jenis_sampah.id', '=', 'setoran.jenis_sampah_id')
            ->groupBy('jenis_sampah.kategori')
            ->selectRaw('jenis_sampah.kategori as kategori, COUNT(*) as jumlah, SUM(setoran.berat_gram) as gram, SUM(setoran.poin) as poin')
            ->get()
            ->keyBy('kategori');

        return [
            'ringkasan' => [
                'jumlah_transaksi' => (int) $r->jumlah,
                'total_gram' => (int) $r->gram,
                'total_poin' => (int) $r->poin,
            ],
            'perKelas' => $perKelas,
            'perKategori' => collect(['organik', 'anorganik', 'b3'])->map(fn ($k) => [
                'kategori' => $k,
                'jumlah' => (int) ($perKategori[$k]->jumlah ?? 0),
                'gram' => (int) ($perKategori[$k]->gram ?? 0),
                'poin' => (int) ($perKategori[$k]->poin ?? 0),
            ])->all(),
        ];
    }
}
