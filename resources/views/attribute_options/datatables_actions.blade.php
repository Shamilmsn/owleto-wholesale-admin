<div class='btn-group btn-group-sm'>
  @can('attributeOptions.show')
  <a data-toggle="tooltip" data-placement="bottom" title="{{trans('lang.view_details')}}" href="{{ route('attributeOptions.show', $attributeOption->id) }}" class='btn btn-link'>
    <i class="fa fa-eye"></i>
  </a>
  @endcan

  @can('attributeOptions.edit')
  <a data-toggle="tooltip" data-placement="bottom" title="{{trans('lang.attribute_option_edit')}}" href="{{ route('attributeOptions.edit', $attributeOption->id) }}" class='btn btn-link'>
    <i class="fa fa-edit"></i>
  </a>
  @endcan

  @can('attributeOptions.destroy')
      @if( $activeAttributesOption->count() <= 0)
          {!! Form::open(['route' => ['attributeOptions.destroy', $attributeOption->id], 'method' => 'delete']) !!}
            {!! Form::button('<i class="fa fa-trash"></i>', [
            'type' => 'submit',
            'class' => 'btn btn-link text-danger',
            'onclick' => "return confirm('Are you sure?')"
            ]) !!}
          {!! Form::close() !!}

        @endif
  @endcan
</div>
