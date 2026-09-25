<?php

namespace App\Models;

use App\Enums\BrazilianState;
use Database\Factories\AddressFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['label', 'recipient_name', 'zip_code', 'street', 'number', 'complement', 'district', 'city', 'state'])]
class Address extends Model
{
    /** @use HasFactory<AddressFactory> */
    use HasFactory;

    /**
     * Personal data is encrypted at rest; city/state stay in clear text for shipping calculation.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'recipient_name' => 'encrypted',
            'zip_code' => 'encrypted',
            'street' => 'encrypted',
            'number' => 'encrypted',
            'complement' => 'encrypted',
            'district' => 'encrypted',
            'state' => BrazilianState::class,
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function formattedZipCode(): string
    {
        return substr($this->zip_code, 0, 5).'-'.substr($this->zip_code, 5);
    }

    /**
     * Immutable copy stored on the order so later edits don't rewrite history.
     *
     * @return array{recipient_name: string, zip_code: string, street: string, number: string, complement: ?string, district: string, city: string, state: string}
     */
    public function toSnapshot(): array
    {
        return [
            'recipient_name' => $this->recipient_name,
            'zip_code' => $this->formattedZipCode(),
            'street' => $this->street,
            'number' => $this->number,
            'complement' => $this->complement,
            'district' => $this->district,
            'city' => $this->city,
            'state' => $this->state->value,
        ];
    }
}
