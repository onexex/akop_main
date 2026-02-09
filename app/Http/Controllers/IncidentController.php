<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Incident;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\IncidentsExport;
use App\Models\IncidentWatermark;
use Maatwebsite\Excel\Facades\Excel;
use DB;

class IncidentController extends Controller
{
//    public function index()
//     {
//         return inertia('MapView', [
//             'incidents' => Incident::with('barangay')->get()
//         ]);
//     }

    //  public function index()
    // {
    //     // We use 'with' to eager-load the barangay coordinates 
    //     // linked via address_id
    //     return response()->json(Incident::with('barangay','attachments')->get());
    // }
public function index()
    {
        try {
            // Aggregate counts by Barangay PCODE
            $counts = Incident::join('barangays', 'incidents.address_id', '=', 'barangays.id')
                ->select('barangays.code as pcode', DB::raw('count(*) as total'))
                ->groupBy('barangays.code')
                ->pluck('total', 'pcode');

            return response()->json([
                'incident_counts' => $counts,
                'total_national' => $counts->sum() // Vital for percentage calculation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server Error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function processedMessages(Request $request)
    {
        $query = Incident::orderBy('created_at', 'desc')
            ->with('barangay', 'classification', 'classification', 'attachments');

        if ($request->reporter) {
            $query = $query->where('reporter', $request->reporter);
        }

        if ($request->source) {
            $query = $query->where('source', $request->source);
        }
        
        if ($request->dateFrom && $request->dateTo) {
            $query = $query->whereBetween('date_of_report', [$request->dateFrom, $request->dateTo]);
        }

        $incidents = $query->paginate(10)
            ->withQueryString();
        
        $watermarks = IncidentWatermark::get();
        
        $reporters = Incident::select('reporter')
            ->whereNotNUll('reporter')
            ->distinct('reporter')
            ->pluck('reporter');
            
        $sources = Incident::select('source')
            ->whereNotNUll('source')
            ->distinct('source')
            ->pluck('source');


        return Inertia::render('Messages/ProcessedMessage', [
            'messages' => $incidents,
            'watermarks' => $watermarks,
            'reporters' => $reporters,
            'sources' => $sources,
            'filter' => [
                'reporter' => $request->reporter ?? '',
                'source' => $request->source ?? '',
                'dateFrom' => $request->dateFrom ?? '',
                'dateTo' => $request->dateTo ?? '',
            ],
        ]);
    }

    public function download(Incident $incident, Request $request)
    {
        $watermark = IncidentWatermark::where('id', $request->copyFor)->first();
        $pdf = Pdf::loadView('incidents.incidentpdf', [
            'incident' => $incident,
            'copyFor' => $watermark->name ?? '',
            'bgColor' => $watermark->color ?? '#c7080826',
            'attachments' => $incident->attachments,
        ]);

        return $pdf->stream("incident-{$incident->id}.pdf");
    }

    public function export(Request $request)
    {
        return Excel::download(
            new IncidentsExport(
                $request->reporter,
                $request->source,
                $request->date_from,
                $request->date_to
            ),
            'incidents.xlsx'
        );
    }
}