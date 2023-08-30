@extends('layouts.app')

@section('content')
<!-- Content Header (Page header) -->
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">{{trans('lang.order_request_plural')}}
{{--          <small class="ml-3 mr-3">|</small><small>{{trans('lang.order_request_desc')}}</small>--}}
        </h1>
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<div class="content">
  <div class="clearfix"></div>
  @include('flash::message')
  <div class="card">
    <div class="card-header">
      <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
        <li class="nav-item">
          <a class="nav-link" href="{!! route('orderRequests.index') !!}"><i class="fa fa-list mr-2"></i>{{trans('lang.order_request_table')}}</a>
        </li>
      </ul>
    </div>
    <div class="card-body">
      <div class="row">
        @include('order_requests.show_fields')
      </div>
      <div class="clearfix"></div>
    </div>
    <div class="card-body">
        {!! Form::open(['route' => 'temporary-order-requests.store', 'id' => 'form-create', 'enctype' => 'multipart/form-data']) !!}

        {!! Form::hidden('user_id', $orderRequest->user_id) !!}
        {!! Form::hidden('order_request_id', $orderRequest->id) !!}
        <div class="form-group  ">
          {!! Form::label('net_amount', trans("lang.temp_order_request_net_amount"),['class' => 'col-12 control-label']) !!}
          <div class="col-6">
            {!! Form::number('net_amount',null,  ['class' => 'form-control','step'=>"any",'placeholder'=>  trans("lang.temp_order_request_net_amount_placeholder")]) !!}
            <div class="form-text text-muted">
              {{ trans("lang.temp_order_request_net_amount_placeholder") }}
            </div>
          </div>
        </div>
{{--        <div class="form-group  ">--}}
{{--            {!! Form::label('status', trans("lang.temp_order_request_status"),['class' => 'col-12 control-label']) !!}--}}
{{--            <div class="col-6">--}}
{{--                    {!! Form::select('status', $statuses, null, ['class' => 'select2 form-control']) !!}--}}
{{--                    <div class="form-text text-muted">{{ trans("lang.temp_order_request_status_placeholder") }}</div>--}}
{{--            </div>--}}
{{--        </div>--}}
        <div class="form-group  ">
            {!! Form::label('image', trans("lang.product_image"),['class' => 'col-12 control-label']) !!}
            <div class="col-6">
                {!! Form::file('image',null,  ['class' => 'form-control','step'=>"any"]) !!}
                <div class="form-text text-muted">
                    {{ trans("lang.choose_image") }}
                </div>
            </div>
        </div>


        @if($tempOrderRequestCount < 1 )
        <div class="form-group row">
            <button type="submit" class="btn btn-{{setting('theme_color')}}" ><i class="fa fa-save"></i> {{trans('lang.save')}}</button>
            {{--          <a href="{!! route('options.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i> {{trans('lang.cancel')}}</a>--}}
        </div>
        @endif

      {!! Form::close() !!}

      <div class="clearfix"></div>
        <div class="card-body">
            @include('order_requests.table')
            <div class="clearfix"></div>
        </div>
        <div class="form-group col-12 text-right">
            <a href="{!! route('orderRequests.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i> {{trans('lang.back')}}</a>
        </div>
    </div>
  </div>
</div>
@include('layouts.media_modal')
@endsection
@push('scripts_lib')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/additional-methods.js"> </script>
<script type="text/javascript">
    $('#form-create').validate({
        rules:{
            // 'status': {
            //     required: true,
            // },
            'net_amount': {
                required: true,
            }
        },

    });
</script>
@endpush

