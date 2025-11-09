<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a random user
        User::factory(1)->create();

        // Create admin only if it doesn't already exist
        if (!User::where('email', 'admin@realestate.com')->exists()) {
            User::factory()->create([
                'name' => 'Admin',
                'email' => 'admin@realestate.com',
                'password' => bcrypt('admin123'),
            ]);
        }

        // Create properties
        Property::factory(70)->create();
    }
}
