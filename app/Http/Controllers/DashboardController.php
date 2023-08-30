<?php

namespace App\Http\Controllers;

use App\Models\Field;
use App\Models\Order;
use App\Models\PackageOrder;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\PickUpDeliveryOrder;
use App\Models\ProductOrder;
use App\Repositories\OrderRepository;
use App\Repositories\PackageOrderRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\MarketRepository;
use App\Repositories\ProductOrderRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{

    /** @var  OrderRepository */
    private $orderRepository;


    /**
     * @var UserRepository
     */
    private $userRepository;

    /** @var  MarketRepository */
    private $marketRepository;
    /** @var  PaymentRepository */
    private $paymentRepository;

    public function __construct(OrderRepository $orderRepo, UserRepository $userRepo, PaymentRepository $paymentRepo, MarketRepository $marketRepo, PackageOrderRepository $packageOrderRepository, ProductOrderRepository $productOrderRepository)
    {
        parent::__construct();
        $this->orderRepository = $orderRepo;
        $this->userRepository = $userRepo;
        $this->marketRepository = $marketRepo;
        $this->paymentRepository = $paymentRepo;
        $this->packageOrderRepository = $packageOrderRepository;
        $this->productOrderRepository = $productOrderRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {

        $user = auth()->user();
        $cityId = $user->city_id;

//        $ordersCount = Order::where(function ($query) {
//            $query->where('payment_method_id', PaymentMethod::PAYMENT_METHOD_RAZORPAY)
//                ->where('payment_status', 'SUCCESS');
//
//        })
//            ->orWhere(function ($query) {
//                $query->where('payment_method_id', PaymentMethod::PAYMENT_METHOD_COD)
//                    ->whereIn('payment_status', ['PENDING', 'SUCCESS']);
//            })->count();

        $ordersCount = Order::where('payment_status','SUCCESS')->count();

        $marketUsers = $this->userRepository->get();
        $membersCount = $marketUsers->count();
        $marketsCount = $this->marketRepository->count();
        $markets = $this->marketRepository->take(4)->get();


//        $earning =  $this->paymentRepository
////            ->with('order.market')
////            ->whereHas('order.market', function ($query) use($cityId) {
////                $query->where('city_id', $cityId);
////            })
//            ->get()->sum('price');
        $pickupOrderEarnings = Order::has('pickUpDeliveryOrder')
            ->where('payment_status', 'SUCCESS')
            ->sum('owleto_commission_amount');

        $orderEarnings = ProductOrder::query()
            ->whereHas('order', function ($order) {
                $order->where('payment_status', 'SUCCESS');
            })->sum('commission_amount');

        $packageEarnings = PackageOrder::query()->whereHas('order', function ($order) {
            $order->where('payment_status', 'SUCCESS');
        })->sum('commission_amount');

        $totalDeliveryFee = Order::where('payment_status', 'SUCCESS')
            ->sum('delivery_fee');

//        $earning = ($pickupOrderEarnings + $orderEarnings + $packageEarnings) + $totalDeliveryFee;
        $earning = $this->orderRepository->sum('owleto_commission_amount');


        $payments = [];
        if (!empty($this->paymentRepository)) {

            $payments = $this->paymentRepository->with('order.market')
//                ->whereHas('order.market', function ($query) use($cityId) {
//                    $query->where('city_id', $cityId);
//                })
                ->orderBy("created_at", 'asc')->all()->map(function ($row) {
                    $row['month'] = $row['created_at']->format('M');
                    return $row;
                })->groupBy('month')->map(function ($row) {
                    return $row->sum('price');
                });
        }

        $orders = [];
        if (!empty($this->orderRepository)) {

            $orders = $this->orderRepository
//                ->whereHas('order.market', function ($query) use($cityId) {
//                    $query->where('city_id', $cityId);
//                })
                ->orderBy("created_at", 'asc')->all()->map(function ($row) {
                    $row['month'] = Carbon::parse($row['created_at'])->format('M');
                    return $row;
                })->groupBy('month')->map(function ($row) {
                    return $row->sum('owleto_commission_amount');
                });
        }


//        $labels = array_keys($payments->toArray());
//        $data = array_values($payments->toArray());

        $labels = array_keys($orders->toArray());
        $data = array_values($orders->toArray());

        $ajaxEarningUrl = route('payments.byMonth', ['api_token' => auth()->user()->api_token]);

        $sector = [];
        Field::query()->get()->each(function ($field) use (&$sector) {

            $sector[$field->name] = ProductOrder::query()
                ->whereHas('order', function ($order) {
                    $order->where('payment_status', 'SUCCESS');
                })->whereHas('product', function ($product) use ($field) {
                    $product->where('sector_id', $field->id);
                })->sum('commission_amount');



            $sector[$field->name] += PackageOrder::query()
                ->whereHas('order', function ($order) {
                    $order->where('payment_status', 'SUCCESS');
                })
                ->where('sector_id', $field->id)
                ->sum('commission_amount');

        });

        $sector['PickUp Delivery'] = Order::has('pickUpDeliveryOrder')
            ->where('payment_status', 'SUCCESS')
            ->sum('owleto_commission_amount');

        $sector['Total Delivery Fee'] = $totalDeliveryFee;

        return view('dashboard.index')
            ->with("orders",$orders)
            ->with("ajaxEarningUrl", $ajaxEarningUrl)
            ->with("ordersCount", $ordersCount)
            ->with("marketsCount", $marketsCount)
            ->with("markets", $markets)
            ->with("membersCount", $membersCount)
            ->with("earning", $earning)
            ->with("data", json_encode($data))
            ->with("labels", json_encode($labels))
            ->with('sectorLabels', json_encode(array_keys($sector)))
            ->with('sectorValues', json_encode(array_values($sector)));
    }

    public function byMonth()
    {
        $user = auth()->user();
        $cityId = $user->city_id;

        $payments = [];
        if (!empty($this->paymentRepository)) {
            $payments = $this->paymentRepository->with('order.market')
                ->whereHas('order.market', function ($query) use ($cityId) {
                    $query->where('city_id', $cityId);
                })->orderBy("created_at", 'asc')->all()->map(function ($row) {
                    $row['month'] = $row['created_at']->format('M');
                    return $row;
                })->groupBy('month')->map(function ($row) {
                    return $row->sum('price');
                });
        }

        return $this->sendResponse([array_values($payments->toArray()), array_keys($payments->toArray())], 'Payment retrieved successfully');
    }
}
