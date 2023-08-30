<?php

namespace App\Repositories;

use App\Models\DriversPayout;
use App\Models\DriverTransaction;

//use InfyOm\Generator\Common\BaseRepository;

/**
 * Class DriversPayoutRepository
 * @package App\Repositories
 * @version March 25, 2020, 9:48 am UTC
 *
 * @method DriversPayout findWithoutFail($id, $columns = ['*'])
 * @method DriversPayout find($id, $columns = ['*'])
 * @method DriversPayout first($columns = ['*'])
*/
class DriverTransactionRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'user_id',
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return DriverTransaction::class;
    }
}
