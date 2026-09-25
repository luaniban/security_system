<?php

namespace App\Models;

use App\Support\Cpf;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * `is_admin` is intentionally NOT fillable to prevent mass-assignment privilege escalation.
 */
#[Fillable(['name', 'email', 'password', 'cpf', 'phone'])]
#[Hidden(['password', 'remember_token', 'cpf', 'cpf_hash', 'phone'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_admin' => false,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Store the CPF digits encrypted (AES-256-CBC + HMAC via APP_KEY) and keep a keyed hash for uniqueness checks.
     */
    protected function cpf(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => $value === null ? null : decrypt($value, false),
            set: fn (string $value): array => [
                'cpf' => encrypt(Cpf::normalize($value), false),
                'cpf_hash' => Cpf::blindIndex($value),
            ],
        );
    }

    protected function phone(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => $value === null ? null : decrypt($value, false),
            set: fn (string $value): string => encrypt(preg_replace('/\D/', '', $value), false),
        );
    }

    public function maskedCpf(): string
    {
        return Cpf::mask($this->cpf);
    }

    public function maskedPhone(): string
    {
        $phone = (string) $this->phone;

        return '('.substr($phone, 0, 2).') *****-'.substr($phone, -4);
    }

    /**
     * @return HasMany<Address, $this>
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    /**
     * @return HasMany<PaymentCard, $this>
     */
    public function paymentCards(): HasMany
    {
        return $this->hasMany(PaymentCard::class);
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'seller_id');
    }

    /**
     * @return HasMany<CartItem, $this>
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Order::class, 'seller_id');
    }
}
