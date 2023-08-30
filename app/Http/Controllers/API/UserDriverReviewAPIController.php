<?php

namespace App\Http\Controllers\API;

use App\Http\Resources\DriverReviewResource;
use App\Models\DriverReview;
use App\Models\MarketReview;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserDriverReviewAPIController extends Controller
{
    public function index(Request $request)
    {
        $driverReview = DriverReview::query()
            ->where('order_id', $request->order_id)
            ->where('user_id', $request->user_id)
            ->first();

        if (!$driverReview) {
            return $this->sendResponse(null, 'Driver Reviews retrieved successfully');
        }

        $driverReview = new DriverReviewResource($driverReview);

        return $this->sendResponse($driverReview, 'Driver Reviews retrieved successfully');
    }
}
