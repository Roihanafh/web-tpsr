<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('penilaian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->enum('pertemuan', array_map('strval', range(1, 16)));
            $table->enum('L0', ['1', '2', '3', '4'])->nullable();
            $table->enum('L1', ['1', '2', '3', '4'])->nullable();
            $table->enum('L2', ['1', '2', '3', '4'])->nullable();
            $table->enum('L3', ['1', '2', '3', '4'])->nullable();
            $table->enum('L4', ['1', '2', '3', '4'])->nullable();
            $table->timestamps();

            // Satu siswa hanya boleh punya satu record per pertemuan
            $table->unique(['siswa_id', 'pertemuan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penilaian');
    }
};
