<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Step-up authentication before reaching sensitive areas (e.g. saved cards).
 */
#[Layout('layouts.guest')]
#[Title('Confirmar senha')]
class ConfirmPassword extends Component
{
    public string $password = '';

    public function confirmPassword(): void
    {
        $key = 'confirm-password|'.Auth::id();

        try {
            $this->validate(['password' => ['required', 'string', 'max:128']]);

            if (RateLimiter::tooManyAttempts($key, 5)) {
                throw ValidationException::withMessages([
                    'password' => __('auth.throttle', ['seconds' => RateLimiter::availableIn($key)]),
                ]);
            }

            if (! Auth::guard('web')->validate(['email' => Auth::user()->email, 'password' => $this->password])) {
                RateLimiter::hit($key, 300);

                throw ValidationException::withMessages(['password' => __('auth.password')]);
            }
        } finally {
            $this->reset('password');
        }

        RateLimiter::clear($key);
        session()->passwordConfirmed();

        $this->redirectIntended(route('dashboard'));
    }

    public function render(): View
    {
        return view('livewire.auth.confirm-password');
    }
}
