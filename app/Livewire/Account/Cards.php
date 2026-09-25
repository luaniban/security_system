<?php

namespace App\Livewire\Account;

use App\Enums\CardBrand;
use App\Rules\ValidCardNumber;
use App\Services\AuditLogger;
use App\Support\BlindIndex;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Behind the `password.confirm` middleware (step-up auth).
 * The PAN is encrypted at rest and only the last four digits are ever rendered.
 * The CVV is never collected here: PCI DSS forbids storing it.
 */
#[Title('Cartões')]
class Cards extends Component
{
    public const MAX_CARDS = 5;

    public bool $showForm = false;

    public string $holder_name = '';

    public string $card_number = '';

    public string $exp_month = '';

    public string $exp_year = '';

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function save(AuditLogger $audit): void
    {
        $currentYear = (int) now()->format('Y');

        try {
            $validated = $this->validate([
                'holder_name' => ['required', 'string', 'min:3', 'max:60', 'regex:/^[\pL\s.]+$/u'],
                'card_number' => ['required', 'string', 'max:23', 'regex:/^[\d\s]+$/', new ValidCardNumber],
                'exp_month' => ['required', 'integer', 'between:1,12'],
                'exp_year' => ['required', 'integer', 'between:'.$currentYear.','.($currentYear + 20)],
            ], [
                'holder_name.regex' => 'Use apenas letras, como impresso no cartão.',
            ]);

            $number = preg_replace('/\D/', '', $validated['card_number']);
            $expiry = now()->setDate((int) $validated['exp_year'], (int) $validated['exp_month'], 1)->endOfMonth();

            if ($expiry->isPast()) {
                throw ValidationException::withMessages(['exp_month' => 'Este cartão está vencido.']);
            }

            $user = Auth::user();
            $fingerprint = BlindIndex::make('card', $number);

            if ($user->paymentCards()->where('fingerprint', $fingerprint)->exists()) {
                throw ValidationException::withMessages(['card_number' => 'Este cartão já está cadastrado.']);
            }

            if ($user->paymentCards()->count() >= self::MAX_CARDS) {
                throw ValidationException::withMessages(['card_number' => 'Limite de '.self::MAX_CARDS.' cartões atingido.']);
            }
        } finally {
            // Never send the PAN back to the browser in the component snapshot.
            $this->reset('card_number');
        }

        $card = $user->paymentCards()->create([
            'holder_name' => mb_strtoupper($validated['holder_name']),
            'number' => $number,
            'fingerprint' => $fingerprint,
            'brand' => CardBrand::detect($number),
            'last_four' => substr($number, -4),
            'exp_month' => (int) $validated['exp_month'],
            'exp_year' => (int) $validated['exp_year'],
        ]);

        $audit->log('card.added', $user, ['card_id' => $card->id, 'last_four' => $card->last_four]);

        $this->resetForm();
        session()->flash('card-status', 'Cartão salvo com segurança.');
    }

    public function delete(int $cardId, AuditLogger $audit): void
    {
        $card = Auth::user()->paymentCards()->findOrFail($cardId);
        $this->authorize('delete', $card);

        $card->delete();
        $audit->log('card.removed', Auth::user(), ['card_id' => $cardId]);
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset('showForm', 'holder_name', 'card_number', 'exp_month', 'exp_year');
        $this->resetValidation();
    }

    public function render(): View
    {
        return view('livewire.account.cards', [
            'cards' => Auth::user()->paymentCards()->latest()->get(),
            'years' => range((int) now()->format('Y'), (int) now()->format('Y') + 15),
        ]);
    }
}
