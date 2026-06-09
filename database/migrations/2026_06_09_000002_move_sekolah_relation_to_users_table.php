<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('sekolah_id')
                ->nullable()
                ->after('id')
                ->constrained('sekolah')
                ->nullOnDelete();
        });

        DB::table('sekolah')
            ->whereNotNull('user_id')
            ->select('id', 'user_id')
            ->orderBy('id')
            ->each(function (object $sekolah): void {
                DB::table('users')
                    ->where('id', $sekolah->user_id)
                    ->update(['sekolah_id' => $sekolah->id]);
            });

        Schema::table('sekolah', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id']);
            $table->dropColumn('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sekolah', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->unique()
                ->constrained()
                ->nullOnDelete();
        });

        DB::table('users')
            ->whereNotNull('sekolah_id')
            ->select('id', 'sekolah_id')
            ->orderBy('id')
            ->each(function (object $user): void {
                DB::table('sekolah')
                    ->where('id', $user->sekolah_id)
                    ->whereNull('user_id')
                    ->update(['user_id' => $user->id]);
            });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['sekolah_id']);
            $table->dropColumn('sekolah_id');
        });
    }
};
