<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class LaporanKelasController extends Controller
{
    public function index(): View
    {
        return view('laporan.kelas');
    }
}
