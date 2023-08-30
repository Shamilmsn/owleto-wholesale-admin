@extends('layouts.app')

@section('content')
<!-- Content Header (Page header) -->
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">{{trans('lang.pickup_vehicle_plural')}}
        </h1>
      </div>
      <div class="col-sm-6">

      </div>
    </div>
  </div>
</div>


<div class="content">
  <div class="clearfix"></div>
  @include('flash::message')
  <div class="card">
    <div class="card-header">
      <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
        <li class="nav-item">
          <a class="nav-link active" href="{!! url()->current() !!}"><i class="fa fa-list mr-2"></i>{{trans('lang.pickup_vehicle_plural')}}</a>
        </li>
        @can('pick-up-vehicles.create')
        <li class="nav-item">
          <a class="nav-link" href="{!! route('pick-up-vehicles.create') !!}"><i class="fa fa-plus mr-2"></i>{{trans('lang.attribute_pickup_vehicle')}}</a>
        </li>
        @endcan
        @include('layouts.right_toolbar', compact('dataTable'))
      </ul>
    </div>
    <div class="card-body">
      @include('pickup-vehicles.table')
      <div class="clearfix"></div>
    </div>
  </div>
</div>
@endsection

