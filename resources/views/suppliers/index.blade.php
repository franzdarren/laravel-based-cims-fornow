@extends('layouts.app')
@section('title', 'Suppliers')
@section('heading', 'Suppliers')
@section('subheading', 'Maintain supplier records')
@section('top-actions')
    <button type="button" class="btn primary" data-modal-open="new-supplier-template" data-modal-title="New supplier">+ New supplier</button>
@endsection

@section('content')
<div class="card">
    <div class="toolbar">
        <div class="search-wrap">
            <input type="text" data-table-search="suppliersTable" placeholder="Search supplier name or contact">
        </div>
    </div>
    <div class="table-wrap">
        <table id="suppliersTable" data-enhance>
            <thead><tr>
                <th data-sort="text">Supplier</th>
                <th data-sort="text">Contact person</th>
                <th data-sort="text">Contact no.</th>
                <th>Address</th>
                <th></th>
            </tr></thead>
            <tbody>
            @forelse($suppliers as $s)
                <tr>
                    <td><b>{{ $s->supplier_name }}</b>@if($s->status === 'inactive')<div class="muted small">Inactive</div>@endif</td>
                    <td>{{ $s->contact_person }}</td>
                    <td class="mono">{{ $s->contact_no }}</td>
                    <td class="wrap">{{ $s->address }}</td>
                    <td>
                        @if($s->status === 'active')
                            <div class="actions">
                                <button type="button" class="btn small" data-modal-open="edit-supplier-{{ $s->supplier_id }}" data-modal-title="Edit supplier">Edit</button>
                                <button type="button" class="btn small danger" data-modal-open="delete-supplier-{{ $s->supplier_id }}" data-modal-title="Delete supplier">Delete</button>
                            </div>
                        @else
                            <span class="muted small">Soft deleted</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr class="empty-row"><td colspan="5" class="empty">No suppliers found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<template id="new-supplier-template">
    <form method="POST" action="{{ route('suppliers.store') }}" class="stack">
        @csrf
        @include('suppliers._form', ['supplier' => null])
        <div class="modal-actions">
            <button type="button" class="btn" onclick="CIMS.modal.close()">Cancel</button>
            <button type="submit" class="btn primary">Save supplier</button>
        </div>
    </form>
</template>

@foreach($suppliers as $s)
    <template id="edit-supplier-{{ $s->supplier_id }}">
        <form method="POST" action="{{ route('suppliers.update', $s) }}" class="stack">
            @csrf @method('PUT')
            @include('suppliers._form', ['supplier' => $s])
            <div class="modal-actions">
                <button type="button" class="btn" onclick="CIMS.modal.close()">Cancel</button>
                <button type="submit" class="btn primary">Save supplier</button>
            </div>
        </form>
    </template>
    <template id="delete-supplier-{{ $s->supplier_id }}">
        <div class="context-summary"><b>{{ $s->supplier_name }}</b>{{ $s->contact_person ?: 'No contact person' }}</div>
        @php $blocked = $s->deletionBlockedMessage(); @endphp
        @if($blocked)
            <div class="notice danger">{{ $blocked }}</div>
            <div class="modal-actions"><button type="button" class="btn" onclick="CIMS.modal.close()">Close</button></div>
        @else
            <div class="notice warn">This is a soft delete. The supplier remains in historical transactions but will no longer be available for new receiving transactions.</div>
            <form method="POST" action="{{ route('suppliers.destroy', $s) }}" class="modal-actions">
                @csrf @method('DELETE')
                <button type="button" class="btn" onclick="CIMS.modal.close()">Cancel</button>
                <button type="submit" class="btn danger">Delete supplier</button>
            </form>
        @endif
    </template>
@endforeach
@endsection
