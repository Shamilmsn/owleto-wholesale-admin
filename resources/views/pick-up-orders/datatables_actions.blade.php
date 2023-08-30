<div class='btn-group btn-group-sm'>
  @can('pickup-orders.show')
  <a data-toggle="tooltip"
     data-placement="bottom"
     title="{{trans('lang.view_details')}}"
     href="{{ route('pickup-orders.show', $order->id) }}"
     class='btn btn-link'>
    <i class="fa fa-eye"></i>
  </a>
  @endcan

  @if ($order->payment_method_id == \App\Models\PaymentMethod::PAYMENT_METHOD_COD &&
                $order->is_collected_from_driver == 0 &&
                $order->order_status_id == \App\Models\OrderStatus::STATUS_DELIVERED)
      <a title="collect cash"
         href="#"
         class='btn btn-link collect-cash'
         data-id="{{ $order->id }}">
        <i class="fa fa-check"></i>
      </a>
  @endif

</div>
