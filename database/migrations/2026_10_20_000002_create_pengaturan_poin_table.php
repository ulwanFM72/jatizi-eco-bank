<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan_poin', function (Blueprint $table) {
            $table->id();
            // Setiap akumulasi `gram_per_unit` gram menghasilkan `poin_per_unit` poin (default 1000 gram = 5 poin)
            $table->unsignedInteger('gram_per_unit')->default(1000);
            $table->unsignedInteger('poin_per_unit')->default(5);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan_poin');
    }
};
