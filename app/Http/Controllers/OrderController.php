<?php

namespace App\Http\Controllers;

use App\Criteria\Orders\OrdersOfUserCriteria;
use App\Criteria\Users\ClientsCriteria;
use App\Criteria\Users\DriversCriteria;
use App\Criteria\Users\DriversOfMarketCriteria;
use App\DataTables\OrderDataTable;
use App\DataTables\ProductOrderDataTable;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Banner;
use App\Models\DeliveryType;
use App\Models\Driver;
use App\Models\DriversCurrentLocation;
use App\Models\DriverTransaction;
use App\Models\Earning;
use App\Models\Market;
use App\Models\MarketTransaction;
use App\Models\Order;
use App\Models\OrderRequest;
use App\Models\OrderStatus;
use App\Models\OwletoEarning;
use App\Models\PackageOrder;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductAttributeOption;
use App\Models\ProductOrder;
use App\Models\ProductOrderRequestOrder;
use App\Models\SlotedDeliveryDriverHistory;
use App\Models\SubscriptionPackage;
use App\Models\TemporaryOrderRequest;
use App\Models\User;
use App\Notifications\AssignedOrder;
use App\Notifications\DriverAssignedNotification;
use App\Notifications\DriverAssignedNotificationToUser;
use App\Notifications\StatusChangedOrder;
use App\Repositories\CustomFieldRepository;
use App\Repositories\DriversCurrentLocationRepository;
use App\Repositories\MarketRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\OrderRepository;
use App\Repositories\OrderStatusRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\ProductAttributeOptionRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Response;
use Kreait\Firebase\Contract\Database;
use Laracasts\Flash\Flash;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;
use Yajra\DataTables\Facades\DataTables;
use Yajra\DataTables\Html\Builder;

