<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WaitlistController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string|min:10|max:15',
            'role' => 'required|in:have,need',
        ]);

        DB::table('waitlist')->insert([
            'phone' => $validated['phone'],
            'role' => $validated['role'],
            'ip_address' => $request->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'You\'re on the list! We\'ll notify you when we launch.');
    }
}