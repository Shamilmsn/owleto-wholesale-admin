<div class='btn-group btn-group-sm'>
    @can('cities.edit')
        <a data-toggle="tooltip" data-placement="bottom" title="{{trans('lang.cities_edit')}}" href="{{ route('cities.edit', $id) }}" class='btn btn-link'>
            <i class="fa fa-edit"></i>
        </a>
    @endcan

    @can('cities.destroy')
        {!! Form::open(['route' => ['cities.destroy', $id], 'method' => 'delete']) !!}
        {!! Form::button('<i class="fa fa-trash"></i>', [
        'type' => 'submit',
        'class' => 'btn btn-link text-danger',
        'onclick' => "return confirm('Are you sure?')"
        ]) !!}
        {!! Form::close() !!}
    @endcan
</div>
