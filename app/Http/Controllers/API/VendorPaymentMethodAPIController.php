<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Market;
use App\Models\MarketPaymentMethod;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class VendorPaymentMethodAPIController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $vendorPaymentMethods = Market::with('market_payment_methods')->get();

        return $this->sendResponse($vendorPaymentMethods->toArray(),'Vendor Payment Methods retrieved successfully');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $vendorPayments =  MarketPaymentMethod::where('market_id', $id)
            ->pluck('payment_method_id')->toArray();

        $vendorPaymentMethods = PaymentMethod::query()->whereIn('id', $vendorPayments);

        if($request->is_package){
            $vendorPaymentMethods->where('id', 2);
        }

        $vendorPaymentMethods = $vendorPaymentMethods->get();

        return $this->sendResponse($vendorPaymentMethods,'Vendor Payment Methods retrieved successfully');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
