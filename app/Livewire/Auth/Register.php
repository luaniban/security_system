<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Rules\ValidCpf;
use App\Support\Cpf;
use Closure;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.guest')]
#[Title('Criar conta')]
class Register extends Component
{
    public string $name = '';

    public string $email = '';

    public string $cpf = '';

    public string $phone = '';

    public string $password = '';

    public string $password_confirmation = '';

    public bool $terms = false;

    public function register(): void
    {
        try {
            $validated = $this->validate([
                'name' => ['required', 'string', 'min:3', 'max:120', 'regex:/^[\pL\s\'.-]+$/u'],
                'email' => ['required', 'string', 'lowercase', 'email:rfc,strict', 'max:255', 'unique:'.User::class],
                'cpf' => ['required', 'string', 'max:14', new ValidCpf, $this->uniqueCpf()],
                'phone' => ['required', 'string', 'regex:/^\(?\d{2}\)?\s?9?\d{4}-?\d{4}$/'],
                'password' => ['required', 'string', 'confirmed', Password::defaults()],
                'terms' => ['accepted'],
            ], [
                'name.regex' => 'O nome deve conter apenas letras.',
                'phone.regex' => 'Informe um telefone no formato (11) 91234-5678.',
            ]);
        } finally {
            $this->reset('password', 'password_confirmation');
        }

        $this->throttle();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'cpf' => $validated['cpf'],
            'phone' => $validated['phone'],
            'password' => $validated['password'],
        ]);

        event(new Registered($user));

        Auth::login($user);
        Session::regenerate();

        $this->redirect(route('verification.notice'));
    }

    /**
     * CPFs are encrypted (non-deterministic), so uniqueness is checked through the blind index.
     */
    private function uniqueCpf(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (is_string($value) && User::where('cpf_hash', Cpf::blindIndex($value))->exists()) {
                $fail('Este CPF já está cadastrado.');
            }
        };
    }

    private function throttle(): void
    {
        if (! RateLimiter::attempt('register|'.request()->ip(), 10, fn (): bool => true, 3600)) {
            throw ValidationException::withMessages([
                'email' => 'Muitos cadastros a partir deste endereço. Tente mais tarde.',
            ]);
        }
    }

    public function render(): View
    {
        return view('livewire.auth.register');
    }
}
