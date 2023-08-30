<div class='btn-group btn-group-sm'>
  @can('attributes.show')
  <a data-toggle="tooltip" data-placement="bottom" title="{{trans('lang.view_details')}}" href="{{ route('attributes.show', $attribute->id) }}" class='btn btn-link'>
    <i class="fa fa-eye"></i>
  </a>
  @endcan

  @can('attributes.edit')
  <a data-toggle="tooltip" data-placement="bottom" title="{{trans('lang.attribute_edit')}}" href="{{ route('attributes.edit', $attribute->id) }}" class='btn btn-link'>
    <i class="fa fa-edit"></i>
  </a>
  @endcan

  @can('attributes.destroy')
      @if($activeAttributes->count() <= 0)
        {!! Form::open(['route' => ['attributes.destroy', $attribute->id], 'method' => 'delete']) !!}
          {!! Form::button('<i class="fa fa-trash"></i>', [
          'type' => 'submit',
          'class' => 'btn btn-link text-danger',
          'onclick' => "return confirm('Are you sure?The product and attribute option under this attribute will be deleted.')"
          ]) !!}
        {!! Form::close() !!}
      @endif
  @endcan

</div>
