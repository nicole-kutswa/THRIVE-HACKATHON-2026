<?php

namespace App\Http\Livewire\Vendor;

use App\Models\WasteListing;
use App\Models\WasteMatch;
use App\Models\QualityReview;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class VendorDashboard extends Component
{
    public string $activeTab = 'listings';
    public bool $showRateModal = false;
    public ?int $ratingMatchId = null;
    public int $rating = 0;
    public string $ratingComment = '';

    protected $listeners = ['wasteListingCreated' => '$refresh'];

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function cancelListing(int $listingId): void
    {
        $listing = WasteListing::where('id', $listingId)
            ->where('vendor_id', Auth::id())
            ->firstOrFail();
        $listing->update(['status' => 'cancelled']);
        session()->flash('success', 'Listing cancelled.');
    }

    public function openRateModal(int $matchId): void
    {
        $this->ratingMatchId = $matchId;
        $this->rating = 0;
        $this->ratingComment = '';
        $this->showRateModal = true;
    }

    public function closeRateModal(): void
    {
        $this->showRateModal = false;
        $this->ratingMatchId = null;
    }

    public function submitRating(): void
    {
        $this->validate(['rating' => 'required|integer|min:1|max:5']);

        $match = WasteMatch::with('wasteListing')->where('id', $this->ratingMatchId)->firstOrFail();

        QualityReview::create([
            'match_id' => $match->id,
            'reviewer_id' => Auth::id(),
            'reviewee_id' => $match->processor_id,
            'rating' => $this->rating,
            'comment' => $this->ratingComment ?: null,
        ]);

        $this->closeRateModal();
        session()->flash('success', 'Thank you for rating!');
    }

    public function getStatsProperty()
    {
        $vendorId = Auth::id();
        $totalListed = WasteListing::where('vendor_id', $vendorId)->sum('quantity_kg');
        $totalCollected = WasteMatch::whereHas('wasteListing', fn($q) => $q->where('vendor_id', $vendorId))
            ->where('status', 'completed')->sum('actual_quantity_kg');
        $activeListings = WasteListing::where('vendor_id', $vendorId)->where('status', 'active')->count();
        $completedMatches = WasteMatch::whereHas('wasteListing', fn($q) => $q->where('vendor_id', $vendorId))
            ->where('status', 'completed')->count();

        return [
            'total_listed' => number_format($totalListed, 1),
            'total_collected' => number_format($totalCollected, 1),
            'active_listings' => $activeListings,
            'completed_matches' => $completedMatches,
            'co2_saved' => number_format($totalCollected * 0.5, 1),
        ];
    }

    public function getActiveListingsProperty()
    {
        return WasteListing::with('wasteType', 'activeMatch.processor')
            ->where('vendor_id', Auth::id())
            ->whereIn('status', ['active', 'claimed', 'collected'])
            ->latest()
            ->get();
    }

    public function getInProgressMatchesProperty()
    {
        return WasteMatch::with(['wasteListing.wasteType', 'processor'])
            ->whereHas('wasteListing', fn($q) => $q->where('vendor_id', Auth::id()))
            ->whereIn('status', ['pending', 'accepted', 'in_transit', 'collected'])
            ->latest('claimed_at')
            ->get();
    }

    public function getCompletedMatchesProperty()
    {
        return WasteMatch::with(['wasteListing.wasteType', 'processor', 'review'])
            ->whereHas('wasteListing', fn($q) => $q->where('vendor_id', Auth::id()))
            ->where('status', 'completed')
            ->latest('completed_at')
            ->get();
    }

    public function render()
    {
        return view('livewire.vendor.vendor-dashboard', [
            'stats' => $this->stats,
            'activeListings' => $this->activeListings,
            'inProgress' => $this->inProgressMatches,
            'completedMatches' => $this->completedMatches,
            'user' => Auth::user(),
        ]);
    }
}