<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): View
    {
        // Get role from URL query parameter, default to 'vendor'
        $role = $request->query('role', 'vendor');

        // Validate role is allowed
        if (!in_array($role, ['vendor', 'processor', 'admin'])) {
            $role = 'vendor';
        }

        return view('auth.register', compact('role'));
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'phone' => ['required', 'string', 'max:20'],
            'role' => ['required', 'string', 'in:vendor,processor,admin'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'market_area' => ['nullable', 'string', 'max:100'],
            'service_radius' => ['nullable', 'integer'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'business_name' => $request->business_name,
            'market_area' => $request->market_area,
            'service_radius' => $request->service_radius,
            'password' => Hash::make($request->password),
            'is_verified' => false,
            'rating_avg' => 0,
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Redirect based on role
        return match ($user->role) {
            'vendor' => redirect()->route('vendor.setup'),
            'processor' => redirect()->route('processor.dashboard'),
            'admin' => redirect()->route('admin.dashboard'),
            default => redirect('/dashboard'),
        };
    }
}