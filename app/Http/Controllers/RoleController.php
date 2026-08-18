<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\RolePermission;
use App\Models\TransactionLog;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    // Grouped for the permission-picker UI: group label => [key => description].
    // Each key must match a role_permission.role_permission_name row (seeded).
    public const PERMISSION_GROUPS = [
        'Inventory & Operations' => [
            'items' => 'Item master records',
            'receiving' => 'Receiving transactions',
            'issuance' => 'Medicine and supply issuance',
            'batches' => 'Batch records',
            'equipment' => 'Equipment registry',
            'disposal' => 'Disposal records',
            'suppliers' => 'Supplier records',
        ],
        'Oversight & Reporting' => [
            'approvals' => 'Supervisor approval queue',
            'reports' => 'Generate and view reports',
            'transaction_log' => 'Audit and transaction history',
        ],
        'Administration' => [
            'users' => 'User account management',
            'roles' => 'Role and permission management',
            'system_settings' => 'System-wide settings',
            'locations' => 'Receiving location list',
        ],
    ];

    public static function permissionKeys(): array
    {
        return array_merge(...array_values(array_map('array_keys', self::PERMISSION_GROUPS)));
    }

    public function index()
    {
        $roles = Role::withCount('users')->with('permissions')->orderBy('role_name')->get();

        return view('roles.index', ['roles' => $roles, 'permissionGroups' => self::PERMISSION_GROUPS]);
    }

    public function create()
    {
        return view('roles.create', ['permissionGroups' => self::PERMISSION_GROUPS]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'role_name' => ['required', 'string', 'max:50', 'unique:role,role_name'],
            'role_description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'in:'.implode(',', self::permissionKeys())],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $role = Role::create([
            'role_name' => $data['role_name'],
            'role_description' => $data['role_description'] ?? null,
            'status' => $data['status'],
        ]);

        $this->syncPermissions($role, $data['permissions'] ?? []);

        TransactionLog::note(auth()->user(), "Created role {$role->role_name}", $role->role_name);

        return redirect()->route('roles.index')->with('status', 'Role created.');
    }

    public function edit(Role $role)
    {
        $role->load('permissions');

        return view('roles.edit', ['role' => $role, 'permissionGroups' => self::PERMISSION_GROUPS]);
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'role_name' => ['required', 'string', 'max:50', 'unique:role,role_name,'.$role->role_id.',role_id'],
            'role_description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'in:'.implode(',', self::permissionKeys())],
            'status' => ['required', 'in:active,inactive'],
        ]);

        if ($data['status'] === 'inactive' && $role->activeUsersCount() > 0) {
            return back()->withErrors([
                'status' => "This role cannot be deactivated while it is assigned to {$role->activeUsersCount()} active user(s). Reassign or deactivate those users first.",
            ])->withInput();
        }

        $role->update([
            'role_name' => $data['role_name'],
            'role_description' => $data['role_description'] ?? null,
            'status' => $data['status'],
        ]);

        $this->syncPermissions($role, $data['permissions'] ?? []);

        TransactionLog::note(auth()->user(), "Updated role {$role->role_name}", $role->role_name);

        return redirect()->route('roles.index')->with('status', 'Role updated.');
    }

    protected function syncPermissions(Role $role, array $keys): void
    {
        $ids = RolePermission::whereIn('role_permission_name', $keys)->pluck('role_permission_id', 'role_permission_name');
        $role->permissions()->sync($ids->values()->all());
    }
}
