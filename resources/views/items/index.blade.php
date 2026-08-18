@extends('layouts.app')
@section('title', 'Item Master')
@section('heading', 'Item Master')
@section('subheading', 'Maintain the baseline catalog for all tracked items')
@section('top-actions')
    <button type="button" class="btn primary" data-modal-open="new-item-template" data-modal-title="Add new item">Add new item</button>
@endsection

@section('content')
<div class="stack">
    <div class="notice"><b>Near-expiry days is one global medicine setting.</b> Every medicine uses the same value from System Settings; it cannot be changed per medicine or per batch.</div>

    <div class="card">
        <div class="card-head"><h2>Item master records</h2><span class="sub">Click a column heading to sort</span></div>
        <div class="toolbar">
            <div class="search-wrap">
                <input type="text" data-table-search="itemMasterTable" placeholder="Search code or item name">
                <select id="itemCategoryFilter"><option value="">All categories</option><option value="MEDICINE">Medicine</option><option value="SUPPLY">Supply</option><option value="EQUIPMENT">Equipment</option></select>
                <select id="itemStatusFilter"><option value="">All statuses</option><option value="active">Active</option><option value="inactive">Inactive</option></select>
            </div>
        </div>
        <div class="table-wrap">
            <table id="itemMasterTable" data-enhance>
                <thead><tr>
                    <th data-sort="text">Code</th>
                    <th data-sort="text">Name</th>
                    <th data-sort="text">Category</th>
                    <th data-sort="text">Unit</th>
                    <th>Supplier</th>
                    <th data-sort="number">Reorder level</th>
                    <th data-sort="text">Status</th>
                    <th></th>
                </tr></thead>
                <tbody>
                @forelse($items as $i)
                    <tr data-category="{{ $i->item_category }}" data-status="{{ $i->item_status }}">
                        <td class="mono">{{ $i->item_code }}</td>
                        <td>{{ $i->item_name }}</td>
                        <td>{{ ucfirst(strtolower($i->item_category)) }}</td>
                        <td>{{ $i->uom->uom_name ?? '—' }}</td>
                        <td>{{ $i->supplier->supplier_name ?? '—' }}</td>
                        <td class="mono">{{ $i->reorder_threshold }}</td>
                        <td><span class="badge {{ $i->item_status === 'active' ? 'green' : '' }}">{{ ucfirst($i->item_status) }}</span></td>
                        <td>
                            <div class="actions">
                                <button type="button" class="btn small" data-modal-open="edit-item-{{ $i->item_id }}" data-modal-title="Edit item">Edit</button>
                                @if($i->item_status === 'active')
                                    @if($i->hasActiveRecords())
                                        <button type="button" class="btn small danger" disabled title="Active batch or equipment record exists">Delete</button>
                                    @else
                                        <button type="button" class="btn small danger" data-modal-open="delete-item-{{ $i->item_id }}" data-modal-title="Delete item">Delete</button>
                                    @endif
                                @else
                                    <form method="POST" action="{{ route('items.reactivate', $i) }}">
                                        @csrf
                                        <button type="submit" class="btn small primary">Reactivate</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="empty-row"><td colspan="8" class="empty">No items found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<template id="new-item-template">
    <form method="POST" action="{{ route('items.store') }}" class="stack">
        @csrf
        @include('items._form', ['item' => null, 'suppliers' => $suppliers, 'uoms' => $uoms])
        <div class="modal-actions">
            <button type="button" class="btn" onclick="CIMS.modal.close()">Cancel</button>
            <button type="submit" class="btn primary">Add new item</button>
        </div>
    </form>
</template>

@foreach($items as $i)
    <template id="edit-item-{{ $i->item_id }}">
        <form method="POST" action="{{ route('items.update', $i) }}" class="stack">
            @csrf @method('PUT')
            @include('items._form', ['item' => $i, 'suppliers' => $suppliers, 'uoms' => $uoms])
            <div class="modal-actions">
                <button type="button" class="btn" onclick="CIMS.modal.close()">Cancel</button>
                <button type="submit" class="btn primary">Save changes</button>
            </div>
        </form>
    </template>
    @if($i->item_status === 'active' && ! $i->hasActiveRecords())
        <template id="delete-item-{{ $i->item_id }}">
            <div class="context-summary"><b>{{ $i->item_name }}</b><span class="mono">{{ $i->item_code }}</span></div>
            <div class="notice warn">This is a soft delete. The item record and its transaction history will remain in the system, but the item will be marked inactive and will no longer be available for new transactions.</div>
            <form method="POST" action="{{ route('items.destroy', $i) }}" class="modal-actions">
                @csrf @method('DELETE')
                <button type="button" class="btn" onclick="CIMS.modal.close()">Cancel</button>
                <button type="submit" class="btn danger">Delete item</button>
            </form>
        </template>
    @endif
@endforeach
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const table = document.getElementById('itemMasterTable');
    const category = document.getElementById('itemCategoryFilter');
    const status = document.getElementById('itemStatusFilter');
    const apply = () => {
        [...table.tBodies[0].rows].forEach(row => {
            if (row.classList.contains('empty-row')) return;
            const catOk = !category.value || row.dataset.category === category.value;
            const statusOk = !status.value || row.dataset.status === status.value;
            row.dataset.filterHidden = (catOk && statusOk) ? '0' : '1';
        });
        CIMS.tables.refresh(table);
    };
    category.addEventListener('change', apply);
    status.addEventListener('change', apply);
});
</script>
@endsection
