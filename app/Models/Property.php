<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Support\Str;

class Property extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'title',
        'description',
        'slug',
        'property_type_id',
        'location_id',
        'property_use_category_id',
        'property_style_id',
        'status',
        'listing_type',
        'price',
        'currency',
        'bedrooms',
        'bathrooms',
        'area',
        'lot_size',
        'year_built',
        'garage_spaces',
        'neighborhood_ratings',
        'is_featured',
        'is_best_deal',
        'virtual_tour_url',
        'view_count',
        'meta_description',
        'meta_keywords',
        'agent_id',
    ];

    protected $casts = [
        'neighborhood_ratings' => 'array',
        'meta_keywords' => 'array',
        'is_featured' => 'boolean',
        'is_best_deal' => 'boolean',
        'price' => 'decimal:2',
        'area' => 'decimal:2',
        'lot_size' => 'decimal:2',
        'view_count' => 'integer',
    ];

    protected $appends = ['formatted_price'];

    // Media Library Collections
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('property_images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useDisk('public');

        $this->addMediaCollection('featured_image')
            ->singleFile()
            ->useDisk('public');

        $this->addMediaCollection('documents')
            ->acceptsMimeTypes(['application/pdf'])
            ->useDisk('public');
    }

    // Relationships
    public function propertyType()
    {
        return $this->belongsTo(PropertyType::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function propertyUseCategory()
    {
        return $this->belongsTo(PropertyUseCategory::class);
    }

    public function propertyStyle()
    {
        return $this->belongsTo(PropertyStyle::class);
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'amenity_property');
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoritedByUsers()
    {
        return $this->belongsToMany(User::class, 'favorites')
            ->withTimestamps();
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeBestDeals($query)
    {
        return $query->where('is_best_deal', true);
    }

    public function scopeForSale($query)
    {
        return $query->where('listing_type', 'sale');
    }

    public function scopeForRent($query)
    {
        return $query->where('listing_type', 'rent');
    }

    public function scopeByType($query, $typeId)
    {
        if ($typeId && $typeId !== 'all') {
            return $query->where('property_type_id', $typeId);
        }
        return $query;
    }

    public function scopeByLocation($query, $locationId)
    {
        if ($locationId) {
            return $query->where('location_id', $locationId);
        }
        return $query;
    }

    public function scopePriceRange($query, $minPrice, $maxPrice)
    {
        return $query->whereBetween('price', [$minPrice, $maxPrice]);
    }

    public function scopeMinBedrooms($query, $bedrooms)
    {
        if ($bedrooms && $bedrooms !== 'any') {
            if ($bedrooms === '4+') {
                return $query->where('bedrooms', '>=', 4);
            }
            return $query->where('bedrooms', '>=', (int)$bedrooms);
        }
        return $query;
    }

    public function scopeMinBathrooms($query, $bathrooms)
    {
        if ($bathrooms && $bathrooms !== 'any') {
            if ($bathrooms === '3+') {
                return $query->where('bathrooms', '>=', 3);
            }
            return $query->where('bathrooms', '>=', (int)$bathrooms);
        }
        return $query;
    }

    public function scopeSearch($query, $searchTerm)
    {
        if ($searchTerm) {
            return $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%")
                    ->orWhereHas('location', function ($locationQuery) use ($searchTerm) {
                        $locationQuery->where('name', 'like', "%{$searchTerm}%")
                            ->orWhere('city', 'like', "%{$searchTerm}%");
                    });
            });
        }
        return $query;
    }

    // Accessors
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 0, '.', ',');
    }

    // Mutators
    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = $value;
        $this->attributes['slug'] = Str::slug($value) . '-' . Str::random(6);
    }

    // Helper Methods
    public function incrementViewCount()
    {
        $this->increment('view_count');
    }

    public function isFavoritedBy($userId)
    {
        return $this->favorites()->where('user_id', $userId)->exists();
    }

    public function getImageUrls()
    {
        return $this->getMedia('property_images')->map(function ($media) {
            return $media->getUrl();
        })->toArray();
    }



    public function getFeaturedImageUrl()
    {
        $media = $this->getFirstMedia('featured_image');
        return $media ? $media->getUrl() : null;
    }
}
