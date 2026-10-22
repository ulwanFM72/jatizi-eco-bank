<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Setoran;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PenggunaController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', Rule::in(User::ROLES)],
        ]);

        $pengguna = User::query()
            ->with('kelas:id,nama')
            ->when($filters['q'] ?? null, function ($q, $cari) {
                $like = addcslashes($cari, '%_\\');
                $q->where(fn ($w) => $w->where('name', 'like', "%{$like}%")
                    ->orWhere('username', 'like', "%{$like}%")
                    ->orWhere('nomor_induk', 'like', "{$like}%"));
            })
            ->when($filters['role'] ?? null, fn ($q, $role) => $q->where('role', $role))
            ->orderBy('role')->orderBy('name')
            ->paginate(15)->withQueryString()
            ->through(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'username' => $u->username,
                'email' => $u->email,
                'role' => $u->role,
                'nomor_induk' => $u->nomor_induk,
                'kelas_id' => $u->kelas_id,
                'kelas' => $u->kelas?->nama,
                'is_active' => $u->is_active,
                'total_poin' => $u->total_poin,
            ]);

        return Inertia::render('Admin/Pengguna/Index', [
            'pengguna' => $pengguna,
            'filters' => ['q' => $filters['q'] ?? '', 'role' => $filters['role'] ?? ''],
            'kelasOptions' => Kelas::orderBy('tipe')->orderBy('nama')->get(['id', 'nama', 'tipe']),
            'roles' => User::ROLES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'alpha_dash', 'max:50', 'unique:users,username'],
            'email' => ['nullable', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:100'],
            'role' => ['required', Rule::in(User::ROLES)],
            'nomor_induk' => ['nullable', 'string', 'max:30'],
            'kelas_id' => ['nullable', 'integer', 'exists:kelas,id', 'required_if:role,siswa'],
            'is_active' => ['boolean'],
        ]);

        // Email tidak wajib untuk siswa; kolom email bawaan Laravel butuh nilai unik.
        $data['email'] = $data['email'] ?? $data['username'].'@jatizi.local';

        User::create($data);

        return back()->with('success', 'Pengguna ditambahkan.');
    }

    public function update(Request $request, User $pengguna)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'alpha_dash', 'max:50', Rule::unique('users', 'username')->ignore($pengguna->id)],
            'email' => ['nullable', 'email', 'max:150', Rule::unique('users', 'email')->ignore($pengguna->id)],
            'password' => ['nullable', 'string', 'min:8', 'max:100'],
            'role' => ['required', Rule::in(User::ROLES)],
            'nomor_induk' => ['nullable', 'string', 'max:30'],
            'kelas_id' => ['nullable', 'integer', 'exists:kelas,id', 'required_if:role,siswa'],
            'is_active' => ['boolean'],
        ]);

        $aktif = $data['is_active'] ?? true;
        $kehilanganAdmin = $pengguna->role === 'admin' && ($data['role'] !== 'admin' || ! $aktif);

        if ($kehilanganAdmin && $request->user()->is($pengguna)) {
            return back()->with('error', 'Anda tidak dapat mengubah peran atau menonaktifkan akun Anda sendiri.');
        }
        if ($kehilanganAdmin && ! $this->adaAdminLain($pengguna)) {
            return back()->with('error', 'Harus ada minimal satu admin aktif.');
        }

        if (blank($data['password'] ?? null)) {
            unset($data['password']); // kosong = kata sandi tidak diubah
        }
        $data['email'] = $data['email'] ?? $data['username'].'@jatizi.local';

        $pengguna->update($data);

        return back()->with('success', 'Pengguna diperbarui.');
    }

    public function destroy(Request $request, User $pengguna)
    {
        if ($request->user()->is($pengguna)) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }
        if ($pengguna->role === 'admin' && ! $this->adaAdminLain($pengguna)) {
            return back()->with('error', 'Harus ada minimal satu admin aktif.');
        }

        $punyaSetoran = Setoran::withTrashed()
            ->where(fn ($q) => $q->where('petugas_id', $pengguna->id)->orWhere('penyetor_id', $pengguna->id))
            ->exists();

        if ($punyaSetoran) {
            return back()->with('error', 'Pengguna sudah terkait dengan setoran, jadi tidak dapat dihapus. Nonaktifkan akunnya saja.');
        }

        $pengguna->delete();

        return back()->with('success', 'Pengguna dihapus.');
    }

    private function adaAdminLain(User $pengguna): bool
    {
        return User::where('role', 'admin')->where('is_active', true)->where('id', '!=', $pengguna->id)->exists();
    }
}
