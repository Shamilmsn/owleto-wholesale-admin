<?php

namespace App\Http\Controllers\API;

use App\Models\Order;
use App\Models\ProductOrder;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductUserReviewAPIController extends Controller
{
    public function index(Request $request)
    {
        $order = Order::find($request->order_id);
        $productReviews = [];

        if ($order->type == Order::PRODUCT_TYPE) {
            $productIds = ProductOrder::query()
                ->where('order_id', $request->order_id)
                ->pluck('product_id');

            $productReviews = ProductReview::whereIn('product_id', $productIds)
                ->where('user_id', $request->user_id)
                ->get();
        }
        return $this->sendResponse($productReviews, 'Product Reviews retrieved successfully');
    }
}
