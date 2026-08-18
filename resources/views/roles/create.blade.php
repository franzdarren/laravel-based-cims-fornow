@extends('layouts.app')
@section('title', 'Add Role')
@section('heading', 'Add role')
@section('subheading', 'Roles')

@section('content')
<div class="card" style="max-width:640px">
    <form method="POST" action="{{ route('roles.store') }}" class="stack">
        @csrf
        @include('roles._form', ['role' => null, 'permissionGroups' => $permissionGroups])
        <div class="actions">
            <button type="submit" class="btn primary">Save role</button>
            <a href="{{ route('roles.index') }}" class="btn">Cancel</a>
        </div>
    </form>
</div>
@endsection
