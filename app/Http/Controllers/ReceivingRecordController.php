<?php

namespace App\Http\Controllers;

use App\Models\ReceivingTransaction;

class ReceivingRecordController extends Controller
{
    // Supervisor's read-only "Receiving Records" page (manual 6.3).
    public function index()
    {
        $records = ReceivingTransaction::with(['supplier', 'receivedBy', 'approvedBy'])
            ->latest('date_received')
            ->get();

        return view('receiving.records', compact('records'));
    }
}
