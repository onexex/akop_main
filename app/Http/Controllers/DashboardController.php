<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\SmsMessage;
use App\Models\Classification; // Added to get list for dropdown
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Prepare the Base Query for Incidents based on filters
        $query = Incident::query();

        if ($request->filled('start_date')) {
            $query->whereDate('incidents.created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('incidents.created_at', '<=', $request->end_date);
        }

        if ($request->filled('classification')) {
            // Since you join with classifications table later, 
            // we filter by the ID or name depending on your dropdown value
            $query->whereHas('classification', function($q) use ($request) {
                $q->where('name', $request->classification);
            });
        }

        // 2. Calculate Trend (Usually kept global, or filtered? Here we keep global)
        $currentMonthCount = Incident::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $lastMonthCount = Incident::whereMonth('created_at', now()->subMonth()->month)->whereYear('created_at', now()->subMonth()->year)->count();
        $trendValue = $lastMonthCount > 0 ? (($currentMonthCount - $lastMonthCount) / $lastMonthCount) * 100 : ($currentMonthCount > 0 ? 100 : 0);

        // 3. KPI Counts (Filtered based on selection)
        $stats = [
            'new_sms' => SmsMessage::count(),
            'total_incidents' => (clone $query)->count(), // Filtered count
            'pending_analysis' => SmsMessage::where('is_read', 0)->count(),
            'processed_today' => Incident::whereDate('created_at', today())->count(),
            'trend_value' => round($trendValue, 1),
        ];

        // 4. Data for Reporter Chart (Filtered)
        $byReporter = (clone $query)->select('reporter', DB::raw('count(*) as total'))
            ->whereNotNull('reporter')
            ->groupBy('reporter')
            ->orderBy('total', 'desc')
            ->pluck('total', 'reporter');

        // 5. Data for Barangay Chart (Filtered)
        $byBarangay = (clone $query)->join('barangays', 'incidents.address_id', '=', 'barangays.id')
            ->select('barangays.barangay as name', DB::raw('count(*) as total'))
            ->groupBy('barangays.barangay')
            ->pluck('total', 'name');

        // 6. Data for Classification Chart (Filtered)
        $byType = (clone $query)->join('classifications', 'incidents.classification_id', '=', 'classifications.id')
            ->select('classifications.name', DB::raw('count(*) as total'))
            ->groupBy('classifications.name')
            ->pluck('total', 'name');

        // 7. Data for Manner Acquired (Filtered)
        $byManner = (clone $query)->select('manner_acquired', DB::raw('count(*) as total'))
            ->whereNotNull('manner_acquired')
            ->groupBy('manner_acquired')
            ->pluck('total', 'manner_acquired');

        // 8. Data for Source Chart (Filtered)
        $bySource = (clone $query)->select('source', DB::raw('count(*) as total'))
            ->whereNotNull('source')
            ->groupBy('source')
            ->orderBy('total', 'desc')
            ->pluck('total', 'source');

        // 9. Recent activity (Filtered)
        $recent = (clone $query)->with(['barangay', 'classification', 'attachments'])
            ->latest()
            ->limit(20)
            ->get();

        // 10. List for the Filter Dropdown
        $classificationList = Classification::pluck('name');

        return Inertia::render('Dashboard', [
            'filters' => $request->only(['start_date', 'end_date', 'classification']),
            'classifications' => $classificationList,
            'stats' => $stats,
            'chartData' => [
                'barangay' => $byBarangay,
                'types' => $byType,
                'manner' => $byManner,
                'reporter' => $byReporter,
                'sources' => $bySource,
            ],
            'recent' => $recent
        ]);
    }

    public function export(Request $request)
    {
        $query = Incident::query();

        // Apply the exact same filters as your index method
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        if ($request->filled('classification')) {
            $query->whereHas('classification', function($q) use ($request) {
                $q->where('name', $request->classification);
            });
        }

        $data = [
            
            'title' => 'Incident Report',
            'date' => now()->format('m/d/Y'),
            'filters' => $request->all(),
            'incidents' => $query->with(['barangay', 'classification'])->get(),
            'total' => $query->count(),
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.dashboard-report', $data);
        
        return $pdf->download('incident-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function filterAddress(Request $request)
    {
        $query = Incident::query();

        if ($request->addressType == 'barangay') {
            $byBarangay = (clone $query)->join('barangays', 'incidents.address_id', '=', 'barangays.id')
                ->select('barangays.barangay as name', DB::raw('count(*) as total'))
                ->groupBy('barangays.barangay')
                ->pluck('total', 'name');
        } else if ($request->addressType == 'city') {
            $byBarangay = (clone $query)->join('barangays', 'incidents.address_id', '=', 'barangays.id')
                ->select('barangays.city_municipality as name', DB::raw('count(*) as total'))
                ->groupBy('barangays.city_municipality')
                ->pluck('total', 'name');
        } else if ($request->addressType == 'province') {
            $byBarangay = (clone $query)->join('barangays', 'incidents.address_id', '=', 'barangays.id')
                ->select('barangays.province as name', DB::raw('count(*) as total'))
                ->groupBy('barangays.province')
                ->pluck('total', 'name');
        } 
        return response()->json($byBarangay);

    }
}