<?php

namespace App\Models;

use App\Enums\CardBrand;
use Database\Factories\PaymentCardFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * The full PAN is encrypted at rest and never rendered; the CVV is never persisted (PCI DSS 3.2).
 */
#[Fillable(['holder_name', 'number', 'fingerprint', 'brand', 'last_four', 'exp_month', 'exp_year'])]
#[Hidden(['number', 'fingerprint'])]
class PaymentCard extends Model
{
    /** @use HasFactory<PaymentCardFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'holder_name' => 'encrypted',
            'number' => 'encrypted',
            'brand' => CardBrand::class,
            'exp_month' => 'integer',
            'exp_year' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function maskedNumber(): string
    {
        return '•••• •••• •••• '.$this->last_four;
    }

    public function expiry(): string
    {
        return str_pad((string) $this->exp_month, 2, '0', STR_PAD_LEFT).'/'.$this->exp_year;
    }

    public function isExpired(): bool
    {
        return Carbon::create($this->exp_year, $this->exp_month)->endOfMonth()->isPast();
    }
}
