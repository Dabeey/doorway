<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Support\Facades\Storage;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'type',
        'listing_type',
        'status',
        'price',
        'price_per_sqft',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'latitude',
        'longitude',
        'bedrooms',
        'bathrooms',
        'total_area',
        'built_year',
        'furnished',
        'parking',
        'parking_spaces',
        'features',
        'images',
        'slug',
        'meta_title',
        'meta_description',
        'is_featured',
        'is_active',
        'featured_until',
        'contact_name',
        'contact_phone',
        'contact_email',
    ];

    protected $casts = [
        'features' => 'array',
        'images' => 'array',  // Let Laravel handle the array casting automatically
        'furnished' => 'boolean',
        'parking' => 'boolean',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'featured_until' => 'datetime',
        'price' => 'decimal:2',
        'price_per_sqft' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    // REMOVED the getImagesAttribute accessor - it was causing conflicts!
    // Laravel's array casting will handle JSON decoding automatically

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($property): void {
            if (empty($property->slug)) {
                $property->slug = Str::slug($property->title);
            }
        });

        static::updating(function ($property): void {
            if ($property->isDirty('title')) {
                $property->slug = Str::slug($property->title);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // Scopes
    #[Scope]
    public function available(Builder $query): Builder
    {
        return $query->where('status', 'available')
            ->where('is_active', true);
    }

    #[Scope]
    public function forSale(Builder $query): Builder
    {
        return $query->where('listing_type', 'sale');
    }

    #[Scope]
    public function forRent(Builder $query): Builder
    {
        return $query->where('listing_type', 'rent');
    }

    #[Scope]
    public function featured(Builder $query): Builder
    {
        return $query->where('is_featured', true)
            ->where('is_active', true)
            ->where('featured_until', '>=', now());
    }

    #[Scope]
    public function inCity(Builder $query, string $city): Builder
    {
        return $query->where('city', 'like', "%{$city}%");
    }

    #[Scope]
    public function priceBetween(Builder $query, float $min, float $max): Builder
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    #[Scope]
    public function byType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    #[Scope]
    public function withBedrooms(Builder $query, int $count): Builder
    {
        return $query->where('bedrooms', '>=', $count);
    }

    // Accessor methods
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 2) . ' NGN';
    }

    public function getFullAddressAttribute(): string
    {
        return "{$this->address}, {$this->city}, {$this->state}, {$this->country}";
    }

    // FIXED: Main image accessor
    public function getMainImageAttribute(): ?string
    {
        $images = $this->images;
        
        // Check if images array exists and has items
        if (!is_array($images) || empty($images)) {
            return null;
        }
        
        $mainImage = $images[0];
        
        // If it's already a full URL (from seeding), return as is
        if (filter_var($mainImage, FILTER_VALIDATE_URL)) {
            return $mainImage;
        }
        
        // For local storage paths, check if file exists before returning URL
        if (Storage::disk('public')->exists($mainImage)) {
            return Storage::disk('public')->url($mainImage);
        }
        
        return null;
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->main_image;
    }

    // FIXED: Get all image URLs
    public function getImageUrlsAttribute(): array
    {
        $images = $this->images;
        
        if (!is_array($images) || empty($images)) {
            return [];
        }
        
        $urls = [];
        
        foreach ($images as $image) {
            if (filter_var($image, FILTER_VALIDATE_URL)) {
                // External URL from seeding
                $urls[] = $image;
            } elseif (Storage::disk('public')->exists($image)) {
                // Local file - convert to URL only if it exists
                $urls[] = Storage::disk('public')->url($image);
            }
        }
        
        return $urls;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'available' => 'success',
            'sold' => 'danger',
            'pending' => 'info',
            'draft' => 'secondary',
            'rented' => 'warning',
            default => 'secondary',
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'apartment' => 'fa-solid fa-building',
            'house' => 'fa-solid fa-house',
            'condo' => 'fa-solid fa-city',
            'land' => 'fa-solid fa-tree',
            'townhouse' => 'fa-solid fa-house-chimney',
            'villa' => 'fa-solid fa-hotel',
            'commercial' => 'fa-solid fa-store',
            default => 'fa-solid fa-home',
        };
    }

    public function isFeatured(): bool
    {
        return $this->is_featured === true;
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available' && $this->is_active;
    }

    public function calculatePricePerSqft(): ?float
    {
        if (!$this->total_area || $this->total_area <= 0) {
            return null;
        }
    
        $pricePerSqft = round($this->price / $this->total_area, 2);
        $this->price_per_sqft = $pricePerSqft;
        $this->save();
    
        return $pricePerSqft;
    }

    public function addFeature(string $feature): void
    {
        $features = $this->features ?? [];

        if (!in_array($feature, $features, true)) {
            $features[] = $feature;
            $this->features = $features;
            $this->save();
        }
    }

    public function removeFeature(string $feature): void
    {
        if (!$this->features) {
            return;
        }

        $features = array_filter($this->features, fn ($f) => $f !== $feature);
        $this->features = array_values($features);
        $this->save();
    }

    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features ?? [], true);
    }

    public static function getPropertyTypes(): array
    {
        return [
            'apartment' => 'Apartment',
            'house' => 'House',
            'condo' => 'Condo',
            'land' => 'Land',
            'townhouse' => 'Townhouse',
            'villa' => 'Villa',
            'commercial' => 'Commercial Property',
        ];
    }

    public static function getListingTypes(): array
    {
        return [
            'sale' => 'For Sale',
            'rent' => 'For Rent',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            'available' => 'Available',
            'sold' => 'Sold',
            'pending' => 'Pending',
            'draft' => 'Draft',
            'rented' => 'Rented',
        ];
    }
}