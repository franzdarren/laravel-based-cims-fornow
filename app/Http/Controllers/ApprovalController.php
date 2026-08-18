<?php

namespace App\Http\Controllers;

use App\Models\ReceivingTransaction;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function index()
    {
        $pending = ReceivingTransaction::with(['supplier', 'receivedBy', 'lines.item'])
            ->where('status', 'PENDING')
            ->orderBy('date_received')
            ->get();

        return view('approvals.index', compact('pending'));
    }

    public function show(ReceivingTransaction $receiving)
    {
        $receiving->load(['supplier', 'receivedBy', 'lines.item']);

        return view('approvals.show', compact('receiving'));
    }

    public function approve(Request $request, ReceivingTransaction $receiving)
    {
        abort_unless($receiving->isPending(), 422, 'This request has already been decided.');

        $receiving->load('lines.item');
        $blockers = $receiving->approvalBlockers();
        if ($blockers) {
            return redirect()->route('approvals.index')->withErrors(['approval' => 'Return the request: '.implode(' ', $blockers)]);
        }

        $receiving->approve($request->user());

        return redirect()->route('approvals.index')->with('status', "{$receiving->ref_no} approved and posted to inventory.");
    }

    public function return(Request $request, ReceivingTransaction $receiving)
    {
        abort_unless($receiving->isPending(), 422, 'This request has already been decided.');

        $data = $request->validate(['reason' => ['required', 'string', 'max:150']]);

        $receiving->returnWithReason($request->user(), $data['reason']);

        return redirect()->route('approvals.index')->with('status', "{$receiving->ref_no} returned to the Nurse.");
    }
}
