<x-app-layout>
    <div class="py-8">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Daily Production</h1>
                        <p class="mt-2 text-gray-600">Track daily production activities</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('stock-management.daily-production.create') }}" class="bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded-lg flex items-center transition-colors duration-200">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Single Add
                        </a>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                <div class="p-6">
                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Filters -->
                    <div class="mb-6 p-4 bg-white rounded-lg border border-gray-200">
                        <form method="GET" action="{{ route('stock-management.daily-production.index') }}">
                            <!-- Search Input -->
                            <div class="mb-4">
                                <input type="text" name="search" placeholder="Search by product, vendor, machine, operator, stone, size, notes..."
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                       value="{{ request('search') }}">
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-4">
                                <!-- Product Filter -->
                                <div>
                                    <label for="product_id" class="block text-sm font-medium text-gray-700 mb-1">Product</label>
                                    <select name="product_id" id="product_id" class="block w-full border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-lg shadow-sm">
                                        <option value="">All Products</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Vendor Filter -->
                                <div>
                                    <label for="vendor_id" class="block text-sm font-medium text-gray-700 mb-1">Vendor</label>
                                    <select name="vendor_id" id="vendor_id" class="block w-full border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-lg shadow-sm">
                                        <option value="">All Vendors</option>
                                        @foreach($vendors as $vendor)
                                            <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                                {{ $vendor->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Machine Filter -->
                                <div>
                                    <label for="machine_name" class="block text-sm font-medium text-gray-700 mb-1">Machine</label>
                                    <select name="machine_name" id="machine_name" class="block w-full border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-lg shadow-sm">
                                        <option value="">All Machines</option>
                                        @foreach($machines as $machine)
                                            <option value="{{ $machine }}" {{ request('machine_name') == $machine ? 'selected' : '' }}>
                                                {{ $machine }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Condition Filter -->
                                <div>
                                    <label for="condition_status" class="block text-sm font-medium text-gray-700 mb-1">Condition</label>
                                    <select name="condition_status" id="condition_status" class="block w-full border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-lg shadow-sm">
                                        <option value="">All Conditions</option>
                                        @foreach($conditionStatuses as $condition)
                                            <option value="{{ $condition }}" {{ request('condition_status') == $condition ? 'selected' : '' }}>
                                                {{ $condition }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Records Per Page -->
                                <div>
                                    <label for="per_page" class="block text-sm font-medium text-gray-700 mb-1">Records Per Page</label>
                                    <select name="per_page" id="per_page" class="block w-full border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-lg shadow-sm">
                                        <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
                                        <option value="200" {{ request('per_page', 200) == '200' ? 'selected' : '' }}>200</option>
                                        <option value="500" {{ request('per_page') == '500' ? 'selected' : '' }}>500</option>
                                        <option value="1000" {{ request('per_page') == '1000' ? 'selected' : '' }}>1000</option>
                                    </select>
                                </div>
                            </div>

                            <div class="flex space-x-4">
                                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                                    Apply Filters
                                </button>
                                @if(request()->hasAny(['search', 'product_id', 'vendor_id', 'machine_name', 'condition_status', 'per_page']))
                                    <a href="{{ route('stock-management.daily-production.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                                        Clear
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>

                    <!-- DataTable -->
                    <div class="table-responsive">
                        <table id="dailyProductionTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Stock PID</th>
                                    <th>Product</th>
                                    <th>Diameter</th>
                                    <th>Machine</th>
                                    <th>Operator</th>
                                    <th>Pieces Produced</th>
                                    <th>Sqft Produced</th>
                                    <th>Weight Produced</th>
                                    <th>Wastage</th>
                                    <th>Weight Wastage</th>
                                    <th>Condition</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dailyProduction as $production)
                                    <tr>
                                        <td>
                                            @if($production->status === 'open')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Open
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Closed
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="font-mono text-sm">{{ $production->stockAddition->pid ?? 'N/A' }}</span>
                                        </td>
                                        <td>{{ $production->stockAddition->product->name }}</td>
                                        <td>
                                            @if($production->stockAddition->diameter)
                                                <span class="text-sm">{{ $production->stockAddition->diameter }}</span>
                                            @else
                                                <span class="text-gray-500 text-sm">N/A</span>
                                            @endif
                                        </td>
                                        <td>{{ $production->machine->name ?? 'N/A' }}</td>
                                        <td>{{ $production->operator->name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="font-semibold">{{ number_format($production->total_pieces) }}</span>
                                        </td>
                                        <td>
                                            <span class="text-sm">{{ number_format($production->total_sqft, 2) }}</span>
                                        </td>
                                        <td>
                                            @if($production->total_weight > 0)
                                                <span class="text-sm">{{ number_format($production->total_weight, 2) }} kg</span>
                                            @else
                                                <span class="text-gray-500 text-sm">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-sm text-red-600">{{ number_format($production->wastage_sqft ?? 0, 2) }} sqft</span>
                                        </td>
                                        <td>
                                            @if($production->wastage_weight && $production->wastage_weight > 0)
                                                <span class="text-sm text-red-600">{{ number_format($production->wastage_weight, 2) }} kg</span>
                                            @else
                                                <span class="text-gray-500 text-sm">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($production->conditionStatus)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    {{ $production->conditionStatus->name }}
                                                </span>
                                            @else
                                                <span class="text-gray-500">N/A</span>
                                            @endif
                                        </td>
                                        <td>{{ $production->date->format('M d, Y') }}</td>
                                        <td>
                                            <div class="flex space-x-2">
                                                <a href="{{ route('stock-management.daily-production.show', $production) }}" class="text-blue-600 hover:text-blue-900 text-sm">View</a>
                                                
                                                @if($production->status === 'open')
                                                    @if(auth()->user()->canEdit('daily-production'))
                                                        <a href="{{ route('stock-management.daily-production.edit', $production) }}" class="text-green-600 hover:text-green-900 text-sm">Edit</a>
                                                    @endif
                                                    
                                                    <form method="POST" action="{{ route('stock-management.daily-production.close', $production) }}" class="inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="text-orange-600 hover:text-orange-900 text-sm" onclick="return confirm('Are you sure you want to close this production?')">Close</button>
                                                    </form>
                                                    
                                                    @if(auth()->user()->canDelete('daily-production'))
                                                        <form method="POST" action="{{ route('stock-management.daily-production.destroy', $production) }}" class="inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-red-600 hover:text-red-900 text-sm" onclick="return confirm('Are you sure you want to delete this production record?')">Delete</button>
                                                        </form>
                                                    @endif
                                                @else
                                                    @if(auth()->user()->canEdit('daily-production'))
                                                        <form method="POST" action="{{ route('stock-management.daily-production.open', $production) }}" class="inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="text-green-600 hover:text-green-900 text-sm" onclick="return confirm('Are you sure you want to open this production?')">Open</button>
                                                        </form>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($dailyProduction->hasPages())
                        <div class="mt-6">
                            {{ $dailyProduction->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#dailyProductionTable').DataTable({
                responsive: true,
                dom: '<"top"Blf>rtip',
                autoWidth: false,
                scrollX: false,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                buttons: [
                    {
                        extend: 'print',
                        text: '🖨️ Print',
                        className: 'btn btn-secondary',
                        title: 'Daily Production Report',
                        messageTop: 'Generated on: {{ now()->format("d/m/Y H:i") }}',
                        customize: function ( win ) {
                            // Hide action column and status column
                            $(win.document.body).find('table th:first-child, table td:first-child').hide(); // Hide Status column
                            $(win.document.body).find('table th:last-child, table td:last-child').hide(); // Hide Actions column
                            
                            // Add weight column header
                            $(win.document.body).find('table thead tr th').eq(8).text('Weight (kg)');
                            
                            // Style the table
                            $(win.document.body).find('table')
                                .addClass('compact')
                                .css({
                                    'font-size': '11px',
                                    'width': '100%'
                                });
                                
                            // Style headers
                            $(win.document.body).find('table thead th')
                                .css({
                                    'background-color': '#f8f9fa',
                                    'font-weight': 'bold',
                                    'border': '1px solid #000'
                                });
                                
                            // Style cells
                            $(win.document.body).find('table tbody td')
                                .css({
                                    'border': '1px solid #000'
                                });
                                
                            // Add page title
                            $(win.document.head).find('title').text('Daily Production Report');
                        }
                    },
                    {
                        extend: 'excel',
                        text: '📊 Excel',
                        className: 'btn btn-success',
                        title: 'Daily Production Report - {{ now()->format("d-m-Y H-i") }}',
                        filename: 'Daily_Production_{{ now()->format("d-m-Y_H-i") }}',
                        exportOptions: {
                            columns: ':not(:first-child, :last-child)' // Exclude Status and Actions columns
                        },
                        customize: function ( xlsx ) {
                            var sheet = xlsx.xl.worksheets['sheet1.xml'];
                            
                            // Add header row styling
                            $('row:first c', sheet).attr('s', '2');
                            
                            // Set column widths
                            var colWidths = [12, 20, 12, 18, 18, 15, 15, 12, 15, 15, 12, 12, 12];
                            $('col', sheet).each(function(index) {
                                if (index < colWidths.length) {
                                    $(this).attr('width', colWidths[index]);
                                }
                            });
                        }
                    },
                    {
                        extend: 'pdf',
                        text: '📄 PDF',
                        className: 'btn btn-danger'
                    }
                ],
                paging: false,
                searching: false,
                info: false,
                order: [[12, 'desc']], // Sort by Date column (descending)
                columnDefs: [
                    { orderable: false, targets: 13 } // Disable sorting on Actions column
                ]
            });

            // Auto-submit form when filter selects change
            const filterSelects = document.querySelectorAll('#product_id, #vendor_id, #machine_name, #condition_status, #per_page');
            filterSelects.forEach(select => {
                select.addEventListener('change', function() {
                    this.form.submit();
                });
            });
        });
    </script>
</x-app-layout>