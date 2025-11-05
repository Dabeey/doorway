<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // Add composite indexes for common queries
            $table->index(['is_active', 'status', 'created_at'], 'properties_active_status_created');
            $table->index(['type', 'listing_type', 'is_active'], 'properties_type_listing_active');
            $table->index(['city', 'is_active'], 'properties_city_active');
            $table->index(['price', 'listing_type'], 'properties_price_listing');
            
            // Full-text index for search (MySQL/MariaDB)
            if (config('database.default') === 'mysql') {
                $table->fullText(['title', 'description', 'address'], 'properties_search_fulltext');
            }
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex('properties_active_status_created');
            $table->dropIndex('properties_type_listing_active');
            $table->dropIndex('properties_city_active');
            $table->dropIndex('properties_price_listing');
            
            if (config('database.default') === 'mysql') {
                $table->dropFullText('properties_search_fulltext');
            }
        });
    }
};