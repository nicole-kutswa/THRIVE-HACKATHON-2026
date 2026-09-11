<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WasteCategory;
use App\Models\WasteListing;
use App\Models\WasteMatch;
use App\Models\QualityReview;
use App\Models\WasteType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    // ==================== DASHBOARD ====================
    public function index()
    {
        // Total users
        $totalUsers = User::count();
        $totalVendors = User::where('role', 'vendor')->count();
        $totalProcessors = User::where('role', 'processor')->count();
        $totalAdmins = User::where('role', 'admin')->count();

        // Waste listings
        $totalListings = WasteListing::count();
        $activeListings = WasteListing::where('status', 'active')->count();

        // Matches
        $totalMatches = WasteMatch::count();
        $pendingMatches = WasteMatch::where('status', 'pending')->count();
        $completedMatches = WasteMatch::where('status', 'completed')->count();

        // Waste collected
        $totalCollected = WasteMatch::where('status', 'completed')->sum('actual_quantity_kg');

        // Platform rating
        $avgRating = QualityReview::avg('rating') ?? 0;

        // User growth data (last 6 months)
        $userGrowth = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $count = User::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
            $userGrowth[] = [
                'month' => $month->format('M Y'),
                'count' => $count,
            ];
        }

        // Waste by category - FIXED: using correct relationship
        $categoryBreakdown = WasteCategory::with(['wasteListings'])->get()->map(function ($cat) {
            return [
                'name' => $cat->name,
                'icon' => $cat->icon,
                'total' => $cat->wasteListings->sum('quantity_kg'),
            ];
        })->filter(fn($cat) => $cat['total'] > 0)->values();

        // Recent activities
        $recentUsers = User::orderBy('created_at', 'desc')->limit(5)->get();
        $recentListings = WasteListing::with('vendor')->orderBy('created_at', 'desc')->limit(5)->get();
        $recentMatches = WasteMatch::with(['wasteListing', 'processor'])->orderBy('created_at', 'desc')->limit(5)->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalVendors',
            'totalProcessors',
            'totalAdmins',
            'totalListings',
            'activeListings',
            'totalMatches',
            'pendingMatches',
            'completedMatches',
            'totalCollected',
            'avgRating',
            'userGrowth',
            'categoryBreakdown',
            'recentUsers',
            'recentListings',
            'recentMatches'
        ));
    }

    // ==================== USERS MANAGEMENT ====================
    public function users(Request $request)
    {
        $query = User::query();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
                    ->orWhere('phone', 'like', "%{$request->search}%");
            });
        }

        if ($request->role && $request->role != 'all') {
            $query->where('role', $request->role);
        }

        if ($request->verification && $request->verification != 'all') {
            $query->where('is_verified', $request->verification == 'verified');
        }

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function getUserDetails($id)
    {
        $user = User::with([
            'wasteListings' => function ($q) {
                $q->latest()->limit(10);
            }
        ])->findOrFail($id);

        return response()->json($user);
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'required|string|max:20',
            'role' => 'required|in:vendor,processor,admin',
            'business_name' => 'nullable|string|max:255',
            'market_area' => 'nullable|string|max:100',
            'is_verified' => 'boolean',
        ]);

        if ($request->password) {
            $validated['password'] = Hash::make($request->password);
        }

        $user->update($validated);

        return response()->json(['success' => true, 'message' => 'User updated successfully']);
    }

    public function verifyUser($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_verified' => !$user->is_verified]);

        $status = $user->is_verified ? 'verified' : 'unverified';
        return response()->json(['success' => true, 'message' => "User {$status}"]);
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['success' => true, 'message' => 'User deleted successfully']);
    }

    // ==================== LISTINGS MANAGEMENT ====================
    public function listings(Request $request)
    {
        $query = WasteListing::with(['vendor', 'category', 'wasteType']);

        if ($request->search) {
            $query->where('waste_type_name', 'like', "%{$request->search}%");
        }

        if ($request->status && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        if ($request->category && $request->category != 'all') {
            $query->where('category_id', $request->category);
        }

        if ($request->vendor_id) {
            $query->where('vendor_id', $request->vendor_id);
        }

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        $listings = $query->orderBy('created_at', 'desc')->paginate(20);
        $categories = WasteCategory::where('is_active', true)->get();
        $vendors = User::where('role', 'vendor')->get();

        return view('admin.listings.index', compact('listings', 'categories', 'vendors'));
    }

    public function getListingDetails($id)
    {
        $listing = WasteListing::with(['vendor', 'category', 'wasteType', 'matches.processor'])->findOrFail($id);
        return response()->json($listing);
    }

    public function deleteListing($id)
    {
        $listing = WasteListing::findOrFail($id);
        $listing->delete();

        return response()->json(['success' => true, 'message' => 'Listing deleted successfully']);
    }

    public function exportListings(Request $request)
    {
        $query = WasteListing::with(['vendor', 'category']);

        if ($request->status && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        $listings = $query->get();

        $filename = 'listings_' . date('Y-m-d') . '.csv';
        $handle = fopen('php://temp', 'w+');

        fputcsv($handle, ['ID', 'Waste Type', 'Category', 'Quantity (kg)', 'Vendor', 'Market Area', 'Status', 'Listed Date']);

        foreach ($listings as $listing) {
            fputcsv($handle, [
                $listing->id,
                $listing->waste_type_name,
                $listing->category->name ?? '-',
                $listing->quantity_kg,
                $listing->vendor->name,
                $listing->market_area,
                $listing->status,
                $listing->created_at->format('Y-m-d'),
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

    // ==================== MATCHES MANAGEMENT ====================
    public function matches(Request $request)
    {
        $query = WasteMatch::with(['wasteListing', 'processor', 'review']);

        if ($request->search) {
            $query->whereHas('wasteListing', function ($q) use ($request) {
                $q->where('waste_type_name', 'like', "%{$request->search}%");
            });
        }

        if ($request->status && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        $matches = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.matches.index', compact('matches'));
    }

    public function getMatchDetails($id)
    {
        $match = WasteMatch::with(['wasteListing.vendor', 'processor', 'review'])->findOrFail($id);
        return response()->json($match);
    }

    public function updateMatchStatus(Request $request, $id)
    {
        $match = WasteMatch::findOrFail($id);
        $match->update(['status' => $request->status]);

        return response()->json(['success' => true, 'message' => 'Match status updated']);
    }

    // ==================== REPORTS & ANALYTICS ====================
    public function reports(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->subDays(30);
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();

        $userRegistrations = [
            'vendors' => User::where('role', 'vendor')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'processors' => User::where('role', 'processor')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'admins' => User::where('role', 'admin')->whereBetween('created_at', [$startDate, $endDate])->count(),
        ];

        $wasteStats = [
            'total_listed' => WasteListing::whereBetween('created_at', [$startDate, $endDate])->sum('quantity_kg'),
            'total_collected' => WasteMatch::where('status', 'completed')->whereBetween('completed_at', [$startDate, $endDate])->sum('actual_quantity_kg'),
            'by_category' => WasteCategory::with([
                'wasteListings' => function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('created_at', [$startDate, $endDate]);
                }
            ])->get()->map(fn($cat) => ['name' => $cat->name, 'total' => $cat->wasteListings->sum('quantity_kg')])->filter(fn($cat) => $cat['total'] > 0),
            'by_market' => WasteListing::select('market_area', DB::raw('SUM(quantity_kg) as total'))
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('market_area')
                ->get(),
        ];

        $matchStats = [
            'total' => WasteMatch::whereBetween('created_at', [$startDate, $endDate])->count(),
            'completed' => WasteMatch::where('status', 'completed')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'avg_completion_time' => $this->getAverageCompletionTime($startDate, $endDate),
            'by_processor' => WasteMatch::select('processor_id', DB::raw('COUNT(*) as count'))
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('processor_id')
                ->with('processor')
                ->get(),
        ];

        $totalCollected = WasteMatch::where('status', 'completed')->sum('actual_quantity_kg');
        $environmentalImpact = [
            'waste_diverted' => $totalCollected,
            'co2_saved' => round($totalCollected * 0.5, 1),
            'trees_equivalent' => round($totalCollected / 21, 1),
        ];

        return view('admin.reports.index', compact(
            'startDate',
            'endDate',
            'userRegistrations',
            'wasteStats',
            'matchStats',
            'environmentalImpact'
        ));
    }

    private function getAverageCompletionTime($startDate, $endDate)
    {
        $matches = WasteMatch::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        if ($matches->isEmpty())
            return 0;

        $totalDays = $matches->sum(function ($match) {
            return $match->completed_at->diffInDays($match->claimed_at);
        });

        return round($totalDays / $matches->count(), 1);
    }

    public function exportReport(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->subDays(30);
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();

        $matches = WasteMatch::with(['wasteListing', 'processor'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $filename = 'report_' . date('Y-m-d') . '.csv';
        $handle = fopen('php://temp', 'w+');

        fputcsv($handle, ['Match ID', 'Waste Type', 'Quantity (kg)', 'Vendor', 'Processor', 'Status', 'Claimed Date', 'Completed Date']);

        foreach ($matches as $match) {
            fputcsv($handle, [
                $match->id,
                $match->wasteListing->waste_type_name,
                $match->actual_quantity_kg ?? $match->wasteListing->quantity_kg,
                $match->wasteListing->vendor->name,
                $match->processor->name,
                $match->status,
                $match->claimed_at?->format('Y-m-d'),
                $match->completed_at?->format('Y-m-d'),
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

    // ==================== SETTINGS ====================
    public function settings()
    {
        $categories = WasteCategory::orderBy('display_order')->get();
        $wasteTypes = WasteType::with('category')->get();

        return view('admin.settings.index', compact('categories', 'wasteTypes'));
    }

    public function updateSettings(Request $request)
    {
        return response()->json(['success' => true, 'message' => 'Settings updated']);
    }

    public function toggleCategory($id)
    {
        $category = WasteCategory::findOrFail($id);
        $category->update(['is_active' => !$category->is_active]);

        return response()->json(['success' => true, 'message' => 'Category updated']);
    }

    public function addWasteType(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:waste_categories,id',
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:20',
            'min_quantity' => 'required|integer',
        ]);

        WasteType::create($validated);

        return response()->json(['success' => true, 'message' => 'Waste type added']);
    }
}