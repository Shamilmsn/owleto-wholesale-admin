<div class='btn-group btn-group-sm'>
{{--  @can('deliveryTypes.show')--}}
{{--  <a data-toggle="tooltip" data-placement="bottom" title="{{trans('lang.view_details')}}" href="{{ route('deliveryTypes.show', $id) }}" class='btn btn-link'>--}}
{{--    <i class="fa fa-eye"></i>--}}
{{--  </a>--}}
{{--  @endcan--}}

  @can('deliveryTypes.edit')
  <a data-toggle="tooltip" data-placement="bottom" title="{{trans('lang.delivery_type_edit')}}" href="{{ route('deliveryTypes.edit', $id) }}" class='btn btn-link'>
    <i class="fa fa-edit"></i>
  </a>
  @endcan

  @can('deliveryTypes.destroy')
{!! Form::open(['route' => ['deliveryTypes.destroy', $id], 'method' => 'delete']) !!}
  {!! Form::button('<i class="fa fa-trash"></i>', [
  'type' => 'submit',
  'class' => 'btn btn-link text-danger',
  'onclick' => "return confirm('Are you sure?')"
  ]) !!}
{!! Form::close() !!}
  @endcan
</div>
