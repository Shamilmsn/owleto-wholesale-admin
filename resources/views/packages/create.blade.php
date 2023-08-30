@extends('layouts.app')

@push('css_lib')
    <link rel="stylesheet" href="{{asset('plugins/iCheck/flat/blue.css')}}">
    <link rel="stylesheet" href="{{asset('plugins/select2/select2.min.css')}}">
    <link rel="stylesheet" href="{{asset('plugins/summernote/summernote-bs4.css')}}">
    <link rel="stylesheet" href="{{asset('plugins/dropzone/bootstrap.min.css')}}">
@endpush

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">{{trans('lang.package_plural')}}</h1>
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
                    @can('packages.index')
                        <li class="nav-item">
                            <a class="nav-link" href="{!! route('packages.index') !!}"><i class="fa fa-list mr-2"></i>{{trans('lang.package_table')}}</a>
                        </li>
                    @endcan
                    <li class="nav-item">
                        <a class="nav-link active" href="{!! url()->current() !!}"><i class="fa fa-plus mr-2"></i>{{trans('lang.package_create')}}</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                {!! Form::open(['route' => 'packages.store', 'id' => 'form-create']) !!}
                <div class="row">
                    @if($customFields)
                        <h5 class="col-12 pb-4">{!! trans('lang.main_fields') !!}</h5>
                    @endif
                    <div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">

                        <div class="form-group row ">
                            {!! Form::label('name', trans("lang.package_name"), ['class' => 'col-3 control-label text-right']) !!}
                            <div class="col-9">
                                {!! Form::text('name', null,  ['class' => 'form-control','placeholder'=>  trans("lang.package_name_placeholder")]) !!}
                                <div class="form-text text-muted">
                                    {{ trans("lang.package_name_help") }}
                                </div>
                            </div>
                        </div>

                        <div class="form-group row ">
                            {!! Form::label('quantity', trans("lang.package_quantity"), ['class' => 'col-3 control-label text-right']) !!}
                            <div class="col-9">
                                {!! Form::number('quantity', null,  ['class' => 'form-control','placeholder'=>  trans("lang.package_quantity_placeholder"), 'step'=>"any", 'min'=>"0"]) !!}
                                <div class="form-text text-muted">
                                    {{ trans("lang.package_quantity_help") }}
                                </div>
                            </div>
                        </div>

                        <div class="form-group row ">
                            {!! Form::label('delivery_times[]', trans("lang.package_delivery_time"),['class' => 'col-3 control-label text-right']) !!}
                            <div class="col-9">
                                {!! Form::select('delivery_times[]', $deliveryTimes, $deliveryTimesSelected, ['class' => 'select2 form-control','multiple'=>'multiple']) !!}
                                <div class="form-text text-muted">{{ trans("lang.package_delivery_time_help") }}</div>
                            </div>
                        </div>

                        <div class="form-group row ">
                            {!! Form::label('market_id', trans("lang.package_market_id"),['class' => 'col-3 control-label text-right']) !!}
                            <div class="col-9">
                                <select  name="market_id" id="market_id" class=" form-control">
                                    @foreach ($markets as  $key => $market)
                                        <option value="{{ $key }}" >{{ $market}}</option>
                                    @endforeach
                                </select>

                                <div class="form-text text-muted">{{ trans("lang.package_market_id_help") }}</div>
                            </div>
                        </div>

                        <div class="form-group row ">
                            {!! Form::label('product_id', trans("lang.package_product_id"),['class' => 'col-3 control-label text-right']) !!}
                            <div class="col-9">
                                <select name="product_id" id="product_id" class=" form-control">

                                </select>
                                <div class="form-text text-muted">{{ trans("lang.package_product_id_help") }}</div>
                            </div>
                        </div>
                        <div class="form-group row ">
                            {!! Form::label('package_days[]', trans("lang.package_days"),['class' => 'col-3 control-label text-right']) !!}
                                <div class="col-9">
                                    {!! Form::select('package_days[]', $packageDays, $packageDaysSelected , ['class' => 'select2 form-control' , 'multiple'=>'multiple','id' =>'package_day_Id']) !!}
                                    <div class="form-text text-muted">{{ trans("lang.package_days_help") }}</div>
                                </div>
                        </div>
                    </div>

                    <div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">
                        <div class="form-group row ">
                            {!! Form::label('days', trans("lang.no_of_package_days"), ['class' => 'col-3 control-label text-right']) !!}
                            <div class="col-9">
                                {!! Form::number('days', null,  ['class' => 'form-control','placeholder'=>  trans("lang.package_days_placeholder"),'step'=>"any", 'min'=>"0",'id' => 'dayId']) !!}
                                <div class="form-text text-muted">
                                    {{ trans("lang.package_days_help") }}
                                </div>
                            </div>
                        </div>

                        <div class="form-group row ">
                            {!! Form::label('price', trans("lang.package_price"), ['class' => 'col-3 control-label text-right']) !!}
                            <div class="col-9">
                                {!! Form::number('price', null,  ['class' => 'form-control','placeholder'=>  trans("lang.package_price_placeholder"),'step'=>"any", 'min'=>"0"]) !!}
                                <div class="form-text text-muted">
                                    {{ trans("lang.package_price_help") }}
                                </div>
                            </div>
                        </div>

                        <div class="form-group row ">
                            {!! Form::label('actual_price', trans("lang.actual_price"), ['class' => 'col-3 control-label text-right']) !!}
                            <div class="col-9">
                                {!! Form::number('actual_price', null,  ['class' => 'form-control','placeholder'=>  trans("lang.actual_price_placeholder"),'step'=>"any", 'min'=>"0"]) !!}
                                <div class="form-text text-muted">
                                    {{ trans("lang.actual_price_help") }}
                                </div>
                            </div>
                        </div>

                        <!-- Description Field -->
                        <div class="form-group row ">
                            {!! Form::label('description', trans("lang.category_description"), ['class' => 'col-3 control-label text-right']) !!}
                            <div class="col-9">
                                {!! Form::textarea('description', null, ['class' => 'form-control','placeholder'=>
                                 trans("lang.category_description_placeholder")  ]) !!}
                                <div class="form-text text-muted">{{ trans("lang.category_description_help") }}</div>
                            </div>
                        </div>
                    </div>

                    @if($customFields)
                        <div class="clearfix"></div>
                        <div class="col-12 custom-field-container">
                            <h5 class="col-12 pb-4">{!! trans('lang.custom_field_plural') !!}</h5>
                            {!! $customFields !!}
                        </div>
                    @endif

                    <div class="form-group col-12 text-right">
                        <button type="submit" class="btn btn-{{setting('theme_color')}}"><i class="fa fa-save"></i> {{trans('lang.save')}} {{trans('lang.package')}}</button>
                        <a href="{!! route('packages.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i> {{trans('lang.cancel')}}</a>
                    </div>

                </div>

                {!! Form::close() !!}
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
    @include('layouts.media_modal')
