<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class KelasController extends Controller
{
    public function index(): View
    {
        return view('kelas.index');
    }
}
