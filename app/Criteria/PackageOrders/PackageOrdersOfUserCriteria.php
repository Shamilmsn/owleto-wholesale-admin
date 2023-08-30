<?php
/**
 * File name: OrdersOfUserCriteria.php
 * Last modified: 2020.04.30 at 08:24:08
 * Author: Pixbit Solutions - https://pixbitsolutions.com
 * Copyright (c) 2020
 *
 */

namespace App\Criteria\PackageOrders;

use App\Models\Order;
use App\Models\User;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class OrdersOfUserCriteria.
 *
 * @package namespace App\Criteria\Orders;
 */
class PackageOrdersOfUserCriteria implements CriteriaInterface
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

            $model->join("package_orders", "orders.id", "=", "package_orders.order_id")
                ->join("subscription_packages", "subscription_packages.id", "=", "package_orders.package_id")
                ->join("user_markets", "user_markets.market_id", "=", "subscription_packages.market_id")
                ->where('user_markets.user_id', $this->userId)
                ->where('type', Order::PACKAGE_TYPE)
                ->groupBy('orders.id')
                ->select('orders.*');
        } else {
            return $model;
        }
    }
}
