@extends('layouts.app')
@section('title', 'Create User')
@section('heading', 'Create new user')
@section('subheading', 'Users')

@section('content')
<div class="card" style="max-width:600px">
    <form method="POST" action="{{ route('users.store') }}" class="stack">
        @csrf
        @include('users._form', ['user' => null, 'roles' => $roles])
        <div class="actions">
            <button type="submit" class="btn primary">Create user</button>
            <a href="{{ route('users.index') }}" class="btn">Cancel</a>
        </div>
    </form>
</div>
@endsection
