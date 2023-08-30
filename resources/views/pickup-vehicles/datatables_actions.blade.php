<div class='btn-group btn-group-sm'>
  @can('pick-up-vehicles.edit')
  <a data-toggle="tooltip" data-placement="bottom" title="{{trans('lang.pickup-vehicle-edit')}}" href="{{ route('pick-up-vehicles.edit', $id) }}" class='btn btn-link'>
    <i class="fa fa-edit"></i>
  </a>
  @endcan

  @can('pick-up-vehicles.destroy')
{!! Form::open(['route' => ['pick-up-vehicles.destroy', $id], 'method' => 'delete']) !!}
  {!! Form::button('<i class="fa fa-trash"></i>', [
  'type' => 'submit',
  'class' => 'btn btn-link text-danger',
  'onclick' => "return confirm('Are you sure?')"
  ]) !!}
{!! Form::close() !!}
  @endcan
</div>
