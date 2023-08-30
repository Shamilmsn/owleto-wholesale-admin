@extends('layouts.app')
@section('content')
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0 text-dark">{{trans('lang.permission_pickup-orders')}}
          </h1>
        </div>
      </div>
    </div>
  </div>
  <div class="content">
    <div class="card">
      <div class="card-header d-print-none">
        <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
          <li class="nav-item">
            <a class="nav-link" href="{!! route('deliver-orders.index') !!}">
              <i class="fa fa-list mr-2"></i>{{trans('lang.order_table')}}</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="{!! url()->current() !!}">
              <i class="fa fa-plus mr-2"></i>{{trans('lang.order')}}</a>
          </li>
        </ul>
      </div>
      <div class="card-body">
        <div class="row">
          @include('deliver-orders.show_fields')
        </div>
        @include('product_orders.table')
        <div class="row">
        <div class="col-5 offset-7">
          <div class="table-responsive table-light">
            <table class="table">
              <tbody>
                    <tr>
                      <th class="text-right">{{trans('lang.order_subtotal')}}</th>
                      <td>{{ $order->sub_total }}</td>
                    </tr>
                    <tr>
                      <th class="text-right">{{trans('lang.order_delivery_fee')}}</th>
                      <td>{{ $order->delivery_fee }}</td>
                    </tr>
                    <tr>
                      <th class="text-right">{{trans('lang.order_tax')}}</th>
                      <td>{{ $order->tax }}</td>
                    </tr>

                    <tr>
                      <th class="text-right">{{trans('lang.order_total')}}</th>
                      <td>{{ $order->total_amount }}</td>
                    </tr>

              </tbody>
            </table>
          </div>
        </div>
        </div>
        <div class="clearfix"></div>
        <div class="row d-print-none">
          <div class="form-group col-12 text-right">
            <a href="{!! route('deliver-orders.index') !!}"
               class="btn btn-default"><i class="fa fa-undo"></i> {{trans('lang.back')}}</a>
          </div>
        </div>
        <div class="clearfix"></div>
      </div>
    </div>
  </div>
@endsection