class
OrderController extends Controller
{
    /** @var  OrderRepository */
    private $orderRepository;

    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var OrderStatusRepository
     */
    private $orderStatusRepository;
    /** @var  NotificationRepository */
    private $notificationRepository;
    /** @var  PaymentRepository */
    private $paymentRepository;
    /** @var  MarketRepository */
    private $marketRepository;
    /** @var  ProductAttributeOptionRepository */
    private $productAttributeOptionRepository;

    private $driversCurrentLocationRepository;


    public function __construct(OrderRepository        $orderRepo, CustomFieldRepository $customFieldRepo, UserRepository $userRepo
        , OrderStatusRepository                        $orderStatusRepo, NotificationRepository $notificationRepo, PaymentRepository $paymentRepo,
                                MarketRepository       $marketRepository, DriversCurrentLocationRepository $driversCurrentLocationRepository,
                                ProductAttributeOption $productAttributeOptionRepository, Database $database)
    {
        parent::__construct();
        $this->orderRepository = $orderRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->userRepository = $userRepo;
        $this->orderStatusRepository = $orderStatusRepo;
        $this->notificationRepository = $notificationRepo;
        $this->paymentRepository = $paymentRepo;
        $this->marketRepository = $marketRepository;
        $this->driversCurrentLocationRepository = $driversCurrentLocationRepository;
        $this->productAttributeOptionRepository = $productAttributeOptionRepository;
        $this->database = $database;
        $this->table = 'user_locations';
    }

    /**
     * Display a listing of the Order.
     *
     * @param OrderDataTable $orderDataTable
     * @return Response
     */
    public function index(OrderDataTable $orderDataTable)
    {
        $orderStatus = OrderStatus::all();
        $paymentMethods = PaymentMethod::all();
        $deliveryTypes = DeliveryType::all();
        $drivers = Driver::where('admin_approved', 1)
            ->whereHas('user', function ($query) {
                $query->where('driver_signup_status', 5);
            })
            ->with('user')
            ->get();

        $markets = Market::cursor();

        return $orderDataTable->render('orders.index', compact('orderStatus',
            'paymentMethods',
            'deliveryTypes', 'drivers', 'markets'));
    }

    /**
     * Display the specified Order.
     *
     * @param int $id
     * @param ProductOrderDataTable $productOrderDataTable
     *
     * @return Response
     * @throws RepositoryException
     */

    public function show(Builder $builder, ProductOrderDataTable $productOrderDataTable, $id)
    {
        $this->orderRepository->pushCriteria(new OrdersOfUserCriteria(auth()->id()));
        $order = $this->orderRepository->findWithoutFail($id);
        if (empty($order)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.order')]));

            return redirect(route('orders.index'));
        }

        $productOrderRow = ProductOrder::where('order_id', $id)->first();
        $product_id = $productOrderRow->product_id;
        $productAttributes = $this->productAttributeOptionRepository->where('product_id', $product_id)->get();
        $subOrders = Order::where('parent_id', $id)->with('payment')->get();
        $drivers = Driver::where('admin_approved', 1)
            ->whereHas('user', function ($query) {
                $query->where('driver_signup_status', 5);
            })
            ->with('user')
            ->get();

        return $productOrderDataTable->with('id', $id)
            ->render('orders.show', [
                "order" => $order,
                "productAttributes" => $productAttributes,
                'subOrders' => $subOrders,
                'drivers' => $drivers
            ]);
    }

    public function edit($id)
    {
        $this->orderRepository->pushCriteria(new OrdersOfUserCriteria(auth()->id()));
        $order = $this->orderRepository->findWithoutFail($id);
        if (empty($order)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.order')]));

            return redirect(route('orders.index'));
        }

        $market = $order->productOrders()->first();
        $market = isset($market) ? $market->product['market_id'] : 0;

        $user = $this->userRepository->getByCriteria(new ClientsCriteria())->pluck('name', 'id');
        $driver = $this->userRepository->getByCriteria(new DriversOfMarketCriteria($market))->pluck('name', 'id');

        $market = $this->marketRepository->findWithoutFail($order->market_id);
        // $primary_sector_id = $order->market->primary_sector_id;
//        $fields = $market->fields()->pluck('fields.id')->toArray();
//        if (!in_array($primary_sector_id, $fields)){
//            array_push($fields, $primary_sector_id);
//        }
        $field_id = $order->sector_id;

        if (!$order->sector_id) {
            $orderStatus = OrderStatus::whereIn('id', [
                OrderStatus::STATUS_RECEIVED, OrderStatus::STATUS_DELIVERED, OrderStatus::STATUS_CANCELED])->pluck('status', 'id');
        } else {
            $orderStatus = $this->orderStatusRepository->with('orderStatusFields')
                ->whereHas('orderStatusFields', function ($query) use ($field_id) {
                    $query->where('field_id', $field_id);
                })->pluck('status', 'id');
        }

        $customFieldsValues = $order->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->orderRepository->model());
        $hasCustomField = in_array($this->orderRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        return view('orders.edit')->with('order', $order)->with("customFields", isset($html) ? $html : false)
            ->with("user", $user)
            ->with("driver", $driver)
            ->with("orderStatus", $orderStatus);
    }

    public function update($id, UpdateOrderRequest $request)
    {
        $input = $request->all();

        $order = $this->orderRepository->findWithoutFail($id);
        $orderStatusId = $input['order_status_id'];

        if ($order->order_status_id == OrderStatus::STATUS_CANCELED) {

            Flash::error(__('Order canceled', ['operator' => __('lang.order')]));
            return redirect(route('orders.edit', $id));
        }

        $oldOrder = $this->orderRepository->findWithoutFail($id);

        if ($order->order_status_id == OrderStatus::STATUS_DELIVERED) {

            Flash::error('Order already delivered');
            return redirect(route('orders.edit', $id));

        }

        $marketId = $oldOrder->market_id;
        $market = $this->marketRepository->findWithoutFail($marketId);
        $latMarket = $market->latitude;
        $longMarket = $market->longitude;

        if (empty($oldOrder)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.order')]));
            return redirect(route('orders.index'));
        }

        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->orderRepository->model());

        try {

            if ($orderStatusId == OrderStatus::STATUS_DRIVER_ASSIGNED) {

                if ($oldOrder->is_order_approved != 1) {
                    Flash::error(__('Order not approved by merchant', ['operator' => __('lang.order')]));
                    return redirect(route('orders.edit', $id));
                }

                if ($oldOrder->driver_id) {
                    Flash::error(__('Driver already assigned', ['operator' => __('lang.order')]));
                    return redirect(route('orders.edit', $id));
                }

                $references = $this->database->getReference($this->table)->getValue();

                foreach ($references as $reference) {

                    if (array_key_exists("user_id", $reference)) {

                        $currentDriverLatitude = $reference['latitude'];
                        $currentDriverLongitude = $reference['longitude'];

                        if (DriversCurrentLocation::getDriverCurrentLocations($latMarket, $longMarket, $currentDriverLatitude,
                                $currentDriverLongitude, "K") < 10) {

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

                if (!$driversCurrentLocations) {
                    Flash::error(__('No driver found', ['operator' => __('lang.order')]));
                    return redirect(route('orders.edit', $id));
                }

                $order = $this->orderRepository->update($input, $id);

                $order->driver_id = $driversCurrentLocations->driver->user_id;
                $order->driver_assigned_at = Carbon::now();

                if ($order->driver_id) {
                    $distance = $order->distance;
                    if ($distance <= $driversCurrentLocations->driver->base_distance) {
                        $driverCommissionAmount = $driversCurrentLocations->driver->delivery_fee;
                    } else {
                        $additionalDistance = $order->distance - $driversCurrentLocations->driver->base_distance;
                        $driverCommissionAmount = $driversCurrentLocations->driver->delivery_fee + $additionalDistance * $driversCurrentLocations->driver->additional_amount;
                    }

                    $order->driver_commission_amount = round($driverCommissionAmount, 2);
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
                    $attributes['message'] = 'Owleto Order with OrderID : ' . $userOrder->id . ' has been Assigned to you.';
                    $attributes['data'] = $userOrder->toArray();

                    Notification::route('fcm', $driverFcmToken)
                        ->notify(new DriverAssignedNotification($attributes));

                } catch (Exception $e) {
                }

                try {

                    $userOrder = Order::findOrFail($id);
                    $user = User::findorFail($userOrder->user_id);
                    $userFcmToken = $user->device_token;
                    // select only order detail  for fcm notification

                    $attributes['title'] = 'Owleto Order';
                    $attributes['message'] = 'Your Order with OrderID ' . $userOrder->id . ' has been Shipped';
                    $attributes['data'] = $userOrder->toArray();

                    Notification::route('fcm', $userFcmToken)
                        ->notify(new DriverAssignedNotificationToUser($attributes));

                } catch (Exception $e) {

                }

            }

            if ($orderStatusId == OrderStatus::STATUS_DELIVERED) {

                $orderDriverId = $oldOrder->driver_id;
                $driverCommissionAmount = round($oldOrder->driver_commission_amount, 2);

                if ($orderDriverId) {

                    $order = $this->orderRepository->update($input, $id);
                    $this->createTransaction($marketId, $order);
                    $orderType = Order::PRODUCT_TYPE;

                    $this->createOwletoEarning($order, $orderType);
                    $this->createOrUpdate($marketId, $order);

                    DriverTransaction::store($orderDriverId, $order, $driverCommissionAmount);
                    $this->updateDriver($orderDriverId, $driverCommissionAmount, $order);

                    Order::orderDeliveredPushNotification($id);
                    Order::orderDeliveredMail($order);

                } else {
                    // take away orders

                    if ($order->sector_id == NULL) {

                        $order = $this->orderRepository->update($input, $id);

                        $this->createTransaction($marketId, $order);
                        $orderType = Order::PRODUCT_TYPE;
                        $this->createOwletoEarning($order, $orderType);
                        $this->createOrUpdate($marketId, $order);


                        Order::orderDeliveredPushNotification($id);
                        Order::orderDeliveredMail($order);

                    } else {
                        Flash::error(__('Driver not assigned', ['operator' => __('lang.order')]));
                        return redirect(route('orders.edit', $id));
                    }
                }

            }

            if ($orderStatusId == OrderStatus::STATUS_CANCELED) {

                $input['is_canceled'] = 1;

                $driver = Driver::where('user_id', $order->driver_id)->first();
                if ($driver) {
                    $driver->available = 1;
                    $driver->save();
                }

                $order = $this->orderRepository->update($input, $id);
            }
            $this->orderRepository->update($input, $id);

            if (setting('enable_notifications', false)) {
                if (isset($input['order_status_id']) && $input['order_status_id'] != $oldOrder->order_status_id) {
                    Notification::send([$order->user], new StatusChangedOrder($order));
                }

                if (isset($input['driver_id']) && ($input['driver_id'] != $oldOrder['driver_id'])) {
                    $driver = $this->userRepository->findWithoutFail($input['driver_id']);
                    if (!empty($driver)) {
                        Notification::send([$driver], new AssignedOrder($order));
                    }
                }
            }

            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $order->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }

        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }


        Flash::success(__('lang.updated_successfully', ['operator' => __('lang.order')]));

        return redirect(route('orders.index'));
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

    public function createOwletoEarning($order, $orderType)
    {
        $owletoEarning = new OwletoEarning();
        $owletoEarning->order_id = $order->id;
        $owletoEarning->order_type = $orderType;
        $owletoEarning->earning = round($order->owleto_commission_amount);
        $owletoEarning->save();
    }

    public function createOrUpdate($marketId, $order)
    {
        $earning = Earning::where('market_id', $marketId)->first();

        $totalOrdersCount = $earning->total_orders + 1;
        $marketTotalEarnings = $earning->market_earning + $order->market_balance;
        $marketBalance = $earning->market_balance + $order->market_balance;
        $totalAdminEarnings = $earning->admin_earning + $order->owleto_commission_amount;
        $totalEarning = $earning->total_earning + $order->total_amount;

        if (!$earning) {
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

    /**
     * Store a newly created Order in storage.
     *
     * @param CreateOrderRequest $request
     *
     * @return Response
     */
    public function store(CreateOrderRequest $request)
    {
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->orderRepository->model());
        try {
            $order = $this->orderRepository->create($input);
            $order->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));

        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully', ['operator' => __('lang.order')]));

        return redirect(route('orders.index'));
    }

    /**
     * Show the form for creating a new Order.
     *
     * @return Response
     */
    public function create()
    {
        $user = $this->userRepository->getByCriteria(new ClientsCriteria())->pluck('name', 'id');
        $driver = $this->userRepository->getByCriteria(new DriversCriteria())->pluck('name', 'id');

        $orderStatus = $this->orderStatusRepository->pluck('status', 'id');

        $hasCustomField = in_array($this->orderRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->orderRepository->model());
            $html = generateCustomField($customFields);
        }
        return view('orders.create')->with("customFields", isset($html) ? $html : false)->with("user", $user)->with("driver", $driver)->with("orderStatus", $orderStatus);
    }

    public function updateDriver($orderDriverId, $driverCommissionAmount, $order)
    {
        $driver = Driver::where('user_id', $orderDriverId)->first();

        if ($driver) {
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

    public function destroy($id)
    {
        if (!env('APP_DEMO', false)) {
            $this->orderRepository->pushCriteria(new OrdersOfUserCriteria(auth()->id()));
            $order = $this->orderRepository->findWithoutFail($id);

            if (empty($order)) {
                Flash::error(__('lang.not_found', ['operator' => __('lang.order')]));

                return redirect(route('orders.index'));
            }

            $this->orderRepository->delete($id);

            Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.order')]));


        } else {
            Flash::warning('This is only demo app you can\'t change this section ');
        }
        return redirect(route('orders.index'));
    }

    /**
     * Remove Media of Order
     * @param Request $request
     */
    public function removeMedia(Request $request)
    {
        $input = $request->all();
        $order = $this->orderRepository->findWithoutFail($input['id']);
        try {
            if ($order->hasMedia($input['collection'])) {
                $order->getFirstMedia($input['collection'])->delete();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    public function updateSectorIds()
    {
        $orders = Order::get();

        foreach ($orders as $order) {

            if ($order->type == Order::PRODUCT_TYPE) {
                $productOrder = ProductOrder::where('order_id', $order->id)->first();
                $product = Product::where('id', $productOrder->product_id)->first();
                $order->sector_id = $product->sector_id;
                $order->save();
            }

            if ($order->type == Order::PACKAGE_TYPE) {

                $packageOrder = PackageOrder::where('order_id', $order->id)->first();
                $package = SubscriptionPackage::where('id', $packageOrder->package_id)->first();
                $product = Product::where('id', $package->product_id)->first();
                $order->sector_id = $product->sector_id;
                $order->save();

                $packageOrders = PackageOrder::where('order_id', $order->id)->get();

                foreach ($packageOrders as $packageOrder) {
                    $packageOrder->sector_id = $product->sector_id;
                    $packageOrder->save();
                }

            }

            if ($order->type == Order::ORDER_REQUEST_TYPE) {

                $productOrderRequestOrder = ProductOrderRequestOrder::where('order_id', $order->id)->first();
                $tempOrder = TemporaryOrderRequest::where('id', $productOrderRequestOrder->temporary_order_request_id)->first();
                $orderRequest = OrderRequest::where('id', $tempOrder->order_request_id)->first();
                $order->sector_id = $orderRequest->sector_id;
                $order->save();
            }
        }
    }

    public function assignDriver(Request $request)
    {
        $orderId = $request->order_id;

        $order = Order::findOrFail($orderId);

        if ($order->driver && $order->is_driver_approved) {
            Flash::error(__('driver already assigned'));
            if ($order->parent_id) {
                return redirect(route('orders.show', $order->parent_id));
            }
            return redirect(route('orders.index'));
        }

        if ($order->order_status_id == OrderStatus::STATUS_CANCELED) {
            Flash::error(__('order already canceled'));
            if ($order->parent_id) {
                return redirect(route('orders.show', $order->parent_id));
            }
            return redirect(route('orders.index'));
        }

        if ($order->is_order_approved != 1) {
            Flash::error(__('Merchant not approved the order'));
            if ($order->parent_id) {
                return redirect(route('orders.show', $order->parent_id));
            }
            return redirect(route('orders.index'));
        }

        $driverId = $request->single_driver_id;
        $driver = Driver::where('user_id', $driverId)->first();

//        if (!$driver->available) {
//            Flash::error(__('Driver have already an order'));
//            return redirect(route('orders.index'));
//        }

        if (!$driver) {
            Flash::error(__('Driver Not Found'));
            if ($order->parent_id) {
                return redirect(route('orders.show', $order->parent_id));
            }
            return redirect(route('orders.index'));
        }

        $isDriverAlreadyExists = true;
        if ($order->driver_id) {
            $previousDriver = Driver::where('user_id', $order->driver_id)->first();
            $previousDriver->available = 1;
            $previousDriver->save();
            $isDriverAlreadyExists = false;
        }

        $distance = $order->distance;
        if ($distance <= $driver->base_distance) {
            $driverCommissionAmount = $driver->delivery_fee;
        } else {
            $additionalDistance = $order->distance - $driver->base_distance;
            $driverCommissionAmount = $driver->delivery_fee + $additionalDistance * $driver->additional_amount;
        }

        $order->order_status_id = OrderStatus::STATUS_DRIVER_ASSIGNED;
        $order->driver_id = $driverId;
        $order->driver_assigned_at = Carbon::now();
//        $order->driver_commission_amount = $driverCommissionAmount;
        $order->driver_commission_amount = $driver->delivery_fee;


        $order->save();
        $driver->available = 0;
        $driver->save();

        try {

            $correspondingDriver = User::findorFail($driverId);
            $driverFcmToken = $correspondingDriver->device_token;

            $attributes['title'] = 'Owleto Order';
            $attributes['message'] = 'Owleto Order with OrderID : ' . $order->id . ' has been Assigned to you.';
            $attributes['data'] = $order->toArray();
            $attributes['redirection_type'] = Order::STATUS_DRIVER_ASSIGNED;
            $attributes['redirection_id'] = $order->id;
            $attributes['type'] = $order->type;

            Notification::route('fcm', $driverFcmToken)
                ->notify(new DriverAssignedNotification($attributes));

        } catch (Exception $e) {

        }
        if ($isDriverAlreadyExists) {
            try {

                $user = User::findorFail($order->user_id);
                $userFcmToken = $user->device_token;
                // select only order detail  for fcm notification

                $attributes['title'] = 'Owleto Order';
                $attributes['message'] = 'Your Order with OrderID ' . $order->id . ' has been Shipped';
                $attributes['data'] = $order->toArray();
                $attributes['redirection_type'] = Order::STATUS_ON_THE_WAY;
                $attributes['redirection_id'] = $order->id;
                $attributes['type'] = $order->type;

                Notification::route('fcm', $userFcmToken)
                    ->notify(new DriverAssignedNotificationToUser($attributes));

            } catch (Exception $e) {

            }
        }

        Flash::success(__('Driver Assigned Successfully'));
        if ($order->parent_id) {
            return redirect(route('orders.show', $order->parent_id));
        }
        return redirect(route('orders.index'));
    }

    public function assignDriverToOrder(Request $request)
    {
        $driverId = $request->driver_id;
        $driver = Driver::where('user_id', $driverId)->first();

        if (!$driver) {
            Flash::error(__('Driver Not Found'));
            return redirect(route('deliver-orders.index'));
        }

        $orderIds = explode(',', $request->ordersIds);

        foreach ($orderIds as $orderId) {

            $order = Order::findOrFail($orderId);
            $slotedDeliveryHistory = SlotedDeliveryDriverHistory::query()
                ->where('order_id', $orderId)
                ->first();

            if ($order->order_status_id != OrderStatus::STATUS_DELIVERED
                || $order->order_status_id != OrderStatus::STATUS_CANCELED) {
                if ($order->is_order_approved && $slotedDeliveryHistory) {
//                $order->picked_or_delivered = Order::DELIVERED;
                    $order->order_status_id = OrderStatus::STATUS_DRIVER_ASSIGNED;
                    $order->driver_id = $driverId;
                    $order->driver_assigned_at = Carbon::now();
                    $order->save();

                    $slotedDeliveryHistory->order_id = $orderId;
                    $slotedDeliveryHistory->delivered_driver_id = $driverId;
                    $slotedDeliveryHistory->delivered_driver_assigned_at = Carbon::now();
                    $slotedDeliveryHistory->status =
                        SlotedDeliveryDriverHistory::STATUS_DELIVER_ASSIGNED;
                    $slotedDeliveryHistory->save();

                    try {

                        $user = User::findorFail($order->user_id);
                        $userFcmToken = $user->device_token;

                        $attributes['title'] = 'Owleto Order';
                        $attributes['message'] = 'Your Order with OrderID '
                            . $order->id . ' has been Shipped';
                        $attributes['data'] = $order->toArray();
                        $attributes['redirection_type'] = Order::STATUS_ON_THE_WAY;
                        $attributes['redirection_id'] = $order->id;
                        $attributes['type'] = $order->type;

                        Notification::route('fcm', $userFcmToken)
                            ->notify(new DriverAssignedNotificationToUser($attributes));

                    } catch (Exception $e) {

                    }
                }
            }

        }

        Flash::success(__('Driver Assigned Successfully'));
        return redirect(route('orders.index'));
    }

}
