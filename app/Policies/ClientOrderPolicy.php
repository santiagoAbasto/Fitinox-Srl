<?php

namespace App\Policies;

use App\Models\ClientOrder;
use App\Models\User;

class ClientOrderPolicy
{
    public function view(User $user, ClientOrder $order): bool
    {
        return $user->is_active && ($order->user_id === $user->id || in_array($user->role, ['super_admin', 'admin'], true));
    }
}
