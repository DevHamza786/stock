@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Create New Record</h1>
                        <p class="text-gray-600 mt-1">Table: {{ $tableName }}</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('database-viewer.table.view', $tableName) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Back to Table
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

                <!-- Create Form -->
                <form method="POST" action="{{ route('database-viewer.table.store', $tableName) }}" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($editableColumns as $column)
                            @php
                                $columnName = $column->name;
                                $isRequired = $column->notnull && $column->dflt_value === null;
                                $fieldType = getFieldType($column);
                            @endphp

                            <div class="space-y-2">
                                <label for="{{ $columnName }}" class="block text-sm font-medium text-gray-700">
                                    {{ ucfirst(str_replace('_', ' ', $columnName)) }}
                                    @if($isRequired)
                                        <span class="text-red-500">*</span>
                                    @endif
                                    @if($column->pk)
                                        <span class="text-blue-600 text-xs">(Primary Key)</span>
                                    @endif
                                </label>

                                @if($fieldType === 'textarea')
                                    <textarea
                                        id="{{ $columnName }}"
                                        name="{{ $columnName }}"
                                        rows="3"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error($columnName) border-red-500 @enderror"
                                        @if($isRequired) required @endif
                                        placeholder="Enter {{ str_replace('_', ' ', $columnName) }}"
                                    >{{ old($columnName, $column->dflt_value) }}</textarea>

                                @elseif($fieldType === 'select')
                                    <select
                                        id="{{ $columnName }}"
                                        name="{{ $columnName }}"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error($columnName) border-red-500 @enderror"
                                        @if($isRequired) required @endif
                                    >
                                        <option value="">Select {{ str_replace('_', ' ', $columnName) }}</option>
                                        <option value="1" {{ old($columnName) == '1' ? 'selected' : '' }}>Yes</option>
                                        <option value="0" {{ old($columnName) == '0' ? 'selected' : '' }}>No</option>
                                    </select>

                                @else
                                    <input
                                        type="{{ $fieldType }}"
                                        id="{{ $columnName }}"
                                        name="{{ $columnName }}"
                                        value="{{ old($columnName, $column->dflt_value) }}"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error($columnName) border-red-500 @enderror"
                                        @if($isRequired) required @endif
                                        placeholder="Enter {{ str_replace('_', ' ', $columnName) }}"
                                    >
                                @endif

                                @if($column->type)
                                    <p class="text-xs text-gray-500">Type: {{ $column->type }}</p>
                                @endif

                                @error($columnName)
                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-3 pt-6 border-t">
                        <a href="{{ route('database-viewer.table.view', $tableName) }}"
                           class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                            Cancel
                        </a>
                        <button type="submit"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Create Record
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@php
    function getFieldType($column) {
        $type = strtolower($column->type);
        $name = strtolower($column->name);

        // Check for boolean fields
        if (str_contains($name, 'is_') || str_contains($name, 'has_') || str_contains($name, 'can_') ||
            str_contains($name, 'status') || $type === 'boolean' || $type === 'tinyint(1)') {
            return 'select';
        }

        // Check for text fields
        if ($type === 'text' || str_contains($name, 'description') || str_contains($name, 'comment') ||
            str_contains($name, 'note') || str_contains($name, 'content')) {
            return 'textarea';
        }

        // Check for date fields
        if (str_contains($name, 'date') || str_contains($name, 'time') ||
            $type === 'date' || $type === 'datetime' || $type === 'timestamp') {
            return 'datetime-local';
        }

        // Check for email fields
        if (str_contains($name, 'email')) {
            return 'email';
        }

        // Check for numeric fields
        if (str_contains($type, 'int') || str_contains($type, 'decimal') || str_contains($type, 'float')) {
            return 'number';
        }

        // Default to text
        return 'text';
    }
@endphp
@endsection
