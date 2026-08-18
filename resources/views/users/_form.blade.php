<div class="form-grid">
    <div class="field span-2">
        <label class="req">Full name</label>
        <input type="text" name="fullname" value="{{ old('fullname', $user->fullname ?? '') }}" required>
    </div>
    <div class="field">
        <label class="req">Email</label>
        <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required>
    </div>
    <div class="field">
        <label class="req">Role</label>
        <select name="role_id" required>
            @foreach($roles as $r)
                <option value="{{ $r->role_id }}" @selected(old('role_id', $user->role_id ?? '') == $r->role_id)>{{ $r->role_name }}</option>
            @endforeach
        </select>
    </div>
    <div class="field span-2">
        <label class="{{ $user ? '' : 'req' }}">{{ $user ? 'Password (leave blank to keep current)' : 'Temporary password' }}</label>
        <input type="password" name="password" placeholder="{{ $user ? 'Enter a new password to change it' : 'Enter temporary password' }}" {{ $user ? '' : 'required' }}>
    </div>
    <div class="field">
        <label class="req">Status</label>
        <select name="is_active" required>
            <option value="1" @selected(old('is_active', $user ? (int) $user->is_active : 1) == 1)>Active</option>
            <option value="0" @selected(old('is_active', $user ? (int) $user->is_active : 1) == 0)>Inactive</option>
        </select>
    </div>
</div>
