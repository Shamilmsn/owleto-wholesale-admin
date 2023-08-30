<?php

namespace App\Http\Controllers\API\Driver;

use App\Http\Controllers\Controller;
use App\Mail\OrderDeliveredMail;
use App\Models\Driver;
use App\Models\DriverTransaction;
use App\Models\Earning;
use App\Models\MarketTransaction;
use App\Models\Order;
use App\Models\OrderRequest;
use App\Models\OrderStatus;
use App\Models\OwletoEarning;
use App\Models\PackageOrder;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\PickUpDeliveryOrder;
use App\Models\PickUpDeliveryOrderRequest;
use App\Models\ProductOrderRequestOrder;
use App\Models\TemporaryOrderRequest;
use App\Models\UserWallet;
use App\Models\UserWalletTransaction;
use App\Notifications\OrderDeliveredPushNotifictaion;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Laracasts\Flash\Flash;
use Lcobucci\JWT\Exception;

class OrderStatusChangeController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $orderId = $request->order_id;
        $driverUserId = $request->user_id;
        $orderStatusId = $request->order_status_id;
        $orderType = $request->type;


        $orderStatus = OrderStatus::find($orderStatusId);
        $driver = Driver::where('user_id', $driverUserId)->first();

        if($orderType == Order::PRODUCT_TYPE || $orderType == Order::ORDER_REQUEST_TYPE){

            $order = Order::find($orderId);
            $marketId = $order->market_id;

            if($order->order_status_id == OrderStatus::STATUS_DELIVERED){
                return $this->sendError('already delivered', 409);
            }

            if($orderStatus->id == OrderStatus::STATUS_DELIVERED){

                $driverCommissionAmount = round($order->driver_commission_amount,2);

                $order->order_status_id = $orderStatusId;
                $order->save();

                $earning = Earning::where('market_id', $marketId)->first();

                $totalOrdersCount = $earning->total_orders + 1;
                $marketTotalEarnings = $earning->market_earning + $order->market_balance;
                $balance = $earning->market_balance + $order->market_balance;
                $totalAdminEarnings = $earning->admin_earning + $order->owleto_commission_amount;
                $totalEarning = $earning->total_earning + $order->total_amount;

                $transaction = new MarketTransaction();
                $transaction->market_id = $marketId;
                $transaction->credit = $order->market_balance;
                $transaction->balance = $balance;
                $transaction->model()->associate($order);
                $transaction->save();

                $earning->total_orders = $totalOrdersCount;
                $earning->total_earning = $totalEarning;
                $earning->admin_earning = $totalAdminEarnings;
                $earning->market_earning = $marketTotalEarnings;
                $earning->market_balance = $balance;
                $earning->save();

                $driver = Driver::where('user_id', $driverUserId)->first();

                $driverBalance = $driver->balance + $driverCommissionAmount;

                $driverTransaction = new DriverTransaction();
                $driverTransaction->user_id = $driverUserId;
                $driverTransaction->type = DriverTransaction::TYPE_CREDIT;
                $driverTransaction->credit = $driverCommissionAmount;
                $driverTransaction->description = 'Amount Credited';
                $driverTransaction->balance = $driverBalance;
                $driverTransaction->model()->associate($order);
                $driverTransaction->save();

                if($driver){
                    $totalOrdersCount = $driver->total_orders + 1;
                    $driver->earning = $driver->earning + $driverCommissionAmount;
                    $driver->balance = $driver->balance + $driverCommissionAmount;
                    $driver->total_orders = $totalOrdersCount;
                    $driver->available = 1;
                    if ($order->payment_method_id == PaymentMethod::PAYMENT_METHOD_COD) {
                        $driver->balance_cod_amount = $driver->balance_cod_amount + $order->total_amount;
                    }
                    $driver->save();
                }

                $owletoEarning = new OwletoEarning();
                $owletoEarning->order_id = $order->id;
                $owletoEarning->order_type = $orderType;
                $owletoEarning->earning = round($order->owleto_commission_amount,2);
                $owletoEarning->save();

                if($orderType == Order::ORDER_REQUEST_TYPE){

                    $productOrderRequestOrder = ProductOrderRequestOrder::where('order_id', $order->id)->first();
                    $temporaryOrder = TemporaryOrderRequest::where('id', $productOrderRequestOrder->temporary_order_request_id)->first();
                    $orderRequest = OrderRequest::where('id', $temporaryOrder->order_request_id)->first();
                    $orderRequest->status = OrderRequest::STATUS_ORDER_PAID;
                    $orderRequest->save();
                }

                $attributes['email'] = $order->user->email;
                $attributes['order_id'] = $order->id;

                Mail::send( new OrderDeliveredMail($attributes));
                try {

                    $userFcmToken = $order->user->device_token;
                    $userOrder = Order::findOrFail($order->id);
                    // select only order detail for fcm notification

                    $attributes['title'] = 'Product Delivered';
                    $attributes['message'] ='Product with orderID '.$order->id.' has been delivered';
                    $attributes['data'] = $userOrder->toArray();
                    $attributes['redirection_id'] = $order->id;
                    $attributes['redirection_type'] = Order::NEW_ORDER_REDIRECTION_TYPE;


                    Notification::route('fcm', $userFcmToken)
                        ->notify(new OrderDeliveredPushNotifictaion($attributes));

                }catch (\Exception $e) {

                }

                $payment = Payment::where('order_id', $order->id)->first();

                if($payment) {
                    $payment->status = 'Paid';
                    $payment->description = 'Payment Successful';
                    $payment->save();
                }
            }
            else {
                $order->order_status_id = $orderStatusId;
                $order->save();

                if($orderStatusId == OrderStatus::STATUS_CANCELED){

                    $payment = Payment::where('order_id', $order->id)->first();

                    if($payment) {
                        $payment->status = 'Order Canceled';
                        $payment->description = 'Payment Canceled';
                        $payment->save();
                    }

                    if($order->payment_method_id == PaymentMethod::PAYMENT_METHOD_RAZORPAY) {

                        $userWallet = UserWallet::where('user_id', $order->user_id)->first();

                        if($userWallet){
                            $balance = $userWallet->balance + $order->total_amount;
                        }

                        else{
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
                        $userWalletTransaction->description = 'Amount added to wallet';
                        $userWalletTransaction->cancelled_date = Carbon::now();
                        $userWalletTransaction->package_id = null;
                        $userWalletTransaction->product_id = null;
                        $userWalletTransaction->save();
                    }
                }
            }
        }

        if($orderType == Order::PACKAGE_TYPE){

            $order = PackageOrder::find($orderId);

            if ($order->canceled == 1) {
                return $this->sendError('order already canceled', 409);
            }

            $marketId = $order->market_id;

            if($order->order_status_id == OrderStatus::STATUS_DELIVERED){
                return $this->sendError('already delivered', 409);
            }

            if($orderStatus->id == OrderStatus::STATUS_DELIVERED) {

                $driverCommissionAmount = round($order->driver_commission_amount,2);

                $order->order_status_id = $orderStatusId;
                $order->delivered = 1;
                $order->save();

                $totalPackageOrders =  PackageOrder::query()
                    ->where('order_id', $order->order_id)
                    ->count();

                $totalDeliveredOrders = PackageOrder::query()
                    ->where('order_id', $order->order_id)
                    ->where('delivered', 1)
                    ->count();

                $totalCanceledOrders = PackageOrder::query()
                    ->where('order_id', $order->order_id)
                    ->where('canceled', 1)
                    ->count();

                $totalDeliveredCanceledOrders = $totalDeliveredOrders + $totalCanceledOrders;

                $orderData = Order::where('id', $order->order_id)->first();

                if ($totalPackageOrders == $totalDeliveredCanceledOrders) {
                    $orderData->order_status_id = OrderStatus::STATUS_DELIVERED;
                    $orderData->save();
                }

                $earning = Earning::where('market_id', $marketId)->first();

                $totalOrdersCount = $earning->total_orders + 1;
                $marketTotalEarnings = $earning->market_earning + $order->market_balance;
                $balance = $earning->market_balance + $order->market_balance;
                $totalAdminEarnings = $earning->admin_earning + $order->commission_amount;
                $totalEarning = $earning->total_earning + $order->price_per_delivery;

                $transaction = new MarketTransaction();
                $transaction->market_id = $marketId;
                $transaction->credit = $order->market_balance;
                $transaction->balance = $balance;
                $transaction->model()->associate($order);
                $transaction->save();

                $earning->total_orders = $totalOrdersCount;
                $earning->total_earning = $totalEarning;
                $earning->admin_earning = $totalAdminEarnings;
                $earning->market_earning = $marketTotalEarnings;
                $earning->market_balance = $balance;
                $earning->save();

                $driver = Driver::where('user_id', $driverUserId)->first();

                $driverBalance = $driver->balance + $driverCommissionAmount;

                $driverTransaction = new DriverTransaction();
                $driverTransaction->user_id = $driverUserId;
                $driverTransaction->type = DriverTransaction::TYPE_CREDIT;
                $driverTransaction->credit = $driverCommissionAmount;
                $driverTransaction->description = 'Amount Credited';
                $driverTransaction->balance = $driverBalance;
                $driverTransaction->model()->associate($order);
                $driverTransaction->save();

                if($driver){
                    $totalOrdersCount = $driver->total_orders + 1;
                    $driver->earning = $driver->earning + $driverCommissionAmount;
                    $driver->balance = $driver->balance + $driverCommissionAmount;
                    $driver->total_orders = $totalOrdersCount;
                    $driver->available = 1;
                    $driver->save();
                }

                $owletoEarning = new OwletoEarning();
                $owletoEarning->order_id = $order->id;
                $owletoEarning->order_type = $orderType;
                $owletoEarning->earning = $order->commission_amount;
                $owletoEarning->save();


                $attributes['email'] = $order->user->email;
                $attributes['order_id'] = $order->id;

                Mail::send( new OrderDeliveredMail($attributes));
                try {

                    $userFcmToken = $order->user->device_token;
                    $userOrder = Order::findOrFail($order->id);

                    $attributes['title'] = 'Order delivered successfully';
                    $attributes['message'] ='Your order (OrderID : '. $order->id.')  status has been changed to Delivered';
                    $attributes['data'] = $userOrder->toArray();

                    Notification::route('fcm', $userFcmToken)
                        ->notify(new OrderDeliveredPushNotifictaion($attributes));

                }catch (\Exception $e) {

                }
            }

            else {
                $order->order_status_id = $orderStatusId;
                $order->save();

                $totalPackageOrders =  PackageOrder::query()
                    ->where('order_id', $order->order_id)
                    ->count();

                $totalCanceledOrders = PackageOrder::query()
                    ->where('order_id', $order->order_id)
                    ->where('canceled', 1)
                    ->count();

                $orderData = Order::where('id', $order->order_id)->first();

                if ($totalCanceledOrders == $totalPackageOrders) {
                    $orderData->order_status_id = OrderStatus::STATUS_CANCELED;
                    $orderData->save();
                }

                if($orderStatusId == OrderStatus::STATUS_CANCELED){

                    $payment = Payment::where('order_id', $order->id)->first();

                    if($payment) {
                        $payment->status = 'Order Canceled';
                        $payment->description = 'Payment Canceled';
                        $payment->save();
                    }

                    if($order->payment_method_id == PaymentMethod::PAYMENT_METHOD_RAZORPAY) {

                        $userWallet = UserWallet::where('user_id', $order->user_id)->first();

                        if($userWallet){
                            $balance = $userWallet->balance + $order->total_amount;
                        }

                        else{
                            $balance = $order->total_amount;

                            $userWallet = new UserWallet();
                        }

                        $userWallet->user_id = $order->user_id;
                        $userWallet->balance = $balance;
                        $userWallet->save();

                        $userWalletTransaction = new UserWalletTransaction();
                        $userWalletTransaction->package_order_id = $order->id;
                        $userWalletTransaction->user_id = $order->user_id;
                        $userWalletTransaction->order_id = null;
                        $userWalletTransaction->type = UserWalletTransaction::TYPE_CREDIT;
                        $userWalletTransaction->amount = $order->total_amount;
                        $userWalletTransaction->description = 'Package order canceled. Amount added to wallet';
                        $userWalletTransaction->cancelled_date = Carbon::now();
                        $userWalletTransaction->package_id = $order->package_id;
                        $userWalletTransaction->product_id = null;
                        $userWalletTransaction->save();
                    }
                }

            }
        }

        if($orderType == Order::PICKUP_DELIVERY_ORDER_TYPE){

            $order = Order::find($orderId);

            if($order->order_status_id == OrderStatus::STATUS_DELIVERED){
                return $this->sendError('already delivered', 409);
            }

            if($orderStatus->id == OrderStatus::STATUS_DELIVERED){

                $driverCommissionAmount = round($order->driver_commission_amount,2);

                $order->order_status_id = $orderStatusId;
                $order->save();

                $driver = Driver::where('user_id', $driverUserId)->first();

                $driverBalance = $driver->balance + $driverCommissionAmount;

                $driverTransaction = new DriverTransaction();
                $driverTransaction->user_id = $driverUserId;
                $driverTransaction->type = DriverTransaction::TYPE_CREDIT;
                $driverTransaction->credit = $driverCommissionAmount;
                $driverTransaction->description = 'Amount Credited';
                $driverTransaction->balance = $driverBalance;
                $driverTransaction->model()->associate($order);
                $driverTransaction->save();

                if($driver){
                    $totalOrdersCount = $driver->total_orders + 1;
                    $driver->earning = $driver->earning + $driverCommissionAmount;
                    $driver->balance = $driver->balance + $driverCommissionAmount;
                    $driver->total_orders = $totalOrdersCount;
                    $driver->available = 1;
                    $driver->save();
                }

                $owletoEarning = new OwletoEarning();
                $owletoEarning->order_id = $order->id;
                $owletoEarning->order_type = $orderType;
                $owletoEarning->earning = round($order->owleto_commission_amount,2);
                $owletoEarning->save();
                
                $pickUpDeliveryOrder = PickUpDeliveryOrder::where('order_id', $order->id)->first();
                $pickUpDeliveryOrderRequest = PickUpDeliveryOrderRequest::where('id', $pickUpDeliveryOrder->pick_up_delivery_order_request_id)->first();
                $pickUpDeliveryOrderRequest->status = PickUpDeliveryOrderRequest::STATUS_ORDER_PAID;
                $pickUpDeliveryOrderRequest->save();
//                info($orderRequest);
//                $orderRequest->status = OrderRequest::STATUS_ORDER_PAID;
//                $orderRequest->save();

                $attributes['email'] = $order->user->email;
                $attributes['order_id'] = $order->id;

                try {
                    Mail::send( new OrderDeliveredMail($attributes));
                }catch (Exception $exception){

                }

                try {

                    $userFcmToken = $order->user->device_token;
                    $userOrder = Order::findOrFail($order->id);
                    // select only order detail for fcm notification

                    $attributes['title'] = 'Order delivered successfully';
                    $attributes['message'] ='Your order (OrderID :  '. $order->id.')  status has been changed to Delivered';
                    $attributes['data'] = $userOrder->toArray();


                    Notification::route('fcm', $userFcmToken)
                        ->notify(new OrderDeliveredPushNotifictaion($attributes));

                }catch (\Exception $e) {
                }

            }
            else {
                $order->order_status_id = $orderStatusId;
                $order->save();
            }
        }

        return $this->sendResponse($order,'Delivered successfully');
    }
}
