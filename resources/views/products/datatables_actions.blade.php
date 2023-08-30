<div class='btn-group btn-group-sm'>
    @if(!auth()->user()->hasRole('admin'))
        @if($product->is_approved)
            <badge class="btn text-success">Approved</badge>
        @else
            <badge class="btn text-danger">Not Approved</badge>
        @endif

    @endif
        @if(request()->user()->hasRole('admin'))
            @if($product->featured == 1)
                <a data-toggle="tooltip" title="remove from featured"
                   href="#" data-id="{{$product->id}}"
                   class='btn btn-link remove-from-featured text-success'>
                    <i class="fa fa-check"></i>
                </a>
            @else
                <a data-toggle="tooltip" data-placement="bottom"
                   title="add to featured" href="#" data-id="{{$product->id}}"
                   class='btn  add-to-featured
                    btn-link text-danger'>
                    <i class="fa fa-times"></i>
                </a>
            @endif
        @endif

  @can('products.show')
  <a data-toggle="tooltip" data-placement="bottom"
     title="{{trans('lang.view_details')}}" href="{{ route('products.show', $product->id) }}"
     class='btn btn-link'>
    <i class="fa fa-eye"></i>
  </a>
  @endcan
  @can('products.edit')
  <a data-toggle="tooltip" data-placement="bottom"
     title="{{trans('lang.product_edit')}}"
     href="{{ route('products.edit', $product->id) }}" class='btn btn-link'>
    <i class="fa fa-edit"></i>
  </a>
  @endcan

    @can('product.approve')
      @if(!$product->is_approved)
        <a data-toggle="tooltip" data-placement="bottom"  data-approve="1" title="{{trans('lang.product_approve')}}" href="{{ route('product.approve', $product->id) }}" class='button-approve text-danger mr-3'>
          <i class="fa fa-tag"></i>
        </a>
      @else
        <a data-toggle="tooltip" data-placement="bottom" data-approve="0"  title="{{trans('lang.product_reject')}}" href="{{ route('product.approve', $product->id) }}" class='button-approve text-success mr-3'>
          <i class="fa fa-bookmark"></i>
        </a>
      @endif
      @if($product->is_flash_sale == true)
          @if(!$product->is_flash_sale_approved )
              <a data-toggle="tooltip" data-placement="bottom"  data-approve="1" title="{{trans('lang.product_flash_sale_approve')}}" href="{{ route('product.flash-sale-approve', $product->id) }}" class='button-flash-sale-approve text-danger'>
                  <i class="fa fa-pause-circle"></i>
              </a>
          @else
              <a data-toggle="tooltip" data-placement="bottom" data-approve="0"  title="{{trans('lang.product_flash_sale_reject')}}" href="{{ route('product.flash-sale-approve', $product->id) }}" class='button-flash-sale-approve text-success'>
                  <i class="fa fa-check-square"></i>
              </a>
          @endif
      @endif
    @endcan

{{--        @can('products.destroy')--}}
{{--            <a data-toggle="tooltip" data-placement="bottom"--}}
{{--               title="delete"--}}
{{--               href="{{ route('products.edit', $product->id) }}" class='btn btn-link text-danger'>--}}
{{--                <i class="fa fa-trash"></i>--}}
{{--            </a>--}}
{{--        @endcan--}}

        @if(request()->user()->hasRole('admin'))
  @can('products.destroy')
{!! Form::open(['route' => ['products.destroy', $product->id], 'method' => 'delete']) !!}
  {!! Form::button('<i class="fa fa-trash"></i>', [
  'type' => 'submit',
  'class' => 'btn btn-link text-danger',
  'onclick' => "return confirm('Are you sure?')"
  ]) !!}
{!! Form::close() !!}
  @endcan
            @endif
</div>
