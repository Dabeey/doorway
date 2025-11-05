<?php

namespace App\Livewire;

use App\Models\Property;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Title;

#[Title('Property Listings')]
class PropertyListing extends Component
{
    use WithPagination;

    // Filtering and searching functionality
    public $search = '';
    public $minPrice = '';
    public $maxPrice = '';
    public $type = '';
    public $listingType = '';
    public $city = '';
    public $minBedrooms = '';
    public $featuredOnly = false;
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    public $viewMode = 'grid';
    public $perPage = 12; // ← ADDED: User can control items per page

    // Property types for dropdown
    public $propertyTypes = [
        'house' => 'House',
        'apartment' => 'Apartment',
        'condo' => 'Condo',
        'villa' => 'Villa',
        'commercial' => 'Commercial',
        'land' => 'Land',
    ];

    public $cities = [
        'Lagos', 'Abuja', 'Port Harcourt', 'Kano', 'Ibadan'
    ];

    public $listingTypes = [
        'sale' => 'For Sale',
        'rent' => 'For Rent'
    ];

    // Synchronize properties with the browser
    protected $queryString = [
        'search' => ['except' => ''],
        'type' => ['except' => ''],
        'listingType' => ['except' => ''],
        'city' => ['except' => ''],
        'minPrice' => ['except' => ''],
        'maxPrice' => ['except' => ''],
        'minBedrooms' => ['except' => ''],
        'featuredOnly' => ['except' => false],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 12], // ← ADDED
        // REMOVED: 'page' - Livewire pagination handles this automatically

    ];

    // Reset page when filters change
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingType(): void
    {
        $this->resetPage();
    }

    public function updatingListingType(): void
    {
        $this->resetPage();
    }
    
    public function updatingCity(): void
    {
        $this->resetPage();
    }

    public function updatingMinPrice(): void
    {
        $this->resetPage();
    }

    public function updatingMaxPrice(): void
    {
        $this->resetPage();
    }

    public function updatingMinBedrooms(): void
    {
        $this->resetPage();
    }

    public function updatingFeaturedOnly(): void
    {
        $this->resetPage();
    }

    // ← ADDED: Reset page when per page changes
    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    // Sorting
    public function sortBy($field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        
        $this->resetPage();
    }

    // Clear Filters
    public function clearFilters(): void
    {
        $this->reset([
            'search', 'minPrice', 'maxPrice', 'type', 
            'listingType', 'city', 'minBedrooms', 'featuredOnly'
        ]);
        $this->resetPage();
    }

    // Change view mode
    public function setViewMode($mode): void
    {
        $this->viewMode = $mode;
    }

    // Computed property for properties
    #[Computed]
    public function properties()
    {
        return Property::query()
        ->select([
            'id', 'title', 'slug', 'type', 'listing_type', 'status',
            'price', 'price_per_sqft', 'address', 'city', 'state', 'country',
            'bedrooms', 'bathrooms', 'total_area', 'images', 'is_featured',
            'created_at'
        ]) // Only select needed columns
        
            ->when($this->search, function(Builder $query) {
                $query->where(function($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%')
                      ->orWhere('address', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->type, fn($q) => $q->where('type', $this->type))
            ->when($this->listingType, fn($q) => $q->where('listing_type', $this->listingType))
            ->when($this->city, fn($q) => $q->where('city', $this->city))
            ->when($this->minPrice, fn($q) => $q->where('price', '>=', $this->minPrice))
            ->when($this->maxPrice, fn($q) => $q->where('price', '<=', $this->maxPrice))
            ->when($this->minBedrooms, fn($q) => $q->where('bedrooms', '>=', $this->minBedrooms))
            ->when($this->featuredOnly, fn($q) => $q->where('is_featured', true))
            ->available()
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage); // ← CHANGED: Use dynamic perPage
    }

    public function render()
    {
        return view('livewire.property-listing');
    }
}