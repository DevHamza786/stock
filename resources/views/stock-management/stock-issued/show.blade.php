<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Stock Issuance Details</h1>
                        <p class="mt-2 text-gray-600">View stock issuance information and related activities</p>
                    </div>
                    <div class="flex space-x-3">
                        <button onclick="printSlip()" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            <svg class="h-5 w-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            Print Slip
                        </button>
                        <button onclick="downloadPDF()" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            <svg class="h-5 w-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Download PDF
                        </button>
                        <a href="{{ route('stock-management.stock-issued.edit', $stockIssued) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            Edit Issuance
                        </a>
                        <a href="{{ route('stock-management.stock-issued.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            Back to List
                        </a>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Stock Issuance Information -->
                <div class="lg:col-span-2">
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-900">Issuance Information</h2>
                        </div>
                        <div class="p-6">
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Product</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $stockIssued->stockAddition->product->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Vendor</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $stockIssued->stockAddition->mineVendor->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Stone Type</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $stockIssued->stockAddition->stone }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Size (3D)</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $stockIssued->stockAddition->size_3d }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Quantity Issued</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ number_format($stockIssued->quantity_issued) }} pieces</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Square Footage Issued</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ number_format($stockIssued->sqft_issued, 2) }} sqft</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Purpose</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $stockIssued->purpose ?? 'Production' }}</dd>
                                </div>
                                @if($stockIssued->machine_name)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Machine Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $stockIssued->machine_name }}</dd>
                                </div>
                                @endif
                                @if($stockIssued->operator_name)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Operator Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $stockIssued->operator_name }}</dd>
                                </div>
                                @endif
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Issue Date</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $stockIssued->date->format('M d, Y') }}</dd>
                                </div>
                                @if($stockIssued->notes)
                                <div class="md:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Notes</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $stockIssued->notes }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <!-- Stock Source Information -->
                    <div class="mt-8 bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-900">Source Stock Information</h2>
                        </div>
                        <div class="p-6">
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Original Stock Addition</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <a href="{{ route('stock-management.stock-additions.show', $stockIssued->stockAddition) }}" class="text-blue-600 hover:text-blue-800">
                                            {{ $stockIssued->stockAddition->date->format('M d, Y') }} - {{ number_format($stockIssued->stockAddition->total_pieces) }} pieces
                                        </a>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Original Total Pieces</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ number_format($stockIssued->stockAddition->total_pieces) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Original Total Sqft</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ number_format($stockIssued->stockAddition->total_sqft, 2) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Remaining Pieces</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ number_format($stockIssued->stockAddition->available_pieces) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Remaining Sqft</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ number_format($stockIssued->stockAddition->available_sqft, 2) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Condition Status</dt>
                                    <dd class="mt-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($stockIssued->stockAddition->condition_status === 'Block') bg-blue-100 text-blue-800
                                            @elseif($stockIssued->stockAddition->condition_status === 'Slabs') bg-green-100 text-green-800
                                            @elseif($stockIssued->stockAddition->condition_status === 'Polished') bg-purple-100 text-purple-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ $stockIssued->stockAddition->condition_status }}
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Quick Actions -->
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                        </div>
                        <div class="p-6 space-y-3">
                            <a href="{{ route('stock-management.stock-issued.edit', $stockIssued) }}" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 text-center block">
                                Edit Issuance
                            </a>
                            <a href="{{ route('stock-management.daily-production.create', ['stock_addition_id' => $stockIssued->stockAddition->id]) }}" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 text-center block">
                                Record Production
                            </a>
                            <a href="{{ route('stock-management.gate-pass.create', ['stock_issued_id' => $stockIssued->id]) }}" class="w-full bg-orange-600 hover:bg-orange-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 text-center block">
                                Create Gate Pass
                            </a>
                        </div>
                    </div>

                    <!-- Issuance Status -->
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Issuance Status</h3>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">Issued</p>
                                    <p class="text-sm text-gray-500">{{ number_format($stockIssued->quantity_issued) }} pieces issued</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Related Information -->
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Related Information</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Product Category</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $stockIssued->stockAddition->product->category ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Vendor Contact</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $stockIssued->stockAddition->mineVendor->contact_person ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Vendor Phone</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $stockIssued->stockAddition->mineVendor->phone ?? 'N/A' }}</dd>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Activities -->
            @if($stockIssued->gatePass->count() > 0 || $stockIssued->stockAddition->dailyProduction->count() > 0)
            <div class="mt-8 bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">Related Activities</h2>
                </div>
                <div class="p-6">
                    <div class="flow-root">
                        <ul class="-mb-8">
                            @foreach($stockIssued->gatePass as $gatePass)
                            <li>
                                <div class="relative pb-8">
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-orange-500 flex items-center justify-center ring-8 ring-white">
                                                <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm2 6a2 2 0 114 0 2 2 0 01-4 0zm8 0a2 2 0 114 0 2 2 0 01-4 0z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-500">Gate pass created: <span class="font-medium text-gray-900">{{ $gatePass->gate_pass_number }}</span></p>
                                                <p class="text-sm text-gray-500">{{ $gatePass->destination ?? 'No destination' }} - {{ $gatePass->vehicle_number ?? 'No vehicle' }}</p>
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                {{ $gatePass->date->format('M d, Y') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endforeach

                            @foreach($stockIssued->stockAddition->dailyProduction->take(3) as $production)
                            <li>
                                <div class="relative pb-8">
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-purple-500 flex items-center justify-center ring-8 ring-white">
                                                <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-500">Production recorded: <span class="font-medium text-gray-900">{{ number_format($production->total_pieces) }} pieces</span></p>
                                                <p class="text-sm text-gray-500">{{ $production->machine_name }} - {{ $production->operator_name }}</p>
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                {{ $production->date->format('M d, Y') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Print Slip Section (Hidden by default) -->
    <div id="printSlip" class="hidden print:block print:p-0 print:m-0">
        <div class="print:bg-white print:text-black print:font-sans print:text-sm">
            <!-- Header -->
            <div class="print:border-b-2 print:border-gray-800 print:pb-4 print:mb-6">
                <div class="print:flex print:justify-between print:items-center">
                    <div>
                        <h1 class="print:text-2xl print:font-bold print:text-gray-900">STOCK ISSUANCE SLIP</h1>
                        <p class="print:text-sm print:text-gray-600">StockPro Management System</p>
                    </div>
                    <div class="print:text-right">
                        <p class="print:text-sm print:text-gray-600">Issue Date: {{ $stockIssued->date->format('M d, Y') }}</p>
                        <p class="print:text-sm print:text-gray-600">Issue ID: #{{ str_pad($stockIssued->id, 6, '0', STR_PAD_LEFT) }}</p>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="print:grid print:grid-cols-2 print:gap-8 print:mb-6">
                <!-- Issuance Details -->
                <div>
                    <h2 class="print:text-lg print:font-bold print:text-gray-900 print:mb-4 print:border-b print:border-gray-300 print:pb-2">ISSUANCE DETAILS</h2>
                    <table class="print:w-full print:text-sm">
                        <tr>
                            <td class="print:font-semibold print:py-1 print:pr-4">Product:</td>
                            <td class="print:py-1">{{ $stockIssued->stockAddition->product->name }}</td>
                        </tr>
                        <tr>
                            <td class="print:font-semibold print:py-1 print:pr-4">Vendor:</td>
                            <td class="print:py-1">{{ $stockIssued->stockAddition->mineVendor->name }}</td>
                        </tr>
                        <tr>
                            <td class="print:font-semibold print:py-1 print:pr-4">Stone Type:</td>
                            <td class="print:py-1">{{ $stockIssued->stockAddition->stone }}</td>
                        </tr>
                        <tr>
                            <td class="print:font-semibold print:py-1 print:pr-4">Size (3D):</td>
                            <td class="print:py-1">{{ $stockIssued->stockAddition->size_3d }}</td>
                        </tr>
                        <tr>
                            <td class="print:font-semibold print:py-1 print:pr-4">Quantity Issued:</td>
                            <td class="print:py-1 print:font-bold">{{ number_format($stockIssued->quantity_issued) }} pieces</td>
                        </tr>
                        <tr>
                            <td class="print:font-semibold print:py-1 print:pr-4">Sqft Issued:</td>
                            <td class="print:py-1 print:font-bold">{{ number_format($stockIssued->sqft_issued, 2) }} sqft</td>
                        </tr>
                        <tr>
                            <td class="print:font-semibold print:py-1 print:pr-4">Purpose:</td>
                            <td class="print:py-1">{{ $stockIssued->purpose ?? 'Production' }}</td>
                        </tr>
                        @if($stockIssued->machine_name)
                        <tr>
                            <td class="print:font-semibold print:py-1 print:pr-4">Machine Name:</td>
                            <td class="print:py-1">{{ $stockIssued->machine_name }}</td>
                        </tr>
                        @endif
                        @if($stockIssued->operator_name)
                        <tr>
                            <td class="print:font-semibold print:py-1 print:pr-4">Operator Name:</td>
                            <td class="print:py-1">{{ $stockIssued->operator_name }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="print:font-semibold print:py-1 print:pr-4">Issue Date:</td>
                            <td class="print:py-1">{{ $stockIssued->date->format('M d, Y') }}</td>
                        </tr>
                    </table>
                </div>

                <!-- Source Stock Information -->
                <div>
                    <h2 class="print:text-lg print:font-bold print:text-gray-900 print:mb-4 print:border-b print:border-gray-300 print:pb-2">SOURCE STOCK</h2>
                    <table class="print:w-full print:text-sm">
                        <tr>
                            <td class="print:font-semibold print:py-1 print:pr-4">Original Stock ID:</td>
                            <td class="print:py-1">#{{ str_pad($stockIssued->stockAddition->id, 6, '0', STR_PAD_LEFT) }}</td>
                        </tr>
                        <tr>
                            <td class="print:font-semibold print:py-1 print:pr-4">Original Date:</td>
                            <td class="print:py-1">{{ $stockIssued->stockAddition->date->format('M d, Y') }}</td>
                        </tr>
                        <tr>
                            <td class="print:font-semibold print:py-1 print:pr-4">Total Pieces:</td>
                            <td class="print:py-1">{{ number_format($stockIssued->stockAddition->total_pieces) }}</td>
                        </tr>
                        <tr>
                            <td class="print:font-semibold print:py-1 print:pr-4">Total Sqft:</td>
                            <td class="print:py-1">{{ number_format($stockIssued->stockAddition->total_sqft, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="print:font-semibold print:py-1 print:pr-4">Remaining Pieces:</td>
                            <td class="print:py-1 print:font-bold print:text-green-700">{{ number_format($stockIssued->stockAddition->available_pieces) }}</td>
                        </tr>
                        <tr>
                            <td class="print:font-semibold print:py-1 print:pr-4">Remaining Sqft:</td>
                            <td class="print:py-1 print:font-bold print:text-green-700">{{ number_format($stockIssued->stockAddition->available_sqft, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="print:font-semibold print:py-1 print:pr-4">Condition:</td>
                            <td class="print:py-1">{{ $stockIssued->stockAddition->condition_status }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Notes Section -->
            @if($stockIssued->notes)
            <div class="print:mb-6">
                <h2 class="print:text-lg print:font-bold print:text-gray-900 print:mb-2 print:border-b print:border-gray-300 print:pb-1">NOTES</h2>
                <p class="print:text-sm print:bg-gray-100 print:p-3 print:rounded">{{ $stockIssued->notes }}</p>
            </div>
            @endif

            <!-- Footer -->
            <div class="print:border-t-2 print:border-gray-800 print:pt-4 print:mt-6">
                <div class="print:flex print:justify-between print:items-center">
                    <div>
                        <p class="print:text-xs print:text-gray-600">Generated on: {{ now()->format('M d, Y H:i:s') }}</p>
                        <p class="print:text-xs print:text-gray-600">StockPro Management System</p>
                    </div>
                    <div class="print:text-right">
                        <p class="print:text-xs print:text-gray-600">Authorized by: ________________</p>
                        <p class="print:text-xs print:text-gray-600">Received by: ________________</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include jsPDF library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <script>
        function printSlip() {
            // Show the print slip section
            const printSection = document.getElementById('printSlip');
            printSection.classList.remove('hidden');

            // Print the slip
            window.print();

            // Hide the print section again after printing
            setTimeout(() => {
                printSection.classList.add('hidden');
            }, 1000);
        }

        function saveAsPDF() {
            // Show the print slip section
            const printSection = document.getElementById('printSlip');
            printSection.classList.remove('hidden');

            // Generate filename with stock name, date and time
            const stockName = '{{ $stockIssued->stockAddition->product->name }}'.replace(/[^a-zA-Z0-9]/g, '_');
            const issueId = '{{ str_pad($stockIssued->id, 6, "0", STR_PAD_LEFT) }}';
            const now = new Date();
            const dateStr = now.toISOString().split('T')[0]; // YYYY-MM-DD
            const timeStr = now.toTimeString().split(' ')[0].replace(/:/g, '-'); // HH-MM-SS
            const filename = `Stock_Issuance_${stockName}_${issueId}_${dateStr}_${timeStr}.pdf`;

            // Create a new window for PDF generation
            const printWindow = window.open('', '_blank');

            // Get the print section content
            const printContent = printSection.innerHTML;

            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Stock Issuance Slip - ${filename}</title>
                    <style>
                        @page {
                            size: A4;
                            margin: 20mm;
                        }
                        body {
                            font-family: Arial, sans-serif;
                            font-size: 12px;
                            line-height: 1.4;
                            color: #000;
                            background: white;
                            margin: 0;
                            padding: 0;
                        }
                        .print\\:bg-white {
                            background: white !important;
                        }
                        .print\\:text-black {
                            color: #000 !important;
                        }
                        .print\\:font-sans {
                            font-family: Arial, sans-serif !important;
                        }
                        .print\\:text-sm {
                            font-size: 12px !important;
                        }
                        .print\\:border-b-2 {
                            border-bottom: 2px solid #000 !important;
                        }
                        .print\\:border-gray-800 {
                            border-color: #000 !important;
                        }
                        .print\\:pb-4 {
                            padding-bottom: 15px !important;
                        }
                        .print\\:mb-6 {
                            margin-bottom: 20px !important;
                        }
                        .print\\:flex {
                            display: flex !important;
                        }
                        .print\\:justify-between {
                            justify-content: space-between !important;
                        }
                        .print\\:items-center {
                            align-items: center !important;
                        }
                        .print\\:text-2xl {
                            font-size: 24px !important;
                        }
                        .print\\:font-bold {
                            font-weight: bold !important;
                        }
                        .print\\:text-gray-900 {
                            color: #000 !important;
                        }
                        .print\\:text-gray-600 {
                            color: #333 !important;
                        }
                        .print\\:text-right {
                            text-align: right !important;
                        }
                        .print\\:grid {
                            display: grid !important;
                        }
                        .print\\:grid-cols-2 {
                            grid-template-columns: 1fr 1fr !important;
                        }
                        .print\\:gap-8 {
                            gap: 30px !important;
                        }
                        .print\\:text-lg {
                            font-size: 16px !important;
                        }
                        .print\\:mb-4 {
                            margin-bottom: 10px !important;
        }
                        .print\\:border-b {
                            border-bottom: 1px solid #333 !important;
                        }
                        .print\\:border-gray-300 {
                            border-color: #333 !important;
                        }
                        .print\\:pb-2 {
                            padding-bottom: 5px !important;
                        }
                        .print\\:w-full {
                            width: 100% !important;
                        }
                        .print\\:py-1 {
                            padding: 2px 0 !important;
                        }
                        .print\\:pr-4 {
                            padding-right: 10px !important;
                        }
                        .print\\:font-semibold {
                            font-weight: 600 !important;
                        }
                        .print\\:font-bold {
                            font-weight: bold !important;
                        }
                        .print\\:text-green-700 {
                            color: #059669 !important;
                        }
                        .print\\:mb-2 {
                            margin-bottom: 5px !important;
                        }
                        .print\\:pb-1 {
                            padding-bottom: 5px !important;
                        }
                        .print\\:bg-gray-100 {
                            background: #f5f5f5 !important;
                        }
                        .print\\:p-3 {
                            padding: 10px !important;
                        }
                        .print\\:rounded {
                            border-radius: 3px !important;
                        }
                        .print\\:border-t-2 {
                            border-top: 2px solid #000 !important;
                        }
                        .print\\:pt-4 {
                            padding-top: 15px !important;
                        }
                        .print\\:mt-6 {
                            margin-top: 20px !important;
                        }
                        .print\\:text-xs {
                            font-size: 10px !important;
                        }
                        table {
                            width: 100%;
                            font-size: 11px;
                        }
                        td {
                            padding: 2px 0;
                            vertical-align: top;
                        }
                        td:first-child {
                            font-weight: bold;
                            padding-right: 10px;
                            width: 40%;
                        }
                    </style>
                </head>
                <body>
                    ${printContent}
                </body>
                </html>
            `);

            printWindow.document.close();

            // Wait for content to load, then trigger print dialog with filename
            setTimeout(() => {
                printWindow.focus();

                // Try to set the filename for download
                if (printWindow.print) {
                    // For browsers that support filename in print dialog
                    printWindow.print();
                }

                // Close the window after a delay
                setTimeout(() => {
                    printWindow.close();
                }, 2000);
            }, 500);

            // Hide the print section again
            setTimeout(() => {
                printSection.classList.add('hidden');
            }, 1000);
        }

        function downloadPDF() {
            // Show the print slip section
            const printSection = document.getElementById('printSlip');
            printSection.classList.remove('hidden');

            // Generate filename with stock name, date and time
            const stockName = '{{ $stockIssued->stockAddition->product->name }}'.replace(/[^a-zA-Z0-9]/g, '_');
            const issueId = '{{ str_pad($stockIssued->id, 6, "0", STR_PAD_LEFT) }}';
            const now = new Date();
            const dateStr = now.toISOString().split('T')[0]; // YYYY-MM-DD
            const timeStr = now.toTimeString().split(' ')[0].replace(/:/g, '-'); // HH-MM-SS
            const filename = `Stock_Issuance_${stockName}_${issueId}_${dateStr}_${timeStr}.pdf`;

            // Create a temporary container with print-optimized content
            const tempContainer = document.createElement('div');
            tempContainer.style.position = 'absolute';
            tempContainer.style.left = '-9999px';
            tempContainer.style.top = '0';
            tempContainer.style.width = '210mm'; // A4 width
            tempContainer.style.backgroundColor = 'white';
            tempContainer.style.padding = '20mm';
            tempContainer.style.fontFamily = 'Arial, sans-serif';
            tempContainer.style.fontSize = '12px';
            tempContainer.style.lineHeight = '1.4';
            tempContainer.style.color = '#000';

            // Get data from PHP variables
            const stockData = {
                productName: '{{ $stockIssued->stockAddition->product->name }}',
                vendorName: '{{ $stockIssued->stockAddition->mineVendor->name }}',
                stoneType: '{{ $stockIssued->stockAddition->stone }}',
                size3d: '{{ $stockIssued->stockAddition->size_3d }}',
                quantityIssued: '{{ number_format($stockIssued->quantity_issued) }}',
                sqftIssued: '{{ number_format($stockIssued->sqft_issued, 2) }}',
                purpose: '{{ $stockIssued->purpose ?? 'Production' }}',
                machineName: '{{ $stockIssued->machine_name }}',
                operatorName: '{{ $stockIssued->operator_name }}',
                issueDate: '{{ $stockIssued->date->format('M d, Y') }}',
                originalStockId: '{{ str_pad($stockIssued->stockAddition->id, 6, '0', STR_PAD_LEFT) }}',
                originalDate: '{{ $stockIssued->stockAddition->date->format('M d, Y') }}',
                totalPieces: '{{ number_format($stockIssued->stockAddition->total_pieces) }}',
                totalSqft: '{{ number_format($stockIssued->stockAddition->total_sqft, 2) }}',
                remainingPieces: '{{ number_format($stockIssued->stockAddition->available_pieces) }}',
                remainingSqft: '{{ number_format($stockIssued->stockAddition->available_sqft, 2) }}',
                condition: '{{ $stockIssued->stockAddition->condition_status }}',
                notes: '{{ $stockIssued->notes }}',
                generatedOn: '{{ now()->format('M d, Y H:i:s') }}',
                issueId: '{{ str_pad($stockIssued->id, 6, '0', STR_PAD_LEFT) }}'
            };

            // Create the PDF content with exact print styling
            tempContainer.innerHTML = `
                <div style="border-bottom: 2px solid #000; padding-bottom: 15px; margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h1 style="font-size: 24px; font-weight: bold; margin: 0; color: #000;">STOCK ISSUANCE SLIP</h1>
                            <p style="margin: 5px 0; font-size: 12px; color: #333;">StockPro Management System</p>
                        </div>
                        <div style="text-align: right;">
                            <p style="margin: 2px 0; font-size: 12px; color: #333;">Issue Date: ${stockData.issueDate}</p>
                            <p style="margin: 2px 0; font-size: 12px; color: #333;">Issue ID: #${stockData.issueId}</p>
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 20px;">
                    <!-- Issuance Details -->
                    <div>
                        <h2 style="font-size: 16px; font-weight: bold; margin-bottom: 10px; border-bottom: 1px solid #333; padding-bottom: 5px; color: #000;">ISSUANCE DETAILS</h2>
                        <table style="width: 100%; font-size: 11px;">
                            <tr>
                                <td style="font-weight: bold; padding: 2px 0; padding-right: 10px; width: 40%;">Product:</td>
                                <td style="padding: 2px 0;">${stockData.productName}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; padding: 2px 0; padding-right: 10px;">Vendor:</td>
                                <td style="padding: 2px 0;">${stockData.vendorName}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; padding: 2px 0; padding-right: 10px;">Stone Type:</td>
                                <td style="padding: 2px 0;">${stockData.stoneType}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; padding: 2px 0; padding-right: 10px;">Size (3D):</td>
                                <td style="padding: 2px 0;">${stockData.size3d}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; padding: 2px 0; padding-right: 10px;">Quantity Issued:</td>
                                <td style="padding: 2px 0; font-weight: bold;">${stockData.quantityIssued} pieces</td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; padding: 2px 0; padding-right: 10px;">Sqft Issued:</td>
                                <td style="padding: 2px 0; font-weight: bold;">${stockData.sqftIssued} sqft</td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; padding: 2px 0; padding-right: 10px;">Purpose:</td>
                                <td style="padding: 2px 0;">${stockData.purpose}</td>
                            </tr>
                            ${stockData.machineName ? `
                            <tr>
                                <td style="font-weight: bold; padding: 2px 0; padding-right: 10px;">Machine Name:</td>
                                <td style="padding: 2px 0;">${stockData.machineName}</td>
                            </tr>
                            ` : ''}
                            ${stockData.operatorName ? `
                            <tr>
                                <td style="font-weight: bold; padding: 2px 0; padding-right: 10px;">Operator Name:</td>
                                <td style="padding: 2px 0;">${stockData.operatorName}</td>
                            </tr>
                            ` : ''}
                            <tr>
                                <td style="font-weight: bold; padding: 2px 0; padding-right: 10px;">Issue Date:</td>
                                <td style="padding: 2px 0;">${stockData.issueDate}</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Source Stock Information -->
                    <div>
                        <h2 style="font-size: 16px; font-weight: bold; margin-bottom: 10px; border-bottom: 1px solid #333; padding-bottom: 5px; color: #000;">SOURCE STOCK</h2>
                        <table style="width: 100%; font-size: 11px;">
                            <tr>
                                <td style="font-weight: bold; padding: 2px 0; padding-right: 10px; width: 40%;">Original Stock ID:</td>
                                <td style="padding: 2px 0;">#${stockData.originalStockId}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; padding: 2px 0; padding-right: 10px;">Original Date:</td>
                                <td style="padding: 2px 0;">${stockData.originalDate}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; padding: 2px 0; padding-right: 10px;">Total Pieces:</td>
                                <td style="padding: 2px 0;">${stockData.totalPieces}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; padding: 2px 0; padding-right: 10px;">Total Sqft:</td>
                                <td style="padding: 2px 0;">${stockData.totalSqft}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; padding: 2px 0; padding-right: 10px;">Remaining Pieces:</td>
                                <td style="padding: 2px 0; font-weight: bold; color: #059669;">${stockData.remainingPieces}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; padding: 2px 0; padding-right: 10px;">Remaining Sqft:</td>
                                <td style="padding: 2px 0; font-weight: bold; color: #059669;">${stockData.remainingSqft}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; padding: 2px 0; padding-right: 10px;">Condition:</td>
                                <td style="padding: 2px 0;">${stockData.condition}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                ${stockData.notes ? `
                <div style="margin-bottom: 20px;">
                    <h2 style="font-size: 16px; font-weight: bold; margin-bottom: 5px; border-bottom: 1px solid #333; padding-bottom: 5px; color: #000;">NOTES</h2>
                    <p style="background: #f5f5f5; padding: 10px; border-radius: 3px; margin: 0; font-size: 11px;">${stockData.notes}</p>
                </div>
                ` : ''}

                <div style="border-top: 2px solid #000; padding-top: 15px; margin-top: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <p style="margin: 2px 0; font-size: 10px; color: #333;">Generated on: ${stockData.generatedOn}</p>
                            <p style="margin: 2px 0; font-size: 10px; color: #333;">StockPro Management System</p>
                        </div>
                        <div style="text-align: right;">
                            <p style="margin: 2px 0; font-size: 10px; color: #333;">Authorized by: ________________</p>
                            <p style="margin: 2px 0; font-size: 10px; color: #333;">Received by: ________________</p>
                        </div>
                    </div>
                </div>
            `;

            document.body.appendChild(tempContainer);

            // Use html2canvas to capture the temporary container
            html2canvas(tempContainer, {
                scale: 2,
                useCORS: true,
                backgroundColor: '#ffffff',
                width: tempContainer.scrollWidth,
                height: tempContainer.scrollHeight,
                logging: false
            }).then(canvas => {
                // Create PDF using jsPDF
                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF('p', 'mm', 'a4');

                // Calculate dimensions
                const imgWidth = 210; // A4 width in mm
                const pageHeight = 295; // A4 height in mm
                const imgHeight = (canvas.height * imgWidth) / canvas.width;
                let heightLeft = imgHeight;

                // Convert canvas to image
                const imgData = canvas.toDataURL('image/png');

                // Add image to PDF
                pdf.addImage(imgData, 'PNG', 0, 0, imgWidth, imgHeight);
                heightLeft -= pageHeight;

                // Add new page if content is longer than one page
                while (heightLeft >= 0) {
                    pdf.addPage();
                    pdf.addImage(imgData, 'PNG', 0, -heightLeft, imgWidth, imgHeight);
                    heightLeft -= pageHeight;
                }

                // Download the PDF
                pdf.save(filename);

                // Clean up
                document.body.removeChild(tempContainer);

                // Hide the print section again
                setTimeout(() => {
                    printSection.classList.add('hidden');
                }, 1000);
            }).catch(error => {
                console.error('Error generating PDF:', error);
                alert('Error generating PDF. Please try again.');
                document.body.removeChild(tempContainer);
                printSection.classList.add('hidden');
            });
        }

        // Auto-print or download PDF if parameters are present
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('print') === '1') {
                setTimeout(() => {
                    printSlip();
                }, 500); // Small delay to ensure page is fully loaded
            } else if (urlParams.get('pdf') === '1') {
                setTimeout(() => {
                    downloadPDF();
                }, 500); // Small delay to ensure page is fully loaded
            }
        });

        // Add print styles
        const printStyles = `
            @media print {
                body * {
                    visibility: hidden;
                }
                #printSlip, #printSlip * {
                    visibility: visible;
                }
                #printSlip {
                    position: absolute;
                    left: 0;
                    top: 0;
                    width: 100%;
                    height: 100%;
                    background: white;
                    padding: 20px;
                    box-sizing: border-box;
                }
                .print\\:hidden {
                    display: none !important;
                }
            }
        `;

        // Add styles to head
        const styleSheet = document.createElement("style");
        styleSheet.type = "text/css";
        styleSheet.innerText = printStyles;
        document.head.appendChild(styleSheet);
    </script>
</x-app-layout>
