<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class LaporanIndividuController extends Controller
{
    public function index(): View
    {
        return view('laporan.individu');
    }
}
