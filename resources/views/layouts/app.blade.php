@use('App\Support\Money')
@use('App\Services\ShippingCalculator')
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title.' · ' : '' }}{{ config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-gray-900 bg-gray-50">
        <div class="min-h-screen flex flex-col">
            <div class="bg-gray-900 text-white">
                <p class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 text-center text-xs sm:text-sm">
                    <span class="font-semibold text-amber-300">Frete grátis</span>
                    em pedidos acima de {{ Money::format(ShippingCalculator::FREE_SHIPPING_THRESHOLD_CENTS) }} por vendedor
                </p>
            </div>

            <livewire:layout.navigation />

            <x-flash />

            <main class="flex-1 py-8">
                {{ $slot }}
            </main>

            <footer class="mt-8 border-t border-gray-200 bg-white">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="flex flex-col gap-3">
                        <a href="{{ route('home') }}" class="flex items-center gap-2 font-bold text-indigo-700">
                            <x-application-logo class="h-7 w-7 fill-current" />
                            {{ config('app.name') }}
                        </a>
                        <p class="text-sm text-gray-600">Marketplace com pagamento protegido e envio direto pelos vendedores.</p>
                    </div>

                    <nav aria-labelledby="footer-shop" class="flex flex-col gap-2 text-sm">
                        <h2 id="footer-shop" class="font-semibold text-gray-900">Comprar</h2>
                        <a href="{{ route('home') }}" class="text-gray-600 hover:text-indigo-700">Todos os produtos</a>
                        <a href="{{ route('cart') }}" class="text-gray-600 hover:text-indigo-700">Carrinho</a>
                        <a href="{{ route('orders.index') }}" class="text-gray-600 hover:text-indigo-700">Minhas compras</a>
                    </nav>

                    <nav aria-labelledby="footer-sell" class="flex flex-col gap-2 text-sm">
                        <h2 id="footer-sell" class="font-semibold text-gray-900">Vender</h2>
                        <a href="{{ route('seller.products.index') }}" class="text-gray-600 hover:text-indigo-700">Meus produtos</a>
                        <a href="{{ route('seller.sales.index') }}" class="text-gray-600 hover:text-indigo-700">Minhas vendas</a>
                    </nav>

                    <nav aria-labelledby="footer-account" class="flex flex-col gap-2 text-sm">
                        <h2 id="footer-account" class="font-semibold text-gray-900">Minha conta</h2>
                        <a href="{{ route('profile.edit') }}" class="text-gray-600 hover:text-indigo-700">Perfil</a>
                        <a href="{{ route('addresses.index') }}" class="text-gray-600 hover:text-indigo-700">Endereços</a>
                        <a href="{{ route('privacy') }}" class="text-gray-600 hover:text-indigo-700">Política de Privacidade</a>
                    </nav>
                </div>

                <div class="border-t border-gray-200">
                    <p class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 text-xs text-gray-500">
                        {{ config('app.name') }} · Projeto acadêmico de Segurança de Sistemas
                    </p>
                </div>
            </footer>
        </div>
    </body>
</html>
