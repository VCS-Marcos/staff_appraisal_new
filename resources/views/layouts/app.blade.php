<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        @if (config('app.branding'))
            <link rel="icon" href="{{ asset('images/school-logo.png') }}">
        @endif

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            @if (session('warning_recipients'))
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 pt-6">
                    <div class="p-4 bg-amber-50 border border-amber-200 text-amber-800 text-sm rounded-md" role="alert">
                        Your change was saved, but the email notification could not be sent to {{ implode(', ', session('warning_recipients')) }}. Please let them know directly.
                    </div>
                </div>
            @endif

            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
