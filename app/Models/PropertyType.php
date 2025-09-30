<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PropertyType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    // Auto-generate slug
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($propertyType) {
            if (empty($propertyType->slug)) {
                $propertyType->slug = Str::slug($propertyType->name);
            }
        });

        static::updating(function ($propertyType) {
            if ($propertyType->isDirty('name') && empty($propertyType->slug)) {
                $propertyType->slug = Str::slug($propertyType->name);
            }
        });
    }

    // Relationships
    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('name');
    }
}
