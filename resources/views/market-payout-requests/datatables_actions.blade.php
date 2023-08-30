<div class='btn-group btn-group-sm'>
    @can('driver-payout-requests.show')
        @if($status == 'PENDING')
        <a data-toggle="tooltip" data-placement="bottom" title="{{trans('lang.view_details')}}"
           href="{{ route('market-payout-requests.show', $id) }}" class='btn btn-link'>
            Pay
        </a>
        @endif
        @if($status == 'PARTIALLY PAID')
            <a data-toggle="tooltip" data-placement="bottom" title="{{trans('lang.view_details')}}"
               href="{{ route('market-payout-requests.show', $id) }}" class='btn btn-link'>
                Pay
            </a>
        @endif
    @endcan
</div>
