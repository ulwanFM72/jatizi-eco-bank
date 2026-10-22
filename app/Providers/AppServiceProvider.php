<?php

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Sekolah berada di WIB. Tanpa ini validasi "tanggal tidak boleh melebihi hari ini"
        // memakai UTC dan menolak setoran hari ini pada pukul 00.00-07.00 WIB.
        config(['app.timezone' => 'Asia/Jakarta']);
        date_default_timezone_set('Asia/Jakarta');

        App::setLocale('id');
    }
}
