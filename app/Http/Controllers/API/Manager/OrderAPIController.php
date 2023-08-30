<?php
/**
 * File name: MarketAPIController.php
 * Last modified: 2020.08.13 at 13:43:34
 * Author: Pixbit Solutions - https://pixbitsolutions.com
 * Copyright (c) 2020
 *
 */

namespace App\Http\Controllers\API\Manager;

use App\Http\Controllers\Controller;
use App\Models\DeliveryType;
use App\Models\Driver;
use App\Models\DriversCurrentLocation;
use App\Models\Field;
use App\Models\Market;
use App\Models\Order;
use App\Models\PackageOrder;
use App\Models\PaymentMethod;
use App\Models\SubscriptionPackage;
use App\Models\User;
use App\Models\UserWallet;
use App\Models\UserWalletTransaction;
use App\Notifications\DriverAssignedNotification;
use App\Notifications\DriverAssignedNotificationToUser;
use App\Notifications\OrderCancelNotification;
use App\Repositories\MarketRepository;
use App\Repositories\OrderRepository;
use App\Repositories\PackageOrderRepository;
use Carbon\Carbon;
use Exception;
use Flash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Kreait\Firebase\Contract\Database;

class OrderAPIController extends Controller
{
    /** @var  OrderRepository */
    private $orderRepository;

    /** @var  MarketRepository */
    private $marketRepository;

    /** @var  PackageOrderRepository */
    private $packageOrderRepository;


    public function __construct(OrderRepository  $orderRepo, PackageOrderRepository $packageOrderRepository,
                                MarketRepository $marketRepository, Database $database)
    {
        parent::__construct();
        $this->orderRepository = $orderRepo;
        $this->marketRepository = $marketRepository;
        $this->packageOrderRepository = $packageOrderRepository;
        $this->database = $database;
        $this->table = 'user_locations';
    }

