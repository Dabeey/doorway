<?php

use Filament\Support\Colors\Color;

return [

    /*
    |--------------------------------------------------------------------------
    | Filament Path
    |--------------------------------------------------------------------------
    | This is the URL path where your admin panel will be accessible.
    */
    'path' => env('FILAMENT_PATH', 'admin'), // string, NOT int

    /*
    |--------------------------------------------------------------------------
    | Filament Domain
    |--------------------------------------------------------------------------
    | You can optionally restrict the panel to a specific domain.
    */
    'domain' => env('FILAMENT_DOMAIN', null), // string|null

    /*
    |--------------------------------------------------------------------------
    | Filament Middleware
    |--------------------------------------------------------------------------
    | Middleware to apply to all admin panel routes.
    | Include 'web' for session/cookie support, and 'auth' for authentication.
    */
    'middleware' => [
        'web',
        // Add authentication middleware if needed
        // \App\Http\Middleware\Authenticate::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Panel Colors
    |--------------------------------------------------------------------------
    */
    'colors' => [
        'primary' => Color::Amber,
    ],

    /*
    |--------------------------------------------------------------------------
    | Widgets & Pages
    |--------------------------------------------------------------------------
    | You can register your own widgets and pages here.
    */
    'widgets' => [],
    'pages' => [],

    /*
    |--------------------------------------------------------------------------
    | Resources
    |--------------------------------------------------------------------------
    | Specify resource directories for auto-discovery.
    */
    'resources' => [
        'directory' => app_path('Filament/Resources'),
        'namespace' => 'App\\Filament\\Resources',
    ],

    /*
    |--------------------------------------------------------------------------
    | Pages
    |--------------------------------------------------------------------------
    */
    'panel_pages' => [
        'directory' => app_path('Filament/Pages'),
        'namespace' => 'App\\Filament\\Pages',
    ],

];
