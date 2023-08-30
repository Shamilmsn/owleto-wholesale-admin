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
use App\Models\Market;
use App\Models\Order;
use App\Models\OrderRequest;
use App\Models\User;
use App\Notifications\OrderRequestPushNotification;
use App\Repositories\OrderRequestRepository;
use App\Repositories\TemporaryOrderRequestRepository;
use Flash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class MarketController
 * @package App\Http\Controllers\API
 */

class OrderRequestAPIController extends Controller
{
    /** @var TemporaryOrderRequestRepository */
    private $temporaryOrderRequestRepository;

    /** @var orderRequestRepository */
    private $orderRequestRepository;



    public function __construct(TemporaryOrderRequestRepository $temporaryOrderRequestRepository, OrderRequestRepository $orderRequestRepository)
    {
        $this->temporaryOrderRequestRepository = $temporaryOrderRequestRepository;
        $this->orderRequestRepository = $orderRequestRepository;
    }

    public function index(Request $request)
    {
        $userMarketIds = Market::whereHas('users', function ($query){
            $query->where('user_id', auth()->id());
        })->pluck('id');

        $orderRequest = OrderRequest::query()
            ->with('market', 'user')
            ->whereIn('market_id', $userMarketIds);

        if($request->market_id){
            $orderRequest = $orderRequest->where('market_id', $request->market_id);
        }

        $orderRequest = $orderRequest->latest()->get();

        return $this->sendResponse($orderRequest->toArray(), 'Orders retrieved successfully');

    }

    public function store(Request $request)
    {
        $input = $request->all();

        $status = OrderRequest::STATUS_NOTIFICATION_SEND;
        $input['status'] = $status;

        $orderRequest = OrderRequest::with('temporaryOrderRequest')->find($request->order_request_id);
        $orderRequest->status = $status;
        $orderRequest->save();

        $user = User::where('id',$orderRequest->user_id)->first();

        if ($request->file('bill_image')) {
            $billImage = $request->file('bill_image');
            $billImageFileName = time() . '.' . $billImage->getClientOriginalExtension();
            $billImage->storeAs('public/order-requests/bill-images/', $billImageFileName);
            $input['bill_image'] = $billImageFileName;
        }
        $input['distance'] = $orderRequest->distance;

        $temporaryOrderRequest = $this->temporaryOrderRequestRepository->create($input);
        info($temporaryOrderRequest->bill_image_url);

        $orderRequestData = $this->orderRequestRepository->with('temporaryOrderRequest')->findWithoutFail($request->order_request_id);
        $totalAmount = $orderRequest->delivery_fee + $temporaryOrderRequest->net_amount;
        $userFcmToken = $user->device_token;

        $attributes['title'] = 'Owleto manual order bill';
        $attributes['redirection_type'] = Order::TEMPORARY_ORDER_REDIRECTION_TYPE;

        $attributes['message'] = 'Hi, Thank you for choosing Owleto.Your order has been updated with bill amount '.$totalAmount.' Please refer the bill attached here.';
        $url = $temporaryOrderRequest->bill_image_url;
        $attributes['image'] = $url;
        $attributes['data'] = $orderRequestData->toArray();


        try {
            \Illuminate\Support\Facades\Notification::route('fcm', $userFcmToken)
                ->notify(new OrderRequestPushNotification($attributes));

        } catch (\Exception $exception) {

        }

        return $this->sendResponse($orderRequest, 'Orders retrieved successfully');
    }

    public function show(Request $request, $id)
    {
        $orderRequest = OrderRequest::query()
            ->with('temporaryOrderRequest', 'market', 'deliveryType', 'user')
            ->where('id', $id)->first();

        return $this->sendResponse($orderRequest, 'Orders retrieved successfully');
    }

}
