<div class='btn-group btn-group-sm'>
    @can('pickup-delivery-orders.show')
        <a data-toggle="tooltip" data-placement="bottom"
           title="{{trans('lang.view_details')}}"
           href="{{ route('pickup-delivery-orders.show', $id) }}"
           class='btn btn-link'>
            <i class="fa fa-eye"></i>
        </a>
    @endcan

{{--    @can('orders.edit')--}}
{{--    <a data-toggle="tooltip" data-placement="bottom"
            title="{{trans('lang.order_edit')}}"
            href="{{ route('pickup-delivery-orders.edit', $id) }}"
             class='btn btn-link'>--}}
{{--        <i class="fa fa-edit"></i>--}}
{{--    </a>--}}
{{--    @endcan--}}

    @if(!$driver_id && $order_status_id != \App\Models\OrderStatus::STATUS_CANCELED)
        <a data-toggle="tooltip" data-placement="bottom"
           data-id="{{ $id }}" href="#" class='btn btn-link assign-driver'>
            Assign Driver
        </a>
    @endif

{{--    @can('orders.destroy')--}}
{{--        {!! Form::open(['route' => ['pickup-delivery-orders.destroy', $id], 'method' => 'delete']) !!}--}}
{{--        {!! Form::button('<i class="fa fa-trash"></i>', [--}}
{{--        'type' => 'submit',--}}
{{--        'class' => 'btn btn-link text-danger',--}}
{{--        'onclick' => "return confirm('Are you sure?')"--}}
{{--        ]) !!}--}}
{{--        {!! Form::close() !!}--}}
{{--    @endcan--}}
</div>
