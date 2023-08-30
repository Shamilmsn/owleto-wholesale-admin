<div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">
  <div class="form-group row ">
    {!! Form::label('name', trans("lang.user_name"), ['class' => 'col-3 control-label text-right']) !!}
    <div class="col-9">
      {!! Form::text('name', null,  ['class' => 'form-control','placeholder'=>  trans("lang.user_name_placeholder")]) !!}
      <div class="form-text text-muted">
        {{ trans("lang.user_name_help") }}
      </div>
    </div>
  </div>

  <div class="form-group row ">
    {!! Form::label('email', trans("lang.user_email"), ['class' => 'col-3 control-label text-right']) !!}
    <div class="col-9">
      {!! Form::text('email', null,  ['class' => 'form-control','placeholder'=>  trans("lang.user_email_placeholder")]) !!}
      <div class="form-text text-muted">
        {{ trans("lang.user_email_help") }}
      </div>
    </div>
  </div>

  <div class="form-group row ">
    {!! Form::label('phone', trans("lang.user_mobile"), ['class' => 'col-3 control-label text-right']) !!}
    <div class="col-9">
      <input type="text" name="phone" class="form-control"
             placeholder="Enter the phone" required maxlength="10" minlength="10">
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

  <div class="form-group row ">
    {!! Form::label('delivery_fee', trans("lang.driver_delivery_fee"), ['class' => 'col-3 control-label text-right']) !!}
    <div class="col-9">
      {!! Form::text('delivery_fee', null,  ['class' => 'form-control','placeholder'=>  trans("lang.driver_delivery_fee_placeholder")]) !!}
      <div class="form-text text-muted">
        {{ trans("lang.driver_delivery_fee_help") }}
      </div>
    </div>
  </div>

  <div class="form-group row ">
    {!! Form::label('available', trans("lang.active"),['class' => 'col-3 control-label text-right']) !!}
    <div class="checkbox icheck">
      <label class="col-9 ml-2 form-check-inline">
        {!! Form::hidden('available', 0) !!}
        {!! Form::checkbox('available', 1, null) !!}
      </label>
    </div>
  </div>


</div>
<div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">

  <div class="form-group row ">
    {!! Form::label('address','Address', ['class' => 'col-3 control-label text-right']) !!}
    <div class="col-9">
      {!! Form::textarea('address', null,  ['class' => 'form-control','placeholder'=>  'Enter the address']) !!}
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
@push('scripts_lib')
    <script>

    $(document).ready(function() {

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
                $('select[name="circle_id"]').empty();

                  $.each(data, function (key, value) {
                      $('#circle_id').append('<option value="' + value.id + '">' + value.name + '</option>');
                  });
                }
              });
            }
        }
    });

    </script>
@endpush