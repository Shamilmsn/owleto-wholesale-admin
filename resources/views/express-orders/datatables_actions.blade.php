<div class='btn-group btn-group-sm'>
      <a data-toggle="tooltip" data-placement="bottom"
         title="{{trans('lang.view_details')}}" href="{{ route('express-orders.show', $order->id) }}"
         class='btn btn-link'>
        <i class="fa fa-eye"></i>
      </a>
</div>
@if(!$order->driver_id &&
    $order->order_status_id != \App\Models\OrderStatus::STATUS_CANCELED &&
      $order->order_status_id != \App\Models\OrderStatus::STATUS_DELIVERED)
    <a data-toggle="tooltip" data-placement="bottom"
       data-id="{{ $order->id }}" href="#" class='btn btn-link assign-driver'>
        Assign Driver
    </a>
@endif
@if($order->driver_id && !$order->is_driver_approved &&
      $order->order_status_id != \App\Models\OrderStatus::STATUS_DELIVERED)
    <a data-toggle="tooltip" data-placement="bottom"
       data-id="{{ $order->id }}" href="#" class='btn btn-link assign-driver'>
        Reassign Driver
    </a>
@endif
