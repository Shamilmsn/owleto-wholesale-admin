<?php

namespace App\Http\Controllers\API;

use App\Criteria\PickUpDeliveryOrderRequests\UserCriteria;
use App\Models\DeliveryType;
use App\Models\Field;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PaymentMethod;
use App\Models\PickUpDeliveryOrder;
use App\Models\PickUpDeliveryOrderRequest;
use App\Http\Controllers\Controller;
use App\Models\PickUpVehicle;
use App\Models\Slot;
use App\Repositories\OrderRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\PickUpDeliveryOrderRepository;
use App\Repositories\PickUpDeliveryOrderRequestRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Razorpay\Api\Api;

class PickUpDeliveryOrderRequestAPIController extends Controller
{
    /** @var  PickUpDeliveryOrderRequestRepository */
    private $pickUpDeliveryOrderRequestRepository;

    /** @var  OrderRepository */
    private $orderRepository;

    /** @var  PickUpDeliveryOrderRepository */
    private $pickUpDeliveryOrderRepository;

    /** @var  PaymentRepository */
    private $paymentRepository;

    public function __construct(PickUpDeliveryOrderRequestRepository $pickUpDeliveryOrderRequestRepository,
                                OrderRepository $orderRepository, PickUpDeliveryOrderRepository $pickUpDeliveryOrderRepository,
                                PaymentRepository $paymentRepository)
    {
        $this->pickUpDeliveryOrderRequestRepository = $pickUpDeliveryOrderRequestRepository;
        $this->orderRepository = $orderRepository;
        $this->pickUpDeliveryOrderRepository = $pickUpDeliveryOrderRepository;
        $this->paymentRepository = $paymentRepository;
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
//        try {
//            $this->pickUpDeliveryOrderRequestRepository->pushCriteria(new RequestCriteria($request));
//            $this->pickUpDeliveryOrderRequestRepository->pushCriteria(new LimitOffsetCriteria($request));
//            $this->pickUpDeliveryOrderRequestRepository->pushCriteria(new UserCriteria($request->user_id));
//        } catch (RepositoryException $e) {
//            return $this->sendError($e->getMessage());
//        }

        $pickUpDeliveryOrderRequests = PickUpDeliveryOrderRequest::query()
            ->where('user_id', $request->user_id)
            ->with('slot', 'pickUpVehicle')
            ->WhereHas('pickupDeliveryOrder.order', function ($query) {
                $query->where('payment_method_id', PaymentMethod::PAYMENT_METHOD_RAZORPAY)
                            ->where('payment_status', 'SUCCESS');
            })
            ->orWhereHas('pickupDeliveryOrder.order', function ($query) {
                $query->where('payment_method_id', PaymentMethod::PAYMENT_METHOD_COD)
                    ->whereIn('payment_status', ['SUCCESS', 'PENDING']);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->sendResponse($pickUpDeliveryOrderRequests->toArray(), 'Pickup order requests retrieved successfully');
    }

    public function store(Request $request)
    {

        $request->validate([
            'user_id' => 'required',
            'name' => 'required',
            'phone' => 'required',
            'delivery_latitude' => 'required',
            'delivery_longitude' => 'required',
            'pickup_latitude' => 'required',
            'pickup_longitude' => 'required',
            'delivery_address' => 'required',
            'pickup_address' => 'required',
            'distance_in_kilometer' => 'required',
            'pick_up_vehicle_id' => 'required'
        ]);

        $input = $request->all();
        $input['status'] = 'PENDING';

        if($request->hasFile('audio_file')){

            $file = $request->file('audio_file');
            $fileName = 'AUDIO_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/pickup-requests/audios/', $fileName);
            $input['audio_file'] = $fileName;
        }

        $deliveryType = DeliveryType::find($request->delivery_type_id);
        $vehicle = PickUpVehicle::find($request->pick_up_vehicle_id);
        $baseDistance = $deliveryType->base_distance;

//        if ($request->distance_in_kilometer <= $baseDistance) {
//            $amount = $deliveryType->charge;
//        }
//        else {
//            $distance = $request->distance_in_kilometer - $baseDistance;
//            $amount = $deliveryType->charge + $distance * $deliveryType->additional_amount;
//        }

        if ($request->distance_in_kilometer <= $vehicle->base_distance) {
            $amount = $vehicle->amount_per_kilometer;
        }
        else {
            $distance = $request->distance_in_kilometer - $baseDistance;
            $amount = $vehicle->amount_per_kilometer + $distance * $vehicle->additional_amount;
        }

        $input['is_used'] = true;
        $input['net_amount'] = $amount;
        $input['delivery_type_id'] = $request->delivery_type_id;

        $pickUpDeliveryOrderRequest = $this->pickUpDeliveryOrderRequestRepository->create($input);

        $pickUpDeliveryOrderRequest = PickUpDeliveryOrderRequest::with('slot')
            ->where('id', $pickUpDeliveryOrderRequest->id)
            ->first();
        
        return $this->sendResponse($pickUpDeliveryOrderRequest,'Pickup order request created successfully');

    }
}
