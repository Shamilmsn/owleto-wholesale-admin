<?php
/**
 * File name: UserAPIController.php
 * Last modified: 2020.10.29 at 17:03:54
 * Author: Pixbit Solutions - https://pixbitsolutions.com
 * Copyright (c) 2020
 *
 */

namespace App\Http\Controllers\API\Driver;

use App\Http\Controllers\Controller;
use App\Models\DriverReview;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriverReviewAPIController extends Controller
{
    public function index()
    {
        $driverReviews = DriverReview::with('driver')
            ->where('user_id', Auth::id())
            ->get();

        return $this->sendResponse($driverReviews, 'Review listed successfully');
    }

    public function store(Request $request)
    {
        $driverReview = DriverReview::where('driver_id', $request->driver_id)
            ->where('user_id', Auth::id())
            ->first();

        if (! $driverReview) {
            $driverReview = new DriverReview();
        }

        $driverReview->user_id = Auth::id();
        $driverReview->driver_id = $request->driver_id;
        $driverReview->order_id = $request->order_id;
        $driverReview->rating = $request->rating;
        $driverReview->review = $request->review;
        $driverReview->save();

        return $this->sendResponse($driverReview, 'Review added successfully');
    }
}
