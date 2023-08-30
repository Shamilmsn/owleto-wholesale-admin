<?php

namespace App\Http\Controllers;

use App\Criteria\Orders\OrdersOfUserCriteria;
use App\Criteria\Users\ClientsCriteria;
use App\Models\Driver;
use App\Models\DriversCurrentLocation;
use App\Models\DriverTransaction;
use App\Models\Earning;
use App\Models\Field;
use App\Models\Market;
use App\Models\MarketTransaction;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\OwletoEarning;
use App\Models\PackageOrder;
use App\Models\ProductOrder;
use App\Models\User;
use App\Notifications\AssignedOrder;
use App\Notifications\DriverAssignedNotification;
use App\Notifications\DriverAssignedNotificationToUser;
use App\Notifications\StatusChangedOrder;
use App\Repositories\CustomFieldRepository;
use App\Repositories\MarketRepository;
use App\Repositories\OrderRepository;
use App\Repositories\OrderStatusRepository;
use App\Repositories\PackageOrderRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Kreait\Firebase\Contract\Database;
use Laracasts\Flash\Flash;
use Prettus\Validator\Exceptions\ValidatorException;

class PackageOrderDetailController extends Controller
{

    /** @var  OrderRepository */
    private $orderRepository;

    /** @var  PackageOrderRepository */
    private $packageOrderRepository;

    /** @var  PaymentRepository */
    private $paymentRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var OrderStatusRepository
     */
    private $orderStatusRepository;

    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;

    /**
     * @var MarketRepository
     */
    private $marketRepository;


    public function __construct(OrderRepository $orderRepository, UserRepository $userRepository,
                                OrderStatusRepository $orderStatusRepository, CustomFieldRepository $customFieldRepository,
                                MarketRepository $marketRepository, PaymentRepository $paymentRepository,
                                PackageOrderRepository $packageOrderRepository, Database $database)
    {
        parent::__construct();

        $this->orderRepository = $orderRepository;
        $this->userRepository = $userRepository;
        $this->orderStatusRepository = $orderStatusRepository;
        $this->customFieldRepository = $customFieldRepository;
        $this->marketRepository = $marketRepository;
        $this->paymentRepository = $paymentRepository;
        $this->packageOrderRepository = $packageOrderRepository;
        $this->database = $database;
        $this->table = 'user_locations';
    }

