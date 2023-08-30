<div class='btn-group btn-group-sm'>
  @can('orders.show')
      <a data-toggle="tooltip" data-placement="bottom"
         title="{{trans('lang.view_details')}}" href="{{ route('orders.show', $order->id) }}"
         class='btn btn-link'>
        <i class="fa fa-eye"></i>
      </a>
  @endcan

{{--  @if(request()->user()->hasRole('admin'))--}}
{{--      @can('orders.edit')--}}
{{--          <a data-toggle="tooltip" data-placement="bottom"--}}
{{--             title="{{trans('lang.order_edit')}}"
 href="{{ route('orders.edit', $order->id) }}"--}}
{{--             class='btn btn-link'>--}}
{{--            <i class="fa fa-edit"></i>--}}
{{--          </a>--}}
{{--      @endcan--}}
{{--  @endif--}}
  @if ($order->payment_method_id == \App\Models\PaymentMethod::PAYMENT_METHOD_COD &&
                $order->is_collected_from_driver == 0 &&
                $order->order_status_id == \App\Models\OrderStatus::STATUS_DELIVERED)
      <a title="collect cash" href="#" class='btn btn-link collect-cash'
         data-id="{{ $order->id }}">
        <i class="fa fa-check"></i>
      </a>
  @endif
{{--  @if(request()->user()->hasRole('admin'))--}}
{{--      @if($order->sector_id != \App\Models\Field::TAKEAWAY)--}}
{{--            @if(!$order->driver_id &&--}}
{{--                $order->order_status_id != \App\Models\OrderStatus::STATUS_CANCELED &&--}}
{{--                  $order->order_status_id != \App\Models\OrderStatus::STATUS_DELIVERED)--}}
{{--                <a data-toggle="tooltip" data-placement="bottom"--}}
{{--                   data-id="{{ $order->id }}" href="#" class='btn btn-link assign-driver'>--}}
{{--                  Assign Driver--}}
{{--                </a>--}}
{{--            @endif--}}
{{--      @endif--}}
{{--      @if($order->sector_id != \App\Models\Field::TAKEAWAY)--}}
{{--          @if($order->driver_id && !$order->is_driver_approved &&--}}
{{--                $order->order_status_id != \App\Models\OrderStatus::STATUS_DELIVERED)--}}
{{--            <a data-toggle="tooltip" data-placement="bottom"--}}
{{--               data-id="{{ $order->id }}" href="#" class='btn btn-link assign-driver'>--}}
{{--              Reassign Driver--}}
{{--            </a>--}}
{{--          @endif--}}
{{--      @endif--}}
{{--  @endif--}}
  @if(request()->user()->hasRole('admin') && $order->order_category == \App\Models\Order::VENDOR_BASED)
      @if($order->sector_id != \App\Models\Field::TAKEAWAY)
            @if(!$order->driver_id &&
                $order->order_status_id != \App\Models\OrderStatus::STATUS_CANCELED &&
                  $order->order_status_id != \App\Models\OrderStatus::STATUS_DELIVERED)
                <a data-toggle="tooltip" data-placement="bottom"
                   data-id="{{ $order->id }}" href="#" class='btn btn-link assign-single-driver'>
                  Assign Driver
                </a>
            @endif
      @endif
      @if($order->sector_id != \App\Models\Field::TAKEAWAY)
          @if($order->driver_id && !$order->is_driver_approved &&
                $order->order_status_id != \App\Models\OrderStatus::STATUS_DELIVERED)
            <a data-toggle="tooltip" data-placement="bottom"
               data-id="{{ $order->id }}" href="#" class='btn btn-link assign-driver'>
              Reassign Driver
            </a>
          @endif
      @endif
  @endif
</div>
