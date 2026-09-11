<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WasteCategory;
use App\Models\WasteType;
use App\Models\WasteListing;
use App\Models\WasteMatch;
use App\Models\QualityReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProcessorController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();

        // Get available waste within service radius
        $serviceRadius = $user->service_radius ?? 10;
        $userLocation = ['lat' => $user->latitude ?? -1.2864, 'lng' => $user->longitude ?? 36.8172];

        $availableWaste = WasteListing::with(['category', 'wasteType', 'vendor'])
            ->where('status', 'active')
            ->whereDoesntHave('matches', function ($q) {
                $q->where('processor_id', Auth::id());
            })
            ->get()
            ->map(function ($listing) use ($userLocation) {
                $distance = $this->calculateDistance(
                    $userLocation['lat'],
                    $userLocation['lng'],
                    $listing->latitude ?? -1.2864,
                    $listing->longitude ?? 36.8172
                );
                $listing->distance = $distance;
                return $listing;
            })
            ->filter(fn($listing) => $listing->distance <= ($listing->vendor->service_radius ?? 10))
            ->take(5);

        $availableCount = WasteListing::where('status', 'active')->count();

        // Get active claims
        $activeClaims = WasteMatch::with(['wasteListing.category', 'wasteListing.wasteType', 'wasteListing.vendor'])
            ->where('processor_id', $user->id)
            ->whereIn('status', ['pending', 'accepted', 'in_transit', 'collected'])
            ->orderBy('created_at', 'desc')
            ->get();

        $activeClaimsCount = $activeClaims->count();

        // Get completed matches stats
        $completedMatches = WasteMatch::where('processor_id', $user->id)
            ->where('status', 'completed')
            ->get();

        $totalCollected = $completedMatches->sum('actual_quantity_kg');
        $completedCount = $completedMatches->count();

        return view('processor.dashboard', compact(
            'availableWaste',
            'availableCount',
            'activeClaims',
            'activeClaimsCount',
            'totalCollected',
            'completedCount'
        ));
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return round($earthRadius * $c, 1);
    }

    public function getListingDetails($id)
    {
        $listing = WasteListing::with(['category', 'wasteType', 'vendor'])
            ->findOrFail($id);

        $user = Auth::user();
        $distance = $this->calculateDistance(
            $user->latitude ?? -1.2864,
            $user->longitude ?? 36.8172,
            $listing->latitude ?? -1.2864,
            $listing->longitude ?? 36.8172
        );

        return response()->json([
            'waste_type' => $listing->wasteType->name ?? $listing->waste_type_name,
            'quantity' => $listing->quantity_kg,
            'unit' => $listing->unit ?? 'kg',
            'market_area' => $listing->market_area,
            'distance' => $distance,
        ]);
    }

    public function claimWaste(Request $request, $id)
    {
        try {
            $listing = WasteListing::where('id', $id)->where('status', 'active')->firstOrFail();

            $existingMatch = WasteMatch::where('waste_listing_id', $id)
                ->where('processor_id', Auth::id())
                ->first();

            if ($existingMatch) {
                return response()->json(['success' => false, 'message' => 'You already claimed this waste'], 400);
            }

            $match = WasteMatch::create([
                'waste_listing_id' => $id,
                'processor_id' => Auth::id(),
                'status' => 'pending',
                'claimed_at' => now(),
            ]);

            $listing->update(['status' => 'claimed']);

            return response()->json(['success' => true, 'message' => 'Waste claimed successfully! Vendor has been notified.']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to claim waste'], 500);
        }
    }

    public function updateMatchStatus(Request $request, $id)
    {
        try {
            $match = WasteMatch::where('id', $id)->where('processor_id', Auth::id())->firstOrFail();
            $newStatus = $request->status;

            $allowedTransitions = [
                'pending' => ['accepted'],
                'accepted' => ['in_transit'],
                'in_transit' => ['collected'],
                'collected' => ['completed'],
            ];

            if (!in_array($newStatus, $allowedTransitions[$match->status] ?? [])) {
                return response()->json(['success' => false, 'message' => 'Invalid status transition'], 400);
            }

            $match->status = $newStatus;

            if ($newStatus === 'collected') {
                $match->collected_at = now();
                if ($request->actual_quantity) {
                    $match->actual_quantity_kg = $request->actual_quantity;
                } else {
                    $match->actual_quantity_kg = $match->wasteListing->quantity_kg;
                }
            }
            if ($newStatus === 'completed') {
                $match->completed_at = now();
            }

            $match->save();

            // Update waste listing status
            if ($newStatus === 'accepted') {
                $match->wasteListing->update(['status' => 'claimed']);
            }
            if ($newStatus === 'collected') {
                $match->wasteListing->update(['status' => 'collected']);
            }
            if ($newStatus === 'completed') {
                $match->wasteListing->update(['status' => 'completed']);
            }

            $message = match ($newStatus) {
                'accepted' => 'Claim confirmed! Contact vendor to arrange pickup.',
                'in_transit' => 'Pickup started! You are on the way to collect waste.',
                'collected' => 'Waste collected! Processing now.',
                'completed' => 'Processing complete! Thank you for recycling.',
                default => 'Status updated',
            };

            return response()->json(['success' => true, 'message' => $message]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error updating status'], 500);
        }
    }
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'business_name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'service_radius' => 'nullable|integer',
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->business_name = $request->business_name;
        $user->phone = '+254' . ltrim($request->phone, '0');
        $user->service_radius = $request->service_radius;

        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return redirect()->back()->with('profile_error', 'Current password is incorrect.');
            }
            $user->password = Hash::make($request->new_password);
        }

        $user->save();

        return redirect()->back()->with('profile_success', 'Profile updated successfully!');
    }
    public function findWaste()
    {
        $user = Auth::user();
        $serviceRadius = $user->service_radius ?? 10;
        $userLocation = ['lat' => $user->latitude ?? -1.2864, 'lng' => $user->longitude ?? 36.8172];

        $availableWaste = WasteListing::with(['category', 'wasteType', 'vendor'])
            ->where('status', 'active')
            ->whereDoesntHave('matches', function ($q) {
                $q->where('processor_id', Auth::id());
            })
            ->get()
            ->map(function ($listing) use ($userLocation) {
                $distance = $this->calculateDistance(
                    $userLocation['lat'],
                    $userLocation['lng'],
                    $listing->latitude ?? -1.2864,
                    $listing->longitude ?? 36.8172
                );
                $listing->distance = $distance;
                return $listing;
            })
            ->filter(fn($listing) => $listing->distance <= $serviceRadius)
            ->sortBy('distance');

        $categories = WasteCategory::where('is_active', true)->orderBy('display_order')->get();

        return view('processor.find-waste', compact('availableWaste', 'categories'));
    }

    public function myClaims()
    {
        $claims = WasteMatch::with(['wasteListing.category', 'wasteListing.wasteType', 'wasteListing.vendor'])
            ->where('processor_id', Auth::id())
            ->whereIn('status', ['pending', 'accepted', 'in_transit', 'collected'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $pendingCount = $claims->where('status', 'pending')->count();
        $acceptedCount = $claims->where('status', 'accepted')->count();
        $inTransitCount = $claims->where('status', 'in_transit')->count();
        $collectedCount = $claims->where('status', 'collected')->count();

        return view('processor.my-claims', compact('claims', 'pendingCount', 'acceptedCount', 'inTransitCount', 'collectedCount'));
    }

    public function history()
    {
        $completedMatches = WasteMatch::with(['wasteListing.category', 'wasteListing.wasteType', 'wasteListing.vendor'])
            ->where('processor_id', Auth::id())
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->paginate(10);

        $totalCollected = $completedMatches->sum('actual_quantity_kg');
        $totalMatches = $completedMatches->count();

        return view('processor.history', compact('completedMatches', 'totalCollected', 'totalMatches'));
    }
    public function getListingsApi()
    {
        $user = Auth::user();
        $serviceRadius = $user->service_radius ?? 10;
        $userLocation = ['lat' => $user->latitude ?? -1.2864, 'lng' => $user->longitude ?? 36.8172];

        $listings = WasteListing::with(['category', 'wasteType', 'vendor'])
            ->where('status', 'active')
            ->whereDoesntHave('matches', function ($q) {
                $q->where('processor_id', Auth::id());
            })
            ->latest()
            ->get()
            ->map(function ($listing) use ($userLocation) {
                $distance = $this->calculateDistance(
                    $userLocation['lat'],
                    $userLocation['lng'],
                    $listing->latitude ?? -1.2864,
                    $listing->longitude ?? 36.8172
                );
                return [
                    'id' => $listing->id,
                    'waste_type_name' => $listing->wasteType->name ?? $listing->waste_type_name,
                    'quantity' => $listing->quantity_kg,
                    'unit' => $listing->unit ?? 'kg',
                    'market_area' => $listing->market_area,
                    'availability_window' => $listing->availability_window,
                    'notes' => $listing->notes,
                    'photo_url' => $listing->photo_url,
                    'icon' => $listing->category->icon ?? '♻️',
                    'category_id' => $listing->category_id,
                    'distance' => $distance,
                    'listed_date' => $listing->created_at->diffForHumans(),
                    'vendor_name' => $listing->vendor->business_name ?? $listing->vendor->name,
                    'vendor_phone' => preg_replace('/^254/', '', $listing->vendor->phone ?? ''),
                ];
            })
            ->filter(fn($listing) => $listing['distance'] <= $serviceRadius)
            ->values();

        return response()->json($listings);
    }
    public function downloadHistory(Request $request)
    {
        $user = Auth::id();
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = WasteMatch::where('processor_id', $user)
            ->where('status', 'completed')
            ->with(['wasteListing.wasteType', 'wasteListing.vendor', 'review']);

        if ($startDate && $endDate) {
            $query->whereBetween('completed_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()]);
        }

        $matches = $query->orderBy('completed_at', 'desc')->get();

        $filename = 'collection_history_' . date('Y-m-d') . '.csv';
        $handle = fopen('php://temp', 'w+');

        // Headers
        fputcsv($handle, ['Date', 'Waste Type', 'Quantity (kg)', 'Market Area', 'Vendor', 'Vendor Phone', 'Rating', 'Review']);

        foreach ($matches as $match) {
            fputcsv($handle, [
                $match->completed_at?->format('Y-m-d'),
                $match->wasteListing->wasteType->name ?? $match->wasteListing->waste_type_name,
                $match->actual_quantity_kg ?? $match->wasteListing->quantity_kg,
                $match->wasteListing->market_area,
                $match->wasteListing->vendor->business_name ?? $match->wasteListing->vendor->name,
                $match->wasteListing->vendor->phone,
                $match->review->rating ?? '',
                $match->review->comment ?? '',
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }
}