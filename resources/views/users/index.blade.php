@extends('layouts.app')
@section('title', 'Users')
@section('heading', 'Users')
@section('subheading', 'Create, edit, deactivate, and assign roles.')
@section('top-actions')
    <button type="button" class="btn primary" data-modal-open="new-user-template" data-modal-title="Create new user">Create New User</button>
@endsection

@section('content')
<div class="card">
    <div class="toolbar">
        <div class="search-wrap">
            <input type="text" data-table-search="usersTable" placeholder="Search name or email">
        </div>
    </div>
    <div class="table-wrap">
        <table id="usersTable" data-enhance>
            <thead><tr>
                <th data-sort="text">Full name</th>
                <th data-sort="text">Email</th>
                <th data-sort="text">Role</th>
                <th data-sort="date">Created</th>
                <th data-sort="text">Status</th>
                <th></th>
            </tr></thead>
            <tbody>
            @forelse($users as $u)
                <tr>
                    <td><b>{{ $u->fullname }}</b></td>
                    <td class="mono">{{ $u->email }}</td>
                    <td>{{ $u->role->role_name ?? '—' }}</td>
                    <td class="mono nowrap" data-sort-value="{{ $u->created_at }}">{{ optional($u->created_at)->format('d M Y') }}</td>
                    <td><span class="badge {{ $u->is_active ? 'green' : '' }}">{{ $u->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>
                        <div class="actions">
                            <button type="button" class="btn small" data-modal-open="edit-user-{{ $u->user_id }}" data-modal-title="Edit user account">Edit</button>
                            @if($u->is_active)
                                <form method="POST" action="{{ route('users.deactivate', $u) }}">
                                    @csrf
                                    <button type="submit" class="btn small danger">Deactivate</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('users.reactivate', $u) }}">
                                    @csrf
                                    <button type="submit" class="btn small primary">Activate</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr class="empty-row"><td colspan="6" class="empty">No users found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<template id="new-user-template">
    <form method="POST" action="{{ route('users.store') }}" class="stack">
        @csrf
        @include('users._form', ['user' => null, 'roles' => $roles])
        <div class="modal-actions">
            <button type="button" class="btn" onclick="CIMS.modal.close()">Cancel</button>
            <button type="submit" class="btn primary">Save user</button>
        </div>
    </form>
</template>

@foreach($users as $u)
    <template id="edit-user-{{ $u->user_id }}">
        <form method="POST" action="{{ route('users.update', $u) }}" class="stack">
            @csrf @method('PUT')
            @include('users._form', ['user' => $u, 'roles' => \App\Models\Role::where('status', 'active')->orWhere('role_id', $u->role_id)->orderBy('role_name')->get()])
            <div class="modal-actions">
                <button type="button" class="btn" onclick="CIMS.modal.close()">Cancel</button>
                <button type="submit" class="btn primary">Save user</button>
            </div>
        </form>
    </template>
@endforeach
@endsection
