<?php

namespace App\Policies;

use App\Models\ClientPriceList;
use App\Models\User;

class ClientPriceListPolicy
{
    public function view(User $user, ClientPriceList $priceList): bool
    {
        return $user->is_active && $user->role === 'client' && $priceList->is_active;
    }
}
