<?php

namespace App\Http\Controllers;

use App\Criteria\Orders\OrdersOfUserCriteria;
use App\Criteria\OrdersRequestOrders\OrderRequestOrdersOfUserCriteria;
use App\Criteria\Users\ClientsCriteria;
use App\Criteria\Users\DriversOfMarketCriteria;
use App\DataTables\OrderRequestOrderDataTable;
use App\DataTables\OrderRequestOrderDetailDataTable;
use App\Mail\OrderDeliveredMail;
use App\Models\Driver;
use App\Models\DriversCurrentLocation;
use App\Models\DriverTransaction;
use App\Models\Earning;
use App\Models\MarketTransaction;
use App\Models\Order;
use App\Models\OrderRequest;
use App\Models\OrderStatus;
use App\Models\OwletoEarning;
use App\Models\PackageOrder;
use App\Models\PaymentMethod;
use App\Models\ProductOrderRequestOrder;
use App\Models\TemporaryOrderRequest;
use App\Models\User;
use App\Notifications\AssignedOrder;
use App\Notifications\DriverAssignedNotification;
use App\Notifications\DriverAssignedNotificationToUser;
use App\Notifications\OrderDeliveredPushNotifictaion;
use App\Notifications\StatusChangedOrder;
use App\Repositories\MarketRepository;
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

class OrderRequestOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /** @var  OrderRepository */
    private $orderRepository;

    /** @var  OrderStatusRepository */
    private $orderStatusRepository;

    /** @var  MarketRepository */
    private $marketRepository;

    public function __construct(OrderRepository $orderRepository,
                                OrderStatusRepository $orderStatusRepository,
                                MarketRepository $marketRepository, Database $database)
    {
        parent::__construct();

        $this->orderRepository = $orderRepository;
        $this->orderStatusRepository = $orderStatusRepository;
        $this->marketRepository = $marketRepository;
        $this->database = $database;
        $this->table = 'user_locations';
    }
    public function index(OrderRequestOrderDataTable $orderRequestOrderDataTable)
    {
        $drivers = Driver::where('admin_approved', 1)
            ->whereHas('user', function ($query) {
                $query->where('driver_signup_status', 5);
            })
            ->with('user')
            ->get();

        return $orderRequestOrderDataTable->render('order_request_orders.index', compact('drivers'));
    }

    public function show(OrderRequestOrderDetailDataTable $orderRequestOrderDetailDataTable, $id)
    {

        $this->orderRepository->pushCriteria(new OrderRequestOrdersOfUserCriteria(auth()->id()));
        $order = $this->orderRepository->findWithoutFail($id);

        if (empty($order)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.order')]));

            return redirect(route('order-request-orders.index'));
        }

        return $orderRequestOrderDetailDataTable->with('id', $id)
            ->render('order_request_orders.show', [
            "order" => $order
        ]);
    }

    public function edit($id)
    {
        $this->orderRepository->pushCriteria(new OrderRequestOrdersOfUserCriteria(auth()->id()));

        $order = $this->orderRepository->findWithoutFail($id);

        if (empty($order)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.order')]));

            return redirect(route('order-request-orders.index'));
        }
        $market = $this->marketRepository->findWithoutFail($order->market_id);
        $primary_sector_id = $order->market->primary_sector_id;
        $fields = $market->fields()->pluck('fields.id')->toArray();

        if (!in_array($primary_sector_id, $fields)){
            array_push($fields, $primary_sector_id);
        }

        $orderStatus = $this->orderStatusRepository->whereHas('orderStatusFields', function ($query) use ($fields) {
            return $query->whereIn('field_id', $fields);
        })->pluck('status', 'id');

        return view('order_request_orders.edit')
            ->with('order', $order)
            ->with("orderStatus", $orderStatus);

    }

    public function update(Request $request, $id)
    {
        $input = $request->all();

        $order = $this->orderRepository->findWithoutFail($id);
        $orderStatusId = $input['order_status_id'];

        $oldOrder = $this->orderRepository->findWithoutFail($id);

        if($order->order_status_id == OrderStatus::STATUS_CANCELED){

            \Laracasts\Flash\Flash::error(__('Order canceled', ['operator' => __('lang.order')]));
            return redirect(route('order-request-orders.edit', $id));
        }

        if($order->order_status_id == OrderStatus::STATUS_DELIVERED){

            \Laracasts\Flash\Flash::error('Order already delivered');
            return redirect(route('order-request-orders.edit', $id));

        }

        $marketId = $oldOrder->market_id;

        $market = $this->marketRepository->findWithoutFail($marketId);
        $latMarket = $market->latitude;
        $longMarket = $market->longitude;

        if (empty($oldOrder)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.order')]));
            return redirect(route('orders.index'));
        }

        try {

            if ($orderStatusId == OrderStatus::STATUS_DRIVER_ASSIGNED) {

                if($oldOrder->driver_id){
                    \Laracasts\Flash\Flash::error(__('Driver already assigned', ['operator' => __('lang.order')]));
                    return redirect(route('order-request-orders.edit', $id));
                }

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

                $driversCurrentLocations = DriversCurrentLocation::getAvailableDriver($latMarket, $longMarket, $market);

                if(!$driversCurrentLocations){
                    \Laracasts\Flash\Flash::error(__('No driver found', ['operator' => __('lang.order')]));
                    return redirect(route('order-request-orders.edit', $id));
                }

                $order = $this->orderRepository->update($input, $id);

                $order->driver_id = $driversCurrentLocations->driver->user_id;
                $order->driver_assigned_at = Carbon::now();

                if ($order->driver_id) {
                    $distance = $order->distance;
                    $driverCommissionAmount = $distance * $driversCurrentLocations->driver->delivery_fee;
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
                    $attributes['message'] ='Owleto Order with OrderID : '. $userOrder->id .' has been Assigned to you.';
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

            if($orderStatusId == OrderStatus::STATUS_DELIVERED){

                $orderDriverId = $oldOrder->driver_id;
                $driverCommissionAmount = round($oldOrder->driver_commission_amount,2);

                if($orderDriverId){

                    $order = $this->orderRepository->update($input, $id);
                    $this->createTransaction($marketId, $order);
                    $orderType = Order::ORDER_REQUEST_TYPE;

                    $this->createOwletoEarning($order, $orderType);
                    $this->createOrUpdate($marketId, $order);

                    DriverTransaction::store($orderDriverId, $order, $driverCommissionAmount);
                    $this->updateDriver($orderDriverId, $driverCommissionAmount, $order);

                    Order::orderDeliveredPushNotification($id);
                    Order::orderDeliveredMail($order);

                }
                else{
                    \Laracasts\Flash\Flash::error(__('Driver not assigned', ['operator' => __('lang.order')]));
                    return redirect(route('orders.edit', $id));
                }

            }

            if($orderStatusId == OrderStatus::STATUS_CANCELED){
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

        return redirect(route('order-request-orders.index'));
    }

    public function assignDriver(Request $request)
    {
        $orderId = $request->order_id;
        $order = Order::findOrFail($orderId);

        if ($order->driver) {
            Flash::error(__('driver already assigned'));
            return redirect(route('order-request-orders.index'));
        }

        if($order->order_status_id == OrderStatus::STATUS_CANCELED){
            Flash::error(__('order already canceled'));
            return redirect(route('order-request-orders.index'));
        }

        $driverId = $request->driver_id;

        $driver = Driver::where('user_id', $driverId)->first();

        if (! $driver) {
            Flash::error(__('Driver not found'));
            return redirect(route('order-request-orders.index'));
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
        return redirect(route('order-request-orders.index'));
    }

    public function createOrUpdate($marketId, $order)
    {
        $earning = Earning::where('market_id', $marketId)->first();

        $totalOrdersCount = $earning->total_orders + 1;
        $marketTotalEarnings = $earning->market_earning + $order->market_balance;
        $marketBalance = $earning->market_balance + $order->market_balance;
        $totalAdminEarnings = $earning->admin_earning + $order->owleto_commission_amount;
        $totalEarning = $earning->total_earning + $order->total_amount;

        if(!$earning){
            $earning = new Earning();
        }

        $earning->market_id = $marketId;
        $earning->total_orders = $totalOrdersCount;
        $earning->total_earning = $totalEarning;
        $earning->admin_earning = $totalAdminEarnings;
        $earning->market_earning = $marketTotalEarnings;
        $earning->market_balance = $marketBalance;
        $earning->save();
    }

    public function updateDriver($orderDriverId, $driverCommissionAmount, $order)
    {
        $driver = Driver::where('user_id', $orderDriverId)->first();

        if($driver){
            $driver->earning = $driver->earning + $driverCommissionAmount;
            $driver->balance = $driver->balance + $driverCommissionAmount;
            $driver->total_orders = $driver->total_orders + 1;
            $driver->available = 1;
            if ($order->payment_method_id == PaymentMethod::PAYMENT_METHOD_COD) {
                $driver->balance_cod_amount = $driver->balance_cod_amount + $order->total_amount;
            }
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

    public function createTransaction($marketId, $order)
    {
        $earning = Earning::where('market_id', $marketId)->first();
        $balance = $earning->market_balance + $order->market_balance;

        $transaction = new MarketTransaction();
        $transaction->market_id = $marketId;
        $transaction->credit = $order->market_balance;
        $transaction->balance = $balance;
        $transaction->model()->associate($order);
        $transaction->save();
    }

}
