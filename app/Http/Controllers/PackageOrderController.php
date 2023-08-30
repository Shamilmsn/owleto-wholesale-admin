<?php

namespace App\Http\Controllers;

use App\Criteria\Orders\OrdersOfUserCriteria;
use App\Criteria\PackageOrders\PackageOrdersOfUserCriteria;
use App\Criteria\Users\ClientsCriteria;
use App\Criteria\Users\DriversOfMarketCriteria;
use App\DataTables\OrderDataTable;
use App\DataTables\PackageDataTable;
use App\DataTables\PackageDetailDataTable;
use App\DataTables\PackageOrderDatatable;
use App\Events\OrderChangedEvent;
use App\Mail\OrderDeliveredMail;
use App\Models\Driver;
use App\Models\DriversCurrentLocation;
use App\Models\DriverTransaction;
use App\Models\Earning;
use App\Models\Field;
use App\Models\MarketTransaction;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PackageOrder;
use App\Models\ProductAddon;
use App\Models\ProductOrder;
use App\Models\User;
use App\Notifications\AssignedOrder;
use App\Notifications\DriverAssignedNotification;
use App\Notifications\DriverAssignedNotificationToUser;
use App\Notifications\OrderDeliveredPushNotifictaion;
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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Laracasts\Flash\Flash;
use Prettus\Validator\Exceptions\ValidatorException;

class PackageOrderController extends Controller
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
                                PackageOrderRepository $packageOrderRepository)
    {
        parent::__construct();

        $this->orderRepository = $orderRepository;
        $this->userRepository = $userRepository;
        $this->orderStatusRepository = $orderStatusRepository;
        $this->customFieldRepository = $customFieldRepository;
        $this->marketRepository = $marketRepository;
        $this->paymentRepository = $paymentRepository;
        $this->packageOrderRepository = $packageOrderRepository;
    }

    public function index(PackageOrderDatatable $packageOrderDatatable)
    {
        return $packageOrderDatatable->render('package_orders.index');
    }

    public function show(PackageDetailDataTable $packageDetailDataTable, $id)
    {
        $drivers = Driver::where('admin_approved', 1)
            ->whereHas('user', function ($query) {
                $query->where('driver_signup_status', 5);
            })
            ->with('user')
            ->get();

        $this->packageOrderRepository->pushCriteria(new PackageOrdersOfUserCriteria(auth()->id()));

        $order = $this->orderRepository->findWithoutFail($id);

        if (empty($order)) {

            Flash::error(__('lang.not_found', ['operator' => __('lang.order')]));

            return redirect(route('package-orders.index'));
        }

        return $packageDetailDataTable->with('id', $id)->render('package_orders.show', [
            "order" => $order,
            'drivers' => $drivers
          ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $this->packageOrderRepository->pushCriteria(new PackageOrdersOfUserCriteria(auth()->id()));

        $order = $this->orderRepository->findWithoutFail($id);

        if (empty($order)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.order')]));

            return redirect(route('package-orders.index'));
        }

        $market = $order->packageOrders[0]->package->market;

        $market = isset($market) ? $market->id : 0;

        $user = $this->userRepository->getByCriteria(new ClientsCriteria())->pluck('name', 'id');
        $driver = $this->userRepository->getByCriteria(new DriversOfMarketCriteria($market))->pluck('name', 'id');

//        $fieldIds = Field::whereHas('markets', function ($query) use ($market){
//            $query->where('market_id', $market);
//        })->pluck('id');
        $fieldId = $order->sector_id;

        $orderStatus = $this->orderStatusRepository->whereHas('orderStatusFields', function ($query) use ($fieldId) {
            return $query->where('field_id', $fieldId);
        })->pluck('status', 'id');


        $customFieldsValues = $order->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->orderRepository->model());
        $hasCustomField = in_array($this->orderRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        return view('package_orders.edit')
            ->with('order', $order)
            ->with("customFields", isset($html) ? $html : false)
            ->with("user", $user)
            ->with("driver", $driver)
            ->with("orderStatus", $orderStatus);
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();

        $oldOrder = $this->orderRepository->findWithoutFail($id);

        if (empty($oldOrder)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.order')]));
            return redirect(route('orders.index'));
        }

        if($oldOrder->order_status_id == OrderStatus::STATUS_CANCELED){
            Flash::error(__('order already canceled'));
            return redirect(route('package-orders..index'));
        }

        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->orderRepository->model());

        try {
            $order = $this->orderRepository->update($input, $id);

            $oldStatus = $oldOrder->payment->status;

//            $this->paymentRepository->update([
//                "status" => $input['status'],
//            ], $order['payment_id']);

            if ($order->type !== Order::PICKUP_DELIVERY_ORDER_TYPE) {
                event(new OrderChangedEvent($oldStatus, $order));
            }

            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $order->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }

        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        if($input['order_status_id'] == Order::STATUS_DELIVERED) {
            try {
                $userFcmToken = $order->user->device_token;
                $userOrder = Order::findOrFail($id);
                // select only order detail for fcm notification

                $attributes['title'] = 'Owleto new order';
                $attributes['message'] ='Your order (OrderID : '.$order->id.')  status has been changed to Delivered';
                $attributes['data'] = $userOrder->toArray();


                Notification::route('fcm', $userFcmToken)
                    ->notify(new OrderDeliveredPushNotifictaion($attributes));

            }catch (\Exception $e) {

            }
            try {
                $attributes['email'] = $order->user->email;
                $attributes['order_id'] = $order->id;

                Mail::send( new OrderDeliveredMail($attributes));

            } catch (\Exception $e) {

            }
        }

        Flash::success(__('lang.updated_successfully', ['operator' => __('lang.order')]));

        return redirect(route('package-orders.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!env('APP_DEMO', false)) {
            $this->orderRepository->pushCriteria(new OrdersOfUserCriteria(auth()->id()));
            $order = $this->orderRepository->findWithoutFail($id);

            if (empty($order)) {
                Flash::error(__('lang.not_found', ['operator' => __('lang.order')]));

                return redirect(route('package-orders.index'));
            }

            $this->orderRepository->delete($id);

            $packageOrders = PackageOrder::where('order_id', $order->id)->get();
            foreach ($packageOrders as $packageOrder){
                $packageOrder->delete();
            }

            Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.order')]));


        } else {
            Flash::warning('This is only demo app you can\'t change this section ');
        }
        return redirect(route('package-orders.index'));
    }

    public function assignDriver(Request $request)
    {
        $orderId = $request->package_order_id;
        $driverId = $request->driver_id;

        $driver = Driver::where('user_id', $driverId)->first();
        $order = PackageOrder::findOrFail($orderId);

        if($order->order_status_id == OrderStatus::STATUS_DELIVERED){
            Flash::error(__('order already canceled'));
            return redirect(route('package-orders..index'));
        }

        $distance = $order->distance;

        if ($distance <= $driver->base_distance) {
            $driverCommissionAmount = $driver->delivery_fee;
        }
        else {
            $additionalDistance = $order->distance - $driver->base_distance;
            $driverCommissionAmount = $driver->delivery_fee + $additionalDistance * $driver->additional_amount;
        }

        if ($order->driver) {
            Flash::error(__('driver already assigned'));
            return redirect(route('package-orders.index'));
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

        return redirect(route('package-orders.show', $order->order_id));
    }
}
