<div class='btn-group btn-group-sm'>
{{--    @can('package-order-details.edit')--}}
{{--        <a data-toggle="tooltip" data-placement="bottom"--}}
{{--           href="{{ route('package-order-details.edit', $packageOrder->id) }}"
 class='btn btn-link'>--}}
{{--            <i class="fa fa-edit"></i>--}}
{{--        </a>--}}
{{--    @endcan--}}

    @can('package-order-details.show')
        <a data-toggle="tooltip" data-placement="bottom" title="{{trans('lang.view_details')}}"
           href="{{ route('package-order-details.show', $packageOrder->id) }}"
           class='btn btn-link'>
            <i class="fa fa-eye"></i>
        </a>
    @endcan

    @if(!$packageOrder->driver_id &&
        $packageOrder->order_status_id != \App\Models\OrderStatus::STATUS_CANCELED &&
         !$packageOrder->canceled)
        <a data-toggle="tooltip" data-placement="bottom" data-id="{{ $packageOrder->id }}"
           href="#" class='btn btn-link btn-driver-assign'>
            Assign Driver
        </a>
    @endif
</div>
