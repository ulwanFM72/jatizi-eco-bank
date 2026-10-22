<?php

namespace App\Http\Middleware;

use App\Models\PengaturanPoin;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => fn () => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'role' => $request->user()->role,
                    'is_staff' => $request->user()->isStaff(),
                    'is_admin' => $request->user()->isAdmin(),
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'aturan' => fn () => PengaturanPoin::current()->only(['gram_per_unit', 'poin_per_unit']),
        ]);
    }
}
