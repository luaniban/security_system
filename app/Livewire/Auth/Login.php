<?php

namespace App\Livewire\Auth;

use App\Services\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Timebox;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.guest')]
#[Title('Entrar')]
class Login extends Component
{
    private const MAX_ATTEMPTS_PER_ACCOUNT = 5;

    private const LOCKOUT_SECONDS = 300;

    private const MAX_ATTEMPTS_PER_IP = 20;

    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function login(AuditLogger $audit): void
    {
        try {
            $this->validate([
                'email' => ['required', 'string', 'email', 'max:255'],
                'password' => ['required', 'string', 'max:128'],
            ]);

            $this->ensureIsNotRateLimited($audit);

            // Constant-time response so the time taken doesn't reveal whether the e-mail exists.
            $authenticated = (new Timebox)->call(function (Timebox $timebox): bool {
                $result = Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember);

                if ($result) {
                    $timebox->returnEarly();
                }

                return $result;
            }, 400_000);

            if (! $authenticated) {
                RateLimiter::hit($this->accountThrottleKey(), self::LOCKOUT_SECONDS);
                RateLimiter::hit($this->ipThrottleKey(), 60);

                throw ValidationException::withMessages(['email' => __('auth.failed')]);
            }
        } finally {
            $this->reset('password');
        }

        RateLimiter::clear($this->accountThrottleKey());

        // Prevent session fixation.
        Session::regenerate();

        $this->redirectIntended(route('dashboard'));
    }

    private function ensureIsNotRateLimited(AuditLogger $audit): void
    {
        $accountLimited = RateLimiter::tooManyAttempts($this->accountThrottleKey(), self::MAX_ATTEMPTS_PER_ACCOUNT);
        $ipLimited = RateLimiter::tooManyAttempts($this->ipThrottleKey(), self::MAX_ATTEMPTS_PER_IP);

        if (! $accountLimited && ! $ipLimited) {
            return;
        }

        $audit->log('auth.lockout', null, ['email' => Str::lower($this->email)]);

        $seconds = max(
            RateLimiter::availableIn($this->accountThrottleKey()),
            RateLimiter::availableIn($this->ipThrottleKey()),
        );

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', ['seconds' => $seconds]),
        ]);
    }

    private function accountThrottleKey(): string
    {
        return 'login|'.Str::transliterate(Str::lower($this->email)).'|'.request()->ip();
    }

    private function ipThrottleKey(): string
    {
        return 'login-ip|'.request()->ip();
    }

    public function render(): View
    {
        return view('livewire.auth.login');
    }
}
