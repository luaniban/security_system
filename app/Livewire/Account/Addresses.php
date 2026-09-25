<?php

namespace App\Livewire\Account;

use App\Enums\BrazilianState;
use App\Models\Address;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Endereços')]
class Addresses extends Component
{
    public const MAX_ADDRESSES = 10;

    #[Locked]
    public ?int $editingId = null;

    public bool $showForm = false;

    public string $label = '';

    public string $recipient_name = '';

    public string $zip_code = '';

    public string $street = '';

    public string $number = '';

    public string $complement = '';

    public string $district = '';

    public string $city = '';

    public string $state = '';

    public function create(): void
    {
        $this->resetForm();
        $this->recipient_name = Auth::user()->name;
        $this->showForm = true;
    }

    public function edit(int $addressId): void
    {
        // Always resolved through the owner's relationship: another user's id simply isn't found (no IDOR).
        $address = $this->findOwnedAddress($addressId);
        $this->authorize('update', $address);

        $this->resetValidation();
        $this->editingId = $address->id;
        $this->fill([
            'label' => $address->label,
            'recipient_name' => $address->recipient_name,
            'zip_code' => $address->formattedZipCode(),
            'street' => $address->street,
            'number' => $address->number,
            'complement' => (string) $address->complement,
            'district' => $address->district,
            'city' => $address->city,
            'state' => $address->state->value,
        ]);
        $this->showForm = true;
    }

    public function save(AuditLogger $audit): void
    {
        $validated = $this->validate([
            'label' => ['required', 'string', 'max:50'],
            'recipient_name' => ['required', 'string', 'min:3', 'max:120', 'regex:/^[\pL\s\'.-]+$/u'],
            'zip_code' => ['required', 'string', 'regex:/^\d{5}-?\d{3}$/'],
            'street' => ['required', 'string', 'max:150'],
            'number' => ['required', 'string', 'max:10', 'regex:/^[\pL\d\s\/-]+$/u'],
            'complement' => ['nullable', 'string', 'max:60'],
            'district' => ['required', 'string', 'max:80'],
            'city' => ['required', 'string', 'max:80', 'regex:/^[\pL\s\'.-]+$/u'],
            'state' => ['required', Rule::enum(BrazilianState::class)],
        ], [
            'zip_code.regex' => 'Informe o CEP no formato 00000-000.',
            'recipient_name.regex' => 'O nome deve conter apenas letras.',
            'city.regex' => 'A cidade deve conter apenas letras.',
        ]);

        $validated['zip_code'] = preg_replace('/\D/', '', $validated['zip_code']);
        $validated['complement'] = $validated['complement'] ?: null;

        $user = Auth::user();

        if ($this->editingId) {
            $address = $this->findOwnedAddress($this->editingId);
            $this->authorize('update', $address);
            $address->update($validated);
            $audit->log('address.updated', $user, ['address_id' => $address->id]);
        } else {
            if ($user->addresses()->count() >= self::MAX_ADDRESSES) {
                throw ValidationException::withMessages(['label' => 'Limite de '.self::MAX_ADDRESSES.' endereços atingido.']);
            }

            $address = $user->addresses()->create($validated);
            $audit->log('address.created', $user, ['address_id' => $address->id]);
        }

        $this->resetForm();
        session()->flash('address-status', 'Endereço salvo.');
    }

    public function delete(int $addressId, AuditLogger $audit): void
    {
        $address = $this->findOwnedAddress($addressId);
        $this->authorize('delete', $address);

        $address->delete();
        $audit->log('address.deleted', Auth::user(), ['address_id' => $addressId]);

        if ($this->editingId === $addressId) {
            $this->resetForm();
        }
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    private function findOwnedAddress(int $addressId): Address
    {
        return Auth::user()->addresses()->findOrFail($addressId);
    }

    private function resetForm(): void
    {
        $this->reset('editingId', 'showForm', 'label', 'recipient_name', 'zip_code', 'street', 'number', 'complement', 'district', 'city', 'state');
        $this->resetValidation();
    }

    public function render(): View
    {
        return view('livewire.account.addresses', [
            'addresses' => Auth::user()->addresses()->latest()->get(),
            'states' => BrazilianState::cases(),
        ]);
    }
}
