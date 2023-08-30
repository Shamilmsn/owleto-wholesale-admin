@extends('layouts.settings.default')
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
@section('settings_title',trans('lang.user_table'))
@section('settings_content')
  @include('flash::message')
  @include('adminlte-templates::common.errors')
  <div class="clearfix"></div>
  <div class="card">
    <div class="card-header">
      <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
        <li class="nav-item">
          <a class="nav-link" href="{!! route('users.index') !!}"><i class="fa fa-list mr-2"></i>{{trans('lang.user_table')}}</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" href="{!! url()->current() !!}"><i class="fa fa-plus mr-2"></i>{{trans('lang.user_create')}}</a>
        </li>
      </ul>
    </div>
    <div class="card-body">
      {!! Form::open(['route' => 'users.store', 'id' => 'form-create']) !!}
      <div class="row">
        @if($customFields)
          <h5 class="col-12 pb-4">{!! trans('lang.main_fields') !!}</h5>
        @endif
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
          @if(Request::segment(3) != 'edit')
          <div class="form-group row ">
            {!! Form::label('email', trans("lang.user_email"), ['class' => 'col-3 control-label text-right']) !!}
            <div class="col-9">
              {!! Form::text('email', null,  ['class' => 'form-control ','id' => 'email' ,'placeholder'=>  trans("lang.user_email_placeholder") ]) !!}
              <div class="form-text text-muted">
                {{ trans("lang.user_email_help") }}
              </div>
            </div>
          </div>
         @endif
        <!-- Password Field -->
          <div class="form-group row ">
            {!! Form::label('password', trans("lang.user_password"), ['class' => 'col-3 control-label text-right']) !!}
            <div class="col-9">
              {!! Form::password('password', ['class' => 'form-control','placeholder'=>  trans("lang.user_password_placeholder")]) !!}
              <div class="form-text text-muted">
                {{ trans("lang.user_password_help") }}
              </div>
            </div>
          </div>

        <!-- Mobile Field -->
          <div class="form-group row ">
            {!! Form::label('phone', trans("lang.user_phone"), ['class' => 'col-3 control-label text-right']) !!}
            <div class="col-9">
              {!! Form::number('phone', null,  ['class' => 'form-control','id' => 'phone','placeholder'=>  trans("lang.user_phone_placeholder"), 'step'=>"any", 'min'=>"0"]) !!}
              <div class="form-text text-muted">
                {{ trans("lang.user_phone_help") }}
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
        </div>
        <div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">
          <!-- $FIELD_NAME_TITLE$ Field -->
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
          @can('permissions.index')
          <!-- Roles Field -->
            <div class="form-group row ">
              {!! Form::label('roles[]', trans("lang.user_role_id"),['class' => 'col-3 control-label text-right']) !!}
              <div class="col-9">
                {!! Form::select('roles[]', $role, $rolesSelected, ['class' => 'select2 form-control' , 'multiple'=>'multiple']) !!}
                <div class="form-text text-muted">{{ trans("lang.user_role_id_help") }}</div>
              </div>
            </div>
          @endcan

        </div>
        @if($customFields)
{{--          TODO generate custom field--}}
          <div class="clearfix"></div>
          <div class="col-12 custom-field-container">
            <h5 class="col-12 pb-4">{!! trans('lang.custom_field_plural') !!}</h5>
            {!! $customFields !!}
          </div>
      @endif
      <!-- Submit Field -->
        <div class="form-group col-12 text-right">
          <button type="submit" class="btn btn-{{setting('theme_color')}}"><i class="fa fa-save"></i> {{trans('lang.save')}} {{trans('lang.user')}}</button>
          <a href="{!! route('users.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i> {{trans('lang.cancel')}}</a>
        </div>
      </div>
      {!! Form::close() !!}
      <div class="clearfix"></div>
    </div>
  </div>
</div>
@include('layouts.media_modal',['collection'=>null])
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/additional-methods.js"> </script>
<script type="text/javascript">
    Dropzone.autoDiscover = false;
    var dropzoneFields = [];

    jQuery.validator.addMethod("lettersonly", function(value, element) {
      return this.optional(element) || /^[a-zA-Z\s]+$/.test(value);
    }, "Enter Letters only");

    $('#form-create').validate({
      rules: {
        name: {
          required: true,
          lettersonly: true
        },
        email: {
          required: true,
          email: true,
          remote: {
            url: '/check-email-unique',
            type: 'post',
            data: {
              email: function () {
                return $('#email').val();
              },
              '_token': "{{ csrf_token() }}",
            }
          }
        },
        phone: {
          required: false,
          remote: {
            url: '/check-phone-unique',
            type: 'post',
            data: {
              phone: function () {
                return $('#phone').val();
              },
              '_token': "{{ csrf_token() }}",
            }
          }
        }
      },
      messages:{
        email: {
          remote: "Email has been already taken"
        },
        phone: {
          remote: "Phone Number has been already taken"
        }
      }
    })
</script>


@endpush