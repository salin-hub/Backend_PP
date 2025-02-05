<?php

namespace App\Policies;

use App\Models\User;

class OrderPolicy
{
    public function updateOrderStatus(User $user)
    {
        return $user->is_admin;
    }
   
    public function __construct()
    {
        //
    }
}
