<?php

namespace App\Http\Controllers;
use App\Criteria\Orders\OrdersOfUserCriteria;
use App\DataTables\SlotedPickUpOrderDataTable;
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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Laracasts\Flash\Flash;

class SlotPickUpOrderController extends Controller
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
    public function index(SlotedPickUpOrderDataTable $orderDataTable)
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

        return $orderDataTable->render('pick-up-orders.index',
            compact('orderStatus',
            'paymentMethods',
            'deliveryTypes', 'drivers', 'markets', 'areas'));
    }

    public function show(ProductOrderDataTable $productOrderDataTable, $id)
    {
        $this->orderRepository->pushCriteria(new OrdersOfUserCriteria(auth()->id()));
        $order = $this->orderRepository->findWithoutFail($id);
        if (empty($order)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.order')]));

            return redirect(route('pickup-orders.index'));
        }

        $productOrderRow = ProductOrder::where('order_id', $id)->first();
        $product_id = $productOrderRow->product_id;
        $productAttributes = $this->productAttributeOptionRepository
            ->where('product_id', $product_id)->get();

        return $productOrderDataTable->with('id', $id)
            ->render('pick-up-orders.show', [
                "order" => $order,
                "productAttributes" => $productAttributes
            ]);
    }

    public function assignDriver(Request $request)
    {
        $driverId = $request->driver_id;
        $driver = Driver::where('user_id', $driverId)->first();

        $orderIds = explode(',', $request->ordersIds);
        foreach ($orderIds as $orderId) {

            info($orderId);
            $order = Order::findOrFail($orderId);

            if ($order->order_status_id == OrderStatus::STATUS_CANCELED) {
                Flash::error(__('order already canceled'));
                return redirect(route('pickup-orders.index'));
            }

            if ($order->is_order_approved != 1) {
                Flash::error(__('Merchant not approved the order'));
                return redirect(route('pickup-orders.index'));
            }

            if (!$driver) {
                Flash::error(__('Driver Not Found'));
                return redirect(route('pickup-orders.index'));
            }

            $order->picked_or_delivered = Order::PICKED;
            $order->save();

            $slotedDeliveryHistory = new SlotedDeliveryDriverHistory();
            $slotedDeliveryHistory->order_id = $orderId;
            $slotedDeliveryHistory->picked_up_driver_id = $driverId;
            $slotedDeliveryHistory->picked_up_driver_assigned_at = Carbon::now();
            $slotedDeliveryHistory->status =
                SlotedDeliveryDriverHistory::STATUS_PICKUP_ASSIGNED;
            $slotedDeliveryHistory->save();
        }

        Flash::success(__('Driver Assigned Successfully'));
        return redirect(route('pickup-orders.index'));
    }
}
