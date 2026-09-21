@php
    $u = $user ?? old();
@endphp

<div>
    <x-input-label for="name" value="Name" />
    <x-text-input id="name" name="name" type="text" class="block mt-1 w-full" :value="old('name', $user->name ?? '')" required autofocus />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div>
    <x-input-label for="email" value="Email" />
    <x-text-input id="email" name="email" type="email" class="block mt-1 w-full" :value="old('email', $user->email ?? '')" required />
    <x-input-error :messages="$errors->get('email')" class="mt-2" />
</div>

<div x-data="{ preview: null }">
    <x-input-label for="photo" value="Staff Photo (optional)" />
    <div class="mt-1 flex items-center gap-4">
        <div class="w-20 h-20 shrink-0 rounded-full overflow-hidden border border-gray-200 bg-indigo-100 text-indigo-700 flex items-center justify-center">
            <img x-show="preview" x-cloak :src="preview" alt="New photo preview" class="w-full h-full object-cover">
            <template x-if="! preview">
                @if ($user && $user->hasPhoto())
                    <img src="{{ $user->photoUrl() }}" alt="Current photo of {{ $user->name }}" class="w-full h-full object-cover">
                @elseif ($user)
                    <span class="font-semibold text-lg">{{ \App\Support\Initials::of($user->name) }}</span>
                @else
                    <x-icon name="user-circle" class="w-10 h-10 text-indigo-400" />
                @endif
            </template>
        </div>
        <div class="min-w-0 flex-1">
            <input id="photo" name="photo" type="file" accept="image/jpeg,image/png"
                   @change="const f = $event.target.files[0]; preview = f && ['image/jpeg','image/png'].includes(f.type) ? URL.createObjectURL(f) : null"
                   class="block w-full text-sm text-gray-700 border border-gray-300 rounded-md shadow-sm file:mr-4 file:py-2 file:px-4 file:border-0 file:bg-gray-100 file:text-gray-700 file:text-xs file:font-semibold file:uppercase file:tracking-widest">
            <p class="text-xs text-gray-500 mt-1">JPEG or PNG only, up to 2 MB. It's cropped to a square and shrunk automatically. Without a photo, the initials are shown instead.</p>
        </div>
    </div>
    @if ($user && $user->hasPhoto())
        <label class="mt-2 inline-flex items-center text-sm text-gray-600">
            <input type="hidden" name="remove_photo" value="0">
            <input type="checkbox" name="remove_photo" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm">
            <span class="ms-2">Remove current photo</span>
        </label>
    @endif
    <x-input-error :messages="$errors->get('photo')" class="mt-2" />
</div>

<div>
    <x-input-label for="password" :value="$user ? 'New Password (leave blank to keep current)' : 'Password'" />
    <x-text-input id="password" name="password" type="password" class="block mt-1 w-full" :required="! $user" autocomplete="new-password" />
    <x-input-error :messages="$errors->get('password')" class="mt-2" />
</div>

<div>
    <x-input-label for="password_confirmation" value="Confirm Password" />
    <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="block mt-1 w-full" :required="! $user" autocomplete="new-password" />
</div>

<div>
    <x-input-label for="role" value="Role" />
    <select id="role" name="role" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
        @foreach (\App\Enums\UserRole::cases() as $role)
            <option value="{{ $role->value }}" @selected(old('role', $user->role->value ?? '') === $role->value)>{{ ucfirst($role->value) }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('role')" class="mt-2" />
</div>

<div>
    <x-input-label for="position" value="Position" />
    <x-text-input id="position" name="position" type="text" class="block mt-1 w-full" :value="old('position', $user->position ?? '')" />
    <x-input-error :messages="$errors->get('position')" class="mt-2" />
</div>

<div>
    <x-input-label for="line_manager_id" value="Line Manager / Reviewer" />
    <select id="line_manager_id" name="line_manager_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
        <option value="">— None —</option>
        @foreach ($managers as $manager)
            <option value="{{ $manager->id }}" @selected((string) old('line_manager_id', $user->line_manager_id ?? '') === (string) $manager->id)>
                {{ $manager->name }} ({{ ucfirst($manager->role->value) }})
            </option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('line_manager_id')" class="mt-2" />
</div>

<div class="flex items-center">
    <input type="hidden" name="is_active" value="0">
    <input id="is_active" name="is_active" type="checkbox" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm" @checked(old('is_active', $user->is_active ?? true))>
    <label for="is_active" class="ms-2 text-sm text-gray-600">Active</label>
</div>
