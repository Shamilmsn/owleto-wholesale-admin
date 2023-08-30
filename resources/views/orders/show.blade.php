@extends('layouts.app')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">{{trans('lang.order_plural')}}
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
                        <a class="nav-link" href="{!! route('orders.index') !!}"><i
                                    class="fa fa-list mr-2"></i>{{trans('lang.order_table')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="{!! url()->current() !!}"><i
                                    class="fa fa-plus mr-2"></i>{{trans('lang.order')}}</a>
                    </li>
                    <div class="ml-auto d-inline-flex">
                        <li class="nav-item">
                            <a class="nav-link pt-1" id="printOrder" href="#"><i
                                        class="fa fa-print"></i> {{trans('lang.print')}}</a>
                        </li>
                    </div>
                </ul>
            </div>
            <div class="clearfix"></div>
            @include('flash::message')
            <div class="card-body">
                @if($order->order_category == \App\Models\Order::PRODUCT_BASED)
                    <div class="row">
                        <h3>Sub Orders</h3>
                        <table class="table" id="tbl-suborder">
                            <thead>
                            <tr>
                                <th scope="col">Order ID</th>
                                <th scope="col">Customer</th>
                                <th scope="col">Order Status</th>
                                <th scope="col">Market</th>
                                <th scope="col">Driver</th>
                                <th scope="col">Is Collected?</th>
                                <th scope="col">Driver Commission</th>
                                <th scope="col">Owleto Commission</th>
                                <th scope="col">Delivery Fee</th>
                                <th scope="col">Vendor Commission</th>
                                <th scope="col">Method</th>
                                <th scope="col">Delivery Type</th>
                                <th scope="col">Updated At</th>
                                <th scope="col">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($subOrders as $subOrder)
                                <tr>
                                    <th scope="row">{{ $subOrder->id }}</th>
                                    <td>{{ optional($subOrder->user)->name }}</td>
                                    <td>{{ optional($subOrder->orderStatus)->status }}</td>
                                    <td>{{ optional($subOrder->market)->name }}</td>
                                    <td>{{ optional($subOrder->driver)->name }}</td>
                                    <td>{{ $subOrder->is_collected_from_driver == 1 ? "Yes" : "No"}}</td>
                                    <td>{{ $subOrder->driver_commission_amount }} <p class="small"> Distance
                                            : {{ round($subOrder->driver_total_distance,3) }} </p></td>
                                    <td>{{ $subOrder->owleto_commission_amount }}</td>
                                    <td>{{ $subOrder->delivery_fee }}</td>
                                    <td>{{ $subOrder->sub_total - $subOrder->owleto_commission_amount }}</td>
                                    <td>{{ optional($subOrder->paymentMethod)->name }}</td>
                                    <td>{{ optional($subOrder->deliveryType)->name }}</td>
                                    <td>{{ $subOrder->updated_at }}</td>
                                    <td>@include('orders.suborder_actions')</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
                <div class="row">
                    @include('orders.show_fields')
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
                        <a href="{!! route('orders.index') !!}" class="btn btn-default"><i
                                    class="fa fa-undo"></i> {{trans('lang.back')}}</a>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="drivers-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="post" action="{{url('assign-driver-to-orders')}}">
                    @csrf
                    <input type="hidden" name="order_id" id="order_id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Assign Driver</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <select data-plugin="select2" class="form-control" name="single_driver_id">
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

@push('scripts')
    <script type="text/javascript">
        $("#printOrder").on("click", function () {
            window.print();
        });
    </script>

    <script>
        $(function () {

            var $table = $('#tbl-suborder');

            $('#tbl-suborder').on('click', '.assign-driver', function (e) {
                e.preventDefault();
                var orderId = $(this).attr('data-id');
                $('#order_id').val(orderId);
                $('#drivers-modal').modal('show');
            });

            $('#tbl-suborder').on('click', '.collect-cash', function (e) {
                e.preventDefault();
                var orderId = $(this).attr('data-id');

                if (confirm("Cash collected from driver?") == true) {
                    $.ajax({
                        url: '{{ route('collected-cash.store') }}',
                        method: 'POST',
                        data: {
                            "_token": "{{ csrf_token() }}",
                            "orderId": orderId
                        },
                        success: function (response) {
                            $table.DataTable().draw();
                        }
                    })
                }
            });

            $('input[name="datetimes"]').daterangepicker({
                locale: {
                    format: 'D/M/Y'
                }
            }, function (start, end, label) {
                $("#start_date").val(start.format('YYYY-MM-DD'));
                $("#end_date").val(end.format('YYYY-MM-DD'));
            });
        })
    </script>

@endpush
