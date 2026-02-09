<?php

namespace App\Http\Controllers;

use App\Models\IncidentWatermark;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IncidentWatermarkController extends Controller
{

    public function index()
    {
        $incidentwatermarks = IncidentWatermark::get();

        return Inertia::render('IncidentWatermark/Index', [
            'incidentWatermarks' => $incidentwatermarks
        ]);
    }

    public function store(Request $request)
    {
        IncidentWatermark::create([
            'name' => $request->name,
            'type' => $request->type,
            'color' => $request->color,
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', 'Successfully added incident watermark');
    }
    
    
    public function update(IncidentWatermark $incidentwatermark, Request $request)
    {
        if ($incidentwatermark) {
            $incidentwatermark->update([
                'name' => $request->name,
                'type' => $request->type,
                'color' => $request->color,
                'description' => $request->description,
            ]);
        }
        return redirect()->back()->with('success', 'Successfully updated incident watermark');
    }
}
