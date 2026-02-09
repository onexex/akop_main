<?php

namespace App\Http\Controllers\SMS;

use Inertia\Inertia;
use App\Models\Barangay;
use App\Models\Incident;
use App\Models\SmsMessage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Classification;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIncidentRequest;
use Illuminate\Support\Facades\Auth;

class SmsController extends Controller
{
    public function index(Request $request)
    {
        $query = SmsMessage::orderBy('created_at', 'desc');

        if (isset($request->status)) {
            $query = $query->where('status', $request->status);
        } else {
            $query = $query->where('status', 'unprocessed');
        }

        if (isset($request->dateFrom) && isset($request->dateTo)) {
            $query = $query->whereBetween('received_at', [$request->dateFrom, $request->dateTo]);
        }

        $messages = $query->paginate(10); 

        $region = Barangay::select('region')
            ->whereIn('region', ['Region X (Northern Mindanao)', 'Region IX (Zamboanga Peninsula)', 'Region XII (SOCCSKSARGEN)', 'Bangsamoro Autonomous Region In Muslim Mindanao (BARMM)', 'Region XI (Davao Region)', 'Region XIII (Caraga)'])
            ->distinct()->get();
        $province = Barangay::select('province', 'region')
            ->whereIn('region', ['Region X (Northern Mindanao)', 'Region IX (Zamboanga Peninsula)', 'Region XII (SOCCSKSARGEN)', 'Bangsamoro Autonomous Region In Muslim Mindanao (BARMM)', 'Region XI (Davao Region)', 'Region XIII (Caraga)'])
            ->orderBy('province')
            ->distinct()->get();

        $municipality = Barangay::select('city_municipality', 'province')
            ->whereIn('region', ['Region X (Northern Mindanao)', 'Region IX (Zamboanga Peninsula)', 'Region XII (SOCCSKSARGEN)', 'Bangsamoro Autonomous Region In Muslim Mindanao (BARMM)', 'Region XI (Davao Region)', 'Region XIII (Caraga)'])
            ->orderBy('city_municipality')
            ->distinct()->get();

        $barangay = Barangay::select('barangay', 'id', 'city_municipality')
            ->whereIn('region', ['Region X (Northern Mindanao)', 'Region IX (Zamboanga Peninsula)', 'Region XII (SOCCSKSARGEN)', 'Bangsamoro Autonomous Region In Muslim Mindanao (BARMM)', 'Region XI (Davao Region)', 'Region XIII (Caraga)'])
            ->orderBy('barangay')
            ->distinct()->get();
        
        $classifications = Classification::all();

        $subjects = Incident::select('subject')
            ->whereNotNUll('subject')
            ->distinct('subject')
            ->pluck('subject');

            
        $reporters = Incident::select('reporter')
            ->whereNotNUll('reporter')
            ->distinct('reporter')
            ->pluck('reporter');

        $sources = Incident::select('source')
            ->whereNotNUll('source')
            ->distinct('source')
            ->pluck('source');

        $fromDb = Incident::whereNotNull('manner_acquired')
            ->pluck('manner_acquired')
            ->toArray();

        $defaults = [
            'Phone Call',
            'Contact Meeting',
            'Elicitation',
            'Casing and Surveillance',
        ];

        $methodofcollections = collect($fromDb)
            ->merge($defaults)
            ->unique()
            ->values()
            ->toArray();

        $year = date('Y');
        $incident = Incident::whereYear('created_at', $year)->count();
        $num = str_pad($incident + 1, 3, '0', STR_PAD_LEFT);
        $filenumber = 'WMSO-' . $year . '-' . $num; 

        return Inertia::render('Messages/Sms', [
            'messages' => $messages,
            'regions' => $region,
            'provinces' => $province,
            'cities' => $municipality,
            'barangays' => $barangay,
            'classifications' => $classifications,
            'filenumber' => $filenumber,
            'subjects' => $subjects,
            'methodofcollections' => $methodofcollections,
            'reporters' => $reporters,
            'sources' => $sources,
            'filters' => [
                'status' => $request->status,
                'dateFrom' => $request->dateFrom,
                'dateTo' => $request->dateTo,
            ],
        ]);
    }

    public function store(StoreIncidentRequest $request)
    {
        $classification = Classification::where('id', $request->classificationId)
            ->first();

        $incident = Incident::create([
            'sms_id' => $request->smsId,
            'address_id' => $request->selectedBarangay,
            'description' => $request->subject,
            'type' =>  $classification ? $classification->name : $request->classificationId,
            'classification_id' => $request->classificationId,
            'file_number' => $request->file_number,
            'reference' => $request->reference,
            'subject' => $request->subject,
            'date_of_report' => $request->date_of_report,
            'reporter' => $request->reporter,
            'designation' => $request->designation,
            'evaluation' => $request->evaluation,
            'source' => $request->source,
            'date_acquired' => $request->dateAcquired,
            'manner_acquired' => $request->mannerAcquired,
            'information_proper' => $request->informationProper,
            'analysis' => $request->analysis,
            'actions' => $request->actions,
            'created_by' => Auth::user()->id,
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
    
                $extension = $file->getClientOriginalExtension();

                $filename = Str::uuid() . '-' . $request->reference . '.' . $extension;

                $path = $file->storeAs(
                    'incident/attachments',
                    $filename,
                    'public'
                );

                $incident->attachments()->create([
                    'file_name' => $filename,
                    'url' => $path,
                ]);
            }
        }

        SmsMessage::where('id', $request->smsId)
            ->update([
                'status' => 'processed',
                'is_read' => 1,
                'processed_by' => Auth::user()->id
            ]);

        return redirect()->back()->with('success', 'Successfully processed message.');
    }

    public function getReference(Request $request)
    {
        $year = date('Y');
        $classification = Classification::where('id', $request->classification_id)
            ->first();

        $incident = Incident::whereYear('created_at', $year)
            ->where('classification_id', $request->classification_id)
            ->count();
        $num = str_pad($incident + 1, 3, '0', STR_PAD_LEFT);
        $ref = $classification?->name . '-' . $year . '-' . $num; 

        return response()->json($ref);
    }

    public function updateStatus(Request $request)
    {
        $message = SmsMessage::find($request->sms_id);

        if ($message) {
            $message->status = $request->status;
            $message->save();

            return back()->with('success', 'Message successfully updated status');
        }

    }
}