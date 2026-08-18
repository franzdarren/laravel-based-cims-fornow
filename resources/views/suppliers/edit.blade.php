@extends('layouts.app')
@section('title', 'Edit Supplier')
@section('heading', 'Edit supplier')
@section('subheading', $supplier->supplier_name)

@section('content')
<div class="card" style="max-width:600px">
    <form method="POST" action="{{ route('suppliers.update', $supplier) }}" class="stack">
        @csrf @method('PUT')
        @include('suppliers._form', ['supplier' => $supplier])
        <div class="actions">
            <button type="submit" class="btn primary">Save changes</button>
            <a href="{{ route('suppliers.index') }}" class="btn">Cancel</a>
        </div>
    </form>
</div>
@endsection
