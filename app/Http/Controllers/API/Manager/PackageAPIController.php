<?php
/**
 * File name: MarketAPIController.php
 * Last modified: 2020.08.13 at 13:43:34
 * Author: Pixbit Solutions - https://pixbitsolutions.com
 * Copyright (c) 2020
 *
 */

namespace App\Http\Controllers\API\Manager;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Flash;
use Illuminate\Http\Request;

class PackageAPIController extends Controller
{

    public function show(Request $request, $id)
    {
        $order = Order::with('packageOrders.package','user','packageOrders.package.product.market','orderStatus','deliveryAddress')
            ->find($id);

        if(!$order){
            return $this->sendError('Order not found');
        }

        return $this->sendResponse($order, 'order retrieved successfully');
    }

}
