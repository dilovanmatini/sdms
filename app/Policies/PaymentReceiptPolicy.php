<?php

namespace App\Policies;

use App\Authorization\Ability;
use App\Models\PaymentReceipt;
use App\Models\User;

class PaymentReceiptPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAbility(Ability::ManageReceipts);
    }

    public function view(User $user, PaymentReceipt $paymentReceipt): bool
    {
        return $user->hasAbility(Ability::ManageReceipts);
    }

    public function create(User $user): bool
    {
        return $user->hasAbility(Ability::ManageReceipts);
    }

    public function update(User $user, PaymentReceipt $paymentReceipt): bool
    {
        return $user->hasAbility(Ability::ManageReceipts) && $paymentReceipt->isDraft();
    }

    public function delete(User $user, PaymentReceipt $paymentReceipt): bool
    {
        return $user->hasAbility(Ability::ManageReceipts) && $paymentReceipt->isDraft();
    }

    public function post(User $user, PaymentReceipt $paymentReceipt): bool
    {
        return $user->hasAbility(Ability::ManageReceipts) && $paymentReceipt->isDraft();
    }

    public function cancel(User $user, PaymentReceipt $paymentReceipt): bool
    {
        return $user->hasAbility(Ability::ManageReceipts) && $paymentReceipt->isPosted();
    }

    public function print(User $user, PaymentReceipt $paymentReceipt): bool
    {
        return $user->hasAbility(Ability::ManageReceipts) && $paymentReceipt->isPosted();
    }
}
