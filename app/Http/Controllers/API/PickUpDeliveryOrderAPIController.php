<?php

namespace App\Http\Controllers\API;

use App\Criteria\Orders\OrdersOfStatusesCriteria;
use App\Criteria\Orders\TypeCriteria;
use App\Criteria\PickUpDeliveryOrderRequests\UserCriteria;
use App\Models\City;
use App\Models\DriversCurrentLocation;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\PickUpDeliveryOrderRequest;
use App\Http\Controllers\Controller;
use App\Repositories\OrderRepository;
use App\Repositories\PickUpDeliveryOrderRequestRepository;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;

class PickUpDeliveryOrderAPIController extends Controller
{
    /** @var  OrderRepository */
    private $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * Display a listing of the Order.
     * GET|HEAD /orders
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $type = Order::PICKUP_DELIVERY_ORDER_TYPE;

        try {
            $this->orderRepository->pushCriteria(new RequestCriteria($request));
            $this->orderRepository->pushCriteria(new LimitOffsetCriteria($request));
            $this->orderRepository->pushCriteria(new OrdersOfStatusesCriteria($request));
            $this->orderRepository->pushCriteria(new \App\Criteria\Orders\UserCriteria($request->user_id));
            $this->orderRepository->pushCriteria(new TypeCriteria($type));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }

        $orders = $this->orderRepository->all();

        $userOrders = [];

        foreach ($orders as $order){
            if($order->payment_method_id == PaymentMethod::PAYMENT_METHOD_RAZORPAY){
                if($order->payment_status == 'SUCCESS'){
                    array_push($userOrders, $order);
                }
            }
            if($order->payment_method_id == PaymentMethod::PAYMENT_METHOD_COD){
                if($order->payment_status == 'SUCCESS' || $order->payment_status == 'PENDING' ){
                    array_push($userOrders, $order);
                }
            }
        }

        return $this->sendResponse($userOrders, 'PickUp orders retrieved successfully');
    }

    public function checkAvailability(Request $request)
    {
        $pickupLatitude = $request->pickup_latitude;
        $pickupLongitude = $request->pickup_longitude;
        $deliveryLatitude = $request->delivery_latitude;
        $deliveryLongitude = $request->delivery_longitude;

        $cities = City::get();

        foreach ($cities as $city){
            if(DriversCurrentLocation::getDriverCurrentLocations($city->center_latitude, $city->center_longitude,
                    $pickupLatitude, $pickupLongitude, "K") < $city->radius) {

                if($deliveryLatitude && $deliveryLongitude){
                    if(DriversCurrentLocation::getDriverCurrentLocations($city->center_latitude, $city->center_longitude,
                            $deliveryLatitude, $deliveryLongitude, "K") < $city->radius) {

                        return $this->sendResponse(['is_pickup_available' => true], null);

                    }
                }
                else{
                    return $this->sendResponse(['is_pickup_available' => true], null);
                }
            }
        }

        return $this->sendResponse(['is_pickup_available' => false], null);

    }

}
