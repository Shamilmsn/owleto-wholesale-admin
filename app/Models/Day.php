<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Day extends Model
{
    use HasFactory;
    public $table = 'days';

    public $fillable = [
        'day_of_week',
        'name'
    ];
}
