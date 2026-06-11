<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class AssessmentController extends Controller
{
    public function index(): View
    {
        return view('assessment.index');
    }
}