    public function index(Request $request)
    {
        $userMarketIds = Market::whereHas('users', function ($query) {
            $query->where('user_id', Auth::id());
        })->pluck('id');


        $orders = Order::with(['user', 'productOrders.product', 'productOrders.options', 'orderStatus', 'deliveryType',
            'deliveryAddress', 'payment', 'packageOrders', 'packageOrders.orderStatus', 'packageOrders.package.product',
            'productOrderRequestOrder.temporaryOrderRequest'])
            ->where(function ($query) use ($userMarketIds) {
                $query->whereIn('payment_method_id', [PaymentMethod::PAYMENT_METHOD_RAZORPAY, PaymentMethod::PAYMENT_METHOD_WALLET])
                    ->where('payment_status', 'SUCCESS')
                    ->whereIn('market_id', $userMarketIds);
            })
            ->orWhere(function ($query) use ($userMarketIds) {
                $query->whereIn('payment_method_id', [PaymentMethod::PAYMENT_METHOD_COD, PaymentMethod::PAYMENT_METHOD_WALLET])
                    ->whereIn('payment_status', ['PENDING', 'SUCCESS'])
                    ->whereIn('market_id', $userMarketIds);
            })
            ->orderBy('created_at', 'desc');

        if ($request->order_id) {
            $orders = $orders->where('id', $request->order_id);
        }

        if ($request->market_id) {
            $orders = $orders->where('market_id', $request->market_id);
        }

        $orders = $orders->get();

        $managerOrders = [];

        foreach ($orders as $order) {
            $addons = [];
            $orderItems = [];

            if ($order->type == Order::PRODUCT_TYPE) {
                foreach ($order->productOrders as $productOrder) {

                    $addons = $productOrder->order_addons;
                    array_push($orderItems, [
                        'order_item_id' => $productOrder->id,
                        'quantity' => $productOrder->quantity,
                        'price' => $productOrder->price,
                        'order_item_name' => $productOrder->product ? $productOrder->product->name : null,
                        'product_id' => $productOrder->product_id,
                        'image' => isset($productOrder->product->media)?$productOrder->product->media:'',
                        'package_price' => null,
                        'addons' => $addons,
                        'order_image_url' => null,
                        'bill_image_url' => null,
                        'order_request_text' => null,
                    ]);
                }

                array_push($managerOrders, [
                    'id' => $order->id,
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'order_status_id' => $order->order_status_id,
                    'type' => $order->type,
                    'distance' => $order->distance,
                    'delivery_address_id' => $order->delivery_address_id,
                    'delivery_type_id' => $order->delivery_type_id,
                    'payment_method_id' => $order->payment_method_id,
                    'total_amount' => $order->total_amount,
                    'sub_total' => $order->sub_total,
                    'market_id' => $order->market_id,
                    'created_at' => $order->created_at,
                    'delivery_fee' => $order->delivery_fee,
                    'tax' => $order->tax,
                    'is_delivered' => null,
                    'is_order_approved' => $order->is_order_approved,
                    'order_items' => $orderItems,
                    'user' => $order->user,
                    'market' => $order->market,
                    'order_status' => $order->orderStatus,
                    'delivery_address' => $order->deliveryAddress,
                    'payment_method' => $order->paymentMethod,
                    'delivery_type' => $order->deliveryType,
                    'coupon_discount_amount' => $order->coupon_discount_amount,
                    'amount_from_wallet' => $order->amount_from_wallet,
                    'package' => null
                ]);
            }
            if ($order->type == Order::PACKAGE_TYPE) {

                $isPackageAssignedToDriver = $order->packageOrders()
                    ->whereNotNull('driver_id')
                    ->first();

                array_push($managerOrders,
                    ['id' => $order->id,
                        'order_id' => $order->id,
                        'user_id' => $order->user_id,
                        'order_status_id' => $order->order_status_id,
                        'type' => $order->type,
                        'distance' => $order->distance,
                        'delivery_address_id' => $order->delivery_address_id,
                        'delivery_type_id' => $order->delivery_type_id,
                        'payment_method_id' => $order->payment_method_id,
                        'total_amount' => $order->total_amount,
                        'sub_total' => $order->sub_total,
                        'market_id' => $order->market_id,
                        'created_at' => $order->created_at,
                        'delivery_fee' => $order->delivery_fee,
                        'tax' => $order->tax,
                        'is_delivered' => null,
                        'is_order_approved' => $order->is_order_approved,
                        'order_items' => null,
                        'user' => $order->user,
                        'market' => $order->market,
                        'order_status' => $order->orderStatus,
                        'delivery_address' => $order->deliveryAddress,
                        'payment_method' => $order->paymentMethod,
                        'delivery_type' => $order->deliveryType,
                        'coupon_discount_amount' => $order->coupon_discount_amount,
                        'amount_from_wallet' => $order->amount_from_wallet,
                        'package' => $order->packageOrders[0]->package,
                        'is_cancelable' => $isPackageAssignedToDriver ? false : true,
                    ]);
            }

            if ($order->type == Order::ORDER_REQUEST_TYPE) {

                array_push($orderItems, [
                    'order_item_id' => $order->productOrderRequestOrder->id,
                    'quantity' => null,
                    'price' => $order->productOrderRequestOrder->price,
                    'order_item_name' => null,
                    'product_id' => null,
                    'image' => null,
                    'package_price' => null,
                    'addons' => $addons,
                    'order_image_url' => $order->productOrderRequestOrder->temporaryOrderRequest->orderRequest->image_url,
                    'bill_image_url' => $order->productOrderRequestOrder->temporaryOrderRequest->bill_image_url,
                    'order_request_text' => $order->productOrderRequestOrder->temporaryOrderRequest->orderRequest->order_text,
                ]);

                array_push($managerOrders, [
                    'id' => $order->id,
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'order_status_id' => $order->order_status_id,
                    'type' => $order->type,
                    'distance' => $order->distance,
                    'delivery_address_id' => $order->delivery_address_id,
                    'delivery_type_id' => $order->delivery_type_id,
                    'payment_method_id' => $order->payment_method_id,
                    'total_amount' => $order->total_amount,
                    'sub_total' => $order->sub_total,
                    'market_id' => $order->market_id,
                    'created_at' => $order->created_at,
                    'delivery_fee' => $order->delivery_fee,
                    'tax' => $order->tax,
                    'is_delivered' => null,
                    'is_order_approved' => $order->is_order_approved,
                    'order_items' => $orderItems,
                    'user' => $order->user,
                    'market' => $order->market,
                    'order_status' => $order->orderStatus,
                    'delivery_address' => $order->deliveryAddress,
                    'payment_method' => $order->paymentMethod,
                    'delivery_type' => $order->deliveryType,
                    'coupon_discount_amount' => $order->coupon_discount_amount,
                    'amount_from_wallet' => $order->amount_from_wallet,
                    'package' => null
                ]);
            }
        }


        return $this->sendResponse($managerOrders, 'Orders retrieved successfully');
    }