@endsection
@push('scripts_lib')
    <script src="{{asset('plugins/iCheck/icheck.min.js')}}"></script>
    <script src="{{asset('plugins/select2/select2.min.js')}}"></script>
{{--    <script src="{{asset('plugins/summernote/summernote-bs4.min.js')}}"></script>--}}
    <script src="{{asset('plugins/dropzone/dropzone.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/additional-methods.js"> </script>
    <script type="text/javascript">
        Dropzone.autoDiscover = false;
        var dropzoneFields = [];
        $("#dayId").click(function(){
            var package_day = $("#package_day_Id :selected").length;
            console.log(package_day);
            $.validator.addMethod('divisible', function(value, element) {
                return parseInt(value) % package_day == 0
            }, 'Number must be divisible by '+ package_day);

        });

            var MarketID = $('#market_id').val();
            MarketProducts(MarketID);

            $('select[name="market_id"]').on('change', function () {
                var MarketID = $(this).val();
                MarketProducts(MarketID);
            });

            function MarketProducts(MarketID) {
                if (MarketID) {
                    $.ajax({
                        url: '/market_products/ajax/' + MarketID,
                        type: "GET",
                        dataType: "json",
                        success: function (data) {
                            // $('#product_id').select2('val','');
                            $('select[name="product_id"]').empty();
                            $.each(data, function (key, value) {
                                if(value.product_type == 2 ) {
                                    var variant_name = value.variant_name;
                                }else {
                                    var variant_name = ' ';
                                }

                                $('select[name="product_id"]').append('<option value="' + value.id + '">' +  value.base_name +' '+ variant_name  + '</option>');
                            });

                        }
                    });
                } else {
                    $('select[name="product_id"]').empty();
                }
            }

        $('#form-create').validate({
            rules:{
                'days': {
                    required: true,
                    number: true,
                    divisible: true
                }
            },
            messages:{
                'days':{
                    required: 'This field is required.',
                    number: 'Numbers only in this field.',
                }
            }
        });
    </script>


@endpush