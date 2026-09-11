<?php

namespace App\Http\Controllers;

use App\Models\WasteListing;
use App\Models\WasteType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class WasteListingController extends Controller
{
    public function store(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'category_id' => 'required|exists:waste_categories,id',
                'waste_type_id' => 'required|exists:waste_types,id',
                'quantity_kg' => 'required|numeric|min:1|max:10000',
                'unit' => 'nullable|string',
                'market_area' => 'required|string',
                'availability_window' => 'required|in:today,tomorrow,this_week',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'notes' => 'nullable|string|max:500',
            ]);

            // Check if quantity is within reasonable limits
            if ($request->quantity_kg < 1) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', '❌ Quantity must be at least 1 kg/piece.');
            }

            if ($request->quantity_kg > 10000) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', '❌ Quantity cannot exceed 10,000 kg. For larger quantities, please contact support.');
            }

            // Get waste type
            $wasteType = WasteType::find($request->waste_type_id);

            if (!$wasteType) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', '❌ Invalid waste type selected. Please try again.');
            }

            // Check if user has completed their profile
            $user = Auth::user();
            if (!$user->market_area) {
                return redirect()->route('vendor.setup')
                    ->with('error', '❌ Please complete your vendor profile before listing waste.');
            }

            // Set expiration based on availability
            $expiresAt = match ($request->availability_window) {
                'today' => now()->endOfDay(),
                'tomorrow' => now()->addDay()->endOfDay(),
                'this_week' => now()->endOfWeek(),
                default => now()->addDays(7),
            };

            // Handle photo upload with error checking
            $photoPath = null;
            if ($request->hasFile('photo')) {
                try {
                    $photoPath = $request->file('photo')->store('waste_photos', 'public');
                } catch (\Exception $e) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', '❌ Failed to upload photo. Please try again with a smaller image (max 2MB).');
                }
            }

            // Create the listing
            $listing = WasteListing::create([
                'vendor_id' => Auth::id(),
                'category_id' => $request->category_id,
                'waste_type_id' => $request->waste_type_id,
                'waste_type_name' => $wasteType->name,
                'quantity_kg' => $request->quantity_kg,
                'unit' => $request->unit ?: $wasteType->unit,
                'market_area' => $request->market_area,
                'availability_window' => $request->availability_window,
                'photo_url' => $photoPath,
                'notes' => $request->notes,
                'status' => 'active',
                'expires_at' => $expiresAt,
            ]);

            if (!$listing) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', '❌ Failed to create listing. Please try again.');
            }

            return redirect()->back()
                ->with('success', '✅ Waste listed successfully! Processors will be notified.');

        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', '❌ Please fix the errors below to list your waste.');

        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Waste listing failed: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', '❌ An unexpected error occurred. Please try again later.');
        }
    }

    public function cancel($id)
    {
        try {
            $listing = WasteListing::where('id', $id)->where('vendor_id', Auth::id())->firstOrFail();

            // Check if listing is already claimed
            if ($listing->status === 'claimed') {
                return response()->json([
                    'success' => false,
                    'message' => '❌ Cannot cancel this listing because it has already been claimed by a processor.'
                ], 400);
            }

            // Delete photo if exists
            if ($listing->photo_url) {
                try {
                    Storage::disk('public')->delete($listing->photo_url);
                } catch (\Exception $e) {
                    // Log error but continue with deletion
                    \Log::warning('Failed to delete photo: ' . $e->getMessage());
                }
            }

            // Delete the listing from database
            $listing->delete();

            return response()->json([
                'success' => true,
                'message' => '✅ Listing cancelled and removed successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '❌ Failed to cancel listing. Please try again.'
            ], 500);
        }
    }
    public function show($id)
    {
        $listing = WasteListing::with(['category', 'wasteType'])
            ->where('vendor_id', Auth::id())
            ->findOrFail($id);

        return response()->json([
            'id' => $listing->id,
            'waste_type_name' => $listing->wasteType->name ?? $listing->waste_type_name,
            'quantity_kg' => $listing->quantity_kg,
            'unit' => $listing->unit,
            'market_area' => $listing->market_area,
            'status' => $listing->status,
            'availability_window' => $listing->availability_window,
            'notes' => $listing->notes,
            'photo_url' => $listing->photo_url,
            'icon' => $listing->category->icon ?? '📦',
            'created_at' => $listing->created_at->format('d M Y, h:i A'),
        ]);
    }
    public function update(Request $request, $id)
    {
        try {
            $listing = WasteListing::where('id', $id)->where('vendor_id', Auth::id())->firstOrFail();

            // Only allow editing if status is active (not claimed yet)
            if ($listing->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => '❌ Cannot edit a listing that has already been claimed.'
                ], 400);
            }

            $validated = $request->validate([
                'quantity_kg' => 'required|numeric|min:1',
                'market_area' => 'required|string',
                'availability_window' => 'required|in:today,tomorrow,this_week',
                'notes' => 'nullable|string',
            ]);

            $expiresAt = match ($request->availability_window) {
                'today' => now()->endOfDay(),
                'tomorrow' => now()->addDay()->endOfDay(),
                'this_week' => now()->endOfWeek(),
                default => now()->addDays(7),
            };

            $listing->update([
                'quantity_kg' => $request->quantity_kg,
                'market_area' => $request->market_area,
                'availability_window' => $request->availability_window,
                'notes' => $request->notes,
                'expires_at' => $expiresAt,
            ]);

            return response()->json([
                'success' => true,
                'message' => '✅ Listing updated successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '❌ Failed to update listing. Please try again.'
            ], 500);
        }
    }
}