<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setoran', function (Blueprint $table) {
            $table->id();
            // Kunci anti-dobel: form yang dikirim dua kali dengan kunci sama hanya tercatat sekali.
            $table->uuid('idempotency_key')->nullable()->unique();
            $table->foreignId('kelas_id')->constrained('kelas')->restrictOnDelete();
            $table->foreignId('penyetor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama_penyetor', 100);
            $table->foreignId('jenis_sampah_id')->constrained('jenis_sampah')->restrictOnDelete();
            $table->unsignedInteger('berat_gram');
            $table->date('tanggal');
            $table->foreignId('petugas_id')->constrained('users')->restrictOnDelete();

            // Snapshot aturan poin saat transaksi dicatat. Perubahan aturan hanya berlaku untuk setoran baru.
            $table->unsignedInteger('aturan_gram');
            $table->unsignedInteger('aturan_poin');

            // Poin dari transaksi ini menurut buku besar kelas, dan menurut buku besar pribadi penyetor.
            $table->unsignedInteger('poin')->default(0);
            $table->unsignedInteger('poin_pribadi')->nullable();

            $table->string('status', 20)->default('valid'); // valid | dikoreksi
            $table->string('catatan')->nullable();
            $table->timestamp('dikoreksi_pada')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['kelas_id', 'id']);
            $table->index(['penyetor_id', 'id']);
            $table->index('tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setoran');
    }
};
