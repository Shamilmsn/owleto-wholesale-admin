<?php

namespace App\Http\Controllers\API;

use App\Http\Resources\MarketReviewResource;
use App\Models\MarketReview;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MarketUserReviewAPIController extends Controller
{
    public function index(Request $request)
    {
        $marketReview = MarketReview::query()
            ->where('market_id', $request->market_id)
            ->where('user_id', $request->user_id)
            ->first();

        if (!$marketReview) {
            return $this->sendResponse(null, 'Market Reviews retrieved successfully');
        }

        $marketReview = new MarketReviewResource($marketReview);

        return $this->sendResponse($marketReview, 'Market Reviews retrieved successfully');
    }
}
