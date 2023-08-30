<div class='btn-group btn-group-sm'>
  @can('orders.show')
      <a data-toggle="tooltip" data-placement="bottom"
         title="{{trans('lang.view_details')}}" href="{{ route('orders.show', $subOrder->id) }}"
         class='btn btn-link'>
        <i class="fa fa-eye"></i>
      </a>
  @endcan

{{--  @if(request()->user()->hasRole('admin'))--}}
{{--      @can('orders.edit')--}}
{{--          <a data-toggle="tooltip" data-placement="bottom"--}}
{{--             title="{{trans('lang.order_edit')}}"
 href="{{ route('orders.edit', $subOrder->id) }}"--}}
{{--             class='btn btn-link'>--}}
{{--            <i class="fa fa-edit"></i>--}}
{{--          </a>--}}
{{--      @endcan--}}
{{--  @endif--}}
  @if ($subOrder->payment_method_id == \App\Models\PaymentMethod::PAYMENT_METHOD_COD &&
                $subOrder->is_collected_from_driver == 0 &&
                $subOrder->order_status_id == \App\Models\OrderStatus::STATUS_DELIVERED)
      <a title="collect cash" href="#" class='btn btn-link collect-cash'
         data-id="{{ $subOrder->id }}">
        <i class="fa fa-check"></i>
      </a>
  @endif
  @if(request()->user()->hasRole('admin') && $subOrder->order_category == \App\Models\Order::VENDOR_BASED)
      @if($subOrder->sector_id != \App\Models\Field::TAKEAWAY)
            @if(!$subOrder->driver_id &&
                $subOrder->order_status_id != \App\Models\OrderStatus::STATUS_CANCELED &&
                  $subOrder->order_status_id != \App\Models\OrderStatus::STATUS_DELIVERED)
                <a data-toggle="tooltip" data-placement="bottom"
                   data-id="{{ $subOrder->id }}" href="#" class='btn btn-link assign-driver'>
                  Assign Driver
                </a>
            @endif
      @endif
      @if($subOrder->sector_id != \App\Models\Field::TAKEAWAY)
          @if($subOrder->driver_id && !$subOrder->is_driver_approved &&
                $subOrder->order_status_id != \App\Models\OrderStatus::STATUS_DELIVERED)
            <a data-toggle="tooltip" data-placement="bottom"
               data-id="{{ $subOrder->id }}" href="#" class='btn btn-link assign-driver'>
              Reassign Driver
            </a>
          @endif
      @endif
  @endif
</div>
