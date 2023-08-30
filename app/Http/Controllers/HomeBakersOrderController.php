<?php

namespace App\Http\Controllers;

use App\Criteria\Orders\OrdersOfUserCriteria;
use App\Criteria\Users\ClientsCriteria;
use App\Criteria\Users\DriversCriteria;
use App\Criteria\Users\DriversOfMarketCriteria;
use App\DataTables\ExpressOrderDataTable;
use App\DataTables\HomeBakersOrderDataTable;
use App\DataTables\OrderDataTable;
use App\DataTables\ProductOrderDataTable;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
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

class HomeBakersOrderController extends Controller
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
    public function index(HomeBakersOrderDataTable $orderDataTable)
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

        return $orderDataTable->render('home-bakers-orders.index', compact('orderStatus',
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

    public function show(ProductOrderDataTable $productOrderDataTable, $id)
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

        return $productOrderDataTable->with('id', $id)
            ->render('home-bakers-orders.show', [
                "order" => $order,
                "productAttributes" => $productAttributes
            ]);
    }

    public function assignDriver(Request $request)
    {
        $orderId = $request->order_id;

        $order = Order::findOrFail($orderId);

        if ($order->driver && $order->is_driver_approved) {
            Flash::error(__('driver already assigned'));
            return redirect(route('home-bakers-orders.index'));
        }

        if ($order->order_status_id == OrderStatus::STATUS_CANCELED) {
            Flash::error(__('order already canceled'));
            return redirect(route('home-bakers-orders.index'));
        }

        if ($order->is_order_approved != 1) {
            Flash::error(__('Merchant not approved the order'));
            return redirect(route('home-bakers-orders.index'));
        }

        $driverId = $request->driver_id;
        $driver = Driver::where('user_id', $driverId)->first();

        if (!$driver) {
            Flash::error(__('Driver Not Found'));
            return redirect(route('home-bakers-orders.index'));
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
        return redirect(route('home-bakers-orders.index'));
    }
}
