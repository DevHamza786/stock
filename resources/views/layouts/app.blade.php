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

        <!-- DataTables CSS and JS -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

        <!-- Excel Export Libraries -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

        <!-- DataTables Custom Styles -->
        <style>
            /* Enhanced button styling */
            .dt-buttons {
                margin-bottom: 1rem;
                display: flex !important;
                gap: 0.5rem;
                flex-wrap: wrap;
                visibility: visible !important;
                opacity: 1 !important;
            }

            .dt-buttons .btn {
                padding: 8px 16px;
                border-radius: 6px;
                font-weight: 500;
                font-size: 14px;
                transition: all 0.2s ease;
                border: 1px solid transparent;
                cursor: pointer;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 6px;
            }

            .dt-buttons .btn:hover {
                transform: translateY(-1px);
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            }

            .dt-buttons .btn-secondary {
                background-color: #6c757d;
                color: white;
                border-color: #6c757d;
            }

            .dt-buttons .btn-secondary:hover {
                background-color: #5a6268;
                border-color: #545b62;
                color: white;
            }

            .dt-buttons .btn-success {
                background-color: #28a745;
                color: white;
                border-color: #28a745;
            }

            .dt-buttons .btn-success:hover {
                background-color: #218838;
                border-color: #1e7e34;
                color: white;
            }

            .dt-buttons .btn-danger {
                background-color: #dc3545;
                color: white;
                border-color: #dc3545;
            }

            .dt-buttons .btn-danger:hover {
                background-color: #c82333;
                border-color: #bd2130;
                color: white;
            }

            /* Force button visibility */
            .dt-button {
                display: inline-block !important;
                visibility: visible !important;
                opacity: 1 !important;
            }

            /* Clean buttons area */
            .dt-buttons {
                min-height: 40px !important;
            }

            /* Top container styling */
            .dataTables_wrapper .top {
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                margin-bottom: 15px !important;
                flex-wrap: wrap !important;
                gap: 15px !important;
            }

            .dataTables_wrapper .top .dt-buttons {
                order: 1 !important;
                margin-bottom: 0 !important;
            }

            .dataTables_wrapper .top .dataTables_length {
                order: 2 !important;
                margin-bottom: 0 !important;
            }

            .dataTables_wrapper .top .dataTables_filter {
                order: 3 !important;
                margin-bottom: 0 !important;
            }

            /* Professional pagination styling */
            .dataTables_paginate {
                margin-top: 1rem;
                text-align: right;
            }

            .dataTables_paginate .paginate_button {
                padding: 8px 12px;
                margin: 0 2px;
                border: 1px solid #d1d5db;
                border-radius: 6px;
                background-color: #ffffff;
                color: #374151;
                text-decoration: none;
                font-weight: 500;
                transition: all 0.2s ease;
                cursor: pointer;
                display: inline-block;
            }

            .dataTables_paginate .paginate_button:hover {
                background-color: #f3f4f6;
                border-color: #9ca3af;
                color: #1f2937;
            }

            .dataTables_paginate .paginate_button.current {
                background-color: #3b82f6;
                border-color: #3b82f6;
                color: #ffffff;
                font-weight: 600;
            }

            .dataTables_paginate .paginate_button.disabled {
                color: #9ca3af;
                background-color: #f9fafb;
                border-color: #e5e7eb;
                cursor: not-allowed;
                opacity: 0.5;
            }

            .dataTables_paginate .paginate_button.disabled:hover {
                background-color: #f9fafb;
                border-color: #e5e7eb;
                color: #9ca3af;
            }

            /* Hide print-only content by default */
            .print-only-content {
                display: none !important;
            }

            /* Professional DataTables Styling */
            .dataTables_wrapper {
                width: 100% !important;
                background-color: #ffffff !important;
                border-radius: 8px !important;
                padding: 20px !important;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important;
                border: 1px solid #e5e7eb !important;
            }

            .dataTables_wrapper .dataTables_scroll {
                width: 100% !important;
            }

            /* Professional table styling */
            .dataTables_wrapper .dataTable {
                width: 100% !important;
                border-collapse: collapse !important;
                border: 1px solid #e5e7eb !important;
                background-color: #ffffff !important;
                color: #374151 !important;
            }

            .dataTables_wrapper .dataTable thead th {
                background-color: #f8f9fa !important;
                color: #374151 !important;
                font-weight: 600 !important;
                padding: 12px 8px !important;
                text-align: center !important;
                border-bottom: 1px solid #e5e7eb !important;
                border-right: 1px solid #e5e7eb !important;
                font-size: 14px !important;
                text-transform: uppercase !important;
                letter-spacing: 0.5px !important;
            }

            .dataTables_wrapper .dataTable thead th:last-child {
                border-right: none !important;
            }

            .dataTables_wrapper .dataTable tbody td {
                background-color: #ffffff !important;
                color: #374151 !important;
                padding: 12px 8px !important;
                vertical-align: middle !important;
                border-bottom: 1px solid #e5e7eb !important;
                border-right: 1px solid #e5e7eb !important;
                font-size: 14px !important;
            }

            .dataTables_wrapper .dataTable tbody td:last-child {
                border-right: none !important;
            }

            .dataTables_wrapper .dataTable tbody tr:nth-child(even) {
                background-color: #f8f9fa !important;
            }

            .dataTables_wrapper .dataTable tbody tr:nth-child(odd) {
                background-color: #ffffff !important;
            }

            .dataTables_wrapper .dataTable tbody tr:hover {
                background-color: #f3f4f6 !important;
            }

            .dataTables_wrapper .dataTable tbody tr:hover td {
                background-color: #f3f4f6 !important;
            }

            /* Professional dropdown styling */
            .dataTables_length,
            .dataTables_filter {
                color: #374151 !important;
                margin-bottom: 15px !important;
            }

            .dataTables_length select {
                background-color: #ffffff !important;
                color: #374151 !important;
                border: 1px solid #d1d5db !important;
                border-radius: 6px !important;
                padding: 8px 12px !important;
                font-size: 14px !important;
                font-weight: 500 !important;
                min-width: 70px !important;
                cursor: pointer !important;
                appearance: none !important;
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e") !important;
                background-position: right 8px center !important;
                background-repeat: no-repeat !important;
                background-size: 16px !important;
                padding-right: 32px !important;
            }

            .dataTables_length select:focus {
                border-color: #3b82f6 !important;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
                outline: none !important;
            }

            .dataTables_filter input {
                background-color: #ffffff !important;
                color: #374151 !important;
                border: 1px solid #d1d5db !important;
                border-radius: 6px !important;
                padding: 8px 12px !important;
                font-size: 14px !important;
            }

            .dataTables_filter input:focus {
                border-color: #3b82f6 !important;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
                outline: none !important;
            }

            /* Professional info styling */
            .dataTables_info {
                color: #6b7280 !important;
                margin-top: 15px !important;
                font-size: 14px !important;
            }

            /* Full width container */
            .table-responsive {
                width: 100% !important;
                background-color: #ffffff !important;
                border-radius: 8px !important;
            }

            /* Remove default table styling overrides */
            .table.table-striped.table-hover {
                border: 1px solid #e5e7eb !important;
                background-color: #ffffff !important;
            }

            .table.table-striped.table-hover thead th {
                border: 1px solid #e5e7eb !important;
                background-color: #f8f9fa !important;
                color: #374151 !important;
            }

            .table.table-striped.table-hover tbody td {
                border: 1px solid #e5e7eb !important;
                background-color: #ffffff !important;
                color: #374151 !important;
            }

            /* Dark theme action buttons */
            .table tbody td a {
                color: #007bff !important;
                text-decoration: none;
                font-weight: 500;
                transition: color 0.2s ease;
            }

            .table tbody td a:hover {
                color: #0056b3 !important;
                text-decoration: underline;
            }

            .table tbody td .text-indigo-600 {
                color: #6f42c1 !important;
            }

            .table tbody td .text-indigo-600:hover {
                color: #5a2d91 !important;
            }

            .table tbody td .text-red-600 {
                color: #dc3545 !important;
            }

            .table tbody td .text-red-600:hover {
                color: #c82333 !important;
            }

            .table tbody td .text-green-600 {
                color: #28a745 !important;
            }

            .table tbody td .text-green-600:hover {
                color: #218838 !important;
            }

            /* Print-specific styles */
            @media print {
                @page {
                    size: A4 landscape;
                    margin: 0.5in;
                }

                /* Hide action columns in print */
                .dataTable th:last-child,
                .dataTable td:last-child {
                    display: none !important;
                }

                /* Hide DataTables controls in print */
                .dt-buttons,
                .dataTables_length,
                .dataTables_filter,
                .dataTables_info,
                .dataTables_paginate,
                .dataTables_processing {
                    display: none !important;
                }

                /* Ensure table is full width in print */
                .dataTable {
                    width: 100% !important;
                    font-size: 11px !important;
                }

                .dataTable th,
                .dataTable td {
                    padding: 4px 6px !important;
                }

                /* Print header */
                .dataTables_wrapper::before {
                    content: "Stock Management Report - Generated on: " attr(data-print-date);
                    display: block;
                    text-align: center;
                    font-size: 16px;
                    font-weight: bold;
                    margin-bottom: 20px;
                    border-bottom: 2px solid #000;
                    padding-bottom: 10px;
                }
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-50">
        <div class="min-h-screen">
            <!-- Navigation -->
            <nav class="bg-white shadow-lg border-b border-gray-200">
                <div class="w-full px-4 sm:px-6 lg:px-8">
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
                                            <a href="{{ route('master-data.machines.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">Machines</a>
                                            <a href="{{ route('master-data.operators.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">Operators</a>
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

                                <a href="{{ route('database-viewer.index') }}" class="inline-flex items-center px-3 py-2 border-b-2 {{ request()->routeIs('database-viewer.*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium transition-colors duration-200">
                                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                                    </svg>
                                    Database Viewer
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
