<x-app-layout>
    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Manage Permissions</h1>
                        <p class="mt-2 text-gray-600">Set edit and delete permissions for {{ $user->name }}</p>
                    </div>
                    <a href="{{ route('user-management.users.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                        Back to List
                    </a>
                </div>
            </div>

            @if($user->isAdmin())
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <p class="text-yellow-800">Admin users have full access to all modules. Permissions cannot be modified for admin users.</p>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                    <div class="p-6">
                        <form method="POST" action="{{ route('user-management.users.permissions.update', $user) }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-6">
                                <p class="text-sm text-gray-600 mb-4">Select which modules this user can edit and delete. Users can always view all accessible modules.</p>
                            </div>

                            <div class="space-y-4">
                                @foreach($modules as $module)
                                    @php
                                        $permission = $user->permissions->firstWhere('module', $module);
                                        $canEdit = $permission ? $permission->can_edit : false;
                                        $canDelete = $permission ? $permission->can_delete : false;
                                    @endphp
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-3">
                                            {{ ucfirst(str_replace('-', ' ', $module)) }}
                                        </h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="flex items-center">
                                                <input type="checkbox" name="permissions[{{ $module }}][edit]" id="edit_{{ $module }}" value="1" {{ $canEdit ? 'checked' : '' }}
                                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                                <label for="edit_{{ $module }}" class="ml-2 text-sm font-medium text-gray-700">
                                                    Can Edit
                                                </label>
                                            </div>
                                            <div class="flex items-center">
                                                <input type="checkbox" name="permissions[{{ $module }}][delete]" id="delete_{{ $module }}" value="1" {{ $canDelete ? 'checked' : '' }}
                                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                                <label for="delete_{{ $module }}" class="ml-2 text-sm font-medium text-gray-700">
                                                    Can Delete
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Buttons -->
                            <div class="mt-8 flex justify-end space-x-4">
                                <a href="{{ route('user-management.users.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                                    Cancel
                                </a>
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                                    Save Permissions
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
