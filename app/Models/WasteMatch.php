<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WasteMatch extends Model
{
    use HasFactory;

    protected $table = 'matches';

    protected $fillable = [
        'waste_listing_id',
        'processor_id',
        'distance_km',
        'status',
        'claimed_at',
        'collected_at',
        'completed_at',
        'actual_quantity_kg',
        'issue_reported',
        'issue_description',
    ];

    protected $casts = [
        'claimed_at' => 'datetime',
        'collected_at' => 'datetime',
        'completed_at' => 'datetime',
        'issue_reported' => 'boolean',
        'actual_quantity_kg' => 'decimal:2',
        'distance_km' => 'decimal:2',
    ];

    // Relationships
    public function wasteListing()
    {
        return $this->belongsTo(WasteListing::class, 'waste_listing_id');
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processor_id');
    }

    public function review()
    {
        return $this->hasOne(QualityReview::class, 'match_id');
    }

    // Scopes
    public function scopeInProgress($query)
    {
        return $query->whereIn('status', ['pending', 'accepted', 'in_transit']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Status label helper
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Claimed – Awaiting Confirmation',
            'accepted' => 'Accepted – Preparing Collection',
            'in_transit' => 'Processor On The Way',
            'collected' => 'Collected – Processing',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    public function isReviewedByVendor(): bool
    {
        return $this->review()
            ->where('reviewer_id', $this->wasteListing->vendor_id)
            ->exists();
    }
}