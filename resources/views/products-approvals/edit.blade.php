@extends('layouts.app')
@push('css_lib')
    <link rel="stylesheet" href="{{asset('plugins/iCheck/flat/blue.css')}}">
    <link rel="stylesheet" href="{{asset('plugins/select2/select2.min.css')}}">
    <link rel="stylesheet" href="{{asset('plugins/dropzone/bootstrap.min.css')}}">
@endpush
@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">{{trans('lang.product')}} - {{ $product->name }}</h1>
                </div>
                <div class="col-sm-6">

                </div>
            </div>
        </div>
    </div>
    <div class="content">
        <div class="clearfix"></div>
        @include('flash::message')
        @include('adminlte-templates::common.errors')
        <div class="clearfix"></div>
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
                    @can('product-approvals.index')
                        <li class="nav-item">
                            <a class="nav-link" href="{!! route('product-approvals.index') !!}"><i class="fa fa-list mr-2"></i>{{trans('lang.product_table')}}</a>
                        </li>
                    @endcan
                </ul>
            </div>
            <div class="card-body">
                {!! Form::model($product, ['route' => ['product-approvals.update', $product->id], 'method' => 'patch', 'id' => 'form-update']) !!}
                <div class="row">
                    @if(request()->user()->hasRole('admin'))
                        <div class="form-group row ">
                            {!! Form::label('tax', 'Tax*', ['class' => 'col-5 control-label text-left']) !!}
                            <div class="col-7">
                                {!! Form::number('tax', null,  ['class' => 'form-control','placeholder'=>  trans("lang.product_tax_placeholder"),'step'=>"any", 'min'=>"0"]) !!}
                                <div class="form-text text-muted">
                                    {{ trans("lang.product_tax_help") }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="row">
                    @if(request()->user()->hasRole('admin'))
                        <div class="form-group row ">
                            {!! Form::label('owleto_commission_percentage', 'Owleto Commission Percentage*', ['class' => 'col-3 control-label text-left']) !!}
                            <div class="col-7">
                                {!! Form::number('owleto_commission_percentage', null,  ['class' => 'form-control','placeholder'=>  trans("lang.owleto_commission_percentage_placeholder")]) !!}
                                <div class="form-text text-muted">
                                    {{ trans("lang.owleto_commission_percentage_help") }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="form-group col-12 text-right">
                    <button type="submit" class="btn btn-{{setting('theme_color')}} " id="button-submit"><i class="fa fa-save"></i> {{trans('lang.save')}} {{trans('lang.product')}}</button>
                    <a href="{!! route('products.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i> {{trans('lang.cancel')}}</a>
                </div>
                {!! Form::close() !!}
                <div class="clearfix"></div>
            </div>

        </div>
    </div>

@endsection