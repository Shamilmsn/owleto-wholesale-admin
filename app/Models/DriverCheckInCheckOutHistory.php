<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverCheckInCheckOutHistory extends Model
{
    use HasFactory;

    public $table = 'driver_check_in_checkout_histories';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = [
        'user_id',
        'type',
        'latitude',
        'longitude',
    ];
}