    public function show(Request $request, $id)
    {
        $order = Order::with(['user', 'productOrders.product', 'productOrders.options', 'orderStatus', 'deliveryType',
            'deliveryAddress', 'payment', 'packageOrders', 'packageOrders.orderStatus',
            'productOrderRequestOrder.temporaryOrderRequest'])
            ->where('id', $id)
            ->first();

        if (empty($order)) {
            return $this->sendError('Order not found');
        }

        $managerOrders = [];
        $addons = [];
        $orderItems = [];

        if ($order->type == Order::PRODUCT_TYPE) {
            foreach ($order->productOrders as $productOrder) {

                $addons = $productOrder->order_addons;
                array_push($orderItems, [
                    'order_item_id' => $productOrder->id,
                    'quantity' => $productOrder->quantity,
                    'price' => $productOrder->price,
                    'order_item_name' => $productOrder->product->name,
                    'product_id' => $productOrder->product_id,
                    'image' => $productOrder->product->media,
                    'package_price' => null,
                    'addons' => $addons,
                    'order_image_url' => null,
                    'bill_image_url' => null,
                    'order_request_text' => null,
                ]);
            }

            $managerOrders = ['id' => $order->id,
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'order_status_id' => $order->order_status_id,
                'type' => $order->type,
                'distance' => $order->distance,
                'delivery_address_id' => $order->delivery_address_id,
                'delivery_type_id' => $order->delivery_type_id,
                'payment_method_id' => $order->payment_method_id,
                'total_amount' => $order->total_amount,
                'sub_total' => $order->sub_total,
                'market_id' => $order->market_id,
                'created_at' => $order->created_at,
                'delivery_fee' => $order->delivery_fee,
                'tax' => $order->tax,
                'is_delivered' => null,
                'is_order_approved' => $order->is_order_approved,
                'order_items' => $orderItems,
                'user' => $order->user,
                'market' => $order->market,
                'order_status' => $order->orderStatus,
                'delivery_address' => $order->deliveryAddress,
                'payment_method' => $order->paymentMethod,
                'delivery_type' => $order->deliveryType,
                'coupon_discount_amount' => $order->coupon_discount_amount,
                'amount_from_wallet' => $order->amount_from_wallet,
            ];
        }

        if ($order->type == Order::PACKAGE_TYPE) {
            foreach ($order->packageOrders as $packageOrder) {

                $addons = null;
                array_push($orderItems, [
                    'order_item_id' => $packageOrder->id,
                    'quantity' => null,
                    'price' => $packageOrder->price_per_delivery,
                    'order_item_name' => $packageOrder->package->product->name,
                    'product_id' => $packageOrder->package->product->id,
                    'image' => $packageOrder->package->product->media,
                    'package_price' => $packageOrder->package_price,
                    'addons' => $addons,
                    'order_image_url' => null,
                    'bill_image_url' => null,
                    'order_request_text' => null,
                ]);
            }

            $managerOrders = ['id' => $order->id,
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'order_status_id' => $order->order_status_id,
                'type' => $order->type,
                'distance' => $order->distance,
                'delivery_address_id' => $order->delivery_address_id,
                'delivery_type_id' => $order->delivery_type_id,
                'payment_method_id' => $order->payment_method_id,
                'total_amount' => $order->total_amount,
                'sub_total' => $order->sub_total,
                'market_id' => $order->market_id,
                'created_at' => $order->created_at,
                'delivery_fee' => $order->delivery_fee,
                'tax' => $order->tax,
                'is_delivered' => null,
                'is_order_approved' => $order->is_order_approved,
                'order_items' => $orderItems,
                'user' => $order->user,
                'market' => $order->market,
                'order_status' => $order->orderStatus,
                'delivery_address' => $order->deliveryAddress,
                'payment_method' => $order->paymentMethod,
                'delivery_type' => $order->deliveryType,
                'coupon_discount_amount' => $order->coupon_discount_amount,
                'amount_from_wallet' => $order->amount_from_wallet,
            ];
        }

        if ($order->type == Order::ORDER_REQUEST_TYPE) {

            array_push($orderItems, [
                'order_item_id' => $order->productOrderRequestOrder->id,
                'quantity' => null,
                'price' => $order->productOrderRequestOrder->price,
                'order_item_name' => null,
                'product_id' => null,
                'image' => null,
                'package_price' => null,
                'addons' => $addons,
                'order_image_url' => $order->productOrderRequestOrder->temporaryOrderRequest->orderRequest->image_url,
                'bill_image_url' => $order->productOrderRequestOrder->temporaryOrderRequest->bill_image_url,
                'order_request_text' => $order->productOrderRequestOrder->temporaryOrderRequest->orderRequest->order_text,
            ]);
            $managerOrders = [
                'id' => $order->id,
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'order_status_id' => $order->order_status_id,
                'type' => $order->type,
                'distance' => $order->distance,
                'delivery_address_id' => $order->delivery_address_id,
                'delivery_type_id' => $order->delivery_type_id,
                'payment_method_id' => $order->payment_method_id,
                'total_amount' => $order->total_amount,
                'sub_total' => $order->sub_total,
                'market_id' => $order->market_id,
                'created_at' => $order->created_at,
                'delivery_fee' => $order->delivery_fee,
                'tax' => $order->tax,
                'is_delivered' => null,
                'is_order_approved' => $order->is_order_approved,
                'order_items' => $orderItems,
                'user' => $order->user,
                'market' => $order->market,
                'order_status' => $order->orderStatus,
                'delivery_address' => $order->deliveryAddress,
                'payment_method' => $order->paymentMethod,
                'delivery_type' => $order->deliveryType,
                'coupon_discount_amount' => $order->coupon_discount_amount,
                'amount_from_wallet' => $order->amount_from_wallet,
            ];
        }

        return $this->sendResponse($managerOrders, 'Order retrieved successfully');
    }

