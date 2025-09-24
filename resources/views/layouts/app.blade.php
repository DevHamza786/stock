<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} - Stock Management</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-50">
        <div class="min-h-screen">
            <!-- Navigation -->
            <nav class="bg-white shadow-lg border-b border-gray-200">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center h-16">
                        <!-- Left side - Logo and Navigation -->
                        <div class="flex items-center space-x-8">
                            <!-- Logo -->
                            <div class="flex-shrink-0">
                                <a href="{{ route('stock-management.dashboard') }}" class="flex items-center">
                                    <div class="h-8 w-8 bg-blue-600 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                        </svg>
                                    </div>
                                    <span class="text-xl font-bold text-gray-900">StockPro</span>
                                </a>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden lg:flex lg:items-center lg:space-x-1">
                                <a href="{{ route('stock-management.dashboard') }}" class="inline-flex items-center px-3 py-2 border-b-2 {{ request()->routeIs('stock-management.dashboard') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium transition-colors duration-200">
                                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"></path>
                                    </svg>
                                    Dashboard
                                </a>

                                <div class="relative group">
                                    <button class="inline-flex items-center px-3 py-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 text-sm font-medium transition-colors duration-200">
                                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                        </svg>
                                        Stock Management
                                        <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                    <div class="absolute left-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                        <div class="py-1">
                                            <a href="{{ route('stock-management.stock-additions.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">Stock Additions</a>
                                            <a href="{{ route('stock-management.stock-issued.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">Stock Issued</a>
                                            <a href="{{ route('stock-management.daily-production.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">Daily Production</a>
                                            <a href="{{ route('stock-management.gate-pass.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">Gate Pass</a>
                                        </div>
                                    </div>
                                </div>

                                <div class="relative group">
                                    <button class="inline-flex items-center px-3 py-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 text-sm font-medium transition-colors duration-200">
                                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                        Master Data
                                        <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                    <div class="absolute left-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                        <div class="py-1">
                                            <a href="{{ route('stock-management.products.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">Products</a>
                                            <a href="{{ route('stock-management.mine-vendors.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">Mine Vendors</a>
                                            <a href="{{ route('stock-management.condition-statuses.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">Condition Statuses</a>
                                        </div>
                                    </div>
                                </div>

                                <div class="relative group">
                                    <button class="inline-flex items-center px-3 py-2 border-b-2 {{ request()->routeIs('accounting.*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium transition-colors duration-200">
                                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                        </svg>
                                        Accounting
                                        <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                    <div class="absolute left-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                        <div class="py-1">
                                            <a href="{{ route('accounting.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">Accounting Dashboard</a>
                                            <a href="{{ route('accounting.chart-of-accounts.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">Chart of Accounts</a>
                                            <a href="{{ route('accounting.journal-entries.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">Journal Entries</a>
                                            <div class="border-t border-gray-100 my-1"></div>
                                            <a href="{{ route('accounting.trial-balance') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">Trial Balance</a>
                                            <a href="{{ route('accounting.balance-sheet') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">Balance Sheet</a>
                                            <a href="{{ route('accounting.income-statement') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">Income Statement</a>
                                            <a href="{{ route('accounting.general-ledger') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">General Ledger</a>
                                        </div>
                                    </div>
                                </div>

                                <a href="{{ route('stock-management.reports') }}" class="inline-flex items-center px-3 py-2 border-b-2 {{ request()->routeIs('stock-management.reports') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium transition-colors duration-200">
                                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Reports
                                </a>
                            </div>
                        </div>

                        <!-- Right side - Actions and User -->
                        <div class="flex items-center space-x-4">
                            <!-- Quick Actions Dropdown -->
                            <div class="relative group">
                                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center transition-colors duration-200 shadow-sm hover:shadow-md">
                                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Quick Actions
                                    <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                    <div class="py-1">
                                        <a href="{{ route('stock-management.stock-additions.create') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">Add Stock</a>
                                        <a href="{{ route('stock-management.stock-issued.create') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">Issue Stock</a>
                                        <a href="{{ route('stock-management.daily-production.create') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">Record Production</a>
                                        <a href="{{ route('stock-management.gate-pass.create') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">Create Gate Pass</a>
                                    </div>
                                </div>
                            </div>

                            <!-- User Dropdown -->
                            <div class="relative group">
                                <button class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                    <div class="h-8 w-8 bg-gray-300 rounded-full flex items-center justify-center">
                                        <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                    <span class="ml-2 text-gray-700 font-medium">{{ Auth::user()->name }}</span>
                                    <svg class="ml-1 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                    <div class="py-1">
                                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">Profile</a>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">Logout</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
