@if (session('status'))
    <div class="max-w-7xl mx-auto mt-4 px-4 sm:px-6 lg:px-8">
        <div class="rounded-md bg-green-50 border border-green-200 p-3 text-sm text-green-800" role="status">
            {{ session('status') }}
        </div>
    </div>
@endif
