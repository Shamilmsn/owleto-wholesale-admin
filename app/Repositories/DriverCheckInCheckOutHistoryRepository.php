<?php

namespace App\Repositories;

use App\Models\DriverCheckInCheckOutHistory;
use App\Models\User;
//use InfyOm\Generator\Common\BaseRepository;

/**
 * Class UserRepository
 * @package App\Repositories
 * @version July 10, 2018, 11:44 am UTC
 *
 * @method User findWithoutFail($id, $columns = ['*'])
 * @method User find($id, $columns = ['*'])
 * @method User first($columns = ['*'])
*/
class DriverCheckInCheckOutHistoryRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'id',
        'user_id',
        'type',
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return DriverCheckInCheckOutHistory::class;
    }
}
