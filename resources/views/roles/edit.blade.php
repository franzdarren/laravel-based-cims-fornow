@extends('layouts.app')
@section('title', 'Edit Role')
@section('heading', 'Edit role')
@section('subheading', $role->role_name)

@section('content')
<div class="card" style="max-width:640px">
    <form method="POST" action="{{ route('roles.update', $role) }}" class="stack">
        @csrf @method('PUT')
        @include('roles._form', ['role' => $role, 'permissionGroups' => $permissionGroups])
        <div class="actions">
            <button type="submit" class="btn primary">Save changes</button>
            <a href="{{ route('roles.index') }}" class="btn">Cancel</a>
        </div>
    </form>
</div>
@endsection
