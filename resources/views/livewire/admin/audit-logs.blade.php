<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-4">
    <x-page-header title="Trilha de auditoria" subtitle="Eventos de segurança: logins, falhas, bloqueios, pedidos, alterações de conta.">
        <x-select-input wire:model.live="event" aria-label="Filtrar evento">
            <option value="">Todos os eventos</option>
            @foreach ($events as $name)
                <option wire:key="ev-{{ $name }}" value="{{ $name }}">{{ $name }}</option>
            @endforeach
        </x-select-input>
        <a href="{{ route('admin.users') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 hover:bg-gray-50">Usuários</a>
    </x-page-header>

    <x-card class="overflow-x-auto p-0">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr>
                    <th class="px-4 py-3">Data</th>
                    <th class="px-4 py-3">Evento</th>
                    <th class="px-4 py-3">Usuário</th>
                    <th class="px-4 py-3">IP</th>
                    <th class="px-4 py-3">Detalhes</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr wire:key="log-{{ $log->id }}" class="border-t align-top">
                        <td class="px-4 py-2 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                        <td class="px-4 py-2 font-mono {{ str_contains($log->event, 'failed') || str_contains($log->event, 'lockout') || str_contains($log->event, 'declined') ? 'text-red-700' : '' }}">{{ $log->event }}</td>
                        <td class="px-4 py-2">{{ $log->user?->email ?? '—' }}</td>
                        <td class="px-4 py-2 font-mono">{{ $log->ip_address }}</td>
                        <td class="px-4 py-2 font-mono text-xs text-gray-600 break-all">{{ $log->context ? json_encode($log->context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">Nenhum evento registrado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-card>

    <div class="mt-6">{{ $logs->links() }}</div>
</div>
