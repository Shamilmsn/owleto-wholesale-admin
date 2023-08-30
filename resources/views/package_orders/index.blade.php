@extends('layouts.app')

@section('content')

    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">{{trans('lang.package_order_plural')}}</h1>
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
                        <a class="nav-link active" href="{!! url()->current() !!}"><i class="fa fa-list mr-2"></i>{{trans('lang.package_order_table')}}</a>
                    </li>

                    @include('layouts.right_toolbar', compact('dataTable'))
                </ul>
            </div>
            <div class="card-body table-responsive">
{{--               <div class="d-flex justify-content-end">--}}
{{--                   <div class="col-3">--}}
{{--                       <input type="text" class="form-control" placeholder="Search here...." id="search">--}}
{{--                   </div>--}}
{{--               </div>--}}
                @include('package_orders.table')
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
@endsection

{{--@push('scripts')--}}
{{--    <script>--}}
{{--        $(function (){--}}
{{--            var $table = $('#order-details');--}}

{{--            $table.on('preXhr.dt', function (e, settings, data) {--}}
{{--                data.filter = {--}}
{{--                    q: $('#search').val(),--}}
{{--                };--}}
{{--            });--}}

{{--            $('body').on('keyup','#search',function (e) {--}}
{{--                e.preventDefault();--}}
{{--                $table.DataTable().draw();--}}
{{--            });--}}
{{--        })--}}
{{--    </script>--}}
{{--@endpush--}}

