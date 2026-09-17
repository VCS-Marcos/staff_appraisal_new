@php
    $tabs = [
        ['route' => 'dashboard', 'active' => request()->routeIs('dashboard'), 'icon' => 'dashboard', 'label' => 'Dashboard'],
        ['route' => 'appraisals.index', 'active' => request()->routeIs('appraisals.*'), 'icon' => 'appraisals', 'label' => 'My Appraisals'],
    ];
    if (Auth::user()->isAdmin() || Auth::user()->isReviewer()) {
        $tabs[] = ['route' => 'reports.index', 'active' => request()->routeIs('reports.*'), 'icon' => 'reports', 'label' => 'Reports'];
    }
    if (Auth::user()->isAdmin()) {
        $tabs[] = ['route' => 'admin.users.index', 'active' => request()->routeIs('admin.users.*'), 'icon' => 'users', 'label' => 'All Staff'];
        $tabs[] = ['route' => 'admin.appraisals.index', 'active' => request()->routeIs('admin.appraisals.*'), 'icon' => 'list', 'label' => 'All Appraisals'];
    }
@endphp

<nav x-data="{ open: false }" class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Row 1: Branding + account actions -->
        <div class="flex items-center justify-between h-16">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 min-w-0">
                <x-application-logo class="w-7 h-7 shrink-0 text-indigo-600" />
                <span class="font-bold text-gray-900 truncate">{{ config('app.name') }} System</span>
            </a>

            <div class="hidden sm:flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-semibold">
                    <x-icon name="user-circle" class="w-3.5 h-3.5" />
                    {{ Auth::user()->name }} &middot; {{ ucfirst(Auth::user()->role->value) }}
                </span>

                <a href="{{ route('profile.edit') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-gray-300 rounded-md text-xs font-medium text-gray-600 hover:bg-gray-50 transition">
                    <x-icon name="lock" class="w-3.5 h-3.5" />
                    Change Password
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-gray-300 rounded-md text-xs font-medium text-gray-600 hover:bg-gray-50 transition">
                        <x-icon name="logout" class="w-3.5 h-3.5" />
                        Sign Out
                    </button>
                </form>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Row 2: Icon tab navigation -->
        <div class="hidden sm:flex gap-1 -mb-px overflow-x-auto">
            @foreach ($tabs as $tab)
                <a href="{{ route($tab['route']) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2.5 text-sm font-medium border-b-2 whitespace-nowrap transition
                          {{ $tab['active'] ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <x-icon :name="$tab['icon']" class="w-4 h-4" />
                    {{ __($tab['label']) }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-gray-100">
        <div class="pt-2 pb-3 space-y-1">
            @foreach ($tabs as $tab)
                <a href="{{ route($tab['route']) }}"
                   class="flex items-center gap-2 ps-3 pe-4 py-2 border-l-4 text-base font-medium transition
                          {{ $tab['active'] ? 'border-indigo-500 text-indigo-700 bg-indigo-50' : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300' }}">
                    <x-icon :name="$tab['icon']" class="w-4 h-4" />
                    {{ __($tab['label']) }}
                </a>
            @endforeach
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4 flex items-center justify-between">
                <div>
                    <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-semibold">
                    {{ ucfirst(Auth::user()->role->value) }}
                </span>
            </div>

            <div class="mt-3 space-y-1">
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 ps-3 pe-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 transition">
                    <x-icon name="lock" class="w-4 h-4" />
                    {{ __('Change Password') }}
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-2 ps-3 pe-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 transition">
                        <x-icon name="logout" class="w-4 h-4" />
                        {{ __('Sign Out') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
