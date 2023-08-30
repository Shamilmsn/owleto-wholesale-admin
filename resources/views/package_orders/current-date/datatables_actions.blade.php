<div class='btn-group btn-group-sm'>
    @can('todays-package-orders.edit')
        <a data-toggle="tooltip" data-placement="bottom"
           href="{{ route('todays-package-orders.edit', $packageOrder->id) }}" class='btn btn-link'>
            <i class="fa fa-edit"></i>
        </a>
    @endcan

    @can('todays-package-orders.show')
        <a data-toggle="tooltip" data-placement="bottom" title="{{trans('lang.view_details')}}"
           href="{{ route('package-order-details.show', $packageOrder->id) }}" class='btn btn-link'>
            <i class="fa fa-eye"></i>
        </a>
    @endcan

    @if(!$packageOrder->driver_id && $packageOrder->order_status_id != \App\Models\OrderStatus::STATUS_CANCELED)
        <a data-toggle="tooltip" data-placement="bottom" data-id="{{ $packageOrder->id }}"
           href="#" class='btn btn-link btn-driver-assign'>
            Assign Driver
        </a>
    @endif
</div>
