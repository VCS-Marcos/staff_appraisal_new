@props(['name'])

@php
    $classes = $attributes->get('class', 'w-5 h-5');
@endphp

@switch($name)
    @case('cap')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" {{ $attributes->merge(['class' => $classes]) }}>
            <path d="M12 4.5 2.5 9 12 13.5 21.5 9 12 4.5Z" />
            <path d="M6.5 11.2V16c0 1.2 2.46 2.5 5.5 2.5s5.5-1.3 5.5-2.5v-4.8" />
            <path d="M21.5 9v6" />
        </svg>
        @break

    @case('dashboard')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" {{ $attributes->merge(['class' => $classes]) }}>
            <rect x="3.5" y="3.5" width="7" height="7" rx="1.2" />
            <rect x="13.5" y="3.5" width="7" height="7" rx="1.2" />
            <rect x="3.5" y="13.5" width="7" height="7" rx="1.2" />
            <rect x="13.5" y="13.5" width="7" height="7" rx="1.2" />
        </svg>
        @break

    @case('appraisals')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" {{ $attributes->merge(['class' => $classes]) }}>
            <rect x="5" y="3.5" width="14" height="17" rx="1.5" />
            <path d="M8.5 8.5h7M8.5 12h7M8.5 15.5h4.5" />
        </svg>
        @break

    @case('reports')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" {{ $attributes->merge(['class' => $classes]) }}>
            <path d="M4 20V10M12 20V4M20 20v-7" />
            <path d="M4 20h16" />
        </svg>
        @break

    @case('users')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" {{ $attributes->merge(['class' => $classes]) }}>
            <circle cx="9" cy="8" r="3" />
            <path d="M3.5 19c0-3 2.5-5 5.5-5s5.5 2 5.5 5" />
            <circle cx="17" cy="9" r="2.3" />
            <path d="M15 19c0-2.3 1.3-4.2 3.2-4.9" />
        </svg>
        @break

    @case('cycles')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" {{ $attributes->merge(['class' => $classes]) }}>
            <rect x="3.5" y="5" width="17" height="15" rx="1.5" />
            <path d="M3.5 9.5h17M8 3v3.5M16 3v3.5" />
        </svg>
        @break

    @case('list')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" {{ $attributes->merge(['class' => $classes]) }}>
            <path d="M8.5 6.5h11.5M8.5 12h11.5M8.5 17.5h11.5" />
            <path d="M4.2 6.5h.01M4.2 12h.01M4.2 17.5h.01" />
        </svg>
        @break

    @case('lock')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" {{ $attributes->merge(['class' => $classes]) }}>
            <rect x="5" y="10.5" width="14" height="9.5" rx="1.5" />
            <path d="M8 10.5V7.5a4 4 0 0 1 8 0v3" />
        </svg>
        @break

    @case('logout')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" {{ $attributes->merge(['class' => $classes]) }}>
            <path d="M9 20H5.5A1.5 1.5 0 0 1 4 18.5v-13A1.5 1.5 0 0 1 5.5 4H9" />
            <path d="M16 16l4-4-4-4M20 12H9" />
        </svg>
        @break

    @case('search')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" {{ $attributes->merge(['class' => $classes]) }}>
            <circle cx="10.5" cy="10.5" r="6.5" />
            <path d="M19.5 19.5 15.3 15.3" />
        </svg>
        @break

    @case('download')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" {{ $attributes->merge(['class' => $classes]) }}>
            <path d="M12 3.5v11M8 11l4 4 4-4" />
            <path d="M4.5 17v2.5A1.5 1.5 0 0 0 6 21h12a1.5 1.5 0 0 0 1.5-1.5V17" />
        </svg>
        @break

    @case('plus')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" {{ $attributes->merge(['class' => $classes]) }}>
            <path d="M12 5v14M5 12h14" />
        </svg>
        @break

    @case('user-circle')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" {{ $attributes->merge(['class' => $classes]) }}>
            <circle cx="12" cy="12" r="8.5" />
            <circle cx="12" cy="10" r="2.6" />
            <path d="M6.8 18c.7-2.2 2.7-3.5 5.2-3.5s4.5 1.3 5.2 3.5" />
        </svg>
        @break

    @case('pencil')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" {{ $attributes->merge(['class' => $classes]) }}>
            <path d="M4 20l.9-3.6L16 5.3a1.7 1.7 0 0 1 2.4 0l.3.3a1.7 1.7 0 0 1 0 2.4L7.6 19 4 20Z" />
        </svg>
        @break

    @case('trash')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" {{ $attributes->merge(['class' => $classes]) }}>
            <path d="M5 7h14M9.5 7V5a1.5 1.5 0 0 1 1.5-1.5h2A1.5 1.5 0 0 1 14.5 5v2" />
            <path d="M6.5 7 7.3 19a1.5 1.5 0 0 0 1.5 1.4h6.4a1.5 1.5 0 0 0 1.5-1.4L17.5 7" />
        </svg>
        @break

    @case('check-circle')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" {{ $attributes->merge(['class' => $classes]) }}>
            <circle cx="12" cy="12" r="8.5" />
            <path d="M8.3 12.3 10.8 14.8 15.7 9.7" />
        </svg>
        @break

    @case('dots-vertical')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" {{ $attributes->merge(['class' => $classes]) }}>
            <circle cx="12" cy="5" r="1.8" fill="currentColor" />
            <circle cx="12" cy="12" r="1.8" fill="currentColor" />
            <circle cx="12" cy="19" r="1.8" fill="currentColor" />
        </svg>
        @break

    @case('eye')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" {{ $attributes->merge(['class' => $classes]) }}>
            <path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z" />
            <circle cx="12" cy="12" r="2.6" />
        </svg>
        @break

    @case('arrow-uturn-left')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" {{ $attributes->merge(['class' => $classes]) }}>
            <path d="M7 10 3.5 6.5 7 3" />
            <path d="M3.5 6.5h10a6 6 0 0 1 0 12H9" />
        </svg>
        @break

    @case('unlock')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" {{ $attributes->merge(['class' => $classes]) }}>
            <rect x="5" y="10.5" width="14" height="9.5" rx="1.5" />
            <path d="M8 10.5V7.5a4 4 0 0 1 7.5-2" />
        </svg>
        @break
@endswitch
