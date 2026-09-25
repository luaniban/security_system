<?php

namespace App\Providers;

use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());

        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        $this->configurePasswordPolicy();
        $this->configureRateLimiting();
        $this->configureAuditTrail();

        Gate::define('admin', fn (User $user): bool => $user->is_admin);

        // Re-apply these route middleware on every Livewire update request, not only on page load.
        Livewire::addPersistentMiddleware([
            EnsureEmailIsVerified::class,
            RequirePassword::class,
        ]);
    }

    /**
     * OWASP ASVS V2.1: minimum length 12, complexity, and check against known breaches.
     */
    private function configurePasswordPolicy(): void
    {
        Password::defaults(function (): Password {
            $rule = Password::min(12)->max(128)->letters()->mixedCase()->numbers()->symbols();

            return $this->app->runningUnitTests() ? $rule : $rule->uncompromised();
        });
    }

    private function configureRateLimiting(): void
    {
        // Credential stuffing protection across many e-mails from one IP.
        RateLimiter::for('login-ip', fn (Request $request): Limit => Limit::perMinute(20)->by('login-ip|'.$request->ip()));

        RateLimiter::for('password-reset', fn (Request $request): Limit => Limit::perMinute(3)->by('password-reset|'.$request->ip()));

        RateLimiter::for('register', fn (Request $request): Limit => Limit::perHour(10)->by('register|'.$request->ip()));

        RateLimiter::for('checkout', fn (Request $request): Limit => Limit::perMinute(5)->by('checkout|'.($request->user()?->id ?: $request->ip())));
    }

    private function configureAuditTrail(): void
    {
        $audit = fn (): AuditLogger => $this->app->make(AuditLogger::class);

        Event::listen(fn (Login $event) => $audit()->log('auth.login', $event->user));
        Event::listen(fn (Logout $event) => $event->user && $audit()->log('auth.logout', $event->user));
        Event::listen(fn (Registered $event) => $audit()->log('auth.registered', $event->user));
        Event::listen(fn (Verified $event) => $audit()->log('auth.email_verified', $event->user));
        Event::listen(fn (PasswordReset $event) => $audit()->log('auth.password_reset', $event->user));
        Event::listen(fn (Failed $event) => $audit()->log('auth.failed', $event->user, [
            'email' => (string) ($event->credentials['email'] ?? ''),
        ]));
    }
}
