<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserRoleChangedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $user;
//    public $deliveryFee;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user
//        ,$deliveryFee
    )
    {
        $this->user = $user;
//        $this->deliveryFee = $deliveryFee;
    }

}
