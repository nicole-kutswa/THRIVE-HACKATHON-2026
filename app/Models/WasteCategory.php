<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WasteCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'color',
        'description',
        'display_order',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function wasteTypes()
    {
        return $this->hasMany(WasteType::class);
    }

    public function wasteListings()
    {
        return $this->hasMany(WasteListing::class, 'category_id');
    }

    public function processors()
    {
        return $this->belongsToMany(User::class, 'processor_categories', 'category_id', 'processor_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }
}