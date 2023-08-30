<?php

namespace App\Http\Controllers\API;

use App\Criteria\Orders\OrdersOfStatusesCriteria;
use App\Criteria\Orders\TypeCriteria;
use App\Criteria\Orders\UserCriteria;
use App\Http\Controllers\Controller;
use App\Models\DeliveryType;
use App\Models\Field;
use App\Models\Market;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PackageOrder;
use App\Models\PaymentMethod;
use App\Models\SubscriptionPackage;
use App\Models\UserWallet;
use App\Models\UserWalletTransaction;
use App\Notifications\NewOrder;
use App\Notifications\OrderCancelNotification;
use App\Repositories\CartRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\OrderRepository;
use App\Repositories\PackageOrderRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\ProductOrderRepository;
use App\Repositories\SubscriptionPackageRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Exception;
use Facade\Ignition\Support\Packagist\Package;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;

class MyPackageAPIController extends Controller
{
    /** @var  OrderRepository */
    private $orderRepository;

    /**
     * OrderAPIController constructor.
     * @param OrderRepository $orderRepo
     */
    public function __construct(OrderRepository $orderRepo)
    {
        $this->orderRepository = $orderRepo;
    }

    /**
     * Display a listing of the Order.
     * GET|HEAD /orders
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $type = Order::PACKAGE_TYPE;
        $search = $request->search;

        $orders = Order::with('packageOrders.package', 'user', 'packageOrders.package.product.market', 'orderStatus', 'deliveryAddress')
            ->where('user_id', $request->user_id)
            ->where('type', $type)
            ->orderBy('created_at', 'desc');

        if ($search) {
            $orders = $orders->whereHas('packageOrders.package', function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            });
        }

        $orders = $orders->get();

        $userOrders = [];

        foreach ($orders as $order) {

            $isPackageAssignedToDriver = $order->packageOrders()
                ->whereNotNull('driver_id')
                ->first();

            if ($isPackageAssignedToDriver) {
                $order->is_cancelable = false;
            } else {
                $order->is_cancelable = true;
            }

            if ($order->payment_method_id == PaymentMethod::PAYMENT_METHOD_RAZORPAY) {
                if ($order->payment_status == 'SUCCESS') {
                    array_push($userOrders, $order);
                }
            } else {
                array_push($userOrders, $order);
            }
        }

        return $this->sendResponse($userOrders, 'My packages retrieved successfully');
    }

    public function show(Request $request, $id)
    {
        $type = Order::PACKAGE_TYPE;

        /** @var Order $order */
        if (!empty($this->orderRepository)) {
            try {
                $this->orderRepository->pushCriteria(new RequestCriteria($request));
                $this->orderRepository->pushCriteria(new LimitOffsetCriteria($request));
                $this->orderRepository->pushCriteria(new TypeCriteria($type));

            } catch (RepositoryException $e) {
                return $this->sendError($e->getMessage());
            }
            $order = $this->orderRepository->findWithoutFail($id);
        }

        if (empty($order)) {
            return $this->sendError('Order not found');
        }

        $isPackageAssignedToDriver = $order->packageOrders()
            ->whereNotNull('driver_id')
            ->first();

        if ($isPackageAssignedToDriver) {
            $order->is_cancelable = false;
        } else {
            $order->is_cancelable = true;
        }

