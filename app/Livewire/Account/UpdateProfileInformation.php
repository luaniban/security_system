<?php

namespace App\Livewire\Account;

use App\Services\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

class UpdateProfileInformation extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $current_password = '';

    public function mount(): void
    {
        $user = Auth::user();

        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
    }

    public function updateProfileInformation(AuditLogger $audit): void
    {
        $user = Auth::user();
        $emailChanged = mb_strtolower($this->email) !== $user->email;

        try {
            $validated = $this->validate([
                'name' => ['required', 'string', 'min:3', 'max:120', 'regex:/^[\pL\s\'.-]+$/u'],
                'email' => ['required', 'string', 'lowercase', 'email:rfc,strict', 'max:255', Rule::unique('users')->ignore($user->id)],
                'phone' => ['required', 'string', 'regex:/^\(?\d{2}\)?\s?9?\d{4}-?\d{4}$/'],
                // Changing the login e-mail is an account-takeover vector: require the current password.
                'current_password' => [Rule::requiredIf($emailChanged), 'nullable', 'string', 'current_password'],
            ], [
                'name.regex' => 'O nome deve conter apenas letras.',
                'phone.regex' => 'Informe um telefone no formato (11) 91234-5678.',
            ]);
        } finally {
            $this->reset('current_password');
        }

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ]);

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        $audit->log('account.profile_updated', $user, ['email_changed' => $emailChanged]);

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
            $this->redirect(route('verification.notice'));

            return;
        }

        $this->phone = $user->phone;
        $this->dispatch('profile-updated');
        session()->flash('profile-status', 'Dados atualizados.');
    }

    public function render(): View
    {
        return view('livewire.account.update-profile-information', [
            'maskedCpf' => Auth::user()->maskedCpf(),
        ]);
    }
}
