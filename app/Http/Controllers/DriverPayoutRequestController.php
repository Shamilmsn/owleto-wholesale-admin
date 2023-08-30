<?php

namespace App\Http\Controllers;

use App\DataTables\DriverPayoutRequestDataTable;
use App\Models\DriverPayoutRequest;
use Illuminate\Http\Request;

class DriverPayoutRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(DriverPayoutRequestDataTable $driverPayoutRequestDataTable)
    {
        return $driverPayoutRequestDataTable->render('driver-payout-requests.index');
    }

    public function show(DriverPayoutRequest $driverPayoutRequest)
    {

        return view('driver-payout-requests.create', compact('driverPayoutRequest'));
    }

}
