{{-- Tabs that link the pages of one area (conta, vendedor, admin). --}}
@props(['items', 'label'])

<nav aria-label="{{ $label }}" class="-mx-4 mb-8 overflow-x-auto px-4 sm:mx-0 sm:px-0">
    <ul class="flex min-w-max gap-1 rounded-xl border border-gray-200 bg-white p-1 shadow-card sm:inline-flex">
        @foreach ($items as $item)
            @php($active = request()->routeIs($item['active'] ?? $item['route']))
            <li>
                <a href="{{ route($item['route']) }}" @if ($active) aria-current="page" @endif
                   class="flex min-h-10 items-center gap-2 rounded-lg px-4 text-sm font-semibold transition duration-150 ease-out {{ $active ? 'bg-brand-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                    {{ $item['label'] }}
                    @if (isset($item['count']) && $item['count'] > 0)
                        <span class="rounded-full px-2 text-xs font-bold {{ $active ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-900' }}">{{ $item['count'] }}</span>
                    @endif
                </a>
            </li>
        @endforeach
    </ul>
</nav>
