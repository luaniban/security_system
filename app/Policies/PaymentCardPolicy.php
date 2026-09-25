<?php

namespace App\Policies;

use App\Models\PaymentCard;
use App\Models\User;

class PaymentCardPolicy
{
    public function delete(User $user, PaymentCard $paymentCard): bool
    {
        return $user->id === $paymentCard->user_id;
    }
}
