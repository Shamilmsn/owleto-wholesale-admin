@extends('layouts.app')
@push('css_lib')
<!-- iCheck -->
<link rel="stylesheet" href="{{asset('plugins/iCheck/flat/blue.css')}}">
<!-- select2 -->
<link rel="stylesheet" href="{{asset('plugins/select2/select2.min.css')}}">
<!-- bootstrap wysihtml5 - text editor -->
{{--<link rel="stylesheet" href="{{asset('plugins/summernote/summernote-bs4.css')}}">--}}
{{--dropzone--}}
<link rel="stylesheet" href="{{asset('plugins/dropzone/bootstrap.min.css')}}">
@endpush

@section('content')
<!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0 text-dark">{{trans('lang.market_plural')}}</h1>
        </div>
        <div class="col-sm-6">

        </div>
      </div>
    </div>
  </div>
<style>
    .error {
        color: #f44336 !important;
        text-decoration: none;
        font-weight: normal !important;
    }
</style>
<div class="content">
  <div class="clearfix"></div>
  @include('flash::message')
  @include('adminlte-templates::common.errors')
  <div class="clearfix"></div>
  <div class="card">
    <div class="card-header">
      <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
        @can('markets.index')
        <li class="nav-item">
          <a class="nav-link" href="{!! route('markets.index') !!}"><i class="fa fa-list mr-2"></i>{{trans('lang.market_table')}}</a>
        </li>
        @endcan
        <li class="nav-item">
          <a class="nav-link active" href="{!! url()->current() !!}"><i class="fa fa-plus mr-2"></i>{{trans('lang.market_create')}}</a>
        </li>
      </ul>
    </div>
    <div class="card-body">
      {!! Form::open(['route' => 'markets.store',  'id' =>'form-create'] ) !!}
      <div class="row">
          @if($customFields)
              <h5 class="col-12 pb-4">{!! trans('lang.main_fields') !!}</h5>
          @endif
          <div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">
              <!-- Name Field -->
              <div class="form-group row ">
                  {!! Form::label('name', 'Name*', ['class' => 'col-3 control-label text-right']) !!}
                  <div class="col-9">
                      {!! Form::text('name', null,  ['class' => 'form-control','placeholder'=>  trans("lang.market_name_placeholder")]) !!}
                      <div class="form-text text-muted">
                          {{ trans("lang.market_name_help") }}
                      </div>
                  </div>
              </div>

              <div class="form-group row ">
                  {!! Form::label('city_id', 'City*',['class' => 'col-3 control-label text-right']) !!}
                  <div class="col-9">
                      {!! Form::select('city_id', $cities, null, ['class' => 'select2 form-control', 'id' => 'city_id']) !!}
                      <div class="form-text text-muted">{{ trans("lang.city_id_help") }}</div>
                  </div>
              </div>

              <div class="form-group row ">
                  {!! Form::label('area_id', 'Area*',['class' => 'col-3 control-label text-right']) !!}
                  <div class="col-9">
                      <select name="area_id" id="area_id" class=" form-control">
                      </select>
                      <div class="form-text text-muted">{{ trans("lang.area_id_help") }}</div>
                  </div>
              </div>

              <!-- fields Field -->
              <div class="form-group row ">
                  {!! Form::label('primary_sector_id', 'Primary sector*',['class' => 'col-3 control-label text-right']) !!}
                  <div class="col-9">
                      {!! Form::select('primary_sector_id', $fields, null, ['class' => 'select2 form-control', 'id' => 'primary_sector_id']) !!}
                      <div class="form-text text-muted">{{ trans("lang.primary_sector_id_help") }}</div>
                  </div>
              </div>

              <div class="form-group row ">
                  {!! Form::label('fields[]', trans("lang.market_fields"),['class' => 'col-3 control-label text-right']) !!}
                  <div class="col-9">
                      {!! Form::select('fields[]', $fields, $fieldsSelected, ['class' => 'select2 form-control', 'id' => 'field_id', 'multiple'=>'multiple']) !!}
                      <div class="form-text text-muted">{{ trans("lang.market_fields_help") }}</div>
                  </div>

              </div>
              <div class="form-group row ">
                  {!! Form::label('categories[]', 'Categories *',['class' => 'col-3 control-label text-right']) !!}
                  <div class="col-9">
                      <select id="category_id" class="select2 form-control" name="categories[]" multiple="multiple">
                      </select>
                      <div class="form-text text-muted">{{ trans("lang.market_categories_help") }}</div>
                  </div>
              </div>

              @hasanyrole('admin|manager')
              <!-- Users Field -->
          {{--    <div class="form-group row ">--}}
          {{--        {!! Form::label('drivers[]', trans("lang.market_drivers"),['class' => 'col-3 control-label text-right']) !!}--}}
          {{--        <div class="col-9">--}}
          {{--            {!! Form::select('drivers[]', $drivers, $driversSelected, ['class' => 'select2 form-control' , 'multiple'=>'multiple']) !!}--}}
          {{--            <div class="form-text text-muted">{{ trans("lang.market_drivers_help") }}</div>--}}
          {{--        </div>--}}
          {{--    </div>--}}
          <!-- delivery_fee Field -->
          {{--    <div class="form-group row ">--}}
          {{--        {!! Form::label('delivery_fee', trans("lang.market_delivery_fee"), ['class' => 'col-3 control-label text-right']) !!}--}}
          {{--        <div class="col-9">--}}
          {{--            {!! Form::number('delivery_fee', null,  ['class' => 'form-control','step'=>'any','placeholder'=>  trans("lang.market_delivery_fee_placeholder")]) !!}--}}
          {{--            <div class="form-text text-muted">--}}
          {{--                {{ trans("lang.market_delivery_fee_help") }}--}}
          {{--            </div>--}}
          {{--        </div>--}}
          {{--    </div>--}}

          <!-- delivery_range Field -->
              <div class="form-group row ">
                  {!! Form::label('delivery_range', 'Delivery Range (KM)*', ['class' => 'col-3 control-label text-right']) !!}
                  <div class="col-9">
                      {!! Form::number('delivery_range', null,  ['class' => 'form-control', 'step'=>'any','placeholder'=>  'Enter the delivery range']) !!}
                      <div class="form-text text-muted">
                          {{ trans("lang.market_delivery_range_help") }}
                      </div>
                  </div>
              </div>

              <!-- default_tax Field -->
              <div class="form-group row ">
                  {!! Form::label('default_tax', trans("lang.market_default_tax"), ['class' => 'col-3 control-label text-right']) !!}
                  <div class="col-9">
                      {!! Form::number('default_tax', null,  ['class' => 'form-control', 'step'=>'any','placeholder'=>  'Enter the tax']) !!}
                      <div class="form-text text-muted">
                          {{ trans("lang.market_default_tax_help") }}
                      </div>
                  </div>
              </div>

              @endhasanyrole

              <!-- Mobile Field -->
              <div class="form-group row ">
                  {!! Form::label('mobile', 'Mobile*', ['class' => 'col-3 control-label text-right']) !!}
                  <div class="col-9">
                      {!! Form::number('mobile', null,  ['class' => 'form-control','placeholder'=>  trans("lang.market_mobile_placeholder")]) !!}
                      <div class="form-text text-muted">
                          {{ trans("lang.market_mobile_help") }}
                      </div>
                  </div>
              </div>

              <!-- Address Field -->
              <div class="form-group row ">
                  {!! Form::label('address', 'Address*', ['class' => 'col-3 control-label text-right']) !!}
                  <div class="col-9">
                      {!! Form::text('address', null,  ['class' => 'form-control','placeholder'=>  trans("lang.market_address_placeholder")]) !!}
                      <div class="form-text text-muted">
                          {{ trans("lang.market_address_help") }}
                      </div>
                  </div>
              </div>

              <!-- Latitude Field -->
{{--              <div class="form-group row ">--}}
{{--                  {!! Form::label('latitude', trans("lang.market_latitude"), ['class' => 'col-3 control-label text-right']) !!}--}}
{{--                  <div class="col-9">--}}
{{--                      {!! Form::text('latitude', null,  ['class' => 'form-control','placeholder'=>  trans("lang.market_latitude_placeholder")]) !!}--}}
{{--                      <div class="form-text text-muted">--}}
{{--                          {{ trans("lang.market_latitude_help") }}--}}
{{--                      </div>--}}
{{--                  </div>--}}
{{--              </div>--}}

              <!-- Longitude Field -->
{{--              <div class="form-group row ">--}}
{{--                  {!! Form::label('longitude', trans("lang.market_longitude"), ['class' => 'col-3 control-label text-right']) !!}--}}
{{--                  <div class="col-9">--}}
{{--                      {!! Form::text('longitude', null,  ['class' => 'form-control','placeholder'=>  trans("lang.market_longitude_placeholder")]) !!}--}}
{{--                      <div class="form-text text-muted">--}}
{{--                          {{ trans("lang.market_longitude_help") }}--}}
{{--                      </div>--}}
{{--                  </div>--}}
{{--              </div>--}}
              <div class="form-group row ">
                  {!! Form::label('paymetMethods[]', 'Payment Method*',['class' => 'col-3 control-label text-right']) !!}
                  <div class="col-9">
                      {!! Form::select('paymetMethods[]', $paymetMethods, $paymetMethodsSelected, ['class' => 'select2 form-control' , 'multiple'=>'multiple']) !!}
                      <div class="form-text text-muted">{{ trans("lang.market_payment_method_help") }}</div>
                  </div>
              </div>

              <div class="form-group row ">
                  {!! Form::label('order_request_commission_amount', 'Order Commission Percentage*',['class' => 'col-3 control-label text-right']) !!}
                  <div class="col-9">
                      {!! Form::number('order_request_commission_amount', null,  ['class' => 'form-control',
                              'placeholder'=>  trans("lang.order_request_commission_amount_placeholder")]) !!}
                      <div class="form-text text-muted">
                          {{ trans("lang.order_request_commission_amount_placeholder") }}
                      </div>
                  </div>
              </div>

              <div class="form-group row ">
                  {!! Form::label('minimum_cart_amount', 'Minimum Cart Amount',['class' => 'col-3 control-label text-right']) !!}
                  <div class="col-9">
                      {!! Form::number('minimum_cart_amount', null,  ['class' => 'form-control',
                              'placeholder'=>  'Enter the minimum cart amount']) !!}
                      <div class="form-text text-muted">
                          Enter the minimum cart amount
                      </div>
                  </div>
              </div>

              <div class="form-group row ">
                  {!! Form::label('closed', trans("lang.market_closed"),['class' => 'col-3 control-label text-right']) !!}
                  <div class="checkbox">
                      <label class="col-9 ml-2 form-check-inline">
                          <div class="form-check">
                              <input class="form-check-input" type="hidden" name="closed" value="0" >
                              <input class="form-check-input" name="closed" type="checkbox" value="1" id="closed_vendor" @if(old('closed') == 'checked') checked @endif>
                          </div>
                      </label>
                  </div>
              </div>

              <div class="form-group row ">
                  {!! Form::label('available_for_delivery', trans("lang.market_available_for_delivery"),['class' => 'col-3 control-label text-right']) !!}
                  <div class="checkbox">
                      <label class="col-9 ml-2 form-check-inline">
                          <div class="form-check">
                              <input class="form-check-input" type="hidden" name="available_for_delivery" value="0" >
                              <input class="form-check-input" name="available_for_delivery" type="checkbox" value="1" id="available_for_delivery" @if(old('available_for_delivery') == 'checked') checked @endif>
                          </div>
                      </label>
                  </div>
              </div>

              <div id="insta-container" class="d-none">
                  <div class="form-group row ">
                      {!! Form::label('insta_name', trans("lang.insta_name"), ['class' => 'col-3 control-label text-right']) !!}
                      <div class="col-9">
                          {!! Form::text('insta_name', null,  ['class' => 'form-control','placeholder'=>  trans("lang.insta_name_placeholder")]) !!}
                          <div class="form-text text-muted">
                              {{ trans("lang.insta_name_help") }}
                          </div>
                      </div>
                  </div>
                  <div class="form-group row ">
                      {!! Form::label('location', trans("lang.insta_location"), ['class' => 'col-3 control-label text-right']) !!}
                      <div class="col-9">
                          {!! Form::text('location', null,  ['class' => 'form-control','placeholder'=>  trans("lang.insta_location_placeholder")]) !!}
                          <div class="form-text text-muted">
                              {{ trans("lang.insta_location_help") }}
                          </div>
                      </div>
                  </div>

                  <div class="form-group row ">
                      {!! Form::label('about', trans("lang.insta_about"), ['class' => 'col-3 control-label text-right']) !!}
                      <div class="col-9">
                          {!! Form::textarea('about', null, ['class' => 'form-control','placeholder'=>
                           trans("lang.insta_about_placeholder")  ]) !!}
                          <div class="form-text text-muted">{{ trans("lang.insta_about_help") }}</div>
                      </div>
                  </div>

                  <div class="form-group row">
                      {!! Form::label('insta_profile_pic', trans("lang.insta_profile_pic"), ['class' => 'col-3 control-label text-right']) !!}
                      <div class="col-9">
                          <div style="width: 100%" class="dropzone insta_profile_pic" id="insta_profile_pic" data-field="insta_profile_pic">
                              <input type="hidden" name="insta_profile_pic">
                          </div>
                          <a href="#loadMediaModal" data-dropzone="insta_profile_pic" data-toggle="modal" data-target="#mediaModal" class="btn btn-outline-{{setting('theme_color','primary')}} btn-sm float-right mt-1">{{ trans('lang.media_select')}}</a>
                          <div class="form-text text-muted w-50">
                              {{ trans("lang.insta_profile_pic_help") }}
                          </div>
                      </div>
                  </div>
                  @prepend('scripts')
                      <script type="text/javascript">
                          var var15671147011688676454ble = '';
                          @if(isset($market) && $market->hasMedia('insta_profile_pic'))
                              var15671147011688676454ble = {
                              name: "{!! $market->getFirstMedia('insta_profile_pic')->name !!}",
                              size: "{!! $market->getFirstMedia('insta_profile_pic')->size !!}",
                              type: "{!! $market->getFirstMedia('insta_profile_pic')->mime_type !!}",
                              collection_name: "{!! $market->getFirstMedia('insta_profile_pic')->collection_name !!}"
                          };
                          @endif
                          var dz_var15671147011688676454ble = $(".dropzone.insta_profile_pic").dropzone({
                              url: "{!!url('uploads/store')!!}",
                              addRemoveLinks: true,
                              maxFiles: 1,
                              init: function () {
                                  @if(isset($market) && $market->hasMedia('insta_profile_pic'))
                                  dzInit(this, var15671147011688676454ble, '{!! url($market->getFirstMediaUrl('insta_profile_pic','thumb')) !!}')
                                  @endif
                              },
                              accept: function (file, done) {
                                  dzAccept(file, done, this.element, "{!!config('medialibrary.icons_folder')!!}");
                              },
                              sending: function (file, xhr, formData) {
                                  dzSending(this, file, formData, '{!! csrf_token() !!}');
                              },
                              maxfilesexceeded: function (file) {
                                  dz_var15671147011688676454ble[0].mockFile = '';
                                  dzMaxfile(this, file);
                              },
                              complete: function (file) {
                                  dzComplete(this, file, var15671147011688676454ble, dz_var15671147011688676454ble[0].mockFile);
                                  dz_var15671147011688676454ble[0].mockFile = file;
                              },
                              removedfile: function (file) {
                                  dzRemoveFile(
                                      file, var15671147011688676454ble, '{!! url("markets/remove-media") !!}',
                                      'insta_profile_pic', '{!! isset($market) ? $market->id : 0 !!}', '{!! url("uplaods/clear") !!}', '{!! csrf_token() !!}'
                                  );
                              }
                          });
                          dz_var15671147011688676454ble[0].mockFile = var15671147011688676454ble;
                          dropzoneFields['insta_profile_pic'] = dz_var15671147011688676454ble;
                      </script>
                  @endprepend

                  <div class="form-group row">
                      {!! Form::label('insta_cover_pic', trans("lang.insta_cover_pic"), ['class' => 'col-3 control-label text-right']) !!}
                      <div class="col-9">
                          <div style="width: 100%" class="dropzone insta_cover_pic" id="insta_cover_pic" data-field="insta_cover_pic">
                              <input type="hidden" name="insta_cover_pic">
                          </div>
                          <a href="#loadMediaModal" data-dropzone="insta_cover_pic" data-toggle="modal" data-target="#mediaModal" class="btn btn-outline-{{setting('theme_color','primary')}} btn-sm float-right mt-1">{{ trans('lang.media_select')}}</a>
                          <div class="form-text text-muted w-50">
                              {{ trans("lang.insta_cover_pic_help") }}
                          </div>
                      </div>
                  </div>
                  @prepend('scripts')
                      <script type="text/javascript">
                          var var15671147011688676454ble = '';
                          @if(isset($market) && $market->hasMedia('insta_cover_pic'))
                              var15671147011688676454ble = {
                              name: "{!! $market->getFirstMedia('insta_cover_pic')->name !!}",
                              size: "{!! $market->getFirstMedia('insta_cover_pic')->size !!}",
                              type: "{!! $market->getFirstMedia('insta_cover_pic')->mime_type !!}",
                              collection_name: "{!! $market->getFirstMedia('insta_cover_pic')->collection_name !!}"
                          };
                          @endif
                          var dz_var15671147011688676454ble = $(".dropzone.insta_cover_pic").dropzone({
                              url: "{!!url('uploads/store')!!}",
                              addRemoveLinks: true,
                              maxFiles: 1,
                              init: function () {
                                  @if(isset($market) && $market->hasMedia('insta_cover_pic'))
                                  dzInit(this, var15671147011688676454ble, '{!! url($market->getFirstMediaUrl('insta_cover_pic','thumb')) !!}')
                                  @endif
                              },
                              accept: function (file, done) {
                                  dzAccept(file, done, this.element, "{!!config('medialibrary.icons_folder')!!}");
                              },
                              sending: function (file, xhr, formData) {
                                  dzSending(this, file, formData, '{!! csrf_token() !!}');
                              },
                              maxfilesexceeded: function (file) {
                                  dz_var15671147011688676454ble[0].mockFile = '';
                                  dzMaxfile(this, file);
                              },
                              complete: function (file) {
                                  dzComplete(this, file, var15671147011688676454ble, dz_var15671147011688676454ble[0].mockFile);
                                  dz_var15671147011688676454ble[0].mockFile = file;
                              },
                              removedfile: function (file) {
                                  dzRemoveFile(
                                      file, var15671147011688676454ble, '{!! url("markets/remove-media") !!}',
                                      'insta_cover_pic', '{!! isset($market) ? $market->id : 0 !!}', '{!! url("uplaods/clear") !!}', '{!! csrf_token() !!}'
                                  );
                              }
                          });
                          dz_var15671147011688676454ble[0].mockFile = var15671147011688676454ble;
                          dropzoneFields['insta_cover_pic'] = dz_var15671147011688676454ble;
                      </script>
                  @endprepend
              </div>

          </div>
          <div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">

              <!-- Image Field -->
              <div class="form-group row" id="image-row">
                  {!! Form::label('image', trans("lang.market_image"), ['class' => 'col-3 control-label text-right']) !!}
                  <div class="col-9">
                      <div style="width: 100%" class="dropzone image" id="image" data-field="image">
                          <input type="hidden" name="image">
                      </div>
                      <a href="#loadMediaModal" data-dropzone="image" data-toggle="modal" data-target="#mediaModal" class="btn btn-outline-{{setting('theme_color','primary')}} btn-sm float-right mt-1">{{ trans('lang.media_select')}}</a>
                      <div class="form-text text-muted w-50">
                          {{ trans("lang.market_image_help") }}
                      </div>
                  </div>
              </div>
              @prepend('scripts')
                  <script type="text/javascript">
                      var var15671147011688676454ble = '';
                      @if(isset($market) && $market->hasMedia('image'))
                          var15671147011688676454ble = {
                          name: "{!! $market->getFirstMedia('image')->name !!}",
                          size: "{!! $market->getFirstMedia('image')->size !!}",
                          type: "{!! $market->getFirstMedia('image')->mime_type !!}",
                          collection_name: "{!! $market->getFirstMedia('image')->collection_name !!}"
                      };
                      @endif
                      var dz_var15671147011688676454ble = $(".dropzone.image").dropzone({
                          url: "{!!url('uploads/store')!!}",
                          addRemoveLinks: true,
                          maxFiles: 1,
                          init: function () {
                              @if(isset($market) && $market->hasMedia('image'))
                              dzInit(this, var15671147011688676454ble, '{!! url($market->getFirstMediaUrl('image','thumb')) !!}')
                              @endif
                          },
                          accept: function (file, done) {
                              dzAccept(file, done, this.element, "{!!config('medialibrary.icons_folder')!!}");
                          },
                          sending: function (file, xhr, formData) {
                              dzSending(this, file, formData, '{!! csrf_token() !!}');
                          },
                          maxfilesexceeded: function (file) {
                              dz_var15671147011688676454ble[0].mockFile = '';
                              dzMaxfile(this, file);
                          },
                          complete: function (file) {
                              dzComplete(this, file, var15671147011688676454ble, dz_var15671147011688676454ble[0].mockFile);
                              dz_var15671147011688676454ble[0].mockFile = file;
                          },
                          removedfile: function (file) {
                              dzRemoveFile(
                                  file, var15671147011688676454ble, '{!! url("markets/remove-media") !!}',
                                  'image', '{!! isset($market) ? $market->id : 0 !!}', '{!! url("uplaods/clear") !!}', '{!! csrf_token() !!}'
                              );
                          }
                      });
                      dz_var15671147011688676454ble[0].mockFile = var15671147011688676454ble;
                      dropzoneFields['image'] = dz_var15671147011688676454ble;
                  </script>
          @endprepend

          <!-- Description Field -->
              <div class="form-group row ">
                  {!! Form::label('description', trans("lang.market_description"), ['class' => 'col-3 control-label text-right']) !!}
                  <div class="col-9">
                      {!! Form::textarea('description', null, ['class' => 'form-control','placeholder'=>
                       trans("lang.market_description_placeholder")  ]) !!}
                      <div class="form-text text-muted">{{ trans("lang.market_description_help") }}</div>
                  </div>
              </div>
              <!-- Information Field -->
              <div class="form-group row ">
                  {!! Form::label('information', trans("lang.market_information"), ['class' => 'col-3 control-label text-right']) !!}
                  <div class="col-9">
                      {!! Form::textarea('information', null, ['class' => 'form-control','placeholder'=>
                       trans("lang.market_information_placeholder")  ]) !!}
                      <div class="form-text text-muted">{{ trans("lang.market_information_help") }}</div>
                  </div>
              </div>

              <div class="form-group row ">
                  {!! Form::label('location', 'Location*', ['class' => 'col-3 control-label text-right']) !!}
                  <div class="col-9">
                      <input type="text" class="form-control" placeholder="Choose location" id="user-location"
                             name="user-location" required/>
                      <div class="mt-2" id="locationpicker" style="width: 500px; height: 400px;"></div>
                     <input type="hidden" id="latitude" name="latitude"/>
                     <input type="hidden" id="longitude" name="longitude"/>
                  </div>
              </div>

          </div>

          @hasrole('admin')
          <div class="col-12 custom-field-container">
              <h5 class="col-12 pb-4">{!! trans('lang.admin_area') !!}</h5>
              <div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">
                  <!-- Users Field -->
                  <div class="form-group row ">
                      {!! Form::label('users[]', 'Vendor Owners*',['class' => 'col-3 control-label text-right']) !!}
                      <div class="col-9">
                          {!! Form::select('users[]', $user, $usersSelected, ['class' => 'select2 form-control' , 'multiple'=>'multiple']) !!}
                          <div class="form-text text-muted">{{ trans("lang.market_users_help") }}</div>
                      </div>
                  </div>

              </div>
              <div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">
                  <!-- admin_commission Field -->
                  {{--        <div class="form-group row ">--}}
                  {{--            {!! Form::label('admin_commission', trans("lang.market_admin_commission"), ['class' => 'col-3 control-label text-right']) !!}--}}
                  {{--            <div class="col-9">--}}
                  {{--                {!! Form::number('admin_commission', null,  ['class' => 'form-control', 'step'=>'any', 'placeholder'=>  trans("lang.market_admin_commission_placeholder")]) !!}--}}
                  {{--                <div class="form-text text-muted">--}}
                  {{--                    {{ trans("lang.market_admin_commission_help") }}--}}
                  {{--                </div>--}}
                  {{--            </div>--}}
                  {{--        </div>--}}
                  <div class="form-group row ">
                      {!! Form::label('active', trans("lang.market_active"),['class' => 'col-3 control-label text-right']) !!}
                      <div class="checkbox icheck">
                          <label class="col-9 ml-2 form-check-inline">
                              {!! Form::hidden('active', 0) !!}
                              {!! Form::checkbox('active', 1, null) !!}
                          </label>
                      </div>
                  </div>
              </div>
          </div>
          @endhasrole

          @if($customFields)
              <div class="clearfix"></div>
              <div class="col-12 custom-field-container">
                  <h5 class="col-12 pb-4">{!! trans('lang.custom_field_plural') !!}</h5>
                  {!! $customFields !!}
              </div>
      @endif
      <!-- Submit Field -->
          <div class="form-group col-12 text-right">
              <button type="submit" class="btn btn-{{setting('theme_color')}}"><i class="fa fa-save"></i> {{trans('lang.save')}} {{trans('lang.market')}}</button>
              <a href="{!! route('markets.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i> {{trans('lang.cancel')}}</a>
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
<!-- iCheck -->
<script src="{{asset('plugins/iCheck/icheck.min.js')}}"></script>
<!-- select2 -->
<script src="{{asset('plugins/select2/select2.min.js')}}"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
{{--<script src="{{asset('plugins/summernote/summernote-bs4.min.js')}}"></script>--}}
{{--dropzone--}}
<script src="{{asset('plugins/dropzone/dropzone.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/additional-methods.js"> </script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-locationpicker/0.1.12/locationpicker.jquery.js"></script>
<script type="text/javascript" src='https://maps.google.com/maps/api/js?libraries=places&key={{ config('services.google.map_api_key') }}' ></script>
<script type="text/javascript">
    Dropzone.autoDiscover = false;
    var dropzoneFields = [];
