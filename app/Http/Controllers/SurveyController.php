<?php

namespace App\Http\Controllers;

use App\Models\SurveyLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SurveyController extends Controller
{
    public function index()
    {
       
        $surveys = SurveyLog::with('addressProperty')->get();

        $provinceOptions = \DB::table('address_properties')
            ->selectRaw('MIN(id) as id, province')
            ->whereNotNull('province')
            ->where('province', '!=', '')
            ->groupBy('province')
            ->orderBy('province', 'asc')
            ->get()
            ->map(fn ($item) => [
                'label' => $item->province,
                'value' => $item->id
            ]);
            
            return Inertia::render('Survey/Index', [
                'surveys' => $surveys,
                'provinceOptions' => $provinceOptions,
            ]);
    }

    public function fetchCities(Request $request)
    {
        // Simple query: kunin ang cities base sa province name
        $cities = \DB::table('address_properties')
            ->where('province', $request->province_name)
            ->selectRaw('MIN(id) as id, city_municipality')
            ->groupBy('city_municipality')
            ->orderBy('city_municipality', 'asc')
            ->get()
            ->map(fn($c) => [
                'label' => $c->city_municipality,
                'value' => $c->id 
            ]);

        return response()->json($cities); // JSON lang, walang Inertia overhead
    }

    public function fetchBarangays(Request $request)
    {
        // Kunin lahat ng barangay base sa napiling city
        $barangays = \DB::table('address_properties')
            ->where('city_municipality', $request->city_name)
            ->select('id', 'barangay')
            ->orderBy('barangay', 'asc')
            ->get()
            ->map(fn($b) => [
                'label' => $b->barangay,
                'value' => $b->id // Ito na ang final ID na itatag natin sa survey
            ]);

        return response()->json($barangays);
    }

    public function store(Request $request)
    {
        SurveyLog::create([
            'caption' => $request->caption,
            'remarks' => $request->remarks,
            'address_tag' => $request->address_tag,
        ]);
        
        return redirect()->back()->with('success', 'Successfully added survey');
    }
}
