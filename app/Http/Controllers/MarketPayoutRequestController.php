<?php

namespace App\Http\Controllers;

use App\DataTables\MarketPayoutRequestDataTable;
use App\Models\MarketPayoutRequest;

class MarketPayoutRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(MarketPayoutRequestDataTable $marketPayoutRequestDataTable)
    {
        return $marketPayoutRequestDataTable->render('market-payout-requests.index');
    }

    public function show(MarketPayoutRequest $marketPayoutRequest)
    {
        return view('market-payout-requests.create', compact('marketPayoutRequest'));
    }

}
