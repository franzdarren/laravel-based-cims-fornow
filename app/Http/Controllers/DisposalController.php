<?php

namespace App\Http\Controllers;

use App\Models\TransactionLog;

class DisposalController extends Controller
{
    // Read-only history. Disposals are always created from the Batch or
    // Equipment record itself (see BatchController::dispose / EquipmentController::dispose)
    // as a DISPOSAL-type transaction_log row.
    public function index()
    {
        $disposals = TransactionLog::with(['lines.batch.item', 'lines.equipment.item', 'user'])
            ->where('transaction_type', 'DISPOSAL')
            ->orderByDesc('transaction_datetime')
            ->get();

        return view('disposals.index', compact('disposals'));
    }
}
