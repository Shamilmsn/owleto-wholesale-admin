<?php
/**
 * File name: OrderAPIController.php
 * Last modified: 2020.05.31 at 19:34:40
 * Author: Pixbit Solutions - https://pixbitsolutions.com
 * Copyright (c) 2020
 *
 */

namespace App\Http\Controllers\API;


use App\Criteria\Orders\ProductCriteria;
use App\Events\OrderChangedEvent;
use App\Http\Controllers\Controller;
use App\Mail\NewOrderMail;
use App\Models\Coupon;
use App\Models\Day;
use App\Models\Days;
use App\Models\DeliveryAddress;
use App\Models\Market;
use App\Models\Order;
use App\Models\OrderAddon;
use App\Models\OrderCoupon;
use App\Models\OrderRequest;
use App\Models\PackageDay;
use App\Models\PackageDeliveryTime;
use App\Models\PackageOrder;
use App\Models\PaymentMethod;
use App\Models\PickUpDeliveryOrderRequest;
use App\Models\Product;
use App\Models\ProductAddon;
use App\Models\ProductOrder;
use App\Models\PujaBookingOrder;
use App\Models\SubscriptionPackage;
use App\Models\TemporaryOrderRequest;
use App\Models\User;
use App\Models\UserWallet;
use App\Models\UserWalletTransaction;
use App\Notifications\AssignedOrder;
use App\Notifications\NewOrder;
use App\Notifications\StatusChangedOrder;
use App\Repositories\CartRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\OrderRepository;
use App\Repositories\PackageOrderRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\PickUpDeliveryOrderRepository;
use App\Repositories\ProductOrderRepository;
use App\Repositories\ProductOrderRequestOrderRepository;
use App\Repositories\UserRepository;
use App\Services\ProductWiseOrderService;
use Flash;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Prettus\Validator\Exceptions\ValidatorException;
use Razorpay\Api\Api;

/**
 * Class OrderController
 * @package App\Http\Controllers\API
 */
class OrderAPIController extends Controller
{
    /** @var  OrderRepository */
    private $orderRepository;

    /** @var  ProductOrderRequestOrderRepository */
    private $productOrderRequestOrderRepository;

    /** @var  PickUpDeliveryOrderRepository */
    private $pickUpDeliveryOrderRepository;

    /** @var  ProductOrderRepository */
    private $productOrderRepository;

    /** @var  PackageOrderRepository */
    private $packageOrderRepository;

    /** @var  CartRepository */
    private $cartRepository;
    /** @var  UserRepository */
    private $userRepository;
    /** @var  PaymentRepository */
    private $paymentRepository;
    /** @var  NotificationRepository */
    private $notificationRepository;

    /**
     * OrderAPIController constructor.
     * @param OrderRepository $orderRepo
     * @param ProductOrderRepository $productOrderRepository
     * @param CartRepository $cartRepo
     * @param PaymentRepository $paymentRepo
     * @param NotificationRepository $notificationRepo
     * @param UserRepository $userRepository
     */
    public function __construct(OrderRepository                    $orderRepo,
                                ProductOrderRepository             $productOrderRepository,
                                CartRepository                     $cartRepo, PaymentRepository $paymentRepo,
                                NotificationRepository             $notificationRepo,
                                UserRepository                     $userRepository, PackageOrderRepository $packageOrderRepository,
                                ProductOrderRequestOrderRepository $productOrderRequestOrderRepository,
                                PickUpDeliveryOrderRepository      $pickUpDeliveryOrderRepository)
    {
        $this->orderRepository = $orderRepo;
        $this->productOrderRepository = $productOrderRepository;
        $this->cartRepository = $cartRepo;
        $this->userRepository = $userRepository;
        $this->paymentRepository = $paymentRepo;
        $this->notificationRepository = $notificationRepo;
        $this->notificationRepository = $notificationRepo;
        $this->packageOrderRepository = $packageOrderRepository;
        $this->productOrderRequestOrderRepository = $productOrderRequestOrderRepository;
        $this->pickUpDeliveryOrderRepository = $pickUpDeliveryOrderRepository;
    }

