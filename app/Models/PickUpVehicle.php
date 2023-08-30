<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickUpVehicle extends Model
{
    use HasFactory;

    public $table = 'pick_up_vehicles';

    public $fillable = [
        'name',
        'maximum_weight',
        'amount_per_kilometer',
        'base_distance',
        'additional_amount',
    ];

}
