<?php

namespace App\Http\Controllers;

use App\Criteria\Orders\OrdersOfUserCriteria;
use App\Criteria\Users\ClientsCriteria;
use App\Criteria\Users\DriversOfMarketCriteria;
use App\DataTables\PickUpDeliveryOrderDataTable;
use App\DataTables\PickUpDeliveryOrderDetailDataTable;
use App\Events\OrderChangedEvent;
use App\Mail\OrderDeliveredMail;
use App\Models\Driver;
use App\Models\DriversCurrentLocation;
use App\Models\DriverTransaction;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\OwletoEarning;
use App\Models\PackageOrder;
use App\Models\PickUpDeliveryOrder;
use App\Models\PickUpDeliveryOrderRequest;
use App\Models\User;
use App\Notifications\DriverAssignedNotification;
use App\Notifications\DriverAssignedNotificationToUser;
use App\Notifications\OrderDeliveredPushNotifictaion;
use App\Repositories\OrderRepository;
use App\Repositories\OrderStatusRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Kreait\Firebase\Contract\Database;
use Laracasts\Flash\Flash;
use Prettus\Validator\Exceptions\ValidatorException;
use Razorpay\Tests\RegisterEmandateTest;

class PickUpDeliveryOrderController extends Controller
{

    /** @var  OrderRepository */
    private $orderRepository;

    /** @var  OrderStatusRepository */
    private $orderStatusRepository;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct(OrderRepository $orderRepository, OrderStatusRepository $orderStatusRepository, Database $database)
    {
        parent::__construct();

        $this->orderRepository = $orderRepository;
        $this->orderStatusRepository = $orderStatusRepository;
        $this->database = $database;
        $this->table = 'user_locations';
    }

