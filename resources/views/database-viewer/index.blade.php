@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Database Viewer</h1>
                        <p class="text-gray-600 mt-1">View and search your SQLite database tables</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('database-viewer.index') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Refresh
                        </a>
                        <a href="{{ route('dashboard') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Back to Dashboard
                        </a>
                    </div>
                </div>

                <!-- Flash Messages -->
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

                <!-- Tables Overview -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">Database Tables</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @forelse($tables as $table)
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                <div class="flex justify-between items-start mb-3">
                                    <h3 class="font-semibold text-lg text-gray-900">{{ $table['name'] }}</h3>
                                    <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                        {{ $table['row_count'] }} rows
                                    </span>
                                </div>

                                <div class="mb-3">
                                    <p class="text-sm text-gray-600 mb-2">Columns:</p>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($table['columns'] as $column)
                                            <span class="bg-gray-200 text-gray-700 text-xs px-2 py-1 rounded">
                                                {{ $column->name }}
                                                @if($column->pk)
                                                    <span class="text-blue-600 font-semibold">*</span>
                                                @endif
                                            </span>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Sample Data Preview -->
                                @if($table['sample_data']->count() > 0)
                                    <div class="mb-3">
                                        <p class="text-sm text-gray-600 mb-2">Sample Data:</p>
                                        <div class="bg-white border rounded p-2 text-xs">
                                            <div class="overflow-x-auto">
                                                <table class="min-w-full">
                                                    <thead>
                                                        <tr class="bg-gray-100">
                                                            @foreach($table['columns'] as $column)
                                                                <th class="px-2 py-1 text-left">{{ $column->name }}</th>
                                                            @endforeach
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($table['sample_data'] as $row)
                                                            <tr class="border-t">
                                                                @foreach($table['columns'] as $column)
                                                                    <td class="px-2 py-1">
                                                                        {{ Str::limit($row->{$column->name} ?? '', 20) }}
                                                                    </td>
                                                                @endforeach
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="flex space-x-2">
                                    <a href="{{ route('database-viewer.table.view', $table['name']) }}"
                                       class="bg-blue-500 hover:bg-blue-700 text-white text-sm font-bold py-2 px-3 rounded">
                                        View Data
                                    </a>
                                    <button onclick="showTableSchema('{{ $table['name'] }}')"
                                            class="bg-green-500 hover:bg-green-700 text-white text-sm font-bold py-2 px-3 rounded">
                                        Schema
                                    </button>
                                    <a href="{{ route('database-viewer.table.export', $table['name']) }}"
                                       class="bg-purple-500 hover:bg-purple-700 text-white text-sm font-bold py-2 px-3 rounded">
                                        Export
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full text-center py-8">
                                <p class="text-gray-500">No tables found in the database.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- SQL Query Section -->
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                    <h2 class="text-xl font-semibold mb-4">Custom SQL Query</h2>
                    <form id="sqlQueryForm">
                        @csrf
                        <div class="mb-4">
                            <label for="sql_query" class="block text-sm font-medium text-gray-700 mb-2">
                                Enter SQL Query (SELECT only)
                            </label>
                            <textarea id="sql_query" name="sql_query" rows="4"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="SELECT * FROM users LIMIT 10;"></textarea>
                        </div>
                        <button type="submit"
                                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            Execute Query
                        </button>
                    </form>

                    <!-- Query Results -->
                    <div id="queryResults" class="mt-6" style="display: none;">
                        <h3 class="text-lg font-semibold mb-3">Query Results</h3>
                        <div id="resultsContainer"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Table Schema Modal -->
<div id="schemaModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Table Schema</h3>
                <button onclick="closeSchemaModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="schemaContent"></div>
        </div>
    </div>
</div>

<script>
// SQL Query Form Handler
document.getElementById('sqlQueryForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const query = formData.get('sql_query');

    if (!query.trim()) {
        alert('Please enter a SQL query');
        return;
    }

    try {
        const response = await fetch('{{ route("database-viewer.execute-query") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const result = await response.json();

        if (result.success) {
            displayQueryResults(result);
        } else {
            alert('Error: ' + result.error);
        }
    } catch (error) {
        alert('Error executing query: ' + error.message);
    }
});

function displayQueryResults(result) {
    const resultsContainer = document.getElementById('resultsContainer');
    const queryResults = document.getElementById('queryResults');

    if (result.results.length === 0) {
        resultsContainer.innerHTML = '<p class="text-gray-500">No results found.</p>';
    } else {
        let tableHTML = `
            <div class="bg-white border rounded-lg overflow-hidden">
                <div class="bg-gray-100 px-4 py-2 border-b">
                    <p class="text-sm font-medium">Found ${result.row_count} rows</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
        `;

        // Headers
        result.columns.forEach(column => {
            tableHTML += `<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">${column}</th>`;
        });

        tableHTML += `
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
        `;

        // Data rows
        result.results.forEach(row => {
            tableHTML += '<tr>';
            result.columns.forEach(column => {
                const value = row[column] || '';
                tableHTML += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${value}</td>`;
            });
            tableHTML += '</tr>';
        });

        tableHTML += `
                        </tbody>
                    </table>
                </div>
            </div>
        `;

        resultsContainer.innerHTML = tableHTML;
    }

    queryResults.style.display = 'block';
}

// Table Schema Modal Functions
async function showTableSchema(tableName) {
    try {
        const response = await fetch(`{{ url('database-viewer/table') }}/${tableName}/schema`);
        const result = await response.json();

        if (result.success) {
            displayTableSchema(tableName, result);
        } else {
            alert('Error loading schema: ' + result.error);
        }
    } catch (error) {
        alert('Error loading schema: ' + error.message);
    }
}

function displayTableSchema(tableName, result) {
    const schemaContent = document.getElementById('schemaContent');

    let schemaHTML = `
        <div class="mb-4">
            <h4 class="font-semibold text-gray-900 mb-2">Table: ${tableName}</h4>
        </div>

        <div class="mb-6">
            <h5 class="font-medium text-gray-700 mb-3">Columns:</h5>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Not Null</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Default</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Primary Key</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
    `;

    result.columns.forEach(column => {
        schemaHTML += `
            <tr>
                <td class="px-4 py-2 text-sm text-gray-900">${column.name}</td>
                <td class="px-4 py-2 text-sm text-gray-900">${column.type || 'N/A'}</td>
                <td class="px-4 py-2 text-sm text-gray-900">${column.notnull ? 'Yes' : 'No'}</td>
                <td class="px-4 py-2 text-sm text-gray-900">${column.dflt_value || 'NULL'}</td>
                <td class="px-4 py-2 text-sm text-gray-900">${column.pk ? 'Yes' : 'No'}</td>
            </tr>
        `;
    });

    schemaHTML += `
                    </tbody>
                </table>
            </div>
        </div>
    `;

    // Add CREATE TABLE SQL if available
    if (result.create_sql) {
        schemaHTML += `
            <div>
                <h5 class="font-medium text-gray-700 mb-3">CREATE TABLE SQL:</h5>
                <pre class="bg-gray-100 p-3 rounded text-xs overflow-x-auto">${result.create_sql}</pre>
            </div>
        `;
    }

    schemaContent.innerHTML = schemaHTML;
    document.getElementById('schemaModal').classList.remove('hidden');
}

function closeSchemaModal() {
    document.getElementById('schemaModal').classList.add('hidden');
}
</script>
@endsection
