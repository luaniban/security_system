@php
    // Plain GET search handled by the catalog (#[Url(as: 'q')]); arrays or oversized input are ignored.
    $searchTerm = request()->routeIs('home') && is_string(request()->query('q')) ? mb_substr(request()->query('q'), 0, 100) : '';
@endphp

<nav x-data="{ open: false }" class="bg-white border-b border-gray-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-4 h-16 lg:gap-8">
            <a href="{{ route('home') }}" class="shrink-0 flex items-center gap-2 text-lg font-bold text-indigo-700">
                <x-application-logo class="h-8 w-8 fill-current" />
                <span class="hidden sm:inline">{{ config('app.name') }}</span>
            </a>

            <form action="{{ route('home') }}" method="GET" role="search" wire:ignore class="hidden sm:flex flex-1 max-w-2xl">
                <label for="site-search" class="sr-only">Buscar produtos</label>
                <input id="site-search" name="q" type="search" value="{{ $searchTerm }}" maxlength="100" placeholder="O que você está procurando?"
                       class="w-full rounded-s-lg border-gray-300 bg-gray-50 text-sm focus:border-indigo-500 focus:bg-white focus:ring-indigo-500">
                <button type="submit" class="inline-flex items-center rounded-e-lg bg-indigo-600 px-4 text-white hover:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2" aria-label="Buscar">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                </button>
            </form>

            <div class="ms-auto flex items-center gap-1 sm:gap-3">
                @auth
                    <div class="hidden sm:block">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button type="button" class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-start hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-700" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                                    <span class="hidden lg:flex flex-col leading-tight">
                                        <span class="text-xs text-gray-500">Olá, {{ \Illuminate\Support\Str::limit(\Illuminate\Support\Str::before(auth()->user()->name, ' '), 16) }}</span>
                                        <span class="text-sm font-semibold text-gray-900">Minha conta</span>
                                    </span>
                                    <svg class="h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                    <span class="sr-only lg:hidden">Minha conta</span>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('profile.edit')">Meu perfil</x-dropdown-link>
                                <x-dropdown-link :href="route('orders.index')">Minhas compras</x-dropdown-link>
                                <x-dropdown-link :href="route('addresses.index')">Endereços</x-dropdown-link>
                                <x-dropdown-link :href="route('cards.index')">Cartões</x-dropdown-link>
                                <x-dropdown-link :href="route('seller.sales.index')">Minhas vendas</x-dropdown-link>

                                <form method="POST" action="{{ route('logout') }}" class="border-t">
                                    @csrf
                                    <button type="submit" class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 hover:bg-gray-100">Sair</button>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="hidden sm:inline-flex rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">Entrar</a>
                    <a href="{{ route('register') }}" class="hidden sm:inline-flex rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Criar conta</a>
                @endauth

                <a href="{{ route('cart') }}" class="relative flex h-11 w-11 items-center justify-center rounded-lg text-gray-700 hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                   aria-label="Carrinho{{ $cartCount > 0 ? ', '.$cartCount.' '.($cartCount === 1 ? 'item' : 'itens') : '' }}">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" /></svg>
                    @if ($cartCount > 0)
                        <span class="absolute right-0.5 top-0.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-amber-400 px-1 text-xs font-bold text-gray-900" aria-hidden="true">{{ $cartCount > 99 ? '99+' : $cartCount }}</span>
                    @endif
                </a>

                <button type="button" @click="open = ! open" class="flex h-11 w-11 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 sm:hidden" aria-label="Menu" :aria-expanded="open">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
            </div>
        </div>

        <form action="{{ route('home') }}" method="GET" role="search" wire:ignore class="flex pb-3 sm:hidden">
            <label for="site-search-mobile" class="sr-only">Buscar produtos</label>
            <input id="site-search-mobile" name="q" type="search" value="{{ $searchTerm }}" maxlength="100" placeholder="Buscar produtos"
                   class="w-full rounded-s-lg border-gray-300 bg-gray-50 text-sm focus:border-indigo-500 focus:bg-white focus:ring-indigo-500">
            <button type="submit" class="inline-flex items-center rounded-e-lg bg-indigo-600 px-4 text-white" aria-label="Buscar">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
            </button>
        </form>

        <div class="hidden sm:flex h-11 gap-6 -mb-px">
            <x-nav-link :href="route('home')" :active="request()->routeIs('home', 'products.show')">Todos os produtos</x-nav-link>
            @auth
                <x-nav-link :href="route('orders.index')" :active="request()->routeIs('orders.*')">Minhas compras</x-nav-link>
                <x-nav-link :href="route('seller.products.index')" :active="request()->routeIs('seller.*')">Vender</x-nav-link>
                <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Painel</x-nav-link>
                @can('admin')
                    <x-nav-link :href="route('admin.users')" :active="request()->routeIs('admin.*')">Admin</x-nav-link>
                @endcan
            @else
                <x-nav-link :href="route('register')" :active="false">Venda no {{ config('app.name') }}</x-nav-link>
            @endauth
        </div>
    </div>

    <div x-show="open" x-cloak class="sm:hidden border-t">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('home')" :active="request()->routeIs('home')">Todos os produtos</x-responsive-nav-link>
            @auth
                <x-responsive-nav-link :href="route('cart')" :active="request()->routeIs('cart')">Carrinho ({{ $cartCount }})</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('orders.index')" :active="request()->routeIs('orders.*')">Minhas compras</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('seller.products.index')" :active="request()->routeIs('seller.*')">Vender</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Painel</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('profile.edit')">Meu perfil</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('addresses.index')">Endereços</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('cards.index')">Cartões</x-responsive-nav-link>
                @can('admin')
                    <x-responsive-nav-link :href="route('admin.users')">Admin</x-responsive-nav-link>
                @endcan
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full ps-3 pe-4 py-2 text-start text-base font-medium text-gray-600">Sair</button>
                </form>
            @else
                <x-responsive-nav-link :href="route('login')">Entrar</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('register')">Criar conta</x-responsive-nav-link>
            @endauth
        </div>
    </div>
</nav>