    public function index(PickUpDeliveryOrderDataTable $pickUpDeliveryOrderDataTable)
    {
        $drivers = Driver::where('admin_approved', 1)
            ->whereHas('user', function ($query) {
                $query->where('driver_signup_status', 5);
            })
            ->with('user')
            ->get();

        return $pickUpDeliveryOrderDataTable->render('pickup_delivery_orders.index', compact('drivers'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PickUpDeliveryOrder  $pickUpDeliveryOrder
     * @return \Illuminate\Http\Response
     */
    public function show(PickUpDeliveryOrderDetailDataTable $pickUpDeliveryOrderDetailDataTable, $id)
    {
        $order = $this->orderRepository->findWithoutFail($id);

        if (empty($order)) {

            Flash::error(__('lang.not_found', ['operator' => __('lang.order')]));

            return redirect(route('pickup-delivery-orders.index'));
        }

        return $pickUpDeliveryOrderDetailDataTable->with('id', $id)-> render('pickup_delivery_orders.show',
            ["order" => $order
        ]);
    }

    public function edit($id)
    {
        $order = $this->orderRepository->findWithoutFail($id);

        if (empty($order)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.order')]));

            return redirect(route('pickup-delivery-orders.index'));
        }

        $orderStatus = $this->orderStatusRepository->whereNotIn('id', [OrderStatus::STATUS_ON_THE_WAY, OrderStatus::STATUS_PREPARING, OrderStatus::STATUS_READY])->pluck('status', 'id');

        return view('pickup_delivery_orders.edit')
            ->with('order', $order)
            ->with("orderStatus", $orderStatus);

    }

    public function update(Request $request, $id)
    {

        $orderStatusId = $request->order_status_id;
        $input = $request->all();

        $order = Order::findOrFail($id);

        if($order->order_status_id == OrderStatus::STATUS_DELIVERED){

            \Laracasts\Flash\Flash::error(__('Order canceled', ['operator' => __('lang.order')]));
            return redirect(route('pickup-delivery-orders.edit', $id));
        }

        $orderStatus = OrderStatus::findOrFail($orderStatusId);
        $pickUpDeliveryOrder = PickUpDeliveryOrder::where('order_id', $id)->first();
        $pickUpOrderRequest = PickUpDeliveryOrderRequest::where('id', $pickUpDeliveryOrder->pick_up_delivery_order_request_id)->first();

        if($order->order_status_id == OrderStatus::STATUS_DELIVERED){
            Flash::error('Order already delivered');
            return redirect(route('pickup-delivery-orders.edit', $id));
        }

        try {

            if ($orderStatusId == OrderStatus::STATUS_DRIVER_ASSIGNED) {

                if($order->driver_id){
                    \Laracasts\Flash\Flash::error(__('Driver already assigned', ['operator' => __('lang.order')]));
                    return redirect(route('pickup-delivery-orders.edit', $id));
                }

                $latMarket = $pickUpOrderRequest->pickup_latitude;
                $longMarket = $pickUpOrderRequest->pickup_longitude;

                $references = $this->database->getReference($this->table)->getValue();

                foreach ($references as $reference){

                    if (array_key_exists("user_id", $reference)) {

                        $currentDriverLatitude = $reference['latitude'];
                        $currentDriverLongitude = $reference['longitude'];

                        if (DriversCurrentLocation::getDriverCurrentLocations($latMarket, $longMarket, $currentDriverLatitude,
                                $currentDriverLongitude, "K") < 5) {
                            $driver = Driver::where('user_id', $reference['user_id'])->first();

                            if ($driver) {
                                $driverId = $driver->id;
                                DriversCurrentLocation::updateCurrentLocation($driverId,
                                    $currentDriverLatitude, $currentDriverLongitude);
                            }

                        }
                    }
                }

                $driversCurrentLocations = DriversCurrentLocation::getAvailableDriver($latMarket, $longMarket, null);

                if(!$driversCurrentLocations){
                    \Laracasts\Flash\Flash::error(__('No driver found', ['operator' => __('lang.order')]));
                    return redirect(route('pickup-delivery-orders.edit', $id));
                }

                $order = $this->orderRepository->update($input, $id);

                $order->driver_id = $driversCurrentLocations->driver->user_id;
                $order->driver_assigned_at = Carbon::now();

                if ($order->driver_id) {
                    $distance = $order->distance;

                    if ($distance <= $driversCurrentLocations->driver->base_distance) {
                        $driverCommissionAmount = $driversCurrentLocations->driver->delivery_fee;
                    }
                    else {
                        $additionalDistance = $order->distance - $driversCurrentLocations->driver->base_distance;
                        $baseDeliveryAmount = $driversCurrentLocations->driver->delivery_fee;
                        $additionalDeliveryAmount = $driversCurrentLocations->driver->additional_amount;
                        $driverCommissionAmount = $baseDeliveryAmount + $additionalDistance * $additionalDeliveryAmount;
                    }

                    $order->driver_commission_amount = round($driverCommissionAmount,2);
                }

                $order->save();

                $driver = Driver::where('id', $driversCurrentLocations->driver_id)->first();
                $driver->available = 0;
                $driver->save();

                try {

                    $userOrder = Order::findOrFail($id);
                    $correspondingDriver = User::findorFail($driver->user_id);
                    $driverFcmToken = $correspondingDriver->device_token;
                    // select only order detail  for fcm notification

                    $attributes['title'] = 'Owleto Order';
                    $attributes['message'] ='Owleto Order with OrderID : ' .$userOrder->id .' has been Assigned to you.';
                    $attributes['data'] = $userOrder->toArray();

                    Notification::route('fcm', $driverFcmToken)
                        ->notify(new DriverAssignedNotification($attributes));

                }catch (\Exception $e) {

                }
                try {

                    $userOrder = Order::findOrFail($id);
                    $user = User::findorFail($userOrder->user_id);
                    $userFcmToken = $user->device_token;
                    // select only order detail  for fcm notification

                    $attributes['title'] = 'Owleto Order';
                    $attributes['message'] ='Your Order with OrderID ' . $userOrder->id . ' has been Shipped';
                    $attributes['data'] = $userOrder->toArray();

                    Notification::route('fcm', $userFcmToken)
                        ->notify(new DriverAssignedNotificationToUser($attributes));

                }catch (\Exception $e) {

                }
            }

            if($orderStatus->id == OrderStatus::STATUS_DELIVERED){

                $driverUserId = $order->driver_id;

                if($driverUserId){

                    $order = $this->orderRepository->update($input, $id);

                    $driverCommissionAmount = round($order->driver_commission_amount,2);

                    $orderType = Order::PICKUP_DELIVERY_ORDER_TYPE;

                    $this->createOwletoEarning($order, $orderType);
                    DriverTransaction::store($driverUserId, $order, $driverCommissionAmount);
                    $this->updateDriver($driverUserId, $driverCommissionAmount);

                    $pickUpDeliveryOrder = PickUpDeliveryOrder::where('order_id', $order->id)->first();
                    $pickUpDeliveryOrderRequest = PickUpDeliveryOrderRequest::where('id', $pickUpDeliveryOrder->pick_up_delivery_order_request_id)->first();
                    $pickUpDeliveryOrderRequest->status = PickUpDeliveryOrderRequest::STATUS_ORDER_PAID;
                    $pickUpDeliveryOrderRequest->save();

                    Order::orderDeliveredPushNotification($id);
                    Order::orderDeliveredMail($order);

                }
                else{
                    \Laracasts\Flash\Flash::error(__('Driver not assigned', ['operator' => __('lang.order')]));
                    return redirect(route('pickup-delivery-orders.edit', $id));
                }
            }

            if($orderStatus->id == OrderStatus::STATUS_CANCELED){
                $input['is_canceled'] = 1;

                $driver = Driver::where('user_id', $order->driver_id)->first();
                if($driver) {
                    $driver->available = 1;
                    $driver->save();
                }

                $this->orderRepository->update($input, $id);
            }

            $this->orderRepository->update($input, $id);

        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }


        Flash::success(__('lang.updated_successfully', ['operator' => __('lang.order')]));

        return redirect(route('pickup-delivery-orders.index'));
    }

    public function updateDriver($orderDriverId, $driverCommissionAmount)
    {
        $driver = Driver::where('user_id', $orderDriverId)->first();

        if($driver){
            $driver->earning = $driver->earning + $driverCommissionAmount;
            $driver->balance = $driver->balance + $driverCommissionAmount;
            $driver->total_orders = $driver->total_orders + 1;
            $driver->available = 1;
            $driver->save();
        }
    }

    public function createOwletoEarning($order, $orderType)
    {
        $owletoEarning = new OwletoEarning();
        $owletoEarning->order_id = $order->id;
        $owletoEarning->order_type = $orderType;
        $owletoEarning->earning = round($order->owleto_commission_amount,2);
        $owletoEarning->save();
    }

    public function assignDriver(Request $request)
    {
        $orderId = $request->order_id;

        $order = Order::findOrFail($orderId);

        if ($order->driver) {
            Flash::error(__('driver already assigned'));
            return redirect(route('pickup-delivery-orders.index'));
        }

        if($order->order_status_id == OrderStatus::STATUS_DELIVERED){
            Flash::error(__('order already canceled'));
            return redirect(route('pickup-delivery-orders.index'));
        }

        $driverId = $request->driver_id;
        $driver = Driver::where('user_id', $driverId)->first();

        if (! $driver) {
            Flash::error(__('Driver Not Found'));
            return redirect(route('pickup-delivery-orders.index'));
        }

        $distance = $order->distance;
        if ($distance <= $driver->base_distance) {
            $driverCommissionAmount = $driver->delivery_fee;
        }
        else {
            $additionalDistance = $order->distance - $driver->base_distance;
            $driverCommissionAmount = $driver->delivery_fee + $additionalDistance * $driver->additional_amount;
        }

        $order->order_status_id = OrderStatus::STATUS_DRIVER_ASSIGNED;
        $order->driver_id = $driverId;
        $order->driver_assigned_at = Carbon::now();
        $order->driver_commission_amount = round($driverCommissionAmount,2);

        $order->save();

        try {

            $correspondingDriver = User::findorFail($driverId);
            $driverFcmToken = $correspondingDriver->device_token;

            $attributes['title'] = 'Owleto Order';
            $attributes['message'] ='Owleto Order with OrderID : '. $order->id .' has been Assigned to you.';
            $attributes['data'] = $order->toArray();

            Notification::route('fcm', $driverFcmToken)
                ->notify(new DriverAssignedNotification($attributes));

        }catch (\Exception $e) {

        }

        try {

            $user = User::findorFail($order->user_id);
            $userFcmToken = $user->device_token;
            // select only order detail  for fcm notification

            $attributes['title'] = 'Owleto Order';
            $attributes['message'] ='Your Order with OrderID ' .$order->id. ' has been Shipped';
            $attributes['data'] = $order->toArray();
            $attributes['redirection_id'] = $order->id;
            $attributes['redirection_type'] = Order::STATUS_READY;

            Notification::route('fcm', $userFcmToken)
                ->notify(new DriverAssignedNotificationToUser($attributes));

        }catch (\Exception $e) {

        }

        Flash::success(__('Driver Assigned Successfully'));
        return redirect(route('pickup-delivery-orders.index'));
    }

}
