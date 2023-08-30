<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlotedDeliveryDriverHistory extends Model
{
    use HasFactory;

    const STATUS_PICKUP_ASSIGNED = 'PICKUP_ASSIGNED';
    const STATUS_PICKED = 'PICKED';
    const STATUS_DROPPED = 'DROPPED';
    const STATUS_DELIVER_ASSIGNED = 'DELIVER_ASSIGNED';
    const STATUS_DELIVERED = 'DELIVER_ASSIGNED';
    const STATUS_CANCELED = 'DELIVER_CANCELED';
}
