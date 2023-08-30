<?php

namespace App\Http\Controllers\API\Manager;

use App\Http\Controllers\Controller;
use App\Models\Market;
use App\Models\MarketTransaction;
use App\Models\User;
use Flash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentTransactionAPIController extends Controller
{

    public function index(Request $request)
    {
        $userId = Auth::id();

        $userMarketIds = Market::whereHas('users', function ($query) use($userId){
            $query->where('user_id', $userId);
        })->pluck('id');

        $marketTransactions = MarketTransaction::with('market')
            ->whereIn('market_id', $userMarketIds)
            ->get();

        return $this->sendResponse($marketTransactions, 'Transaction histories retrieved successfully');
    }

}
