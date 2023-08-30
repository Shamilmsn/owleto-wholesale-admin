<div class='btn-group btn-group-sm'>
    @can('order-request-orders.show')
        <a data-toggle="tooltip" data-placement="bottom" title="{{trans('lang.view_details')}}"
           href="{{ route('order-request-orders.show', $order->id) }}" class='btn btn-link'>
            <i class="fa fa-eye"></i>
        </a>
    @endcan

    @can('orders.edit')
    <a data-toggle="tooltip" data-placement="bottom" title="{{trans('lang.order_edit')}}"
       href="{{ route('order-request-orders.edit', $order->id) }}" class='btn btn-link'>
        <i class="fa fa-edit"></i>
    </a>
    @endcan

    @if ($order->payment_method_id == \App\Models\PaymentMethod::PAYMENT_METHOD_COD &&
            $order->is_collected_from_driver == 0 &&
            $order->order_status_id == \App\Models\OrderStatus::STATUS_DELIVERED)
        <a title="collect cash" href="#" class='btn btn-link collect-cash' data-id="{{ $order->id }}">
            <i class="fa fa-check"></i>
        </a>
    @endif

    @if($order->sector_id != \App\Models\Field::TAKEAWAY)
        @if(!$order->driver_id && $order->order_status_id != \App\Models\OrderStatus::STATUS_CANCELED)
            <a data-toggle="tooltip" data-placement="bottom" data-id="{{$order->id}}"
               href="#" class='btn btn-link btn-driver-assign'>
                Assign Driver
            </a>
        @endif
    @endif
{{--    @can('orders.destroy')--}}
{{--        {!! Form::open(['route' => ['order-request-orders.destroy', $id], 'method' => 'delete']) !!}--}}
{{--        {!! Form::button('<i class="fa fa-trash"></i>', [--}}
{{--        'type' => 'submit',--}}
{{--        'class' => 'btn btn-link text-danger',--}}
{{--        'onclick' => "return confirm('Are you sure?')"--}}
{{--        ]) !!}--}}
{{--        {!! Form::close() !!}--}}
{{--    @endcan--}}
</div>
