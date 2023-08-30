<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    var $attributes;

    public function __construct($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        $attributes =  $this->attributes;

        $order = Order::findOrFail($attributes['order_id']);

        if($order->type == Order::PRODUCT_TYPE){
            $marketName = $order->productOrders[0]->product->market->name;
        }
        else if($order->type == Order::PACKAGE_TYPE){
            $marketName = $order->packageOrders[0]->package->product->market->name;

        }
        else if($order->type == Order::ORDER_REQUEST_TYPE){
            $marketName = $order->productOrderRequestOrder->temporaryOrderRequest->orderRequest->market->name;
        }

        return $this->to($this->attributes['email'])
            ->subject('OWLETO - NEW ORDER MAIL')
            ->view('emails.new_order_mail', compact('attributes','marketName'));

    }
}
