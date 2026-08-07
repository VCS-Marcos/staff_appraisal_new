<div>
    <x-input-label for="name" value="Name (e.g. 2025-2026)" />
    <x-text-input id="name" name="name" type="text" class="block mt-1 w-full" :value="old('name', $cycle->name ?? '')" required autofocus />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div>
    <x-input-label for="term" value="Term" />
    <select id="term" name="term" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
        @foreach (\App\Enums\CycleTerm::cases() as $term)
            <option value="{{ $term->value }}" @selected(old('term', $cycle->term->value ?? '') === $term->value)>{{ $term->value }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('term')" class="mt-2" />
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="start_date" value="Start Date" />
        <x-text-input id="start_date" name="start_date" type="date" class="block mt-1 w-full" :value="old('start_date', optional($cycle->start_date ?? null)->format('Y-m-d'))" required />
        <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="end_date" value="End Date" />
        <x-text-input id="end_date" name="end_date" type="date" class="block mt-1 w-full" :value="old('end_date', optional($cycle->end_date ?? null)->format('Y-m-d'))" required />
        <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
    </div>
</div>

<div class="flex items-center">
    <input type="hidden" name="is_active" value="0">
    <input id="is_active" name="is_active" type="checkbox" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm" @checked(old('is_active', $cycle->is_active ?? true))>
    <label for="is_active" class="ms-2 text-sm text-gray-600">Active</label>
</div>
