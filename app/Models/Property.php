<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Property extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'title',
        'description',
        'type',
        'status',
        'listing_type',
        'price',
        'currency',
        'address',
        'city',
        'county',
        'country',
        'latitude',
        'longitude',
        'bedrooms',
        'bathrooms',
        'square_footage',
        'lot_size',
        'year_built',
        'parking_spaces',
        'amenities',
        'features',
        'virtual_tour_url',
        'slug',
        'is_featured',
        'view_count'
    ];

    protected $casts = [
        'amenities' => 'array',
        'features' => 'array',
        'is_featured' => 'boolean',
        'price' => 'decimal:2',
        'square_footage' => 'decimal:2',
        'lot_size' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8'
    ];

    // Media Collections
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->singleFile(false);

        $this->addMediaCollection('featured_image')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->singleFile(true);

        $this->addMediaCollection('documents')
            ->acceptsMimeTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
            ->singleFile(false);

        $this->addMediaCollection('floor_plans')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'application/pdf'])
            ->singleFile(false);
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10)
            ->performOnCollections('images', 'featured_image');

        $this->addMediaConversion('medium')
            ->width(600)
            ->height(400)
            ->sharpen(10)
            ->performOnCollections('images', 'featured_image');

        $this->addMediaConversion('large')
            ->width(1200)
            ->height(800)
            ->sharpen(10)
            ->performOnCollections('images', 'featured_image');
    }

    // Relationships
    public function inquiries()
    {
        return $this->hasMany(PropertyInquiry::class);
    }

    public function views()
    {
        return $this->hasMany(PropertyView::class);
    }

    // Accessors
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 0) . ' ' . $this->currency;
    }

    public function getFeaturedImageUrlAttribute()
    {
        $featuredImage = $this->getFirstMedia('featured_image');
        return $featuredImage ? $featuredImage->getUrl('medium') : null;
    }

    public function getImageGalleryAttribute()
    {
        return $this->getMedia('images')->map(function ($media) {
            return [
                'id' => $media->id,
                'name' => $media->name,
                'thumb' => $media->getUrl('thumb'),
                'medium' => $media->getUrl('medium'),
                'large' => $media->getUrl('large'),
                'original' => $media->getUrl(),
            ];
        });
    }

    public function getDocumentsAttribute()
    {
        return $this->getMedia('documents')->map(function ($media) {
            return [
                'id' => $media->id,
                'name' => $media->name,
                'file_name' => $media->file_name,
                'mime_type' => $media->mime_type,
                'size' => $media->size,
                'url' => $media->getUrl(),
            ];
        });
    }

    public function getFloorPlansAttribute()
    {
        return $this->getMedia('floor_plans')->map(function ($media) {
            return [
                'id' => $media->id,
                'name' => $media->name,
                'file_name' => $media->file_name,
                'mime_type' => $media->mime_type,
                'url' => $media->getUrl(),
                'thumb' => $media->mime_type === 'application/pdf' ? null : $media->getUrl('thumb'),
            ];
        });
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeForSale($query)
    {
        return $query->where('listing_type', 'sale');
    }

    public function scopeForRent($query)
    {
        return $query->where('listing_type', 'rent');
    }

    // Methods
    public function incrementViewCount()
    {
        $this->increment('view_count');
    }

    public function trackView($request)
    {
        // Avoid counting multiple views from same IP within an hour
        $recentView = $this->views()
                          ->where('ip_address', $request->ip())
                          ->where('viewed_at', '>', now()->subHour())
                          ->first();

        if (!$recentView) {
            $this->views()->create([
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referrer' => $request->header('referer'),
                'viewed_at' => now()
            ]);

            $this->incrementViewCount();
        }
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($property) {
            if (empty($property->slug)) {
                $property->slug = Str::slug($property->title);
            }
        });
    }
}
