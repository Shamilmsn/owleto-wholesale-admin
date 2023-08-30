<?php

namespace App\Http\Controllers;
use App\Criteria\Orders\OrdersOfUserCriteria;
use App\DataTables\SlotedDeliveryOrderDataTable;
use App\DataTables\ProductOrderDataTable;
use App\Models\Area;
use App\Models\DeliveryType;
use App\Models\Driver;
use App\Models\Market;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PaymentMethod;
use App\Models\ProductAttributeOption;
use App\Models\ProductOrder;
use App\Models\SlotedDeliveryDriverHistory;
use App\Models\User;
use App\Notifications\DriverAssignedNotification;
use App\Notifications\DriverAssignedNotificationToUser;
use App\Repositories\OrderRepository;
use App\Repositories\ProductAttributeOptionRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Laracasts\Flash\Flash;

class SlotDeliveryOrderController extends Controller
{
    /** @var  OrderRepository */
    private $orderRepository;

    /** @var  ProductAttributeOptionRepository */
    private $productAttributeOptionRepository;


    public function __construct( OrderRepository $orderRepo,
        ProductAttributeOption $productAttributeOptionRepository)
    {
        parent::__construct();
        $this->orderRepository = $orderRepo;
        $this->productAttributeOptionRepository = $productAttributeOptionRepository;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(SlotedDeliveryOrderDataTable $orderDataTable)
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
        $areas = Area::cursor();

        return $orderDataTable->render('deliver-orders.index', compact('orderStatus',
            'paymentMethods',
            'deliveryTypes', 'drivers', 'markets', 'areas'));
    }

    public function show(ProductOrderDataTable $productOrderDataTable, $id)
    {
        $this->orderRepository->pushCriteria(new OrdersOfUserCriteria(auth()->id()));
        $order = $this->orderRepository->findWithoutFail($id);
        if (empty($order)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.order')]));

            return redirect(route('deliver-orders.index'));
        }

        $productOrderRow = ProductOrder::where('order_id', $id)->first();
        $product_id = $productOrderRow->product_id;
        $productAttributes = $this->productAttributeOptionRepository
            ->where('product_id', $product_id)->get();

        return $productOrderDataTable->with('id', $id)
            ->render('deliver-orders.show', [
                "order" => $order,
                "productAttributes" => $productAttributes
            ]);
    }

    public function assignDriver(Request $request)
    {
        $driverId = $request->driver_id;
        $driver = Driver::where('user_id', $driverId)->first();

        if (!$driver) {
            Flash::error(__('Driver Not Found'));
            return redirect(route('deliver-orders.index'));
        }

        $orderIds = explode(',', $request->ordersIds);
        foreach ($orderIds as $orderId){

            $order = Order::findOrFail($orderId);
            $slotedDeliveryHistory = SlotedDeliveryDriverHistory::query()
                ->where('order_id', $orderId)
                ->first();

            if ($order->is_order_approved && $slotedDeliveryHistory) {
                $order->picked_or_delivered = Order::DELIVERED;
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
        Flash::success(__('Driver Assigned Successfully'));
        return redirect(route('deliver-orders.index'));
    }
}
