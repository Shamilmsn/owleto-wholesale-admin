<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    protected $table = 'areas';

    public $fillable = [
        'name',
        'city_id',
        'covered_kilometer',
        'address',
        'updated_at'

    ];
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'city_id' => 'integer',
        'covered_kilometer' => 'double',
        'address' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'name' => 'required',
        'city_id' => 'required',
//        'covered_kilometer' => 'required',
    ];
    public function city()
    {
        return $this->belongsTo(\App\Models\City::class);
    }

}