    public function edit($id)
    {
        $packageOrder = PackageOrder::findOrFail($id);

        $user = $this->userRepository->getByCriteria(new ClientsCriteria())->pluck('name', 'id');

        $fieldId = $packageOrder->order->sector_id;

        $orderStatus = $this->orderStatusRepository->whereHas('orderStatusFields', function ($query) use ($fieldId) {
            return $query->where('field_id', $fieldId);
        })->pluck('status', 'id');

        return view('package_orders.package-order-details.edit')
            ->with('order', $packageOrder)
            ->with("user", $user)
            ->with("orderStatus", $orderStatus);
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();

        $oldOrder = $this->packageOrderRepository->findWithoutFail($id);

        if (empty($oldOrder)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.order')]));
            return redirect(route('package-orders.index'));
        }

        $orderDate = Carbon::parse($oldOrder->date)->format('Y-m-d');

        $marketId = $oldOrder->market_id;

        $market = Market::find($marketId);
        $orderStatusId = $input['order_status_id'];
        $orderStatus = OrderStatus::findOrFail($orderStatusId);

        if($oldOrder->canceled == 1){

            \Laracasts\Flash\Flash::error(__('Order canceled', ['operator' => __('lang.order')]));
            return redirect(route('package-order-details.edit', $id));
        }

        if($oldOrder->order_status_id == OrderStatus::STATUS_DELIVERED){
            Flash::error('Order already delivered');
            return redirect(route('package-order-details.edit', $id));
        }

        if ($orderStatus->id == OrderStatus::STATUS_DRIVER_ASSIGNED) {

            if($oldOrder->driver_id){
                \Laracasts\Flash\Flash::error(__('Driver already assigned', ['operator' => __('lang.order')]));
                return redirect(route('package-order-details.edit', $id));
            }

            $market = $this->marketRepository->findWithoutFail($marketId);
            $latMarket = $market->latitude;
            $longMarket = $market->longitude;

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
                return redirect(route('package-order-details.edit', $id));
            }

            $order = $this->packageOrderRepository->update($input, $id);

            $order->driver_id = $driversCurrentLocations->driver->user_id;
            $order->driver_assigned_at = Carbon::now();

            if ($order->driver_id) {
                $distance = $order->distance;

                if ($distance <= $driversCurrentLocations->driver->base_distance) {
                    $driverCommissionAmount = $driversCurrentLocations->driver->delivery_fee;
                }
                else {
                    $additionalDistance = $order->distance - $driversCurrentLocations->driver->base_distance;
                    $driverCommissionAmount = $driversCurrentLocations->driver->delivery_fee + $additionalDistance * $driversCurrentLocations->driver->additional_amount;
                }

                $order->driver_commission_amount = round($driverCommissionAmount,2);
            }

            $order->save();

            $driver = Driver::where('id', $driversCurrentLocations->driver_id)->first();
            $driver->available = 0;
            $driver->save();

            try {

                $correspondingDriver = User::findorFail($driver->user_id);
                $driverFcmToken = $correspondingDriver->device_token;
                $attributes['title'] = 'Owleto Order';
                $attributes['message'] ='Owleto Order with OrderID : '. $oldOrder->id .' has been Assigned to you.';
                $attributes['data'] = $oldOrder->toArray();

                Notification::route('fcm', $driverFcmToken)
                    ->notify(new DriverAssignedNotification($attributes));

            }catch (\Exception $e) {

            }

            try {

                $user = User::findorFail($oldOrder->user_id);
                $userFcmToken = $user->device_token;
                $attributes['title'] = 'Owleto Order';
                $attributes['message'] ='Your package order item with OrderID ' .$oldOrder->id. ' has been Shipped';
                $attributes['data'] = $oldOrder->toArray();

                Notification::route('fcm', $userFcmToken)
                    ->notify(new DriverAssignedNotificationToUser($attributes));

            }catch (\Exception $e) {

            }
        }

        if($orderStatus->id == OrderStatus::STATUS_DELIVERED){

            $today = Carbon::now()->format('Y-m-d');
            if( $orderDate > $today ){
                Flash::error('Order cannot delivered today');
                return redirect(route('package-order-details.edit', $id));
            }

            $orderDriverId = $oldOrder->driver_id;
            $driverCommissionAmount = round($oldOrder->driver_commission_amount,2);

            $latestPackageOrderItem = PackageOrder::query()
                ->where('order_id', $oldOrder->order_id)
                ->latest()
                ->first();

            if (Carbon::parse($latestPackageOrderItem->date) == Carbon::today()) {

                try {
                    $user = User::findorFail($oldOrder->user_id);
                    $userFcmToken = $user->device_token;
                    $attributes['title'] = 'Owleto Order';
                    $attributes['message'] ='Thank you for choosing Owleto! Your Subscription package with'.$market->name.'expires today
                    . Please enjoy yourself with delicious meal by subscribing to a new plan.Checkout the packages in Owleto app.';
                    $attributes['data'] = $oldOrder->toArray();

                    Notification::route('fcm', $userFcmToken)
                        ->notify(new DriverAssignedNotificationToUser($attributes));

                }catch (\Exception $e) {

                }

            }

            if($orderDriverId){

                $order = $this->packageOrderRepository->update($input, $id);
                $oldOrder->delivered = 1;
                $oldOrder->save();

                $orderData = Order::where('id', $oldOrder->order_id)
                    ->first();

                $totalPackageOrders = PackageOrder::query()
                    ->where('order_id', $oldOrder->order_id)
                    ->count();

                $totalDeliveredOrders = PackageOrder::query()
                    ->where('order_id', $oldOrder->order_id)
                    ->where('delivered', 1)
                    ->count();

                $totalCanceledOrders = PackageOrder::query()
                    ->where('order_id', $oldOrder->order_id)
                    ->where('canceled', 1)
                    ->count();

                $totalDeliveredCanceledOrders = $totalDeliveredOrders + $totalCanceledOrders;

                if ($totalPackageOrders == $totalDeliveredCanceledOrders) {
                    $orderData->order_status_id = OrderStatus::STATUS_DELIVERED;
                    $orderData->save();
                }

                $this->createTransaction($marketId, $order);
                $orderType = Order::PACKAGE_TYPE;

                $this->createOwletoEarning($order, $orderType);
                $this->createOrUpdate($marketId, $order);

                DriverTransaction::store($orderDriverId, $order, $driverCommissionAmount);
                $this->updateDriver($orderDriverId, $driverCommissionAmount);
            }

            else{
                Flash::error(__('Driver not assigned', ['operator' => __('lang.order')]));
                return redirect(route('package-orders.show', $oldOrder->order_id));
            }

            try {

                $user = User::findorFail($oldOrder->user_id);
                $userFcmToken = $user->device_token;
                $attributes['title'] = 'Owleto Order';
                $attributes['message'] ='Your package order item with OrderID ' .$oldOrder->id. ' has been delivered';
                $attributes['data'] = $oldOrder->toArray();

                Notification::route('fcm', $userFcmToken)
                    ->notify(new DriverAssignedNotificationToUser($attributes));

            }catch (\Exception $e) {

            }

        }

