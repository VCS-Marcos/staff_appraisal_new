<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col items-center justify-center bg-gray-50 px-4 py-10">
            <div class="w-full max-w-md bg-white border border-gray-200 rounded-2xl shadow-sm p-8">
                <div class="flex flex-col items-center text-center mb-6">
                    <div class="w-14 h-14 rounded-full bg-indigo-600 flex items-center justify-center mb-4">
                        <x-application-logo class="w-7 h-7 text-white" />
                    </div>
                    <h1 class="text-xl font-bold text-gray-900">{{ config('app.name') }} System</h1>
                    <p class="text-sm text-gray-500 mt-1">{{ config('app.subtitle') }}</p>
                </div>

                {{ $slot }}
            </div>
        </div>
    </body>
</html>
