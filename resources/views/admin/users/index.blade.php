<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Staff Accounts') }}</h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.users.import') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                    <x-icon name="upload" class="w-3.5 h-3.5" /> Import CSV
                </a>
                <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    <x-icon name="plus" class="w-3.5 h-3.5" /> Add Staff
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @if (session('status'))
                    <div class="p-4 bg-green-50 text-green-700 text-sm">{{ session('status') }}</div>
                @endif

                <div class="p-4 border-b border-gray-100">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">All Staff</p>
                    <form method="GET" class="flex flex-col sm:flex-row gap-3">
                        <div class="relative flex-1">
                            <x-icon name="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, position or email..."
                                   class="w-full pl-9 border-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <select name="line_manager_id" class="border-gray-300 rounded-md shadow-sm text-sm" onchange="this.form.submit()">
                            <option value="">All Staff</option>
                            @foreach ($managers as $manager)
                                <option value="{{ $manager->id }}" @selected(request('line_manager_id') == $manager->id)>{{ $manager->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm text-gray-600 hover:bg-gray-50">Search</button>
                        @if (request('search') || request('line_manager_id'))
                            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center justify-center px-4 py-2 text-sm text-gray-500 hover:text-gray-700">Clear</a>
                        @endif
                    </form>
                </div>

                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Position</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Line Manager</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($users as $user)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $user->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $user->email }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 capitalize">{{ $user->role->value }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $user->position }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $user->lineManager?->name ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="{{ $user->is_active ? 'text-green-700' : 'text-red-700' }}">
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-right whitespace-nowrap">
                                    <x-action-menu>
                                        <x-action-menu.item :href="route('admin.users.edit', $user)" icon="pencil">Edit</x-action-menu.item>
                                        @if ($user->id !== auth()->id())
                                            <div class="my-1 border-t border-gray-100"></div>
                                            <x-confirm-delete menu-item :action="route('admin.users.destroy', $user)"
                                                prompt="Delete this staff account?" />
                                        @endif
                                    </x-action-menu>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">No staff found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="p-4">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