        if($orderStatus->id == OrderStatus::STATUS_CANCELED){

            $input['canceled'] = 1;

            $driver = Driver::where('user_id', $oldOrder->driver_id)->first();
            if($driver) {
                $driver->available = 1;
                $driver->save();
            }

            $this->packageOrderRepository->update($input, $id);

            $orderData = Order::where('id', $oldOrder->order_id)
                ->first();

            $totalPackageOrders = PackageOrder::query()
                ->where('order_id', $oldOrder->order_id)
                ->count();

            $totalDeliveredOrders = PackageOrder::query()
                ->where('order_id', $oldOrder->order_id)
                ->where('delivered', 1)
                ->count();

            $totalCanceledOrders = PackageOrder::query()
                ->where('order_id', $oldOrder->order_id)
                ->where('canceled', 1)
                ->count();

            $totalDeliveredCanceledOrders = $totalDeliveredOrders + $totalCanceledOrders;

            if ($totalPackageOrders == $totalDeliveredCanceledOrders) {
                $orderData->order_status_id = OrderStatus::STATUS_DELIVERED;
                $orderData->save();
            }

            if ($totalPackageOrders == $totalCanceledOrders) {
                $orderData->order_status_id = OrderStatus::STATUS_CANCELED;
                $orderData->save();
            }


            try {

                $user = User::findorFail($oldOrder->user_id);
                $userFcmToken = $user->device_token;
                $attributes['title'] = 'Owleto Order';
                $attributes['message'] ='Your package order item with OrderID ' .$oldOrder->id. ' has been canceled';
                $attributes['data'] = $oldOrder->toArray();

                Notification::route('fcm', $userFcmToken)
                    ->notify(new DriverAssignedNotificationToUser($attributes));

            }catch (\Exception $e) {

            }
        }

        $this->packageOrderRepository->update($input, $id);

        Flash::success(__('lang.updated_successfully', ['operator' => __('lang.order')]));

        return redirect(route('package-orders.show', $oldOrder->order_id));
    }

    public function show($id)
    {
        $order = PackageOrder::with('user', 'market', 'paymentMethod', 'deliveryAddress', 'driver')->where('id', $id)->first();

        if (empty($order)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.order')]));

            return redirect(route('orders.index'));
        }

        return view('package_orders.package-order-details.show', compact('order'));

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
