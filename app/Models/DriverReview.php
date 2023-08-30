<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverReview extends Model
{
    use HasFactory;

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function driverUser()
    {
        return $this->belongsTo(User::class,'driver_id','id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