    /**
     * Display a listing of the Order.
     * GET|HEAD /orders
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $start = new Carbon('first day of this month');
        $last = new Carbon('last day of this month');

        $type = Order::PRODUCT_TYPE;

        $orders = Order::with('user', 'productOrders', 'productOrders.product', 'driver',
            'productOrders.order_addons', 'productOrders.options', 'orderStatus', 'deliveryAddress', 'payment', 'market', 'deliveryType')
            ->where('type', $type)
            ->where('user_id', $request->user_id);

        if ($request->search) {

            $orders = $orders->whereHas('productOrders.product', function ($query) use ($request) {
                $query->where('base_name', 'like', '%' . $request->search . '%')
                    ->orWhere('variant_name', 'like', '%' . $request->search . '%');
            });
        }

        $orders = $orders->orderBy('created_at', 'desc')->get();

        $userOrders = [];

        foreach ($orders as $order) {
            if ($order->payment_method_id == PaymentMethod::PAYMENT_METHOD_RAZORPAY) {
                if ($order->payment_status == 'SUCCESS') {
                    array_push($userOrders, $order);
                }
            } else {
                array_push($userOrders, $order);
            }
        }

        return $this->sendResponse($userOrders, 'Orders retrieved successfully');
    }

    public function show(Request $request, $id)
    {
        $type = Order::PRODUCT_TYPE;

        $order = Order::with('user', 'productOrders', 'productOrders.product',
            'productOrders.order_addons', 'productOrders.options', 'orderStatus', 'deliveryAddress', 'payment', 'market', 'deliveryType')
            ->where('id', $id)
            ->first();

        if (empty($order)) {
            return $this->sendError('Order not found');
        }

        return $this->sendResponse($order->toArray(), 'Order retrieved successfully');

    }

    public function store(Request $request, ProductWiseOrderService $orderService)
    {
        info("HERE");
        info($request);
        if (isset($request->payment_method_id)) {

            //order changed from vendor based to product based
            $vendors = [];
            if (count($request->get('products')) > 0) {
                $productIds = array_unique(collect($request->get('products'))->pluck('product_id')->toArray());
                $vendors = Product::whereIn('id', $productIds)->pluck('market_id')->toArray();
            }

            $mainData = [];
            if (count($vendors) > 1 && count($request->get('products')) > 0) {

                $deliveryAddress = DeliveryAddress::findOrFail($request->get('delivery_address_id'));
                $deliveryLat = $deliveryAddress->latitude;
                $deliveryLon = $deliveryAddress->longitude;

                foreach ($vendors as $i => $vendor) {
                    $market = Market::findOrFail($vendor);
                    if ($i == 0) {
                        $mainData[] = $orderService->fetchBaseOrderForSubOrder(collect($request));
                    }
                    $mainData[] = $orderService->fetchMutatedRequestForSubOrder(
                        collect($request),
                        $market,
                        count($vendors),
                        $deliveryLat,
                        $deliveryLon
                    );

                }

            } else {
                $data = $request->all();
                $data['order_category'] = Order::VENDOR_BASED;

                $mainData[] = $data;
            }
            $parentId = null;

            foreach ($mainData as $data) {
                if ($request->payment_method_id == PaymentMethod::PAYMENT_METHOD_RAZORPAY) {
                    return $this->razorPay($request);
                }
                if ($request->payment_method_id == PaymentMethod::PAYMENT_METHOD_COD) {
                    $response = $this->cashPayment(collect($data), $parentId);
                    info("FINAL : " . json_encode($response));
                    if ($response['order']['order_category'] == Order::PRODUCT_BASED) {
                        $parentId = $response['order']['id'];
                    }
                }
                if ($request->payment_method_id == PaymentMethod::PAYMENT_METHOD_WALLET) {
                    return $this->walletTransaction($request);
                }
            }

            if ($response['order']['parent_id']) {
                $order = Order::findOrFail($response['order']['parent_id']);
                $response['order'] = $order;
            }

            return $this->sendResponse($response, __('lang.saved_successfully', ['operator' => __('lang.order')]));
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    private function razorPay(Request $request)
    {
        $input = $request->all();

        $owletoCommissionAmount = 0;
        $totalAmount = $input['total_amount'];
        $delivery_type = $input['delivery_type_id'];
        $marketId = null;
        $productCount = count($input['products']);
        $deliveryFee = $request->get('delivery_fee', 0);

        $amountFromWallet = $input['amount_from_wallet'];


        DB::beginTransaction();


        if ($amountFromWallet) {
            $userWallet = UserWallet::where('user_id', $input['user_id'])->first();
            $currentUserWalletBalance = $userWallet->balance;

            if ($amountFromWallet > $currentUserWalletBalance) {
                return $this->sendError('Wallet does not have sufficient amount', 406);
            }

        }
        if ($productCount > 0) {
            foreach ($input['products'] as $productOrder) {
                $product = Product::find($productOrder['product_id']);
                $productQuantity = $productOrder['quantity'];
                $owletoCommissionAmount = $owletoCommissionAmount + ($product->owleto_commission_amount * $productQuantity);
                $marketId = $product->market_id;
            }

            $type = Order::PRODUCT_TYPE;
        }

        if ($input['package']) {
            $package = SubscriptionPackage::where('id', $input['package']['package_id'])->first();
            $product = Product::where('id', $package->product_id)->first();
            $owletoCommissionAmount = $owletoCommissionAmount + $product->owleto_commission_amount;
            $marketId = $product->market_id;
            $type = Order::PACKAGE_TYPE;
        }

        if ($input['order_request']) {
            $tempOrderRequest = TemporaryOrderRequest::where('id', $input['order_request']['temporary_order_request_id'])->first();
            $orderRequest = OrderRequest::where('id', $tempOrderRequest->order_request_id)->first();
            $type = Order::ORDER_REQUEST_TYPE;
            $marketId = $orderRequest->market_id;
            $market = Market::find($marketId);
            $owletoCommissionAmount = $market->order_request_commission_amount ? $totalAmount * $market->order_request_commission_amount / 100 : 0;
        }

        if ($input['pickup_delivery_order']) {
            $owletoCommissionAmount = $totalAmount;
            $type = Order::PICKUP_DELIVERY_ORDER_TYPE;
            $marketId = null;
        }


        try {
            $user = $this->userRepository->findWithoutFail($input['user_id']);
            if (empty($user)) {
                return $this->sendError('User not found');
            }

            $isWalletUsed = ($amountFromWallet ? true : false);

            if (empty($input['delivery_address_id'])) {
                $order = $this->orderRepository->create([
                    'user_id' => $request->get('user_id'),
                    'order_status_id' => $request->get('order_status_id'),
                    'tax' => $request->get('tax'),
                    'delivery_fee' => $request->get('delivery_fee'),
                    'delivery_type_id' => $delivery_type,
                    'payment_method_id' => $request->get('payment_method_id'),
                    'hint' => $request->get('hint'),
                    'total_amount' => $totalAmount,
                    'sub_total' => $request->get('sub_total'),
                    'market_balance' => $totalAmount - $owletoCommissionAmount,
                    'owleto_commission_amount' => $owletoCommissionAmount,
                    'market_id' => $marketId,
                    'type' => $type,
                    'amount_from_wallet' => $amountFromWallet,
                    'is_wallet_used' => $isWalletUsed,
                    'distance' => $request->get('distance'),
                    'sector_id' => $request->get('sector_id')
                ]);
            } else {
                $deliveryAddress = DeliveryAddress::find($request->get('delivery_address_id'));

                $order = $this->orderRepository->create([
                    'user_id' => $request->get('user_id'),
                    'order_status_id' => $request->get('order_status_id'),
                    'tax' => $request->get('tax'),
                    'delivery_type_id' => $delivery_type,
                    'delivery_address_id' => $request->get('delivery_address_id'),
                    'payment_method_id' => $request->get('payment_method_id'),
                    'delivery_fee' => $request->get('delivery_fee'),
                    'hint' => $request->get('hint'),
                    'total_amount' => $totalAmount,
                    'sub_total' => $request->get('sub_total'),
                    'market_balance' => $totalAmount - $owletoCommissionAmount,
                    'owleto_commission_amount' => $owletoCommissionAmount,
                    'market_id' => $marketId,
                    'type' => $type,
                    'amount_from_wallet' => $amountFromWallet,
                    'is_wallet_used' => $isWalletUsed,
                    'distance' => $request->get('distance'),
                    'sector_id' => $request->get('sector_id'),
                    'latitude' => $deliveryAddress->latitude,
                    'longitude' => $deliveryAddress->longitude,
                    'address_data' => json_encode($deliveryAddress),

                ]);
            }

            if ($amountFromWallet) {
                $userWalletTransaction = new UserWalletTransaction();
                $userWalletTransaction->user_id = $request->get('user_id');
                $userWalletTransaction->order_id = $order->id;
                $userWalletTransaction->description = 'Amount Credited';
                $userWalletTransaction->type = UserWalletTransaction::TYPE_DEBIT;
                $userWalletTransaction->amount = $amountFromWallet;
                $userWalletTransaction->save();
            }

            if ($productCount > 0) {
                foreach ($input['products'] as $productOrder) {
                    $product = Product::find($productOrder['product_id']);
                    $productOrder['commission_percentage'] = $product->owleto_commission_percentage;
                    $productOrder['commission_amount'] = $product->owleto_commission_amount * $productOrder['quantity'];
                    $productOrder['order_id'] = $order->id;

                    $productsOrder = $this->productOrderRepository->create($productOrder);

                    if (count($productOrder['addons']) > 0) {
                        foreach ($productOrder['addons'] as $productOrderAddon) {

                            $productAddon = ProductAddon::where('id', $productOrderAddon)->first();

                            $orderAddon = new OrderAddon();
                            $orderAddon->product_order_id = $productsOrder->id;
                            $orderAddon->product_addon_id = $productOrderAddon;
                            $orderAddon->price = $productAddon->price;
                            $orderAddon->save();
                        }
                    }
                }
            }

            if ($input['package']) {

                $package = SubscriptionPackage::where('id', $input['package']['package_id'])->first();
                $packageDays = PackageDay::where('package_id', $package->id)->get();

                $packageDeliveryTimes = PackageDeliveryTime::where('package_id', $package->id)->get();

                $pricePerDelivery = $package->price / $package->days;

                $product = Product::where('id', $package->product_id)->first();

                $marketBalace = $pricePerDelivery - $product->owleto_commission_amount;

                $productOrder['commission_percentage'] = $product->owleto_commission_percentage;
                $productOrder['commission_amount'] = $product->owleto_commission_amount;
                $productOrder['order_id'] = $order->id;
                $productOrder['package_id'] = $input['package']['package_id'];
                $productOrder['package_price'] = $input['package']['price'];
                $productOrder['price_per_delivery'] = $pricePerDelivery;
                $productOrder['market_balance'] = $marketBalace;

                $packageDates = [];
                foreach ($packageDays as $packageDay) {
                    $day = Day::where('id', $packageDay->day_id)->first();
                    array_push($packageDates, $day->day_of_week);
                }
                $now = Carbon::now();
                $collection = collect($packageDates);
                $allDays = [];
                $i = 0;

                while ($i < $package->days) {
                    $todayName = $now->dayOfWeek;
                    $date = $now->getTimestamp();
                    if (Carbon::today()->format('d/m/Y') == date('d/m/Y', $date)) {
                        $hour = Carbon::now()->hour;
                        foreach ($packageDeliveryTimes as $packageDeliveryTime) {
                            if ($packageDeliveryTime->delivery_time_id == 1) {
                                if ($hour < PackageOrder::MORNING_TIME_LIMIT) {
                                    if ($collection->contains($todayName)) {
                                        array_push($allDays, $date);
                                        $i++;
                                    }
                                }
                            }
                            if ($packageDeliveryTime->delivery_time_id == 2) {
                                if ($hour < PackageOrder::EVENING_TIME_LIMIT) {
                                    if ($collection->contains($todayName)) {
                                        array_push($allDays, $date);
                                        $i++;
                                    }
                                }
                            }
                        }
                        $now = $now->addDays(1);
                    } else {
                        if ($collection->contains($todayName)) {
                            array_push($allDays, $date);
                            $i++;
                        }
                        $now = $now->addDays(1);
                    }
                }

                $i = 0;
                $j = 0;
                while ($i <= count($allDays)) {
                    $deliveryAddress = DeliveryAddress::find($request->get('delivery_address_id'));
                    foreach ($packageDeliveryTimes as $packageDeliveryTime) {
                        $dateTime = date('m/d/Y H:i:s', $allDays[$i]);
                        $date = date('d/m/Y', $allDays[$i]);

                        if ($date == date('d/m/Y')) {
                            $hour = Carbon::now()->hour;
                            if ($packageDeliveryTime->delivery_time_id == 1) {
                                if ($hour < PackageOrder::MORNING_TIME_LIMIT) {
                                    $productOrder['date'] = Carbon::parse($dateTime);
                                    $productOrder['created_at'] = Carbon::now()->timezone('Asia/Kolkata');
                                    $productOrder['delivery_time_id'] = $packageDeliveryTime->delivery_time_id;
                                    $productOrder['order_status_id'] = $request->get('order_status_id');
                                    $productOrder['market_id'] = $marketId;
                                    $productOrder['delivery_address_id'] = $request->get('delivery_address_id');
                                    $productOrder['payment_method_id'] = $request->get('payment_method_id');
                                    $productOrder['tax'] = $request->get('tax');
                                    $productOrder['user_id'] = $request->get('user_id');
                                    $productOrder['distance'] = $request->get('distance');
                                    $productOrder['sector_id'] = $request->get('sector_id');
                                    $productOrder['latitude'] = $deliveryAddress->latitude;
                                    $productOrder['longitude'] = $deliveryAddress->longitude;
                                    $productOrder['address_data'] = json_encode($deliveryAddress);
                                    $this->packageOrderRepository->create($productOrder);
                                    $j++;
                                    if ($j == count($allDays)) {
                                        $i = $j;
                                        break;
                                    }
                                }
                            }

                            if ($packageDeliveryTime->delivery_time_id == 2) {
                                if ($hour < PackageOrder::EVENING_TIME_LIMIT) {
                                    $productOrder['date'] = Carbon::parse($dateTime);
                                    $productOrder['created_at'] = Carbon::now()->timezone('Asia/Kolkata');
                                    $productOrder['delivery_time_id'] = $packageDeliveryTime->delivery_time_id;
                                    $productOrder['order_status_id'] = $request->get('order_status_id');
                                    $productOrder['market_id'] = $marketId;
                                    $productOrder['delivery_address_id'] = $request->get('delivery_address_id');
                                    $productOrder['payment_method_id'] = $request->get('payment_method_id');
                                    $productOrder['tax'] = $request->get('tax');
                                    $productOrder['user_id'] = $request->get('user_id');
                                    $productOrder['distance'] = $request->get('distance');
                                    $productOrder['sector_id'] = $request->get('sector_id');
                                    $productOrder['latitude'] = $deliveryAddress->latitude;
                                    $productOrder['longitude'] = $deliveryAddress->longitude;
                                    $productOrder['address_data'] = json_encode($deliveryAddress);
                                    $this->packageOrderRepository->create($productOrder);

                                    $j++;
                                    if ($j == count($allDays)) {
                                        $i = $j;
                                        break;
                                    }
                                }
                            }

                        } else {
                            $productOrder['date'] = Carbon::parse($dateTime);
                            $productOrder['created_at'] = Carbon::now()->timezone('Asia/Kolkata');
                            $productOrder['delivery_time_id'] = $packageDeliveryTime->delivery_time_id;
                            $productOrder['order_status_id'] = $request->get('order_status_id');
                            $productOrder['market_id'] = $marketId;
                            $productOrder['delivery_address_id'] = $request->get('delivery_address_id');
                            $productOrder['payment_method_id'] = $request->get('payment_method_id');
                            $productOrder['tax'] = $request->get('tax');
                            $productOrder['user_id'] = $request->get('user_id');
                            $productOrder['distance'] = $request->get('distance');
                            $productOrder['sector_id'] = $request->get('sector_id');
                            $productOrder['latitude'] = $deliveryAddress->latitude;
                            $productOrder['longitude'] = $deliveryAddress->longitude;
                            $productOrder['address_data'] = json_encode($deliveryAddress);
                            $this->packageOrderRepository->create($productOrder);
                            $j++;
                            if ($j == count($allDays)) {
                                $i = $j;
                                break;
                            }
                        }
                    }
                    $i++;
                }
            }

            if ($input['order_request']) {
                $tempOrderRequestId = $input['order_request']['temporary_order_request_id'];
                $productOrderRequestOrder['temporary_order_request_id'] = $tempOrderRequestId;
                $productOrderRequestOrder['price'] = $input['order_request']['price'];
                $productOrderRequestOrder['order_id'] = $order->id;
                $this->productOrderRequestOrderRepository->create($productOrderRequestOrder);

                $tempOrderRequest = TemporaryOrderRequest::find($tempOrderRequestId);
                $tempOrderRequest->status = OrderRequest::STATUS_NOTIFICATION_SEND;
                $tempOrderRequest->save();

                $OrderRequest = OrderRequest::find($tempOrderRequest->order_request_id);
                $OrderRequest->status = OrderRequest::STATUS_NOTIFICATION_SEND;
                $OrderRequest->save();

            }

            if ($input['pickup_delivery_order']) {

                $pickUpDeliveryOrderRequestId = $input['pickup_delivery_order']['pick_up_delivery_order_request_id'];
                $pickUpDeliveryOrder['pick_up_delivery_order_request_id'] = $pickUpDeliveryOrderRequestId;
                $pickUpDeliveryOrder['price'] = $input['pickup_delivery_order']['price'];
                $pickUpDeliveryOrder['order_id'] = $order->id;
                $this->pickUpDeliveryOrderRepository->create($pickUpDeliveryOrder);


                $pickUpDeliveryOrderRequest = PickUpDeliveryOrderRequest::where('id', $pickUpDeliveryOrderRequestId)->first();
                $pickUpDeliveryOrderRequest->status = PickUpDeliveryOrderRequest::STATUS_ORDER_CREATED;
                $pickUpDeliveryOrderRequest->save();
            }


            $payment = $this->paymentRepository->create([
                "user_id" => $input['user_id'],
                "order_id" => $order->id,
                "description" => trans("lang.payment_order_waiting"),
                "price" => $totalAmount,
                "status" => 'Waiting for Client',
                "method" => PaymentMethod::RAZORPAY,
                'payment_method_id' => PaymentMethod::PAYMENT_METHOD_RAZORPAY
            ]);

            $amountToRazorpay = (int)($totalAmount * 100);

            $api_key = config('services.razorpay.api_key');
            $api_secret = config('services.razorpay.api_secret');
            $api = new Api($api_key, $api_secret);
            $razorPayOrder = $api->order->create(array(
                'receipt' => $order->id,
                'amount' => $amountToRazorpay,
                'currency' => 'INR',
            ));

            $order = Order::with(['user', 'market'])->findOrFail($order->id);
            $order->razorpay_order_id = $razorPayOrder['id'];
            $order->payment_gateway = 'RAZORPAY';
            $order->payment_id = $payment->id;

            $order->save();

            $response = ['order' => $order, 'razorpayOrderId' => $razorPayOrder['id']];

        } catch (ValidatorException $e) {
            return $this->sendError($e->getMessage());
        }

        if ($request->coupon_code) {

            $coupon = Coupon::where('code', $request->coupon_code)->first();

            $order->is_coupon_used = true;
            $order->coupon_code = $request->coupon_code;
            $order->coupon_discount_amount = $coupon->discount;
            $order->save();

            if (!$coupon) {
                return $this->sendError('Coupon code is not found');
            }

            $orderCoupon = new OrderCoupon();

            $orderCoupon->user_id = $request->user_id;
            $orderCoupon->order_id = $order->id;
            $orderCoupon->coupon_id = $coupon->id;
            $orderCoupon->coupon_redeemed_amount = $request->coupon_redeemed_amount;

            $isCouponAlreadyUsed = OrderCoupon::where('coupon_id', $coupon->id)
                ->orderBy('id', 'desc')
                ->first();

            if (!$isCouponAlreadyUsed) {
                $orderCoupon->number_of_usage = 1;
            } else {
                $number_of_usage = $isCouponAlreadyUsed->number_of_usage;
                $orderCoupon->number_of_usage = $number_of_usage + 1;
            }

            $orderCoupon->save();

        }

        DB::commit();

        event(new \App\Events\NewOrderEvent());

        return $this->sendResponse($response, 'Inserted Successfully');
    }

    private function cashPayment(Collection $request, $parentId)
    {
        $input = $request->all();

        $owletoCommissionAmount = 0;
        $totalAmount = $input['total_amount'];
        $marketId = null;
        $productCount = count($input['products']);
        $amountFromWallet = $input['amount_from_wallet'];
        $deliveryFee = $request->get('delivery_fee') ?: 0;

        DB::beginTransaction();


        if ($amountFromWallet) {
            $userWallet = UserWallet::where('user_id', $input['user_id'])->first();
            $currentUserWalletBalance = $userWallet->balance;

            if ($amountFromWallet > $currentUserWalletBalance) {
                return $this->sendError('Wallet does not have sufficient amount', 406);
            }
            $userWallet->balance = $userWallet->balance - $amountFromWallet;
            $userWallet->save();
        }

        if ($productCount > 0) {
            foreach ($input['products'] as $productOrder) {
                $product = Product::find($productOrder['product_id']);
                $productQuantity = $productOrder['quantity'];
                $owletoCommissionAmount = $owletoCommissionAmount + ($product->owleto_commission_amount * $productQuantity);
                $marketId = $product->market_id;
            }
            $type = Order::PRODUCT_TYPE;
        }


        if ($input['package']) {
            $package = SubscriptionPackage::where('id', $input['package']['package_id'])->first();
            $product = Product::where('id', $package->product_id)->first();
            $owletoCommissionAmount = $owletoCommissionAmount + $product->owleto_commission_amount;
            $marketId = $product->market_id;
            $type = Order::PACKAGE_TYPE;
        }

        if ($input['order_request']) {

            $tempOrderRequest = TemporaryOrderRequest::where('id', $input['order_request']['temporary_order_request_id'])->first();
            $orderRequest = OrderRequest::where('id', $tempOrderRequest->order_request_id)->first();
            $type = Order::ORDER_REQUEST_TYPE;
            $marketId = $orderRequest->market_id;

            $market = Market::find($marketId);
            $owletoCommissionAmount = $market->order_request_commission_amount ? $totalAmount * $market->order_request_commission_amount / 100 : 0;

        }

        if ($input['pickup_delivery_order']) {
            $owletoCommissionAmount = $totalAmount;
            $type = Order::PICKUP_DELIVERY_ORDER_TYPE;
            $marketId = null;
        }

        if ($input['order_category'] == Order::PRODUCT_BASED) {
            $marketId = null;
        }

        info("MARKET ID : " . $marketId);

        try {
            $isWalletUsed = ($amountFromWallet ? true : false);
            $deliveryAddress = DeliveryAddress::find($request->get('delivery_address_id'));

            $order = $this->orderRepository->create([
                'user_id' => $request->get('user_id'),
                'order_status_id' => $request->get('order_status_id'),
                'tax' => $request->get('tax'),
                'delivery_address_id' => $request->get('delivery_address_id'),
                'delivery_type_id' => $request->get('delivery_type_id'),
                'payment_method_id' => $request->get('payment_method_id'),
                'delivery_fee' => $request->get('delivery_fee'),
                'hint' => $request->get('hint'),
                'total_amount' => $totalAmount,
                'sub_total' => $request->get('sub_total'),
                'market_balance' => $totalAmount - $owletoCommissionAmount,
                'owleto_commission_amount' => $owletoCommissionAmount,
                'market_id' => $marketId,
                'type' => $type,
                'amount_from_wallet' => $amountFromWallet,
                'is_wallet_used' => $isWalletUsed,
                'distance' => $input['distance'],
                'sector_id' => $request->get('sector_id'),
                'latitude' => $deliveryAddress->latitude,
                'longitude' => $deliveryAddress->longitude,
                'address_data' => json_encode($deliveryAddress),
                'order_category' => $request->get('order_category'),
                'parent_id' => $parentId
            ]);

            if ($amountFromWallet) {
                $userWalletTransaction = new UserWalletTransaction();
                $userWalletTransaction->user_id = $request->get('user_id');
                $userWalletTransaction->order_id = $order->id;
                $userWalletTransaction->description = 'Amount Credited';
                $userWalletTransaction->type = UserWalletTransaction::TYPE_DEBIT;
                $userWalletTransaction->amount = $amountFromWallet;
                $userWalletTransaction->save();
            }

            if ($productCount > 0) {
                foreach ($input['products'] as $productOrder) {
                    $product = Product::find($productOrder['product_id']);
                    $productOrder['commission_percentage'] = $product->owleto_commission_percentage;
                    $productOrder['commission_amount'] = $product->owleto_commission_amount;
                    $productOrder['order_id'] = $order->id;

                    $productsOrder = $this->productOrderRepository->create($productOrder);

                    if (count($productOrder['addons']) > 0) {
                        foreach ($productOrder['addons'] as $productOrderAddon) {

                            $productAddon = ProductAddon::where('id', $productOrderAddon)->first();

                            $orderAddon = new OrderAddon();
                            $orderAddon->product_order_id = $productsOrder->id;
                            $orderAddon->product_addon_id = $productOrderAddon;
                            $orderAddon->price = $productAddon->price;
                            $orderAddon->save();
                        }
                    }
                }
            }

            if ($input['package']) {

                $package = SubscriptionPackage::where('id', $input['package']['package_id'])->first();
                $packageDays = PackageDay::where('package_id', $package->id)->get();

                $packageDeliveryTimes = PackageDeliveryTime::where('package_id', $package->id)->get();
                $pricePerDelivery = $package->price / $package->days;

                $product = Product::where('id', $package->product_id)->first();

                $marketBalace = $pricePerDelivery - $product->owleto_commission_amount;

                $productOrder['commission_percentage'] = $product->owleto_commission_percentage;
                $productOrder['commission_amount'] = $product->owleto_commission_amount;
                $productOrder['order_id'] = $order->id;
                $productOrder['package_id'] = $input['package']['package_id'];
                $productOrder['package_price'] = $input['package']['price'];
                $productOrder['price_per_delivery'] = $pricePerDelivery;
                $productOrder['market_balance'] = $marketBalace;

                $packageDates = [];
                foreach ($packageDays as $packageDay) {
                    $day = Day::where('id', $packageDay->day_id)->first();
                    array_push($packageDates, $day->day_of_week);
                }
                $now = Carbon::now();
                $collection = collect($packageDates);

                $allDays = [];

                $i = 0;

                while ($i < $package->days) {
                    $todayName = $now->dayOfWeek;
                    $date = $now->getTimestamp();
                    if (Carbon::today()->format('d/m/Y') == date('d/m/Y', $date)) {
                        $hour = Carbon::now()->hour;
                        foreach ($packageDeliveryTimes as $packageDeliveryTime) {
                            if ($packageDeliveryTime->delivery_time_id == 1) {
                                if ($hour < PackageOrder::MORNING_TIME_LIMIT) {
                                    if ($collection->contains($todayName)) {
                                        array_push($allDays, $date);
                                        $i++;
                                    }
                                }
                            }
                            if ($packageDeliveryTime->delivery_time_id == 2) {
                                if ($hour < PackageOrder::EVENING_TIME_LIMIT) {
                                    if ($collection->contains($todayName)) {
                                        array_push($allDays, $date);
                                        $i++;
                                    }
                                }
                            }
                        }
                        $now = $now->addDays(1);
                    } else {
                        if ($collection->contains($todayName)) {
                            array_push($allDays, $date);
                            $i++;
                        }
                        $now = $now->addDays(1);
                    }
                }

                $i = 0;
                $j = 0;
                while ($i <= count($allDays)) {
                    $deliveryAddress = DeliveryAddress::find($request->get('delivery_address_id'));
                    foreach ($packageDeliveryTimes as $packageDeliveryTime) {
                        $dateTime = date('m/d/Y H:i:s', $allDays[$i]);
                        $date = date('d/m/Y', $allDays[$i]);

                        if ($date == date('d/m/Y')) {
                            $hour = Carbon::now()->hour;
                            if ($packageDeliveryTime->delivery_time_id == 1) {
                                if ($hour < PackageOrder::MORNING_TIME_LIMIT) {
                                    $productOrder['date'] = Carbon::parse($dateTime);
                                    $productOrder['created_at'] = Carbon::now()->timezone('Asia/Kolkata');
                                    $productOrder['delivery_time_id'] = $packageDeliveryTime->delivery_time_id;
                                    $productOrder['order_status_id'] = $request->get('order_status_id');
                                    $productOrder['market_id'] = $marketId;
                                    $productOrder['delivery_address_id'] = $request->get('delivery_address_id');
                                    $productOrder['payment_method_id'] = $request->get('payment_method_id');
                                    $productOrder['tax'] = $request->get('tax');
                                    $productOrder['user_id'] = $request->get('user_id');
                                    $productOrder['distance'] = $request->get('distance');
                                    $productOrder['sector_id'] = $request->get('sector_id');
                                    $productOrder['latitude'] = $deliveryAddress->latitude;
                                    $productOrder['longitude'] = $deliveryAddress->longitude;
                                    $productOrder['address_data'] = json_encode($deliveryAddress);
                                    $this->packageOrderRepository->create($productOrder);
                                    $j++;
                                    if ($j == count($allDays)) {
                                        $i = $j;
                                        break;
                                    }
                                }
                            }

                            if ($packageDeliveryTime->delivery_time_id == 2) {
                                if ($hour < PackageOrder::EVENING_TIME_LIMIT) {
                                    $productOrder['date'] = Carbon::parse($dateTime);
                                    $productOrder['created_at'] = Carbon::now()->timezone('Asia/Kolkata');
                                    $productOrder['delivery_time_id'] = $packageDeliveryTime->delivery_time_id;
                                    $productOrder['order_status_id'] = $request->get('order_status_id');
                                    $productOrder['market_id'] = $marketId;
                                    $productOrder['delivery_address_id'] = $request->get('delivery_address_id');
                                    $productOrder['payment_method_id'] = $request->get('payment_method_id');
                                    $productOrder['tax'] = $request->get('tax');
                                    $productOrder['user_id'] = $request->get('user_id');
                                    $productOrder['distance'] = $request->get('distance');
                                    $productOrder['sector_id'] = $request->get('sector_id');
                                    $productOrder['latitude'] = $deliveryAddress->latitude;
                                    $productOrder['longitude'] = $deliveryAddress->longitude;
                                    $productOrder['address_data'] = json_encode($deliveryAddress);
                                    $this->packageOrderRepository->create($productOrder);

                                    $j++;
                                    if ($j == count($allDays)) {
                                        $i = $j;
                                        break;
                                    }
                                }
                            }

                        } else {
                            $productOrder['date'] = Carbon::parse($dateTime);
                            $productOrder['created_at'] = Carbon::now()->timezone('Asia/Kolkata');
                            $productOrder['delivery_time_id'] = $packageDeliveryTime->delivery_time_id;
                            $productOrder['order_status_id'] = $request->get('order_status_id');
                            $productOrder['market_id'] = $marketId;
                            $productOrder['delivery_address_id'] = $request->get('delivery_address_id');
                            $productOrder['payment_method_id'] = $request->get('payment_method_id');
                            $productOrder['tax'] = $request->get('tax');
                            $productOrder['user_id'] = $request->get('user_id');
                            $productOrder['distance'] = $request->get('distance');
                            $productOrder['sector_id'] = $request->get('sector_id');
                            $productOrder['latitude'] = $deliveryAddress->latitude;
                            $productOrder['longitude'] = $deliveryAddress->longitude;
                            $productOrder['address_data'] = json_encode($deliveryAddress);
                            $this->packageOrderRepository->create($productOrder);
                            $j++;
                            if ($j == count($allDays)) {
                                $i = $j;
                                break;
                            }
                        }
                    }
                    $i++;
                }

            }

            if ($input['order_request']) {
                $tempOrderRequestId = $input['order_request']['temporary_order_request_id'];
                $productOrderRequestOrder['temporary_order_request_id'] = $tempOrderRequestId;
                $productOrderRequestOrder['price'] = $input['order_request']['price'];
                $productOrderRequestOrder['order_id'] = $order->id;
                $this->productOrderRequestOrderRepository->create($productOrderRequestOrder);

                $tempOrderRequest = TemporaryOrderRequest::find($tempOrderRequestId);
                $tempOrderRequest->status = OrderRequest::STATUS_NOTIFICATION_SEND;
                $tempOrderRequest->save();

                $OrderRequest = OrderRequest::find($tempOrderRequest->order_request_id);
                $OrderRequest->status = OrderRequest::STATUS_NOTIFICATION_SEND;
                $OrderRequest->save();
            }

            if ($input['pickup_delivery_order']) {

                $pickUpDeliveryOrderRequestId = $input['pickup_delivery_order']['pick_up_delivery_order_request_id'];
                $pickUpDeliveryOrder['pick_up_delivery_order_request_id'] = $pickUpDeliveryOrderRequestId;
                $pickUpDeliveryOrder['price'] = $input['pickup_delivery_order']['price'];
                $pickUpDeliveryOrder['order_id'] = $order->id;
                $this->pickUpDeliveryOrderRepository->create($pickUpDeliveryOrder);

                $pickUpDeliveryOrderRequest = PickUpDeliveryOrderRequest::where('id', $pickUpDeliveryOrderRequestId)->first();
                $pickUpDeliveryOrderRequest->status = PickUpDeliveryOrderRequest::STATUS_ORDER_CREATED;
                $pickUpDeliveryOrderRequest->save();

            }

            $marketID = null;

            if ($input['order_category'] != Order::PRODUCT_BASED) {
                if ($productCount > 0) {
                    $marketID = $order->productOrders[0]->product->market->id;
                }
                if ($input['package']) {
                    $marketID = $order->packageOrders[0]->package->product->market->id;
                }
                if ($input['order_request']) {
                    $marketID = $marketId;
                }

                if ($input['pickup_delivery_order']) {
                    $marketID = null;
                }

            }


            info("PARENT : " . $parentId);

            if (!$parentId) {
                $payment = $this->paymentRepository->create([
                    "user_id" => $input['user_id'],
                    "order_id" => $order->id,
                    "description" => trans("lang.payment_order_waiting"),
                    "price" => $totalAmount,
                    "status" => 'Waiting for Client',
                    "method" => PaymentMethod::CASH_ON_DELIVERY,
                    'payment_method_id' => PaymentMethod::PAYMENT_METHOD_COD
                ]);


                $this->orderRepository->update(['payment_id' => $payment->id, 'market_id' => $marketID], $order->id);
            }

            $this->cartRepository->deleteWhere(['user_id' => $order->user_id]);

            if ($productCount > 0) {
                $marketName = $order->productOrders[0]->product->market->name;
                $url = url($order->productOrders[0]->product->market->getFirstMediaUrl('image', 'thumb'));

//               // Notification::send($order->productOrders[0]->product->market->users, new NewOrder($order));

            }
            if ($input['package']) {
                $marketName = $order->packageOrders[0]->package->product->market->name;
                $url = url($order->packageOrders[0]->package->product->market->getFirstMediaUrl('image', 'thumb'));
                // Notification::send($order->packageOrders[0]->package->product->market->users, new NewOrder($order));
            }

            if ($input['order_request']) {
                $marketName = $order->productOrderRequestOrder->temporaryOrderRequest->orderRequest->market->name;
                $url = url($order->productOrderRequestOrder->temporaryOrderRequest->orderRequest->market->getFirstMediaUrl('image', 'thumb'));

                //    Notification::send($order->productOrderRequestOrder->temporaryOrderRequest->orderRequest->market->users, new NewOrder($order));
            }

//            if (!$parentId) {

                try {
                    $currentUser = User::findOrFail($request->get('user_id'));
                    if ($marketID) {
                        $market = Market::with('users')
                            ->where('id', $marketID)
                            ->first();


                        if (count($market->users) > 0) {
                            foreach ($market->users as $user) {
                                info($user->device_token);
                                info($user->name);
                                $userFcmToken = $user->device_token;
                                $userOrder = Order::findOrFail($order->id);
                                $attributes['title'] = $userOrder->type == Order::ORDER_REQUEST_TYPE ? 'Manual order placed successfully' : 'Order placed successfully';
                                $attributes['redirection_type'] = Order::NEW_ORDER_REDIRECTION_TYPE;
                                $attributes['message'] = 'You have received a new order from ' . $currentUser->name . ' with OrderID ' . $order->id . 'for ' . $market->name;
                                $attributes['image'] = $url;
                                $attributes['data'] = null;
                                $attributes['redirection_id'] = $order->id;
                                $attributes['type'] = $order->type;

                                Notification::route('fcm', $userFcmToken)
                                    ->notify(new NewOrder($attributes));
                            }
                        }
                    }


                } catch (\Exception $e) {

                }

                try {
                    $userFcmToken = $order->user->device_token;
                    // select only order detail for fcm notification
                    $userOrder = Order::findOrFail($order->id);
                    $attributes['title'] = $userOrder->type == Order::ORDER_REQUEST_TYPE ? 'Manual order placed successfully' : 'Order placed successfully';
                    $attributes['redirection_type'] = Order::NEW_ORDER_REDIRECTION_TYPE;
                    $attributes['message'] = 'Your new Order from ' . $marketName . ' is placed with OrderID' . $order->id;
                    $attributes['image'] = $url;
                    $attributes['data'] = $userOrder->toArray();
                    $attributes['type'] = $userOrder->type;
                    $attributes['redirection_id'] = $userOrder->id;
                    Notification::route('fcm', $userFcmToken)
                        ->notify(new NewOrder($attributes));

                } catch (\Exception $e) {

                }

                try {

                    $attributes['email'] = $order->user->email;
                    $attributes['order_id'] = $order->id;
                    Mail::send(new NewOrderMail($attributes));

                } catch (\Exception $e) {

                }
//            }


            if ($request->get('addons')) {
                $ordeAddons = $request->get('addons');
                foreach ($ordeAddons as $ordeAddon) {
                    $order->order_addons()->attach($order->id, ['order_id' => $order->id, 'product_addon_id' => $ordeAddon]);
                }
            }

            DB::commit();

            info($order);

            $response = ['order' => $order, 'razorpayOrderId' => null];

        } catch (ValidatorException $e) {
            return $this->sendError($e->getMessage());
        }

        if (!$parentId) {
            if ($request->get('coupon_code')) {
                $coupon = Coupon::where('code', $request->get('coupon_code'))->first();

                $order->is_coupon_used = true;
                $order->coupon_code = $request->get('coupon_code');
                $order->coupon_discount_amount = $coupon->discount;
                $order->save();

                if (!$coupon) {
                    return $this->sendError('Coupon code is not found');
                }

                $orderCoupon = new OrderCoupon();

                $orderCoupon->user_id = $request->get('user_id');
                $orderCoupon->order_id = $order->id;
                $orderCoupon->coupon_id = $coupon->id;
                $orderCoupon->coupon_redeemed_amount = $request->get('coupon_redeemed_amount');

                $isCouponAlreadyUsed = OrderCoupon::where('coupon_id', $coupon->id)
                    ->orderBy('id', 'desc')
                    ->first();

                if (!$isCouponAlreadyUsed) {
                    $orderCoupon->number_of_usage = 1;
                } else {
                    $number_of_usage = $isCouponAlreadyUsed->number_of_usage;
                    $orderCoupon->number_of_usage = $number_of_usage + 1;
                }

                $orderCoupon->save();

            }

        }

        info("MARKETID : " . $marketId);
        info(Order::findOrFail($order->id));

        return $response;
    }

    /**
     * Update the specified Order in storage.
     *
     * @param int $id
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, Request $request)
    {
        $oldOrder = $this->orderRepository->findWithoutFail($id);
        if (empty($oldOrder)) {
            return $this->sendError('Order not found');
        }
        $oldStatus = $oldOrder->payment->status;
        $input = $request->all();

        try {
            $order = $this->orderRepository->update($input, $id);
            if (isset($input['order_status_id']) && $input['order_status_id'] == 5 && !empty($order)) {
                $this->paymentRepository->update(['status' => 'Paid'], $order['payment_id']);
            }
            event(new OrderChangedEvent($oldStatus, $order));

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

        } catch (ValidatorException $e) {
            return $this->sendError($e->getMessage());
        }

        event(new \App\Events\NewOrderEvent());

        return $this->sendResponse($order->toArray(), __('lang.saved_successfully', ['operator' => __('lang.order')]));
    }

    private function walletTransaction(Request $request)
    {
        $input = $request->all();
        $deliveryFee = $request->get('delivery_fee') ?: 0;


        DB::beginTransaction();

        $userWallet = UserWallet::where('user_id', $request->get('user_id'))->first();

        if (!$userWallet) {
            return $this->sendResponse(null, 'User does not having a wallet');
        }
        if ($userWallet->balance < $input['total_amount']) {
            return $this->sendResponse(null, 'Wallet does not have sufficient amount');
        }
        $balance = $userWallet->balance - $input['total_amount'];
        $userWallet->balance = $balance;
        $userWallet->save();

        $owletoCommissionAmount = 0;
        $totalAmount = $input['total_amount'];
        $marketId = null;


        $productCount = count($input['products']);

        if ($productCount > 0) {
            foreach ($input['products'] as $productOrder) {
                $product = Product::find($productOrder['product_id']);
                $productQuantity = $productOrder['quantity'];
                $owletoCommissionAmount = $owletoCommissionAmount + ($product->owleto_commission_amount * $productQuantity);
                $marketId = $product->market_id;
            }
            $type = Order::PRODUCT_TYPE;
        }

        if ($input['package']) {
            $package = SubscriptionPackage::where('id', $input['package']['package_id'])->first();
            $product = Product::where('id', $package->product_id)->first();
            $owletoCommissionAmount = $owletoCommissionAmount + $product->owleto_commission_amount;
            $marketId = $product->market_id;
            $type = Order::PACKAGE_TYPE;
        }

        if ($input['order_request']) {

            $tempOrderRequest = TemporaryOrderRequest::where('id', $input['order_request']['temporary_order_request_id'])->first();
            $orderRequest = OrderRequest::where('id', $tempOrderRequest->order_request_id)->first();
            $type = Order::ORDER_REQUEST_TYPE;
            $marketId = $orderRequest->market_id;
            $market = Market::find($marketId);
            $owletoCommissionAmount = $market->order_request_commission_amount ? $totalAmount * $market->order_request_commission_amount / 100 : 0;

        }

        if ($input['pickup_delivery_order']) {
            $owletoCommissionAmount = $totalAmount;
            $type = Order::PICKUP_DELIVERY_ORDER_TYPE;
            $marketId = null;
        }

        try {
            $deliveryAddress = DeliveryAddress::find($request->get('delivery_address_id'));

            $order = $this->orderRepository->create([
                'user_id' => $request->get('user_id'),
                'order_status_id' => $request->get('order_status_id'),
                'tax' => $request->get('tax'),
                'delivery_address_id' => $request->get('delivery_address_id'),
                'delivery_type_id' => $request->get('delivery_type_id'),
                'payment_method_id' => $request->get('payment_method_id'),
                'delivery_fee' => $request->get('delivery_fee'),
                'hint' => $request->get('hint'),
                'total_amount' => $totalAmount,
                'sub_total' => $request->get('sub_total'),
                'market_balance' => $totalAmount - $owletoCommissionAmount,
                'owleto_commission_amount' => round($owletoCommissionAmount, 2),
                'market_id' => $marketId,
                'type' => $type,
                'amount_from_wallet' => $totalAmount,
                'is_wallet_used' => true,
                'distance' => $request->get('distance'),
                'sector_id' => $request->get('sector_id'),
                'latitude' => $deliveryAddress->latitude,
                'longitude' => $deliveryAddress->longitude,
                'address_data' => json_encode($deliveryAddress),
            ]);


            $userWalletTransaction = new UserWalletTransaction();
            $userWalletTransaction->user_id = $request->get('user_id');
            $userWalletTransaction->order_id = $order->id;
            $userWalletTransaction->description = 'Amount taken from user wallet for order' . $order->id;
            $userWalletTransaction->type = UserWalletTransaction::TYPE_DEBIT;
            $userWalletTransaction->amount = $totalAmount;
            $userWalletTransaction->save();

            if ($productCount > 0) {
                foreach ($input['products'] as $productOrder) {
                    $product = Product::find($productOrder['product_id']);
                    $productOrder['commission_percentage'] = $product->owleto_commission_percentage;
                    $productOrder['commission_amount'] = round($product->owleto_commission_amount, 2);
                    $productOrder['order_id'] = $order->id;

                    $productsOrder = $this->productOrderRepository->create($productOrder);

                    if (count($productOrder['addons']) > 0) {
                        foreach ($productOrder['addons'] as $productOrderAddon) {

                            $productAddon = ProductAddon::where('id', $productOrderAddon)->first();

                            $orderAddon = new OrderAddon();
                            $orderAddon->product_order_id = $productsOrder->id;
                            $orderAddon->product_addon_id = $productOrderAddon;
                            $orderAddon->price = $productAddon->price;
                            $orderAddon->save();
                        }
                    }
                }
            }

            if ($input['package']) {

                $package = SubscriptionPackage::where('id', $input['package']['package_id'])->first();
                $packageDays = PackageDay::where('package_id', $package->id)->get();

                $packageDeliveryTimes = PackageDeliveryTime::where('package_id', $package->id)->get();

                $pricePerDelivery = $package->price / $package->days;

                $product = Product::where('id', $package->product_id)->first();

                $marketBalace = $pricePerDelivery - $product->owleto_commission_amount;

                $productOrder['commission_percentage'] = $product->owleto_commission_percentage;
                $productOrder['commission_amount'] = round($product->owleto_commission_amount, 2);
                $productOrder['order_id'] = $order->id;
                $productOrder['package_id'] = $input['package']['package_id'];
                $productOrder['package_price'] = $input['package']['price'];
                $productOrder['price_per_delivery'] = $pricePerDelivery;
                $productOrder['market_balance'] = $marketBalace;

                $packageDates = [];
                foreach ($packageDays as $packageDay) {
                    $day = Day::where('id', $packageDay->day_id)->first();
                    array_push($packageDates, $day->day_of_week);
                }
                $now = Carbon::now();
                $collection = collect($packageDates);

                $allDays = [];

                $i = 0;

                while ($i < $package->days) {
                    $todayName = $now->dayOfWeek;
                    $date = $now->getTimestamp();
                    if (Carbon::today()->format('d/m/Y') == date('d/m/Y', $date)) {
                        $hour = Carbon::now()->hour;
                        foreach ($packageDeliveryTimes as $packageDeliveryTime) {
                            if ($packageDeliveryTime->delivery_time_id == 1) {
                                if ($hour < PackageOrder::MORNING_TIME_LIMIT) {
                                    if ($collection->contains($todayName)) {
                                        array_push($allDays, $date);
                                        $i++;
                                    }
                                }
                            }
                            if ($packageDeliveryTime->delivery_time_id == 2) {
                                if ($hour < PackageOrder::EVENING_TIME_LIMIT) {
                                    if ($collection->contains($todayName)) {
                                        array_push($allDays, $date);
                                        $i++;
                                    }
                                }
                            }
                        }
                        $now = $now->addDays(1);
                    } else {
                        if ($collection->contains($todayName)) {
                            array_push($allDays, $date);
                            $i++;
                        }
                        $now = $now->addDays(1);
                    }
                }

                $i = 0;
                $j = 0;
                while ($i <= count($allDays)) {
                    $deliveryAddress = DeliveryAddress::find($request->get('delivery_address_id'));
                    foreach ($packageDeliveryTimes as $packageDeliveryTime) {
                        $dateTime = date('m/d/Y H:i:s', $allDays[$i]);
                        $date = date('d/m/Y', $allDays[$i]);

                        if ($date == date('d/m/Y')) {
                            $hour = Carbon::now()->hour;
                            if ($packageDeliveryTime->delivery_time_id == 1) {
                                if ($hour < PackageOrder::MORNING_TIME_LIMIT) {
                                    $productOrder['date'] = Carbon::parse($dateTime);
                                    $productOrder['created_at'] = Carbon::now()->timezone('Asia/Kolkata');
                                    $productOrder['delivery_time_id'] = $packageDeliveryTime->delivery_time_id;
                                    $productOrder['order_status_id'] = $request->get('order_status_id');
                                    $productOrder['market_id'] = $marketId;
                                    $productOrder['delivery_address_id'] = $request->get('delivery_address_id');
                                    $productOrder['payment_method_id'] = $request->get('payment_method_id');
                                    $productOrder['tax'] = $request->get('tax');
                                    $productOrder['user_id'] = $request->get('user_id');
                                    $productOrder['distance'] = $request->get('distance');
                                    $productOrder['sector_id'] = $request->get('sector_id');
                                    $productOrder['latitude'] = $deliveryAddress->latitude;
                                    $productOrder['longitude'] = $deliveryAddress->longitude;
                                    $productOrder['address_data'] = json_encode($deliveryAddress);
                                    $this->packageOrderRepository->create($productOrder);
                                    $j++;
                                    if ($j == count($allDays)) {
                                        $i = $j;
                                        break;
                                    }
                                }
                            }

                            if ($packageDeliveryTime->delivery_time_id == 2) {
                                if ($hour < PackageOrder::EVENING_TIME_LIMIT) {
                                    $productOrder['date'] = Carbon::parse($dateTime);
                                    $productOrder['created_at'] = Carbon::now()->timezone('Asia/Kolkata');
                                    $productOrder['delivery_time_id'] = $packageDeliveryTime->delivery_time_id;
                                    $productOrder['order_status_id'] = $request->get('order_status_id');
                                    $productOrder['market_id'] = $marketId;
                                    $productOrder['delivery_address_id'] = $request->get('delivery_address_id');
                                    $productOrder['payment_method_id'] = $request->get('payment_method_id');
                                    $productOrder['tax'] = $request->get('tax');
                                    $productOrder['user_id'] = $request->get('user_id');
                                    $productOrder['distance'] = $request->get('distance');
                                    $productOrder['sector_id'] = $request->get('sector_id');
                                    $productOrder['latitude'] = $deliveryAddress->latitude;
                                    $productOrder['longitude'] = $deliveryAddress->longitude;
                                    $productOrder['address_data'] = json_encode($deliveryAddress);
                                    $this->packageOrderRepository->create($productOrder);

                                    $j++;
                                    if ($j == count($allDays)) {
                                        $i = $j;
                                        break;
                                    }
                                }
                            }

                        } else {
                            $productOrder['date'] = Carbon::parse($dateTime);
                            $productOrder['created_at'] = Carbon::now()->timezone('Asia/Kolkata');
                            $productOrder['delivery_time_id'] = $packageDeliveryTime->delivery_time_id;
                            $productOrder['order_status_id'] = $request->get('order_status_id');
                            $productOrder['market_id'] = $marketId;
                            $productOrder['delivery_address_id'] = $request->get('delivery_address_id');
                            $productOrder['payment_method_id'] = $request->get('payment_method_id');
                            $productOrder['tax'] = $request->get('tax');
                            $productOrder['user_id'] = $request->get('user_id');
                            $productOrder['distance'] = $request->get('distance');
                            $productOrder['sector_id'] = $request->get('sector_id');
                            $productOrder['latitude'] = $deliveryAddress->latitude;
                            $productOrder['longitude'] = $deliveryAddress->longitude;
                            $productOrder['address_data'] = json_encode($deliveryAddress);
                            $this->packageOrderRepository->create($productOrder);
                            $j++;
                            if ($j == count($allDays)) {
                                $i = $j;
                                break;
                            }
                        }
                    }
                    $i++;
                }
            }

            if ($input['order_request']) {

                $tempOrderRequestId = $input['order_request']['temporary_order_request_id'];
                $productOrderRequestOrder['temporary_order_request_id'] = $tempOrderRequestId;
                $productOrderRequestOrder['price'] = $input['order_request']['price'];
                $productOrderRequestOrder['order_id'] = $order->id;
                $this->productOrderRequestOrderRepository->create($productOrderRequestOrder);

                $tempOrderRequest = TemporaryOrderRequest::find($tempOrderRequestId);
                $tempOrderRequest->status = OrderRequest::STATUS_NOTIFICATION_SEND;
                $tempOrderRequest->save();

                $OrderRequest = OrderRequest::find($tempOrderRequest->order_request_id);
                $OrderRequest->status = OrderRequest::STATUS_NOTIFICATION_SEND;
                $OrderRequest->save();
            }

            if ($input['pickup_delivery_order']) {

                $pickUpDeliveryOrderRequestId = $input['pickup_delivery_order']['pick_up_delivery_order_request_id'];
                $pickUpDeliveryOrder['pick_up_delivery_order_request_id'] = $pickUpDeliveryOrderRequestId;
                $pickUpDeliveryOrder['price'] = $input['pickup_delivery_order']['price'];
                $pickUpDeliveryOrder['order_id'] = $order->id;
                $this->pickUpDeliveryOrderRepository->create($pickUpDeliveryOrder);

                $pickUpDeliveryOrderRequest = PickUpDeliveryOrderRequest::where('id', $pickUpDeliveryOrderRequestId)->first();
                $pickUpDeliveryOrderRequest->status = PickUpDeliveryOrderRequest::STATUS_ORDER_CREATED;
                $pickUpDeliveryOrderRequest->save();
            }

            if ($productCount > 0) {
                $marketID = $order->productOrders[0]->product->market->id;
            }
            if ($input['package']) {
                $marketID = $order->packageOrders[0]->package->product->market->id;
            }
            if ($input['order_request']) {
                $marketID = $marketId;
            }

            if ($input['pickup_delivery_order']) {
                $marketID = null;
            }

            $payment = $this->paymentRepository->create([
                "user_id" => $input['user_id'],
                "order_id" => $order->id,
                "description" => trans("lang.payment_order_waiting"),
                "price" => $totalAmount,
                "status" => 'Waiting for Client',
                "payment_method_id" => $request->get('payment_method_id'),
                "method" => PaymentMethod::WALLET,
            ]);

            $this->orderRepository->update(['payment_id' => $payment->id, 'market_id' => $marketID], $order->id);

            $this->cartRepository->deleteWhere(['user_id' => $order->user_id]);

            if ($productCount > 0) {
                $marketName = $order->productOrders[0]->product->market->name;
                $url = url($order->productOrders[0]->product->market->getFirstMediaUrl('image', 'thumb'));

//               // Notification::send($order->productOrders[0]->product->market->users, new NewOrder($order));

            }
            if ($input['package']) {
                $marketName = $order->packageOrders[0]->package->product->market->name;
                $url = url($order->packageOrders[0]->package->product->market->getFirstMediaUrl('image', 'thumb'));
                // Notification::send($order->packageOrders[0]->package->product->market->users, new NewOrder($order));
            }

            if ($input['order_request']) {
                $marketName = $order->productOrderRequestOrder->temporaryOrderRequest->orderRequest->market->name;
                $url = url($order->productOrderRequestOrder->temporaryOrderRequest->orderRequest->market->getFirstMediaUrl('image', 'thumb'));

                //    Notification::send($order->productOrderRequestOrder->temporaryOrderRequest->orderRequest->market->users, new NewOrder($order));
            }

            try {
                if ($marketID) {
                    $market = Market::with('users')
                        ->where('id', $marketID)
                        ->first();
                    if (count($market->users) > 0) {
                        foreach ($market->users as $user) {
                            $userFcmToken[] = $user->device_token;
                            $attributes['title'] = 'Owleto new order';
                            $attributes['redirection_type'] = Order::NEW_ORDER_REDIRECTION_TYPE;
                            $attributes['message'] = 'You have received a new order from ' . $order->user->name . ' with OrderID ' . $order->id . ' for ' . $market->name;
                            $attributes['image'] = $url;
                            $attributes['data'] = null;
                            $attributes['redirection_id'] = $order->id;
                            $attributes['type'] = $order->type;

                            Notification::route('fcm', $userFcmToken)
                                ->notify(new NewOrder($attributes));
                        }
                    }
                }


            } catch (\Exception $e) {

            }

            try {
                $userFcmToken = $order->user->device_token;
                $userOrder = Order::findOrFail($order->id);
                $attributes['title'] = $attributes['title'] = $userOrder->type == Order::ORDER_REQUEST_TYPE ? 'Manual order placed successfully' : 'Order placed successfully';
                $attributes['redirection_type'] = Order::NEW_ORDER_REDIRECTION_TYPE;
                $attributes['message'] = 'Your new Order from ' . $marketName . ' is placed with OrderID' . $order->id;
                $attributes['image'] = $url;
                $attributes['data'] = $userOrder->toArray();
                $attributes['type'] = $userOrder->type;
                $attributes['redirection_id'] = $userOrder->id;

                Notification::route('fcm', $userFcmToken)
                    ->notify(new NewOrder($attributes));

            } catch (\Exception $e) {

            }

            try {

                $attributes['email'] = $order->user->email;
                $attributes['order_id'] = $order->id;

                Mail::send(new NewOrderMail($attributes));

            } catch (\Exception $e) {

            }

            if ($request->addons) {
                $ordeAddons = $request->addons;
                foreach ($ordeAddons as $ordeAddon) {
                    $order->order_addons()->attach($order->id, ['order_id' => $order->id, 'product_addon_id' => $ordeAddon]);
                }
            }

            $response = ['order' => $order, 'razorpayOrderId' => null];

        } catch (ValidatorException $e) {
            return $this->sendError($e->getMessage());
        }

        if ($request->coupon_code) {
            $coupon = Coupon::where('code', $request->coupon_code)->first();

            $order->is_coupon_used = true;
            $order->coupon_code = $request->coupon_code;
            $order->coupon_discount_amount = $coupon->discount;
            $order->save();

            if (!$coupon) {
                return $this->sendError('Coupon code is not found');
            }

            $orderCoupon = new OrderCoupon();

            $orderCoupon->user_id = $request->user_id;
            $orderCoupon->order_id = $order->id;
            $orderCoupon->coupon_id = $coupon->id;
            $orderCoupon->coupon_redeemed_amount = $request->coupon_redeemed_amount;

            $isCouponAlreadyUsed = OrderCoupon::where('coupon_id', $coupon->id)
                ->orderBy('id', 'desc')
                ->first();

            if (!$isCouponAlreadyUsed) {
                $orderCoupon->number_of_usage = 1;
            } else {
                $number_of_usage = $isCouponAlreadyUsed->number_of_usage;
                $orderCoupon->number_of_usage = $number_of_usage + 1;
            }

            $orderCoupon->save();

        }

        DB::commit();

        event(new \App\Events\NewOrderEvent());

        return $this->sendResponse($response, __('lang.saved_successfully', ['operator' => __('lang.order')]));
    }

    public function productFirstOrder(Request $request)
    {

        $data = [];

        $productOrders = $request->products;
        foreach ($productOrders as $key => $productOrder) {
            $product = Product::find($productOrder['product_id']);
            $IsFirstOrder = ProductOrder::where('product_id', $productOrder['product_id'])
                ->whereDate('created_at', Carbon::today())->first();
            if (!$IsFirstOrder) {
                if ($productOrder['quantity'] >= $product->minimum_orders) {

                    $is_minimum_order = true;
                } else {
                    $is_minimum_order = false;

                }
            } else {
                $is_minimum_order = true;
            }

            array_push($data, ['id' => $productOrder['product_id'], 'is_minimum_order' => $is_minimum_order]);

        }

        return $this->sendResponse($data, 'Minimum order retried successfully');
    }

}

