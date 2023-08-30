<?php

namespace App\Repositories;

use App\Models\Cart;

/**
 * Class OrderStatusRepository
 * @package App\Repositories
 * @version August 29, 2019, 9:38 pm UTC
 *
 * @method Cart findWithoutFail($id, $columns = ['*'])
 * @method Cart find($id, $columns = ['*'])
 * @method Cart first($columns = ['*'])
*/
class OrderRequestCartRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'product_id',
        'user_id',
        'quantity'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Cart::class;
    }
}
