<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\TransactionLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('role')->orderBy('fullname')->get();
        $roles = Role::where('status', 'active')->orderBy('role_name')->get();

        return view('users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::where('status', 'active')->orderBy('role_name')->get();

        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fullname' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:100', 'unique:user,email'],
            'role_id' => ['required', 'exists:role,role_id'],
            'password' => ['required', 'string', 'min:6'],
            'is_active' => ['required', 'boolean'],
        ]);

        $user = User::create([
            'fullname' => $data['fullname'],
            'email' => $data['email'],
            'role_id' => $data['role_id'],
            'is_active' => $data['is_active'],
            'password' => Hash::make($data['password']),
        ]);

        TransactionLog::note(auth()->user(), "Created user account for {$user->fullname} ({$user->role->role_name})", $user->email);

        return redirect()->route('users.index')->with('status', 'User created. Share the temporary password with them securely.');
    }

    public function edit(User $user)
    {
        $roles = Role::where('status', 'active')->orWhere('role_id', $user->role_id)->orderBy('role_name')->get();

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'fullname' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:100', 'unique:user,email,'.$user->user_id.',user_id'],
            'role_id' => ['required', 'exists:role,role_id'],
            'is_active' => ['required', 'boolean'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $user->fullname = $data['fullname'];
        $user->email = $data['email'];
        $user->role_id = $data['role_id'];
        $user->is_active = $data['is_active'];
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        TransactionLog::note(auth()->user(), "Edited user account for {$user->fullname}", $user->email);

        return redirect()->route('users.index')->with('status', 'User updated.');
    }

    public function deactivate(User $user)
    {
        $user->update(['is_active' => false]);

        TransactionLog::note(auth()->user(), "Deactivated user account for {$user->fullname}", $user->email);

        return redirect()->route('users.index')->with('status', 'User deactivated.');
    }

    public function reactivate(User $user)
    {
        $user->update(['is_active' => true]);

        TransactionLog::note(auth()->user(), "Reactivated user account for {$user->fullname}", $user->email);

        return redirect()->route('users.index')->with('status', 'User reactivated.');
    }
}
