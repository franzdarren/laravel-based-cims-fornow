@extends('layouts.app')
@section('title', 'Edit Item')
@section('heading', 'Edit item')
@section('subheading', $item->item_code)

@section('content')
<div class="card" style="max-width:720px">
    <form method="POST" action="{{ route('items.update', $item) }}" class="stack">
        @csrf @method('PUT')
        @include('items._form', ['item' => $item, 'suppliers' => $suppliers, 'uoms' => $uoms])
        <div class="actions">
            <button type="submit" class="btn primary">Save changes</button>
            <a href="{{ route('items.index') }}" class="btn">Cancel</a>
        </div>
    </form>
</div>
@endsection
