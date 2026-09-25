<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Even administrators only see masked CPF/phone (least privilege / LGPD data minimisation).
 */
#[Title('Admin · Usuários')]
class Users extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public function mount(): void
    {
        $this->authorize('admin');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $term = '%'.addcslashes(mb_substr(trim($this->search), 0, 100), '%_\\').'%';

        return view('livewire.admin.users', [
            'users' => User::query()
                ->when(trim($this->search) !== '', fn ($query) => $query->where(fn ($query) => $query->where('name', 'like', $term)->orWhere('email', 'like', $term)))
                ->withCount(['purchases', 'sales', 'products'])
                ->latest()
                ->paginate(15),
        ]);
    }
}
