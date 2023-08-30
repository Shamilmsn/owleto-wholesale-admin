<?php
/**
 * File name: OrderAPIController.php
 * Last modified: 2020.05.31 at 19:34:40
 * Author: Pixbit Solutions - https://pixbitsolutions.com
 * Copyright (c) 2020
 *
 */

namespace App\Http\Controllers\API\Driver;


use App\Http\Controllers\Controller;
use App\Models\Days;
use App\Models\DeliveryAddress;
use App\Models\Order;
use App\Models\PackageOrder;
use App\Models\PickUpDeliveryOrderRequest;
use App\Models\PujaBookingOrder;
use App\Repositories\CartRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\OrderRepository;
use App\Repositories\PackageOrderRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\PickUpDeliveryOrderRepository;
use App\Repositories\ProductOrderRepository;
use App\Repositories\ProductOrderRequestOrderRepository;
use App\Repositories\UserRepository;
use Flash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

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
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $start = new Carbon('first day of this month');
        $last = new Carbon('last day of this month');

        $orders = Order::with(['user', 'productOrders.product', 'productOrders.options', 'orderStatus',
            'deliveryAddress', 'payment', 'packageOrders', 'pickUpDeliveryOrder.pickUpDeliveryOrderRequest' => function ($query) use ($request, $start, $last) {
                $query->with('package.product.market')->where('driver_id', $request->user_id);
                if ($request->order_status_ids) {
                    $query->whereIn('order_status_id', $request->order_status_ids);
                }

                if ($request->date_filter == 'today') {
                    $query->whereDate('driver_assigned_at', Carbon::today());
                }

                if ($request->date_filter == 'month') {

                    $query->whereBetween('created_at', [$start, $last]);
                }

            }, 'packageOrders.orderStatus',
            'productOrderRequestOrder.temporaryOrderRequest', 'pickUpDeliveryOrder.pickUpDeliveryOrderRequest'])
            ->where(function ($query) use ($request, $start, $last) {
                $query->where('driver_id', $request->user_id)
                    ->orWhereHas('packageOrders', function ($query) use ($request, $start, $last) {
                        $query->where('driver_id', $request->user_id);
                        if ($request->order_status_ids) {
                            $query->whereIn('order_status_id', $request->order_status_ids);
                        }
                    });
            })
            ->orderBy('id', 'desc');

//        if($request->date_filter == 'today') {
//
//            $orders = $orders->whereDate('driver_assigned_at', Carbon::today());
//        }

        if ($request->order_status_ids) {

            $orders = $orders->whereIn('order_status_id', $request->order_status_ids)
                ->orWhereHas('packageOrders', function ($query) use ($request, $start, $last) {
                    $query->whereIn('order_status_id', $request->order_status_ids);
                });
        }

        if ($request->date_filter == 'month') {

            $orders = $orders->whereBetween('driver_assigned_at', [$start, $last]);
        }

        $orders = $orders->get();

