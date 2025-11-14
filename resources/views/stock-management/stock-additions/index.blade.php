<x-app-layout>
    <div class="py-8">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Stock Additions</h1>
                        <p class="mt-2 text-gray-600">Manage your stock inventory additions</p>
                    </div>
                    <div class="flex gap-3">
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('stock-management.stock-additions.edit-excel') }}" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg flex items-center transition-colors duration-200">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Excel Edit
                            </a>
                        @endif
                        <a href="{{ route('stock-management.stock-additions.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg flex items-center transition-colors duration-200">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Excel Add
                        </a>
                        <a href="{{ route('stock-management.stock-additions.create') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg flex items-center transition-colors duration-200">
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
                        <form method="GET" action="{{ route('stock-management.stock-additions.index') }}">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-4">
                                <!-- Product Filter -->
                                <div>
                                    <label for="product_id" class="block text-sm font-medium text-gray-700 mb-1">Product</label>
                                    <select name="product_id" id="product_id" class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm">
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
                                    <select name="vendor_id" id="vendor_id" class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm">
                                        <option value="">All Vendors</option>
                                        @foreach($vendors as $vendor)
                                            <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                                {{ $vendor->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Condition Filter -->
                                <div>
                                    <label for="condition_status" class="block text-sm font-medium text-gray-700 mb-1">Condition</label>
                                    <select name="condition_status" id="condition_status" class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm">
                                        <option value="">All Conditions</option>
                                        @foreach($conditions as $condition)
                                            <option value="{{ $condition->name }}" {{ request('condition_status') == $condition->name ? 'selected' : '' }}>
                                                {{ $condition->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Stock Status Filter -->
                                <div>
                                    <label for="stock_status" class="block text-sm font-medium text-gray-700 mb-1">Stock Status</label>
                                    <select name="stock_status" id="stock_status" class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm">
                                        <option value="">All Stock</option>
                                        <option value="available" {{ request('stock_status') == 'available' ? 'selected' : '' }}>Available</option>
                                        <option value="issued" {{ request('stock_status') == 'issued' ? 'selected' : '' }}>Issued</option>
                                    </select>
                                </div>

                                <!-- Records Per Page -->
                                <div>
                                    <label for="per_page" class="block text-sm font-medium text-gray-700 mb-1">Records Per Page</label>
                                    <select name="per_page" id="per_page" class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm">
                                        <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
                                        <option value="200" {{ request('per_page', 200) == '200' ? 'selected' : '' }}>200</option>
                                        <option value="500" {{ request('per_page') == '500' ? 'selected' : '' }}>500</option>
                                        <option value="1000" {{ request('per_page') == '1000' ? 'selected' : '' }}>1000</option>
                                    </select>
                                </div>
                            </div>

                            <div class="flex space-x-4">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                                    Apply Filters
                                </button>
                                @if(request()->hasAny(['product_id', 'vendor_id', 'condition_status', 'stock_status', 'per_page']))
                                    <a href="{{ route('stock-management.stock-additions.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                                        Clear
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>

                    <!-- Summary Cards -->
                    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                        <!-- Current Pieces -->
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-lg p-4 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium text-blue-600 uppercase tracking-wide">Current Pieces</p>
                                    <p class="text-2xl font-bold text-blue-900 mt-1">{{ number_format($totals['total_pieces']) }}</p>
                                </div>
                                <div class="bg-blue-200 rounded-full p-3">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Total Sqft -->
                        <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-lg p-4 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium text-green-600 uppercase tracking-wide">Total Sqft</p>
                                    <p class="text-2xl font-bold text-green-900 mt-1">{{ number_format($totals['total_sqft'], 2) }}</p>
                                </div>
                                <div class="bg-green-200 rounded-full p-3">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Total Weight -->
                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-lg p-4 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium text-purple-600 uppercase tracking-wide">Total Weight</p>
                                    <p class="text-2xl font-bold text-purple-900 mt-1">{{ number_format($totals['total_weight'], 2) }}</p>
                                    <p class="text-xs text-purple-600 mt-1">kg</p>
                                </div>
                                <div class="bg-purple-200 rounded-full p-3">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Available Pieces -->
                        <div class="bg-gradient-to-br from-orange-50 to-orange-100 border border-orange-200 rounded-lg p-4 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium text-orange-600 uppercase tracking-wide">Available Pieces</p>
                                    <p class="text-2xl font-bold text-orange-900 mt-1">{{ number_format($totals['available_pieces']) }}</p>
                                </div>
                                <div class="bg-orange-200 rounded-full p-3">
                                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Available Weight -->
                        <div class="bg-gradient-to-br from-red-50 to-red-100 border border-red-200 rounded-lg p-4 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium text-red-600 uppercase tracking-wide">Available Weight</p>
                                    <p class="text-2xl font-bold text-red-900 mt-1">{{ number_format($totals['available_weight'], 2) }}</p>
                                    <p class="text-xs text-red-600 mt-1">kg</p>
                                </div>
                                <div class="bg-red-200 rounded-full p-3">
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Available Sqft -->
                        <div class="bg-gradient-to-br from-teal-50 to-teal-100 border border-teal-200 rounded-lg p-4 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium text-teal-600 uppercase tracking-wide">Available Sqft</p>
                                    <p class="text-2xl font-bold text-teal-900 mt-1">{{ number_format($totals['available_sqft'], 2) }}</p>
                                </div>
                                <div class="bg-teal-200 rounded-full p-3">
                                    <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Records Info -->
                    <div class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                        <div class="flex justify-between items-center text-sm text-blue-700">
                            <div>
                                <span class="font-medium">Showing {{ $stockAdditions->count() }} of {{ $stockAdditions->total() }} records</span>
                                <span class="ml-4">({{ $stockAdditions->perPage() }} per page)</span>
                            </div>
                            <div class="text-xs text-blue-600">
                                Page {{ $stockAdditions->currentPage() }} of {{ $stockAdditions->lastPage() }}
                            </div>
                        </div>
                    </div>

                    <!-- DataTable -->
                    <div class="table-responsive">
                        <table id="stockAdditionsTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>PID</th>
                                    <th>Product</th>
                                    <th>Vendor</th>
                                    <th>Condition</th>
                                    <th>Particulars</th>
                                    <th>Size/Weight</th>
                                    <th>Diameter</th>
                                    <th>Pieces</th>
                                    <th>Sqft</th>
                                    <th>Available</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stockAdditions as $addition)
                                    <tr>
                                        <td>
                                            <span class="font-mono text-sm">{{ $addition->pid ?? 'N/A' }}</span>
                                        </td>
                                        <td>{{ $addition->product->name }}</td>
                                        <td>{{ $addition->mineVendor->name }}</td>
                                        <td>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($addition->condition_status === 'Block') bg-blue-100 text-blue-800
                                                @elseif($addition->condition_status === 'Slabs') bg-green-100 text-green-800
                                                @elseif($addition->condition_status === 'Polished') bg-purple-100 text-purple-800
                                                @elseif($addition->condition_status === 'Rough') bg-orange-100 text-orange-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ $addition->condition_status }}
                                            </span>
                                        </td>
                                        <td>{{ $addition->stone ?? 'N/A' }}</td>
                                        <td>
                                            @if($addition->condition_status === 'Block' || $addition->condition_status === 'Monuments')
                                                <div class="text-sm">
                                                    <div class="font-medium text-gray-900">{{ number_format($addition->weight, 2) }} kg</div>
                                                    <div class="text-xs text-gray-500">Per piece</div>
                                                </div>
                                            @else
                                                <div class="text-sm">
                                                @if($addition->length && $addition->height)
                                                        <div>{{ number_format($addition->length, 2) }} × {{ number_format($addition->height, 2) }} cm</div>
                                                        <div class="text-xs text-gray-500">{{ number_format($addition->length * $addition->height, 2) }} cm²</div>
                                                @else
                                                        <span class="text-gray-500">N/A</span>
                                                @endif
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($addition->diameter)
                                                <span class="text-sm">{{ $addition->diameter }}</span>
                                            @else
                                                <span class="text-gray-500 text-sm">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="font-semibold">{{ number_format($addition->total_pieces) }}</span>
                                        </td>
                                        <td>
                                            <span class="text-sm">{{ number_format($addition->total_sqft, 2) }}</span>
                                        </td>
                                        <td>
                                            <div class="text-sm">
                                                <div>{{ number_format($addition->available_pieces) }} pcs</div>
                                                @if($addition->condition_status === 'Block' || $addition->condition_status === 'Monuments')
                                                    <div class="text-xs text-gray-500">{{ number_format($addition->available_weight, 2) }} kg</div>
                                                @else
                                                    <div class="text-xs text-gray-500">{{ number_format($addition->available_sqft, 2) }} sqft</div>
                                                @endif
                                            </div>
                                        </td>
                                        <td>{{ $addition->date->format('M d, Y') }}</td>
                                        <td>
                                            <div class="flex space-x-2">
                                                <a href="{{ route('stock-management.stock-additions.show', $addition) }}" class="text-blue-600 hover:text-blue-900 text-sm">View</a>
                                                @if(auth()->user()->canEdit('stock-additions'))
                                                    <a href="{{ route('stock-management.stock-additions.edit', $addition) }}" class="text-indigo-600 hover:text-indigo-900 text-sm">Edit</a>
                                                @endif
                                                @if(auth()->user()->canDelete('stock-additions'))
                                                    <form method="POST" action="{{ route('stock-management.stock-additions.destroy', $addition) }}" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900 text-sm" onclick="return confirm('Are you sure you want to delete this stock addition?')">Delete</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Check if required libraries are loaded
            console.log('jQuery loaded:', typeof $ !== 'undefined');
            console.log('DataTables loaded:', typeof $.fn.DataTable !== 'undefined');
            console.log('Buttons loaded:', typeof $.fn.DataTable.Buttons !== 'undefined');
            console.log('JSZip loaded:', typeof JSZip !== 'undefined');
            
            $('#stockAdditionsTable').DataTable({
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
                        title: 'Stock Additions Report',
                        messageTop: 'Generated on: {{ now()->format("d/m/Y H:i") }}',
                        customize: function ( win ) {
                            // Hide action column (last column)
                            $(win.document.body).find('table th:last-child, table td:last-child').hide();
                            
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
                            $(win.document.head).find('title').text('Stock Additions Report');
                        }
                    },
                    {
                        extend: 'excel',
                        text: '📊 Excel',
                        className: 'btn btn-success',
                        title: 'Stock Additions Report - {{ now()->format("d-m-Y H-i") }}',
                        filename: 'Stock_Additions_{{ now()->format("d-m-Y_H-i") }}',
                        exportOptions: {
                            columns: ':not(:last-child)' // Exclude Actions column
                        },
                        customize: function ( xlsx ) {
                            var sheet = xlsx.xl.worksheets['sheet1.xml'];
                            
                            // Add header row styling
                            $('row:first c', sheet).attr('s', '2');
                            
                            // Set column widths (updated for new Condition column)
                            var colWidths = [12, 20, 20, 12, 15, 18, 12, 10, 12, 15, 12];
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
                pageLength: {{ $stockAdditions->perPage() }},
                order: [[10, 'desc']], // Sort by Date column (descending)
                columnDefs: [
                    { orderable: false, targets: 11 } // Disable sorting on Actions column
                ],
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                },
                initComplete: function() {
                    console.log('DataTable initialized');
                    console.log('Buttons container:', $('.dt-buttons').length);
                    console.log('Button elements:', $('.dt-buttons button').length);
                }
            });

            // Auto-submit form when filter selects change
            const filterSelects = document.querySelectorAll('#product_id, #vendor_id, #condition_status, #stock_status, #per_page');
            filterSelects.forEach(select => {
                select.addEventListener('change', function() {
                    this.form.submit();
                });
            });
        });
    </script>
</x-app-layout>