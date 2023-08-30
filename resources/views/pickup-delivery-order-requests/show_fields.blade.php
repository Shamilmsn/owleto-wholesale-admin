<!-- Id Field -->
<div class="form-group row col-md-4 col-sm-12">

    {!! Form::label('name', trans('lang.name'), ['class' => 'col-4 control-label']) !!}
    <div class="col-8">
        {{--        <p>{!! isset($order->user->custom_fields['phone']) ? $order->user->custom_fields['phone']['view'] : "" !!}</p>--}}
        <p>{!! $orderRequest->name !!}</p>
    </div>

    {!! Form::label('phone', trans('lang.phone'), ['class' => 'col-4 control-label']) !!}
    <div class="col-8">
        {{--        <p>{!! isset($order->user->custom_fields['phone']) ? $order->user->custom_fields['phone']['view'] : "" !!}</p>--}}
        <p>{!! $orderRequest->phone !!}</p>
    </div>

    {!! Form::label('delivery_latitude', trans('lang.delivery_latitude'), ['class' => 'col-4 control-label']) !!}
    <div class="col-8">
        <p>{!! $orderRequest->delivery_latitude !!}</p>
    </div>

    {!! Form::label('delivery_longitude', trans('lang.delivery_longitude'), ['class' => 'col-4 control-label']) !!}
    <div class="col-8">
        <p>{!! $orderRequest->delivery_longitude !!}</p>
    </div>

    {!! Form::label('pickup_latitude', 'Pickup Latitude', ['class' => 'col-4 control-label']) !!}
    <div class="col-8">
        <p>{!! $orderRequest->pickup_latitude !!}</p>
    </div>

    {!! Form::label('pickup_longitude', trans('lang.pickup_longitude'), ['class' => 'col-4 control-label']) !!}
    <div class="col-8">
        <p>{!! $orderRequest->pickup_longitude !!}</p>
    </div>

    {!! Form::label('order_date', trans('lang.order_date'), ['class' => 'col-4 control-label']) !!}
    <div class="col-8">
        <p>{!! \Illuminate\Support\Carbon::parse($orderRequest->created_at)->format('d M Y') !!}</p>
    </div>

    {!! Form::label('item_description', trans('lang.item_description'), ['class' => 'col-4 control-label']) !!}
    <div class="col-8">
        <p>{!! $orderRequest->item_description !!}</p>
    </div>
    {!! Form::label('pick_up_vehicle_id', trans('lang.pick_up_vehicle_name'), ['class' => 'col-4 control-label']) !!}
    <div class="col-8">
        <p>{!! $orderRequest->pickUpVehicle->name ?? ' ' !!}</p>
    </div>
</div>

<!-- Order Status Id Field -->
<div class="form-group row col-md-4 col-sm-12">
    {!! Form::label('status', trans('lang.pickup_order_status'), ['class' => 'col-4 control-label']) !!}
    <div class="col-8">
        <p>{!! $orderRequest->status  !!}</p>
    </div>

    {!! Form::label('pickup_address', trans('lang.pickup_address'), ['class' => 'col-4 control-label']) !!}
    <div class="col-8">
        <p>{!! $orderRequest->pickup_address !!}</p>
    </div>

    {!! Form::label('delivery_address', trans('lang.delivery_address'), ['class' => 'col-4 control-label']) !!}
    <div class="col-8">
        <p>{!! $orderRequest->delivery_address !!}</p>
    </div>

    {!! Form::label('distance_in_kilometer', trans('lang.distance_in_kilometer'), ['class' => 'col-4 control-label']) !!}
    <div class="col-8">
        <p>{!! $orderRequest->distance_in_kilometer !!}</p>
    </div>

    {!! Form::label('pickup_time', trans('lang.pickup_time'), ['class' => 'col-4 control-label']) !!}
    <div class="col-8">
        <p>{!! \Illuminate\Support\Carbon::parse($orderRequest->pickup_time)->format('H:i:s')  !!}</p>
    </div>

    {!! Form::label('order_updated_date', trans('lang.order_updated_at'), ['class' => 'col-4 control-label']) !!}
    <div class="col-8">
        <p>{!! $orderRequest->updated_at !!}</p>
    </div>

    @if($orderRequest->rejected_reason)
        {!! Form::label('reject_reason', 'Reject Reason', ['class' => 'col-4 control-label']) !!}
        <div class="col-8">
            <p>{!! $orderRequest->rejected_reason !!}</p>
        </div>
    @endif
    @if($orderRequest->expected_delivery_time)
    {!! Form::label('expected_delivery_time', trans('lang.expected_delivery_time'), ['class' => 'col-4 control-label']) !!}
    <div class="col-8">
        <p>{!! $orderRequest->expected_delivery_time !!}</p>
    </div>
    @endif
    {!! Form::label('net_amount', trans('lang.net_amount'), ['class' => 'col-4 control-label']) !!}
    <div class="col-8">
        <p>{!! $orderRequest->net_amount !!}</p>
    </div>

</div>


