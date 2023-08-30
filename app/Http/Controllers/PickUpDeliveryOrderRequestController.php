<?php

namespace App\Http\Controllers;

use App\DataTables\PickUpDeliveryOrderRequestDataTable;
use App\DataTables\PickUpVehicleDataTable;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Payment;
use App\Models\PickUpDeliveryOrder;
use App\Models\PickUpDeliveryOrderRequest;
use App\Models\PickUpVehicle;
use App\Models\User;
use App\Notifications\OrderRequestPushNotification;
use App\Notifications\PickUpDeliveryOrderAcceptPushNotification;
use App\Repositories\CustomFieldRepository;
use App\Repositories\PickUpDeliveryOrderRequestRepository;
use App\Repositories\PickUpVehicleRepository;
use Illuminate\Http\Request;
use Laracasts\Flash\Flash;
use Prettus\Validator\Exceptions\ValidatorException;

class PickUpDeliveryOrderRequestController extends Controller
{
    /** @var  PickUpDeliveryOrderRequestRepository */
    private $pickUpDeliveryOrderRequestRepository;

    public function __construct(PickUpDeliveryOrderRequestRepository $pickUpDeliveryOrderRequestRepository)
    {
        parent::__construct();
        $this->pickUpDeliveryOrderRequestRepository = $pickUpDeliveryOrderRequestRepository;
    }

    public function index(PickUpDeliveryOrderRequestDataTable $pickUpDeliveryOrderRequestDataTable)
    {
        return $pickUpDeliveryOrderRequestDataTable->render('pickup-delivery-order-requests.index');
    }

    public function edit($id)
    {

        $orderRequest = PickUpDeliveryOrderRequest::findOrFail($id);

        $vehicle = PickUpVehicle::where('id', $orderRequest->pick_up_vehicle_id)->first();
        $netAmount = $orderRequest->distance_in_kilometer * $vehicle->amount_per_kilometer;
        $orderRequest->status = PickUpDeliveryOrderRequest:: STATUS_ACCEPTED;
        $orderRequest->net_amount = $netAmount;
        $orderRequest->save();

        $user = User::where('id', $orderRequest->user_id)->first();
        $userFcmToken = $user->device_token;



        $attributes['title'] = 'Pickup order request accepted';
        $attributes['redirection_type'] = Order::PICKUP_DELIVERY_ORDER_REDIRECTION_TYPE;
        $attributes['message'] = 'Hi, Thank you for choosing Owleto.Your order has been accepted with amount '.$netAmount;
        $attributes['data'] = $orderRequest;

        try {
            \Illuminate\Support\Facades\Notification::route('fcm', $userFcmToken)
                ->notify(new PickUpDeliveryOrderAcceptPushNotification($attributes));

        } catch (\Exception $exception) {

        }

        return redirect(route('pickup-delivery-order-requests.index'));

    }

    public function destroy($id)
    {
        $pickUpDeliveryOrderRequest = PickUpDeliveryOrderRequest::findOrFail($id);

        if (empty($pickUpDeliveryOrderRequest)) {
            Flash::error('Order Status not found');

            return redirect(route('pickup-delivery-order-requests.index'));
        }

        if($pickUpDeliveryOrderRequest->status == PickUpDeliveryOrderRequest::STATUS_ACCEPTED){
            Flash::error(__('Order already accepted'));

            return redirect(route('pickup-delivery-order-requests.index'));
        }

        $pickUpDeliveryOrderRequest->delete();

        Flash::success(__('lang.deleted_successfully'));

        return redirect(route('pickup-delivery-order-requests.index'));
    }
    public function show($id){

        $orderRequest = PickUpDeliveryOrderRequest::findOrFail($id);

        return view('pickup-delivery-order-requests.show',compact('orderRequest'));

    }

    public function statusRejected(Request $request){

        $pickupOrderRequest = PickUpDeliveryOrderRequest::findOrFail($request->pickup_order_request_Id);
        $pickupOrderRequest->status = 'REJECTED';
        $pickupOrderRequest->rejected_reason = $request->rejected_reason;
        $pickupOrderRequest->save();

        Flash::success('Pick Order Request rejected');
        return redirect(route('pickup-delivery-order-requests.index'));

    }

}