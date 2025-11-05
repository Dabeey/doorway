<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Carbon\Carbon;


class PropertyFactory extends Factory
{
    public function definition(): array
    {
        $type = $this->faker->randomElement(['house','apartment','condo','townhouse','villa','land','commercial']);
        $listingType = $this->faker->randomElement(['sale','rent']);
        $city = $this->faker->randomElement(['Lagos','Abuja', 'Port Harcourt','Ibadan', 'Enugu', 'Awka']);

        // Pricing based on type and listing
        $basePrice = match ($type) {
            'land' => $this->faker->numberBetween(50000000, 500000000),
            'house' => $this->faker->numberBetween(25000000, 200000000),
            'villa' => $this->faker->numberBetween(50000000, 300000000),
            'townhouse' => $this->faker->numberBetween(15000000, 80000000),
            'condo' => $this->faker->numberBetween(10000000, 60000000),
            'apartment' => $this->faker->numberBetween(8000000, 40000000),
            'commercial' => $this->faker->numberBetween(100000000, 1000000000),
            default => $this->faker->numberBetween(5000000, 50000000),
        };

        // Adjust price for rent vs sale
        $price = match ($listingType) {
            'rent' => $basePrice / 100, // Rent is roughly 1% of sale price per month
            'sale' => $basePrice,
        };

        return [
            'type' => $type,
            'listing_type' => $listingType,
            'status' => $this->faker->randomElement(['available', 'sold', 'rented', 'pending', 'draft']),
            'city' => $city,
            'state' => $this->faker->randomElement(['Lagos State', 'Federal Capital Territory', 'Rivers State', 'Oyo State', 'Enugu State', 'Anambra State']),
            'country' => 'Nigeria',
            'postal_code' => $this->faker->postcode(),
            'address' => $this->faker->streetAddress(),
            'latitude' => $this->faker->latitude(6.4, 6.6), // Nigeria latitude range
            'longitude' => $this->faker->longitude(3.3, 3.5), // Nigeria longitude range
            'price' => round($price, 2),
            'price_per_sqft' => $this->getArea($type) > 0 ? round($price / $this->getArea($type), 2) : 0,
            'built_year' => $this->faker->numberBetween(1980, 2023),
            'furnished' => $this->faker->boolean(40), // 40% chance of being furnished
            'parking' => $this->faker->boolean(70), // 70% chance of having parking
            'parking_spaces' => $this->faker->numberBetween(0, 4),
            'bedrooms' => $this->getBedrooms($type),
            'bathrooms' => $this->getBathrooms($type),
            'total_area' => $this->getArea($type),
            // 'features' => json_encode($this->getFeatures($type), JSON_UNESCAPED_SLASHES),
            'features' => $this->getFeatures($type),
            // 'images' => json_encode($this->getImages($type)),
            'images' => $this->getImages($type),
            'slug' => Str::slug($this->generateTitle($type, $listingType, $city)) . '-' . $this->faker->unique()->numberBetween(1000, 9999),
            'meta_title' => $this->faker->sentence(6),
            'meta_description' => $this->faker->paragraph(1),
            'title' => $this->generateTitle($type, $listingType, $city),
            'description' => $this->faker->paragraphs(3, true),
            'is_featured' => $this->faker->boolean(20),
            'is_active' => $this->faker->boolean(90), // 90% chance of being active
            'featured_until' => $this->faker->optional(0.3)->dateTimeBetween('now', '+30 days'), // 30% chance of having featured expiry
            'contact_name' => $this->faker->name(),
            'contact_phone' => $this->faker->phoneNumber(),
            'contact_email' => $this->faker->safeEmail(),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }

    private function generateTitle($type, $listingType, $city): string
    {
        $typeNames = [
            'house' => ['Luxury House', 'Family Home', 'Modern House', 'Spacious House'],
            'apartment' => ['Modern Apartment', 'Luxury Apartment', 'Cozy Apartment', 'Studio Apartment'],
            'condo' => ['Condominium', 'Luxury Condo', 'Modern Condo'],
            'townhouse' => ['Townhouse', 'Modern Townhouse', 'Spacious Townhouse'],
            'villa' => ['Luxury Villa', 'Modern Villa', 'Beachfront Villa'],
            'land' => ['Prime Land', 'Commercial Land', 'Residential Plot'],
            'commercial' => ['Commercial Space', 'Office Space', 'Retail Space']
        ];
    
        $adjectives = ['Beautiful', 'Spacious', 'Modern', 'Luxurious', 'Prime', 'Excellent','Executive', 'Premium', 'Elegant'];        
        $typeName = $this->faker->randomElement($typeNames[$type]);
        $locations = ['in Prime Location', 'in GRA', 'in City Center', 'in Peaceful Neighborhood'];
        $location = $this->faker->randomElement($locations); 
        $adjective = $this->faker->randomElement($adjectives);
        
        return "$adjective $typeName for $listingType $location in $city";
    
    }

    private function getBedrooms($type): ?int
    {
        return match ($type) {
            'land' => null, // Land doesn't have bedrooms
            'commercial' => null, // Commercial might not have traditional bedrooms
            'apartment' => $this->faker->numberBetween(1, 4),
            'condo' => $this->faker->numberBetween(1, 3),
            'townhouse' => $this->faker->numberBetween(2, 5),
            'house' => $this->faker->numberBetween(2, 6),
            'villa' => $this->faker->numberBetween(3, 8),
            default => $this->faker->numberBetween(1, 4),
        };
    }


    private function getBathrooms($type): ?int
    {

    return match ($type) {
        'land' => null,
        'commercial' => $this->faker->numberBetween(1, 5), // Commercial has restrooms
        'apartment' => $this->faker->numberBetween(1, 3),
        'condo' => $this->faker->numberBetween(1, 2),
        'townhouse' => $this->faker->numberBetween(2, 4),
        'house' => $this->faker->numberBetween(2, 5),
        'villa' => $this->faker->numberBetween(3, 6),
        default => $this->faker->numberBetween(1, 3),
        };
    }


    private function getArea($type): int
    {
        $total_area = match ($type) {
            'land' => $this->faker->numberBetween(500, 5000),
            'commercial' => $this->faker->numberBetween(100, 2000),
            'apartment' => $this->faker->numberBetween(50, 200),
            'condo' => $this->faker->numberBetween(40, 150),
            'townhouse' => $this->faker->numberBetween(80, 250),
            'house' => $this->faker->numberBetween(100, 400),
            'villa' => $this->faker->numberBetween(200, 600),
            default => $this->faker->numberBetween(50, 200),
        };

        return $total_area;
    }



    private function getFeatures($type): array
    {
        $baseFeatures = ['Security', 'Water Supply', 'Electricity'];
        
        $typeFeatures = match ($type) {
            'house' => ['Garden', 'Garage', 'Swimming Pool', 'Security Gate', 'Balcony'],
            'apartment' => ['Elevator', 'Security', 'Parking Space', 'Balcony', 'Water Heater'],
            'condo' => ['Swimming Pool', 'Gym', 'Security', 'Parking', 'Concierge'],
            'townhouse' => ['Garage', 'Garden', 'Security', 'Parking'],
            'villa' => ['Swimming Pool', 'Garden', 'Garage', 'Security', 'Maid Room', 'Guest House'],
            'land' => ['Fenced', 'Surveyed', 'Access Road', 'Drainage'],
            'commercial' => ['Parking', 'Security', 'Elevator', 'AC', 'Restrooms', 'Reception'],
            default => ['Security', 'Parking'],
        };

        $selectedFeatures = array_merge($baseFeatures, $this->faker->randomElements($typeFeatures, $this->faker->numberBetween(2, 4)));
        
        return array_unique($selectedFeatures);
    }

    /**
     * Generate realistic image URLs based on property type
     */
    private function getImages($type): array
    {
        $imageBase = match ($type) {
            'house' => 'house',
            'apartment' => 'apartment', 
            'condo' => 'apartment',
            'townhouse' => 'house',
            'villa' => 'villa',
            'land' => 'land',
            'commercial' => 'office',
            default => 'property',
        };

        $images = [];
        $numImages = $this->faker->numberBetween(1, 6);
        
        for ($i = 1; $i <= $numImages; $i++) {
            $images[] = "https://picsum.photos/800/600?random={$this->faker->numberBetween(1000, 9999)}";
            // Alternative: "images/properties/{$imageBase}_{$i}.jpg"
        }
        
        return $images;
    }


    // Function for property listingtype
    public function sold(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'sold',
                'listing_type' => 'sale',
                'is_active' => false,
                'is_featured' => false,
            ];
        });
    }

    /**
     * Configure the factory for RENTED properties
     */
    public function rented(): Factory
    {
        return $this->state(function (array $attributes) {
            $rentedDate = $this->faker->dateTimeBetween('-2 years', 'now');
            
            return [
                'status' => 'rented',
                'listing_type' => 'rent',
                'is_active' => false,
                'is_featured' => false,
            ];
        });
    }

    /**
     * Configure the factory for AVAILABLE properties
     */
    public function available(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'available',
                'is_active' => true,
            ];
        });
    }

    /**
     * Configure the factory for UNDER CONTRACT properties
     */
    public function pending(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending',
                'contract_start' => $this->faker->dateTimeBetween('-1 month', 'now'),
                'contract_end' => $this->faker->dateTimeBetween('now', '+2 months'),
                'is_active' => false,
            ];
        });
    }

    /**
     * Configure the factory for DRAFT properties
     */
    public function draft(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'draft',
                'is_active' => false,
                'is_featured' => false,
                'price' => null, // Draft might not have price set yet
            ];
        });
    }
}