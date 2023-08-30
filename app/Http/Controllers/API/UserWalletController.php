<?php
/**
 * File name: UserAPIController.php
 * Last modified: 2020.10.29 at 17:03:54
 * Author: Pixbit Solutions - https://pixbitsolutions.com
 * Copyright (c) 2020
 *
 */

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\UserWallet;
use App\Models\UserWalletTransaction;
use App\Repositories\UserRepository;
use App\Repositories\UserWalletRepository;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;

class UserWalletController extends Controller
{
    public function userWalletAmount(Request $request)
    {
        $userWallet = UserWallet::where('user_id', $request->user_id)->first();

        if(!$userWallet){
            return response()->json(['user have no wallet']);
        }

        return $this->sendResponse($userWallet, 'Wallet retrieved successfully');
    }

    public function userWalletHistories(Request $request)
    {
        $userWalletHistories = UserWalletTransaction::where('user_id', $request->user_id)->orderBy('created_at', 'desc')
                     ->get();

        return $this->sendResponse($userWalletHistories, 'Wallet retrieved successfully');
    }
}
