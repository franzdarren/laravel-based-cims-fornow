@extends('layouts.app')
@section('title', 'Roles')
@section('heading', 'Roles')
@section('subheading', 'Define user roles and their page-level access.')
@section('top-actions')
    <button type="button" class="btn primary" data-modal-open="new-role-template" data-modal-title="Create new role">Create New Role</button>
@endsection

@section('content')
<div class="card">
    <div class="table-wrap">
        <table id="rolesTable" data-enhance>
            <thead><tr>
                <th data-sort="text">Role</th>
                <th>Description</th>
                <th>Pages / permissions</th>
                <th data-sort="number" class="right">Assigned users</th>
                <th data-sort="text">Status</th>
                <th></th>
            </tr></thead>
            <tbody>
            @forelse($roles as $r)
                <tr>
                    <td><b>{{ $r->role_name }}</b></td>
                    <td class="wrap">{{ $r->role_description }}</td>
                    <td class="wrap">
                        @forelse($r->permissions as $p)
                            <span class="badge">{{ $p->role_permission_name }}</span>
                        @empty
                            <span class="muted small">No permissions</span>
                        @endforelse
                    </td>
                    <td class="mono right">{{ $r->users_count }}</td>
                    <td><span class="badge {{ $r->status === 'active' ? 'green' : '' }}">{{ ucfirst($r->status) }}</span></td>
                    <td><button type="button" class="btn small" data-modal-open="edit-role-{{ $r->role_id }}" data-modal-title="Edit role">Edit</button></td>
                </tr>
            @empty
                <tr class="empty-row"><td colspan="6" class="empty">No roles found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<template id="new-role-template">
    <form method="POST" action="{{ route('roles.store') }}" class="stack">
        @csrf
        @include('roles._form', ['role' => null, 'permissionGroups' => $permissionGroups])
        <div class="modal-actions">
            <button type="button" class="btn" onclick="CIMS.modal.close()">Cancel</button>
            <button type="submit" class="btn primary">Save role</button>
        </div>
    </form>
</template>

@foreach($roles as $r)
    <template id="edit-role-{{ $r->role_id }}">
        <form method="POST" action="{{ route('roles.update', $r) }}" class="stack">
            @csrf @method('PUT')
            @include('roles._form', ['role' => $r, 'permissionGroups' => $permissionGroups])
            <div class="modal-actions">
                <button type="button" class="btn" onclick="CIMS.modal.close()">Cancel</button>
                <button type="submit" class="btn primary">Save role</button>
            </div>
        </form>
    </template>
@endforeach
@endsection
