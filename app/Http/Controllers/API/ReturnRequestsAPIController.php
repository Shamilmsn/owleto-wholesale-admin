<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ProductOrder;
use App\Models\ReturnRequest;
use Illuminate\Support\Facades\DB;

class ReturnRequestsAPIController extends Controller
{
    public function store(\App\Http\Requests\ReturnRequest $request)
    {
        DB::beginTransaction();

        $returnRequest = new ReturnRequest();
        $returnRequest->user_id = $request->user_id;
        $returnRequest->order_id = $request->order_id;
        $returnRequest->product_id = $request->product_id;
        $returnRequest->product_order_id = $request->product_order_id;
        $returnRequest->description = $request->description;
        $returnRequest->amount = $request->amount;
        $returnRequest->save();

        $productOrder = ProductOrder::find($request->product_order_id);
        $productOrder->is_already_requested = true;
        $productOrder->save();

        DB::commit();

        return $this->sendResponse($returnRequest, 'successfully sent return request');
    }

}

