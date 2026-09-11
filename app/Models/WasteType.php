<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WasteType extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'unit',
        'min_quantity',
        'max_quantity',
        'image_url',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(WasteCategory::class);
    }

    public function wasteListings()
    {
        return $this->hasMany(WasteListing::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}