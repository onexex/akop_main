<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('auth/UserManagement', [
            'filters' => $request->only(['search']),
            'users'   => User::query()
                ->when($request->input('search'), function ($query, $search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                ->latest()
                ->paginate(10)
                ->withQueryString()
                ->through(fn($user) => [
                    'id'                 => $user->id,
                    'name'               => $user->name,
                    'email'              => $user->email,
                    'is_verified'        => $user->email_verified_at !== null,
                    'two_factor_enabled' => $user->two_factor_confirmed_at !== null,
                    'created_at'         => $user->created_at->format('M d, Y'),
                ]),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|lowercase|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            // Password is optional on edit
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->update(array_filter([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => $request->filled('password') ? Hash::make($validated['password']) : null,
        ]));

        return back()->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        // Prevent deleting yourself!
        if (auth()->id() === $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return back()->with('success', 'User deleted successfully.');
    }
}
