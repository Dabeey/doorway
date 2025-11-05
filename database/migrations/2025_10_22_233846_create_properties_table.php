<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();

            // Basic information
            $table->string('title');
            $table->text('description');
            $table->enum('type', ['apartment', 'house', 'condo', 'land', 'townhouse', 'villa', 'commercial']);
            $table->enum('listing_type', ['sale', 'rent'])->default('sale');
            $table->enum('status', ['available', 'sold', 'pending', 'draft', 'rented'])->default('available');

            // Pricing
            $table->decimal('price', 12, 2);
            $table->decimal('price_per_sqft', 8, 2)->nullable();

            // Location
            $table->string('address');
            $table->string('city');
            $table->string('state');
            $table->string('country')->default('Nigeria');
            $table->string('postal_code')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 10, 8)->nullable();

            // Property details
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->integer('total_area')->nullable();
            $table->integer('built_year')->nullable();
            $table->boolean('furnished')->nullable();
            $table->boolean('parking')->nullable();
            $table->integer('parking_spaces')->nullable();

            // Features (JSON)
            $table->json('features')->nullable();
            $table->json('images')->nullable();

            // SEO
            $table->string('slug')->unique();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            // Visibility
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('featured_until')->nullable();

            // Contact info
            $table->string('contact_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();

            // Indexes
            $table->index(['type', 'listing_type', 'status']);
            $table->index(['city', 'state']);
            $table->index(['price', 'listing_type']);
            $table->index(['is_featured']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
