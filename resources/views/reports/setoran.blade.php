<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Setoran JATIZI ECO-BANK</title>
    <style>
        @page { margin: 28px 32px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; }
        h1 { font-size: 16px; margin: 0; color: #14532d; }
        h2 { font-size: 12px; margin: 18px 0 6px; color: #166534; }
        .sub { color: #4b5563; margin: 2px 0 0; }
        .head { border-bottom: 3px solid #86efac; padding-bottom: 8px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #dcfce7; color: #14532d; text-align: left; padding: 5px 6px; border: 1px solid #bbf7d0; }
        td { padding: 4px 6px; border: 1px solid #e5e7eb; }
        .r { text-align: right; }
        .total td { background: #f0fdf4; font-weight: bold; }
        .kartu td { border: 1px solid #bbf7d0; background: #f0fdf4; text-align: center; padding: 8px; }
        .kartu .n { font-size: 15px; font-weight: bold; color: #15803d; }
        .catatan { margin-top: 14px; color: #6b7280; font-size: 9px; }
    </style>
</head>
<body>
@php
    $kg = fn ($g) => number_format($g / 1000, 2, ',', '.').' kg';
    $tgl = fn ($d) => \Carbon\Carbon::parse($d)->format('d/m/Y');
    $namaKategori = ['organik' => 'Organik', 'anorganik' => 'An-organik', 'b3' => 'B3'];
@endphp

<div class="head">
    <h1>Laporan Setoran JATIZI ECO-BANK</h1>
    <p class="sub">Bank Sampah SMK Negeri 1 Cijati</p>
    <p class="sub">Periode: {{ $tgl($filters['dari']) }} s.d. {{ $tgl($filters['sampai']) }} &middot; Kelas: {{ $namaKelas }}</p>
</div>

<table class="kartu">
    <tr>
        <td><div class="n">{{ number_format($ringkasan['jumlah_transaksi'], 0, ',', '.') }}</div>Jumlah transaksi</td>
        <td><div class="n">{{ $kg($ringkasan['total_gram']) }}</div>Total sampah</td>
        <td><div class="n">{{ number_format($ringkasan['total_poin'], 0, ',', '.') }}</div>Total poin</td>
    </tr>
</table>

<h2>Ringkasan kontribusi per kelas</h2>
<table>
    <thead>
    <tr><th>#</th><th>Kelas</th><th class="r">Transaksi</th><th class="r">Berat</th><th class="r">Poin</th><th class="r">Sisa berat (g)</th></tr>
    </thead>
    <tbody>
    @forelse ($perKelas as $i => $k)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $k['nama'] }}</td>
            <td class="r">{{ $k['jumlah'] }}</td>
            <td class="r">{{ $kg($k['gram']) }}</td>
            <td class="r">{{ $k['poin'] }}</td>
            <td class="r">{{ number_format($k['sisa_gram'], 0, ',', '.') }}</td>
        </tr>
    @empty
        <tr><td colspan="6">Tidak ada setoran pada periode ini.</td></tr>
    @endforelse
    </tbody>
</table>

<h2>Rekap per kategori sampah</h2>
<table>
    <thead><tr><th>Kategori</th><th class="r">Transaksi</th><th class="r">Berat</th><th class="r">Poin</th></tr></thead>
    <tbody>
    @foreach ($perKategori as $k)
        <tr>
            <td>{{ $namaKategori[$k['kategori']] }}</td>
            <td class="r">{{ $k['jumlah'] }}</td>
            <td class="r">{{ $kg($k['gram']) }}</td>
            <td class="r">{{ $k['poin'] }}</td>
        </tr>
    @endforeach
    <tr class="total">
        <td>Total</td>
        <td class="r">{{ $ringkasan['jumlah_transaksi'] }}</td>
        <td class="r">{{ $kg($ringkasan['total_gram']) }}</td>
        <td class="r">{{ $ringkasan['total_poin'] }}</td>
    </tr>
    </tbody>
</table>

<h2>Rincian transaksi</h2>
<table>
    <thead>
    <tr><th>Tanggal</th><th>Penyetor</th><th>Kelas</th><th>Jenis sampah</th><th class="r">Berat (g)</th><th class="r">Poin</th><th>Petugas</th><th>Status</th></tr>
    </thead>
    <tbody>
    @forelse ($detail as $s)
        <tr>
            <td>{{ $tgl($s->tanggal) }}</td>
            <td>{{ $s->nama_penyetor }}</td>
            <td>{{ $s->kelas?->nama }}</td>
            <td>{{ $s->jenis?->nama }}</td>
            <td class="r">{{ number_format($s->berat_gram, 0, ',', '.') }}</td>
            <td class="r">{{ $s->poin }}</td>
            <td>{{ $s->petugas?->name }}</td>
            <td>{{ $s->status === 'dikoreksi' ? 'Dikoreksi' : 'Valid' }}</td>
        </tr>
    @empty
        <tr><td colspan="8">Tidak ada data.</td></tr>
    @endforelse
    </tbody>
</table>

@if ($dipotong)
    <p class="catatan">Rincian dibatasi {{ number_format($maksBaris, 0, ',', '.') }} transaksi pertama. Persempit rentang tanggal atau pilih satu kelas untuk melihat sisanya. Ringkasan di atas tetap mencakup seluruh data.</p>
@endif

<p class="catatan">Dicetak oleh {{ $dicetakOleh }} pada {{ now()->format('d/m/Y H:i') }} WIB. Poin dihitung di server: setiap akumulasi {{ number_format(\App\Models\PengaturanPoin::current()->gram_per_unit, 0, ',', '.') }} gram = {{ \App\Models\PengaturanPoin::current()->poin_per_unit }} poin (aturan yang berlaku saat ini), sisa berat dibawa ke setoran berikutnya.</p>
</body>
</html>
