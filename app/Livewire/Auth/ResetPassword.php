<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Services\SessionManager;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.guest')]
#[Title('Redefinir senha')]
class ResetPassword extends Component
{
    #[Locked]
    public string $token = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = (string) request()->string('email');
    }

    public function resetPassword(SessionManager $sessions): void
    {
        try {
            $this->validate([
                'token' => ['required', 'string'],
                'email' => ['required', 'string', 'email', 'max:255'],
                'password' => ['required', 'string', 'confirmed', PasswordRule::defaults()],
            ]);

            // Password::reset validates the hashed token, its expiry, and deletes it (single use).
            $status = Password::reset(
                ['email' => $this->email, 'password' => $this->password, 'token' => $this->token],
                function (User $user, string $password) use ($sessions): void {
                    $user->forceFill([
                        'password' => $password,
                        'remember_token' => Str::random(60),
                    ])->save();

                    $sessions->destroyAll($user);

                    event(new PasswordReset($user));
                },
            );
        } finally {
            $this->reset('password', 'password_confirmation');
        }

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => __('passwords.token')]);
        }

        session()->flash('status', __('passwords.reset'));

        $this->redirect(route('login'));
    }

    public function render(): View
    {
        return view('livewire.auth.reset-password');
    }
}
