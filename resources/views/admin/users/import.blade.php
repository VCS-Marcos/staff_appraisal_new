<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Import Staff from CSV') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 text-sm">
                    <p class="font-semibold mb-1">The file couldn't be imported — please fix the following:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">CSV format</h3>
                <p class="text-sm text-gray-500 mb-3">
                    The first row must be a header with these column names (any order):
                </p>
                <div class="flex flex-wrap gap-1.5 mb-3">
                    @foreach (\App\Services\UserCsvImporter::EXPECTED_HEADERS as $header)
                        <code class="px-2 py-0.5 bg-gray-100 text-gray-700 rounded text-xs">{{ $header }}</code>
                    @endforeach
                </div>
                <ul class="text-sm text-gray-500 list-disc list-inside space-y-1 mb-4">
                    <li><strong>name</strong> and <strong>email</strong> are required; <strong>role</strong> must be <code>admin</code>, <code>reviewer</code> or <code>employee</code>.</li>
                    <li><strong>line_manager_email</strong> is optional — the email of another row (or existing account) in the system.</li>
                    <li><strong>position</strong> and <strong>is_active</strong> are optional (is_active defaults to yes).</li>
                    <li>If the email already exists, that account is updated instead of duplicated. Passwords for existing accounts are never changed.</li>
                    <li>New accounts get a randomly generated password, shown once on the results page after import — save it somewhere before leaving that page.</li>
                </ul>
                <a href="{{ route('admin.users.import.template') }}" class="inline-flex items-center gap-1.5 text-sm text-indigo-600 hover:underline">
                    <x-icon name="download" class="w-4 h-4" /> Download a template CSV
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('admin.users.import.submit') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="file" value="CSV File" />
                        <input id="file" name="file" type="file" accept=".csv,text/csv" required
                               class="block mt-1 w-full text-sm text-gray-700 border border-gray-300 rounded-md shadow-sm file:mr-4 file:py-2 file:px-4 file:border-0 file:bg-gray-100 file:text-gray-700 file:text-xs file:font-semibold file:uppercase file:tracking-widest">
                        <x-input-error :messages="$errors->get('file')" class="mt-2" />
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            Upload &amp; Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
