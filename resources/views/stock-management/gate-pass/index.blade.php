<x-app-layout>
    <div class="py-8">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Gate Pass</h1>
                        <p class="mt-2 text-gray-600">Manage gate passes for stock dispatch</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('stock-management.gate-pass.create-excel') }}" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg flex items-center transition-colors duration-200">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Excel Add
                        </a>
                        <a href="{{ route('stock-management.gate-pass.create') }}" class="bg-orange-600 hover:bg-orange-700 text-white font-semibold py-2 px-4 rounded-lg flex items-center transition-colors duration-200">
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
                        <form method="GET" action="{{ route('stock-management.gate-pass.index') }}">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                                <!-- Product Filter -->
                                <div>
                                    <label for="product_id" class="block text-sm font-medium text-gray-700 mb-1">Product</label>
                                    <select name="product_id" id="product_id" class="block w-full border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-lg shadow-sm">
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
                                    <select name="vendor_id" id="vendor_id" class="block w-full border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-lg shadow-sm">
                                        <option value="">All Vendors</option>
                                        @foreach($vendors as $vendor)
                                            <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                                {{ $vendor->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Status Filter -->
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                    <select name="status" id="status" class="block w-full border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-lg shadow-sm">
                                        <option value="">All Statuses</option>
                                        @foreach($statuses as $status)
                                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                                {{ $status }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Destination Filter -->
                                <div>
                                    <label for="destination" class="block text-sm font-medium text-gray-700 mb-1">Destination</label>
                                    <select name="destination" id="destination" class="block w-full border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-lg shadow-sm">
                                        <option value="">All Destinations</option>
                                        @foreach($destinations as $destination)
                                            <option value="{{ $destination }}" {{ request('destination') == $destination ? 'selected' : '' }}>
                                                {{ $destination }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="flex space-x-4">
                                <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                                    Apply Filters
                                </button>
                                @if(request()->hasAny(['product_id', 'vendor_id', 'status', 'destination']))
                                    <a href="{{ route('stock-management.gate-pass.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                                        Clear
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>

                    <!-- DataTable -->
                    <div class="table-responsive">
                        <table id="gatePassTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Gate Pass #</th>
                                    <th>Client</th>
                                    <th>Items</th>
                                    <th>Total Quantity</th>
                                    <th>Total Sqft</th>
                                    <th>Destination</th>
                                    <th>Vehicle</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($gatePasses as $gatePass)
                                    <tr>
                                        <td>
                                            <span class="font-mono text-sm font-semibold">{{ $gatePass->gate_pass_number }}</span>
                                        </td>
                                        <td>
                                            @if($gatePass->client_name)
                                                <div class="text-sm">
                                                    <div class="font-medium">{{ $gatePass->client_name }}</div>
                                                    @if($gatePass->client_number)
                                                        <div class="text-gray-500 text-xs">{{ $gatePass->client_number }}</div>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-gray-500 text-sm">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($gatePass->items->count() > 0)
                                                <div class="text-sm">
                                                    <div class="font-medium">{{ $gatePass->items->count() }} items</div>
                                                    <div class="text-gray-500 text-xs">
                                                        @foreach($gatePass->items->take(2) as $item)
                                                            {{ $item->stockAddition->product->name ?? 'N/A' }}{{ !$loop->last ? ', ' : '' }}
                                                        @endforeach
                                                        @if($gatePass->items->count() > 2)
                                                            +{{ $gatePass->items->count() - 2 }} more
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-gray-500 text-sm">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="font-semibold">{{ number_format($gatePass->quantity_issued) }}</span>
                                        </td>
                                        <td>
                                            <span class="font-semibold">{{ number_format($gatePass->sqft_issued, 2) }}</span>
                                        </td>
                                        <td>{{ $gatePass->destination ?? 'N/A' }}</td>
                                        <td>
                                            <span class="font-mono text-sm">{{ $gatePass->vehicle_number ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $gatePass->status === 'Dispatched' ? 'bg-green-100 text-green-800' : ($gatePass->status === 'Approved' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                {{ $gatePass->status ?? 'Pending' }}
                                            </span>
                                        </td>
                                        <td>{{ $gatePass->date->format('M d, Y') }}</td>
                                        <td>
                                            <div class="flex space-x-2">
                                                <a href="{{ route('stock-management.gate-pass.show', $gatePass) }}" class="text-blue-600 hover:text-blue-900 text-sm">View</a>
                                                <a href="{{ route('stock-management.gate-pass.edit', $gatePass) }}" class="text-green-600 hover:text-green-900 text-sm">Edit</a>
                                                <form method="POST" action="{{ route('stock-management.gate-pass.destroy', $gatePass) }}" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 text-sm" onclick="return confirm('Are you sure you want to delete this gate pass?')">Delete</button>
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
            $('#gatePassTable').DataTable({
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
                        title: 'Gate Pass Report',
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
                            $(win.document.head).find('title').text('Gate Pass Report');
                        }
                    },
                    {
                        extend: 'excel',
                        text: '📊 Excel',
                        className: 'btn btn-success',
                        title: 'Gate Pass Report - {{ now()->format("d-m-Y H-i") }}',
                        filename: 'Gate_Pass_{{ now()->format("d-m-Y_H-i") }}',
                        exportOptions: {
                            columns: ':not(:last-child)' // Exclude Actions column
                        },
                        customize: function ( xlsx ) {
                            var sheet = xlsx.xl.worksheets['sheet1.xml'];

                            // Add header row styling
                            $('row:first c', sheet).attr('s', '2');

                            // Set column widths
                            var colWidths = [15, 20, 12, 12, 20, 15, 12, 12];
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
                pageLength: 10,
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
        });
    </script>
</x-app-layout>
