<?php

use App\Http\Controllers\KelasController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SiswaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/classes', [KelasController::class, 'index'])->name('classes.index');
    Route::get('/students', [SiswaController::class, 'index'])->name('students.index');
    Route::get('/assessment', [App\Http\Controllers\AssessmentController::class, 'index'])->name('assessment.index');
    Route::get('/reports/individual', [App\Http\Controllers\Laporan\LaporanIndividuController::class, 'index'])->name('reports.individual');
    Route::get('/reports/class', [App\Http\Controllers\Laporan\LaporanKelasController::class, 'index'])->name('reports.class');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin routes
    Route::middleware('can:manage_users')->group(function () {
        Route::get('/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users');
    });

    Route::middleware('can:manage_settings')->group(function () {
        Route::get('/sekolah', [App\Http\Controllers\Admin\SekolahController::class, 'index'])->name('admin.sekolah');
        Route::get('/tahun-ajar', [App\Http\Controllers\Admin\TahunAjarController::class, 'index'])->name('admin.tahun-ajar');
    });
});

require __DIR__.'/auth.php';
