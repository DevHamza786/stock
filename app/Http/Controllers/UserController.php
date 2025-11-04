<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('permissions')->paginate(15);
        return view('user-management.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('user-management.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:admin,user'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return redirect()->route('user-management.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load('permissions');
        return view('user-management.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $modules = $this->getAvailableModules();
        $user->load('permissions');
        return view('user-management.edit', compact('user', 'modules'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:admin,user'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        
        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('user-management.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return redirect()->route('user-management.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('user-management.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Show the form for editing user permissions
     */
    public function editPermissions(User $user)
    {
        $modules = $this->getAvailableModules();
        $user->load('permissions');
        return view('user-management.permissions', compact('user', 'modules'));
    }

    /**
     * Update user permissions
     */
    public function updatePermissions(Request $request, User $user)
    {
        // Only allow editing permissions for non-admin users
        if ($user->isAdmin()) {
            return redirect()->route('user-management.users.index')
                ->with('error', 'Cannot modify permissions for admin users.');
        }

        $modules = $this->getAvailableModules();
        
        foreach ($modules as $module) {
            $canEdit = $request->has("permissions.{$module}.edit");
            $canDelete = $request->has("permissions.{$module}.delete");

            UserPermission::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'module' => $module,
                ],
                [
                    'can_edit' => $canEdit,
                    'can_delete' => $canDelete,
                ]
            );
        }

        return redirect()->route('user-management.users.index')
            ->with('success', 'User permissions updated successfully.');
    }

    /**
     * Get available modules for permissions
     */
    private function getAvailableModules(): array
    {
        return [
            'products',
            'mine-vendors',
            'condition-statuses',
            'stock-additions',
            'stock-issued',
            'daily-production',
            'gate-pass',
            'machines',
            'operators',
        ];
    }
}
