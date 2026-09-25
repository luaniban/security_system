<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.guest')]
#[Title('Confirme seu e-mail')]
class VerifyEmail extends Component
{
    public ?string $status = null;

    public function mount(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(route('dashboard'));
        }
    }

    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(route('dashboard'));

            return;
        }

        if (! RateLimiter::attempt('verify-email|'.Auth::id(), 3, fn (): bool => true, 300)) {
            $this->status = 'Muitos envios. Aguarde alguns minutos.';

            return;
        }

        Auth::user()->sendEmailVerificationNotification();

        $this->status = 'Um novo link de confirmação foi enviado para o seu e-mail.';
    }

    public function render(): View
    {
        return view('livewire.auth.verify-email');
    }
}
