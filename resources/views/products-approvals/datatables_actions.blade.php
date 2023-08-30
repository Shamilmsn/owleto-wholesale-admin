<div class='btn-group btn-group-sm'>
    @can('product-approvals.show')
        <a data-toggle="tooltip"
           data-placement="bottom"
           title="{{trans('lang.view_details')}}"
           href="{{ route('product-approvals.show', $product->id) }}"
         class='btn btn-link'>
        <i class="fa fa-eye"></i>
        </a>
    @endcan
    @can('product-approvals.edit')
        <a data-toggle="tooltip"
           data-placement="bottom"
           href="{{ route('product-approvals.edit', $product->id) }}"
           class='btn btn-link'>
            <i class="fa fa-edit"></i>
        </a>
    @endcan
    @can('product.approve')
        @if(!$product->is_approved)
            <a data-toggle="tooltip" data-placement="bottom"
               data-approve="1" title="{{trans('lang.product_approve')}}"
               href="{{ route('product.approve', $product->id) }}"
               class='button-approve text-danger ml-3'>
                <i class="fa fa-tag"></i>
            </a>
        @endif
    @endcan
</div>
