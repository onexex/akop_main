<?php

namespace App\Http\Controllers;

use App\Models\Classification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClassificationController extends Controller
{
    public function index()
    {
        $classifications = Classification::get();
        return Inertia::render('Classification/Index', [
            'classifications' => $classifications,
        ]);
    }

    public function store(Request $request)
    {
        Classification::create([
            'name' => $request->code,
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', 'Successfully added classification');
    }

    public function update(Classification $classification, Request $request) 
    {
        if ($classification) {
            $classification->name = $request->code;
            $classification->description = $request->description;
            $classification->save();
            
            return redirect()->back()->with('success', 'Successfully updated classification');
        }
    }
}
