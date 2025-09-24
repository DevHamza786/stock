<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Operator Details') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('master-data.operators.edit', $operator) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                    Edit Operator
                </a>
                <a href="{{ route('master-data.operators.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                    Back to Operators
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Operator Information -->
                        <div class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Operator Name</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $operator->name }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Employee ID</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $operator->employee_id ?: 'Not provided' }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $operator->phone ?: 'Not provided' }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Email</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $operator->email ?: 'Not provided' }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $operator->status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $operator->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </dd>
                            </div>
                        </div>

                        <!-- Audit Information -->
                        <div class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Created By</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $operator->creator->name ?? 'N/A' }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Created At</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $operator->created_at->format('M d, Y H:i A') }}</dd>
                            </div>

                            @if($operator->updater)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Last Updated By</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $operator->updater->name }}</dd>
                                </div>
                            @endif

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Last Updated At</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $operator->updated_at->format('M d, Y H:i A') }}</dd>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <div class="flex justify-between items-center">
                            <form method="POST" action="{{ route('master-data.operators.destroy', $operator) }}" onsubmit="return confirm('Are you sure you want to delete this operator? This action cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                                    Delete Operator
                                </button>
                            </form>

                            <div class="flex space-x-2">
                                <a href="{{ route('master-data.operators.edit', $operator) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                                    Edit Operator
                                </a>
                                <a href="{{ route('master-data.operators.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                                    Back to Operators
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
