<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

{{-- Head: page metadata --}}
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'Page Title' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Preload critical resources --}}
    <link rel="dns-prefetch" href="//doorway.test">
    <link rel="preconnect" href="//doorway.test">

    {{-- Add alpine.js CDN for dropdown--}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>


<body>
    <div>
    
        {{-- Navigation --}}
        <nav class="bg-white dark:bg-neutral-900 shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    {{-- Logo --}}
                    <div class="flex items-center">
                        <a href="{{ route('properties.index') }}" class="flex items-center space-x-2">
                            <span class="text-2xl">🏠</span>
                            <span class="text-xl font-bold text-gray-900 dark:text-gray-200">Doorway</span>
                        </a>
                    </div>

                    {{-- Desktop Navigation Links --}}
                    <div class="hidden md:flex items-center space-x-8">
                        <a href="{{ route('properties.index') }}"
                            class="text-gray-600 dark:text-gray-200 hover:text-gray-900 dark:hover:text-gray-300 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('properties.index') ? 'text-blue-600 ' : '' }}">
                            All Properties
                        </a>
                        <a href="{{ route('properties.index', ['listingType' => 'sale']) }}"
                            class="text-gray-600 dark:text-gray-200 hover:text-gray-900 dark:hover:text-gray-300 px-3 py-2 rounded-md text-sm font-medium">
                            For Sale
                        </a>
                        <a href="{{ route('properties.index', ['listingType' => 'rent']) }}"
                            class="text-gray-600 dark:text-gray-200 hover:text-gray-900 dark:hover:text-gray-300 px-3 py-2 rounded-md text-sm font-medium">
                            For Rent
                        </a>
                        <a href="{{ route('properties.index', ['featuredOnly' => true]) }}"
                            class="text-gray-600 dark:text-gray-200 hover:text-gray-900 dark:hover:text-gray-300 px-3 py-2 rounded-md text-sm font-medium">
                            Featured
                        </a>
                    </div>

                                
                    {{-- ADD USER DROPDOWN --}}
                    @auth
                    <div class="hidden md:flex items-center space-x-8">
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" 
                                    class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </div>
                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
                                </svg>
                            </button>

                            <div x-show="open" 
                                @click.away="open = false"
                                class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 z-50"
                                style="display: none;">
                                
                                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ auth()->user()->name }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ auth()->user()->email }}</p>
                                </div>

                                <div class="py-1">
                                    <a href="{{ route('profile.edit') }}" 
                                    class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                        ⚙️ Settings
                                    </a>
                                    
                                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                                        @csrf
                                        <button type="submit" 
                                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                            🚪 Log Out
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    @else
                    <div class="hidden md:flex items-center space-x-4">
                        <a href="{{ route('login') }}" class="text-gray-600 dark:text-gray-200 hover:text-gray-900 dark:hover:text-gray-300">Login</a>
                        <a href="{{ route('register') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Register</a>
                    </div>
                    @endauth


                    {{-- Mobile menu button --}}
                    <div class="md:hidden"> 
                    {{--  → JavaScript toggle --}}
                        <button type="button" id="mobile-menu-button" 
                            class="text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500 p-2 rounded-md">
                            <span class="sr-only">Open main menu</span>
                            {{-- Hamburger icon --}}
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>


            {{-- Mobile menu --}}
            <div class="md:hidden" id="mobile-menu" style="display: none;">
            {{-- Hidden by default--}}
                {{-- Requires JavaScript to toggle display: none when button is clicked --}}
                <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 bg-white dark:bg-neutral-900 border-t border-gray-200">
                    <a href="{{ route('properties.index') }}"
                        class="text-gray-600 hover:text-gray-900 dark:hover:text-gray-300 block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('properties.index') ? 'text-blue-600 bg-blue-50' : '' }}">
                        All Properties
                    </a>
                    <a href="{{ route('properties.index', ['listingType' => 'sale']) }}"
                        class="text-gray-600 hover:text-gray-900 dark:hover:text-gray-300 block px-3 py-2 rounded-md text-base font-medium">
                        For Sale
                    </a>
                    <a href="{{ route('properties.index', ['listingType' => 'rent']) }}"
                        class="text-gray-600 hover:text-gray-900 dark:hover:text-gray-300 block px-3 py-2 rounded-md text-base font-medium">
                        For Rent
                    </a>
                    <a href="{{ route('properties.index', ['featuredOnly' => true]) }}"
                        class="text-gray-600 hover:text-gray-900 dark:hover:text-gray-300 block px-3 py-2 rounded-md text-base font-medium">
                        Featured
                    </a>

                    
                    {{-- ADD MOBILE AUTH LINKS --}}
                    @auth
                    <div class="border-t border-gray-200 pt-2 mt-2" x-data="{ open: false }">
                        <button @click="open = !open" 
                                class="flex items-center justify-between w-full px-3 py-2 rounded-md text-base font-medium text-gray-600 hover:text-gray-900 dark:hover:text-gray-300">
                            <div class="flex items-center space-x-2">
                                <div class="w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center text-white text-xs font-medium">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </div>
                                <span>Account</span>
                            </div>
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
                            </svg>
                        </button>
            
                        <div x-show="open" 
                            x-transition
                            class="mt-2 space-y-1 pl-4">
                            <a href="{{ route('profile.edit') }}" 
                            class="block px-3 py-2 rounded-md text-sm text-gray-600 hover:text-gray-900 dark:hover:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                                ⚙️ Settings
                            </a>
                            
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" 
                                        class="block w-full text-left px-3 py-2 rounded-md text-sm text-gray-600 hover:text-gray-900 dark:hover:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                                    🚪 Log Out
                                </button>
                            </form>
                        </div>
                    </div>
                    @else
                    <div class="border-t border-gray-200 pt-2 mt-2 space-y-1">
                        <a href="{{ route('login') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-600 hover:text-gray-900 dark:hover:text-gray-300">Login</a>
                        <a href="{{ route('register') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-600 hover:text-gray-900 dark:hover:text-gray-300">Register</a>
                    </div>
                    @endauth
                    
                </div>
            </div>
        </nav>


        {{-- Main Content Area --}}
        <div>
            {{ $slot }}
        </div>


        {{-- Footer --}}
        <footer class="bg-gray-800 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    {{-- Company Info --}}
                    <div class="col-span-1 md:col-span-2">
                        <div class="flex items-center space-x-2 mb-4">
                            <span class="text-2xl">🏠</span>
                            <span class="text-xl font-bold">Doorway</span>
                        </div>
                        <p class="text-gray-300 mb-4">
                            Your trusted partner in finding the perfect property in Nigeria.
                            We offer the best selection of houses, apartments, land, and commercial properties.
                        </p>
                        <div class="flex space-x-4">
                            <a href="#" class="text-gray-300 hover:text-white">📘 Facebook</a>
                            <a href="#" class="text-gray-300 hover:text-white">📷 Instagram</a>
                            <a href="#" class="text-gray-300 hover:text-white">🐦 Twitter</a>
                        </div>
                    </div>

                    {{-- Quick Links - Same navigation as header--}}
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                        <ul class="space-y-2 text-gray-300">
                            <li><a href="{{ route('properties.index') }}" class="hover:text-white">All Properties</a></li>
                            <li><a href="{{ route('properties.index', ['listingType' => 'sale']) }}"
                                    class="hover:text-white">For Sale</a></li>
                            <li><a href="{{ route('properties.index', ['listingType' => 'rent']) }}"
                                    class="hover:text-white">For Rent</a></li>
                            <li><a href="{{ route('properties.index', ['featuredOnly' => true]) }}"
                                    class="hover:text-white">Featured</a></li>
                        </ul>
                    </div>

                    {{-- Contact Info --}}
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Contact Us</h3>
                        <ul class="space-y-2 text-gray-300">
                            <li>📍 ESUT Agbani, Enugu, Nigeria.</li>
                            <li>📞 +234 907 7886 670</li>
                            <li>✉️ info@doorway.ng</li>
                            <li>🕐 Mon - Sun: 6AM - 9PM</li>
                        </ul>
                    </div>
                </div>

                {{-- Bottom Footer --}}
                <div class="border-t border-gray-700 pt-8 mt-8">
                    <div class="flex flex-col md:flex-row justify-between items-center">
                        <p class="text-gray-400 text-sm">
                        
                        {{-- © {{ date('Y') }}: Automatically updates year --}}
                            © {{ date('Y') }} Doorway. All rights reserved.
                        </p>
                        <div class="flex space-x-6 mt-4 md:mt-0">
                            <a href="#" class="text-gray-400 hover:text-white text-sm">Privacy Policy</a>
                            <a href="#" class="text-gray-400 hover:text-white text-sm">Terms of Service</a>
                            <a href="#" class="text-gray-400 hover:text-white text-sm">Contact</a>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>