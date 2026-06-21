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
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolah')->cascadeOnDelete();
            $table->string('nama', 20);
            $table->boolean('is_ganjil')->default(true)->comment('true = semester ganjil, false = semester genap');
            $table->timestamps();

            // Satu sekolah tidak boleh punya kelas dengan nama + semester yang sama
            $table->unique(['sekolah_id', 'nama', 'is_ganjil']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};
