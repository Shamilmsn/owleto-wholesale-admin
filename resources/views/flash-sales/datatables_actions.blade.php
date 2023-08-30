<div class='btn-group btn-group-sm'>
     @if(request()->user()->hasRole('admin') && !$product->is_flash_sale_approved)
        <a data-toggle="tooltip"
           data-placement="bottom"
           title="approve"
           href="{{ url('/flash-sales/'.$product->id.'/approve') }}"
           class='btn btn-link'>
                <i class="fa fa-check-circle"></i>
        </a>
     @endcan
    @can('flash-sales.edit')
        <a data-toggle="tooltip"
           data-placement="bottom"
           title="edit"
           href="{{ route('flash-sales.edit', $product->id) }}"
         class='btn btn-link'>
        <i class="fa fa-edit"></i>
        </a>
    @endcan
    @if($product->is_flash_sale_approved)
        <a data-toggle="tooltip"
                data-placement="bottom"
                title="edit"
                href="{{ url('/flash-sales/'.$product->id.'/delete')  }}"
                class='btn btn-link'>
                     <i class="fa fa-trash"></i>
        </a>
     @endif
</div>
