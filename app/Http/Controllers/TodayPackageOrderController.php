<?php

namespace App\Http\Controllers;

use App\Criteria\Users\ClientsCriteria;
use App\DataTables\TodayPackageOrderDatatable;
use App\Models\Driver;
use App\Models\DriversCurrentLocation;
use App\Models\DriverTransaction;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PackageOrder;
use App\Models\User;
use App\Notifications\DriverAssignedNotification;
use App\Notifications\DriverAssignedNotificationToUser;
use App\Repositories\CustomFieldRepository;
use App\Repositories\MarketRepository;
use App\Repositories\OrderRepository;
use App\Repositories\OrderStatusRepository;
use App\Repositories\PackageOrderRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Kreait\Firebase\Contract\Database;
use Laracasts\Flash\Flash;

class TodayPackageOrderController extends Controller
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /** @var  PackageOrderRepository */
    private $packageOrderRepository;

    /**
     * @var OrderStatusRepository
     */
    private $orderStatusRepository;


    public function __construct(UserRepository $userRepository,
                                OrderStatusRepository $orderStatusRepository,
                                PackageOrderRepository $packageOrderRepository)
    {
        parent::__construct();

        $this->userRepository = $userRepository;
        $this->orderStatusRepository = $orderStatusRepository;
        $this->packageOrderRepository = $packageOrderRepository;
        $this->table = 'user_locations';
    }

    public function index(TodayPackageOrderDatatable $todayPackageOrderDatatable)
    {
        $drivers = Driver::where('admin_approved', 1)
            ->whereHas('user', function ($query) {
                $query->where('driver_signup_status', 5);
            })
            ->with('user')
            ->get();

        return $todayPackageOrderDatatable->render('package_orders.current-date.index', compact('drivers'));
    }

    public function edit($id)
    {
        $packageOrder = PackageOrder::findOrFail($id);

        $user = $this->userRepository->getByCriteria(new ClientsCriteria())->pluck('name', 'id');

        $fieldId = $packageOrder->order->sector_id;

        $orderStatus = $this->orderStatusRepository->whereHas('orderStatusFields', function ($query) use ($fieldId) {
            return $query->where('field_id', $fieldId);
        })->pluck('status', 'id');

        return view('package_orders.current-date.edit')
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
            return redirect(route('todays-package-orders.index'));
        }

        if($oldOrder->order_status_id == OrderStatus::STATUS_DELIVERED){
            Flash::error(__('order already canceled'));
            return redirect(route('todays-package-orders.index'));
        }

        $orderDate = Carbon::parse($oldOrder->date)->format('Y-m-d');

        $marketId = $oldOrder->market_id;
        $orderStatusId = $input['order_status_id'];
        $orderStatus = OrderStatus::findOrFail($orderStatusId);

        if($oldOrder->canceled == 1){

            \Laracasts\Flash\Flash::error(__('Order canceled', ['operator' => __('lang.order')]));
            return redirect(route('todays-package-orders.edit', $id));
        }

        if($oldOrder->order_status_id == OrderStatus::STATUS_DELIVERED){
            Flash::error('Order already delivered');
            return redirect(route('todays-package-orders.edit', $id));
        }

        if ($orderStatus->id == OrderStatus::STATUS_DRIVER_ASSIGNED) {

            if($oldOrder->driver_id){
                \Laracasts\Flash\Flash::error(__('Driver already assigned', ['operator' => __('lang.order')]));
                return redirect(route('todays-package-orders.edit', $id));
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
                return redirect(route('todays-package-orders.edit', $id));
            }

            $order = $this->packageOrderRepository->update($input, $id);

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
        }

        if($orderStatus->id == OrderStatus::STATUS_DELIVERED){

            $today = Carbon::now()->format('Y-m-d');
            if( $orderDate > $today ){
                Flash::error('Order cannot delivered today');
                return redirect(route('todays-package-orders.edit', $id));
            }

            $orderDriverId = $oldOrder->driver_id;
            $driverCommissionAmount = round($oldOrder->driver_commission_amount,2);

            if($orderDriverId){

                $order = $this->packageOrderRepository->update($input, $id);
                $oldOrder->delivered = 1;
                $oldOrder->save();

                $this->createTransaction($marketId, $order);
                $orderType = Order::PACKAGE_TYPE;

                $this->createOwletoEarning($order, $orderType);
                $this->createOrUpdate($marketId, $order);

                DriverTransaction::store($orderDriverId, $order, $driverCommissionAmount);
                $this->updateDriver($orderDriverId, $driverCommissionAmount);
            }
            else{
                Flash::error(__('Driver not assigned', ['operator' => __('lang.order')]));
                return redirect(route('todays-package-orders.index'));
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
        }

        $this->packageOrderRepository->update($input, $id);

        Flash::success(__('lang.updated_successfully', ['operator' => __('lang.order')]));

        return redirect(route('todays-package-orders.index'));
    }

    public function assignDriver(Request $request)
    {
        $orderId = $request->package_order_id;
        $driverId = $request->driver_id;

        $driver = Driver::where('user_id', $driverId)->first();
        $order = PackageOrder::findOrFail($orderId);

        $distance = $order->distance;
        $driverCommissionAmount = $distance * $driver->delivery_fee;

        if ($order->driver) {
            Flash::error(__('driver already assigned'));
            return redirect(route('todays-package-orders.index'));
        }

        if($order->order_status_id == OrderStatus::STATUS_DELIVERED){
            Flash::error(__('order already canceled'));
            return redirect(route('todays-package-orders.index'));
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
            $attributes['redirection_id'] = $order->id;
            $attributes['redirection_type'] = Order::STATUS_READY;

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

        return redirect(route('todays-package-orders.index'));
    }
}
