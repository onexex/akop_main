<?php

namespace App\Http\Controllers;

use App\Helpers\NotificationHelper;
use App\Models\CashAdvance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CashAdvanceController extends Controller
{

    public function index()
    {
        $user = auth()->user();
        $query = CashAdvance::with(['items', 'user']); 

        // 1. Logic para sa Approver Level 1
        if ($user->can('approve request level 1')) {
            // Makikita lahat ng status: approve, disapproved, pending, release
            // Walang filter sa status, pero baka gusto mo i-filter based sa department? 
            // Kung hindi, hayaan lang nating walang status filter.
        } 
        
        // 2. Logic para sa Approver Level 2
        elseif ($user->can('approve request level 2')) {
            $query->whereIn('status', [
                'approved_by_l1', // Na-approve ni L1 (Hinihintay si L2)
                'approved_by_l2', // Na-approve na ni L2
                'disapproved_by_l2', // Na-disapprove ni L2
                'released' // Na-release na
            ]);
        } 
        
        // 3. Logic para sa Releasing
        elseif ($user->can('release request')) {
            $query->whereIn('status', [
                'approved_by_l2', // Na-approve na ni L2 (Hinihintay i-release)
                'released' // Na-release na niya
            ]);
        } 
        
        else {
            $query->where('user_id', $user->id);
            // dd( $user->id);
        }
       

        $requests = $query->latest()->get();

        return Inertia::render('RequestDashboard/Index', [
            'requests' => $requests,
        ]);
    }

    public function store(Request $request)
    {
     
        $validated = $request->validate([
            'date' => 'required|date',
            'district_office' => 'required|string',
            'purpose' => 'required|string',
            'beneficiaries' => 'required|string',
            'amount_in_figure' => 'required|numeric',
            'amount_in_words' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.details' => 'required|string',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.amount_peso' => 'required|numeric',
        ]);

        DB::transaction(function () use ($validated) {
            $year = now()->year;
            $count = CashAdvance::whereYear('created_at', $year)->count() + 1;
            $caNumber = 'CA-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            $ca = CashAdvance::create([
                'ca_number' => $caNumber,
                'user_id' => Auth::id(),
                'date' => $validated['date'],
                'district_office' => $validated['district_office'],
                'purpose' => $validated['purpose'],
                'beneficiaries' => $validated['beneficiaries'],
                'amount_in_figure' => $validated['amount_in_figure'],
                'amount_in_words' => $validated['amount_in_words'],
                'status' => 'pending',
            ]);

            foreach ($validated['items'] as $item) {
              
                $qty = $item['qty'] ?? 0;
                $peso = $item['amount_peso'] ?? 0;
                         
                $usdRate = (!empty($item['amount_usd']) && $item['amount_usd'] > 0) 
                        ? $item['amount_usd'] 
                        : 1;

                $ca->items()->create([
                    'details'     => $item['details'],
                    'qty'         => $qty,
                    'amount_usd'  => $item['amount_usd'] ?? 0, 
                    'amount_peso' => $peso,
                    // Formula: Qty * USD (Rate) * Peso
                    'total'       => $qty * $usdRate * $peso,
                ]);
            }

            $approvers = \App\Models\User::role('level 1 approver')->get();

            foreach ($approvers as $approver) {
            NotificationHelper::send(
                    $approver->id, 
                    'New Approval Needed', 
                    "Request from " . auth()->user()->name . " is ready for review.", 
                    "/cash-advances"
                );
            }
        });

        return redirect()->back()->with('success', 'Submitted successfully!');
    }

    // public function update(Request $request, CashAdvance $cashAdvance)
    // {
    //     if ($cashAdvance->user_id !== Auth::id() || $cashAdvance->status !== 'pending') {
    //         abort(403);
    //     }

    //     $validated = $request->validate([
    //         'date' => 'required|date',
    //         'district_office' => 'required|string',
    //         'purpose' => 'required|string',
    //         'beneficiaries' => 'required|string',
    //         'amount_in_figure' => 'required|numeric',
    //         'amount_in_words' => 'required|string',
    //         'items' => 'required|array|min:1',
    //         'items.*.details' => 'required|string',
    //         'items.*.qty' => 'required|integer|min:1',
    //         'items.*.amount_peso' => 'required|numeric',
    //     ]);

    //     DB::transaction(function () use ($validated, $cashAdvance) {
    //         $cashAdvance->update($validated);
    //         $cashAdvance->items()->delete();
    //         foreach ($validated['items'] as $item) {
    //             $cashAdvance->items()->create([
    //                 'details' => $item['details'],
    //                 'qty' => $item['qty'],
    //                 'amount_usd' => $item['amount_usd'] ?? 0,
    //                 'amount_peso' => $item['amount_peso'],
    //                 'total' => $item['qty'] * $item['amount_peso'],
    //             ]);
    //         }
    //     });

    //     return redirect()->back()->with('success', 'Updated successfully!');
    // }

    public function update(Request $request, CashAdvance $cashAdvance)
    {
        // 1. Payagan ang edit kung may-ari siya AT kung ang status ay pending O disapproved
        $allowedStatuses = ['pending', 'disapproved_by_l1', 'disapproved_by_l2'];
        
        if ($cashAdvance->user_id !== Auth::id() || !in_array($cashAdvance->status, $allowedStatuses)) {
            abort(403, 'Unauthorized or record is already being processed.');
        }

        $validated = $request->validate([
            'date' => 'required|date',
            'district_office' => 'required|string',
            'purpose' => 'required|string',
            'beneficiaries' => 'required|string',
            'amount_in_figure' => 'required|numeric',
            'amount_in_words' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.details' => 'required|string',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.amount_peso' => 'required|numeric',
        ]);

        DB::transaction(function () use ($validated, $cashAdvance) {

            $cashAdvance->update(array_merge($validated, [
                'status' => 'pending'
            ]));

            $cashAdvance->items()->delete();
            foreach ($validated['items'] as $item) {
                $qty = $item['qty'] ?? 0;
                $peso = $item['amount_peso'] ?? 0;
                
                $usdRate = (!empty($item['amount_usd']) && $item['amount_usd'] > 0) 
                        ? $item['amount_usd'] 
                        : 1;

                $cashAdvance->items()->create([
                    'details'     => $item['details'],
                    'qty'         => $qty,
                    'amount_usd'  => $item['amount_usd'] ?? 0, 
                    'amount_peso' => $peso,
                    
                    'total'       => $qty * $usdRate * $peso,
                ]);
            }
        });

        return redirect()->back()->with('success', 'Request resubmitted and status reset to pending.');
    }

    // public function updateStatus(Request $request, CashAdvance $cashAdvance)
    // {
    //     $validated = $request->validate([
    //         'status' => 'required|string|in:approved_by_l1,disapproved_by_l1,approved_by_l2,disapproved_by_l2,released'
    //     ]);

    //     if ($validated['status'] === 'approved_by_l1' && !auth()->user()->can('approve request level 1')) {
    //         abort(403);
    //     }

    //     $cashAdvance->update([
    //         'status' => $validated['status']
    //     ]);

    //     return back()->with('success', 'Status updated successfully.');
    // }

    public function updateStatus(Request $request, CashAdvance $cashAdvance)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:approved_by_l1,disapproved_by_l1,approved_by_l2,disapproved_by_l2,released'
        ]);

        if ($validated['status'] === 'approved_by_l1' && !auth()->user()->can('approve request level 1')) {
            abort(403);
        }

        $cashAdvance->update([
            'status' => $validated['status']
        ]);

        // --- START NG NOTIFICATION LOGIC ---

        // 1. Notification para sa Source Creator (Ang nag-request)
        // Sasabihan ang user kung na-approve o na-disapprove ang request niya.
        NotificationHelper::send(
            $cashAdvance->user_id, 
            'Cash Advance Update', 
            "Your request has been: " . str_replace('_', ' ', $validated['status'] . " with CA Number: " . $cashAdvance->ca_number),
            "/cash-advances"
        );

        // 2. Notification para sa Level 2 Approvers (Kung na-approve na ng L1)
        if ($validated['status'] === 'approved_by_l1') {
            $l2Approvers = \App\Models\User::role('level 2 approver')->get();
            
            foreach ($l2Approvers as $approver) {
                NotificationHelper::send(
                    $approver->id, 
                    'New Approval Needed (L2)', 
                    "The request from {$cashAdvance->user->name} has been approved by L1. Ready for your review.",
                    "/cash-advances"
                );
            }
        }

        // --- END NG NOTIFICATION LOGIC ---

        return back()->with('success', 'Status updated successfully.');
    }
}