@extends('layouts.app')
@section('title', 'Edit User')
@section('heading', 'Edit user')
@section('subheading', $user->fullname)

@section('content')
<div class="card" style="max-width:600px">
    <form method="POST" action="{{ route('users.update', $user) }}" class="stack">
        @csrf @method('PUT')
        @include('users._form', ['user' => $user, 'roles' => $roles])
        <div class="actions">
            <button type="submit" class="btn primary">Save changes</button>
            <a href="{{ route('users.index') }}" class="btn">Cancel</a>
        </div>
    </form>
</div>
@endsection
