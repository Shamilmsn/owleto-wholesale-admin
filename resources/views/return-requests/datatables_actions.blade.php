<div class='btn-group btn-group-sm'>
    @can('areas.edit')
        <a data-toggle="tooltip" data-placement="bottom" title="{{trans('lang.areas_edit')}}" href="{{ route('areas.edit', $id) }}" class='btn btn-link'>
            <i class="fa fa-edit"></i>
        </a>
    @endcan

    @can('areas.destroy')
        {!! Form::open(['route' => ['areas.destroy', $id], 'method' => 'delete']) !!}
        {!! Form::button('<i class="fa fa-trash"></i>', [
        'type' => 'submit',
        'class' => 'btn btn-link text-danger',
        'onclick' => "return confirm('Are you sure?')"
        ]) !!}
        {!! Form::close() !!}
    @endcan
</div>
