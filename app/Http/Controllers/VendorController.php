<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WasteCategory;
use App\Models\WasteType;
use App\Models\WasteListing;
use App\Models\WasteMatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\QualityReview;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;



class VendorController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();

        // Redirect to setup if profile is incomplete
        if (!$user->market_area) {
            return redirect()->route('vendor.setup')->with('info', 'Please complete your profile first.');
        }

        // Get all categories
        $categories = WasteCategory::where('is_active', true)->orderBy('display_order')->get();

        // Get all waste types grouped by category
        $wasteTypes = WasteType::with('category')->get()->groupBy('category_id');

        // Get vendor's waste listings
        $listings = WasteListing::where('vendor_id', $user->id)->latest()->get();

        // Calculate counts
        $activeListings = $listings->whereIn('status', ['active', 'claimed']);
        $activeListingsCount = $activeListings->count();

        // Get matches
        $inProgressMatches = WasteMatch::whereHas('wasteListing', function ($q) use ($user) {
            $q->where('vendor_id', $user->id);
        })->whereIn('status', ['pending', 'accepted', 'in_transit', 'collected'])->get();

        $completedMatches = WasteMatch::whereHas('wasteListing', function ($q) use ($user) {
            $q->where('vendor_id', $user->id);
        })->where('status', 'completed')->get();

        $completedCount = $completedMatches->count();

        // Calculate totals
        $totalListed = $listings->sum('quantity_kg');
        $totalCollected = $completedMatches->sum('actual_quantity_kg') ?: 0;
        $co2Saved = round($totalCollected * 0.5, 1);

        // Category breakdown for analytics
        $categoryBreakdown = [];
        foreach ($categories as $cat) {
            $total = $listings->where('category_id', $cat->id)->sum('quantity_kg');
            if ($total > 0) {
                $categoryBreakdown[] = [
                    'name' => $cat->name,
                    'icon' => $cat->icon,
                    'total' => $total,
                    'percentage' => $totalListed > 0 ? round(($total / $totalListed) * 100, 1) : 0,
                ];
            }
        }

        return view('vendor.dashboard', compact(
            'categories',
            'wasteTypes',
            'activeListings',
            'inProgressMatches',
            'completedMatches',
            'totalListed',
            'totalCollected',
            'co2Saved',
            'activeListingsCount',
            'completedCount',
            'categoryBreakdown'
        ));
    }
    public function confirmClaim($id)
    {
        try {
            $match = WasteMatch::with(['wasteListing', 'processor'])->findOrFail($id);

            // Verify ownership
            if ($match->wasteListing->vendor_id !== Auth::id()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            if ($match->status !== 'pending') {
                return response()->json(['success' => false, 'message' => 'Claim already processed'], 400);
            }

            $match->update(['status' => 'accepted']);
            $match->wasteListing->update(['status' => 'claimed']);

            return response()->json([
                'success' => true,
                'message' => '✅ Claim confirmed! Processor has been notified.'
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => '❌ Error confirming claim'], 500);
        }
    }

    public function rejectClaim($id)
    {
        try {
            $match = WasteMatch::with(['wasteListing', 'processor'])->findOrFail($id);

            if ($match->wasteListing->vendor_id !== Auth::id()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            if ($match->status !== 'pending') {
                return response()->json(['success' => false, 'message' => 'Claim already processed'], 400);
            }

            $match->update(['status' => 'cancelled']);
            $match->wasteListing->update(['status' => 'active']);

            return response()->json([
                'success' => true,
                'message' => '✅ Claim rejected. Waste listing is available again.'
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => '❌ Error rejecting claim'], 500);
        }
    }
    public function rateMatch(Request $request, $id)
    {
        try {
            $match = WasteMatch::with(['wasteListing', 'processor'])->findOrFail($id);

            // Verify ownership
            if ($match->wasteListing->vendor_id !== Auth::id()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            // Check if match is completed
            if ($match->status !== 'completed') {
                return response()->json(['success' => false, 'message' => 'Can only rate completed matches'], 400);
            }

            // Check if already rated
            $existingReview = QualityReview::where('match_id', $id)
                ->where('reviewer_id', Auth::id())
                ->first();

            if ($existingReview) {
                return response()->json(['success' => false, 'message' => 'You have already rated this processor'], 400);
            }

            $request->validate([
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:500',
            ]);

            // Create review
            QualityReview::create([
                'match_id' => $match->id,
                'reviewer_id' => Auth::id(),
                'reviewee_id' => $match->processor_id,
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);

            // Update processor's average rating
            $avgRating = QualityReview::where('reviewee_id', $match->processor_id)->avg('rating');
            User::where('id', $match->processor_id)->update(['rating_avg' => round($avgRating, 2)]);

            return response()->json([
                'success' => true,
                'message' => '✅ Thank you for rating! Your feedback helps others.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Rating error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error submitting rating. Please try again.'], 500);
        }
    }
    public function setup()
    {
        return view('vendor.setup', ['user' => Auth::user()]);
    }

    public function storeSetup(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'nullable|string|max:255',
            'market_area' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
        ]);

        Auth::user()->update($validated);

        return redirect()->route('vendor.dashboard')->with('success', 'Profile saved successfully!');
    }
    public function matches()
    {
        $user = Auth::user();

        $matches = WasteMatch::with(['wasteListing.category', 'wasteListing.wasteType', 'processor'])
            ->whereHas('wasteListing', function ($q) use ($user) {
                $q->where('vendor_id', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $pendingCount = WasteMatch::whereHas('wasteListing', fn($q) => $q->where('vendor_id', $user->id))->where('status', 'pending')->count();
        $acceptedCount = WasteMatch::whereHas('wasteListing', fn($q) => $q->where('vendor_id', $user->id))->where('status', 'accepted')->count();
        $inTransitCount = WasteMatch::whereHas('wasteListing', fn($q) => $q->where('vendor_id', $user->id))->where('status', 'in_transit')->count();
        $completedCount = WasteMatch::whereHas('wasteListing', fn($q) => $q->where('vendor_id', $user->id))->where('status', 'completed')->count();

        return view('vendor.matches', compact('matches', 'pendingCount', 'acceptedCount', 'inTransitCount', 'completedCount'));
    }

    public function getMatchDetails($id)
    {
        $match = WasteMatch::with(['wasteListing.category', 'wasteListing.wasteType', 'processor'])
            ->whereHas('wasteListing', fn($q) => $q->where('vendor_id', Auth::id()))
            ->findOrFail($id);

        return response()->json([
            'waste_type' => $match->wasteListing->wasteType->name ?? $match->wasteListing->waste_type_name,
            'waste_icon' => $match->wasteListing->category->icon ?? '♻️',
            'quantity' => $match->wasteListing->quantity_kg,
            'unit' => $match->wasteListing->unit ?? 'kg',
            'market_area' => $match->wasteListing->market_area,
            'processor_name' => $match->processor->business_name ?? $match->processor->name,
            'processor_phone' => $match->processor->phone,
            'status' => $match->status,
            'claimed_at' => $match->claimed_at?->format('d M Y, h:i A'),
            'collected_at' => $match->collected_at?->format('d M Y, h:i A'),
            'completed_at' => $match->completed_at?->format('d M Y, h:i A'),
            'actual_quantity' => $match->actual_quantity_kg,
        ]);
    }
    public function myListings()
    {
        $user = Auth::user();

        $listings = WasteListing::with(['category', 'wasteType', 'activeMatch.processor'])
            ->where('vendor_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $totalListings = WasteListing::where('vendor_id', $user->id)->count();
        $activeCount = WasteListing::where('vendor_id', $user->id)->where('status', 'active')->count();
        $claimedCount = WasteListing::where('vendor_id', $user->id)->where('status', 'claimed')->count();
        $completedCount = WasteListing::where('vendor_id', $user->id)->where('status', 'completed')->count();

        $categories = WasteCategory::where('is_active', true)->orderBy('display_order')->get();
        $wasteTypes = WasteType::with('category')->get()->groupBy('category_id');

        return view('vendor.my-listings', compact(
            'listings',
            'totalListings',
            'activeCount',
            'claimedCount',
            'completedCount',
            'categories',
            'wasteTypes'
        ));
    }
    public function analytics()
    {
        return view('vendor.analytics');
    }

    public function getAnalyticsData(Request $request)
    {
        $user = Auth::id();
        $range = $request->get('range', 'week');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Date range calculation
        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
        } else {
            switch ($range) {
                case 'week':
                    $start = Carbon::now()->startOfWeek();
                    $end = Carbon::now()->endOfWeek();
                    break;
                case 'month':
                    $start = Carbon::now()->startOfMonth();
                    $end = Carbon::now()->endOfMonth();
                    break;
                case 'year':
                    $start = Carbon::now()->startOfYear();
                    $end = Carbon::now()->endOfYear();
                    break;
                default:
                    $start = Carbon::create(2020, 1, 1);
                    $end = Carbon::now();
            }
        }

        // Previous period for trends
        $duration = $start->diffInDays($end);
        $prevStart = (clone $start)->subDays($duration);
        $prevEnd = (clone $start)->subDay();

        // Total waste listed
        $totalListed = WasteListing::where('vendor_id', $user)->whereBetween('created_at', [$start, $end])->sum('quantity_kg');
        $prevListed = WasteListing::where('vendor_id', $user)->whereBetween('created_at', [$prevStart, $prevEnd])->sum('quantity_kg');
        $listedTrend = $prevListed > 0 ? round(($totalListed - $prevListed) / $prevListed * 100, 1) : ($totalListed > 0 ? 100 : 0);

        // Total collected
        $totalCollected = WasteMatch::whereHas('wasteListing', fn($q) => $q->where('vendor_id', $user))
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$start, $end])
            ->sum('actual_quantity_kg');
        $prevCollected = WasteMatch::whereHas('wasteListing', fn($q) => $q->where('vendor_id', $user))
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$prevStart, $prevEnd])
            ->sum('actual_quantity_kg');
        $collectedTrend = $prevCollected > 0 ? round(($totalCollected - $prevCollected) / $prevCollected * 100, 1) : ($totalCollected > 0 ? 100 : 0);

        // CO2 saved (1kg waste = 0.5kg CO2)
        $co2Saved = round($totalCollected * 0.5, 1);

        // Total matches
        $totalMatches = WasteMatch::whereHas('wasteListing', fn($q) => $q->where('vendor_id', $user))
            ->whereBetween('created_at', [$start, $end])
            ->count();
        $prevMatches = WasteMatch::whereHas('wasteListing', fn($q) => $q->where('vendor_id', $user))
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->count();
        $matchesTrend = $prevMatches > 0 ? round(($totalMatches - $prevMatches) / $prevMatches * 100, 1) : ($totalMatches > 0 ? 100 : 0);

        // Category breakdown
        $categoryBreakdown = WasteCategory::with([
            'wasteListings' => function ($q) use ($user, $start, $end) {
                $q->where('vendor_id', $user)->whereBetween('created_at', [$start, $end]);
            }
        ])->get()->map(fn($cat) => [
                'name' => $cat->name,
                'icon' => $cat->icon,
                'total' => $cat->wasteListings->sum('quantity_kg'),
            ])->filter(fn($cat) => $cat['total'] > 0)->values();

        // Monthly trends (last 6 months)
        $monthlyTrend = collect();
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
            $monthEnd = Carbon::now()->subMonths($i)->endOfMonth();
            $collected = WasteMatch::whereHas('wasteListing', fn($q) => $q->where('vendor_id', $user))
                ->where('status', 'completed')
                ->whereBetween('completed_at', [$monthStart, $monthEnd])
                ->sum('actual_quantity_kg');
            $monthlyTrend->push(['month' => $monthStart->format('M Y'), 'collected' => round($collected, 1)]);
        }

        // Status distribution
        $statusDistribution = collect(['pending', 'accepted', 'in_transit', 'collected', 'completed', 'cancelled'])
            ->map(fn($status) => [
                'name' => ucfirst(str_replace('_', ' ', $status)),
                'count' => WasteMatch::whereHas('wasteListing', fn($q) => $q->where('vendor_id', $user))
                    ->where('status', $status)
                    ->whereBetween('created_at', [$start, $end])
                    ->count(),
            ])->filter(fn($s) => $s['count'] > 0)->values();

        // Top processors
        $topProcessors = WasteMatch::whereHas('wasteListing', fn($q) => $q->where('vendor_id', $user))
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$start, $end])
            ->select('processor_id', DB::raw('SUM(actual_quantity_kg) as total_collected'), DB::raw('COUNT(*) as matches'))
            ->with('processor')
            ->groupBy('processor_id')
            ->orderBy('total_collected', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($match) => [
                'name' => $match->processor->business_name ?? $match->processor->name,
                'total_collected' => round($match->total_collected, 1),
                'matches' => $match->matches,
            ]);

        // Recent activity
        $recentActivity = WasteMatch::whereHas('wasteListing', fn($q) => $q->where('vendor_id', $user))
            ->with(['wasteListing.wasteType', 'processor'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($match) => [
                'date' => $match->created_at->format('d M Y'),
                'waste_type' => $match->wasteListing->wasteType->name ?? $match->wasteListing->waste_type_name,
                'quantity' => $match->wasteListing->quantity_kg,
                'unit' => $match->wasteListing->unit ?? 'kg',
                'processor' => $match->processor->business_name ?? $match->processor->name,
                'status' => ucfirst($match->status),
                'status_color' => match ($match->status) {
                    'pending' => 'bg-amber-100 text-amber-700',
                    'accepted' => 'bg-blue-100 text-blue-700',
                    'in_transit' => 'bg-purple-100 text-purple-700',
                    'collected' => 'bg-indigo-100 text-indigo-700',
                    'completed' => 'bg-green-100 text-green-700',
                    default => 'bg-gray-100 text-gray-600',
                },
            ]);

        return response()->json([
            'total_listed' => round($totalListed, 1),
            'total_collected' => round($totalCollected, 1),
            'co2_saved' => $co2Saved,
            'total_matches' => $totalMatches,
            'listed_trend' => $listedTrend,
            'collected_trend' => $collectedTrend,
            'matches_trend' => $matchesTrend,
            'category_breakdown' => $categoryBreakdown,
            'monthly_trend' => $monthlyTrend,
            'status_distribution' => $statusDistribution,
            'top_processors' => $topProcessors,
            'recent_activity' => $recentActivity,
        ]);
    }

    public function downloadAnalyticsReport(Request $request)
    {
        $user = Auth::id();
        $range = $request->get('range', 'week');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Same data fetching as above, but for CSV export
        // Build CSV and download

        $headers = ['Date', 'Waste Type', 'Quantity (kg)', 'Processor', 'Status'];
        $rows = [];

        $matches = WasteMatch::whereHas('wasteListing', fn($q) => $q->where('vendor_id', $user))
            ->with(['wasteListing.wasteType', 'processor'])
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($matches as $match) {
            $rows[] = [
                $match->created_at->format('Y-m-d'),
                $match->wasteListing->wasteType->name ?? $match->wasteListing->waste_type_name,
                $match->wasteListing->quantity_kg,
                $match->processor->business_name ?? $match->processor->name,
                $match->status,
            ];
        }

        $filename = 'agrismart_report_' . date('Y-m-d') . '.csv';
        $handle = fopen('php://temp', 'w+');
        fputcsv($handle, $headers);
        foreach ($rows as $row) {
            fputcsv($handle, $row);
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