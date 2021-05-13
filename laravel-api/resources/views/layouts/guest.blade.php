<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Styles -->
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">

        <!-- Scripts -->
        <script src="{{ asset('js/app.js') }}" defer></script>
    </head>
    <body>
        <div class="relative flex items-top justify-center bg-gray-100 dark:bg-gray-900 sm:items-center py-4 sm:pt-0">
            <div class="hidden fixed top-0 left-0 px-6 py-4 sm:block">
                @foreach ($langs as $lang)
                    <a href="{{ route('lang', $lang) }}" class="text-sm {{ App::currentLocale() === $lang ? 'text-gray-400 ' : 'text-gray-700 ' }} underline">{{ $lang }}</a>
                @endforeach
            </div>
        </div>

        <div class="font-sans text-gray-900 antialiased">
            {{ $slot }}
        </div>
    </body>
</html>
