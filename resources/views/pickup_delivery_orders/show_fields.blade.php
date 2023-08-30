<div class="form-group row col-md-4 col-sm-12">
    {!! Form::label('id', trans('lang.order_id'), ['class' => 'col-4 control-label']) !!}
    <div class="col-8">
        <p>#{!! $order->id !!}</p>
    </div>

    {!! Form::label('order_client', trans('lang.order_client'), ['class' => 'col-4 control-label']) !!}
    <div class="col-8">
        <p>{!! $order->user->name !!}</p>
    </div>

    {!! Form::label('order_client_phone', trans('lang.order_client_phone'), ['class' => 'col-4 control-label']) !!}
    <div class="col-8">
        <p>{!! $order->user->phone !!}</p>
    </div>

    {!! Form::label('order_date', trans('lang.order_date'), ['class' => 'col-4 control-label']) !!}
    <div class="col-8">
        <p>{!! $order->created_at !!}</p>
    </div>

</div>

<div class="form-group row col-md-4 col-sm-12">
    {!! Form::label('order_status_id', trans('lang.order_status_status'), ['class' => 'col-4 control-label']) !!}
    <div class="col-8">
        <p>{!! $order->orderStatus->status  !!}</p>
    </div>

    {!! Form::label('active', trans('lang.order_active'), ['class' => 'col-4 control-label']) !!}
    <div class="col-8">
        @if($order->active)
            <p><span class='badge badge-success'> {{trans('lang.yes')}}</span></p>
        @else
            <p><span class='badge badge-danger'>{{trans('lang.order_canceled')}}</span></p>
        @endif
    </div>

    {!! Form::label('payment_method', trans('lang.payment_method'), ['class' => 'col-4 control-label']) !!}
    <div class="col-8">
        <p>{!! isset($order->payment) ? $order->payment->method : ''  !!}</p>
    </div>
    {!! Form::label('order_updated_date', trans('lang.order_updated_at'), ['class' => 'col-4 control-label']) !!}
    <div class="col-8">
        <p>{!! $order->updated_at !!}</p>
    </div>

</div>

<!-- Id Field -->
<div class="form-group row col-md-4 col-sm-12">
    {!! Form::label('driver', trans('lang.driver'), ['class' => 'col-4 control-label']) !!}
    <div class="col-8">

        @if(isset($order->driver))
            <p>{!! $order->driver->name !!}</p>
        @else
            <p>{{trans('lang.order_driver_not_assigned')}}</p>
        @endif

    </div>
    {!! Form::label('delivery_address', trans('lang.delivery_address'), ['class' => 'col-4 control-label']) !!}
    <div class="col-8">
        <p>{!! $order->pickUpDeliveryOrder->pickUpDeliveryOrderRequest->delivery_address ?? ' ' !!}</p>
    </div>

    {!! Form::label('pickup_address', trans('lang.pickup_address'), ['class' => 'col-4 control-label']) !!}
    <div class="col-8">
        <p>{!! $order->pickUpDeliveryOrder->pickUpDeliveryOrderRequest->pickup_address ?? ' ' !!}</p>
    </div>
    @if($order->pickUpDeliveryOrder->pickUpDeliveryOrderRequest->audio_file)
    {!! Form::label('audio_file', 'Voice Instructions', ['class' => 'col-4 control-label']) !!}
    <div class="col-8">
        <audio controls>
            <source src="{{ url('storage/pickup-requests/audios/'.$order->pickUpDeliveryOrder->pickUpDeliveryOrderRequest->audio_file) }}" type="audio/mpeg">
        </audio>
    </div>
    @endif

</div>



