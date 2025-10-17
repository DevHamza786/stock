<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Production Report - {{ $dailyProduction->id }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { 
                font-size: 12px; 
                margin: 0;
                padding: 20px;
            }
            .page-break { 
                page-break-before: always; 
            }
            .no-print { 
                display: none !important; 
            }
            .bg-gray-50, .bg-green-50, .bg-blue-50 {
                background-color: #f9fafb !important;
                border: 1px solid #d1d5db !important;
            }
            .border {
                border: 1px solid #d1d5db !important;
            }
            .shadow-lg {
                box-shadow: none !important;
            }
        }
    </style>
</head>
<body class="bg-white">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8 border-b-2 border-gray-300 pb-4">
            <h1 class="text-2xl font-bold text-gray-900">Daily Production Report</h1>
            <p class="text-sm text-gray-600 mt-2">Production ID: {{ $dailyProduction->id }} | Date: {{ $dailyProduction->date->format('M d, Y') }}</p>
        </div>

        <!-- Production Information -->
        <div class="mb-6 border border-gray-300 rounded-lg p-4">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">Production Information</h2>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="font-medium text-gray-700">Machine:</span>
                    <span class="text-gray-900">{{ $dailyProduction->machine_name }}</span>
                </div>
                <div>
                    <span class="font-medium text-gray-700">Operator:</span>
                    <span class="text-gray-900">{{ $dailyProduction->operator_name }}</span>
                </div>
                <div>
                    <span class="font-medium text-gray-700">Date:</span>
                    <span class="text-gray-900">{{ $dailyProduction->date->format('M d, Y') }}</span>
                </div>
                <div>
                    <span class="font-medium text-gray-700">Status:</span>
                    <span class="text-gray-900">{{ ucfirst($dailyProduction->status) }}</span>
                </div>
                <div>
                    <span class="font-medium text-gray-700">Total Pieces:</span>
                    <span class="text-gray-900">{{ number_format($dailyProduction->total_pieces) }}</span>
                </div>
                <div>
                    <span class="font-medium text-gray-700">Total Sqft:</span>
                    <span class="text-gray-900">{{ number_format($dailyProduction->total_sqft, 2) }} sqft</span>
                </div>
                @if($dailyProduction->total_weight > 0)
                <div>
                    <span class="font-medium text-gray-700">Total Weight:</span>
                    <span class="text-gray-900">{{ number_format($dailyProduction->total_weight, 2) }} kg</span>
                </div>
                @endif
                @if($dailyProduction->notes)
                <div class="col-span-2">
                    <span class="font-medium text-gray-700">Notes:</span>
                    <span class="text-gray-900">{{ $dailyProduction->notes }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Source Stock Information -->
        @if($dailyProduction->stockAddition)
        <div class="mb-6 border border-gray-300 rounded-lg p-4">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">Source Stock Information</h2>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="font-medium text-gray-700">Stock PID:</span>
                    <span class="text-gray-900">{{ $dailyProduction->stockAddition->pid ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="font-medium text-gray-700">Product:</span>
                    <span class="text-gray-900">{{ $dailyProduction->stockAddition->product->name ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="font-medium text-gray-700">Vendor:</span>
                    <span class="text-gray-900">{{ $dailyProduction->stockAddition->mineVendor->name ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="font-medium text-gray-700">Condition:</span>
                    <span class="text-gray-900">{{ $dailyProduction->stockAddition->condition_status ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
        @endif

        <!-- Production Items -->
        <div class="mb-6 border border-gray-300 rounded-lg p-4">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">Production Items ({{ $dailyProduction->items->count() }})</h2>
            @if($dailyProduction->items->count() > 0)
                <div class="space-y-3">
                    @foreach($dailyProduction->items as $index => $item)
                        <div class="bg-gray-50 p-3 rounded border border-gray-200">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="font-semibold text-gray-900">Item #{{ $index + 1 }}</h3>
                                <div class="text-right text-sm">
                                    <div class="font-medium">{{ $item->total_pieces }} pieces</div>
                                    @php
                                        // Find the corresponding produced stock addition for this item
                                        $producedStock = $producedStockAdditions->where('stone', $item->product_name)
                                            ->where('condition_status', $item->condition_status)
                                            ->first();
                                    @endphp
                                    @if($producedStock)
                                        <div class="text-green-700">New Stock PID: {{ $producedStock->pid ?? 'N/A' }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div>
                                    <span class="font-medium text-gray-700">Product:</span>
                                    <span class="text-gray-900">{{ $item->product_name }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Condition:</span>
                                    <span class="text-gray-900">{{ $item->condition_status }}</span>
                                </div>
                                @if($item->size)
                                <div>
                                    <span class="font-medium text-gray-700">Size:</span>
                                    <span class="text-gray-900">{{ $item->size }}</span>
                                </div>
                                @endif
                                @if($item->diameter)
                                <div>
                                    <span class="font-medium text-gray-700">Diameter:</span>
                                    <span class="text-gray-900">{{ $item->diameter }}</span>
                                </div>
                                @endif
                                @if($item->total_sqft > 0)
                                <div>
                                    <span class="font-medium text-gray-700">Sqft:</span>
                                    <span class="text-gray-900">{{ number_format($item->total_sqft, 2) }}</span>
                                </div>
                                @endif
                                @if($item->total_weight > 0)
                                <div>
                                    <span class="font-medium text-gray-700">Weight:</span>
                                    <span class="text-gray-900">{{ number_format($item->total_weight, 2) }} kg</span>
                                </div>
                                @endif
                                @if($item->special_status)
                                <div class="col-span-2">
                                    <span class="font-medium text-gray-700">Special Status:</span>
                                    <span class="text-gray-900">{{ $item->special_status }}</span>
                                </div>
                                @endif
                            </div>

                            <!-- New Stock Information for Print -->
                            @if($producedStock)
                            <div class="mt-3 p-2 bg-green-50 border border-green-200 rounded">
                                <h5 class="text-xs font-semibold text-green-900 mb-1">New Stock Created:</h5>
                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    <div>
                                        <span class="font-medium text-green-700">Stock PID:</span>
                                        <span class="text-green-900 font-mono">{{ $producedStock->pid ?? 'N/A' }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-green-700">Available Pieces:</span>
                                        <span class="text-green-900">{{ number_format($producedStock->available_pieces) }}</span>
                                    </div>
                                    @if($producedStock->available_sqft > 0)
                                    <div>
                                        <span class="font-medium text-green-700">Available Sqft:</span>
                                        <span class="text-green-900">{{ number_format($producedStock->available_sqft, 2) }} sqft</span>
                                    </div>
                                    @endif
                                    @if($producedStock->available_weight > 0)
                                    <div>
                                        <span class="font-medium text-green-700">Available Weight:</span>
                                        <span class="text-green-900">{{ number_format($producedStock->available_weight, 2) }} kg</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-4">No production items found.</p>
            @endif
        </div>

        <!-- Produced Stock Additions -->
        @if($producedStockAdditions->count() > 0)
        <div class="mb-6 border border-gray-300 rounded-lg p-4">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">Produced Stock Additions ({{ $producedStockAdditions->count() }})</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach($producedStockAdditions as $stockAddition)
                    <div class="bg-green-50 p-3 rounded border border-green-200">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="font-semibold text-green-900">Stock PID: {{ $stockAddition->pid ?? 'N/A' }}</h3>
                            <span class="bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded">
                                {{ $stockAddition->available_pieces }} pieces
                            </span>
                        </div>
                        <div class="space-y-1 text-sm">
                            <div>
                                <span class="font-medium text-green-700">Product:</span>
                                <span class="text-green-900">{{ $stockAddition->stone }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-green-700">Condition:</span>
                                <span class="text-green-900">{{ $stockAddition->condition_status }}</span>
                            </div>
                            @if($stockAddition->available_sqft > 0)
                            <div>
                                <span class="font-medium text-green-700">Available Sqft:</span>
                                <span class="text-green-900">{{ number_format($stockAddition->available_sqft, 2) }} sqft</span>
                            </div>
                            @endif
                            @if($stockAddition->available_weight > 0)
                            <div>
                                <span class="font-medium text-green-700">Available Weight:</span>
                                <span class="text-green-900">{{ number_format($stockAddition->available_weight, 2) }} kg</span>
                            </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Footer -->
        <div class="mt-8 pt-4 border-t border-gray-300 text-center text-xs text-gray-500">
            <p>Generated on {{ now()->format('M d, Y H:i:s') }}</p>
            <p>Daily Production Management System</p>
        </div>
    </div>

    <!-- Print Button -->
    <div class="fixed bottom-4 right-4 no-print">
        <button onclick="window.print()" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg shadow-lg transition-colors duration-200 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            Print Report
        </button>
    </div>
</body>
</html>
