@extends('layouts.app')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">{{trans('lang.pickup_delivery_order_request_plural')}}</h1>
                </div>
            </div>
        </div>
    </div>
    <div class="content">
        @include('flash::message')
        <div class="card">
            <div class="card-header d-print-none">
                <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
                    <li class="nav-item">
                        <a class="nav-link" href="{!! route('pickup-delivery-order-requests.index') !!}"><i class="fa fa-list mr-2"></i>{{trans('lang.pickup_delivery_order_request_table')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="{!! url()->current() !!}"><i class="fa fa-plus mr-2"></i>{{trans('lang.pickup_delivery_order_request_table')}}</a>
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
                    @include('pickup-delivery-order-requests.show_fields')
                </div>
                <div class="clearfix"></div>
                <div class="row d-print-none">
                    <div class="form-group col-12 text-right">
                        <a href="{!! route('pickup-delivery-order-requests.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i> {{trans('lang.back')}}</a>
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
