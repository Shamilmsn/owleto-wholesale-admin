@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">{{trans('lang.order_request_orders_plural')}}</h1>
                </div>
            </div>
        </div>
    </div>
    <!-- /.content-header -->

    <div class="content">
        <div class="clearfix"></div>
        @include('flash::message')
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
                    <li class="nav-item">
                        <a class="nav-link active" href="{!! url()->current() !!}"><i class="fa fa-list mr-2"></i>{{trans('lang.order_request_order_table')}}</a>
                    </li>
                    @include('layouts.right_toolbar', compact('dataTable'))
                </ul>
            </div>
            <div class="card-body table-responsive">
                @include('order_request_orders.table')
                <div class="clearfix"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="drivers-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="post" action="{{url('assign-driver-to-order-request-orders')}}">
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

            var $table = $('#order-table');

            $('#order-table').on('click', '.btn-driver-assign', function (e){
                e.preventDefault();
                var orderId = $(this).attr('data-id');
                $('#order_id').val(orderId);
                $('#drivers-modal').modal('show');
            });

            $('#order-table').on('click', '.collect-cash', function (e){
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
        })
    </script>
@endpush