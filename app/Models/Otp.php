<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Otp extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $dates = [
        'verified_at'
    ];

    public function isExpired()
    {
        return Carbon::now()->gt($this->updated_at->addMinutes(10));
    }

    public function isVerified()
    {
        return !is_null($this->verified_at);
    }

    public function getToken()
    {
       return Crypt::encryptString($this->id);
    }

    public function markAsVerified()
    {
        $this->verified_at = Carbon::now();
        $this->save();
    }
}