        return $this->sendResponse($order->toArray(), 'package retrieved successfully');

    }

    public function cancelOrder(Request $request)
    {
        $orderId = $request->id;

        $order = Order::with('user')->find($orderId);

        if (!$order) {
            return $this->sendError('No order found', 202);
        }

        if ($order->order_status_id == OrderStatus::STATUS_DELIVERED) {
            return $this->sendError('Order already delivered. This order cannot be canceled', 202);
        }

        if ($order->order_status_id == OrderStatus::STATUS_CANCELED) {
            return $this->sendError('Order already canceled', 202);
        }

        if ($order->delivery_type_id == DeliveryType::TYPE_EXPRESS || $order->sector_id == Field::HOME_COOKED_FOOD) {
            if ($order->is_order_approved == 1) {
                return $this->sendError('Cannot cancel the order. Order already approved by the merchant.', 202);
            }
        }

        if ($order->type == Order::PACKAGE_TYPE) {

            $deliveredPackageOrders = PackageOrder::where('order_id', $orderId)->where('delivered', 1)->get();

            if (count($deliveredPackageOrders) > 0) {
                return $this->sendError('You have already purchased some items. This order cannot be canceled', 202);
            }
        }

        $order->order_status_id = OrderStatus::STATUS_CANCELED;
        $order->is_canceled = true;
        $order->save();

        $amountToWallet = $order->total_amount;

        if ($order->payment_method_id == PaymentMethod::PAYMENT_METHOD_RAZORPAY && $order->payment_status == 'SUCCESS') {

            if ($order->type == Order::PACKAGE_TYPE) {

                $sumofCanceledPackageOrders = PackageOrder::where('order_id', $orderId)
                    ->where('canceled', 1)
                    ->sum('price_per_delivery');

                if ($sumofCanceledPackageOrders > 0) {
                    $amountToWallet = $amountToWallet - $sumofCanceledPackageOrders;
                }

                $packageOrders = PackageOrder::where('order_id', $order->id)->get();

                foreach ($packageOrders as $packageOrder) {
                    $packageOrder->order_status_id = OrderStatus::STATUS_CANCELED;
                    $packageOrder->canceled = true;
                    $packageOrder->save();
                }

            }

            $userWallet = UserWallet::where('user_id', $order->user_id)->first();
            if ($userWallet) {
                $balance = $userWallet->balance + $amountToWallet;
            } else {
                $balance = $amountToWallet;
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
            $userWalletTransaction->amount = $amountToWallet;
            $userWalletTransaction->description = 'Amount added to wallet';
            $userWalletTransaction->cancelled_date = Carbon::now();
            $userWalletTransaction->package_id = null;
            $userWalletTransaction->product_id = null;
            $userWalletTransaction->save();
        }
// wallet refund
        if ($order->payment_method_id == PaymentMethod::PAYMENT_METHOD_WALLET) {

            if ($order->type == Order::PACKAGE_TYPE) {

                $sumofCanceledPackageOrders = PackageOrder::where('order_id', $orderId)
                    ->where('canceled', 1)
                    ->sum('price_per_delivery');

                if ($sumofCanceledPackageOrders > 0) {
                    $amountToWallet = $amountToWallet - $sumofCanceledPackageOrders;
                }

                $packageOrders = PackageOrder::where('order_id', $order->id)->get();

                foreach ($packageOrders as $packageOrder) {
                    $packageOrder->order_status_id = OrderStatus::STATUS_CANCELED;
                    $packageOrder->canceled = true;
                    $packageOrder->save();
                }

            }

            $userWallet = UserWallet::where('user_id', $order->user_id)->first();
            if ($userWallet) {
                $balance = $userWallet->balance + $amountToWallet;
            } else {
                $balance = $amountToWallet;
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
            $userWalletTransaction->amount = $amountToWallet;
            $userWalletTransaction->description = 'Amount added to wallet';
            $userWalletTransaction->cancelled_date = Carbon::now();
            $userWalletTransaction->package_id = null;
            $userWalletTransaction->product_id = null;
            $userWalletTransaction->save();
        }

        try {
            $marketID = $order->market_id;
            if ($marketID) {
                $market = Market::with('users')
                    ->where('id', $marketID)
                    ->first();

                if (count($market->users) > 0) {
                    foreach ($market->users as $user) {
                        $userFcmToken[] = $user->device_token;
                        $attributes['title'] = 'Owleto Order Canceled';
                        $attributes['redirection_type'] = Order::ORDER_CANCELLED_REDIRECTION_TYPE;
                        $attributes['message'] = 'User has been canceled the order with OrderID ' . $order->id;
                        $attributes['data'] = null;
                        $attributes['redirection_id'] = $order->id;
                        $attributes['type'] = $order->type;

                        Notification::route('fcm', $userFcmToken)
                            ->notify(new OrderCancelNotification($attributes));
                    }
                }
            }

        } catch (Exception $e) {

        }

        return $this->sendResponse($order, 'order canceled successfully');
    }

    public function cancelPackageItem(Request $request)
    {
        DB::beginTransaction();

        $PackageOrderId = $request->id;
        $packageOrder = PackageOrder::find($PackageOrderId);

        if ($packageOrder->order_status_id == OrderStatus::STATUS_DELIVERED) {
            return $this->sendError('Order already delivered. This order cannot be canceled', 202);
        }

        if ($packageOrder->canceled == 1) {
            return $this->sendError('Order already canceled', 202);
        }

        $packageOrder->canceled = true;
        $packageOrder->order_status_id = Order::STATUS_CANCELED;
        $packageOrder->save();

        $order = Order::where('id', $packageOrder->order_id)->first();

        $totalCanceledOrders = PackageOrder::query()
            ->where('order_id', $packageOrder->order_id)
            ->where('canceled', 1)
            ->count();

        $totalDeliveredOrders = PackageOrder::query()
            ->where('order_id', $packageOrder->order_id)
            ->where('delivered', 1)
            ->count();

        $totalDeliveredCanceledOrders = $totalCanceledOrders + $totalDeliveredOrders;

        $totalPackageOrders = PackageOrder::query()
            ->where('order_id', $packageOrder->order_id)
            ->count();

        if ($totalCanceledOrders == $totalPackageOrders) {
            $order->order_status_id = OrderStatus::STATUS_CANCELED;
            $order->save();
        }

        if ($totalDeliveredCanceledOrders == $totalPackageOrders) {
            $order->order_status_id = OrderStatus::STATUS_DELIVERED;
            $order->save();
        }

        try {
            $marketID = $order->market_id;
            if ($marketID) {
                $market = Market::with('users')
                    ->where('id', $marketID)
                    ->first();

                if (count($market->users) > 0) {
                    foreach ($market->users as $user) {
                        $userFcmToken[] = $user->device_token;
                        $attributes['title'] = 'Owleto Order Canceled';
                        $attributes['redirection_type'] = Order::ORDER_CANCELLED_REDIRECTION_TYPE;
                        $attributes['message'] = 'User has been canceled the subscription item with OrderID ' . $packageOrder->id;
                        $attributes['data'] = null;
                        $attributes['redirection_id'] = $order->id;
                        $attributes['type'] = $order->type;

                        Notification::route('fcm', $userFcmToken)
                            ->notify(new OrderCancelNotification($attributes));
                    }
                }
            }

        } catch (Exception $e) {
        }

        if ($packageOrder->payment_method_id == PaymentMethod::PAYMENT_METHOD_RAZORPAY && $order->payment_status == 'SUCCESS') {
            $package = SubscriptionPackage::where('id', $packageOrder->package_id)->first();

            $userWallet = UserWallet::where('user_id', $request->user_id)->first();
            if ($userWallet) {
                $balance = $userWallet->balance + $packageOrder->price_per_delivery;
            } else {
                $balance = $packageOrder->price_per_delivery;
                $userWallet = new UserWallet();
            }
            $userWallet->user_id = $request->user_id;
            $userWallet->balance = $balance;
            $userWallet->save();

            $userWalletTransaction = new UserWalletTransaction();
            $userWalletTransaction->package_order_id = $PackageOrderId;
            $userWalletTransaction->user_id = $request->user_id;
            $userWalletTransaction->order_id = $packageOrder->order_id;
            $userWalletTransaction->type = UserWalletTransaction::TYPE_CREDIT;
            $userWalletTransaction->amount = $packageOrder->price_per_delivery;
            $userWalletTransaction->description = 'Amount added to wallet';
            $userWalletTransaction->cancelled_date = $packageOrder->date;
            $userWalletTransaction->package_id = $packageOrder->package_id;
            $userWalletTransaction->product_id = $package->product_id;
            $userWalletTransaction->save();
        }

        DB::commit();

        return $this->sendResponse(null, 'order canceled successfully');
    }
}
