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
        <h1 class="m-0 text-dark">{{trans('lang.driver_plural')}}
{{--          <small class="ml-3 mr-3">|</small><small>{{trans('lang.driver_desc')}}</small>--}}
        </h1>
      </div><!-- /.col -->
{{--      <div class="col-sm-6">--}}
{{--        <ol class="breadcrumb float-sm-right">--}}
{{--          <li class="breadcrumb-item"><a href="{{url('/dashboard')}}"><i class="fa fa-dashboard"></i> {{trans('lang.dashboard')}}</a></li>--}}
{{--          <li class="breadcrumb-item"><a href="{!! route('drivers.index') !!}">{{trans('lang.driver_plural')}}</a>--}}
{{--          </li>--}}
{{--          <li class="breadcrumb-item active">{{trans('lang.driver_edit')}}</li>--}}
{{--        </ol>--}}
{{--      </div><!-- /.col -->--}}
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->
<div class="content">
  <div class="clearfix"></div>
  @include('flash::message')
  @include('adminlte-templates::common.errors')
  <div class="clearfix"></div>
  <div class="card">
    <div class="card-header">
      <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
        @can('drivers.index')
        <li class="nav-item">
          <a class="nav-link" href="{!! route('drivers.index') !!}"><i class="fa fa-list mr-2"></i>Drivers list</a>
        </li>
        @endcan
{{--        @can('drivers.create')--}}
{{--        <li class="nav-item">--}}
{{--          <a class="nav-link" href="{!! route('drivers.create') !!}"><i class="fa fa-plus mr-2"></i>{{trans('lang.driver_create')}}</a>--}}
{{--        </li>--}}
{{--        @endcan--}}
        <li class="nav-item">
          <a class="nav-link active" href="{!! url()->current() !!}"><i class="fa fa-pencil mr-2"></i>{{trans('lang.driver_edit')}}</a>
        </li>
      </ul>
    </div>
    <div class="card-body">
      {!! Form::model($user, ['route' => ['drivers.update', $user->id], 'method' => 'patch']) !!}
      <div class="row">
        <div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">
          <!-- Name Field -->
          <div class="form-group row ">
            {!! Form::label('name', trans("lang.user_name"), ['class' => 'col-3 control-label text-right']) !!}
            <div class="col-9">
              {!! Form::text('name', null,  ['class' => 'form-control','placeholder'=>  trans("lang.user_name_placeholder")]) !!}
              <div class="form-text text-muted">
                {{ trans("lang.user_name_help") }}
              </div>
            </div>
          </div>

          <!-- Email Field -->
          <div class="form-group row ">
            {!! Form::label('email', trans("lang.user_email"), ['class' => 'col-3 control-label text-right']) !!}
            <div class="col-9">
              {!! Form::text('email', null,  ['class' => 'form-control','placeholder'=>  trans("lang.user_email_placeholder"), 'disabled' => true]) !!}
              <div class="form-text text-muted">
                {{ trans("lang.user_email_help") }}
              </div>
            </div>
          </div>

          <div class="form-group row ">
            {!! Form::label('phone', trans("lang.user_mobile"), ['class' => 'col-3 control-label text-right']) !!}
            <div class="col-9">
              <input type="text" name="phone" class="form-control"
                     placeholder="Enter the phone" required maxlength="10" minlength="10" value="{{ $user->phone }}">
              <div class="form-text text-muted">
                {{ trans("lang.user_mobile_help") }}
              </div>
            </div>
          </div>

          <div class="form-group row ">
            {!! Form::label('city_id', trans("lang.city_id"),['class' => 'col-3 control-label text-right']) !!}
            <div class="col-9">
              {!! Form::select('city_id', $cities, null, ['class' => 'select2 form-control', 'id' => 'city_id']) !!}
              <div class="form-text text-muted">{{ trans("lang.city_id_help") }}</div>
            </div>
          </div>

          <div class="form-group row ">
            {!! Form::label('circle_id', trans("lang.area_id"),['class' => 'col-3 control-label text-right']) !!}
            <div class="col-9">
              <select name="circle_id" id="circle_id" class=" form-control">
              </select>
              <div class="form-text text-muted">{{ trans("lang.area_id_help") }}</div>
            </div>
          </div>

          <div class="form-group row ">
            {!! Form::label('vehicle_id', trans("lang.vehicle_id"),['class' => 'col-3 control-label text-right']) !!}
            <div class="col-9">
              {!! Form::select('vehicle_id', $vehicles, null, ['class' => 'select2 form-control', 'id' => 'vehicle_id']) !!}
              <div class="form-text text-muted">{{ trans("lang.vehicle_id_help") }}</div>
            </div>
          </div>

          <!-- delivery fee Field -->
          <div class="form-group row ">
            <label for="delivery_fee" class="col-3 control-label text-right">{{ trans("lang.driver_delivery_fee") }}</label>
            <div class="col-9">
              <input class="form-control" placeholder="{{ trans("lang.driver_delivery_fee_placeholder") }}"
                     name="delivery_fee" type="text" id="delivery_fee" value="{{ $driver->delivery_fee }}">
              <div class="form-text text-muted">
                {{ trans("lang.driver_delivery_fee_help") }}
              </div>
            </div>
          </div>

          <div class="form-group row ">
            {!! Form::label('base_distance', 'Base Distance', ['class' => 'col-3 control-label text-right']) !!}
            <div class="col-9">
              <input class="form-control" placeholder="Enter the base distance"
                     name="base_distance" type="text" id="base_distance" value="{{ $driver->base_distance }}">
              <div class="form-text text-muted">
                Enter the base distance
              </div>
            </div>
          </div>

          <div class="form-group row ">
            {!! Form::label('additional_amount', 'Additional Amount', ['class' => 'col-3 control-label text-right']) !!}
            <div class="col-9">
              <input class="form-control" placeholder="Enter the additional amount"
                     name="additional_amount" type="text" id="additional_amount" value="{{ $driver->additional_amount }}">
              <div class="form-text text-muted">
                Enter the additional amount
              </div>
            </div>
          </div>

          <div class="form-group row ">
            {!! Form::label('available', trans("lang.active"),['class' => 'col-3 control-label text-right']) !!}
            <div class="checkbox icheck">
              <label class="col-9 ml-2 form-check-inline">
{{--                {!! Form::hidden('available', 0) !!}--}}
{{--                {!! Form::checkbox('available', 1, null) !!}--}}
                <input class="form-control" placeholder="{{ trans("lang.driver_delivery_fee_placeholder") }}"
                       name="available" type="checkbox" id="available" @if($driver->available == 1) checked @endif>
              </label>
            </div>
          </div>

        </div>
        <div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">

          <div class="form-group row ">
            {!! Form::label('address','Address', ['class' => 'col-3 control-label text-right']) !!}
            <div class="col-9">
              {!! Form::textarea('address', $user->address,  ['class' => 'form-control','placeholder'=>  'Enter the address']) !!}
              <div class="form-text text-muted">
                Enter the address
              </div>
            </div>
          </div>

          <div class="form-group row">
            {!! Form::label('avatar', trans("lang.user_avatar"), ['class' => 'col-3 control-label text-right']) !!}
            <div class="col-9">
              <div style="width: 100%" class="dropzone avatar" id="avatar" data-field="avatar">
                <input type="hidden" name="avatar">
              </div>
              <a href="#loadMediaModal" data-dropzone="avatar" data-toggle="modal" data-target="#mediaModal" class="btn btn-outline-{{setting('theme_color','primary')}} btn-sm float-right mt-1">{{ trans('lang.media_select')}}</a>
              <div class="form-text text-muted w-50">
                {{ trans("lang.user_avatar_help") }}
              </div>
            </div>
          </div>
          @prepend('scripts')
            <script type="text/javascript">
              var user_avatar = '';
              @if(isset($user) && $user->hasMedia('avatar'))
                      user_avatar = {
                name: "{!! $user->getFirstMedia('avatar')->name !!}",
                size: "{!! $user->getFirstMedia('avatar')->size !!}",
                type: "{!! $user->getFirstMedia('avatar')->mime_type !!}",
                collection_name: "{!! $user->getFirstMedia('avatar')->collection_name !!}"
              };
              @endif
              var dz_user_avatar = $(".dropzone.avatar").dropzone({
                url: "{!!url('uploads/store')!!}",
                addRemoveLinks: true,
                maxFiles: 1,
                init: function () {
                  @if(isset($user) && $user->hasMedia('avatar'))
                  dzInit(this, user_avatar, '{!! url($user->getFirstMediaUrl('avatar','thumb')) !!}')
                  @endif
                },
                accept: function (file, done) {
                  dzAccept(file, done, this.element, "{!!config('medialibrary.icons_folder')!!}");
                },
                sending: function (file, xhr, formData) {
                  dzSending(this, file, formData, '{!! csrf_token() !!}');
                },
                maxfilesexceeded: function (file) {
                  dz_user_avatar[0].mockFile = '';
                  dzMaxfile(this, file);
                },
                complete: function (file) {
                  dzComplete(this, file, user_avatar, dz_user_avatar[0].mockFile);
                  dz_user_avatar[0].mockFile = file;
                },
                removedfile: function (file) {
                  dzRemoveFile(
                          file, user_avatar, '{!! url("users/remove-media") !!}',
                          'avatar', '{!! isset($user) ? $user->id : 0 !!}', '{!! url("uplaods/clear") !!}', '{!! csrf_token() !!}'
                  );
                }
              });
              dz_user_avatar[0].mockFile = user_avatar;
              dropzoneFields['avatar'] = dz_user_avatar;
            </script>
          @endprepend

            <input type="hidden" name="roles[]" value="driver" >

        </div>
        <div class="form-group col-12 text-right">
          <button type="submit" class="btn btn-{{setting('theme_color')}}"><i class="fa fa-save"></i> {{trans('lang.save')}} {{trans('lang.driver')}}</button>
          <a href="{!! route('drivers.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i> {{trans('lang.cancel')}}</a>
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
<script type="text/javascript">
    Dropzone.autoDiscover = false;
    var dropzoneFields = [];
</script>
@endpush

@push('scripts_lib')
  <script>

    $(document).ready(function() {

      var cityId = $('#city_id').val();

      var circle_id = '{{ $driver->circle_id }}';

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
              $('select[name="circle_id"]').empty();

              $.each(data, function (key, value) {
                $('#circle_id').append('<option value="' + value.id + '"' + (value.id == circle_id ? 'selected="selected"' : '') +
                        '>' + value.name+ '</option>');
              });
            }
          });
        }
      }
    });

  </script>
@endpush