</script>
<script>
    $(function (){

        $('#closed_vendor').on('change',function() {
            if(this.checked == true) {
                $("#available_for_delivery").attr('disabled', true);
            }else{
                $("#available_for_delivery").attr('disabled', false);
            }
        });

        $.validator.addMethod('positiveNumber', function(value, element) {
            return Number(value) >= 0
        }, 'Negative Number is not allowed.');


        $('#form-create').validate({
            rules:{
                'delivery_range': {
                    required: false,
                    positiveNumber: true,
                },
                'default_tax': {
                    required: false,
                    positiveNumber: true,
                },
                'order_request_commission_amount': {
                    required: false,
                    positiveNumber: true,
                },
                // 'phone' : {
                //     maxlength: 10
                // },
                'mobile' : {
                    maxlength: 10,
                    minlength: 10
                }
            },
        });


        $("#primary_sector_id").change(function () {

            var sector = $(this).val();
            instaContainer(sector);
            var values = $("#field_id").val();
            sectorCategories(values);


        });

        $("#field_id").change(function () {

          var values = $(this).val();
          var sector;
          console.log(values);
            sectorCategories(values);

          if(jQuery.inArray('7', values) !== -1) {

              sector = 7;
              instaContainer(sector);

          }
          else {
              instaContainer(null);
          }
        });

        function instaContainer(sector) {

            if(sector == 7){
                $("#insta-container").removeClass('d-none');
                $("#image-row").addClass('d-none');
            }
            else{
                $("#insta-container").addClass('d-none');
                $("#image-row").removeClass('d-none');
            }
        }
        function sectorCategories(values) {
            console.log(values);
            console.log("sectorIds");
            var primary_sector_id =  $("#primary_sector_id").val();
            if (values) {
                $.ajax({
                    url: '/market_categories/ajax',
                    method: "POST",
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "sectorIds": values,
                        "primary_sector_id": primary_sector_id
                    },
                    dataType: "json",
                    success: function (data) {
                        $('#category_id').empty();
                        $.each(data, function (key, value) {
                            $('#category_id').append('<option value="' + value.id + '"' + '>' + value.name+ '</option>');
                            // $('#category_id').append('<option value="' + value.id + '"' +  '>' + value.name+ '</option>');

                        });
                    }
                });
            }
        }
        var cityId = $('#city_id').val();
        circleByCity(cityId)

        $("#city_id").change(function () {

            var cityId = $('#city_id').val();
            circleByCity(cityId)
        });

        function circleByCity(cityId) {
            if (cityId) {
                $.ajax({
                    url: '/circle/city/' + cityId,
                    type: "GET",
                    dataType: "json",
                    success: function (data) {
                        $('select[name="area_id"]').empty();

                        $.each(data, function (key, value) {
                            $('#area_id').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    }
                });
            }
        }

    });
</script>
<script>

    $( document ).ready(function() {

        $('#locationpicker').locationpicker({
            enableAutocomplete: true,
            enableReverseGeocode: true,
            radius: 0,
            inputBinding: {
                latitudeInput: $('#latitude'),
                longitudeInput: $('#longitude'),
                locationNameInput: $('#user-location')
            },
            onchanged: function (currentLocation, radius, isMarkerDropped) {
                var addressComponents = $(this).locationpicker('map').location.addressComponents;
                console.log(currentLocation);  //latlon
                updateControls(addressComponents); //Data
            },
        });

        function updateControls(addressComponents) {
            console.log(addressComponents);
        }
    });


</script>
@endpush