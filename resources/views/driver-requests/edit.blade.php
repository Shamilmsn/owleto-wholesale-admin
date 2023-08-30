@extends('layouts.app')
@push('css_lib')
<!-- iCheck -->
<link rel="stylesheet" href="{{asset('plugins/iCheck/flat/blue.css')}}">
<!-- select2 -->
<link rel="stylesheet" href="{{asset('plugins/select2/select2.min.css')}}">
<!-- bootstrap wysihtml5 - text editor -->
<link rel="stylesheet" href="{{asset('plugins/summernote/summernote-bs4.css')}}">
{{--dropzone--}}
<link rel="stylesheet" href="{{asset('plugins/dropzone/bootstrap.min.css')}}">
@endpush
@section('content')
<!-- Content Header (Page header) -->
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">{{trans('lang.requested_drivers')}}</h1>
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
        @can('driver-requests.index')
        <li class="nav-item">
          <a class="nav-link" href="{!! route('driver-requests.index') !!}"><i class="fa fa-list mr-2"></i>{{trans('lang.driver_table')}}</a>
        </li>
        @endcan
        <li class="nav-item">
          <a class="nav-link active" href="{!! url()->current() !!}"><i class="fa fa-pencil mr-2"></i>{{trans('lang.driver_edit')}}</a>
        </li>
      </ul>
    </div>
    <div class="card-body">
      {!! Form::model($user, ['route' => ['driver-requests.update', $user->id], 'method' => 'patch']) !!}
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

          <div class="form-group row ">
            {!! Form::label('admin_approved', trans("lang.admin_approve"),['class' => 'col-3 control-label text-right']) !!}
            <div class="checkbox icheck">
              <label class="col-9 ml-2 form-check-inline">
                {!! Form::hidden('admin_approved', 0) !!}
                {!! Form::checkbox('admin_approved', 1, null) !!}
              </label>
            </div>
          </div>

        </div>
{{--        <div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">--}}
{{--          <!-- $FIELD_NAME_TITLE$ Field -->--}}
{{--          <div class="form-group row">--}}
{{--            {!! Form::label('avatar', trans("lang.user_avatar"), ['class' => 'col-3 control-label text-right']) !!}--}}
{{--            <div class="col-9">--}}
{{--              <div style="width: 100%" class="dropzone avatar" id="avatar" data-field="avatar">--}}
{{--                <input type="hidden" name="avatar">--}}
{{--              </div>--}}
{{--              <a href="#loadMediaModal" data-dropzone="avatar" data-toggle="modal" data-target="#mediaModal" class="btn btn-outline-{{setting('theme_color','primary')}} btn-sm float-right mt-1">{{ trans('lang.media_select')}}</a>--}}
{{--              <div class="form-text text-muted w-50">--}}
{{--                {{ trans("lang.user_avatar_help") }}--}}
{{--              </div>--}}
{{--            </div>--}}
{{--          </div>--}}
{{--          @prepend('scripts')--}}
{{--            <script type="text/javascript">--}}
{{--              var user_avatar = '';--}}
{{--              @if(isset($user) && $user->hasMedia('avatar'))--}}
{{--                      user_avatar = {--}}
{{--                name: "{!! $user->getFirstMedia('avatar')->name !!}",--}}
{{--                size: "{!! $user->getFirstMedia('avatar')->size !!}",--}}
{{--                type: "{!! $user->getFirstMedia('avatar')->mime_type !!}",--}}
{{--                collection_name: "{!! $user->getFirstMedia('avatar')->collection_name !!}"--}}
{{--              };--}}
{{--              @endif--}}
{{--              var dz_user_avatar = $(".dropzone.avatar").dropzone({--}}
{{--                url: "{!!url('uploads/store')!!}",--}}
{{--                addRemoveLinks: true,--}}
{{--                maxFiles: 1,--}}
{{--                init: function () {--}}
{{--                  @if(isset($user) && $user->hasMedia('avatar'))--}}
{{--                  dzInit(this, user_avatar, '{!! url($user->getFirstMediaUrl('avatar','thumb')) !!}')--}}
{{--                  @endif--}}
{{--                },--}}
{{--                accept: function (file, done) {--}}
{{--                  dzAccept(file, done, this.element, "{!!config('medialibrary.icons_folder')!!}");--}}
{{--                },--}}
{{--                sending: function (file, xhr, formData) {--}}
{{--                  dzSending(this, file, formData, '{!! csrf_token() !!}');--}}
{{--                },--}}
{{--                maxfilesexceeded: function (file) {--}}
{{--                  dz_user_avatar[0].mockFile = '';--}}
{{--                  dzMaxfile(this, file);--}}
{{--                },--}}
{{--                complete: function (file) {--}}
{{--                  dzComplete(this, file, user_avatar, dz_user_avatar[0].mockFile);--}}
{{--                  dz_user_avatar[0].mockFile = file;--}}
{{--                },--}}
{{--                removedfile: function (file) {--}}
{{--                  dzRemoveFile(--}}
{{--                          file, user_avatar, '{!! url("users/remove-media") !!}',--}}
{{--                          'avatar', '{!! isset($user) ? $user->id : 0 !!}', '{!! url("uplaods/clear") !!}', '{!! csrf_token() !!}'--}}
{{--                  );--}}
{{--                }--}}
{{--              });--}}
{{--              dz_user_avatar[0].mockFile = user_avatar;--}}
{{--              dropzoneFields['avatar'] = dz_user_avatar;--}}
{{--            </script>--}}
{{--          @endprepend--}}
{{--        </div>--}}
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
<script src="{{asset('plugins/summernote/summernote-bs4.min.js')}}"></script>
{{--dropzone--}}
<script src="{{asset('plugins/dropzone/dropzone.js')}}"></script>
<script type="text/javascript">
    Dropzone.autoDiscover = false;
    var dropzoneFields = [];
</script>
@endpush