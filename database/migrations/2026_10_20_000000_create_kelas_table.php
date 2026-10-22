<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 50)->unique();
            // kelas = ikut peringkat kelas; guru / tendik = kelompok penampung setoran guru & tenaga administrasi
            $table->string('tipe', 10)->default('kelas');
            // Buku besar (ledger) kelas: selalu dihitung ulang oleh SetoranService, jangan diubah manual.
            $table->unsignedBigInteger('total_gram')->default(0);
            $table->unsignedInteger('sisa_gram')->default(0);
            $table->unsignedBigInteger('total_poin')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};
