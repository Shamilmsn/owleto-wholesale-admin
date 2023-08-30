
<div class='btn-group btn-group-sm'>
@can('terms.edit')
    <a data-toggle="tooltip" data-placement="bottom"
       title="Edit"
       href="{{ route('terms.edit', $id) }}" class='btn btn-link'>
        <i class="fa fa-edit"></i>
    </a>
@endcan
@can('terms.destroy')
    {!! Form::open(['route' => ['terms.destroy', $id], 'method' => 'delete']) !!}
    {!! Form::button('<i class="fa fa-trash"></i>', [
    'type' => 'submit',
    'class' => 'btn btn-link text-danger',
    'onclick' => "return confirm('Are you sure?')"
    ]) !!}
    {!! Form::close() !!}
@endcan
</div>
