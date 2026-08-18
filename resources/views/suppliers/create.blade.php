@extends('layouts.app')
@section('title', 'Add Supplier')
@section('heading', 'Add supplier')
@section('subheading', 'Suppliers')

@section('content')
<div class="card" style="max-width:600px">
    <form method="POST" action="{{ route('suppliers.store') }}" class="stack">
        @csrf
        @include('suppliers._form', ['supplier' => null])
        <div class="actions">
            <button type="submit" class="btn primary">Save supplier</button>
            <a href="{{ route('suppliers.index') }}" class="btn">Cancel</a>
        </div>
    </form>
</div>
@endsection
