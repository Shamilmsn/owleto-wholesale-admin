@extends('layouts.app')

@section('content')
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0 text-dark">Express Orders</h1>
        </div>
      </div>
    </div>
  </div>
  <div class="content">
    <div class="card">
      <div class="card-header d-print-none">
        <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
          <li class="nav-item">
            <a class="nav-link" href="{!! route('express-orders.index') !!}"><i class="fa fa-list mr-2"></i>{{trans('lang.order_table')}}</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="{!! url()->current() !!}"><i class="fa fa-plus mr-2"></i>{{trans('lang.order')}}</a>
          </li>
          <div class="ml-auto d-inline-flex">
            <li class="nav-item">
              <a class="nav-link pt-1" id="printOrder" href="#"><i class="fa fa-print"></i> {{trans('lang.print')}}</a>
            </li>
          </div>
        </ul>
      </div>
      <div class="card-body">
        <div class="row">
          @include('express-orders.show_fields')
        </div>
        @include('product_orders.table')
        <div class="row">
        <div class="col-5 offset-7">
          <div class="table-responsive table-light">
            <table class="table">
              <tbody>
{{--                @if($order_addons->count() > 0)--}}
{{--                  <th class="text-right">{{trans('lang.order_addons')}}</th>--}}
{{--                  <tr>--}}
{{--                  @foreach($order_addons as $order_addon)--}}
{{--                    <tr>--}}
{{--                      <td class="text-right"> {{  $order_addon->name}}</td>--}}
{{--                      <td>{{  $order_addon->price}} </td>--}}
{{--                    </tr>--}}
{{--                    @endforeach--}}
{{--                    </tr>--}}
{{--                 @endif--}}
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
          <!-- Back Field -->
          <div class="form-group col-12 text-right">
            <a href="{!! route('orders.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i> {{trans('lang.back')}}</a>
          </div>
        </div>
        <div class="clearfix"></div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script type="text/javascript">
    $("#printOrder").on("click",function () {
      window.print();
    });
  </script>
@endpush
