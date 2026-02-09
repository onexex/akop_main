<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;

class AssesmentCtrl extends Controller
{
    public function index()
    {
        return Inertia::render('AssessmentAI/Assessment');
    }
}
