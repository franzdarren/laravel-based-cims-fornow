<?php

namespace App\Http\Controllers;

use App\Models\TransactionLog;
use Illuminate\Http\Request;

class TransactionLogController extends Controller
{
    public function index(Request $request)
    {
        $query = TransactionLog::with(['user', 'lines'])->orderByDesc('transaction_datetime');

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('reference_no', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%")
                    ->orWhere('transaction_type', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('fullname', 'like', "%{$search}%"));
            });
        }

        if ($type = $request->query('type')) {
            $query->where('transaction_type', $type);
        }

        $logs = $query->paginate(25)->withQueryString();

        return view('logs.index', [
            'logs' => $logs,
            'types' => TransactionLog::CANONICAL_TYPES,
            'search' => $search ?? '',
            'selectedType' => $type ?? '',
        ]);
    }
}
