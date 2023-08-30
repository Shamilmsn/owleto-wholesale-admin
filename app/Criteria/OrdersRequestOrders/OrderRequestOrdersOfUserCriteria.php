<?php
/**
 * File name: OrdersOfUserCriteria.php
 * Last modified: 2020.04.30 at 08:24:08
 * Author: Pixbit Solutions - https://pixbitsolutions.com
 * Copyright (c) 2020
 *
 */

namespace App\Criteria\OrdersRequestOrders;

use App\Models\Market;
use App\Models\Order;
use App\Models\User;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class OrdersOfUserCriteria.
 *
 * @package namespace App\Criteria\Orders;
 */
class OrderRequestOrdersOfUserCriteria implements CriteriaInterface
{
    /**
     * @var User
     */
    private $userId;

    /**
     * OrdersOfUserCriteria constructor.
     */
    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Apply criteria in query repository
     *
     * @param string $model
     * @param RepositoryInterface $repository
     *
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        if (auth()->user()->hasRole('admin')) {
            return $model;
        } else if (auth()->user()->hasRole('manager')) {

            $userId = auth()->id();
            
            $userMarkets = Market::whereHas('users', function ($query) use ($userId){
                $query->where('id', $userId);
            })->pluck('id');

            return $model->where('type', Order::ORDER_REQUEST_TYPE)
                ->whereIn('market_id', $userMarkets)
                ->groupBy('orders.id')
                ->select('orders.*');
        } else {
            return $model;
        }
    }
}
