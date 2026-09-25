<?php

namespace App\Livewire\Account;

use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Meu perfil')]
class Profile extends Component
{
    public function render(): View
    {
        return view('livewire.account.profile');
    }
}
