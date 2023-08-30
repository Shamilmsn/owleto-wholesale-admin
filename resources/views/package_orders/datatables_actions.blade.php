<div class='btn-group btn-group-sm'>
    @can('package-orders.show')
    <a data-toggle="tooltip" data-placement="bottom"
       title="{{trans('lang.view_details')}}"
       href="{{ route('package-orders.show', $id) }}" class='btn btn-link'>
        <i class="fa fa-eye"></i>
    </a>
    @endcan

{{--    @can('package-orders.edit')--}}
{{--    <a data-toggle="tooltip" data-placement="bottom"
             title="{{trans('lang.order_edit')}}"
             href="{{ route('package-orders.edit', $id) }}"
              class='btn btn-link'>--}}
{{--        <i class="fa fa-edit"></i>--}}
{{--    </a>--}}
{{--    @endcan--}}

{{--    @can('package-orders.destroy')--}}
{{--    {!! Form::open(['route' => ['package-orders.destroy', $id],
'method' => 'delete']) !!}--}}
{{--    {!! Form::button('<i class="fa fa-trash"></i>', [--}}
{{--    'type' => 'submit',--}}
{{--    'class' => 'btn btn-link text-danger',--}}
{{--    'onclick' => "return confirm('Are you sure?')"--}}
{{--    ]) !!}--}}
{{--    {!! Form::close() !!}--}}
{{--    @endcan--}}
</div>
