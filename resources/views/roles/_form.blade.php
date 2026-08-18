@php $selected = old('permissions', $role?->permissions->pluck('role_permission_name')->all() ?? []); @endphp
<div class="form-grid">
    <div class="field span-2">
        <label class="req">Role name</label>
        <input type="text" name="role_name" value="{{ old('role_name', $role->role_name ?? '') }}" required>
    </div>
    <div class="field span-2">
        <label>Description</label>
        <input type="text" name="role_description" value="{{ old('role_description', $role->role_description ?? '') }}">
    </div>
</div>

<div class="field" style="margin-top:12px">
    <label>Permissions</label>
    <div class="permission-panel" data-permission-panel>
        <div class="permission-toolbar">
            <strong data-permission-count>{{ count($selected) }} selected</strong>
            <div class="actions">
                <button type="button" class="btn small" data-permission-select-all>Select all</button>
                <button type="button" class="btn small" data-permission-clear>Clear</button>
            </div>
        </div>
        <div class="permission-groups">
            @foreach($permissionGroups as $group => $items)
                <div class="permission-group">
                    <div class="permission-group-title">{{ $group }}</div>
                    <div class="permission-grid">
                        @foreach($items as $key => $description)
                            <label class="permission-card">
                                <input class="role-permission" type="checkbox" name="permissions[]" value="{{ $key }}" @checked(in_array($key, $selected))>
                                <span><b>{{ ucwords(str_replace('_', ' ', $key)) }}</b><span>{{ $description }}</span></span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="field" style="margin-top:12px">
    <label class="req">Status</label>
    <select name="status" required>
        <option value="active" @selected(old('status', $role->status ?? 'active') === 'active')>Active</option>
        <option value="inactive" @selected(old('status', $role->status ?? '') === 'inactive')>Inactive</option>
    </select>
    @if($role && $role->activeUsersCount() > 0)
        <span class="small muted">Currently assigned to {{ $role->activeUsersCount() }} active user(s) — can't be deactivated until they're reassigned.</span>
    @endif
</div>
