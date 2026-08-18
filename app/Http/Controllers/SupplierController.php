<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\TransactionLog;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::orderBy('supplier_name')->get();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'contact_no' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);
        $data['status'] = 'active';

        $supplier = Supplier::create($data);

        TransactionLog::note(auth()->user(), "Added supplier {$supplier->supplier_name}", $supplier->supplier_name);

        return redirect()->route('suppliers.index')->with('status', 'Supplier saved.');
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'supplier_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'contact_no' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $supplier->update($data);

        TransactionLog::note(auth()->user(), "Edited supplier {$supplier->supplier_name}", $supplier->supplier_name);

        return redirect()->route('suppliers.index')->with('status', 'Supplier updated.');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->status !== 'active') {
            return redirect()->route('suppliers.index')->withErrors(['supplier' => 'Supplier is already inactive.']);
        }

        if ($reason = $supplier->deletionBlockedMessage()) {
            return redirect()->route('suppliers.index')->withErrors(['supplier' => $reason]);
        }

        $supplier->update(['status' => 'inactive']);

        TransactionLog::note(auth()->user(), "Soft-deleted supplier record {$supplier->supplier_name}", $supplier->supplier_name);

        return redirect()->route('suppliers.index')->with('status', 'Supplier deleted.');
    }
}
