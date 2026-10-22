<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Validasi setoran (dipakai untuk catat dan koreksi). Konversi kg ke gram juga dilakukan di sini, di server. */
class SetoranRequest extends FormRequest
{
    public const MAKS_GRAM = 500000; // 500 kg per transaksi

    public function authorize(): bool
    {
        return (bool) $this->user()?->isStaff();
    }

    public function rules(): array
    {
        return [
            'penyetor_id' => ['nullable', 'integer', 'exists:users,id'],
            'nama_penyetor' => ['required_without:penyetor_id', 'nullable', 'string', 'max:100'],
            'kelas_id' => ['nullable', 'integer', 'exists:kelas,id'],
            'jenis_sampah_id' => ['required', 'integer', Rule::exists('jenis_sampah', 'id')->where('aktif', true)],
            'berat' => ['required', 'numeric', 'min:0.001'],
            'satuan' => ['required', Rule::in(['gram', 'kg'])],
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'catatan' => ['nullable', 'string', 'max:255'],
            'idempotency_key' => ['nullable', 'uuid'],
        ];
    }

    public function attributes(): array
    {
        return [
            'penyetor_id' => 'Akun penyetor',
            'nama_penyetor' => 'Nama penyetor',
            'kelas_id' => 'Kelas',
            'jenis_sampah_id' => 'Jenis sampah',
            'berat' => 'Berat',
            'satuan' => 'Satuan',
            'tanggal' => 'Tanggal',
            'catatan' => 'Catatan',
        ];
    }

    /** @return array<int, callable> */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->has('berat') || $validator->errors()->has('satuan')) {
                    return;
                }

                $berat = trim((string) $this->input('berat'));

                if ($this->input('satuan') === 'gram') {
                    if (! preg_match('/^\d+$/', $berat)) {
                        $validator->errors()->add('berat', 'Berat dalam gram harus bilangan bulat.');
                        return;
                    }
                } elseif (! preg_match('/^\d+(\.\d{1,3})?$/', $berat)) {
                    $validator->errors()->add('berat', 'Berat dalam kg maksimal 3 angka di belakang titik desimal.');
                    return;
                }

                $gram = $this->beratGram();
                if ($gram < 1) {
                    $validator->errors()->add('berat', 'Berat minimal 1 gram.');
                } elseif ($gram > self::MAKS_GRAM) {
                    $validator->errors()->add('berat', 'Berat per setoran maksimal 500 kg.');
                }
            },
        ];
    }

    /** Berat dalam gram (bilangan bulat) dihitung di server dari input kg atau gram. */
    public function beratGram(): int
    {
        $berat = trim((string) $this->input('berat'));

        return $this->input('satuan') === 'kg'
            ? (int) round(((float) $berat) * 1000)
            : (int) $berat;
    }

    /** Data bersih untuk SetoranService. */
    public function dataSetoran(): array
    {
        $v = $this->validated();

        return [
            'penyetor_id' => $v['penyetor_id'] ?? null,
            'nama_penyetor' => $v['nama_penyetor'] ?? null,
            'kelas_id' => $v['kelas_id'] ?? null,
            'jenis_sampah_id' => (int) $v['jenis_sampah_id'],
            'berat_gram' => $this->beratGram(),
            'tanggal' => $v['tanggal'],
            'catatan' => $v['catatan'] ?? null,
            'idempotency_key' => $v['idempotency_key'] ?? null,
        ];
    }
}
