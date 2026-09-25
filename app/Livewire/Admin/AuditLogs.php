<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Admin · Auditoria')]
class AuditLogs extends Component
{
    use WithPagination;

    #[Url(as: 'evento', except: '')]
    public string $event = '';

    public function mount(): void
    {
        $this->authorize('admin');
    }

    public function updatedEvent(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $events = AuditLog::query()->distinct()->orderBy('event')->pluck('event');

        return view('livewire.admin.audit-logs', [
            'events' => $events,
            'logs' => AuditLog::query()
                ->with('user:id,name,email')
                ->when($events->contains($this->event), fn ($query) => $query->where('event', $this->event))
                ->latest('id')
                ->paginate(25),
        ]);
    }
}