//        return $orders;

        $driverOrders = [];

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
                        'order_item_name' => $productOrder->product->name,
                        'product_id' => $productOrder->product_id,
                        'image' => $productOrder->product->media,
                        'package_price' => null,
                        'addons' => $addons
                    ]);
                }

                array_push($driverOrders, [
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
                    'order_items' => $orderItems,
                    'user' => $order->user,
                    'market' => $order->market,
                    'order_status' => $order->orderStatus,
                    'delivery_address' => $order->deliveryAddress,
                    'payment_method' => $order->paymentMethod,
                    'coupon_discount_amount' => $order->coupon_discount_amount,
                    'amount_from_wallet' => $order->amount_from_wallet,
                    'pickup_address' => null,
                    'is_driver_approved' => $order->is_driver_approved,
                ]);
            }
            if ($order->type == Order::PACKAGE_TYPE) {

                $packageOrders = $order->packageOrders()
                    ->when($request->order_status_ids, function ($query) use ($request) {
                        $query->whereIn('order_status_id', $request->order_status_ids);
                    })->get();

                foreach ($packageOrders as $packageOrder) {
                    if ($packageOrder->driver_id == $request->user_id) {
                        $orderItems = [];
                        array_push($orderItems, [
                            'order_item_id' => $packageOrder->package->product->id,
                            'quantity' => null,
                            'price' => $packageOrder->price_per_delivery,
                            'order_item_name' => $packageOrder->package->product->name,
                            'product_id' => $packageOrder->package->product->id,
                            'image' => $packageOrder->package->product->media,
                            'package_price' => $packageOrder->package_price,
                            'addons' => $addons
                        ]);

                        array_push($driverOrders, [
                            'id' => $packageOrder->id,
                            'order_id' => $order->id,
                            'user_id' => $order->user_id,
                            'order_status_id' => $packageOrder->order_status_id,
                            'type' => $order->type,
                            'distance' => $packageOrder->distance,
                            'delivery_address_id' => $order->delivery_address_id,
                            'delivery_type_id' => $order->delivery_type_id,
                            'payment_method_id' => $order->payment_method_id,
                            'total_amount' => $packageOrder->price_per_delivery,
                            'sub_total' => $order->price_per_delivery,
                            'market_id' => $order->market_id,
                            'created_at' => $packageOrder->created_at,
                            'delivery_fee' => $order->delivery_fee,
                            'tax' => $order->tax,
                            'is_delivered' => $packageOrder->delivered,
                            'order_items' => $orderItems,
                            'user' => $order->user,
                            'market' => $order->market,
                            'order_status' => $packageOrder->orderStatus,
                            'delivery_address' => $order->deliveryAddress,
                            'payment_method' => $order->paymentMethod,
                            'coupon_discount_amount' => $order->coupon_discount_amount,
                            'amount_from_wallet' => $order->amount_from_wallet,
                            'pickup_address' => null,
                            'is_driver_approved' => $packageOrder->is_driver_approved,
                        ]);
                    }
                }
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

                array_push($driverOrders, [
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
                    'order_items' => $orderItems,
                    'user' => $order->user,
                    'market' => $order->market,
                    'order_status' => $order->orderStatus,
                    'delivery_address' => $order->deliveryAddress,
                    'payment_method' => $order->paymentMethod,
                    'coupon_discount_amount' => $order->coupon_discount_amount,
                    'amount_from_wallet' => $order->amount_from_wallet,
                    'pickup_address' => null,
                    'is_driver_approved' => $order->is_driver_approved,
                ]);
            }

            if ($order->type == Order::PICKUP_DELIVERY_ORDER_TYPE) {
                array_push($orderItems, [
                    'order_item_id' => $order->pickUpDeliveryOrder->id,
                    'quantity' => null,
                    'price' => $order->pickUpDeliveryOrder->price,
                    'order_item_name' => $order->pickUpDeliveryOrder->pickUpDeliveryOrderRequest->item_description,
                    'product_id' => null,
                    'image' => null,
                    'package_price' => null,
                    'addons' => $addons,
                    'audio_description' => Storage::disk('public')->url('pickup-requests/audios/'.$order->pickUpDeliveryOrder->audio_file),
                ]);


                $pickup_address = new PickUpDeliveryOrderRequest();
                $pickup_address->id = $order->pickUpDeliveryOrder->pickUpDeliveryOrderRequest->id;
                $pickup_address->address = $order->pickUpDeliveryOrder->pickUpDeliveryOrderRequest->pickup_address;
                $pickup_address->latitude = $order->pickUpDeliveryOrder->pickUpDeliveryOrderRequest->pickup_latitude;
                $pickup_address->longitude = $order->pickUpDeliveryOrder->pickUpDeliveryOrderRequest->pickup_longitude;

                $delivery_address = new DeliveryAddress();
                $delivery_address->id = $order->pickUpDeliveryOrder->pickUpDeliveryOrderRequest->id;
                $delivery_address->address = $order->pickUpDeliveryOrder->pickUpDeliveryOrderRequest->delivery_address;
                $delivery_address->latitude = $order->pickUpDeliveryOrder->pickUpDeliveryOrderRequest->delivery_latitude;
                $delivery_address->longitude = $order->pickUpDeliveryOrder->pickUpDeliveryOrderRequest->delivery_longitude;


                array_push($driverOrders, [
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
                    'market_id' => null,
                    'created_at' => $order->created_at,
                    'delivery_fee' => $order->delivery_fee,
                    'tax' => $order->tax,
                    'is_delivered' => null,
                    'order_items' => $orderItems,
                    'user' => $order->user,
                    'market' => $order->market,
                    'order_status' => $order->orderStatus,
                    'payment_method' => $order->paymentMethod,
                    'pickup_address' => $pickup_address,
                    'delivery_address' => $delivery_address,
                    'coupon_discount_amount' => $order->coupon_discount_amount,
                    'amount_from_wallet' => $order->amount_from_wallet,
                    'vehicle' => $order->pickUpDeliveryOrder->pickUpDeliveryOrderRequest->pickUpVehicle->name,
                    'is_driver_approved' => $order->is_driver_approved,
                ]);
            }

        }

        return $this->sendResponse($driverOrders, 'Orders retrieved successfully');
    }

    public function show(Request $request, $id)
    {
        $orderItems = [];

        if ($request->type != Order::PACKAGE_TYPE) {

            $order = Order::with(['user', 'productOrders.product', 'productOrders.options', 'orderStatus',
                'deliveryAddress', 'payment', 'packageOrders', 'productOrderRequestOrder.temporaryOrderRequest',
                'pickUpDeliveryOrder.pickUpDeliveryOrderRequest'])
                ->where('id', $id)
                ->first();

            $addons = $order->order_addons;
        } else {

            $packageOrder = PackageOrder::where('id', $id)->first();

            $order = Order::with(['user', 'productOrders.product', 'productOrders.options', 'orderStatus', 'order_addons',
                'deliveryAddress', 'payment', 'packageOrders', 'productOrderRequestOrder.temporaryOrderRequest',
                'pickUpDeliveryOrder.pickUpDeliveryOrderRequest'])
                ->where('id', $packageOrder->order_id)
                ->first();

            $addons = $order->order_addons;
        }

        if ($request->type == Order::PRODUCT_TYPE) {

            foreach ($order->productOrders as $productOrder) {
                array_push($orderItems, [
                    'order_item_id' => $productOrder->id,
                    'quantity' => $productOrder->quantity,
                    'price' => $productOrder->price,
                    'order_item_name' => $productOrder->product->name,
                    'product_id' => $productOrder->product_id,
                    'image' => $productOrder->product->media,
                    'package_price' => null,
                ]);
            }

            $data = [
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
                'order_items' => $orderItems,
                'user' => $order->user,
                'market' => $order->market,
                'order_status' => $order->orderStatus,
                'delivery_address' => $order->deliveryAddress,
                'payment_method' => $order->paymentMethod,
                'coupon_discount_amount' => $order->coupon_discount_amount,
                'amount_from_wallet' => $order->amount_from_wallet,
                'addons' => $addons,
                'is_driver_approved' => $order->is_driver_approved,
            ];
        }

        if ($request->type == Order::ORDER_REQUEST_TYPE) {

            array_push($orderItems, [
                'order_item_id' => $order->productOrderRequestOrder->id,
                'quantity' => null,
                'price' => $order->productOrderRequestOrder->price,
                'order_item_name' => null,
                'product_id' => null,
                'image' => $order->productOrderRequestOrder->temporaryOrderRequest->orderRequest->image_url,
                'package_price' => null,
            ]);

            $data = [
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
                'order_items' => $orderItems,
                'user' => $order->user,
                'market' => $order->market,
                'order_status' => $order->orderStatus,
                'delivery_address' => $order->deliveryAddress,
                'payment_method' => $order->paymentMethod,
                'addons' => $addons,
                'is_driver_approved' => $order->is_driver_approved,
            ];
        }

        if ($request->type == Order::PICKUP_DELIVERY_ORDER_TYPE) {

            array_push($orderItems, [
                'order_item_id' => $order->pickUpDeliveryOrder->id,
                'quantity' => null,
                'price' => $order->pickUpDeliveryOrder->price,
                'order_item_name' => $order->pickUpDeliveryOrder->pickUpDeliveryOrderRequest->item_description,
                'product_id' => null,
                'image' => null,
                'package_price' => null,
            ]);

            $data = [
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
                'market_id' => null,
                'created_at' => $order->created_at,
                'delivery_fee' => $order->delivery_fee,
                'tax' => $order->tax,
                'is_delivered' => null,
                'order_items' => $orderItems,
                'user' => $order->user,
                'market' => $order->market,
                'order_status' => $order->orderStatus,
                'delivery_address' => $order->deliveryAddress,
                'payment_method' => $order->paymentMethod,
                'coupon_discount_amount' => $order->coupon_discount_amount,
                'amount_from_wallet' => $order->amount_from_wallet,
                'addons' => $addons,
                'is_driver_approved' => $order->is_driver_approved,
            ];
        }

        if ($request->type == Order::PACKAGE_TYPE) {

            array_push($orderItems, [
                'order_item_id' => $packageOrder->package->product->id,
                'quantity' => null,
                'price' => $packageOrder->price_per_delivery,
                'order_item_name' => $packageOrder->package->product->name,
                'product_id' => $packageOrder->package->product->id,
                'image' => $packageOrder->package->product->media,
                'package_price' => $packageOrder->package_price,
            ]);

            $data = [
                'id' => $packageOrder->id,
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'order_status_id' => $packageOrder->order_status_id,
                'type' => $order->type,
                'distance' => $packageOrder->distance,
                'delivery_address_id' => $order->delivery_address_id,
                'delivery_type_id' => $order->delivery_type_id,
                'payment_method_id' => $order->payment_method_id,
                'total_amount' => $packageOrder->price_per_delivery,
                'sub_total' => $order->price_per_delivery,
                'market_id' => $order->market_id,
                'created_at' => $packageOrder->created_at,
                'delivery_fee' => $order->delivery_fee,
                'tax' => $order->tax,
                'is_delivered' => $packageOrder->delivered,
                'order_items' => $orderItems,
                'user' => $order->user,
                'market' => $order->market,
                'order_status' => $order->orderStatus,
                'delivery_address' => $order->deliveryAddress,
                'payment_method' => $order->paymentMethod,
                'coupon_discount_amount' => $order->coupon_discount_amount,
                'amount_from_wallet' => $order->amount_from_wallet,
                'addons' => $addons,
                'is_driver_approved' => $order->is_driver_approved,
            ];
        }

        return $this->sendResponse($data, 'Order retrieved successfully');
    }

}
