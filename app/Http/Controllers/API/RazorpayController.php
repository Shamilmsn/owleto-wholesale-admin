<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\NewOrderMail;
use App\Mail\PaymentSuccessfulMail;
use App\Models\Market;
use App\Models\Order;
use App\Models\OrderRequest;
use App\Models\OrderStatus;
use App\Models\PackageOrder;
use App\Models\Payment;
use App\Models\PickUpDeliveryOrder;
use App\Models\PickUpDeliveryOrderRequest;
use App\Models\ProductOrderRequestOrder;
use App\Models\TemporaryOrderRequest;
use App\Models\User;
use App\Models\UserWallet;
use App\Notifications\NewOrder;
use App\Notifications\OrderDeliveredPushNotifictaion;
use App\Notifications\PaymentSuccessfullPushNotifictaion;
use App\Notifications\PickUpDeliveryOrderAcceptPushNotification;
use App\Repositories\CartRepository;
use App\Repositories\OrderRepository;
use App\Repositories\PaymentRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

class RazorpayController extends Controller
{
    /** @var  OrderRepository */
    private $orderRepository;
    /** @var   */
/** @var  PaymentRepository */
    private $paymentRepository;
    /** @var   */

    /** @var  CartRepository */
    private $cartRepository;
    /** @var   */

    public function __construct(OrderRepository $orderRepository, PaymentRepository $paymentRepository, CartRepository $cartRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->paymentRepository = $paymentRepository;
        $this->cartRepository = $cartRepository;
    }

    public function verifySignature(Request $request)
    {
        $success = false;
        
        $order = Order::with(
                'productOrders.product.market',
                'packageOrders.package.product.market',
                'productOrderRequestOrder.temporaryOrderRequest.orderRequest.market'
            )
            ->where('id', $request->id)
            ->first();


         $user = User::find($order->user->id);



        $api_key = config('services.razorpay.api_key');
        $api_secret = config('services.razorpay.api_secret');
        $api = new Api($api_key, $api_secret);

        try
        {
            $attributes  = array(
                'razorpay_signature'  => $request->razorpay_signature,
                'razorpay_payment_id'  => $request->razorpay_payment_id ,
                'razorpay_order_id' => $request->razorpay_order_id
            );

            $api->utility->verifyPaymentSignature($attributes);
            $success = true;

        }
        catch(SignatureVerificationError $e)
        {
            $success = false;
            $error = 'Razorpay Error : ' . $e->getMessage();
        }

        if ($success == true){

            $order->payment_status = 'SUCCESS';
            $order->save();

            if($order->type == Order::ORDER_REQUEST_TYPE){

                $productOrderRequestOrder = ProductOrderRequestOrder::where('order_id', $order->id)->first();

                $temporaryOrder = TemporaryOrderRequest::where('id', $productOrderRequestOrder->temporary_order_request_id)->first();
                $temporaryOrder->status = OrderRequest::STATUS_ORDER_PAID;
                $temporaryOrder->save();

                $orderRequest = OrderRequest::where('id', $temporaryOrder->order_request_id)->first();
                $orderRequest->status = OrderRequest::STATUS_ORDER_PAID;
                $orderRequest->save();
            }

            if($order->type == Order::PICKUP_DELIVERY_ORDER_TYPE){
                $pickUpDeliveryOrder = PickUpDeliveryOrder::where('order_id', $order->id)->first();
                $pickUpDeliveryOrderRequest = PickUpDeliveryOrderRequest::where('id', $pickUpDeliveryOrder->pick_up_delivery_order_request_id)->first();
                $pickUpDeliveryOrderRequest->status = PickUpDeliveryOrderRequest::STATUS_ORDER_PAID;
                $pickUpDeliveryOrderRequest->save();
            }

            $userWallet = UserWallet::where('user_id', $order->user_id)->first();

            if($userWallet){
                $userWallet->balance = $userWallet->balance - $order->amount_from_wallet;
                $userWallet->save();
            }

            $payment = Payment::where('order_id', $order->id)->first();

            if($payment) {
                $payment->status = 'Paid';
                $payment->description = 'Payment Successful';
                $payment->save();
            }

            try {

                $attributes['email'] = $order->user->email;
                $attributes['order_id'] = $order->id;
                $attributes['amount'] = $order->total_amount;

                Mail::send( new PaymentSuccessfulMail($attributes));

            }catch (\Exception $e) {

            }

            try {
                if ($order->market_id) {
                    $market = Market::with('users')
                        ->where('id', $order->market_id)
                        ->first();

                    if($order->type == Order::PRODUCT_TYPE){
                        $url = url($order->productOrders[0]->product->market->getFirstMediaUrl('image', 'thumb'));
                    }

                    if($order->type == Order::PACKAGE_TYPE){
                        $url = url($order->packageOrders[0]->package->product->market->getFirstMediaUrl('image', 'thumb'));
                    }

                    if($order->type == Order::ORDER_REQUEST_TYPE){

                        $url = url($order->productOrderRequestOrder->temporaryOrderRequest->orderRequest->market->getFirstMediaUrl('image', 'thumb'));
                    }

                    if (count($market->users) > 0) {
                        foreach ($market->users as $user) {
                            $userFcmToken[] = $user->device_token;
                            $marketAttributes['title'] = 'Owleto new order';
                            $marketAttributes['redirection_type'] = Order::NEW_ORDER_REDIRECTION_TYPE;
                            $marketAttributes['message'] ='You have received a new order from '. $order->user->name .' with OrderID '.$order->id.' for '. $market->name;
                            $marketAttributes['image'] = $url;
                            $marketAttributes['data'] = null;
                            $marketAttributes['redirection_id'] = $order->id;
                            $marketAttributes['type'] = $order->type;

                            Notification::route('fcm', $userFcmToken)
                                ->notify(new NewOrder($marketAttributes));
                        }
                    }
                }


            }catch (\Exception $e) {

            }

            try {

                $userFcmToken = $order->user->device_token;

                $userOrder = Order::findOrFail($order->id);

                $attribute['title'] = $userOrder->type == Order::ORDER_REQUEST_TYPE ? 'Manual order placed successfully' : 'Order placed successfully';
                $attribute['redirection_type'] = Order::NEW_ORDER_REDIRECTION_TYPE;
                $attribute['type'] = $userOrder->type;
                $attribute['redirection_id'] = $userOrder->id;
                $attribute['message'] ='Your new order is placed with OrderID '.$order->id.' from ' . $order->market->name;

                $attribute['data'] = $userOrder->toArray();

                Notification::route('fcm', $userFcmToken)
                    ->notify(new PaymentSuccessfullPushNotifictaion($attribute));

            }catch (\Exception $e) {

            }

            $this->cartRepository->deleteWhere(['user_id' => $order->user_id]);

            return ['message' => 'Payment has been done successfully', 'data' => $order, 'status' => 200];

        }
        else{

            $order->payment_status = 'FAILED';
            $order->order_status_id = OrderStatus::STATUS_CANCELED;
            $order->save();

            $payment = Payment::where('order_id', $order->id)->first();

            if($payment) {
                $payment->status = 'Failed';
                $payment->description = 'Payment Failed';
                $payment->save();
            }

            if($order->type == Order::PACKAGE_TYPE){

                $packageOrders = PackageOrder::where('order_id', $order->id)->get();
                foreach ($packageOrders as $packageOrder){
                    $packageOrder->order_status_id = OrderStatus::STATUS_CANCELED;
                    $packageOrder->save();
                }

            }

            return ['message' => 'Payment failed', 'data' => null, 'status' => 404];
        }
    }
}
