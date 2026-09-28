{{-- Standalone error layout: no Livewire / session dependency, no inline styles (CSP). --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex">
        <title>@yield('title') · {{ config('app.name') }}</title>
        @vite(['resources/css/app.css'])
    </head>
    <body class="bg-gray-50 font-sans text-gray-900 antialiased">
        <main class="flex min-h-screen flex-col items-center justify-center px-4 py-12 text-center">
            <a href="{{ url('/') }}" class="mb-10 flex items-center gap-2 text-lg font-extrabold tracking-tight">
                <x-application-logo class="h-8 w-8 fill-brand-600" />
                {{ config('app.name') }}
            </a>

            <p class="text-sm font-bold text-brand-700">Erro @yield('code')</p>
            <h1 class="mt-2 max-w-xl text-3xl font-extrabold tracking-tight sm:text-4xl">@yield('title')</h1>
            <p class="mt-4 max-w-md text-base text-gray-600">@yield('message')</p>

            <div class="mt-8 flex flex-wrap justify-center gap-3">
                @hasSection('action')
                    @yield('action')
                @else
                    <a href="{{ url('/') }}" class="inline-flex min-h-11 items-center rounded-lg bg-brand-600 px-5 text-sm font-bold text-white shadow-sm hover:bg-brand-700">Voltar para a loja</a>
                @endif
            </div>
        </main>
    </body>
</html>
