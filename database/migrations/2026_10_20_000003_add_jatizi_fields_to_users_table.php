<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 50)->nullable()->unique();
            $table->string('role', 20)->default('siswa'); // admin | petugas | siswa | guru | tendik
            $table->string('nomor_induk', 30)->nullable(); // NIS / NIP / NUPTK
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->nullOnDelete();
            // Buku besar (ledger) pribadi penyetor: dihitung ulang oleh SetoranService.
            $table->unsignedBigInteger('total_gram')->default(0);
            $table->unsignedInteger('sisa_gram')->default(0);
            $table->unsignedBigInteger('total_poin')->default(0);
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kelas_id');
            $table->dropUnique(['username']);
            $table->dropColumn(['username', 'role', 'nomor_induk', 'total_gram', 'sisa_gram', 'total_poin', 'is_active']);
        });
    }
};
