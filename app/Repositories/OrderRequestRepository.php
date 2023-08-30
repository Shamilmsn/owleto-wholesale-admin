<?php

namespace App\Repositories;

use App\Models\OrderRequest;

//use InfyOm\Generator\Common\BaseRepository;

/**
 * Class OrderRepository
 * @package App\Repositories
 * @version August 31, 2019, 11:11 am UTC
 *
 * @method OrderRequest findWithoutFail($id, $columns = ['*'])
 * @method OrderRequest find($id, $columns = ['*'])
 * @method OrderRequest first($columns = ['*'])
 */
class OrderRequestRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [

        'user_id',
        'market_id',
        'sector_id',
        'order_id',
        'type',
        'order_text',
        'status',
        'reviewed_by',
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return OrderRequest::class;
    }
}