    public function cancelOrder(Request $request)
    {
        $orderId = $request->order_id;

        $order = Order::find($orderId);

        if (!$order) {
            return $this->sendError('No order found', 202);
        }

        if ($order->type == Order::PACKAGE_TYPE) {

            $deliveredPackageOrders = PackageOrder::where('order_id', $orderId)
                ->where('delivered', 1)
                ->get();

            if (count($deliveredPackageOrders) > 0) {
                return $this->sendError('You have already purchased some items. This order cannot be canceled', 202);
            }
        }

        $order->order_status_id = 6;
        $order->is_canceled = true;
        $order->save();

        try {
            $userFcmToken[] = $order->user->device_token;
            $userOrder = Order::findOrFail($order->id);

            $attribute['title'] = 'Owleto order cancellation';
            $attribute['redirection_type'] = Order::ORDER_CANCELLED_REDIRECTION_TYPE;
            $attribute['message'] = 'Your order with OrderID ' . $order->id . ' is canceled by the market owner';
            $attribute['data'] = $userOrder->toArray();
            $attribute['redirection_id'] = $order->id;
//            $attribute['type'] = Order::PACKAGE_TYPE;

            Notification::route('fcm', $userFcmToken)
                ->notify(new OrderCancelNotification($attribute));

        } catch (Exception $e) {

        }

        if ($order->payment_method_id == PaymentMethod::PAYMENT_METHOD_RAZORPAY) {

            $userWallet = UserWallet::where('user_id', $order->user_id)->first();

            if ($userWallet) {
                $balance = $userWallet->balance + $order->total_amount;
            } else {
                $balance = $order->total_amount;
                $userWallet = new UserWallet();
            }

            $userWallet->user_id = $order->user_id;
            $userWallet->balance = $balance;
            $userWallet->save();

            $userWalletTransaction = new UserWalletTransaction();
            $userWalletTransaction->package_order_id = null;
            $userWalletTransaction->user_id = $order->user_id;
            $userWalletTransaction->order_id = $order->id;
            $userWalletTransaction->type = UserWalletTransaction::TYPE_CREDIT;
            $userWalletTransaction->amount = $order->total_amount;
            $userWalletTransaction->description = 'Amount to be credited';
            $userWalletTransaction->cancelled_date = Carbon::now();
            $userWalletTransaction->package_id = null;
            $userWalletTransaction->product_id = null;
            $userWalletTransaction->save();

        }
        // manager wallet refund
        if ($order->payment_method_id == PaymentMethod::PAYMENT_METHOD_WALLET) {

            $userWallet = UserWallet::where('user_id', $order->user_id)->first();

            if ($userWallet) {
                $balance = $userWallet->balance + $order->total_amount;
            } else {
                $balance = $order->total_amount;
                $userWallet = new UserWallet();
            }

            $userWallet->user_id = $order->user_id;
            $userWallet->balance = $balance;
            $userWallet->save();

            $userWalletTransaction = new UserWalletTransaction();
            $userWalletTransaction->package_order_id = null;
            $userWalletTransaction->user_id = $order->user_id;
            $userWalletTransaction->order_id = $order->id;
            $userWalletTransaction->type = UserWalletTransaction::TYPE_CREDIT;
            $userWalletTransaction->amount = $order->total_amount;
            $userWalletTransaction->description = 'Amount to be credited';
            $userWalletTransaction->cancelled_date = Carbon::now();
            $userWalletTransaction->package_id = null;
            $userWalletTransaction->product_id = null;
            $userWalletTransaction->save();

        }

        return $this->sendResponse($order, 'order canceled successfully');

    }

