@extends('layouts.app')
@section('title', 'Add Item')
@section('heading', 'Add new item')
@section('subheading', 'Item Master')

@section('content')
<div class="card" style="max-width:720px">
    <form method="POST" action="{{ route('items.store') }}" class="stack">
        @csrf
        @include('items._form', ['item' => null, 'suppliers' => $suppliers, 'uoms' => $uoms])
        <div class="actions">
            <button type="submit" class="btn primary">Add new item</button>
            <a href="{{ route('items.index') }}" class="btn">Cancel</a>
        </div>
    </form>
</div>
@endsection
