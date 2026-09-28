@props(['title', 'subtitle' => null])

<div class="mb-8 flex flex-wrap items-end justify-between gap-4">
    <div class="min-w-0">
        <h1 class="text-2xl font-extrabold tracking-tight text-gray-900 sm:text-3xl">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-1.5 max-w-2xl text-sm text-gray-600">{{ $subtitle }}</p>
        @endif
    </div>
    @if ($slot->isNotEmpty())
        <div class="flex flex-wrap gap-2">{{ $slot }}</div>
    @endif
</div>
