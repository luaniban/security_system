<?php

namespace App\Livewire\Auth;

use App\Services\AuditLogger;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Timebox;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.guest')]
#[Title('Recuperar senha')]
class ForgotPassword extends Component
{
    public string $email = '';

    public ?string $status = null;

    /**
     * Always answers with the same message (and roughly the same time) so the
     * form can't be used to discover which e-mails have an account.
     */
    public function sendResetLink(AuditLogger $audit): void
    {
        $this->validate(['email' => ['required', 'string', 'email', 'max:255']]);

        if (! RateLimiter::attempt('password-reset|'.request()->ip(), 3, fn (): bool => true, 60)) {
            throw ValidationException::withMessages([
                'email' => 'Muitas solicitações. Aguarde um minuto e tente novamente.',
            ]);
        }

        (new Timebox)->call(function (): void {
            $result = Password::sendResetLink(['email' => $this->email]);

            if ($result === Password::RESET_LINK_SENT) {
                app(AuditLogger::class)->log('auth.password_reset_requested', null, ['email' => Str::lower($this->email)]);
            }
        }, 600_000);

        $this->reset('email');
        $this->status = __('passwords.sent');
    }

    public function render(): View
    {
        return view('livewire.auth.forgot-password');
    }
}
