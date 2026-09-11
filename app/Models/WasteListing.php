<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WasteListing extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'category_id',
        'waste_type_id',
        'waste_type_name',
        'quantity_kg',
        'unit',
        'latitude',
        'longitude',
        'market_area',
        'availability_window',
        'photo_url',
        'status',
        'is_urgent',
        'is_free',
        'offered_price',
        'expires_at',
        'notes',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'quantity_kg' => 'decimal:2',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    // Relationships
    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function wasteType()
    {
        return $this->belongsTo(WasteType::class);
    }

    public function matches()
    {
        return $this->hasMany(WasteMatch::class, 'waste_listing_id');
    }

    public function activeMatch()
    {
        return $this->hasOne(WasteMatch::class, 'waste_listing_id')
            ->whereNotIn('status', ['cancelled', 'completed']);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    // Accessor for photo URL
    public function getPhotoUrlAttribute($value)
    {
        if ($value) {
            return asset('storage/' . $value);
        }
        return null;
    }
    public function category()
    {
        return $this->belongsTo(WasteCategory::class);
    }

    // Status badge color helper
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'active' => 'bg-emerald-100 text-emerald-800',
            'claimed' => 'bg-amber-100 text-amber-800',
            'collected' => 'bg-blue-100 text-blue-800',
            'completed' => 'bg-gray-100 text-gray-700',
            'expired' => 'bg-red-100 text-red-700',
            'cancelled' => 'bg-gray-100 text-gray-500',
            default => 'bg-gray-100 text-gray-600',
        };
    }
}