<div class='btn-group btn-group-sm'>
  @can('driver-requests.show')
    <a data-toggle="tooltip" data-placement="bottom" title="{{trans('lang.view_details')}}" href="{{ route('driver-requests.show', $id) }}" class='btn btn-link'>
      <i class="fa fa-eye"></i>
    </a>
  @endcan

  @can('driver-requests.edit')
    <a data-toggle="tooltip" data-placement="bottom" title="{{trans('lang.driver_edit')}}" href="{{ route('driver-requests.edit', $id) }}" class='btn btn-link'>
      <i class="fa fa-edit"></i>
    </a>
  @endcan

{{--  @can('driver-requests.destroy')--}}
{{--    {!! Form::open(['route' => ['driver-requests.destroy', $id], 'method' => 'delete']) !!}--}}
{{--      {!! Form::button('<i class="fa fa-trash"></i>', [--}}
{{--      'type' => 'submit',--}}
{{--      'class' => 'btn btn-link text-danger',--}}
{{--      'onclick' => "return confirm('Are you sure?')"--}}
{{--      ]) !!}--}}
{{--    {!! Form::close() !!}--}}
{{--  @endcan--}}
</div>
