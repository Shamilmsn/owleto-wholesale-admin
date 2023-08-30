<?php

namespace App\Repositories;

use App\Models\TemporaryOrderRequest;

//use InfyOm\Generator\Common\BaseRepository;

/**
 * Class CategoryRepository
 * @package App\Repositories
 * @version April 11, 2020, 1:57 pm UTC
 *
 * @method TemporaryOrderRequest findWithoutFail($id, $columns = ['*'])
 * @method TemporaryOrderRequest find($id, $columns = ['*'])
 * @method TemporaryOrderRequest first($columns = ['*'])
 */
class TemporaryOrderRequestRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'order_request_id',
        'user_id',
        'net_amount',
        'status',

    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return TemporaryOrderRequest::class;
    }
}
