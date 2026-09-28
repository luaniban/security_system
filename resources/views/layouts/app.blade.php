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
        <a href="#conteudo" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:font-semibold focus:text-brand-800 focus:shadow-lift">Pular para o conteúdo</a>

        <div class="min-h-screen flex flex-col">
            <div class="bg-brand-900 text-brand-50">
                <p class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 text-center text-xs sm:text-sm">
                    <span class="font-semibold text-amber-300">Frete grátis</span>
                    acima de {{ Money::format(ShippingCalculator::FREE_SHIPPING_THRESHOLD_CENTS) }}<span class="hidden sm:inline"> em cada vendedor</span>
                </p>
            </div>

            <livewire:layout.navigation />

            <x-flash />

            <main id="conteudo" class="flex-1 py-8 sm:py-10">
                {{ $slot }}
            </main>

            <footer class="mt-12 border-t border-gray-200 bg-white">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 grid grid-cols-2 gap-8 lg:grid-cols-4">
                    <div class="col-span-2 flex flex-col gap-3 lg:col-span-1">
                        <a href="{{ route('home') }}" class="flex items-center gap-2 text-lg font-extrabold tracking-tight text-gray-900">
                            <x-application-logo class="h-7 w-7 fill-brand-600" />
                            {{ config('app.name') }}
                        </a>
                        <p class="text-sm text-gray-600">Marketplace com pagamento protegido e envio direto pelos vendedores.</p>
                    </div>

                    <nav aria-labelledby="footer-shop" class="flex flex-col gap-2 text-sm">
                        <h2 id="footer-shop" class="font-semibold text-gray-900">Comprar</h2>
                        <a href="{{ route('home') }}" class="text-gray-600 hover:text-brand-700">Todos os produtos</a>
                        <a href="{{ route('cart') }}" class="text-gray-600 hover:text-brand-700">Carrinho</a>
                        <a href="{{ route('orders.index') }}" class="text-gray-600 hover:text-brand-700">Minhas compras</a>
                    </nav>

                    <nav aria-labelledby="footer-sell" class="flex flex-col gap-2 text-sm">
                        <h2 id="footer-sell" class="font-semibold text-gray-900">Vender</h2>
                        <a href="{{ route('seller.products.index') }}" class="text-gray-600 hover:text-brand-700">Meus produtos</a>
                        <a href="{{ route('seller.sales.index') }}" class="text-gray-600 hover:text-brand-700">Minhas vendas</a>
                    </nav>

                    <nav aria-labelledby="footer-account" class="flex flex-col gap-2 text-sm">
                        <h2 id="footer-account" class="font-semibold text-gray-900">Minha conta</h2>
                        <a href="{{ route('profile.edit') }}" class="text-gray-600 hover:text-brand-700">Perfil</a>
                        <a href="{{ route('addresses.index') }}" class="text-gray-600 hover:text-brand-700">Endereços</a>
                        <a href="{{ route('privacy') }}" class="text-gray-600 hover:text-brand-700">Política de Privacidade</a>
                    </nav>
                </div>

                <div class="border-t border-gray-200">
                    <p class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 text-xs text-gray-500">
                        {{ config('app.name') }} é um projeto acadêmico da disciplina de Segurança de Sistemas. Não use dados reais.
                    </p>
                </div>
            </footer>
        </div>
    </body>
</html>
