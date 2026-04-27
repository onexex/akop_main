<?php

namespace App\Http\Controllers;

use App\Models\CashAdvance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CashAdvanceController extends Controller
{
//  public function index()
//     {
//         // Importante: Gamitin ang .with('items')
//         $requests = CashAdvance::where('user_id', Auth::id())
//             ->with(['items']) 
//             ->latest()
//             ->get();

//         return Inertia::render('RequestDashboard/Index', [
//             'requests' => $requests,
//         ]);
//     }

    public function index()
    {
        $user = auth()->user();
        $query = CashAdvance::with(['items', 'user']); // Idagdag ang 'user' para makita kung kanino ang request

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
        
        // 4. Default: Requestor (Sariling request lang ang makikita)
        else {
            $query->where('user_id', $user->id);
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
                $ca->items()->create([
                    'details' => $item['details'],
                    'qty' => $item['qty'],
                    'amount_usd' => $item['amount_usd'] ?? 0,
                    'amount_peso' => $item['amount_peso'],
                    'total' => $item['qty'] * $item['amount_peso'],
                ]);
            }
        });

        return redirect()->back()->with('success', 'Submitted successfully!');
    }

    public function update(Request $request, CashAdvance $cashAdvance)
    {
        if ($cashAdvance->user_id !== Auth::id() || $cashAdvance->status !== 'pending') {
            abort(403);
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
            $cashAdvance->update($validated);
            $cashAdvance->items()->delete();
            foreach ($validated['items'] as $item) {
                $cashAdvance->items()->create([
                    'details' => $item['details'],
                    'qty' => $item['qty'],
                    'amount_usd' => $item['amount_usd'] ?? 0,
                    'amount_peso' => $item['amount_peso'],
                    'total' => $item['qty'] * $item['amount_peso'],
                ]);
            }
        });

        return redirect()->back()->with('success', 'Updated successfully!');
    }

    public function updateStatus(Request $request, CashAdvance $cashAdvance)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:approved_by_l1,disapproved_by_l1,approved_by_l2,disapproved_by_l2,released'
        ]);

        // Opsyonal: Double check permissions sa backend para sa security
        if ($validated['status'] === 'approved_by_l1' && !auth()->user()->can('approve request level 1')) {
            abort(403);
        }

        $cashAdvance->update([
            'status' => $validated['status']
        ]);

        return back()->with('success', 'Status updated successfully.');
    }
}