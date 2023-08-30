<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchantRequest extends Model
{
    use HasFactory;

    public $fillable = [
        'name',
        'email',
        'phone',
        'status',
        'description'
    ];

    const PENDING_STATUS = 'PENDING';
    const CONTACTED_STATUS = 'CONTACTED';
    const REJECTED_STATUS = 'REJECTED';

    static $statuses = [
        'PENDING',
        'CONTACTED',
        'REJECTED',
    ];
}
