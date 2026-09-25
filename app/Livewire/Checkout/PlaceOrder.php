<?php

namespace App\Livewire\Checkout;

use App\Exceptions\CheckoutException;
use App\Exceptions\PaymentDeclinedException;
use App\Models\Address;
use App\Services\AuditLogger;
use App\Services\CheckoutService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Finalizar compra')]
class PlaceOrder extends Component
{
    public int|string|null $addressId = null;

    public int|string|null $cardId = null;

    public string $cvv = '';

    public function mount(): void
    {
        $user = Auth::user();

        if (! $user->cartItems()->exists()) {
            $this->redirect(route('cart'));

            return;
        }

        $this->addressId = $user->addresses()->latest()->value('id');
        $this->cardId = $user->paymentCards()->latest()->value('id');
    }

    public function placeOrder(CheckoutService $checkout, AuditLogger $audit): void
    {
        $user = Auth::user();

        try {
            $this->validate([
                // Rule::exists scoped to the buyer: ids from other users are rejected.
                'addressId' => ['required', 'integer', Rule::exists('addresses', 'id')->where('user_id', $user->id)],
                'cardId' => ['required', 'integer', Rule::exists('payment_cards', 'id')->where('user_id', $user->id)],
                'cvv' => ['required', 'string', 'regex:/^\d{3,4}$/'],
            ], [
                'cvv.regex' => 'O CVV deve ter 3 ou 4 dígitos.',
            ]);

            $cvv = $this->cvv;
        } finally {
            $this->reset('cvv');
        }

        $rateKey = 'checkout|'.$user->id;

        if (RateLimiter::tooManyAttempts($rateKey, 5)) {
            $this->addError('payment', 'Muitas tentativas de pagamento. Aguarde '.RateLimiter::availableIn($rateKey).' segundos.');

            return;
        }

        RateLimiter::hit($rateKey, 60);

        try {
            $orders = $checkout->placeOrder(
                $user,
                $user->addresses()->findOrFail((int) $this->addressId),
                $user->paymentCards()->findOrFail((int) $this->cardId),
                $cvv,
            );
        } catch (PaymentDeclinedException $exception) {
            $audit->log('payment.declined', $user, ['card_id' => $this->cardId]);
            $this->addError('payment', $exception->getMessage());

            return;
        } catch (CheckoutException $exception) {
            $this->addError('payment', $exception->getMessage());

            return;
        }

        $this->dispatch('cart-updated');
        session()->flash('status', $orders->count() > 1
            ? "Compra realizada! Foram gerados {$orders->count()} pedidos (um por vendedor)."
            : 'Compra realizada com sucesso!');

        $this->redirect(route('orders.index'));
    }

    public function render(CheckoutService $checkout): View
    {
        $user = Auth::user();
        $address = $this->addressId ? $user->addresses()->find((int) $this->addressId) : null;
        $groups = $checkout->summary($user, $address instanceof Address ? $address : null);

        return view('livewire.checkout.place-order', [
            'addresses' => $user->addresses()->latest()->get(),
            'cards' => $user->paymentCards()->latest()->get(),
            'groups' => $groups,
            'subtotal' => $groups->sum('subtotal_cents'),
            'shipping' => $groups->sum('shipping_cents'),
            'total' => $groups->sum('total_cents'),
            'hasAddress' => $address !== null,
        ]);
    }
}
