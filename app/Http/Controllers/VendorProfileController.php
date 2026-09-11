<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class VendorProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'business_name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'market_area' => 'required|string',
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed',
        ]);

        // Update basic info
        $user->name = $request->name;
        $user->email = $request->email;
        $user->business_name = $request->business_name;

        // Format phone number (remove +254 if present, add it back)
        $phone = preg_replace('/^\+254/', '', $request->phone);
        $phone = preg_replace('/^0/', '', $phone);
        $user->phone = '+254' . $phone;
        $user->market_area = $request->market_area;

        // Update password if provided
        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return redirect()->back()->with('profile_error', '❌ Current password is incorrect.');
            }
            $user->password = Hash::make($request->new_password);
        }

        $user->save();

        return redirect()->back()->with('profile_success', '✅ Profile updated successfully!');
    }
}