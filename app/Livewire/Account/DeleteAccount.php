<?php

namespace App\Livewire\Account;

use App\Enums\OrderStatus;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Component;

/**
 * LGPD art. 18 - the data subject may request deletion of their personal data.
 */
class DeleteAccount extends Component
{
    public string $password = '';

    public bool $confirming = false;

    public function deleteUser(AuditLogger $audit): void
    {
        try {
            $this->validate(['password' => ['required', 'string', 'current_password']]);
        } finally {
            $this->reset('password');
        }

        $user = Auth::user();
        $openStatuses = [OrderStatus::Paid, OrderStatus::Shipped];

        if ($user->purchases()->whereIn('status', $openStatuses)->exists() || $user->sales()->whereIn('status', $openStatuses)->exists()) {
            throw ValidationException::withMessages([
                'password' => 'Você possui pedidos em andamento. Conclua-os antes de excluir a conta.',
            ]);
        }

        $audit->log('account.deleted', null, ['user_id' => $user->id]);

        $imagePaths = $user->products()->whereNotNull('image_path')->pluck('image_path')->all();

        Auth::guard('web')->logout();
        $user->delete();
        Storage::disk('public')->delete($imagePaths);

        session()->invalidate();
        session()->regenerateToken();

        $this->redirect(route('home'));
    }

    public function render(): View
    {
        return view('livewire.account.delete-account');
    }
}
