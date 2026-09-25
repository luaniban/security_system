<?php

namespace App\Livewire\Account;

use App\Services\AuditLogger;
use App\Services\SessionManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Livewire\Component;

class UpdatePassword extends Component
{
    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public ?string $status = null;

    public function updatePassword(SessionManager $sessions, AuditLogger $audit): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', 'confirmed', 'different:current_password', Password::defaults()],
            ], [
                'password.different' => 'A nova senha deve ser diferente da atual.',
            ]);
        } finally {
            $this->reset('current_password', 'password', 'password_confirmation');
        }

        $user = Auth::user();

        $user->forceFill([
            'password' => $validated['password'],
            'remember_token' => Str::random(60),
        ])->save();

        // Sign out every other device and rotate the current session id.
        $sessions->destroyOthers($user, session()->getId());
        session()->migrate(true);

        $audit->log('account.password_changed', $user);

        $this->status = 'Senha alterada. As demais sessões foram encerradas.';
    }

    public function render(): View
    {
        return view('livewire.account.update-password');
    }
}
