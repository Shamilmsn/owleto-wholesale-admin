@extends('layouts.app')

@section('content')

  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0 text-dark"> Express Orders</h1>
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
            <a class="nav-link active" href="{!! url()->current() !!}"><i class="fa fa-list mr-2"></i>{{trans('lang.order_table')}}</a>
          </li>
          @include('layouts.right_toolbar', compact('dataTable'))
        </ul>
      </div>
      <div class="card-body bg-grey-100">
        <form id="form-filter-orders" class=" mb-0">
            <div class="row">
                <div class="form-group">
                    <select class="select2 form-control" id="order_status_id">
                        <option value=""  selected>Select Status</option>
                        @foreach($orderStatus as $data)
                            <option value="{{ $data->id }}">{{ $data->status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group ml-2">
                    <select class="select2 form-control" id="payment_method_id">
                        <option value="" selected>Select Payment Method</option>
                        @foreach($paymentMethods as $paymentMethod)
                            <option value="{{ $paymentMethod->id }}">{{ $paymentMethod->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group ml-2 mr-2">
                    <select class="select2 form-control" id="delivery_type_id">
                        <option value="" selected>Select Delivery Type</option>
                        @foreach($deliveryTypes as $deliveryType)
                            <option value="{{ $deliveryType->id }}">{{ $deliveryType->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group ml-2 mr-2">
                    <select class="select2 form-control" id="driver_id">
                        <option value="" selected>Select Driver</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->user->id }}">{{ $driver->user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group ml-2 mr-2">
                    <select class="select2 form-control" id="market_id">
                        <option value="" selected>Select Market</option>
                        @foreach($markets as $market)
                            <option value="{{ $market->id }}">{{ $market->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="Search by order ID" id="search">
                </div>
                <div class="form-group ml-2">
                    <input type="text" name="datetimes" class="form-control" id="order_date"/>
                    <input type="hidden" id="start_date"/>
                    <input type="hidden" id="end_date"/>
                </div>
                <div class="form-group">
                    <button id="btn-filter-order" type="submit" class="ml-2 btn btn-primary btn-outline">Search</button>
                    <a id="btn-clear" class="btn btn-primary ml-2 text-white">Clear</a>
                </div>
            </div>

        </form>
      </div>
      <div class="card-body table-responsive">
        @include('express-orders.table')
        <div class="clearfix"></div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="drivers-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <form method="post" action="{{url('assign-driver-to-express-orders')}}">
                  @csrf
                  <input type="hidden" name="order_id" id="order_id">
                  <div class="modal-header">
                      <h5 class="modal-title" id="exampleModalLabel">Assign Driver</h5>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                      </button>
                  </div>
                  <div class="modal-body">
                      <select data-plugin="select2" class="form-control" name="driver_id">
                          @foreach($drivers as $driver)
                              <option value="{{$driver->user->id}}">{{$driver->user->name}}</option>
                          @endforeach
                      </select>
                  </div>
                  <div class="modal-footer">
                      <button type="submit" class="btn btn-primary">Save</button>
                  </div>
              </form>
          </div>
      </div>
  </div>
@endsection

@push('scripts_lib')
  <script>
      $(function (){

          var $table = $('#tbl-order');

          $('#tbl-order').on('click', '.assign-driver', function (e){
            e.preventDefault();
            var orderId = $(this).attr('data-id');
            $('#order_id').val(orderId);
            $('#drivers-modal').modal('show');
          });

          $('#tbl-order').on('click', '.collect-cash', function (e){
              e.preventDefault();
              var orderId = $(this).attr('data-id');

              if (confirm("Cash collected from driver?") == true) {
                  $.ajax({
                      url:'{{ route('collected-cash.store') }}',
                      method:'POST',
                      data: {
                          "_token": "{{ csrf_token() }}",
                          "orderId": orderId
                      },
                      success : function (response) {
                          $table.DataTable().draw();
                      }
                  })
              }
          });

          $('input[name="datetimes"]').daterangepicker({
              locale: {
                  format: 'D/M/Y'
              }
          }, function(start, end, label) {
              $("#start_date").val(start.format('YYYY-MM-DD'));
              $("#end_date").val(end.format('YYYY-MM-DD'));
          });
      })
  </script>
@endpush

