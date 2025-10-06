@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Table: {{ $tableName }}</h1>
                        <p class="text-gray-600 mt-1">{{ $totalCount }} total records</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('database-viewer.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Back to Tables
                        </a>
                        <a href="{{ route('database-viewer.table.export', $tableName) }}" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                            Export CSV
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

                <!-- Search Form -->
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
                    <form method="GET" action="{{ route('database-viewer.table.view', $tableName) }}" class="flex space-x-4 items-end">
                        <div class="flex-1">
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                                Search in all columns
                            </label>
                            <input type="text"
                                   id="search"
                                   name="search"
                                   value="{{ $search }}"
                                   placeholder="Enter search term..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Search
                        </button>
                        @if($search)
                            <a href="{{ route('database-viewer.table.view', $tableName) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Clear
                            </a>
                        @endif
                    </form>
                </div>

                <!-- Results Info -->
                <div class="mb-4">
                    @if($search)
                        <p class="text-gray-600">
                            Showing {{ $data->count() }} of {{ $totalCount }} records matching "{{ $search }}"
                        </p>
                    @else
                        <p class="text-gray-600">
                            Showing {{ $data->count() }} of {{ $totalCount }} records
                        </p>
                    @endif
                </div>

                <!-- Data Table -->
                @if($data->count() > 0)
                    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        @foreach($columns as $column)
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ $column->name }}
                                                @if($column->pk)
                                                    <span class="text-blue-600 font-semibold ml-1">*</span>
                                                @endif
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($data as $row)
                                        <tr class="hover:bg-gray-50">
                                            @foreach($columns as $column)
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    @php
                                                        $value = $row->{$column->name};
                                                        if (is_null($value)) {
                                                            echo '<span class="text-gray-400 italic">NULL</span>';
                                                        } elseif (is_string($value) && strlen($value) > 100) {
                                                            echo '<span title="' . htmlspecialchars($value) . '">' . htmlspecialchars(Str::limit($value, 100)) . '</span>';
                                                        } else {
                                                            echo htmlspecialchars($value);
                                                        }
                                                    @endphp
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    @if($totalPages > 1)
                        <div class="mt-6 flex items-center justify-between">
                            <div class="flex-1 flex justify-between sm:hidden">
                                @if($page > 1)
                                    <a href="{{ route('database-viewer.table.view', ['tableName' => $tableName, 'page' => $page - 1, 'search' => $search]) }}"
                                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        Previous
                                    </a>
                                @endif
                                @if($page < $totalPages)
                                    <a href="{{ route('database-viewer.table.view', ['tableName' => $tableName, 'page' => $page + 1, 'search' => $search]) }}"
                                       class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        Next
                                    </a>
                                @endif
                            </div>

                            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-gray-700">
                                        Showing page <span class="font-medium">{{ $page }}</span> of <span class="font-medium">{{ $totalPages }}</span>
                                    </p>
                                </div>
                                <div>
                                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                        @if($page > 1)
                                            <a href="{{ route('database-viewer.table.view', ['tableName' => $tableName, 'page' => 1, 'search' => $search]) }}"
                                               class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                                First
                                            </a>
                                            <a href="{{ route('database-viewer.table.view', ['tableName' => $tableName, 'page' => $page - 1, 'search' => $search]) }}"
                                               class="relative inline-flex items-center px-2 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                                Previous
                                            </a>
                                        @endif

                                        @php
                                            $start = max(1, $page - 2);
                                            $end = min($totalPages, $page + 2);
                                        @endphp

                                        @for($i = $start; $i <= $end; $i++)
                                            <a href="{{ route('database-viewer.table.view', ['tableName' => $tableName, 'page' => $i, 'search' => $search]) }}"
                                               class="relative inline-flex items-center px-4 py-2 border text-sm font-medium {{ $i == $page ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50' }}">
                                                {{ $i }}
                                            </a>
                                        @endfor

                                        @if($page < $totalPages)
                                            <a href="{{ route('database-viewer.table.view', ['tableName' => $tableName, 'page' => $page + 1, 'search' => $search]) }}"
                                               class="relative inline-flex items-center px-2 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                                Next
                                            </a>
                                            <a href="{{ route('database-viewer.table.view', ['tableName' => $tableName, 'page' => $totalPages, 'search' => $search]) }}"
                                               class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                                Last
                                            </a>
                                        @endif
                                    </nav>
                                </div>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="text-center py-8">
                        <div class="text-gray-500">
                            @if($search)
                                <p>No records found matching "{{ $search }}"</p>
                                <a href="{{ route('database-viewer.table.view', $tableName) }}" class="text-blue-500 hover:text-blue-700 mt-2 inline-block">
                                    Clear search and view all records
                                </a>
                            @else
                                <p>No records found in this table</p>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Table Info -->
                <div class="mt-8 bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h3 class="text-lg font-semibold mb-3">Table Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h4 class="font-medium text-gray-700 mb-2">Columns:</h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach($columns as $column)
                                    <span class="bg-white border border-gray-300 text-gray-700 text-sm px-3 py-1 rounded">
                                        {{ $column->name }}
                                        @if($column->pk)
                                            <span class="text-blue-600 font-semibold ml-1">*</span>
                                        @endif
                                        @if($column->notnull)
                                            <span class="text-red-600 font-semibold ml-1">NOT NULL</span>
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-700 mb-2">Column Details:</h4>
                            <div class="space-y-2">
                                @foreach($columns as $column)
                                    <div class="text-sm text-gray-600">
                                        <span class="font-medium">{{ $column->name }}:</span>
                                        {{ $column->type ?: 'TEXT' }}
                                        @if($column->dflt_value)
                                            (default: {{ $column->dflt_value }})
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
