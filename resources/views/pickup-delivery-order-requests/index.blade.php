@extends('layouts.app')

@section('content')
<!-- Content Header (Page header) -->
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">{{trans('lang.pickup_delivery_order_requests_plural')}}
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
          <a class="nav-link active" href="{!! url()->current() !!}"><i class="fa fa-list mr-2"></i>{{trans('lang.pickup_delivery_order_requests_plural')}}</a>
        </li>
        @include('layouts.right_toolbar', compact('dataTable'))
      </ul>
    </div>
    <div class="card-body table-responsive">
      @include('pickup-delivery-order-requests.table')
      <div class="clearfix"></div>
    </div>
  </div>
</div>
@endsection
@push('scripts')
  <script>
    $(function() {
      let $table = $('#pickup-order-request-table');

      $table.on('click', '.button-status-reject', function (e) {
        e.preventDefault();

        let Id = $(this).data('order-request-id');
        $('#pickup_order_request_Id').val(Id);
        $('#StatusRejectModal').modal('show');

        {{--let url = '{{ url('pickup-delivery-order-request/status/rejected') }}' + '/' + Id;--}}

        {{--$('#status-reject-update').attr('action', url);--}}
      });
    });
  </script>
@endpush

