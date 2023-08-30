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
                    <h1 class="m-0 text-dark">Flash Sales</h1>
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
                    @can('flash-sales')
                        <li class="nav-item">
                            <a class="nav-link" href="{!! route('flash-sales.index') !!}"><i class="fa fa-list mr-2"></i>{{trans('lang.product_table')}}</a>
                        </li>
                    @endcan
                    <li class="nav-item">
                        <a class="nav-link active" href="{!! url()->current() !!}"><i class="fa fa-plus mr-2"></i>{{trans('lang.product_create')}}</a>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                <form method="POST"
                      action="{{route('flash-sales.update', $product->id)}}"
                      id="form-create">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="form column">
                            <div class="form-group row ">
                                {!! Form::label('product_id', 'Product*',['class' => 'col-3 control-label text-right']) !!}
                                <div class="col-9">
                                    <select class="form-control select2 " id="product_id" name="product_id">
                                        @foreach($products as $data)
                                            <option value="{{$data->id}}"
                                                    @if($data->id == $product->id) selected @endif>
                                                {{$product->name}}</option>
                                        @endforeach
                                    </select>
                                    <div class="form-text text-muted">Select Product</div>
                                </div>
                            </div>

                            <div class="form-group row ">
                                <div class="input-group mb-3">
                                    {!! Form::label('flash_sale_start_time', trans("lang.product_flash_sale_start_time"),['class' => 'col-3 control-label text-right ']) !!}
                                    <div class="col-6">
                                        <input type="datetime-local" name="flash_sale_start_time"  class="form-control @error('flash_sale_start_time')
                        is-invalid @enderror" id="flash_sale_start_time" value="{{$product->flash_sale_start_time}}">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row ">
                                <div class="input-group mb-3">
                                    {!! Form::label('flash_sale_end_time', trans("lang.product_flash_sale_end_time"),['class' => 'col-3 control-label text-right ']) !!}
                                    <div class="col-6">
                                        <input type="datetime-local" name="flash_sale_end_time"  class="form-control @error('flash_sale_end_time')
                        is-invalid @enderror" id="flash_sale_end_time" value="{{$product->flash_sale_end_time}}">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row ">
                                <div class="input-group row ">
                                    {!! Form::label('flash_sale_price', trans("lang.product_flash_sale_price"),['class' => 'col-3 control-label text-right ']) !!}
                                    <div class="col-6 ml-2">
                                        {!! Form::number('flash_sale_price', $product->flash_sale_price, ['class' => 'form-control','id' => 'flash_sale_price']) !!}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-12 text-right">
                            <button type="submit" class="btn btn-{{setting('theme_color')}} "
                                    id="button-submit"><i class="fa fa-save"></i> {{trans('lang.save')}}
                                {{trans('lang.product')}}</button>
                            <a href="{!! route('products.index') !!}" class="btn btn-default">
                                <i class="fa fa-undo"></i> {{trans('lang.cancel')}}</a>
                        </div>
                    </div>
                </form>

                {!! Form::close() !!}
                <div class="clearfix"></div>
            </div>
        </div>
    </div>

@endsection
@push('scripts_lib')
    <script src="{{asset('plugins/iCheck/icheck.min.js')}}"></script>
    <script src="{{asset('plugins/select2/select2.min.js')}}"></script>
    <script src="{{asset('plugins/dropzone/dropzone.js')}}"></script>
    <script type="text/javascript">
        Dropzone.autoDiscover = false;
        var dropzoneFields = [];
    </script>
@endpush

