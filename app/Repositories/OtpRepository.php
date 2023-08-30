<?php

namespace App\Repositories;

use App\Models\Otp;

//use InfyOm\Generator\Common\BaseRepository;

/**
 * Class OtpRepository
 * @package App\Repositories
 * @version April 6, 2020, 10:56 am UTC
 *
 * @method Otp findWithoutFail($id, $columns = ['*'])
 * @method Otp find($id, $columns = ['*'])
 * @method Otp first($columns = ['*'])
*/
class OtpRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'code',
        'phone',
        'email',
        'verified_at',
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Otp::class;
    }
}
