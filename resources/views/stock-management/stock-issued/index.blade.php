<x-app-layout>
    <div class="py-8">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Stock Issued</h1>
                        <p class="mt-2 text-gray-600">Track stock issued for production</p>
                    </div>
                    <a href="{{ route('stock-management.stock-issued.create') }}" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg flex items-center transition-colors duration-200">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Issue New Stock
                    </a>
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
                        <form method="GET" action="{{ route('stock-management.stock-issued.index') }}">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-4">
                                <!-- Product Filter -->
                                <div>
                                    <label for="product_id" class="block text-sm font-medium text-gray-700 mb-1">Product</label>
                                    <select name="product_id" id="product_id" class="block w-full border-gray-300 focus:border-green-500 focus:ring-green-500 rounded-lg shadow-sm">
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
                                    <select name="vendor_id" id="vendor_id" class="block w-full border-gray-300 focus:border-green-500 focus:ring-green-500 rounded-lg shadow-sm">
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
                                    <select name="condition_status" id="condition_status" class="block w-full border-gray-300 focus:border-green-500 focus:ring-green-500 rounded-lg shadow-sm">
                                        <option value="">All Conditions</option>
                                        @foreach($conditions as $condition)
                                            <option value="{{ $condition->name }}" {{ request('condition_status') == $condition->name ? 'selected' : '' }}>
                                                {{ $condition->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Purpose Filter -->
                                <div>
                                    <label for="purpose" class="block text-sm font-medium text-gray-700 mb-1">Purpose</label>
                                    <select name="purpose" id="purpose" class="block w-full border-gray-300 focus:border-green-500 focus:ring-green-500 rounded-lg shadow-sm">
                                        <option value="">All Purposes</option>
                                        @foreach($purposes as $purpose)
                                            <option value="{{ $purpose }}" {{ request('purpose') == $purpose ? 'selected' : '' }}>
                                                {{ $purpose }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Records Per Page -->
                                <div>
                                    <label for="per_page" class="block text-sm font-medium text-gray-700 mb-1">Records Per Page</label>
                                    <select name="per_page" id="per_page" class="block w-full border-gray-300 focus:border-green-500 focus:ring-green-500 rounded-lg shadow-sm">
                                        <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
                                        <option value="200" {{ request('per_page', 200) == '200' ? 'selected' : '' }}>200</option>
                                        <option value="500" {{ request('per_page') == '500' ? 'selected' : '' }}>500</option>
                                        <option value="1000" {{ request('per_page') == '1000' ? 'selected' : '' }}>1000</option>
                                    </select>
                                </div>
                            </div>

                            <div class="flex space-x-4">
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                                    Apply Filters
                                </button>
                                @if(request()->hasAny(['product_id', 'vendor_id', 'condition_status', 'purpose', 'per_page']))
                                    <a href="{{ route('stock-management.stock-issued.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                                        Clear
                                </a>
                                @endif
                            </div>
                        </form>
                    </div>

                    <!-- Records Info -->
                    <div class="mb-4 p-3 bg-green-50 rounded-lg border border-green-200">
                        <div class="flex justify-between items-center text-sm text-green-700">
                            <div>
                                <span class="font-medium">Showing {{ $stockIssued->count() }} of {{ $stockIssued->total() }} records</span>
                                <span class="ml-4">({{ $stockIssued->perPage() }} per page)</span>
                            </div>
                            <div class="text-xs text-green-600">
                                Page {{ $stockIssued->currentPage() }} of {{ $stockIssued->lastPage() }}
                            </div>
                        </div>
                    </div>

                    <!-- DataTable -->
                    <div class="table-responsive">
                        <table id="stockIssuedTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Stock PID</th>
                                    <th>Product</th>
                                    <th>Diameter</th>
                                    <th>Machine</th>
                                    <th>Operator</th>
                                    <th>Pieces Issued</th>
                                    <th>Weight/Sqft Issued</th>
                                    <th>Purpose</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stockIssued as $issue)
                                    <tr>
                                        <td>
                                            <span class="font-mono text-sm">{{ $issue->stockAddition->pid ?? 'N/A' }}</span>
                                        </td>
                                        <td>{{ $issue->stockAddition->product->name }}</td>
                                        <td>
                                            @if($issue->stockAddition->diameter)
                                                <span class="text-sm">{{ $issue->stockAddition->diameter }}</span>
                                            @else
                                                <span class="text-gray-500 text-sm">N/A</span>
                                            @endif
                                        </td>
                                        <td>{{ $issue->machine->name ?? 'N/A' }}</td>
                                        <td>{{ $issue->operator->name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="font-semibold">{{ number_format($issue->quantity_issued) }}</span>
                                        </td>
                                        <td>
                                            @if(in_array(strtolower(trim($issue->stockAddition->condition_status)), ['block', 'monuments']))
                                                <span class="text-sm font-medium text-blue-600">{{ number_format($issue->weight_issued, 2) }} kg</span>
                                            @else
                                                <span class="text-sm">{{ number_format($issue->sqft_issued, 2) }} sqft</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $issue->purpose }}
                                            </span>
                                        </td>
                                        <td>{{ $issue->date->format('M d, Y') }}</td>
                                        <td>
                                            <div class="flex space-x-2">
                                                <a href="{{ route('stock-management.stock-issued.show', $issue) }}" class="text-blue-600 hover:text-blue-900 text-sm">View</a>
                                                <a href="{{ route('stock-management.stock-issued.edit', $issue) }}" class="text-indigo-600 hover:text-indigo-900 text-sm">Edit</a>
                                                <form method="POST" action="{{ route('stock-management.stock-issued.destroy', $issue) }}" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 text-sm" onclick="return confirm('Are you sure you want to delete this stock issue?')">Delete</button>
                                                </form>
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
            $('#stockIssuedTable').DataTable({
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
                        title: 'Stock Issued Report',
                        messageTop: 'Generated on: {{ now()->format("d/m/Y H:i") }}',
                        customize: function ( win ) {
                            // Hide action column
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
                            $(win.document.head).find('title').text('Stock Issued Report');
                        }
                    },
                    {
                        extend: 'excel',
                        text: '📊 Excel',
                        className: 'btn btn-success',
                        title: 'Stock Issued Report - {{ now()->format("d-m-Y H-i") }}',
                        filename: 'Stock_Issued_{{ now()->format("d-m-Y_H-i") }}',
                        exportOptions: {
                            columns: ':not(:last-child)' // Exclude Actions column
                        },
                        customize: function ( xlsx ) {
                            var sheet = xlsx.xl.worksheets['sheet1.xml'];
                            
                            // Add header row styling
                            $('row:first c', sheet).attr('s', '2');
                            
                            // Set column widths
                            var colWidths = [12, 20, 12, 18, 18, 15, 15, 15, 12];
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
                pageLength: {{ $stockIssued->perPage() }},
                order: [[8, 'desc']], // Sort by Date column (descending)
                columnDefs: [
                    { orderable: false, targets: 9 } // Disable sorting on Actions column
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
                }
            });

            // Auto-submit form when filter selects change
            const filterSelects = document.querySelectorAll('#product_id, #vendor_id, #condition_status, #purpose, #per_page');
            filterSelects.forEach(select => {
                select.addEventListener('change', function() {
                    this.form.submit();
                });
            });
        });
    </script>
</x-app-layout>