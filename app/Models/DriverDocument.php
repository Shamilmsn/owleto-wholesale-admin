<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverDocument extends Model
{
    use HasFactory;

    protected $appends = [
        'pan_image_url',
        'license_image_url'
    ];

    public function getPanImageUrlAttribute()
    {
        if(!$this->pancard_file){
            return;
        }
        return url('storage/pancards/images/'.$this->pancard_file);
    }

    public function getLicenseImageUrlAttribute()
    {
        if(!$this->license_file){
            return;
        }
        return url('storage/licenses/images/'.$this->license_file);
    }
}
