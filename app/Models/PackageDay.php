<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageDay extends Model
{
    use HasFactory;
    protected  $table = 'package_days';

    public $fillable = [
        'package_id',
        'day_id'

    ];

    public function day()
    {
        return $this->belongsTo(Day::class, 'day_id', 'id');
    }

}
