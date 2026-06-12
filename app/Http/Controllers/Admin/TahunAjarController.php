<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class TahunAjarController extends Controller
{
    public function index(): View
    {
        return view('admin.tahun-ajar.index');
    }
}