    public function cancelPackageItem(Request $request)
    {
        DB::beginTransaction();

        $PackageOrderId = $request->order_id;
        $packageOrder = PackageOrder::find($PackageOrderId);

        if (!$packageOrder) {
            return $this->sendError('No order found', 202);
        }

        if ($packageOrder->canceled == 1) {
            return $this->sendError('Order already canceled', 202);
        }
        $packageOrder->canceled = true;
        $packageOrder->order_status_id = Order::STATUS_CANCELED;
        $packageOrder->save();

        try {
            $userFcmToken[] = $packageOrder->user->device_token;
            $attribute['title'] = 'Owleto order cancellation';
            $attribute['redirection_type'] = Order::ORDER_CANCELLED_REDIRECTION_TYPE;
            $attribute['redirection_id'] = $packageOrder->order_id;
            $attribute['type'] = Order::PACKAGE_TYPE;
            $attribute['message'] = 'Your subscription item with OrderID ' . $packageOrder->id . ' is canceled by the market owner';
            $attribute['data'] = $packageOrder->toArray();

            Notification::route('fcm', $userFcmToken)
                ->notify(new OrderCancelNotification($attribute));

        } catch (Exception $e) {

        }

        if ($packageOrder->payment_method_id == PaymentMethod::PAYMENT_METHOD_RAZORPAY) {

            $package = SubscriptionPackage::where('id', $packageOrder->package_id)->first();

            $userWallet = UserWallet::where('user_id', $packageOrder->user_id)->first();

            if ($userWallet) {
                $balance = $userWallet->balance + $packageOrder->price_per_delivery;
            } else {
                $balance = $packageOrder->price_per_delivery;
                $userWallet = new UserWallet();
            }
            $userWallet->user_id = $packageOrder->user_id;
            $userWallet->balance = $balance;
            $userWallet->save();

            $userWalletTransaction = new UserWalletTransaction();
            $userWalletTransaction->package_order_id = $PackageOrderId;
            $userWalletTransaction->user_id = $packageOrder->user_id;
            $userWalletTransaction->order_id = $packageOrder->order_id;
            $userWalletTransaction->type = UserWalletTransaction::TYPE_CREDIT;
            $userWalletTransaction->amount = $packageOrder->price_per_delivery;
            $userWalletTransaction->description = 'Amount to be credited';
            $userWalletTransaction->cancelled_date = $packageOrder->date;
            $userWalletTransaction->package_id = $packageOrder->package_id;
            $userWalletTransaction->product_id = $package->product_id;
            $userWalletTransaction->save();
        }

        DB::commit();

        return $this->sendResponse(null, 'order canceled successfully');
    }

