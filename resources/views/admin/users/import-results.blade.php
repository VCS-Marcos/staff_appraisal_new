<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Import Results') }}</h2>
    </x-slot>

    @php
        $created = collect($results)->where('status', 'created')->count();
        $updated = collect($results)->where('status', 'updated')->count();
        $skipped = collect($results)->where('status', 'skipped')->count();
        $hasGenerated = collect($results)->contains(fn ($r) => $r['generated_password']);
    @endphp

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-white border border-gray-200 rounded-xl p-4">
                    <div class="text-2xl font-bold text-emerald-600">{{ $created }}</div>
                    <div class="text-sm text-gray-500">Created</div>
                </div>
                <div class="bg-white border border-gray-200 rounded-xl p-4">
                    <div class="text-2xl font-bold text-indigo-600">{{ $updated }}</div>
                    <div class="text-sm text-gray-500">Updated</div>
                </div>
                <div class="bg-white border border-gray-200 rounded-xl p-4">
                    <div class="text-2xl font-bold text-gray-500">{{ $skipped }}</div>
                    <div class="text-sm text-gray-500">Skipped</div>
                </div>
            </div>

            @if ($hasGenerated)
                <div class="p-4 bg-amber-50 border border-amber-200 text-amber-800 text-sm rounded-lg">
                    <strong>Save these passwords now.</strong> They're generated once for newly created accounts and
                    are not shown again — this page won't be reachable after you navigate away.
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Row</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Generated Password</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Note</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($results as $row)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $row['row'] }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $row['name'] ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $row['email'] ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm">
                                    @php
                                        $badge = match ($row['status']) {
                                            'created' => 'bg-emerald-100 text-emerald-800',
                                            'updated' => 'bg-indigo-100 text-indigo-800',
                                            default => 'bg-gray-100 text-gray-600',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badge }}">
                                        {{ ucfirst($row['status']) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-mono text-gray-800">{{ $row['generated_password'] ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $row['message'] ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.users.import') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                    Import Another File
                </a>
                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    Done — View Staff
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
