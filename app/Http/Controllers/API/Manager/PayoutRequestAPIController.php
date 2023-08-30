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
use App\Http\Requests\MarketPayoutRequestStore;
use App\Models\AppSetting;
use App\Models\Earning;
use App\Models\MarketPayoutRequest;
use Flash;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PayoutRequestAPIController extends Controller
{

    public function store(MarketPayoutRequestStore $request)
    {
        $input = $request->all();

        $appSetting = AppSetting::where('key','min_payout_amount')->first();

        $marketEarning = Earning::where('market_id', $input['market_id'])->first();

        $marketpayoutRequestAmount = MarketPayoutRequest::where('market_id', $input['market_id'])
            ->where('status', MarketPayoutRequest::PENDING)
            ->sum('amount');

        $earning = $marketEarning->market_balance;

        $amount = $marketpayoutRequestAmount+$input['amount'];

        if($amount > $earning) {
            $errorMsg = 'Already sent payout request';
            return $this->sendError($errorMsg,409);
        }

        if($earning < $input['amount']){
            $errorMsg = 'Does not have sufficient balance';
            return $this->sendError($errorMsg,409);
        }

        if($earning < $appSetting->value){
            $errorMsg = 'Amount must be greater than'. $appSetting->value;
            return $this->sendError($errorMsg,409);
        }

        $marketpayoutRequest = new MarketPayoutRequest();
        $marketpayoutRequest->user_id = Auth::id();
        $marketpayoutRequest->market_id = $input['market_id'];
        $marketpayoutRequest->amount = $request->amount;
        $marketpayoutRequest->status = MarketPayoutRequest::PENDING;
        $marketpayoutRequest->save();

        return $this->sendResponse($marketpayoutRequest, 'Payout request sent successfully');
    }

}
