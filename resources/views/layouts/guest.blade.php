<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title.' · ' : '' }}{{ config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-50">
        <div class="min-h-screen flex flex-col items-center px-4 py-10 sm:justify-center">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-2xl font-bold text-brand-700">
                <x-application-logo class="w-10 h-10 fill-current" />
                {{ config('app.name') }}
            </a>

            <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-white border border-gray-200 shadow-sm rounded-2xl sm:px-8">
                {{ $slot }}
            </div>

            <a href="{{ route('home') }}" class="mt-6 text-sm text-gray-600 hover:text-brand-700 hover:underline">&larr; Voltar para a loja</a>
        </div>
    </body>
</html>
