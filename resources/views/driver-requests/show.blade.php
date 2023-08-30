@extends('layouts.app')

@section('content')
<!-- Content Header (Page header) -->
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">{{trans('lang.requested_drivers')}}</h1>
      </div>
    </div>
  </div>
</div>

<div class="content">
  <div class="card">
    <div class="card-header">
      <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
        <li class="nav-item">
          <a class="nav-link" href=""><i class="fa fa-list mr-2"></i>{{trans('lang.driver_table')}}</a>
        </li>
      </ul>
    </div>
    <div class="card-body">
      <div class="row">
        @include('driver-requests.show_fields')
{{--        <div class="form-group col-12 text-right">--}}
{{--          <a href="{!! route('driver-requests.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i> {{trans('lang.back')}}</a>--}}
{{--        </div>--}}
      </div>
      <div class="clearfix"></div>
    </div>
  </div>

  @if($driverBankDetail)

    <div class="card">
      <div class="card-header">
        <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
          <li class="nav-item">
            <a class="nav-link" href=""><i class="fa fa-list mr-2"></i>{{trans('lang.bank_details')}}</a>
          </li>
        </ul>
      </div>
      <div class="card-body">
        <div class="row">
          @include('driver-requests.bank_details')
        </div>
        <div class="clearfix"></div>
      </div>
    </div>

  @endif

  @if($driverDocument)

      <div class="card">
        <div class="card-header">
          <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
            <li class="nav-item">
              <a class="nav-link" href=""><i class="fa fa-list mr-2"></i>{{trans('lang.documents')}}</a>
            </li>
          </ul>
        </div>
        <div class="card-body">
          <div class="row">
            @include('driver-requests.documents')

            <div class="form-group col-12 text-right">
              <a href="{!! route('driver-requests.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i> {{trans('lang.back')}}</a>
            </div>
          </div>
          <div class="clearfix"></div>
        </div>
      </div>

    @endif
</div>
@endsection