    public function approveOrder(Request $request)
    {
        $orderId = $request->order_id;

        $order = Order::with(['user', 'productOrders.product', 'productOrders.options', 'orderStatus', 'deliveryType',
            'deliveryAddress', 'payment', 'packageOrders', 'packageOrders.orderStatus',
            'productOrderRequestOrder.temporaryOrderRequest'])
            ->where('id', $orderId)
            ->first();

        if (empty($order) || $order->type == Order::PACKAGE_TYPE || $order->type == Order::ORDER_REQUEST_TYPE) {
            return $this->sendError('Order not found');
        }

        if ($order->is_order_approved == 1) {
            return $this->sendError('Order already approved');
        }

        $order->is_order_approved = true;
        $order->save();

        try {
            info("owleto order approved".$order->id);
            $userFcmToken[] = $order->user->device_token;
            $attributes['title'] = 'Owleto Order Approved';
            $attributes['redirection_type'] = Order::ORDER_APPROVED_REDIRECTION_TYPE;
            $attributes['message'] = 'Your order with order id ' . $order->id . ' has been approved';
            $attributes['data'] = null;
            $attributes['redirection_id'] = $order->id;
            $attributes['type'] = $order->type;

            Notification::route('fcm', $userFcmToken)
                ->notify(new OrderCancelNotification($attributes));

        } catch (Exception $e) {

        }

        $managerOrders = [];
        $addons = [];
        $orderItems = [];

        $marketId = $order->market_id;
        $market = $this->marketRepository->findWithoutFail($marketId);
        $latMarket = $market->latitude;
        $longMarket = $market->longitude;

        if ($order->type == Order::PRODUCT_TYPE) {

            foreach ($order->productOrders as $productOrder) {
                $addons = $productOrder->order_addons;
                array_push($orderItems, [
                    'order_item_id' => $productOrder->id,
                    'quantity' => $productOrder->quantity,
                    'price' => $productOrder->price,
                    'order_item_name' => $productOrder->product->name,
                    'product_id' => $productOrder->product_id,
                    'image' => $productOrder->product->media,
                    'package_price' => null,
                    'addons' => $addons
                ]);
            }

            $managerOrders = ['id' => $order->id,
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'order_status_id' => $order->order_status_id,
                'type' => $order->type,
                'distance' => $order->distance,
                'delivery_address_id' => $order->delivery_address_id,
                'delivery_type_id' => $order->delivery_type_id,
                'payment_method_id' => $order->payment_method_id,
                'total_amount' => $order->total_amount,
                'sub_total' => $order->sub_total,
                'market_id' => $order->market_id,
                'created_at' => $order->created_at,
                'delivery_fee' => $order->delivery_fee,
                'tax' => $order->tax,
                'is_delivered' => null,
                'is_order_approved' => $order->is_order_approved,
                'order_items' => $orderItems,
                'user' => $order->user,
                'market' => $order->market,
                'order_status' => $order->orderStatus,
                'delivery_address' => $order->deliveryAddress,
                'payment_method' => $order->paymentMethod,
                'delivery_type' => $order->deliveryType
            ];
        }

        if ($order->type == Order::ORDER_REQUEST_TYPE) {

            array_push($orderItems, [
                'order_item_id' => $order->productOrderRequestOrder->id,
                'quantity' => null,
                'price' => $order->productOrderRequestOrder->price,
                'order_item_name' => null,
                'product_id' => null,
                'image' => $order->productOrderRequestOrder->temporaryOrderRequest->orderRequest->image_url,
                'package_price' => null,
                'addons' => $addons
            ]);
            $managerOrders = [
                'id' => $order->id,
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'order_status_id' => $order->order_status_id,
                'type' => $order->type,
                'distance' => $order->distance,
                'delivery_address_id' => $order->delivery_address_id,
                'delivery_type_id' => $order->delivery_type_id,
                'payment_method_id' => $order->payment_method_id,
                'total_amount' => $order->total_amount,
                'sub_total' => $order->sub_total,
                'market_id' => $order->market_id,
                'created_at' => $order->created_at,
                'delivery_fee' => $order->delivery_fee,
                'tax' => $order->tax,
                'is_delivered' => null,
                'is_order_approved' => $order->is_order_approved,
                'order_items' => $orderItems,
                'user' => $order->user,
                'market' => $order->market,
                'order_status' => $order->orderStatus,
                'delivery_address' => $order->deliveryAddress,
                'payment_method' => $order->paymentMethod,
                'delivery_type' => $order->deliveryType
            ];
        }

        if ($order->sector_id != Field::TAKEAWAY || $order->delivery_type_id == DeliveryType::TYPE_EXPRESS) {

            $input['order_status_id'] = Order::STATUS_DRIVER_ASSIGNED;

            $references = $this->database->getReference($this->table)->getValue();

            foreach ($references as $reference) {

                if ($reference) {

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

            }

            $driversCurrentLocations = DriversCurrentLocation::getAvailableDriver($latMarket, $longMarket, $market);

            if (!$driversCurrentLocations) {
                return $this->sendResponse($managerOrders, 'Order approved, Driver Not Assigned');
            }

            $order = $this->orderRepository->update($input, $orderId);

            $distance = $order->distance;
            $driverCommissionAmount = $distance * $driversCurrentLocations->driver->delivery_fee;

            $order->driver_id = $driversCurrentLocations->driver->user_id;
            $order->driver_assigned_at = Carbon::now();
            $order->driver_commission_amount = round($driverCommissionAmount, 2);
            $order->save();

            $driver = Driver::where('id', $driversCurrentLocations->driver_id)->first();
            $driver->available = 0;
            $driver->save();

            try {
                $userOrder = Order::findOrFail($orderId);
                $correspondingDriver = User::findorFail($driver->user_id);
                $driverFcmToken = $correspondingDriver->device_token;

                $attributes['title'] = 'Owleto Order';
                $attributes['message'] = 'Owleto Order with OrderID : ' . $userOrder->id . ' has been Assigned to you.';
                $attributes['data'] = $userOrder->toArray();

                Notification::route('fcm', $driverFcmToken)
                    ->notify(new DriverAssignedNotification($attributes));

            } catch (Exception $e) {

            }

            try {

                $userOrder = Order::findOrFail($orderId);
                $user = User::findorFail($userOrder->user_id);
                $userFcmToken = $user->device_token;

                $attributes['title'] = 'Owleto Order';
                $attributes['message'] = 'Your Order with OrderID ' . $userOrder->id . ' has been Shipped';
                $attributes['data'] = $userOrder->toArray();

                Notification::route('fcm', $userFcmToken)
                    ->notify(new DriverAssignedNotificationToUser($attributes));

            } catch (Exception $e) {

            }
        }

        return $this->sendResponse($managerOrders, 'order approved successfully');
    }

}
