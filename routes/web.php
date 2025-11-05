<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;
use App\Livewire\PropertyListing;
use App\Livewire\PropertyShow;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;

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
        // Artisan::call('db:seed', ['--force' => true]);
        
        return response()->json([
            'status' => 'success',
            'message' => '✅ Database setup completed!',
            'migrations' => $migrations,
            'storage' => $storage
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
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
