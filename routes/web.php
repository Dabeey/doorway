<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;
use App\Livewire\PropertyListing;
use App\Livewire\PropertyShow;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Cloudinary\Cloudinary;


Route::get('/', function (): RedirectResponse{
    return redirect()->route('properties.index');
});


Route::get('properties', PropertyListing::class)->name('properties.index');

// Show
Route::get('properties/{property:slug}', PropertyShow::class)->name('properties.show');

// Temporary route to run migrations - DELETE AFTER USE!
Route::get('/setup-database', function() {
    try {
        // Run migrations
        Artisan::call('migrate', ['--force' => true]);
        $migrations = Artisan::output();
        
        // Link storage
        Artisan::call('storage:link');
        $storage = Artisan::output();
        
        // Seed database (optional)
        Artisan::call('db:seed', ['--force' => true]);
        $seed = Artisan::output();

        return response()->json([
            'status' => 'success',
            'message' => '✅ Database setup completed!',
            'migrations' => $migrations,
            'storage' => $storage,
            'seed' => $seed

        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});
    
// TEMPORARY - Create admin user
Route::get('/create-admin', function() {
    if (app()->environment('production')) {
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'admin@realestate.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('admin123'), // Change this password!
            ]
        );
        
        return "Admin created! Email: admin@realestate.com, Password: admin123 - DELETE THIS ROUTE NOW!";
    }
    return 'Not in production';
});


Route::get('/test-db', function() {
    try {
        DB::connection()->getPdo();
        $tables = DB::select('SHOW TABLES');
        return response()->json([
            'status' => 'Database connected!',
            'tables' => $tables
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'Database connection failed',
            'error' => $e->getMessage()
        ], 500);
    }
});


Route::get('/test-storage', function() {
    $results = [];
    
    // Test storage write
    try {
        Storage::disk('public')->put('test.txt', 'test');
        $results['storage_write'] = 'OK';
    } catch (\Exception $e) {
        $results['storage_write'] = $e->getMessage();
    }
    
    // Check symlink
    $results['symlink_exists'] = file_exists(public_path('storage'));
    $results['storage_path'] = storage_path('app/public');
    $results['public_path'] = public_path('storage');
    
    return response()->json($results);
});

Route::get('/make-admin/{email}', function($email) {
    $user = \App\Models\User::where('email', $email)->first();
    
    if (!$user) {
        return "User not found!";
    }
    
    // If you have an is_admin column
    // $user->is_admin = true;
    // $user->save();
    
    return "User {$user->email} is now admin! (Delete this route now!)";
})->where('email', '.*');


// Temporary routes - DELETE AFTER USE!
Route::get('/seed-properties', function() {
    if (app()->environment('production')) {
        try {
            // Seed properties
            Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\DatabaseSeeder', '--force' => true]);
            
            $output = Artisan::output();
            
            $propertyCount = \App\Models\Property::count();
            $userCount = \App\Models\User::count();
            
            return "<h1>✅ Database Seeded Successfully!</h1>
                    <p><strong>Properties created:</strong> {$propertyCount}</p>
                    <p><strong>Users:</strong> {$userCount}</p>
                    <p><a href='/'>Go to Homepage</a></p>
                    <p><a href='/admin'>Go to Admin</a></p>
                    <pre>{$output}</pre>
                    <p style='color:red;'><strong>⚠️ DELETE /seed-properties route now!</strong></p>";
        } catch (\Exception $e) {
            return "<h1>❌ Seeding Failed</h1><pre>" . $e->getMessage() . "</pre>";
        }
    }
    return 'Only works in production';
});

Route::get('/add-test-property', function() {
    if (app()->environment('production')) {
        try {
            $property = \App\Models\Property::create([
                'title' => 'Beautiful 3 Bedroom House in Lagos',
                'description' => 'A stunning property with modern amenities',
                'type' => 'house',
                'listing_type' => 'sale',
                'status' => 'available',
                'price' => 50000000,
                'address' => '123 Test Street',
                'city' => 'Lagos',
                'state' => 'Lagos State',
                'country' => 'Nigeria',
                'bedrooms' => 3,
                'bathrooms' => 2,
                'total_area' => 200,
                'built_year' => 2020,
                'furnished' => true,
                'parking' => true,
                'parking_spaces' => 2,
                'features' => ['Swimming Pool', 'Garden', 'Security'],
                'images' => [
                    'https://picsum.photos/seed/house1/800/600',
                    'https://picsum.photos/seed/house2/800/600',
                    'https://picsum.photos/seed/house3/800/600',
                ],
                'slug' => 'beautiful-3-bedroom-house-lagos-' . rand(1000, 9999),
                'is_featured' => true,
                'is_active' => true,
            ]);
            
            return "<h1>✅ Test Property Created!</h1>
                    <p><strong>ID:</strong> {$property->id}</p>
                    <p><strong>Title:</strong> {$property->title}</p>
                    <p><a href='/'>View on Homepage</a></p>
                    <p><a href='/admin/properties'>View in Admin</a></p>";
        } catch (\Exception $e) {
            return "<h1>❌ Failed</h1><pre>" . $e->getMessage() . "</pre>";
        }
    }
    return 'Only works in production';
});


Route::get('/activate-all-properties', function() {
    $updated = \App\Models\Property::query()
        ->update([
            'is_active' => true,
            'status' => 'available'
        ]);
    
    return "✅ Updated {$updated} properties to active and available! <a href='/properties'>View Properties</a>";
});




Route::get('/clear-all-images', function() {
    $count = \App\Models\Property::query()->update(['images' => json_encode([])]);
    return "✅ Cleared images from {$count} properties! <a href='/admin/properties'>Go to Admin</a>";
});

Route::get('/activate-all-properties', function() {
    $updated = \App\Models\Property::query()
        ->update([
            'is_active' => true,
            'status' => 'available'
        ]);
    
    $total = \App\Models\Property::count();
    $active = \App\Models\Property::where('is_active', true)->where('status', 'available')->count();
    
    return "
        <h1>✅ Properties Activated!</h1>
        <p>Total properties: {$total}</p>
        <p>Now available: {$active}</p>
        <p><a href='/properties'>View Properties</a></p>
        <p><a href='/admin/properties'>Go to Admin</a></p>
        <p style='color:red;'>DELETE THIS ROUTE NOW!</p>
    ";
});

Route::get('/clear-property-images', function() {
    $count = \App\Models\Property::query()->update(['images' => '[]']);
    
    return "
        <h1>✅ Cleared images from {$count} properties!</h1>
        <p>You can now add images manually from the admin panel.</p>
        <p><a href='/admin/properties'>Go to Admin</a></p>
        <p><a href='/properties'>View Properties (will show placeholders)</a></p>
        <p style='color:red;'>DELETE THIS ROUTE NOW!</p>
    ";
});



Route::get('/delete-duplicates', function() {
    // Keep only first 70 properties, delete the rest
    $toKeep = \App\Models\Property::orderBy('id', 'asc')->take(70)->pluck('id');
    $deleted = \App\Models\Property::whereNotIn('id', $toKeep)->delete();
    
    $remaining = \App\Models\Property::count();
    
    return "
        <h1>✅ Cleanup Complete!</h1>
        <p>Deleted: {$deleted} duplicate properties</p>
        <p>Remaining: {$remaining} properties</p>
        <p><a href='/properties'>View Properties</a></p>
        <p style='color:red;'>DELETE THIS ROUTE NOW!</p>
    ";
});


Route::get('/fix-upload', function() {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    
    $debug = [
        'filesystem_default' => config('filesystems.default'),
        'cloudinary_cloud_name' => config('cloudinary.cloud_name'),
        'cloudinary_api_key' => config('cloudinary.api_key') ? 'Set (' . strlen(config('cloudinary.api_key')) . ' chars)' : 'NOT SET',
        'cloudinary_api_secret' => config('cloudinary.api_secret') ? 'Set (' . strlen(config('cloudinary.api_secret')) . ' chars)' : 'NOT SET',
        'env_filesystem_disk' => env('FILESYSTEM_DISK'),
        'env_cloudinary_cloud' => env('CLOUDINARY_CLOUD_NAME'),
        'env_cloudinary_key' => env('CLOUDINARY_API_KEY') ? 'Set' : 'NOT SET',
    ];
    
    // Check if cloudinary disk exists
    try {
        $disks = config('filesystems.disks');
        $debug['cloudinary_disk_configured'] = isset($disks['cloudinary']) ? 'Yes' : 'No';
    } catch (\Exception $e) {
        $debug['cloudinary_disk_configured'] = 'Error: ' . $e->getMessage();
    }
    
    echo "<h1>Debug Info</h1>";
    echo "<pre>";
    print_r($debug);
    echo "</pre>";
    echo "<p><a href='/admin/properties'>Back to Admin</a></p>";
});


Route::get('/session-check', function () {
    return session()->all();
});


Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});



Route::get('/test-cloudinary-simple', function () {
    try {
        // Initialize Cloudinary client
        $cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key'    => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ]
        ]);

        // Upload
        $result = $cloudinary->uploadApi()->upload(
            'https://via.placeholder.com/150',
            [
                'folder'    => 'test',
                'public_id' => 'test-' . time(),
            ]
        );

        return response()->json([
            'success' => true,
            'url' => $result['secure_url'],
            'public_id' => $result['public_id']
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});
