<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Paid = 'paid';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Paid => 'Pagamento aprovado',
            self::Shipped => 'Enviado',
            self::Delivered => 'Entregue',
            self::Cancelled => 'Cancelado',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Paid => 'bg-blue-100 text-blue-800',
            self::Shipped => 'bg-amber-100 text-amber-800',
            self::Delivered => 'bg-green-100 text-green-800',
            self::Cancelled => 'bg-gray-200 text-gray-700',
        };
    }
}
